<?php

use PHPUnit\Framework\TestCase;

final class ImportSafetyTest extends TestCase
{
    public function testExcelImportNeverDeletesExistingQuestions(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Excelsoal.php');
        self::assertStringNotContainsString("delete('cbt_soal')", $source);
        self::assertStringNotContainsString('DELETE FROM CBT_SOAL', strtoupper($source));
        self::assertStringNotContainsString("value=\"replace\"", file_get_contents(dirname(__DIR__) . '/application/views/cbt/banksoal/_excel_import_draft.php'));
        self::assertStringContainsString('_excel_import_draft', file_get_contents(dirname(__DIR__) . '/application/views/cbt/banksoal/_excel_import.php'));
    }

    public function testLegacyWordImportIsNotRendered(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/views/cbt/banksoal/import.php');
        self::assertStringContainsString('import_draft', $source);
        self::assertStringNotContainsString('uploadSoal', $source);

        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        self::assertStringContainsString("'doimport'", strtolower($hook));
        self::assertStringContainsString("'uploadsoal'", strtolower($hook));
    }

    public function testCommitOnlySelectsValidUnimportedDraftItems(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Excelsoal.php');
        self::assertStringContainsString("'is_valid' => 1, 'imported' => 0", $source);
        self::assertStringContainsString("array('imported' => 1", $source);
        self::assertStringContainsString("'imported' => 0", $source);
        self::assertStringContainsString('trans_rollback()', $source);
    }
}
