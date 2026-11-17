<?php

use PHPUnit\Framework\TestCase;

final class QuestionSaveRegressionTest extends TestCase
{
    public function testComplexQuestionSaveHandlesMissingSelectedAnswersSafely(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtbanksoal.php');

        self::assertStringContainsString(
            'is_array($jawaban_pg2) ? array_values($jawaban_pg2) : []',
            $source
        );
        self::assertStringNotContainsString(
            '$jwb_pg2 = count($this->input->post(',
            $source
        );
        self::assertStringNotContainsString(
            'if (!($i <= $jwb_pg2))',
            $source
        );
    }

    public function testQuestionSaveChecksDatabaseResultBeforeReportingSuccess(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtbanksoal.php');
        $javascript = file_get_contents(dirname(__DIR__) . '/assets/app/js/cbt/banksoal/soal.js');

        self::assertStringContainsString('if (!$saved) { goto dbSaveFailed; }', $source);
        self::assertStringContainsString('dbSaveFailed:', $source);
        self::assertStringContainsString('data.error || "Soal gagal disimpan ke database."', $javascript);
    }
}
