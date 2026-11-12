<?php
/**
 * Smoke test simulasi ujian TANPA SEB (jadwal.seb_mode = OFF).
 *
 * Alur yang diuji lewat HTTP sungguhan:
 *   login siswa -> konfirmasi -> validasisiswa -> penilaian ->
 *   loadnomorsoal -> savejawaban -> selesaiujian -> cbt_nilai + attempt.
 *
 * Data uji memakai penanda SMOKE dan dibersihkan lagi di akhir kecuali --keep.
 *
 *   php tools/smoke-simulasi.php
 *   php tools/smoke-simulasi.php --base=http://localhost/garudacbt/ --keep
 */

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
mysqli_report(MYSQLI_REPORT_OFF);

$base = 'http://localhost/garudacbt/';
$keep = false;
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--base=') === 0) {
        $base = rtrim(substr($arg, 7), '/') . '/';
    } elseif ($arg === '--keep') {
        $keep = true;
    }
}

$db = array();
include dirname(__DIR__) . '/application/config/database.php';
$cfg = $db['default'];
$dbh = new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($dbh->connect_errno) {
    fwrite(STDERR, "Gagal koneksi database: {$dbh->connect_error}\n");
    exit(1);
}
$dbh->set_charset('utf8mb4');

$failures = 0;
function step(string $name, bool $ok, string $detail = ''): void
{
    global $failures;
    if (!$ok) {
        $failures++;
    }
    echo ($ok ? 'PASS ' : 'FAIL '), $name, ($detail !== '' ? " :: $detail" : ''), PHP_EOL;
}

function q(mysqli $dbh, string $sql, array $params = array()): mysqli_stmt
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

function scalar(mysqli $dbh, string $sql, array $params = array())
{
    $stmt = q($dbh, $sql, $params);
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return $value;
}

$jar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smoke-simulasi.cookies';
@unlink($jar);

/* Login peserta kini wajib dari Safe Exam Browser: pakai UA SEB + Config Key
   hash milik jadwal SEB yang tersimpan (bila ada). */
$sebKey = (string) scalar($dbh, "SELECT seb_config_key FROM cbt_jadwal WHERE seb_config_key IS NOT NULL AND seb_config_key <> '' ORDER BY id_jadwal DESC LIMIT 1");

/* Aplikasi memakai CSRF token cookie (config: csrf_token=csrf_token,
   csrf_cookie=csrf_cookie, regenerate=FALSE). Ambil nilainya dari cookie
   jar lalu kirim ulang sebagai field pada setiap POST. */
function csrf_token(): string
{
    global $jar;
    if (!is_file($jar)) {
        return '';
    }
    foreach (file($jar) as $line) {
        if (strpos($line, "\tcsrf_cookie\t") === false && preg_match('/\tcsrf_cookie\t/', $line) !== 1) {
            continue;
        }
        $parts = explode("\t", rtrim($line, "\r\n"));
        if (count($parts) >= 7) {
            return urldecode($parts[6]);
        }
    }
    return '';
}

function http(string $url, ?array $post = null, bool $follow = true): array
{
    global $jar, $sebKey;
    $headers = array('X-Requested-With: XMLHttpRequest');
    if ($sebKey !== '') {
        $headers[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . hash('sha256', $url . $sebKey);
    }
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Mobile Safari/537.36 SEB/3.7.0',
        CURLOPT_HTTPHEADER => $headers,
    ));
    if ($post !== null) {
        $token = csrf_token();
        if ($token !== '') {
            $post['csrf_token'] = $token;
        }
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($curl);
    $code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    return array($code, $body, $error);
}

function json_of(array $response)
{
    $decoded = json_decode($response[1], true);
    return is_array($decoded) ? $decoded : null;
}

/* ---------------------------------------------------------------- preflight */
$tp = $dbh->query("SELECT * FROM master_tp WHERE active=1 LIMIT 1")->fetch_assoc();
$smt = $dbh->query("SELECT * FROM master_smt WHERE active=1 LIMIT 1")->fetch_assoc();
$guru = $dbh->query("SELECT id_guru FROM master_guru LIMIT 1")->fetch_assoc();
$level = $dbh->query("SELECT id_level FROM level_kelas LIMIT 1")->fetch_assoc();
step('tahun pelajaran aktif', (bool) $tp, $tp ? $tp['tahun'] : '');
step('semester aktif', (bool) $smt, $smt ? $smt['smt'] : '');
step('data guru tersedia', (bool) $guru);
step('level kelas tersedia', (bool) $level);
if (!$tp || !$smt || !$guru || !$level) {
    fwrite(STDERR, "Prasyarat data master belum lengkap.\n");
    exit(1);
}

