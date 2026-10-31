<?php
/**
 * Smoke test simulasi ujian TANPA SEB dengan banyak peserta BERBARENGAN.
 *
 * Semua peserta login, validasi token, memuat soal, menyimpan jawaban, dan
 * mengumpulkan ujian secara paralel lewat curl_multi, lalu hasilnya diperiksa
 * langsung ke database (nilai, durasi, attempt, jawaban, tanpa silang peserta).
 *
 *   php tools/smoke-simulasi-konkuren.php
 *   php tools/smoke-simulasi-konkuren.php --peserta=50 --base=http://localhost/garudacbt/
 */

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
mysqli_report(MYSQLI_REPORT_OFF);

$base = 'http://localhost/garudacbt/';
$jumlah = 50;
$keep = false;
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--base=') === 0) {
        $base = rtrim(substr($arg, 7), '/') . '/';
    } elseif (strpos($arg, '--peserta=') === 0) {
        $jumlah = max(2, min(200, (int) substr($arg, 10)));
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

$UA = 'Mozilla/5.0 (Linux; Android 13) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0 Mobile Safari/537.36 SEB/3.7.0';
/* Login peserta wajib dari SEB: kirim Config Key hash milik jadwal SEB yang ada. */
$sebKey = (string) scalar($dbh, "SELECT seb_config_key FROM cbt_jadwal WHERE seb_config_key IS NOT NULL AND seb_config_key <> '' ORDER BY id_jadwal DESC LIMIT 1");

/**
 * Jalankan sekumpulan request bersamaan.
 * $job = ['key'=>string, 'jar'=>path, 'url'=>string, 'post'=>array|null,
 *         'csrf'=>string, 'follow'=>bool]
 */
function multi(array $jobs): array
{
    global $UA, $sebKey;
    $mh = curl_multi_init();
    $handles = array();
    foreach ($jobs as $i => $job) {
        $headers = array('X-Requested-With: XMLHttpRequest');
        if ($sebKey !== '') {
            $headers[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . hash('sha256', $job['url'] . $sebKey);
        }
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $job['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => isset($job['follow']) ? $job['follow'] : true,
            CURLOPT_COOKIEJAR => $job['jar'],
            CURLOPT_COOKIEFILE => $job['jar'],
            CURLOPT_TIMEOUT => 180,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_USERAGENT => $UA,
            CURLOPT_HTTPHEADER => $headers,
        ));
        if (isset($job['post'])) {
            $post = $job['post'];
            if (!empty($job['csrf'])) {
                $post['csrf_token'] = $job['csrf'];
            }
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
        }
        curl_multi_add_handle($mh, $ch);
        $handles[$i] = $ch;
    }
    $running = null;
    do {
        curl_multi_exec($mh, $running);
        if ($running > 0) {
            curl_multi_select($mh, 0.5);
        }
    } while ($running > 0);

    $out = array();
    foreach ($handles as $i => $ch) {
        $info = array('code' => (int) curl_getinfo($ch, CURLINFO_HTTP_CODE),
            'body' => (string) curl_multi_getcontent($ch),
            'error' => curl_error($ch));
        $decoded = json_decode($info['body'], true);
        $info['json'] = is_array($decoded) ? $decoded : null;
        $out[$jobs[$i]['key']] = $info;
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);
    return $out;
}

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

/* --------------------------------------------------------------------- seed */
$tag = 'SMKQ' . substr((string) time(), -5);
$password = 'Smoke#' . $tag;
$today = date('Y-m-d');
$ids = array('siswa' => array(), 'users' => array(), 'kelas_siswa' => array());

q($dbh, "INSERT INTO master_mapel (nama_mapel, kode, urutan, kelompok_ujian) VALUES ('SMOKE Q MAPEL', ?, 999, 'NON-EKSATA')", array('QM' . $tag));
$mapelId = (int) $dbh->insert_id;

q($dbh, "INSERT INTO master_kelas (id_tp, id_smt, nama_kelas, kode_kelas, jurusan_id, level_id) VALUES (?, ?, ?, 'QMK', 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], 'SMOKE Q ' . $tag, (int) $level['id_level']));
$kelasId = (int) $dbh->insert_id;

$bankKelas = serialize(array(array('kelas_id' => $kelasId)));
q($dbh, "INSERT INTO cbt_bank_soal (bank_jenis_id, bank_kode, bank_level, bank_kelas, bank_mapel_id, bank_jurusan_id, bank_guru_id, bank_nama, jml_soal, jml_kompleks, jml_esai, tampil_pg, tampil_kompleks, tampil_bs, tampil_esai, tampil_jodohkan, tampil_isian, bobot_pg, bobot_kompleks, bobot_bs, opsi, status, status_soal, id_tp, id_smt) VALUES (1, ?, 'SMK', ?, ?, 0, ?, ?, 3, 0, 0, 3, 0, 0, 0, 0, 0, 100, 0, 0, 4, 1, 1, ?, ?)",
    array('Q' . $tag, $bankKelas, $mapelId, (int) $guru['id_guru'], 'SMOKE Q BANK ' . $tag, (int) $tp['id_tp'], (int) $smt['id_smt']));
$bankId = (int) $dbh->insert_id;

for ($nomor = 1; $nomor <= 3; $nomor++) {
    q($dbh, "INSERT INTO cbt_soal (bank_id, jenis, nomor_soal, soal, deskripsi, opsi_a, opsi_b, opsi_c, opsi_d, opsi_e, jawaban, tampilkan, kesulitan) VALUES (?, 1, ?, ?, '', 'Opsi A', 'Opsi B', 'Opsi C', 'Opsi D', 'Opsi E', 'A', '1', '8')",
        array($bankId, $nomor, 'SMOKE Q SOAL ' . $nomor));
}

q($dbh, "INSERT INTO cbt_jadwal (id_tp, id_smt, id_bank, id_jenis, tgl_mulai, tgl_selesai, durasi_ujian, pengawas, acak_soal, acak_opsi, hasil_tampil, token, status, ulang, reset_login, seb_mode, rekap, jam_ke, jarak, time_create) VALUES (?, ?, ?, 1, ?, ?, 30, '', 0, 0, 1, 0, 1, 0, 0, 'OFF', 1, 1, 0, ?)",
    array((int) $tp['id_tp'], (int) $smt['id_smt'], $bankId, $today, $today, date('Y-m-d H:i:s')));
$jadwalId = (int) $dbh->insert_id;

$kelasSiswaBase = (int) scalar($dbh, "SELECT COALESCE(MAX(id_kelas_siswa),0) FROM kelas_siswa");
$hash = password_hash($password, PASSWORD_BCRYPT);
$peserta = array();
for ($i = 1; $i <= $jumlah; $i++) {
    $username = 'q' . $i . '_' . strtolower($tag);
    $nisn = $tag . str_pad((string) $i, 3, '0', STR_PAD_LEFT);
    q($dbh, "INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, nik, warga_negara, uid) VALUES (?, ?, ?, 'L', ?, ?, ?, ?, 'WNI', ?)",
        array($nisn, 'N' . $nisn, 'SMOKE Q ' . $i, $username, $password, $kelasId, 'NIK' . $nisn, 'UID' . $nisn));
    $siswaId = (int) $dbh->insert_id;
    $ids['siswa'][] = $siswaId;

    q($dbh, "INSERT INTO kelas_siswa (id_kelas_siswa, id_tp, id_smt, id_siswa, id_kelas) VALUES (?, ?, ?, ?, ?)",
        array($kelasSiswaBase + $i, (int) $tp['id_tp'], (int) $smt['id_smt'], $siswaId, $kelasId));

    q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active) VALUES ('127.0.0.1', ?, ?, ?, ?, 1)",
        array($username, $hash, $username . '@smoke.test', time()));
    $userId = (int) $dbh->insert_id;
    $ids['users'][] = $userId;
    q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 3)", array($userId));

    $jar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'smokeq-' . $tag . '-' . $i . '.cookies';
    @unlink($jar);
    $peserta[$i] = array(
        'id' => $siswaId,
        'username' => $username,
        'jar' => $jar,
        'csrf' => '',
        'soal' => array(),
    );
}

