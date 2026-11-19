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
        self::assertStringContainsString('set_status_header(500)', $source);
        self::assertStringContainsString('$result["soal_id"] = $this->db->insert_id()', $source);
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

        self::assertStringContainsString('$classes = is_array($classes) ? $classes : array();', $hook);
        self::assertStringContainsString("\$_POST['kelas'] = \$classes;", $hook);
        self::assertStringContainsString("Pilih minimal satu kelas untuk bank soal.", $hook);
        self::assertStringContainsString("'bank_kelas' => serialize(\$bankClasses)", $hook);
        self::assertStringContainsString('err.message || err.error || err.Message', $view);
    }
}