/* --------------------------------------------------------------------- seed */
$tag = 'SMOKE' . substr((string) time(), -5);
$username = 'smoke_' . strtolower($tag);
$password = 'Smoke#' . $tag;

$cleanup = array();

function wipe(array $ids): void
{
    global $dbh;
    $jadwal = (int) ($ids['cbt_jadwal'][0] ?? 0);
    $bank = (int) ($ids['cbt_bank_soal'][0] ?? 0);
    $siswa = (int) ($ids['master_siswa'][0] ?? 0);
    $kelas = (int) ($ids['master_kelas'][0] ?? 0);
    $mapel = (int) ($ids['master_mapel'][0] ?? 0);
    $user = (int) ($ids['users'][0] ?? 0);

    if ($jadwal > 0) {
        $attempts = array();
        $res = $dbh->query("SELECT attempt_id FROM cbt_exam_attempt WHERE id_jadwal=$jadwal");
        while ($row = $res->fetch_assoc()) {
            $attempts[] = (int) $row['attempt_id'];
        }
        if ($attempts) {
            $in = implode(',', $attempts);
            $dbh->query("DELETE FROM cbt_exam_event WHERE attempt_id IN ($in)");
        }
        foreach (array('cbt_nilai', 'cbt_exam_attempt', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian') as $table) {
            $dbh->query("DELETE FROM `$table` WHERE id_jadwal=$jadwal");
        }
        $dbh->query("DELETE FROM cbt_jadwal WHERE id_jadwal=$jadwal");
    }
    if ($bank > 0) {
        $dbh->query("DELETE FROM cbt_soal WHERE bank_id=$bank");
        $dbh->query("DELETE FROM cbt_bank_soal WHERE id_bank=$bank");
    }
    if ($siswa > 0) {
        foreach (array('cbt_nilai', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian', 'kelas_siswa') as $table) {
            $dbh->query("DELETE FROM `$table` WHERE id_siswa=$siswa");
        }
        $dbh->query("DELETE FROM master_siswa WHERE id_siswa=$siswa");
    }
    if ($user > 0) {
        $dbh->query("DELETE FROM users_groups WHERE user_id=$user");
        $dbh->query("DELETE FROM users_profile WHERE id_user=$user");
        $dbh->query("DELETE FROM users WHERE id=$user");
    }
    if ($kelas > 0) {
        $dbh->query("DELETE FROM kelas_siswa WHERE id_kelas=$kelas");
        $dbh->query("DELETE FROM master_kelas WHERE id_kelas=$kelas");
    }
    if ($mapel > 0) {
        $dbh->query("DELETE FROM master_mapel WHERE id_mapel=$mapel");
    }
}

$ids = array();
// Buat mapel sendiri agar tidak mengubah data yang ada.
q($dbh, "INSERT INTO master_mapel (nama_mapel, kode, urutan, kelompok_ujian) VALUES (?, ?, 999, 'NON-EKSATA')",
    array('SMOKE MAPEL', 'SMK' . $tag));
$mapelId = (int) $dbh->insert_id;
$ids['master_mapel'] = array($mapelId);

q($dbh, "INSERT INTO master_kelas (id_tp, id_smt, nama_kelas, kode_kelas, jurusan_id, level_id) VALUES (?, ?, ?, ?, 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], 'SMOKE ' . $tag, 'SMK', (int) $level['id_level']));
$kelasId = (int) $dbh->insert_id;
$ids['master_kelas'] = array($kelasId);

q($dbh, "INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, nik, warga_negara, uid) VALUES (?, ?, ?, 'L', ?, ?, ?, ?, 'WNI', ?)",
    array($tag, 'N' . $tag, 'SMOKE SISWA', $username, $password, $kelasId, 'NIK' . $tag, 'UID' . $tag));
$siswaId = (int) $dbh->insert_id;
$ids['master_siswa'] = array($siswaId);

$kelasSiswaId = (int) scalar($dbh, "SELECT COALESCE(MAX(id_kelas_siswa),0)+1 FROM kelas_siswa");
q($dbh, "INSERT INTO kelas_siswa (id_kelas_siswa, id_tp, id_smt, id_siswa, id_kelas) VALUES (?, ?, ?, ?, ?)",
    array($kelasSiswaId, (int) $tp['id_tp'], (int) $smt['id_smt'], $siswaId, $kelasId));
$ids['kelas_siswa'] = array($kelasSiswaId);

$hash = password_hash($password, PASSWORD_BCRYPT);
q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active) VALUES ('127.0.0.1', ?, ?, ?, ?, 1)",
    array($username, $hash, $username . '@smoke.test', time()));
$userId = (int) $dbh->insert_id;
$ids['users'] = array($userId);
q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 3)", array($userId));
$ids['users_groups'] = array((int) $dbh->insert_id);

