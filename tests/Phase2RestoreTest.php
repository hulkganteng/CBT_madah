<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Db_restore.php';

final class Phase2RestoreTest extends TestCase
{
    private $server;
    private $folder;
    private $live;
    private $prefix = 'codex_phase2_';

    protected function setUp(): void
    {
        mysqli_report(MYSQLI_REPORT_OFF);
        $this->server = @new mysqli('localhost', 'root', '', '');
        if ($this->server->connect_errno) {
            self::markTestSkipped('MySQL lokal tidak tersedia: ' . $this->server->connect_error);
        }
        $this->folder = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('gcbt_restore_', true);
        if (!mkdir($this->folder, 0775, TRUE)) {
            self::fail('Folder sementara tidak dapat dibuat.');
        }
        $this->live = $this->prefix . 'live_' . bin2hex(random_bytes(3));
        $this->server->query('CREATE DATABASE `' . $this->live . '` CHARACTER SET utf8mb4');
        $this->installSchema($this->live, 'LAMA');
    }

    protected function tearDown(): void
    {
        if ($this->server instanceof mysqli) {
            $result = $this->server->query("SHOW DATABASES LIKE '" . $this->prefix . "%'");
            if ($result) {
                while ($row = $result->fetch_row()) {
                    $this->server->query('DROP DATABASE IF EXISTS `' . $row[0] . '`');
                }
            }
            $this->server->close();
        }
        foreach (glob($this->folder . DIRECTORY_SEPARATOR . '*') as $file) {
            @unlink($file);
        }
        @rmdir($this->folder);
    }

    public function testValidBackupIsRestoredThroughStaging(): void
    {
        $zip = $this->zipWith($this->dump('BARU'));

        $this->restorer()->restore($zip);

        self::assertSame('BARU', $this->setting($this->live));
        self::assertSame(1, $this->tableCount($this->live, 'users'));
        self::assertSame(0, $this->leftoverDatabases(), 'Database staging/rollback harus dihapus.');
        self::assertFileDoesNotExist($this->folder . DIRECTORY_SEPARATOR . '.restore.lock');
    }

