<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Phase7P2RetentionTest extends TestCase
{
    public function testMigrationP2FileExistsAndValid(): void
    {
        $file = dirname(__DIR__) . '/application/migrations/20261111000300_exam_foreign_keys_and_retention.php';
        self::assertFileExists($file);

        $content = file_get_contents($file);
        self::assertStringContainsString('class Migration_Exam_foreign_keys_and_retention extends CI_Migration', $content);
        self::assertStringContainsString('fk_soal_siswa_jadwal', $content);
        self::assertStringContainsString('fk_durasi_siswa_jadwal', $content);
        self::assertStringContainsString('fk_nilai_jadwal', $content);
        self::assertStringContainsString('fk_log_ujian_jadwal', $content);
        self::assertStringContainsString('ON DELETE CASCADE', $content);
        self::assertStringContainsString('ON DELETE SET NULL', $content);
    }

    public function testMigrationVersionUpdatedToP2(): void
    {
        $content = file_get_contents(dirname(__DIR__) . '/application/config/migration.php');
        self::assertMatchesRegularExpression('/\$config\[\'migration_version\'\]\s*=\s*20261111000[3-9]00;/', $content);
    }

    public function testCleanupToolExistsAndHasCliHelp(): void
    {
        $tool = dirname(__DIR__) . '/tools/cleanup-exam-logs.php';
        self::assertFileExists($tool);

        $output = shell_exec('php ' . escapeshellarg($tool) . ' --help');
        self::assertIsString($output);
        self::assertStringContainsString('GarudaCBT Retention & Cleanup Tool', $output);
        self::assertStringContainsString('--days=N', $output);
        self::assertStringContainsString('--dry-run', $output);
    }
}
