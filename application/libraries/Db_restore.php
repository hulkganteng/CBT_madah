<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Restore database dua tahap.
 *
 * DDL MySQL tidak bisa di-rollback lewat transaksi, jadi dump tidak pernah
 * dijalankan langsung ke database aktif:
 *   1. impor ke database staging,
 *   2. verifikasi tabel/kolom/data penting di staging,
 *   3. tukar tabel secara atomik memakai satu perintah RENAME TABLE,
 *   4. verifikasi ulang; bila gagal, tukar balik sebelum staging dihapus.
 *
 * File backup selalu divalidasi dulu (zip, isi SQL, ukuran, perintah
 * berbahaya). Saat restore berjalan dibuat berkas kunci
 * backups/.restore.lock yang dibaca Exam_security_hook sebagai
 * maintenance mode.
 */
class Db_restore
{
    const MAX_SQL_BYTES = 536870912; // 512 MB isi SQL setelah unzip

    /** Tabel dan kolom minimum agar aplikasi tidak rusak setelah restore. */
    private static $required = array(
        'users' => array('id', 'username', 'password'),
        'users_groups' => array('user_id', 'group_id'),
        'setting' => array('id_setting', 'sekolah'),
        'cbt_bank_soal' => array('id_bank', 'bank_nama'),
        'cbt_jadwal' => array('id_jadwal', 'id_bank'),
        'cbt_soal' => array('id_soal', 'bank_id'),
        'cbt_nilai' => array('id_nilai', 'id_siswa', 'id_jadwal'),
        'cbt_durasi_siswa' => array('id_durasi', 'id_siswa', 'id_jadwal', 'status'),
        'cbt_exam_attempt' => array('attempt_id', 'id_siswa', 'id_jadwal', 'status'),
    );

    private $config;
    private $folder;

    public function __construct(array $config)
    {
        foreach (array('hostname', 'username', 'database', 'folder') as $key) {
            if (!isset($config[$key]) || (string) $config[$key] === '') {
                throw new InvalidArgumentException('Konfigurasi restore tidak lengkap: ' . $key);
            }
        }
        if (!preg_match('/^[A-Za-z0-9_]+$/', (string) $config['database'])) {
            throw new InvalidArgumentException('Nama database tidak valid untuk restore.');
        }

        $this->config = array(
            'hostname' => (string) $config['hostname'],
            'username' => (string) $config['username'],
            'password' => isset($config['password']) ? (string) $config['password'] : '',
            'database' => (string) $config['database'],
            'port' => !empty($config['port']) ? (int) $config['port'] : NULL,
        );
        $this->folder = rtrim((string) $config['folder'], '/\\') . DIRECTORY_SEPARATOR;
    }

    /**
     * Validasi berkas backup dan kembalikan isi SQL-nya.
     * Melempar RuntimeException bila berkas tidak layak dipulihkan.
     */
    public function readBackup($path)
    {
        if (!is_file($path) || !is_readable($path)) {
            throw new RuntimeException('Berkas backup tidak ditemukan atau tidak dapat dibaca.');
        }
        $size = @filesize($path);
        if ($size === FALSE || $size <= 0) {
            throw new RuntimeException('Berkas backup kosong.');
        }
        if (!class_exists('ZipArchive')) {
            throw new RuntimeException('Ekstensi PHP zip tidak tersedia.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE) {
            throw new RuntimeException('Berkas backup bukan arsip zip yang valid.');
        }
        $sql = NULL;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nama = $zip->getNameIndex($i);
            if (!preg_match('/\.sql$/i', (string) $nama)) {
                continue;
            }
            $stat = $zip->statIndex($i);
            if (!is_array($stat) || !isset($stat['size']) || (int) $stat['size'] > self::MAX_SQL_BYTES) {
                $zip->close();
                throw new RuntimeException('Isi SQL di dalam backup terlalu besar.');
            }
            $sql = $zip->getFromIndex($i);
            break;
        }
        $zip->close();

        if (!is_string($sql) || trim($sql) === '') {
            throw new RuntimeException('Backup tidak berisi berkas .sql.');
        }
        if (strlen($sql) > self::MAX_SQL_BYTES) {
            throw new RuntimeException('Isi SQL di dalam backup terlalu besar.');
        }
        if (!preg_match('/\bCREATE\s+TABLE\b/i', $sql)) {
            throw new RuntimeException('Backup tidak berisi perintah CREATE TABLE.');
        }
        if (preg_match('/(?:^|;)\s*(?:USE|CREATE\s+DATABASE|DROP\s+DATABASE|GRANT|REVOKE|CREATE\s+USER|ALTER\s+USER|DROP\s+USER|SET\s+PASSWORD|INSTALL\s+PLUGIN|SHUTDOWN)\b/i', $sql)
            || preg_match('/\b(?:INTO\s+(?:OUT|DUMP)FILE|LOAD\s+DATA)\b/i', $sql)) {
            throw new RuntimeException('Backup berisi perintah yang tidak diizinkan saat restore.');
        }

        return $sql;
    }

