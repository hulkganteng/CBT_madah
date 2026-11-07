<?php
/* Bukti saling-kunci pada DB buang: dua koneksi MySQL nyata. */
mysqli_report(MYSQLI_REPORT_OFF);
$db = 'garuda_phase3_locktest';
$root = new mysqli('localhost', 'root', '', '');
if (!$root) { fwrite(STDERR, "root connect failed\n"); exit(1); }
$root->query("DROP DATABASE IF EXISTS `$db`");
if (!$root->query("CREATE DATABASE `$db`")) { fwrite(STDERR, $root->error . "\n"); exit(1); }
$root->select_db($db);
$root->query('CREATE TABLE cbt_bank_soal (id_bank INT NOT NULL, PRIMARY KEY (id_bank)) ENGINE=InnoDB');
$root->query('CREATE TABLE cbt_soal (id_soal INT AUTO_INCREMENT, bank_id INT, jenis INT, nomor_soal INT, PRIMARY KEY (id_soal)) ENGINE=InnoDB');
$root->query('CREATE TABLE cbt_exam_attempt (attempt_id CHAR(36) NOT NULL, status VARCHAR(20) NOT NULL, deadline_at DATETIME NOT NULL, extra_minutes INT NOT NULL DEFAULT 0, extra_reason TEXT NULL, updated_at DATETIME NOT NULL, PRIMARY KEY (attempt_id)) ENGINE=InnoDB');
$root->query("INSERT INTO cbt_bank_soal VALUES (1)");
$root->query("INSERT INTO cbt_exam_attempt VALUES ('a-1','IN_PROGRESS','2026-01-01 10:00:00',0,NULL,'2026-01-01 09:00:00')");

$a = new mysqli('localhost', 'root', '', $db);
$b = new mysqli('localhost', 'root', '', $db);
$fail = 0;

/* IMPORT-001: kunci baris bank membuat commit kedua menunggu. */
$a->begin_transaction();
$a->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
$b->query('SET innodb_lock_wait_timeout = 1');
$b->begin_transaction();
$blocked = !$b->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
echo $blocked ? "PASS import: koneksi kedua terblokir saat baris bank dikunci\n" : "FAIL import: lock tidak berlaku\n";
if (!$blocked) { $fail++; }
$b->rollback();
$a->commit();
$b->begin_transaction();
$ok = (bool) $b->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
echo $ok ? "PASS import: lock dilepas setelah commit\n" : "FAIL import: lock tidak dilepas\n";
if (!$ok) { $fail++; }
$b->commit();

/* EXAM-003: kunci attempt membuat penambahan waktu bersamaan terserialisasi. */
$a->begin_transaction();
$a->query("SELECT deadline_at, extra_minutes FROM cbt_exam_attempt WHERE attempt_id = 'a-1' AND status = 'IN_PROGRESS' FOR UPDATE");
$b->begin_transaction();
$blocked = !$b->query("SELECT deadline_at, extra_minutes FROM cbt_exam_attempt WHERE attempt_id = 'a-1' AND status = 'IN_PROGRESS' FOR UPDATE");
echo $blocked ? "PASS extend: update kedua terblokir sampai transaksi pertama selesai\n" : "FAIL extend: lock attempt tidak berlaku\n";
if (!$blocked) { $fail++; }
$b->rollback();
$a->commit();

/* Rollback: update gagal tidak meninggalkan perubahan. */
$a->begin_transaction();
$a->query("UPDATE cbt_exam_attempt SET extra_minutes = 5, deadline_at = '2026-01-01 10:05:00' WHERE attempt_id = 'a-1'");
$a->rollback();
$row = $a->query("SELECT extra_minutes, deadline_at FROM cbt_exam_attempt WHERE attempt_id = 'a-1'")->fetch_assoc();
$clean = (int) $row['extra_minutes'] === 0 && $row['deadline_at'] === '2026-01-01 10:00:00';
echo $clean ? "PASS extend: rollback mengembalikan deadline dan extra_minutes\n" : "FAIL extend: rollback meninggalkan perubahan\n";
if (!$clean) { $fail++; }

/* IMPORT-001 lanjutan: penomoran soal paralel tetap unik setelah lock dilepas. */
function nomorMaks(mysqli $dbh, int $bankId, int $jenis): int
{
    $result = $dbh->query("SELECT COALESCE(MAX(nomor_soal),0) m FROM cbt_soal WHERE bank_id=$bankId AND jenis=$jenis");
    return (int) $result->fetch_assoc()['m'];
}
function sisipSoal(mysqli $dbh, int $bankId, int $jenis, int $mulai, int $jumlah): void
{
    for ($i = 1; $i <= $jumlah; $i++) {
        $dbh->query("INSERT INTO cbt_soal (bank_id, jenis, nomor_soal) VALUES ($bankId, $jenis, " . ($mulai + $i) . ")");
    }
}

$a->begin_transaction();
$a->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
sisipSoal($a, 1, 1, nomorMaks($a, 1, 1), 3);
$b->query('SET innodb_lock_wait_timeout = 1');
$b->begin_transaction();
$blocked = !$b->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
$b->rollback();
$a->commit();
echo $blocked ? "PASS import: commit kedua menunggu penomoran selesai\n" : "FAIL import: penomoran paralel tidak terserialisasi\n";
if (!$blocked) { $fail++; }

$b->begin_transaction();
$b->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank = 1 FOR UPDATE');
sisipSoal($b, 1, 1, nomorMaks($b, 1, 1), 3);
$b->commit();

$distinct = (int) $a->query('SELECT COUNT(DISTINCT nomor_soal) d FROM cbt_soal WHERE bank_id = 1 AND jenis = 1')->fetch_assoc()['d'];
$range = $a->query('SELECT MIN(nomor_soal) mn, MAX(nomor_soal) mx FROM cbt_soal WHERE bank_id = 1 AND jenis = 1')->fetch_assoc();
$unik = $distinct === 6 && (int) $range['mn'] === 1 && (int) $range['mx'] === 6;
echo $unik ? "PASS import: 6 nomor soal unik tanpa duplikat\n" : "FAIL import: nomor soal duplikat ($distinct unik, " . $range['mn'] . "-" . $range['mx'] . ")\n";
if (!$unik) { $fail++; }

/* Double-submit: submit kedua melihat status akhir dan tidak menilai ulang. */
$a->begin_transaction();
$a->query("UPDATE cbt_exam_attempt SET status = 'SUBMITTED' WHERE attempt_id = 'a-1'");
$a->commit();
$b->begin_transaction();
$state = $b->query("SELECT status FROM cbt_exam_attempt WHERE attempt_id = 'a-1' FOR UPDATE")->fetch_assoc();
$b->commit();
$skip = $state['status'] === 'SUBMITTED';
echo $skip ? "PASS submit: submit kedua membaca status SUBMITTED dan berhenti\n" : "FAIL submit: status akhir tidak terbaca\n";
if (!$skip) { $fail++; }

$a->close();
$b->close();
$root->query("DROP DATABASE `$db`");
$root->close();
exit($fail ? 1 : 0);
