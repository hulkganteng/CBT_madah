<?php
/**
 * Hapus data simulasi SEB yang dibuat oleh tools/siapkan-simulasi.php.
 *
 *   php tools/hapus-simulasi.php
 *
 * Aman: hanya menghapus baris dengan penanda tetap (username simulasi.seb,
 * bank "Simulasi SEB ...", mapel SIM-SEB, kelas XII SIMULASI SEB).
 */

if (!function_exists('hapus_simulasi')) {
    /**
     * @return array<string,int> jumlah baris yang dihapus per tabel
     */
    function hapus_simulasi(mysqli $dbh)
    {
        $counts = array();
        $affected = function ($result) use (&$counts, $dbh) {
            return $dbh->affected_rows;
        };

        $bankIds = array();
        $res = $dbh->query("SELECT id_bank FROM cbt_bank_soal WHERE bank_nama LIKE 'Simulasi SEB%'");
        while ($row = $res->fetch_assoc()) {
            $bankIds[] = (int) $row['id_bank'];
        }

        $jadwalIds = array();
        if ($bankIds) {
            $in = implode(',', $bankIds);
            $res = $dbh->query("SELECT id_jadwal FROM cbt_jadwal WHERE id_bank IN ($in)");
            while ($row = $res->fetch_assoc()) {
                $jadwalIds[] = (int) $row['id_jadwal'];
            }
        }

        if ($jadwalIds) {
            $in = implode(',', $jadwalIds);
            $dbh->query("DELETE FROM cbt_exam_event WHERE attempt_id IN (SELECT attempt_id FROM cbt_exam_attempt WHERE id_jadwal IN ($in))");
            foreach (array('cbt_nilai', 'cbt_exam_attempt', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian') as $table) {
                $dbh->query("DELETE FROM `$table` WHERE id_jadwal IN ($in)");
                $counts[$table] = ($counts[$table] ?? 0) + $dbh->affected_rows;
            }
            $dbh->query("DELETE FROM cbt_jadwal WHERE id_jadwal IN ($in)");
            $counts['cbt_jadwal'] = ($counts['cbt_jadwal'] ?? 0) + $dbh->affected_rows;
        }

        if ($bankIds) {
            $in = implode(',', $bankIds);
            $dbh->query("DELETE FROM cbt_soal WHERE bank_id IN ($in)");
            $counts['cbt_soal'] = $dbh->affected_rows;
            $dbh->query("DELETE FROM cbt_bank_soal WHERE id_bank IN ($in)");
            $counts['cbt_bank_soal'] = $dbh->affected_rows;
        }

        $siswaIds = array();
        $res = $dbh->query("SELECT id_siswa FROM master_siswa WHERE username='simulasi.seb'");
        while ($row = $res->fetch_assoc()) {
            $siswaIds[] = (int) $row['id_siswa'];
        }
        if ($siswaIds) {
            $in = implode(',', $siswaIds);
            foreach (array('cbt_nilai', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian', 'kelas_siswa') as $table) {
                $dbh->query("DELETE FROM `$table` WHERE id_siswa IN ($in)");
                $counts[$table] = ($counts[$table] ?? 0) + $dbh->affected_rows;
            }
            $dbh->query("DELETE FROM master_siswa WHERE id_siswa IN ($in)");
            $counts['master_siswa'] = $dbh->affected_rows;
        }

        $userIds = array();
        $res = $dbh->query("SELECT id FROM users WHERE username='simulasi.seb'");
        while ($row = $res->fetch_assoc()) {
            $userIds[] = (int) $row['id'];
        }
        if ($userIds) {
            $in = implode(',', $userIds);
            $dbh->query("DELETE FROM users_groups WHERE user_id IN ($in)");
            $counts['users_groups'] = $dbh->affected_rows;
            $dbh->query("DELETE FROM users_profile WHERE id_user IN ($in)");
            $dbh->query("DELETE FROM users WHERE id IN ($in)");
            $counts['users'] = $dbh->affected_rows;
        }

        $dbh->query("DELETE FROM master_kelas WHERE nama_kelas='XII SIMULASI SEB' OR kode_kelas='SIMSEB'");
        $counts['master_kelas'] = $dbh->affected_rows;
        $dbh->query("DELETE FROM master_mapel WHERE kode='SIM-SEB'");
        $counts['master_mapel'] = $dbh->affected_rows;

        return $counts;
    }
}

if (PHP_SAPI === 'cli' && isset($argv[0]) && realpath($argv[0]) === realpath(__FILE__)) {
    define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
    define('ENVIRONMENT', 'development');
    mysqli_report(MYSQLI_REPORT_OFF);

    $db = array();
    include dirname(__DIR__) . '/application/config/database.php';
    $cfg = $db['default'];
    $dbh = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
    if ($dbh->connect_errno) {
        fwrite(STDERR, "Gagal koneksi database: {$dbh->connect_error}\n");
        exit(1);
    }

    $counts = hapus_simulasi($dbh);
    $total = array_sum($counts);
    foreach ($counts as $table => $n) {
        echo str_pad($table, 20), $n, "\n";
    }
    echo $total > 0 ? "Data simulasi dihapus ($total baris).\n" : "Tidak ada data simulasi.\n";
}