    /**
     * Jalankan restore penuh. Mengembalikan TRUE atau melempar RuntimeException.
     */
    public function restore($path)
    {
        $sql = $this->readBackup($path);
        $handle = $this->lock();
        $server = NULL;
        $dibuat = array();
        $diaktifkan = array();
        $diganti = array();

        try {
            $server = $this->connect('');
            $staging = $this->temporaryName('new');
            $rollback = $this->temporaryName('old');

            $this->query($server, "CREATE DATABASE `{$staging}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $dibuat[] = $staging;
            $this->query($server, "CREATE DATABASE `{$rollback}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
            $dibuat[] = $rollback;

            $this->import($server, $staging, $sql);
            $this->verify($server, $staging);

            list($diaktifkan, $diganti) = $this->swap($server, $staging, $rollback);
            $this->verify($server, $this->config['database']);
        } catch (Throwable $error) {
            $pesan = $error->getMessage();
            if ($server !== NULL && $diaktifkan) {
                try {
                    $this->swapBack($server, $staging, $rollback, $diaktifkan, $diganti);
                    $pesan .= ' Data lama sudah dikembalikan.';
                } catch (Throwable $rollbackError) {
                    $pesan .= ' PENGEMBALIAN OTOMATIS GAGAL: ' . $rollbackError->getMessage()
                        . ' Gunakan backup pengaman dan database ' . $rollback . '.';
                }
            }
            if ($server !== NULL) {
                foreach (array_reverse($dibuat) as $db) {
                    $server->query("DROP DATABASE IF EXISTS `{$db}`");
                }
                $server->close();
            }
            $this->unlock($handle);
            throw new RuntimeException($pesan);
        }

        foreach ($dibuat as $db) {
            $server->query("DROP DATABASE IF EXISTS `{$db}`");
        }
        $server->close();
        $this->unlock($handle);
        return TRUE;
    }

    private function import(mysqli $server, $database, $sql)
    {
        if (!$server->select_db($database)) {
            throw new RuntimeException('Database staging tidak dapat dipilih: ' . $server->error);
        }
        if (!$server->multi_query($sql)) {
            throw new RuntimeException('Impor ke staging gagal: ' . $server->error);
        }
        do {
            if ($result = $server->store_result()) {
                $result->free();
            }
            if ($server->errno) {
                throw new RuntimeException('Impor ke staging gagal: ' . $server->error);
            }
        } while ($server->more_results() && $server->next_result());
        if ($server->errno) {
            throw new RuntimeException('Impor ke staging gagal: ' . $server->error);
        }
    }

    /**
     * Pastikan staging berisi tabel, kolom, dan data penting sebelum
     * menyentuh database aktif.
     */
    private function verify(mysqli $server, $database)
    {
        $tabel = $this->tables($server, $database);
        if (!$tabel) {
            throw new RuntimeException('Database hasil restore tidak memiliki tabel.');
        }

        $kolom = array();
        $escaped = $server->real_escape_string($database);
        $result = $server->query("SELECT TABLE_NAME, COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$escaped}'");
        if (!$result) {
            throw new RuntimeException('Struktur database tidak dapat dibaca: ' . $server->error);
        }
        while ($row = $result->fetch_row()) {
            $kolom[$row[0]][$row[1]] = TRUE;
        }
        $result->free();

        foreach (self::$required as $table => $columns) {
            if (!isset($kolom[$table])) {
                throw new RuntimeException('Tabel wajib tidak ada di backup: ' . $table);
            }
            foreach ($columns as $column) {
                if (!isset($kolom[$table][$column])) {
                    throw new RuntimeException('Kolom wajib hilang: ' . $table . '.' . $column);
                }
            }
        }

        $users = $this->count($server, $database, 'users');
        if ($users < 1) {
            throw new RuntimeException('Backup tidak berisi akun pengguna.');
        }
        if ($this->count($server, $database, 'setting') < 1) {
            throw new RuntimeException('Backup tidak berisi profil sekolah.');
        }
        $admin = $server->query("SELECT COUNT(*) FROM `{$database}`.`users_groups` WHERE group_id = 1");
        if (!$admin || (int) $admin->fetch_row()[0] < 1) {
            throw new RuntimeException('Backup tidak berisi akun administrator.');
        }
    }

    private function count(mysqli $server, $database, $table)
    {
        $result = $server->query("SELECT COUNT(*) FROM `{$database}`.`{$table}`");
        if (!$result) {
            throw new RuntimeException('Data ' . $table . ' tidak dapat dibaca: ' . $server->error);
        }
        $total = (int) $result->fetch_row()[0];
        $result->free();
        return $total;
    }

    /**
     * Tukar tabel staging ke database aktif dalam satu perintah RENAME TABLE.
     * Tabel lama dipindah ke database rollback, bukan dihapus.
     *
     * @return array [tabel yang diaktifkan, tabel yang punya salinan lama]
     */
    private function swap(mysqli $server, $from, $rollback)
    {
        $aktif = $this->tables($server, $this->config['database']);
        $baru = $this->tables($server, $from);
        if (!$baru) {
            throw new RuntimeException('Database staging kosong.');
        }
        $live = array_flip($aktif);
        $parts = array();
        $diganti = array();
        foreach ($baru as $table) {
            if (isset($live[$table])) {
                $parts[] = "`{$this->config['database']}`.`{$table}` TO `{$rollback}`.`{$table}`";
                $diganti[] = $table;
            }
            $parts[] = "`{$from}`.`{$table}` TO `{$this->config['database']}`.`{$table}`";
        }

        $server->query('SET FOREIGN_KEY_CHECKS=0');
        $ok = $server->query('RENAME TABLE ' . implode(', ', $parts));
        $server->query('SET FOREIGN_KEY_CHECKS=1');
        if (!$ok) {
            throw new RuntimeException('Aktivasi tabel gagal: ' . $server->error);
        }

        return array($baru, $diganti);
    }

    private function swapBack(mysqli $server, $from, $rollback, array $aktif, array $diganti)
    {
        $live = array_flip($this->tables($server, $this->config['database']));
        $lama = array_flip($diganti);
        $parts = array();
        foreach ($aktif as $table) {
            if (!isset($live[$table])) {
                continue;
            }
            $parts[] = "`{$this->config['database']}`.`{$table}` TO `{$from}`.`{$table}`";
            if (isset($lama[$table])) {
                $parts[] = "`{$rollback}`.`{$table}` TO `{$this->config['database']}`.`{$table}`";
            }
        }
        if (!$parts) {
            return;
        }
        $server->query('SET FOREIGN_KEY_CHECKS=0');
        $ok = $server->query('RENAME TABLE ' . implode(', ', $parts));
        $server->query('SET FOREIGN_KEY_CHECKS=1');
        if (!$ok) {
            throw new RuntimeException('Rollback tabel gagal: ' . $server->error);
        }
    }

    private function tables(mysqli $server, $database)
    {
        $escaped = $server->real_escape_string($database);
        $result = $server->query("SHOW FULL TABLES FROM `{$escaped}` WHERE Table_type = 'BASE TABLE'");
        if (!$result) {
            throw new RuntimeException('Daftar tabel tidak dapat dibaca: ' . $server->error);
        }
        $tables = array();
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
        }
        $result->free();
        return $tables;
    }

    private function temporaryName($label)
    {
        $base = substr(preg_replace('/[^A-Za-z0-9_]/', '_', $this->config['database']), 0, 30);
        return $base . '_restore_' . $label . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(2));
    }