    public function testCorruptDumpDoesNotTouchLiveDatabase(): void
    {
        $zip = $this->zipWith("SET foreign_key_checks = 0;\nCREATE TABLE `rusak` (`id` int;\n");

        try {
            $this->restorer()->restore($zip);
            self::fail('Dump rusak seharusnya ditolak.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('gagal', strtolower($error->getMessage()));
        }

        self::assertSame('LAMA', $this->setting($this->live));
        self::assertSame(0, $this->leftoverDatabases(), 'Sisa database staging harus dibersihkan.');
        self::assertFileDoesNotExist($this->folder . DIRECTORY_SEPARATOR . '.restore.lock');
    }

    public function testBackupWithoutRequiredTableIsRejectedBeforeActivation(): void
    {
        $sql = $this->dump('BARU');
        $sql = preg_replace('/CREATE TABLE `cbt_exam_attempt`[^;]+;\s*/', '', $sql);
        $zip = $this->zipWith($sql);

        try {
            $this->restorer()->restore($zip);
            self::fail('Backup tanpa tabel wajib seharusnya ditolak.');
        } catch (RuntimeException $error) {
            self::assertStringContainsString('cbt_exam_attempt', $error->getMessage());
        }

        self::assertSame('LAMA', $this->setting($this->live));
        self::assertSame(0, $this->leftoverDatabases());
    }

    public function testDangerousStatementsInDumpAreRejected(): void
    {
        $zip = $this->zipWith($this->dump('BARU') . "\nDROP DATABASE `mysql`;\n");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('tidak diizinkan');
        $this->restorer()->readBackup($zip);
    }

    public function testRestoreLockIsReportedAsMaintenanceMode(): void
    {
        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        self::assertStringContainsString(".restore.lock", $hook);
        self::assertStringContainsString('maintenanceActive()', $hook);
        self::assertStringContainsString('Pemulihan database sedang berjalan', $hook);
    }

    public function testRestoreIsBlockedWhenSafetyBackupFails(): void
    {
        $controller = file_get_contents(dirname(__DIR__) . '/application/controllers/Dbpemulihan.php');
        self::assertStringContainsString("if (!\$pengaman['ok'])", $controller);
        self::assertStringContainsString('Restore dibatalkan karena backup pengaman gagal', $controller);
        self::assertStringContainsString('private function buatBackup()', $controller);
        self::assertStringContainsString('readBackup($tmp)', $controller);
    }

    private function restorer(): Db_restore
    {
        return new Db_restore(array(
            'hostname' => 'localhost',
            'username' => 'root',
            'password' => '',
            'database' => $this->live,
            'folder' => $this->folder,
        ));
    }

    private function installSchema(string $database, string $marker): void
    {
        $tables = array(
            'users' => '`id` int NOT NULL, `username` varchar(100) NOT NULL, `password` varchar(255) NOT NULL, PRIMARY KEY (`id`)',
            'users_groups' => '`user_id` int NOT NULL, `group_id` int NOT NULL',
            'setting' => '`id_setting` int NOT NULL, `sekolah` varchar(100) NOT NULL',
            'cbt_bank_soal' => '`id_bank` int NOT NULL, `bank_nama` varchar(100) NOT NULL',
            'cbt_jadwal' => '`id_jadwal` int NOT NULL, `id_bank` int NOT NULL',
            'cbt_soal' => '`id_soal` int NOT NULL, `bank_id` int NOT NULL',
            'cbt_nilai' => '`id_nilai` int NOT NULL, `id_siswa` int NOT NULL, `id_jadwal` int NOT NULL',
            'cbt_durasi_siswa' => '`id_durasi` int NOT NULL, `id_siswa` int NOT NULL, `id_jadwal` int NOT NULL, `status` int NOT NULL',
            'cbt_exam_attempt' => '`attempt_id` char(36) NOT NULL, `id_siswa` int NOT NULL, `id_jadwal` int NOT NULL, `status` varchar(20) NOT NULL',
        );
        foreach ($tables as $table => $columns) {
            $ok = $this->server->query("CREATE TABLE `{$database}`.`{$table}` ({$columns}) ENGINE=InnoDB");
            self::assertTrue($ok, 'Gagal membuat tabel uji ' . $table . ': ' . $this->server->error);
        }
        $this->server->query("INSERT INTO `{$database}`.users VALUES (1,'admin','hash')");
        $this->server->query("INSERT INTO `{$database}`.users_groups VALUES (1,1)");
        $this->server->query("INSERT INTO `{$database}`.setting VALUES (1,'{$marker}')");
    }

    private function dump(string $marker): string
    {
        $sql = "SET foreign_key_checks = 0;\n";
        $tables = array(
            'users' => array('id', 'username', 'password'),
            'users_groups' => array('user_id', 'group_id'),
            'setting' => array('id_setting', 'sekolah'),
            'cbt_bank_soal' => array('id_bank', 'bank_nama'),
            'cbt_jadwal' => array('id_jadwal', 'id_bank'),
            'cbt_soal' => array('id_soal', 'bank_id'),
            'cbt_nilai' => array('id_nilai', 'id_siswa', 'id_jadwal'),
            'cbt_durasi_siswa' => array('id_durasi', 'id_siswa', 'id_jadwal', 'status'),
            'cbt_exam_attempt' => array('attempt_id', 'id_siswa', 'id_jadwal', 'status'),
        );
        $types = array(
            'users' => array('int', 'varchar(100)', 'varchar(255)'),
            'setting' => array('int', 'varchar(100)'),
            'cbt_exam_attempt' => array('char(36)', 'int', 'int', 'varchar(20)'),
        );
        foreach ($tables as $table => $columns) {
            $defs = array();
            foreach ($columns as $index => $column) {
                $type = isset($types[$table][$index]) ? $types[$table][$index] : 'int';
                $defs[] = '`' . $column . '` ' . $type . ' NOT NULL';
            }
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= "CREATE TABLE `{$table}` (" . implode(', ', $defs) . ") ENGINE=InnoDB;\n";
        }
        $sql .= "INSERT INTO `users` VALUES (1,'admin','hash');\n";
        $sql .= "INSERT INTO `users_groups` VALUES (1,1);\n";
        $sql .= "INSERT INTO `setting` VALUES (1,'{$marker}');\n";
        $sql .= "SET foreign_key_checks = 1;\n";
        return $sql;
    }

    private function zipWith(string $sql): string
    {
        $path = $this->folder . DIRECTORY_SEPARATOR . 'backup-' . bin2hex(random_bytes(3)) . '.sql.zip';
        $zip = new ZipArchive();
        self::assertTrue($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE);
        $zip->addFromString('backup.sql', $sql);
        $zip->close();
        return $path;
    }

    private function setting(string $database): string
    {
        $result = $this->server->query("SELECT sekolah FROM `{$database}`.setting LIMIT 1");
        return $result ? (string) $result->fetch_row()[0] : '';
    }

    private function tableCount(string $database, string $table): int
    {
        $result = $this->server->query("SELECT COUNT(*) FROM `{$database}`.`{$table}`");
        return $result ? (int) $result->fetch_row()[0] : 0;
    }

    private function leftoverDatabases(): int
    {
        $result = $this->server->query('SHOW DATABASES');
        $count = 0;
        while ($result && $row = $result->fetch_row()) {
            if (strpos($row[0], $this->live . '_restore_') === 0) {
                $count++;
            }
        }
        return $count;
    }
}