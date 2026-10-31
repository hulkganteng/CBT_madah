<?php
/**
 * Uji paket simulasi nyata (tools/siapkan-simulasi.php) lewat HTTP sungguhan.
 *
 *   php tools/uji-simulasi.php
 *   php tools/uji-simulasi.php --base=https://domain-anda/
 *
 * Menguji alur siswa lengkap dengan header SEB (sha256(url + Config Key)),
 * lalu MENGOSONGKAN kembali attempt/nilai siswa simulasi supaya akun siap
 * dipakai manual di laptop. Data bank/jadwal/siswa tidak dihapus.
 */

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
mysqli_report(MYSQLI_REPORT_OFF);

$base = '';
foreach (array_slice($argv, 1) as $arg) {
    if (strpos($arg, '--base=') === 0) {
        $base = rtrim(substr($arg, 7), '/') . '/';
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

$sim = $dbh->query("
    SELECT j.id_jadwal, j.id_bank, j.seb_config_key, j.token, s.id_siswa, s.username, u.password AS hash
    FROM cbt_jadwal j
    JOIN cbt_bank_soal b ON b.id_bank = j.id_bank
    JOIN master_siswa s ON s.username = 'simulasi.seb'
    JOIN users u ON u.username = 'simulasi.seb'
    WHERE b.bank_nama LIKE 'Simulasi SEB%'
    LIMIT 1
")->fetch_assoc();
if (!$sim) {
    fwrite(STDERR, "Paket simulasi belum ada. Jalankan: php tools/siapkan-simulasi.php\n");
    exit(1);
}

$jadwalId = (int) $sim['id_jadwal'];
$bankId = (int) $sim['id_bank'];
$siswaId = (int) $sim['id_siswa'];
$sebKey = (string) $sim['seb_config_key'];
$username = (string) $sim['username'];
$password = getenv('SIMULASI_PASSWORD') ?: 'Simulasi#2026';

if (!password_verify($password, (string) $sim['hash'])) {
    step('password akun simulasi cocok', false, 'atur SIMULASI_PASSWORD atau jalankan ulang siapkan-simulasi.php');
    exit(1);
}

/* Buang sisa percobaan lama supaya ujian bisa dibuka lagi. */
function bersihkanAttempt(mysqli $dbh, int $siswaId, int $jadwalId): void
{
    $ids = array();
    $res = $dbh->query("SELECT attempt_id FROM cbt_exam_attempt WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId");
    while ($row = $res->fetch_assoc()) {
        $ids[] = "'" . $dbh->real_escape_string($row['attempt_id']) . "'";
    }
    if ($ids) {
        $dbh->query("DELETE FROM cbt_exam_event WHERE attempt_id IN (" . implode(',', $ids) . ")");
    }
    foreach (array('cbt_exam_attempt', 'cbt_soal_siswa', 'cbt_durasi_siswa', 'cbt_nilai', 'log_ujian') as $table) {
        $dbh->query("DELETE FROM `$table` WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId");
    }
}
bersihkanAttempt($dbh, $siswaId, $jadwalId);

$UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) '
    . 'Chrome/126.0.0.0 Safari/537.36 SEB/3.7.0';
$jar = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'uji-simulasi.cookies';
@unlink($jar);

$csrf = function () use ($jar): string {
    if (!is_file($jar)) {
        return '';
    }
    foreach (file($jar) as $line) {
        if (preg_match('/\tcsrf_cookie\t/', $line) === 1) {
            $parts = explode("\t", rtrim($line, "\r\n"));
            if (count($parts) >= 7) {
                return urldecode($parts[6]);
            }
        }
    }
    return '';
};

function http(string $url, ?array $post = null, bool $seb = true): array
{
    global $sebKey, $UA, $jar, $csrf;
    $headers = array('X-Requested-With: XMLHttpRequest');
    if ($seb) {
        $headers[] = 'X-SafeExamBrowser-ConfigKeyHash: ' . hash('sha256', $url . $sebKey);
    }
    $ch = curl_init();
    curl_setopt_array($ch, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_COOKIEJAR => $jar,
        CURLOPT_COOKIEFILE => $jar,
        CURLOPT_TIMEOUT => 60,
        CURLOPT_USERAGENT => $UA,
        CURLOPT_HTTPHEADER => $headers,
    ));
    if ($post !== null) {
        $token = $csrf();
        if ($token !== '') {
            $post['csrf_token'] = $token;
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
    }
    $body = (string) curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $decoded = json_decode($body, true);
    return array($code, $body, is_array($decoded) ? $decoded : null);
}

if ($base === '') {
    $base = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '') ? 'https' : 'http') . '://';
    $base = 'https://cbtmadah.bhydev.my.id/';
}

