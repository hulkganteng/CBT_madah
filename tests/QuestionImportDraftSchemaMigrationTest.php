<?php

use PHPUnit\Framework\TestCase;

final class QuestionImportDraftSchemaMigrationTest extends TestCase
{
    public function testRepairMigrationRestoresRequiredInsertSemantics(): void
    {
        $source = file_get_contents(
            dirname(__DIR__) . '/application/migrations/20261114000100_repair_question_import_draft_schema.php'
        );

        self::assertStringContainsString(
            'MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT',
            $source
        );
        self::assertSame(2, substr_count(
            $source,
            'MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT'
        ));
        self::assertStringContainsString("DEFAULT \\'draft\\'", $source);
        self::assertStringContainsString("'import_id', '`import_id`'", $source);
        self::assertStringContainsString("'is_valid_imported', '`is_valid`, `imported`'", $source);
    }

    public function testApplicationTargetsRepairMigration(): void
    {
        $config = file_get_contents(dirname(__DIR__) . '/application/config/migration.php');
        self::assertStringContainsString(
            "migration_version'] = 20261114000100",
            $config
        );
    }
}
