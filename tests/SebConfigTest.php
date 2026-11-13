<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SebConfigTest extends TestCase
{
    public function testGeneratorCreatesDownloadableSebFile(): void
    {
        require_once dirname(__DIR__) . '/tools/buat-seb.php';
        $path = tempnam(sys_get_temp_dir(), 'garuda_seb_test_');

        try {
            $result = buat_seb_config([
                'startURL' => 'https://cbt.example.test/login-siswa',
                'host' => 'cbt.example.test',
                'file' => 'Ujian.seb',
            ], $path, 'admin-test-123', 'keluar-test-123');

            self::assertGreaterThan(0, $result['bytes']);
            self::assertSame('https://cbt.example.test/login-siswa', $result['startURL']);
            self::assertSame(64, strlen($result['configKey']));
            $outer = gzdecode((string) file_get_contents($path));
            self::assertStringStartsWith('plnd', $outer);
            $xml = gzdecode(substr($outer, 4));
            self::assertStringContainsString('https://cbt.example.test/login-siswa', $xml);
            self::assertStringContainsString(hash('sha256', 'admin-test-123'), $xml);
            self::assertStringNotContainsString('admin-test-123', $xml);
            self::assertStringNotContainsString('keluar-test-123', $xml);
        } finally {
            @unlink($path);
        }
    }

    public function testAdminControllerValidatesUrlAndPasswords(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Sebconfig.php');

        self::assertStringContainsString('$this->ion_auth->is_admin()', $source);
        self::assertStringContainsString("['http', 'https']", $source);
        self::assertStringContainsString('strlen($adminPassword) < 8', $source);
        self::assertStringContainsString("Cache-Control: no-store", $source);
        self::assertStringContainsString("saveGlobalPolicy(\$result['startURL'], \$result['configKey'])", $source);
        self::assertStringContainsString("file_put_contents(\$path, \$php, LOCK_EX)", $source);
    }

    public function testMadahPresetUsesProductionDomain(): void
    {
        require dirname(__DIR__) . '/tools/buat-seb.php';

        self::assertSame('https://cbtmadah.web.id/login-siswa', $seb_presets['madah']['startURL']);
        self::assertSame('cbtmadah.web.id', $seb_presets['madah']['host']);
        self::assertSame('https://cbtmadah.bhydev.my.id/login-siswa', $seb_presets['development']['startURL']);
        self::assertSame('cbtmadah.bhydev.my.id', $seb_presets['development']['host']);
    }
}
