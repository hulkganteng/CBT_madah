<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PHPUnit\Framework\TestCase;

final class Phase3StabilityTest extends TestCase
{
    public function testImporterRejectsWorkbooksOverTheRowLimit(): void
    {
        $rows = array(array('NO', 'JENIS', 'PERTANYAAN', 'A', 'B', 'JAWABAN'));
        for ($i = 1; $i <= 2100; $i++) {
            $rows[] = array($i, 'Pilihan Ganda', 'Soal ' . $i, 'A', 'B', 'A');
        }

        $parsed = $this->parse($rows);

        self::assertContains('Maksimal 2000 soal per file.', $parsed['file_errors']);
        self::assertLessThanOrEqual(2000, count($parsed['items']));
    }

    public function testImporterIgnoresColumnsBeyondTheReadFilter(): void
    {
        $row = array('NO', 'JENIS', 'PERTANYAAN', 'A', 'B', 'JAWABAN');
        for ($i = 0; $i < 40; $i++) {
            $row[] = 'extra-' . $i;
        }
        $parsed = $this->parse(array($row, array(1, 'Pilihan Ganda', 'Soal', 'A', 'B', 'A')));

        self::assertCount(1, $parsed['items']);
        self::assertSame(array(), $parsed['items'][0]['errors']);
    }

    public function testCommitLocksBankBeforeNumberingQuestions(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Excelsoal.php');

        self::assertStringContainsString('FOR UPDATE', $source);
        self::assertStringContainsString('cbt_bank_soal WHERE id_bank = ? FOR UPDATE', $source);
        $lock = strpos($source, 'cbt_bank_soal WHERE id_bank = ? FOR UPDATE');
        $numbering = strpos($source, 'MAX(nomor_soal)');
        self::assertNotFalse($lock);
        self::assertNotFalse($numbering);
        self::assertLessThan($numbering, $lock, 'lock harus diambil sebelum penomoran');
    }

    public function testExtendIsTransactionalAndLocksTheAttempt(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php');
        $start = strpos($source, 'public function extend(');
        $end = strpos($source, 'public function kick(', $start);
        $extend = substr($source, $start, $end - $start);

        self::assertStringContainsString('trans_begin()', $extend);
        self::assertStringContainsString('FOR UPDATE', $extend);
        self::assertStringContainsString('trans_rollback()', $extend);
        self::assertStringContainsString('trans_commit()', $extend);
        self::assertStringContainsString("trans_status() === FALSE", $extend);
        self::assertStringContainsString('TIME_EXTENDED', $extend);
    }

    public function testCriticalEventsAlwaysRecordedWhileFloodIsLimited(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php');

        self::assertStringContainsString('EVENT_LIMIT_PER_MINUTE', $source);
        self::assertStringContainsString('EVENT_DUPLICATE_WINDOW', $source);
        self::assertStringContainsString('isDuplicateEvent', $source);
        self::assertStringContainsString('eventRateAllowed', $source);
        self::assertStringContainsString("'PROCTOR_KICK'", $source);
        self::assertStringContainsString("'TIME_EXTENDED'", $source);
    }

    public function testProxyHeadersOnlyTrustedFromConfiguredProxies(): void
    {
        $config = file_get_contents(dirname(__DIR__) . '/application/config/config.php');
        $service = file_get_contents(dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php');

        self::assertStringContainsString('APP_TRUSTED_PROXIES', $config);
        self::assertStringContainsString('APP_ALLOWED_HOSTS', $config);
        self::assertStringContainsString('$proxyTrusted', $config);
        self::assertStringContainsString('trustedProxy()', $service);
    }

    public function testForgotPasswordNeverReportsFalseSuccess(): void
    {
        $library = file_get_contents(dirname(__DIR__) . '/application/libraries/Ion_auth.php');
        $config = file_get_contents(dirname(__DIR__) . '/application/config/ion_auth.php');
        $english = file_get_contents(dirname(__DIR__) . '/application/language/english/ion_auth_lang.php');
        $indonesian = file_get_contents(dirname(__DIR__) . '/application/language/indonesian/ion_auth_lang.php');

        self::assertStringContainsString('AUTH_EMAIL_ENABLED', $config);
        self::assertStringContainsString('forgot_password_email_not_configured', $library);
        self::assertStringNotContainsString(
            "if (!\$this->config->item('use_ci_email', 'ion_auth'))\n\t\t\t\t{\n\t\t\t\t\t\$this->set_message('forgot_password_successful');",
            $library
        );
        self::assertStringContainsString('forgot_password_email_not_configured', $english);
        self::assertStringContainsString('forgot_password_email_not_configured', $indonesian);
    }

    public function testIdentityColumnIsReadableFromOutsideTheModel(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/models/Ion_auth_model.php');

        self::assertMatchesRegularExpression(
            '/public\s+\$identity_column;/',
            $source,
            'Ion_auth membaca identity_column dari luar model; properti harus public'
        );
        self::assertStringNotContainsString(
            'private $identity_column;',
            $source,
            'private identity_column membuat query WHERE = \'x\' dan lupa password gagal 500'
        );
    }

    private function parse(array $rows): array
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getActiveSheet()->setTitle('SOAL')->fromArray($rows);
        $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('garuda_phase3_', true) . '.xlsx';
        (new Xlsx($spreadsheet))->save($path);
        $spreadsheet->disconnectWorksheets();

        try {
            $reflection = new ReflectionClass(Excel_question_draft_importer::class);
            $importer = $reflection->newInstanceWithoutConstructor();
            $property = $reflection->getProperty('normalizer');
            $property->setValue($importer, new Question_import());
            return $importer->parse($path);
        } finally {
            @unlink($path);
        }
    }
}