    private function lock()
    {
        if (!is_dir($this->folder) || !is_writable($this->folder)) {
            throw new RuntimeException('Folder backup tidak dapat ditulis.');
        }
        $marker = $this->folder . '.restore.lock';
        $handle = @fopen($marker, 'c');
        if (!$handle) {
            throw new RuntimeException('Kunci restore tidak dapat dibuat. Periksa izin folder backups.');
        }
        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            throw new RuntimeException('Restore lain sedang berjalan. Tunggu hingga selesai.');
        }
        ftruncate($handle, 0);
        fwrite($handle, date('c'));
        fflush($handle);
        return $handle;
    }

    private function unlock($handle)
    {
        flock($handle, LOCK_UN);
        fclose($handle);
        @unlink($this->folder . '.restore.lock');
    }

    private function connect($database)
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $link = $this->config['port'] === NULL
            ? @new mysqli($this->config['hostname'], $this->config['username'], $this->config['password'], $database)
            : @new mysqli($this->config['hostname'], $this->config['username'], $this->config['password'], $database, $this->config['port']);
        if ($link->connect_errno) {
            throw new RuntimeException('Koneksi database gagal: ' . $link->connect_error);
        }
        if (!$link->set_charset('utf8mb4')) {
            throw new RuntimeException('Charset database tidak dapat diset: ' . $link->error);
        }
        return $link;
    }

    private function query(mysqli $server, $sql)
    {
        if ($server->query($sql) === FALSE) {
            throw new RuntimeException($server->error);
        }
    }
}