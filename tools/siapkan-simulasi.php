<?php
/**
 * Siapkan SIMULASI NYATA ujian (SEB) yang bisa dicoba di laptop sendiri.
 *
 * Yang dibuat (tetap tersimpan di database, tidak dihapus otomatis):
 *   - mapel "Simulasi SEB" + kelas "XII SIMULASI SEB"
 *   - 1 akun siswa: simulasi.seb
 *   - bank soal "Simulasi SEB - 5 Soal Mudah" (5 pilihan ganda, kunci A/B/C/D)
 *   - jadwal ujian hari ini, 30 menit, SEB mode REQUIRED
 *   - berkas .seb yang Config Key-nya sudah dipasang di jadwal
 *
 *   php tools/siapkan-simulasi.php
 *   php tools/siapkan-simulasi.php --base=https://domain-anda/ --out=SEB.seb
 *   php tools/hapus-simulasi.php     (bersihkan lagi)
 */

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
mysqli_report(MYSQLI_REPORT_OFF);

$root = dirname(__DIR__);
$base = 'https://cbtmadah.bhydev.my.id/';
$out = $root . DIRECTORY_SEPARATOR . 'SEB-Simulasi-CBT.seb';
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--base=') === 0) {
        $base = rtrim(substr($arg, 7), '/') . '/';
    } elseif (strpos($arg, '--out=') === 0) {
        $out = substr($arg, 6);
        if (!preg_match('#^([a-zA-Z]:[\\\\/]|/)#', $out)) {
            $out = $root . DIRECTORY_SEPARATOR . $out;
        }
    }
}

require __DIR__ . '/buat-seb.php';
require __DIR__ . '/hapus-simulasi.php';

$db = array();
include $root . '/application/config/database.php';
$cfg = $db['default'];
$dbh = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($dbh->connect_errno) {
    fwrite(STDERR, "Gagal koneksi database: {$dbh->connect_error}\n");
    exit(1);
}
$dbh->set_charset('utf8mb4');

function q(mysqli $dbh, string $sql, array $params = array())
{
    $stmt = $dbh->prepare($sql);
    if (!$stmt) {
        throw new RuntimeException('Query gagal: ' . $dbh->error . ' | ' . $sql);
    }
    if ($params) {
        $types = '';
        foreach ($params as $value) {
            $types .= is_int($value) ? 'i' : 's';
        }
        $stmt->bind_param($types, ...$params);
    }
    if (!$stmt->execute()) {
        throw new RuntimeException('Eksekusi gagal: ' . $stmt->error);
    }
    return $stmt;
}

$tp = $dbh->query("SELECT * FROM master_tp WHERE active=1 LIMIT 1")->fetch_assoc();
$smt = $dbh->query("SELECT * FROM master_smt WHERE active=1 LIMIT 1")->fetch_assoc();
$level = $dbh->query("SELECT id_level FROM level_kelas ORDER BY id_level LIMIT 1")->fetch_assoc();
$guru = $dbh->query("SELECT id_guru FROM master_guru LIMIT 1")->fetch_assoc();
$tokenRow = $dbh->query("SELECT token FROM cbt_token LIMIT 1")->fetch_assoc();
if (!$tp || !$smt || !$level) {
    fwrite(STDERR, "Data master (tahun/semester/level) belum lengkap.\n");
    exit(1);
}

$host = (string) parse_url($base, PHP_URL_HOST);
$sim = array(
    'username' => 'simulasi.seb',
    'password' => 'Simulasi#2026',
    'nama' => 'SISWA SIMULASI SEB',
    'nisn' => 'SIMSEB001',
    'nis' => 'SIM-001',
    'bank' => 'Simulasi SEB - 5 Soal Mudah',
    'mapel_kode' => 'SIM-SEB',
    'kelas' => 'XII SIMULASI SEB',
    'token' => $tokenRow ? (string) $tokenRow['token'] : '',
);

/* 1. Berkas .seb lebih dulu: Config Key-nya yang dipasang di jadwal. */
$seb = buat_seb_config(
    array(
        'startURL' => $base . 'login-siswa',
        'host' => $host,
        'filter' => false, /* Simulasi: tanpa URL filter agar tidak ada situs lokal terblokir. */
    ),
    $out
);

/* 2. Bersihkan sisa simulasi sebelumnya, lalu isi ulang. */
hapus_simulasi($dbh);

