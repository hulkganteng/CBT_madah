<?php
declare(strict_types=1);

// Real MariaDB advisory-lock test. It only uses GET_LOCK/RELEASE_LOCK and does not touch exam rows.
$root = dirname(__DIR__);
define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
define('ENVIRONMENT', 'development');
$db = [];
include $root . '/application/config/database.php';
$cfg = $db['default'];
$dsn = [$cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']];
$key = 'codex_exam_integrity_' . getmypid() . '_' . bin2hex(random_bytes(4));
$first = new mysqli($dsn[0], $dsn[1], $dsn[2], $dsn[3]);
$second = new mysqli($dsn[0], $dsn[1], $dsn[2], $dsn[3]);
if ($first->connect_errno || $second->connect_errno) { fwrite(STDERR, "MariaDB connection failed\n"); exit(1); }
$first->query("SELECT GET_LOCK('" . $first->real_escape_string($key) . "', 1)");
$firstResult = (int) $first->query('SELECT IS_USED_LOCK(\'' . $first->real_escape_string($key) . '\') AS owner')->fetch_assoc()['owner'] > 0;
$secondResult = (int) $second->query("SELECT GET_LOCK('" . $second->real_escape_string($key) . "', 0)")->fetch_row()[0];
$first->query("SELECT RELEASE_LOCK('" . $first->real_escape_string($key) . "')");
$secondAfterRelease = (int) $second->query("SELECT GET_LOCK('" . $second->real_escape_string($key) . "', 0)")->fetch_row()[0];
$second->query("SELECT RELEASE_LOCK('" . $second->real_escape_string($key) . "')");
if (!$firstResult || $secondResult !== 0 || $secondAfterRelease !== 1) {
    fwrite(STDERR, "FAIL: advisory lock did not serialize concurrent requests\n"); exit(1);
}
echo "PASS: two real MariaDB connections serialize GET_LOCK scope\n";