$bankKelas = serialize(array(array('kelas_id' => $kelasId)));
q($dbh, "INSERT INTO cbt_bank_soal (bank_jenis_id, bank_kode, bank_level, bank_kelas, bank_mapel_id, bank_jurusan_id, bank_guru_id, bank_nama, jml_soal, jml_kompleks, jml_esai, tampil_pg, tampil_kompleks, tampil_bs, tampil_esai, tampil_jodohkan, tampil_isian, bobot_pg, bobot_kompleks, bobot_bs, opsi, status, status_soal, id_tp, id_smt) VALUES (1, ?, 'SMK', ?, ?, 0, ?, ?, 3, 0, 0, 3, 0, 0, 0, 0, 0, 100, 0, 0, 4, 1, 1, ?, ?)",
    array('B' . $tag, $bankKelas, $mapelId, (int) $guru['id_guru'], 'SMOKE BANK ' . $tag, (int) $tp['id_tp'], (int) $smt['id_smt']));
$bankId = (int) $dbh->insert_id;
$ids['cbt_bank_soal'] = array($bankId);

$ids['cbt_soal'] = array();
for ($nomor = 1; $nomor <= 3; $nomor++) {
    q($dbh, "INSERT INTO cbt_soal (bank_id, jenis, nomor_soal, soal, deskripsi, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan, kesulitan) VALUES (?, 1, ?, ?, '', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'A', '1', '8')",
        array($bankId, $nomor, 'SMOKE SOAL PG ' . $nomor));
    $ids['cbt_soal'][] = (int) $dbh->insert_id;
}

