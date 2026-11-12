<?php
declare(strict_types=1);

/**
 * GarudaCBT Exam Data Retention & Cleanup CLI Tool
 *
 * Usage:
 *   php tools/cleanup-exam-logs.php [--days=90] [--dry-run]
 */

$root = dirname(__DIR__);
define('BASEPATH', $root . DIRECTORY_SEPARATOR . 'system' . DIRECTORY_SEPARATOR);
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

$db = array();
require $root . '/application/config/database.php';
$cfg = $db['default'] ?? $db['development'] ?? reset($db);

$options = getopt('', array('days::', 'dry-run', 'help'));
if (isset($options['help'])) {
    echo "GarudaCBT Retention & Cleanup Tool\n";
    echo "Options:\n";
    echo "  --days=N     Retention threshold in days (default: 90)\n";
    echo "  --dry-run    Inspect rows to be cleaned without deleting\n";
    echo "  --help       Show this help message\n";
    exit(0);
}

$days = isset($options['days']) ? max(7, (int) $options['days']) : 90;
$dryRun = isset($options['dry-run']);

echo "=== GarudaCBT Data Retention & Cleanup ===\n";
echo "Retention window : {$days} days\n";
echo "Execution mode   : " . ($dryRun ? "DRY-RUN (no rows modified)" : "PURGE (live deletion)") . "\n";
echo "Database host    : {$cfg['hostname']} ({$cfg['database']})\n\n";

$mysqli = @new mysqli($cfg['hostname'], $cfg['username'], $cfg['password'], $cfg['database']);
if ($mysqli->connect_errno) {
    fwrite(STDERR, "[ERROR] Cannot connect to database: " . $mysqli->connect_error . "\n");
    exit(1);
}
$mysqli->set_charset("utf8");

$targets = array(
    array(
        'name' => 'Old audit logs (log_ujian)',
        'table' => 'log_ujian',
        'count_sql' => "SELECT COUNT(*) FROM `log_ujian` WHERE `log_time` < DATE_SUB(NOW(), INTERVAL ? DAY)",
        'action_sql' => "DELETE FROM `log_ujian` WHERE `log_time` < DATE_SUB(NOW(), INTERVAL ? DAY)",
        'param' => $days,
    ),
    array(
        'name' => 'Old exam events (cbt_exam_event)',
        'table' => 'cbt_exam_event',
        'count_sql' => "SELECT COUNT(*) FROM `cbt_exam_event` WHERE `recorded_at` < DATE_SUB(NOW(), INTERVAL ? DAY)",
        'action_sql' => "DELETE FROM `cbt_exam_event` WHERE `recorded_at` < DATE_SUB(NOW(), INTERVAL ? DAY)",
        'param' => $days,
    ),
    array(
        'name' => 'Orphaned exam questions (cbt_soal_siswa)',
        'table' => 'cbt_soal_siswa',
        'count_sql' => "SELECT COUNT(*) FROM `cbt_soal_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'action_sql' => "DELETE FROM `cbt_soal_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'param' => null,
    ),
    array(
        'name' => 'Orphaned student durations (cbt_durasi_siswa)',
        'table' => 'cbt_durasi_siswa',
        'count_sql' => "SELECT COUNT(*) FROM `cbt_durasi_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'action_sql' => "DELETE FROM `cbt_durasi_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'param' => null,
    ),
    array(
        'name' => 'Orphaned exam grades (cbt_nilai)',
        'table' => 'cbt_nilai',
        'count_sql' => "SELECT COUNT(*) FROM `cbt_nilai` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'action_sql' => "DELETE FROM `cbt_nilai` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)",
        'param' => null,
    ),
);

$totalCleaned = 0;
foreach ($targets as $t) {
    // Check if table exists
    $tCheck = $mysqli->query("SHOW TABLES LIKE '{$t['table']}'");
    if (!$tCheck || $tCheck->num_rows === 0) {
        echo "[SKIP] Table `{$t['table']}` does not exist.\n";
        continue;
    }

    // Count rows
    if ($t['param'] !== null) {
        $stmt = $mysqli->prepare($t['count_sql']);
        $stmt->bind_param('i', $t['param']);
        $stmt->execute();
        $count = $stmt->get_result()->fetch_row()[0] ?? 0;
        $stmt->close();
    } else {
        $res = $mysqli->query($t['count_sql']);
        $count = $res ? (int) ($res->fetch_row()[0] ?? 0) : 0;
    }

    echo sprintf(" - %-45s : %d row(s) found\n", $t['name'], $count);

    if ($count > 0 && !$dryRun) {
        if ($t['param'] !== null) {
            $stmt = $mysqli->prepare($t['action_sql']);
            $stmt->bind_param('i', $t['param']);
            $stmt->execute();
            $affected = $stmt->affected_rows;
            $stmt->close();
        } else {
            $mysqli->query($t['action_sql']);
            $affected = $mysqli->affected_rows;
        }
        $totalCleaned += $affected;
        echo sprintf("   -> Deleted %d row(s)\n", $affected);
    }
}

echo "\nSummary:\n";
if ($dryRun) {
    echo "Dry-run complete. No rows were deleted. Run without --dry-run to purge.\n";
} else {
    echo "Cleanup complete. Total rows removed: {$totalCleaned}\n";
}
$mysqli->close();
