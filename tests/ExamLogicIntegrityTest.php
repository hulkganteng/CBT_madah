<?php

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';

final class ExamLogicIntegrityTest extends TestCase
{
    public function testExpiredValidationDoesNotMutateAttempt(): void
    {
        $service = (new ReflectionClass(Exam_attempt_service::class))->newInstanceWithoutConstructor();
        $attempt = (object) array(
            'status' => 'IN_PROGRESS',
            'deadline_at' => date('Y-m-d H:i:s', time() - 30),
        );

        self::assertSame(array(FALSE, 'Waktu ujian sudah berakhir.'), $service->validateActiveAttempt($attempt, 10));
        self::assertSame('IN_PROGRESS', $attempt->status);
    }

    public function testElapsedUsesTheSameExtensionOffsetAsLegacyTimer(): void
    {
        $service = (new ReflectionClass(Exam_attempt_service::class))->newInstanceWithoutConstructor();
        $attempt = (object) array(
            'started_at' => date('Y-m-d H:i:s', time() - 600),
            'extra_minutes' => 5,
        );

        $parts = array_map('intval', explode(':', $service->elapsed($attempt)));
        $seconds = $parts[0] * 3600 + $parts[1] * 60 + $parts[2];
        self::assertGreaterThanOrEqual(299, $seconds);
        self::assertLessThanOrEqual(302, $seconds);
    }

    public function testLegacyControllerContainsTheScoringAndPoolGuards(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/controllers/Siswa.php');

        self::assertStringContainsString('array_slice($pool, 0, $limit)', $source);
        self::assertStringContainsString('count($arr_benar) - count($arr_salah)', $source);
        self::assertStringContainsString('$skor_pg2 = $s_pg2;', $source);
        self::assertStringContainsString("array_pad(array_map('intval'", $source);
    }

    public function testSchemaMigrationPreservesFractionalScoresAndLongKeys(): void
    {
        $source = file_get_contents(dirname(__DIR__) . '/application/migrations/20261109000100_exam_logic_integrity.php');

        self::assertStringContainsString("'constraint' => '6,2'", $source);
        self::assertSame(2, substr_count($source, "'constraint' => 64"));
    }

    public function testEmptyAutoSubmitAndForcedFinishUseSafePaths(): void
    {
        $view = file_get_contents(dirname(__DIR__) . '/application/views/members/siswa/cbt/ujian.php');
        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');

        self::assertStringContainsString('jsonJawaban && jsonJawaban.jenis ? $.ajax', $view);
        self::assertStringContainsString("\$method === 'applyaction'", $hook);
        self::assertStringContainsString("'status' => 'SUBMITTED'", $hook);
    }
}
