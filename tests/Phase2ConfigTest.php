<?php

use PHPUnit\Framework\TestCase;

final class Phase2ConfigTest extends TestCase
{
    public function testDatabaseCredentialsComeFromEnvironment(): void
    {
        if (!defined('ENVIRONMENT')) {
            define('ENVIRONMENT', 'production');
        }
        $previous = array();
        foreach (array('DB_HOSTNAME', 'DB_USERNAME', 'DB_PASSWORD', 'DB_DATABASE_DEVELOPMENT', 'DB_DATABASE_PRODUCTION') as $key) {
            $previous[$key] = getenv($key);
        }
        putenv('DB_HOSTNAME=db.internal.test');
        putenv('DB_USERNAME=garuda_cbt_app');
        putenv('DB_PASSWORD=rahasia-lokal');
        putenv('DB_DATABASE_DEVELOPMENT=dev_db_test');
        putenv('DB_DATABASE_PRODUCTION=prod_db_test');

        try {
            $db = array();
            include dirname(__DIR__) . '/application/config/database.php';
            self::assertSame('db.internal.test', $db['production']['hostname']);
            self::assertSame('garuda_cbt_app', $db['production']['username']);
            self::assertSame('rahasia-lokal', $db['production']['password']);
            self::assertSame('prod_db_test', $db['production']['database']);
            self::assertSame('dev_db_test', $db['development']['database']);
        } finally {
            foreach ($previous as $key => $value) {
                putenv($value === FALSE ? $key . '=' : $key . '=' . $value);
            }
        }
    }

    public function testConfigurationTechniquesDoNotLeakSecrets(): void
    {
        $config = file_get_contents(dirname(__DIR__) . '/application/config/database.php');
        self::assertStringContainsString("DB_USERNAME", $config);
        self::assertStringContainsString("DB_PASSWORD", $config);
        self::assertStringNotContainsString('var_dump', $config);
        self::assertStringNotContainsString('print_r', $config);

        $example = file_get_contents(dirname(__DIR__) . '/.env.example');
        self::assertStringContainsString('DB_USERNAME', $example);
        self::assertStringContainsString('GRANT SELECT, INSERT, UPDATE, DELETE', $example);

        $htaccess = file_get_contents(dirname(__DIR__) . '/.htaccess');
        self::assertStringContainsString('FilesMatch "^\.env"', $htaccess);
        self::assertStringContainsString('RewriteRule ^backups/ - [F,L]', $htaccess);
    }

    public function testInstallerTemplateAlsoReadsEnvironment(): void
    {
        $template = file_get_contents(dirname(__DIR__) . '/assets/app/db/database.php');
        self::assertStringContainsString("DB_USERNAME", $template);
        self::assertStringContainsString('%HOSTNAME%', $template);
        self::assertStringContainsString('%DATABASE_PRODUCTION%', $template);
    }
}