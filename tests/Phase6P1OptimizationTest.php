<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class Phase6P1OptimizationTest extends TestCase
{
    public function testMigrationP1FileExistsAndValid(): void
    {
        $file = dirname(__DIR__) . '/application/migrations/20261111000200_optimize_critical_indexes_and_types.php';
        self::assertFileExists($file);
        $content = (string) file_get_contents($file);

        self::assertStringContainsString('Migration_Optimize_critical_indexes_and_types', $content);
        self::assertStringContainsString('BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT', $content);
        self::assertStringContainsString('idx_log_siswa_jadwal', $content);
        self::assertStringContainsString('idx_durasi_jadwal_siswa', $content);
        self::assertStringContainsString('idx_cbt_nilai_jadwal_siswa', $content);
    }

    public function testMigrationVersionConfigPointsToP1(): void
    {
        $configContent = (string) file_get_contents(dirname(__DIR__) . '/application/config/migration.php');
        self::assertMatchesRegularExpression('/\$config\[\'migration_version\'\]\s*=\s*20261111000[2-9]00;/', $configContent);
    }

    public function testDatabaseConfigHardened(): void
    {
        $dbContent = (string) file_get_contents(dirname(__DIR__) . '/application/config/database.php');
        self::assertStringContainsString("'save_queries' => in_array(ENVIRONMENT, array('development', 'testing'), TRUE)", $dbContent);
        self::assertStringContainsString("'stricton' => (bool) \$dbValue('DB_STRICT_ON', FALSE)", $dbContent);
    }

    public function testMasterSqlContainsP1Optimizations(): void
    {
        $sql = (string) file_get_contents(dirname(__DIR__) . '/assets/app/db/master.sql');
        self::assertStringContainsString('`id_durasi` bigint(20) NOT NULL', $sql);
        self::assertStringContainsString('`id_log` bigint(20) unsigned NOT NULL AUTO_INCREMENT', $sql);
        self::assertStringContainsString('KEY `idx_durasi_jadwal_siswa`', $sql);
        self::assertStringContainsString('KEY `idx_log_siswa_jadwal`', $sql);
        self::assertStringContainsString('KEY `idx_cbt_nilai_jadwal_siswa`', $sql);
    }
}
