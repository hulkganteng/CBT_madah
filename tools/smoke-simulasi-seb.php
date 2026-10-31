<?php
/**
 * Smoke test simulasi ujian SATU peserta DENGAN Safe Exam Browser (SEB).
 * Default target: domain aktif https://cbtmadah.bhydev.my.id/
 *
 * Yang diuji:
 *   - jadwal seb_mode=REQUIRED menolak permintaan tanpa ConfigKeyHash
 *   - permintaan dengan hash salah ditolak
 *   - alur lengkap dengan header SEB (sha256(url + config key)) berjalan:
 *     konfirmasi -> validasisiswa -> penilaian -> loadnomorsoal ->
 *     savejawaban -> selesaiujian
 *   - attempt tercatat seb_verified=1 dan berstatus SUBMITTED
 *
 *   php tools/smoke-simulasi-seb.php
 *   php tools/smoke-simulasi-seb.php --base=http://localhost/garudacbt/ --keep
 */

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
mysqli_report(MYSQLI_REPORT_OFF);

$base = 'https://cbtmadah.bhydev.my.id/';
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

function scalar(mysqli $dbh, string $sql, array $params = array())
{
    $stmt = q($dbh, $sql, $params);
    $stmt->bind_result($value);
    $stmt->fetch();
    $stmt->close();
    return $value;
}

/* --------------------------------------------------------------- HTTP helper */
/* Header SEB harus sha256(url + config key) untuk URL yang sedang dibuka,
   persis seperti yang dikirim Safe Exam Browser. */
$sebKey = '';
$UA_SEB = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
    . 'Chrome/126.0.0.0 Safari/537.36 SEB/3.7.0';

function csrf_from_jar(string $jar): string
{
    if (!is_file($jar)) {
        return '';
    }
    foreach (file($jar) as $line) {
        if (preg_match('/\tcsrf_cookie\t/', $line) !== 1) {
            continue;
        }
        $parts = explode("\t", rtrim($line, "\r\n"));
        if (count($parts) >= 7) {
            return urldecode($parts[6]);
        }
    }
    return '';
}

/**
 * @param string|null $seb 'valid'|'salah'|null
 */