try {
    list($code) = http($base . 'auth', null, false);
    step('domain dapat diakses', $code === 200, 'HTTP ' . $code . ' ' . $base);
    if ($code !== 200) {
        throw new RuntimeException('domain tidak dapat diakses');
    }

    list($code, $body, $json) = http($base . 'auth/cek_login', array(
        'identity' => $username, 'password' => $password, 'remember' => '0',
    ), true);
    step('login ' . $username, $code === 200 && $json && !empty($json['status']), substr($body, 0, 120));
    if ($code !== 200 || !$json || empty($json['status'])) {
        throw new RuntimeException('login gagal');
    }

    list($code, $body) = http($base . 'siswa/konfirmasi/' . $jadwalId);
    step('halaman konfirmasi ujian', $code === 200 && stripos($body, 'token') !== false, 'HTTP ' . $code);

    list($code, $body, $json) = http($base . 'siswa/validasisiswa', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId, 'token' => '',
    ));
    step('validasi token + header SEB diterima',
        $code === 200 && $json && !empty($json['izinkan']), 'HTTP ' . $code . ' ' . substr($body, 0, 160));

    list($code, $body) = http($base . 'siswa/penilaian/' . $jadwalId);
    step('halaman soal terbuka', $code === 200 && strpos($body, 'loadnomorsoal') !== false, 'HTTP ' . $code);

    $attempt = $dbh->query("SELECT status, seb_verified FROM cbt_exam_attempt WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('attempt IN_PROGRESS + seb_verified=1',
        $attempt && strtoupper((string) $attempt['status']) === 'IN_PROGRESS' && (int) $attempt['seb_verified'] === 1,
        $attempt ? 'status=' . $attempt['status'] . ' seb_verified=' . $attempt['seb_verified'] : 'attempt tidak ada');

    $jumlahSoal = (int) $dbh->query("SELECT COUNT(*) FROM cbt_soal WHERE bank_id=$bankId")->fetch_row()[0];
    $saved = 0;
    for ($nomor = 1; $nomor <= $jumlahSoal; $nomor++) {
        list($code, $body, $soal) = http($base . 'siswa/loadnomorsoal', array(
            'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
            'nomor' => $nomor, 'timer' => '00:00:05', 'elapsed' => '00:00:05',
        ));
        $ok = $code === 200 && $soal && !empty($soal['soal_siswa_id']);
        step('muat soal ' . $nomor . '/' . $jumlahSoal, $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 100));
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
        list($code, $body, $res) = http($base . 'siswa/savejawaban', array(
            'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
            'timer' => '00:00:10', 'elapsed' => '00:00:10', 'data' => json_encode($jawaban),
        ));
        $ok = $code === 200 && $res && ($res['status'] ?? false) !== false;
        step('simpan jawaban ' . $nomor, $ok, 'HTTP ' . $code . ' ' . substr($body, 0, 100));
        if ($ok) {
            $saved++;
        }
    }

    list($code, $body, $finish) = http($base . 'siswa/selesaiujian', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
    ));
    step('kumpulkan ujian', $code === 200 && $finish && !empty($finish['status']), 'HTTP ' . $code . ' ' . substr($body, 0, 120));

    $benarDiharapkan = (int) $dbh->query("SELECT COUNT(*) FROM cbt_soal WHERE bank_id=$bankId AND jawaban='A'")->fetch_row()[0];
    $nilai = $dbh->query("SELECT pg_benar, pg_nilai FROM cbt_nilai WHERE id_siswa=$siswaId AND id_jadwal=$jadwalId")->fetch_assoc();
    step('nilai tersimpan (' . $saved . ' jawaban, kunci A=' . $benarDiharapkan . ')',
        $nilai && (int) $nilai['pg_benar'] === $benarDiharapkan && $saved === $jumlahSoal,
        $nilai ? 'benar=' . $nilai['pg_benar'] . ' nilai=' . $nilai['pg_nilai'] : 'baris tidak ada');

    list($code, $body) = http($base . 'siswa/loadnomorsoal', array(
        'siswa' => $siswaId, 'bank' => $bankId, 'jadwal' => $jadwalId,
        'nomor' => 1, 'timer' => '00:00:05', 'elapsed' => '00:00:05',
    ));
    step('setelah dikumpulkan sesi ditolak', $code === 403, 'HTTP ' . $code);
} catch (Throwable $e) {
    step('alur berhenti', false, $e->getMessage());
} finally {
    bersihkanAttempt($dbh, $siswaId, $jadwalId);
    @unlink($jar);
    echo "Attempt dinilai lalu dikosongkan lagi; akun siap dicoba manual.\n";
}

echo $failures === 0
    ? "UJI SIMULASI SEB: OK ($base)\n"
    : "UJI SIMULASI SEB: $failures GAGAL ($base)\n";
exit($failures === 0 ? 0 : 1);
