<?php
/**
 * Isi DB dengan data "Data Umum" + bank soal kosong untuk uji Tambah Soal / Import Soal.
 *
 *   php tools/seed-data-umum.php
 *
 * Idempotent: semua data ditandai prefix UMU dan dihapus dulu sebelum dibuat ulang.
 * Tidak menyentuh data simulasi SEB atau data lain.
 */

declare(strict_types=1);

mysqli_report(MYSQLI_REPORT_OFF);

$root = dirname(__DIR__);
define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
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

function password(string $plain): string
{
    return password_hash($plain, PASSWORD_BCRYPT);
}

$tp = $dbh->query("SELECT * FROM master_tp WHERE active=1 LIMIT 1")->fetch_assoc();
$smt = $dbh->query("SELECT * FROM master_smt WHERE active=1 LIMIT 1")->fetch_assoc();
if (!$tp || !$smt) {
    fwrite(STDERR, "Tahun pelajaran / semester aktif belum ada.\n");
    exit(1);
}
$tpId = (int) $tp['id_tp'];
$smtId = (int) $smt['id_smt'];

$dbh->begin_transaction();
try {
    /* Bersihkan hasil seed lama. */
    $oldBanks = array();
    $res = $dbh->query("SELECT id_bank FROM cbt_bank_soal WHERE bank_kode LIKE 'UMU%'");
    while ($row = $res->fetch_assoc()) {
        $oldBanks[] = (int) $row['id_bank'];
    }
    if ($oldBanks) {
        $ids = implode(',', $oldBanks);
        $dbh->query("DELETE FROM cbt_soal WHERE bank_id IN ({$ids})");
        $dbh->query("DELETE FROM cbt_soal_import_item WHERE import_id IN (SELECT id FROM cbt_soal_import WHERE bank_id IN ({$ids}))");
        $dbh->query("DELETE FROM cbt_soal_import WHERE bank_id IN ({$ids})");
        $dbh->query("DELETE FROM cbt_bank_soal WHERE id_bank IN ({$ids})");
    }
    $oldSiswa = array();
    $res = $dbh->query("SELECT id_siswa FROM master_siswa WHERE nisn LIKE 'UMU%' OR username LIKE 'umum.siswa%'");
    while ($row = $res->fetch_assoc()) {
        $oldSiswa[] = (int) $row['id_siswa'];
    }
    if ($oldSiswa) {
        $ids = implode(',', $oldSiswa);
        $dbh->query("DELETE FROM kelas_siswa WHERE id_siswa IN ({$ids})");
        $dbh->query("DELETE FROM master_siswa WHERE id_siswa IN ({$ids})");
    }
    $oldUsers = array();
    $res = $dbh->query("SELECT id FROM users WHERE username LIKE 'umum.%'");
    while ($row = $res->fetch_assoc()) {
        $oldUsers[] = (int) $row['id'];
    }
    if ($oldUsers) {
        $ids = implode(',', $oldUsers);
        $dbh->query("DELETE FROM users_groups WHERE user_id IN ({$ids})");
        $dbh->query("DELETE FROM users_profile WHERE id_user IN ({$ids})");
        $dbh->query("DELETE FROM users WHERE id IN ({$ids})");
    }
    $dbh->query("DELETE FROM master_kelas WHERE kode_kelas LIKE 'UMU%'");
    $dbh->query("DELETE FROM master_guru WHERE nip LIKE 'UMU%'");
    $dbh->query("DELETE FROM master_mapel WHERE kode LIKE 'UMU-%'");
    $dbh->query("DELETE FROM master_ekstra WHERE kode_ekstra LIKE 'UMU%'");

    /* Ekstrakurikuler */
    $ekstra = array('Pramuka', 'Paskibra', 'Futsal', 'Robotik');
    foreach ($ekstra as $i => $nama) {
        q($dbh, "INSERT INTO master_ekstra (nama_ekstra, kode_ekstra) VALUES (?, ?)", array($nama, 'UMU' . ($i + 1)));
    }

    /* Mata pelajaran */
    $mapel = array(
        array('Matematika Umum', 'UMU-MTK', 'A'),
        array('Bahasa Indonesia Umum', 'UMU-BIN', 'A'),
        array('Bahasa Inggris Umum', 'UMU-BIG', 'A'),
        array('IPA Umum', 'UMU-IPA', 'A'),
        array('IPS Umum', 'UMU-IPS', 'A'),
        array('PPKn Umum', 'UMU-PPKN', 'A'),
        array('PAI Umum', 'UMU-PAI', 'A'),
        array('PJOK Umum', 'UMU-PJOK', 'B'),
        array('Seni Budaya Umum', 'UMU-SBD', 'B'),
        array('Informatika Umum', 'UMU-INF', 'B'),
    );
    $mapelIds = array();
    foreach ($mapel as $i => $item) {
        q($dbh, "INSERT INTO master_mapel (nama_mapel, kode, kelompok, kelompok_ujian, urutan, status, deletable) VALUES (?, ?, ?, 'NON_EKSATA', ?, 1, 1)",
            array($item[0], $item[1], $item[2], 900 + $i));
        $mapelIds[$item[1]] = (int) $dbh->insert_id;
    }

    /* Guru + akun login (group 2) */
    $guruNames = array(
        'Guru Umum Satu', 'Guru Umum Dua', 'Guru Umum Tiga',
        'Guru Umum Empat', 'Guru Umum Lima', 'Guru Umum Enam',
    );
    $guruIds = array();
    foreach ($guruNames as $i => $nama) {
        $n = $i + 1;
        $username = 'umum.guru' . $n;
        $email = $username . '@umum.local';
        q($dbh, "INSERT INTO master_guru (nip, nama_guru, email, kode_guru, username, password, jenis_kelamin, status_aktif) VALUES (?, ?, ?, ?, ?, ?, 'L', 'Aktif')",
            array('UMU' . str_pad((string) $n, 8, '0', STR_PAD_LEFT), $nama, $email, 'UMU' . $n, $username, password('Guru#2026')));
        $guruId = (int) $dbh->insert_id;
        $guruIds[] = $guruId;

        q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active, first_name, last_name) VALUES ('127.0.0.1', ?, ?, ?, ?, 1, ?, '')",
            array($username, password('Guru#2026'), $email, time(), $nama));
        $userId = (int) $dbh->insert_id;
        q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 2)", array($userId));
        q($dbh, "INSERT INTO users_profile (id_user, nama_lengkap, jabatan, level_access) VALUES (?, ?, 'Guru', 2)", array($userId, $nama));
    }

    /* Kelas / rombel */
    $levels = array('X' => 10, 'XI' => 11, 'XII' => 12);
    $kelasList = array();
    foreach ($levels as $tingkat => $levelId) {
        for ($rombel = 1; $rombel <= 2; $rombel++) {
            $nama = $tingkat . ' UMUM ' . $rombel;
            $kode = 'UMU-' . $tingkat . '-' . $rombel;
            $wali = $guruIds[(count($kelasList)) % count($guruIds)];
            q($dbh, "INSERT INTO master_kelas (id_tp, id_smt, nama_kelas, kode_kelas, jurusan_id, level_id, guru_id, set_siswa) VALUES (?, ?, ?, ?, 5, ?, ?, '0')",
                array($tpId, $smtId, $nama, $kode, $levelId, $wali));
            $kelasList[] = array(
                'id' => (int) $dbh->insert_id,
                'nama' => $nama,
                'kode' => $kode,
                'level' => $levelId,
            );
        }
    }

    /* Siswa + akun login (group 3) */
    $nextKelasSiswa = (int) $dbh->query("SELECT COALESCE(MAX(id_kelas_siswa),0)+1 FROM kelas_siswa")->fetch_row()[0];
    $siswaTotal = 0;
    foreach ($kelasList as $kelas) {
        for ($i = 1; $i <= 5; $i++) {
            $siswaTotal++;
            $username = 'umum.siswa' . str_pad((string) $siswaTotal, 2, '0', STR_PAD_LEFT);
            $nisn = 'UMU' . str_pad((string) $siswaTotal, 8, '0', STR_PAD_LEFT);
            $nis = 'UMU-' . str_pad((string) $siswaTotal, 2, '0', STR_PAD_LEFT);
            $nama = 'Siswa Umum ' . str_pad((string) $siswaTotal, 2, '0', STR_PAD_LEFT);
            $jk = $siswaTotal % 2 === 0 ? 'P' : 'L';
            q($dbh, "INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, tahun_masuk, nik, warga_negara, uid) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'WNI', ?)",
                array($nisn, $nis, $nama, $jk, $username, 'Siswa#2026', $kelas['id'], (string) ($tpId + 2021), 'NIK' . $nisn, 'UID' . $nisn));
            $siswaId = (int) $dbh->insert_id;

            q($dbh, "INSERT INTO kelas_siswa (id_kelas_siswa, id_tp, id_smt, id_siswa, id_kelas) VALUES (?, ?, ?, ?, ?)",
                array($nextKelasSiswa++, $tpId, $smtId, $siswaId, $kelas['id']));

            q($dbh, "INSERT INTO users (ip_address, username, password, email, created_on, active, first_name, last_name) VALUES ('127.0.0.1', ?, ?, ?, ?, 1, ?, '')",
                array($username, password('Siswa#2026'), $username . '@umum.local', time(), $nama));
            $userId = (int) $dbh->insert_id;
            q($dbh, "INSERT INTO users_groups (user_id, group_id) VALUES (?, 3)", array($userId));
            q($dbh, "INSERT INTO users_profile (id_user, nama_lengkap, jabatan, level_access) VALUES (?, ?, 'Siswa', 3)", array($userId, $nama));
        }
    }

    /* Dua bank soal kosong: satu untuk Tambah Soal, satu untuk Import Soal. */
    $bankKelas = serialize(array_map(fn ($k) => array('kelas_id' => $k['id']), $kelasList));
    $banks = array(
        array('UMU-ADD', 'UMUM - Latihan Tambah Soal'),
        array('UMU-IMP', 'UMUM - Latihan Import Soal'),
    );
    $bankIds = array();
    foreach ($banks as $item) {
        q($dbh, "INSERT INTO cbt_bank_soal (bank_jenis_id, bank_kode, bank_level, bank_kelas, bank_mapel_id, bank_jurusan_id, bank_guru_id, bank_nama, kkm, jml_soal, jml_kompleks, jml_esai, tampil_pg, tampil_kompleks, tampil_bs, tampil_esai, tampil_jodohkan, tampil_isian, bobot_pg, bobot_kompleks, bobot_bs, bobot_esai, bobot_jodohkan, bobot_isian, opsi, status, status_soal, id_tp, id_smt, deskripsi) VALUES (1, ?, 'SMA', ?, ?, 5, ?, ?, 75, 0, 0, 0, 0, 0, 0, 0, 0, 0, 100, 0, 0, 0, 0, 0, 4, 1, 0, ?, ?, 'Bank soal latihan fitur Data Umum')",
            array($item[0], $bankKelas, $mapelIds['UMU-MTK'], $guruIds[0], $item[1], $tpId, $smtId));
        $bankIds[$item[0]] = (int) $dbh->insert_id;
    }

    $dbh->commit();
} catch (Throwable $e) {
    $dbh->rollback();
    fwrite(STDERR, "Gagal menyiapkan data: " . $e->getMessage() . "\n");
    exit(1);
}

echo "=== DATA UMUM SIAP ===\n\n";
echo "Tahun aktif   : {$tp['tahun']} (id_tp={$tpId})\n";
echo "Semester      : {$smt['nama_smt']} (id_smt={$smtId})\n";
echo "Ekstrakurikuler: 4\n";
echo "Mata pelajaran : " . count($mapel) . "\n";
echo "Guru          : " . count($guruNames) . " (login umum.guru1..6 / Guru#2026)\n";
echo "Kelas         : " . count($kelasList) . " (X/XI/XII UMUM 1-2)\n";
echo "Siswa         : {$siswaTotal} (login umum.siswa01..30 / Siswa#2026)\n";
echo "Bank soal     :\n";
echo "  - UMUM - Latihan Tambah Soal (id_bank={$bankIds['UMU-ADD']})\n";
echo "  - UMUM - Latihan Import Soal  (id_bank={$bankIds['UMU-IMP']})\n\n";
echo "Buka: http://localhost/garudacbt/cbtbanksoal\n";