function http(string $url, ?array $post = null, ?string $seb = 'valid', bool $follow = true, ?string $jar = null): array
{
    global $sebKey, $UA_SEB;
    $jar = $jar ?: $GLOBALS['jar'];
    $headers = array('X-Requested-With: XMLHttpRequest');
    if ($seb === 'valid') {
        $headers[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . hash('sha256', $url . $sebKey);
    } elseif ($seb === 'salah') {
        $headers[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . str_repeat('0', 64);
    }
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => $follow,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => $UA_SEB,
        CURLOPT_HTTPHEADER => $headers,
    ));
    if ($post !== null) {
        $token = csrf_from_jar($jar);
        if ($token !== '') {
            $post['csrf_token'] = $token;
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    $decoded = json_decode($body, true);
    return array($code, $body, $error, is_array($decoded) ? $decoded : null);
}

/* ---------------------------------------------------------------- preflight */
$tp = $dbh->query("SELECT * FROM master_tp WHERE active=1 LIMIT 1")->fetch_assoc();
$smt = $dbh->query("SELECT * FROM master_smt WHERE active=1 LIMIT 1")->fetch_assoc();
$guru = $dbh->query("SELECT id_guru FROM master_guru LIMIT 1")->fetch_assoc();
$level = $dbh->query("SELECT id_level FROM level_kelas LIMIT 1")->fetch_assoc();
step('prasyarat data master', (bool) ($tp && $smt && $guru && $level),
    ($tp ? $tp['tahun'] : '-') . ' / ' . ($smt ? $smt['smt'] : '-'));
if (!$tp || !$smt || !$guru || !$level) {
    exit(1);
}

list($code, $body, $err) = http($base . 'auth', null, null, true, $GLOBALS['jar'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smoke-seb-probe.cookies');
$tlsOk = $code === 200;
step('domain uji dapat diakses', $tlsOk, 'HTTP ' . $code . ($err !== '' ? ' (' . $err . ')' : ''));

/* --------------------------------------------------------------------- seed */
$tag = 'SEB' . substr((string) time(), -5);
$username = 'seb_' . strtolower($tag);
$password = 'Smoke#' . $tag;
$sebKey = bin2hex(random_bytes(32));
$today = date('Y-m-d');
$ids = array();

q($dbh, "INSERT INTO master_mapel (nama_mapel, kode, urutan, kelompok_ujian) VALUES ('SMOKE SEB MAPEL', ?, 999, 'NON-EKSATA')",
    array('SM' . $tag));
$mapelId = (int) $dbh->insert_id;
$ids['master_mapel'] = $mapelId;

q($dbh, "INSERT INTO master_kelas (id_tp, id_smt, nama_kelas, kode_kelas, jurusan_id, level_id) VALUES (?, ?, ?, 'SMB', 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], 'SMOKE SEB ' . $tag, (int) $level['id_level']));
$kelasId = (int) $dbh->insert_id;
$ids['master_kelas'] = $kelasId;

q($dbh, "INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, nik, warga_negara, uid) VALUES (?, ?, 'SMOKE SEB SISWA', 'L', ?, ?, ?, ?, 'WNI', ?)",
    array($tag, 'N' . $tag, $username, $password, $kelasId, 'NIK' . $tag, 'UID' . $tag));
$siswaId = (int) $dbh->insert_id;
$ids['master_siswa'] = $siswaId;

$kelasSiswaId = (int) scalar($dbh, "SELECT COALESCE(MAX(id_kelas_siswa),0)+1 FROM kelas_siswa");
q($dbh, "INSERT INTO kelas_siswa (id_kelas_siswa, id_tp, id_smt, id_siswa, id_kelas) VALUES (?, ?, ?, ?, ?)",
    array($kelasSiswaId, (int) $tp['id_tp'], (int) $smt['id_smt'], $siswaId, $kelasId));
$ids['kelas_siswa'] = $kelasSiswaId;

$hash = password_hash($password, PASSWORD_BCRYPT);
q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active) VALUES ('127.0.0.1', ?, ?, ?, ?, 1)",
    array($username, $hash, $username . '@smoke.test', time()));
$userId = (int) $dbh->insert_id;
$ids['users'] = $userId;
q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 3)", array($userId));
$ids['users_groups'] = (int) $dbh->insert_id;

$bankKelas = serialize(array(array('kelas_id' => $kelasId)));
q($dbh, "INSERT INTO cbt_bank_soal (bank_jenis_id, bank_kode, bank_level, bank_kelas, bank_mapel_id, bank_jurusan_id, bank_guru_id, bank_nama, jml_soal, jml_kompleks, jml_esai, tampil_pg, tampil_kompleks, tampil_bs, tampil_esai, tampil_jodohkan, tampil_isian, bobot_pg, bobot_kompleks, bobot_bs, opsi, status, status_soal, id_tp, id_smt) VALUES (1, ?, 'SMK', ?, ?, 0, ?, ?, 3, 0, 0, 3, 0, 0, 0, 0, 0, 100, 0, 0, 4, 1, 1, ?, ?)",
    array('S' . $tag, $bankKelas, $mapelId, (int) $guru['id_guru'], 'SMOKE SEB BANK ' . $tag, (int) $tp['id_tp'], (int) $smt['id_smt']));
$bankId = (int) $dbh->insert_id;
$ids['cbt_bank_soal'] = $bankId;

$ids['cbt_soal'] = array();
for ($nomor = 1; $nomor <= 3; $nomor++) {
    q($dbh, "INSERT INTO cbt_soal (bank_id, jenis, nomor_soal, soal, deskripsi, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan, kesulitan) VALUES (?, 1, ?, ?, '', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'A', '1', '8')",
        array($bankId, $nomor, 'SMOKE SEB SOAL ' . $nomor));
    $ids['cbt_soal'][] = (int) $dbh->insert_id;
}

q($dbh, "INSERT INTO cbt_jadwal (id_tp, id_smt, id_bank, id_jenis, tgl_mulai, tgl_selesai, durasi_ujian, pengawas, acak_soal, acak_opsi, hasil_tampil, token, status, ulang, reset_login, seb_mode, seb_config_key, rekap, jam_ke, jarak, time_create) VALUES (?, ?, ?, 1, ?, ?, 30, '', 0, 0, 1, 0, 1, 0, 0, 'REQUIRED', ?, 1, 1, 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], $bankId, $today, $today, $sebKey, date('Y-m-d H:i:s')));
$jadwalId = (int) $dbh->insert_id;
$ids['cbt_jadwal'] = $jadwalId;

step('seed jadwal SEB REQUIRED', $jadwalId > 0,
    "bank=$bankId jadwal=$jadwalId siswa=$siswaId key=" . substr($sebKey, 0, 8) . '...');

function wipe(array $ids): void
{
    global $dbh;
    $jadwal = (int) ($ids['cbt_jadwal'] ?? 0);
    $bank = (int) ($ids['cbt_bank_soal'] ?? 0);
    $siswa = (int) ($ids['master_siswa'] ?? 0);
    $kelas = (int) ($ids['master_kelas'] ?? 0);
    $mapel = (int) ($ids['master_mapel'] ?? 0);
    $user = (int) ($ids['users'] ?? 0);

    if ($jadwal > 0) {
        $attempts = array();
        $res = $dbh->query("SELECT attempt_id FROM cbt_exam_attempt WHERE id_jadwal=$jadwal");
        while ($row = $res->fetch_assoc()) {
            $attempts[] = "'" . $dbh->real_escape_string($row['attempt_id']) . "'";
        }
        if ($attempts) {
            $dbh->query("DELETE FROM cbt_exam_event WHERE attempt_id IN (" . implode(',', $attempts) . ")");
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
        foreach (array('cbt_nilai', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian') as $table) {
            $dbh->query("DELETE FROM `$table` WHERE id_siswa=$siswa");
        }
        $dbh->query("DELETE FROM kelas_siswa WHERE id_siswa=$siswa");
        $dbh->query("DELETE FROM master_siswa WHERE id_siswa=$siswa");
    }
    if ($user > 0) {
        $dbh->query("DELETE FROM users_groups WHERE user_id=$user");
        $dbh->query("DELETE FROM users_profile WHERE id_user=$user");
        $dbh->query("DELETE FROM users WHERE id=$user");
    }
    if ($kelas > 0) {
        $dbh->query("DELETE FROM master_kelas WHERE id_kelas=$kelas");
    }
    if ($mapel > 0) {
        $dbh->query("DELETE FROM master_mapel WHERE id_mapel=$mapel");
    }
}

/* ---------------------------------------------------------------------- run */
$jar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smoke-seb-' . $tag . '.cookies';
$jarNegatif = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smoke-seb-neg-' . $tag . '.cookies';
@unlink($jar);
@unlink($jarNegatif);

try {
    /* --- Sesi A: tanpa SEB dan dengan hash salah, harus ditolak. --- */
    list($code) = http($base . 'auth', null, null, true, $jarNegatif);
    step('sesi negatif: halaman login', $code === 200, 'HTTP ' . $code);

    list($code, $body, $err, $json) = http($base . 'auth/cek_login', array(
        'identity' => $username, 'password' => $password, 'remember' => '0',
    ), null, false, $jarNegatif);
    step('sesi negatif: login siswa', $code === 200 && $json && !empty($json['status']),
        'HTTP ' . $code . ' ' . substr($body, 0, 100));

    list($code, $body, $err, $json) = http($base . 'siswa/konfirmasi/' . $jadwalId, null, null, true, $jarNegatif);
    step('sesi negatif: halaman konfirmasi tetap terbuka', $code === 200, 'HTTP ' . $code);

    list($code, $body, $err, $json) = http($base . 'siswa/validasisiswa', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId, 'token' => '',
    ), null, true, $jarNegatif);
    $sebDitolak = ($code === 403 && strpos($body, 'Safe Exam Browser') !== false) || $code === 403;
    step('tanpa header SEB: validasi token ditolak', $sebDitolak, 'HTTP ' . $code . ' ' . substr($body, 0, 140));

    list($code, $body, $err, $json) = http($base . 'siswa/validasisiswa', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId, 'token' => '',
    ), 'salah', true, $jarNegatif);
    step('hash SEB salah: validasi token ditolak', $code === 403, 'HTTP ' . $code . ' ' . substr($body, 0, 140));

    $attemptNegatif = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_exam_attempt WHERE id_siswa=? AND id_jadwal=?",
        array($siswaId, $jadwalId));
    step('permintaan ditolak tidak membuat attempt', $attemptNegatif === 0, 'attempt=' . $attemptNegatif);

    /* --- Sesi B: alur lengkap sebagai SEB asli. --- */
    list($code) = http($base . 'auth', null, null, true, $jar);
    step('halaman login dapat diakses', $code === 200, 'HTTP ' . $code);

    list($code, $body, $err, $json) = http($base . 'auth/cek_login', array(
        'identity' => $username, 'password' => $password, 'remember' => '0',
    ), null, false, $jar);
    step('login siswa', $code === 200 && $json && !empty($json['status']),
        'HTTP ' . $code . ' ' . substr($body, 0, 100));

    list($code, $body) = http($base . 'siswa/konfirmasi/' . $jadwalId, null, null, true, $jar);
    step('halaman konfirmasi ujian', $code === 200 && stripos($body, 'token') !== false, 'HTTP ' . $code);

    list($code, $body, $err, $json) = http($base . 'siswa/validasisiswa', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId, 'token' => '',
    ), 'valid', true, $jar);
    step('validasi token dengan header SEB', $code === 200 && $json && !empty($json['token']) && !empty($json['izinkan']),
        'HTTP ' . $code . ' ' . substr($body, 0, 160));
    step('kuota soal terjadwal', !empty($json['jml_soal']), 'jml_soal=' . (string) ($json['jml_soal'] ?? '-'));

    list($code, $body) = http($base . 'siswa/penilaian/' . $jadwalId, null, 'valid', true, $jar);
    step('halaman ujian terbuka dengan SEB', $code === 200 && strpos($body, 'loadnomorsoal') !== false, 'HTTP ' . $code);

    $attempt = $dbh->query("SELECT status, seb_verified FROM cbt_exam_attempt WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('attempt dibuat dengan SEB terverifikasi',
        $attempt && strtoupper((string) $attempt['status']) === 'IN_PROGRESS' && (int) $attempt['seb_verified'] === 1,
        $attempt ? 'status=' . $attempt['status'] . ' seb_verified=' . $attempt['seb_verified'] : 'attempt tidak ada');

    $saved = 0;
    for ($nomor = 1; $nomor <= 3; $nomor++) {
        list($code, $body, $err, $soal) = http($base . 'siswa/loadnomorsoal', array(
            'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
            'nomor' => $nomor, 'timer' => '00:00:05', 'elapsed' => '00:00:05',
        ), 'valid', true, $jar);
        $ok = $code === 200 && $soal && !empty($soal['soal_siswa_id']);
        step('muat soal nomor ' . $nomor . ' dengan SEB', $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 100));
        if (!$ok) {
            continue;
        }
        $jawaban = array(
            'no_soal_alias' => $nomor,
            'jawaban_alias' => 'A',
            'jawaban_siswa' => 'A',
            'jenis' => 1,
            'id_soal' => (int) $soal['soal_id'],
            'id_soal_siswa' => (int) $soal['soal_siswa_id'],
            'id_jadwal' => $jadwalId,
            'id_bank' => $bankId,
            'id_siswa' => $siswaId,
        );
        list($code, $body, $err, $res) = http($base . 'siswa/savejawaban', array(
            'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
            'timer' => '00:00:10', 'elapsed' => '00:00:10', 'data' => json_encode($jawaban),
        ), 'valid', true, $jar);
        $ok = $code === 200 && $res && ($res['status'] ?? false) !== false;
        step('simpan jawaban nomor ' . $nomor . ' dengan SEB', $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 100));
        if ($ok) {
            $saved++;
        }
    }

    list($code, $body, $err, $finish) = http($base . 'siswa/selesaiujian', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
    ), 'valid', true, $jar);
    step('kumpulkan ujian dengan SEB', $code === 200 && $finish && !empty($finish['status']),
        'HTTP ' . $code . ' ' . substr($body, 0, 120));

    $durasi = $dbh->query("SELECT status, selesai FROM cbt_durasi_siswa WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('durasi ujian ditutup', $durasi && (string) $durasi['status'] === '2' && !empty($durasi['selesai']),
        $durasi ? 'status=' . $durasi['status'] : 'baris tidak ada');

    $nilai = $dbh->query("SELECT pg_benar, pg_nilai FROM cbt_nilai WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('nilai tersimpan', $nilai && (int) $nilai['pg_benar'] === $saved,
        $nilai ? 'benar=' . $nilai['pg_benar'] . ' nilai=' . $nilai['pg_nilai'] : 'baris tidak ada');

    $attempt = $dbh->query("SELECT status, seb_verified FROM cbt_exam_attempt WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId ORDER BY created_at DESC LIMIT 1")->fetch_assoc();
    step('attempt SUBMITTED dengan seb_verified=1',
        $attempt && strtoupper((string) $attempt['status']) === 'SUBMITTED' && (int) $attempt['seb_verified'] === 1,
        $attempt ? 'status=' . $attempt['status'] . ' seb_verified=' . $attempt['seb_verified'] : 'attempt tidak ada');

    $jawabanTersimpan = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_soal_siswa WHERE id_siswa=? AND id_jadwal=? AND jawaban_siswa IS NOT NULL AND jawaban_siswa<>''",
        array($siswaId, $jadwalId));
    step('jawaban tersimpan lengkap', $jawabanTersimpan === $saved, "tersimpan=$jawabanTersimpan dari $saved");

    list($code, $body, $err, $json) = http($base . 'siswa/loadnomorsoal', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
        'nomor' => 1, 'timer' => '00:00:05', 'elapsed' => '00:00:05',
    ), 'valid', true, $jar);
    step('setelah dikumpulkan, sesi ditolak', $code === 403, 'HTTP ' . $code . ' ' . substr($body, 0, 120));
} finally {
    if ($keep) {
        echo "Data uji dipertahankan: username=$username password=$password jadwal=$jadwalId seb_config_key=$sebKey\n";
    } else {
        wipe($ids);
        echo "Data uji dibersihkan.\n";
    }
    @unlink($jar);
    @unlink($jarNegatif);
}

echo $failures === 0
    ? "SMOKE SEB SATU PESERTA: OK ($base)\n"
    : "SMOKE SEB SATU PESERTA: $failures GAGAL ($base)\n";
exit($failures === 0 ? 0 : 1);
