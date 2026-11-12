<?php

use PHPUnit\Framework\TestCase;

class Phase6ExamIndexAndQueryOptimizationTest extends TestCase
{
    public function testMigrationFileExistsAndSyntaxValid(): void
    {
        $migrationFile = __DIR__ . '/../application/migrations/20261111000400_exam_index_dedup_and_query_optimization.php';
        $this->assertFileExists($migrationFile);

        $output = [];
        $exitCode = 0;
        exec('php -l ' . escapeshellarg($migrationFile), $output, $exitCode);
        $this->assertSame(0, $exitCode, 'Migration file has PHP syntax error');
    }

    public function testMigrationConfigMatchesLatestVersion(): void
    {
        $configFile = __DIR__ . '/../application/config/migration.php';
        $this->assertFileExists($configFile);

        $content = file_get_contents($configFile);
        $this->assertMatchesRegularExpression(
            "/\\\$config\\['migration_version'\\]\\s*=\\s*20261111000400;/",
            $content,
            'Migration version must be bumped to 20261111000400'
        );
    }

    public function testCbtModelQuerySanitization(): void
    {
        $cbtModelFile = __DIR__ . '/../application/models/Cbt_model.php';
        $this->assertFileExists($cbtModelFile);

        $content = file_get_contents($cbtModelFile);

        // Verify raw concatenations are eliminated
        $this->assertStringNotContainsString('id_siswa=" . $siswa_id . " AND id_jadwal=" . $id_jadwal', $content);
        $this->assertStringNotContainsString('"id_jadwal=" . $id_jadwal', $content);
        $this->assertStringNotContainsString('"id_siswa=" . $id_siswa', $content);

        // Verify parameterized calls are present
        $this->assertStringContainsString('["id_siswa" => $siswa_id, "id_jadwal" => $id_jadwal]', $content);
        $this->assertStringContainsString('["id_jadwal" => $id_jadwal]', $content);
        $this->assertStringContainsString('["id_siswa" => $id_siswa]', $content);

        $output = [];
        $exitCode = 0;
        exec('php -l ' . escapeshellarg($cbtModelFile), $output, $exitCode);
        $this->assertSame(0, $exitCode, 'Cbt_model.php has PHP syntax error');
    }

    public function testMigrationClassImplementsUpAndDown(): void
    {
        $migrationFile = __DIR__ . '/../application/migrations/20261111000400_exam_index_dedup_and_query_optimization.php';
        $content = file_get_contents($migrationFile);

        $this->assertStringContainsString('class Migration_Exam_index_dedup_and_query_optimization extends CI_Migration', $content);
        $this->assertStringContainsString('public function up()', $content);
        $this->assertStringContainsString('public function down()', $content);
        $this->assertStringContainsString('idx_soal_siswa_jadwal_siswa', $content);
        $this->assertStringContainsString('idx_log_time', $content);
        $this->assertStringContainsString('idjawal_relation', $content);
        $this->assertStringContainsString('id_bank_soal', $content);
    }
}
