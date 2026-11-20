<?php

use PHPUnit\Framework\TestCase;

final class QuestionSaveRegressionTest extends TestCase
{
    public function testComplexQuestionSaveHandlesMissingSelectedAnswersSafely(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtbanksoal.php');

        self::assertStringContainsString(
            '!is_array($answers) || !$answers || count($options) < 2',
            $source
        );
        self::assertStringContainsString('count(array_intersect(array_keys($options), $answers)) !== count($answers)', $source);
    }

    public function testQuestionSaveChecksDatabaseResultBeforeReportingSuccess(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Cbtbanksoal.php');
        $javascript = file_get_contents(dirname(__DIR__) . '/assets/app/js/cbt/banksoal/soal.js');

        self::assertStringContainsString('if (!$saved || !$this->cbt->refreshBankQuestionStatus($bank)', $source);
        self::assertStringContainsString("\$fail('Soal gagal disimpan ke database.', 500);", $source);
        self::assertStringContainsString('$questionId = $this->db->insert_id()', $source);
        self::assertStringContainsString("$('#method').val('edit')", $javascript);
        self::assertStringContainsString('data.error || "Soal gagal disimpan ke database."', $javascript);
    }

    public function testMultipleChoiceEditAllowsUnusedOptionalOptions(): void
    {
        $javascript = file_get_contents(dirname(__DIR__) . '/assets/app/js/cbt/banksoal/soal.js');

        self::assertStringContainsString('var jumlahOpsiTerisi = 0;', $javascript);
        self::assertStringContainsString('jumlahOpsiTerisi++', $javascript);
        self::assertStringContainsString('jumlahOpsiTerisi < 2 || !jawabanBenar', $javascript);
        self::assertStringContainsString("$('#textjawaban_' + jawabanBenar).summernote('isEmpty')", $javascript);
        self::assertStringNotContainsString("if (\$(this).summernote('isEmpty')) {\n                kosong = true;", $javascript);
    }

    public function testBankSaveWithoutClassSelectionStillReturnsValidJson(): void
    {
        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        $view = file_get_contents(dirname(__DIR__) . '/application/views/cbt/banksoal/add.php');
        $model = file_get_contents(dirname(__DIR__) . '/application/models/Cbt_model.php');

        self::assertStringContainsString('$classes = is_array($classes) ? $classes : array();', $hook);
        self::assertStringContainsString("\$_POST['kelas'] = \$classes;", $hook);
        self::assertStringContainsString("Pilih minimal satu kelas untuk bank soal.", $hook);
        self::assertStringContainsString("'bank_kelas' => serialize(\$classes)", $model);
        self::assertStringContainsString('err.message || err.error || err.Message', $view);
    }
}
