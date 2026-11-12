<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class FlexibleExamConfigTest extends TestCase
{
    public function testFixedExamRulesAreNoLongerEnforced(): void
    {
        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        $config = file_get_contents(dirname(__DIR__) . '/application/config/exam_baku.php');

        self::assertStringNotContainsString('applyBakuSchedule', $hook);
        self::assertStringNotContainsString('applyBakuBank', $hook);
        self::assertStringNotContainsString('applyBakuCapacity', $hook);
        self::assertStringNotContainsString('exam_baku_durasi', $config);
        self::assertStringNotContainsString('exam_baku_kapasitas', $config);
        self::assertStringContainsString('validateScheduleInput', $hook);
        self::assertStringContainsString('validateBankInput', $hook);
    }

    public function testScheduleDurationIsEditable(): void
    {
        $view = file_get_contents(dirname(__DIR__) . '/application/views/cbt/jadwal/add.php');

        self::assertStringContainsString("name='durasi_ujian'", $view);
        self::assertStringContainsString('min="1"', $view);
        self::assertStringNotContainsString('exam_baku_durasi', $view);
    }
}