$today = date('Y-m-d');
q($dbh, "INSERT INTO cbt_jadwal (id_tp, id_smt, id_bank, id_jenis, tgl_mulai, tgl_selesai, durasi_ujian, pengawas, acak_soal, acak_opsi, hasil_tampil, token, status, ulang, reset_login, seb_mode, rekap, jam_ke, jarak, time_create) VALUES (?, ?, ?, 1, ?, ?, 30, '', 0, 0, 1, 0, 1, 0, 0, 'OFF', 1, 1, 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], $bankId, $today, $today, date('Y-m-d H:i:s')));
$jadwalId = (int) $dbh->insert_id;
$ids['cbt_jadwal'] = array($jadwalId);

step('seed data uji', $bankId > 0 && $jadwalId > 0, "bank=$bankId jadwal=$jadwalId siswa=$siswaId");
step('mode SEB jadwal', scalar($dbh, "SELECT seb_mode FROM cbt_jadwal WHERE id_jadwal=?", array($jadwalId)) === 'OFF', 'OFF');

/* ---------------------------------------------------------------------- run */
try {
    list($code) = http($base . 'auth');
    step('halaman login dapat diakses', $code === 200, "HTTP $code");

    list($code, $body) = http($base . 'auth/cek_login', array(
        'identity' => $username,
        'password' => $password,
        'remember' => '0',
    ), false);
    $login = json_decode($body, true);
    step('login siswa', $code === 200 && is_array($login) && !empty($login['status']), 'HTTP ' . $code . ' ' . substr($body, 0, 120));

    list($code, $body) = http($base . 'dashboard');
    step('dashboard siswa terbuka', $code === 200, "HTTP $code");

    list($code, $body) = http($base . 'siswa/konfirmasi/' . $jadwalId);
    step('halaman konfirmasi ujian', $code === 200 && stripos($body, 'token') !== false, "HTTP $code");

    list($code, $body) = http($base . 'siswa/validasisiswa', array(
        'siswa' => $siswaId,
        'bank' => $bankId,
        'jadwal' => $jadwalId,
        'token' => '',
    ));
    $valid = json_of(array($code, $body));
    step('token/validasi peserta', $code === 200 && $valid !== null && !empty($valid['token']) && !empty($valid['izinkan']),
        'HTTP ' . $code . ' ' . substr($body, 0, 160));
    step('kuota soal terjadwal', !empty($valid['jml_soal']), 'jml_soal=' . (string) ($valid['jml_soal'] ?? '-'));

    list($code, $body) = http($base . 'siswa/penilaian/' . $jadwalId);
    step('halaman ujian terbuka', $code === 200 && strpos($body, 'loadnomorsoal') !== false, "HTTP $code");

    $soalSiswa = array();
    for ($nomor = 1; $nomor <= 3; $nomor++) {
        list($code, $body) = http($base . 'siswa/loadnomorsoal', array(
            'siswa' => $siswaId,
            'bank' => $bankId,
            'jadwal' => $jadwalId,
            'nomor' => $nomor,
            'timer' => '00:00:05',
            'elapsed' => '00:00:05',
        ));
        $soal = json_of(array($code, $body));
        $ok = $code === 200 && $soal !== null && !empty($soal['soal_siswa_id']);
        step('muat soal nomor ' . $nomor, $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 120));
        if (!$ok) {
            break;
        }
        $soalSiswa[$nomor] = $soal;
    }

    $saved = 0;
    foreach ($soalSiswa as $nomor => $soal) {
        $jawaban = array(
            'no_soal_alias' => $nomor,
            'jawaban_alias' => 'A',
            'jawaban_siswa' => 'A',
            'jenis' => 1,
            'id_soal' => (int) $soal['soal_id'],
            'id_soal_siswa' => (string) $soal['soal_siswa_id'],
            'id_jadwal' => $jadwalId,
            'id_bank' => $bankId,
            'id_siswa' => $siswaId,
        );
        list($code, $body) = http($base . 'siswa/savejawaban', array(
            'siswa' => $siswaId,
            'bank' => $bankId,
            'jadwal' => $jadwalId,
            'timer' => '00:00:10',
            'elapsed' => '00:00:10',
            'data' => json_encode($jawaban),
        ));
        $res = json_of(array($code, $body));
        $ok = $code === 200 && $res !== null && isset($res['status']) && $res['status'] !== false;
        step('simpan jawaban nomor ' . $nomor, $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 120));
        if ($ok) {
            $saved++;
        }
    }

    list($code, $body) = http($base . 'siswa/selesaiujian', array(
        'siswa' => $siswaId,
        'bank' => $bankId,
        'jadwal' => $jadwalId,
    ));
    $finish = json_of(array($code, $body));
    step('kumpulkan ujian', $code === 200 && $finish !== null && !empty($finish['status']),
        'HTTP ' . $code . ' ' . substr($body, 0, 160));

    $durasi = $dbh->query("SELECT status, selesai FROM cbt_durasi_siswa WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('durasi ujian ditutup', $durasi && (string) $durasi['status'] === '2' && !empty($durasi['selesai']),
        $durasi ? 'status=' . $durasi['status'] . ' selesai=' . (string) $durasi['selesai'] : 'baris tidak ada');

    $nilai = $dbh->query("SELECT pg_benar, pg_nilai FROM cbt_nilai WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('nilai tersimpan', $nilai !== null && (int) $nilai['pg_benar'] === $saved,
        $nilai ? 'benar=' . $nilai['pg_benar'] . ' nilai=' . $nilai['pg_nilai'] : 'baris tidak ada');

    $attempt = $dbh->query("SELECT status, seb_verified FROM cbt_exam_attempt WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    step('attempt ujian dikumpulkan', $attempt !== null && strtoupper((string) $attempt['status']) === 'SUBMITTED',
        $attempt ? 'status=' . $attempt['status'] : 'attempt tidak ada');
    step('tanpa SEB tidak dianggap terverifikasi', $attempt !== null && (int) $attempt['seb_verified'] === 0,
        $attempt ? 'seb_verified=' . $attempt['seb_verified'] : '-');

    $jawabanTersimpan = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_soal_siswa WHERE id_siswa=? AND id_jadwal=? AND jawaban_siswa IS NOT NULL AND jawaban_siswa<>''",
        array($siswaId, $jadwalId));
    step('jawaban tersimpan di bank peserta', $jawabanTersimpan === $saved, "tersimpan=$jawabanTersimpan dari $saved");
} finally {
    if ($keep) {
        echo "Data uji dipertahankan: username=$username password=$password jadwal=$jadwalId\n";
    } else {
        wipe($ids);
        echo "Data uji dibersihkan.\n";
    }
    @unlink($jar);
}

echo $failures === 0 ? "SMOKE SIMULASI TANPA SEB: OK\n" : "SMOKE SIMULASI TANPA SEB: $failures GAGAL\n";
exit($failures === 0 ? 0 : 1);