$today = date('Y-m-d');
try {
    q($dbh, "INSERT INTO master_mapel (nama_mapel, kode, urutan, kelompok_ujian) VALUES ('Simulasi SEB', ?, 999, 'NON-EKSATA')",
        array($sim['mapel_kode']));
    $mapelId = (int) $dbh->insert_id;

    q($dbh, "INSERT INTO master_kelas (id_tp, id_smt, nama_kelas, kode_kelas, jurusan_id, level_id) VALUES (?, ?, ?, 'SIMSEB', 0, ?)",
        array((int) $tp['id_tp'], (int) $smt['id_smt'], $sim['kelas'], (int) $level['id_level']));
    $kelasId = (int) $dbh->insert_id;

    q($dbh, "INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, nik, warga_negara, uid) VALUES (?, ?, ?, 'L', ?, ?, ?, 'NIK-SIMSEB', 'WNI', 'UID-SIMSEB')",
        array($sim['nisn'], $sim['nis'], $sim['nama'], $sim['username'], $sim['password'], $kelasId));
    $siswaId = (int) $dbh->insert_id;

    $kelasSiswaId = (int) $dbh->query("SELECT COALESCE(MAX(id_kelas_siswa),0)+1 FROM kelas_siswa")->fetch_row()[0];
    q($dbh, "INSERT INTO kelas_siswa (id_kelas_siswa, id_tp, id_smt, id_siswa, id_kelas) VALUES (?, ?, ?, ?, ?)",
        array($kelasSiswaId, (int) $tp['id_tp'], (int) $smt['id_smt'], $siswaId, $kelasId));

    q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active) VALUES ('127.0.0.1', ?, ?, ?, ?, 1)",
        array($sim['username'], password_hash($sim['password'], PASSWORD_BCRYPT), $sim['username'] . '@simulasi.local', time()));
    $userId = (int) $dbh->insert_id;
    q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 3)", array($userId));

    $bankKelas = serialize(array(array('kelas_id' => $kelasId)));
    q($dbh, "INSERT INTO cbt_bank_soal (bank_jenis_id, bank_kode, bank_level, bank_kelas, bank_mapel_id, bank_jurusan_id, bank_guru_id, bank_nama, kkm, jml_soal, jml_kompleks, jml_esai, tampil_pg, tampil_kompleks, tampil_bs, tampil_esai, tampil_jodohkan, tampil_isian, bobot_pg, bobot_kompleks, bobot_bs, bobot_esai, bobot_jodohkan, bobot_isian, opsi, status, status_soal, id_tp, id_smt, deskripsi) VALUES (1, 'SIMSEB', 'SMK', ?, ?, 0, ?, ?, 75, 5, 0, 0, 5, 0, 0, 0, 0, 0, 100, 0, 0, 0, 0, 0, 4, 1, 1, ?, ?, 'Simulasi ujian dengan Safe Exam Browser')",
        array($bankKelas, $mapelId, $guru ? (int) $guru['id_guru'] : 0, $sim['bank'], (int) $tp['id_tp'], (int) $smt['id_smt']));
    $bankId = (int) $dbh->insert_id;

    $soal = array(
        array('2 + 3 = ...', array('4', '5', '6', '7'), 'B'),
        array('Ibu kota Indonesia adalah ...', array('Bandung', 'Surabaya', 'Jakarta', 'Medan'), 'C'),
        array('10 - 4 = ...', array('5', '6', '7', '8'), 'B'),
        array('Warna daun yang sehat umumnya ...', array('Biru', 'Merah', 'Hijau', 'Hitam'), 'C'),
        array('3 x 4 = ...', array('12', '9', '7', '15'), 'A'),
    );
    foreach ($soal as $i => $item) {
        q($dbh, "INSERT INTO cbt_soal (bank_id, mapel_id, jenis, nomor_soal, soal, deskripsi, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan, kesulitan, timer, timer_menit, created_on) VALUES (?, ?, 1, ?, ?, '', ?, ?, ?, ?, '', ?, 1, 1, 0, 0, ?)",
            array($bankId, $mapelId, $i + 1, $item[0], $item[1][0], $item[1][1], $item[1][2], $item[1][3], $item[2], time()));
    }

    q($dbh, "INSERT INTO cbt_jadwal (id_tp, id_smt, id_bank, id_jenis, tgl_mulai, tgl_selesai, durasi_ujian, pengawas, acak_soal, acak_opsi, hasil_tampil, token, status, ulang, reset_login, seb_mode, seb_config_key, rekap, jam_ke, jarak, time_create) VALUES (?, ?, ?, 1, ?, ?, 30, '', 0, 0, 1, 123456, 1, 0, 0, 'REQUIRED', ?, 1, 1, 0, ?)",
        array((int) $tp['id_tp'], (int) $smt['id_smt'], $bankId, $today, $today, $seb['configKey'], date('Y-m-d H:i:s')));
    $jadwalId = (int) $dbh->insert_id;
} catch (Throwable $e) {
    hapus_simulasi($dbh);
    fwrite(STDERR, "Gagal menyiapkan data: " . $e->getMessage() . "\n");
    exit(1);
}

$publicUrl = '';
if (realpath(dirname($out)) === realpath($root)) {
    $publicUrl = $base . rawurlencode(basename($out));
}

echo "=== SIMULASI UJIAN SEB SIAP ===\n\n";
echo "URL login      : {$base}login-siswa\n";
echo "Username       : {$sim['username']}\n";
echo "Password       : {$sim['password']}\n";
echo "Token ujian    : ", ($sim['token'] !== '' ? $sim['token'] : '(tidak ada token di cbt_token)'), "\n";
echo "Jumlah soal    : 5 pilihan ganda (mudah)\n";
echo "Durasi         : 30 menit, jadwal hari ini ({$today})\n";
echo "Mode SEB       : REQUIRED\n";
echo "Berkas .seb    : {$out} ({$seb['bytes']} byte)\n";
if ($publicUrl !== '') {
    echo "Unduh .seb     : {$publicUrl}\n";
}
echo "Config Key     : {$seb['configKey']}\n";
echo "id_jadwal      : {$jadwalId}\n\n";
echo "Langkah coba di laptop:\n";
echo "  1. Unduh berkas .seb di atas, lalu buka (double-click) dengan Safe Exam Browser.\n";
echo "  2. Login memakai username/password di atas di dalam jendela SEB.\n";
echo "  3. Buka menu ujian, pilih jadwal 'Simulasi SEB', masukkan token ujian.\n";
echo "  4. Kerjakan 5 soal, lalu kumpulkan.\n\n";
echo "Catatan:\n";
echo "  - Config Key di atas sudah otomatis dipasang di jadwal; tidak perlu isi manual.\n";
echo "  - Bila SEB menampilkan 'belum terverifikasi', buka SEB Config Tool, muat .seb ini,\n";
echo "    salin nilai 'Config Key', lalu tempel ke form jadwal (kolom SEB Config Key).\n";
echo "  - Hapus data simulasi: php tools/hapus-simulasi.php\n";