function wipe(array $ids, int $jadwalId, int $bankId, int $kelasId, int $mapelId, array $peserta): void
{
    global $dbh;
    $dbh->query("DELETE FROM cbt_exam_event WHERE attempt_id IN (SELECT attempt_id FROM cbt_exam_attempt WHERE id_jadwal=$jadwalId)");
    foreach (array('cbt_nilai', 'cbt_exam_attempt', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian') as $table) {
        $dbh->query("DELETE FROM `$table` WHERE id_jadwal=$jadwalId");
    }
    $dbh->query("DELETE FROM cbt_jadwal WHERE id_jadwal=$jadwalId");
    $dbh->query("DELETE FROM cbt_soal WHERE bank_id=$bankId");
    $dbh->query("DELETE FROM cbt_bank_soal WHERE id_bank=$bankId");
    if ($ids['siswa']) {
        $in = implode(',', array_map('intval', $ids['siswa']));
        foreach (array('cbt_nilai', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'log_ujian', 'kelas_siswa') as $table) {
            $dbh->query("DELETE FROM `$table` WHERE id_siswa IN ($in)");
        }
        $dbh->query("DELETE FROM master_siswa WHERE id_siswa IN ($in)");
    }
    if ($ids['users']) {
        $in = implode(',', array_map('intval', $ids['users']));
        $dbh->query("DELETE FROM users_groups WHERE user_id IN ($in)");
        $dbh->query("DELETE FROM users_profile WHERE id_user IN ($in)");
        $dbh->query("DELETE FROM users WHERE id IN ($in)");
    }
    $dbh->query("DELETE FROM kelas_siswa WHERE id_kelas=$kelasId");
    $dbh->query("DELETE FROM master_kelas WHERE id_kelas=$kelasId");
    $dbh->query("DELETE FROM master_mapel WHERE id_mapel=$mapelId");
    foreach ($peserta as $row) {
        @unlink($row['jar']);
    }
}

step('seed peserta', count($peserta) === $jumlah, "peserta=$jumlah bank=$bankId jadwal=$jadwalId");

/* ---------------------------------------------------------------------- run */
$startedAt = microtime(true);
try {
    /* 1. Ambil halaman login serentak untuk mendapat cookie CSRF per peserta. */
    $jobs = array();
    foreach ($peserta as $i => $row) {
        $jobs[] = array('key' => (string) $i, 'jar' => $row['jar'], 'url' => $base . 'auth');
    }
    $res = multi($jobs);
    $loginPageOk = 0;
    foreach ($res as $key => $info) {
        if ($info['code'] === 200) {
            $loginPageOk++;
            $peserta[(int) $key]['csrf'] = csrf_from_jar($peserta[(int) $key]['jar']);
        }
    }
    step('muat halaman login ' . $jumlah . ' sesi', $loginPageOk === $jumlah, "ok=$loginPageOk/$jumlah");

    /* 2. Login serentak. */
    $jobs = array();
    foreach ($peserta as $i => $row) {
        $jobs[] = array(
            'key' => (string) $i,
            'jar' => $row['jar'],
            'url' => $base . 'auth/cek_login',
            'csrf' => $row['csrf'],
            'follow' => false,
            'post' => array('identity' => $row['username'], 'password' => $password, 'remember' => '0'),
        );
    }
    $res = multi($jobs);
    $loginOk = 0;
    foreach ($res as $info) {
        if ($info['code'] === 200 && $info['json'] && !empty($info['json']['status'])) {
            $loginOk++;
        }
    }
    step('login serentak ' . $jumlah . ' peserta', $loginOk === $jumlah, "berhasil=$loginOk/$jumlah");

    /* 3. Bug 500: buka halaman ujian tanpa validasi harus dialihkan, bukan 500. */
    $probe = $peserta[1];
    $res = multi(array(array(
        'key' => 'probe',
        'jar' => $probe['jar'],
        'url' => $base . 'siswa/penilaian/' . $jadwalId,
        'follow' => false,
    )));
    $probeCode = $res['probe']['code'];
    step('halaman ujian tanpa durasi tidak 500', in_array($probeCode, array(302, 303, 307, 308), true), 'HTTP ' . $probeCode);

    /* 4. Validasi token serentak. */
    $jobs = array();
    foreach ($peserta as $i => $row) {
        $jobs[] = array(
            'key' => (string) $i,
            'jar' => $row['jar'],
            'url' => $base . 'siswa/validasisiswa',
            'csrf' => $row['csrf'],
            'post' => array('siswa' => $row['id'], 'bank' => $bankId, 'jadwal' => $jadwalId, 'token' => ''),
        );
    }
    $res = multi($jobs);
    $validOk = 0;
    foreach ($res as $info) {
        if ($info['code'] === 200 && $info['json'] && !empty($info['json']['token']) && !empty($info['json']['izinkan'])) {
            $validOk++;
        }
    }
    step('validasi token serentak', $validOk === $jumlah, "berhasil=$validOk/$jumlah");

    /* 4b. Buka halaman ujian serentak; di sinilah attempt dibuat dan diikat
       ke sesi (Exam_attempt_service::createOrResume + bindSession). */
    $jobs = array();
    foreach ($peserta as $i => $row) {
        $jobs[] = array(
            'key' => (string) $i,
            'jar' => $row['jar'],
            'url' => $base . 'siswa/penilaian/' . $jadwalId,
        );
    }
    $res = multi($jobs);
    $bukaOk = 0;
    $contohBuka = null;
    foreach ($res as $key => $info) {
        if ($info['code'] === 200 && strpos($info['body'], 'loadnomorsoal') !== false) {
            $bukaOk++;
        } elseif ($contohBuka === null) {
            $contohBuka = 'p' . $key . ' HTTP ' . $info['code'] . ' ' . substr($info['body'], 0, 200);
        }
    }
    if ($bukaOk < $jumlah && $contohBuka !== null) {
        echo '   contoh gagal: ', preg_replace('/\s+/', ' ', $contohBuka), PHP_EOL;
    }
    step('buka halaman ujian serentak', $bukaOk === $jumlah, "berhasil=$bukaOk/$jumlah");

    /* 5. Muat + simpan jawaban serentak, tiga gelombang (satu soal per gelombang). */
    for ($nomor = 1; $nomor <= 3; $nomor++) {
        $jobs = array();
        foreach ($peserta as $i => $row) {
            $jobs[] = array(
                'key' => (string) $i,
                'jar' => $row['jar'],
                'url' => $base . 'siswa/loadnomorsoal',
                'csrf' => $row['csrf'],
                'post' => array('siswa' => $row['id'], 'bank' => $bankId, 'jadwal' => $jadwalId,
                    'nomor' => $nomor, 'timer' => '00:00:10', 'elapsed' => '00:00:10'),
            );
        }
        $res = multi($jobs);
        $loadOk = 0;
        $contohGagal = null;
        foreach ($res as $key => $info) {
            $soal = $info['json'];
            if ($info['code'] === 200 && $soal && !empty($soal['soal_siswa_id'])) {
                $peserta[(int) $key]['soal'][$nomor] = $soal;
                $loadOk++;
            } elseif ($contohGagal === null) {
                $contohGagal = 'p' . $key . ' HTTP ' . $info['code'] . ' ' . substr($info['body'], 0, 200);
            }
        }
        if ($loadOk === 0 && $contohGagal !== null) {
            echo '   contoh gagal: ', preg_replace('/\s+/', ' ', $contohGagal), PHP_EOL;
        }
        step('muat soal nomor ' . $nomor . ' serentak', $loadOk === $jumlah, "berhasil=$loadOk/$jumlah");

        $jobs = array();
        foreach ($peserta as $i => $row) {
            if (!isset($row['soal'][$nomor])) {
                continue;
            }
            $soal = $row['soal'][$nomor];
            $jawaban = array(
                'no_soal_alias' => $nomor,
                'jawaban_alias' => 'A',
                'jawaban_siswa' => 'A',
                'jenis' => 1,
                'id_soal' => (int) $soal['soal_id'],
                'id_soal_siswa' => (int) $soal['soal_siswa_id'],
                'id_jadwal' => $jadwalId,
                'id_bank' => $bankId,
                'id_siswa' => $row['id'],
            );
            $jobs[] = array(
                'key' => (string) $i,
                'jar' => $row['jar'],
                'url' => $base . 'siswa/savejawaban',
                'csrf' => $row['csrf'],
                'post' => array('siswa' => $row['id'], 'bank' => $bankId, 'jadwal' => $jadwalId,
                    'timer' => '00:00:12', 'elapsed' => '00:00:12', 'data' => json_encode($jawaban)),
            );
        }
        $res = multi($jobs);
        $saveOk = 0;
        foreach ($res as $info) {
            if ($info['code'] === 200 && $info['json'] && ($info['json']['status'] ?? false) !== false) {
                $saveOk++;
            }
        }
        step('simpan jawaban nomor ' . $nomor . ' serentak', $saveOk === count($jobs), "berhasil=$saveOk/" . count($jobs));
    }

    /* 6. Kumpulkan ujian serentak. */
    $jobs = array();
    foreach ($peserta as $i => $row) {
        $jobs[] = array(
            'key' => (string) $i,
            'jar' => $row['jar'],
            'url' => $base . 'siswa/selesaiujian',
            'csrf' => $row['csrf'],
            'post' => array('siswa' => $row['id'], 'bank' => $bankId, 'jadwal' => $jadwalId),
        );
    }
    $res = multi($jobs);
    $finishOk = 0;
    foreach ($res as $info) {
        if ($info['code'] === 200 && $info['json'] && !empty($info['json']['status'])) {
            $finishOk++;
        }
    }
    step('kumpulkan ujian serentak', $finishOk === $jumlah, "berhasil=$finishOk/$jumlah");

    $elapsed = microtime(true) - $startedAt;

    /* 7. Verifikasi database. */
    $nilaiRows = array();
    $res = $dbh->query("SELECT id_siswa, pg_benar, pg_nilai FROM cbt_nilai WHERE id_jadwal=$jadwalId");
    while ($row = $res->fetch_assoc()) {
        $nilaiRows[(int) $row['id_siswa']] = $row;
    }
    $harap = array();
    foreach ($peserta as $row) {
        $harap[] = $row['id'];
    }
    $nilaiLengkap = count($nilaiRows) === $jumlah;
    $nilaiBenar = true;
    foreach ($harap as $idSiswa) {
        if (!isset($nilaiRows[$idSiswa]) || (int) $nilaiRows[$idSiswa]['pg_benar'] !== 3) {
            $nilaiBenar = false;
            break;
        }
    }
    step('nilai semua peserta tersimpan', $nilaiLengkap && $nilaiBenar, 'baris=' . count($nilaiRows) . '/' . $jumlah);

    step('tidak ada nilai silang peserta', count($nilaiRows) === count(array_intersect(array_keys($nilaiRows), $harap)),
        'id unik=' . count($nilaiRows));

    $attemptOk = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_exam_attempt WHERE id_jadwal=? AND status='SUBMITTED'", array($jadwalId));
    step('attempt semua SUBMITTED', $attemptOk === $jumlah, "attempt=$attemptOk/$jumlah");

    $durasiOk = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_durasi_siswa WHERE id_jadwal=? AND status=2 AND selesai IS NOT NULL", array($jadwalId));
    step('durasi semua ditutup', $durasiOk === $jumlah, "ditutup=$durasiOk/$jumlah");

    $jawabOk = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_soal_siswa WHERE id_jadwal=? AND jawaban_siswa IS NOT NULL AND jawaban_siswa<>''", array($jadwalId));
    step('jawaban tersimpan lengkap', $jawabOk === $jumlah * 3, "jawaban=$jawabOk/" . ($jumlah * 3));

    $soalSiswaOk = (int) scalar($dbh, "SELECT COUNT(DISTINCT id_siswa) FROM cbt_soal_siswa WHERE id_jadwal=?", array($jadwalId));
    step('kuota soal per peserta', $soalSiswaOk === $jumlah, "peserta=$soalSiswaOk/$jumlah");

    $tanpaSeb = (int) scalar($dbh, "SELECT COUNT(*) FROM cbt_exam_attempt WHERE id_jadwal=? AND seb_verified=0", array($jadwalId));
    step('attempt tanpa SEB tidak terverifikasi', $tanpaSeb === $jumlah, "seb_verified=0: $tanpaSeb/$jumlah");

    step('waktu eksekusi', true, number_format($elapsed, 1) . ' detik untuk ' . $jumlah . ' peserta');
} finally {
    if ($keep) {
        $contoh = $peserta[1];
        echo "Data uji dipertahankan: username={$contoh['username']} password=$password jadwal=$jadwalId\n";
    } else {
        wipe($ids, $jadwalId, $bankId, $kelasId, $mapelId, $peserta);
        echo "Data uji dibersihkan.\n";
    }
}

echo $failures === 0
    ? "SMOKE $jumlah PESERTA SERENTAK TANPA SEB: OK\n"
    : "SMOKE $jumlah PESERTA SERENTAK TANPA SEB: $failures GAGAL\n";
exit($failures === 0 ? 0 : 1);
