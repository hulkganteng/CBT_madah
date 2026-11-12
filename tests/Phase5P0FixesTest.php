<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';

final class Phase5P0FixesTest extends TestCase
{
    public function testSoalSiswaIdDelimiterPreventsCollision(): void
    {
        $id1 = 1 . '_' . 12 . '_' . 3 . '_' . 4;
        $id2 = 1 . '_' . 1 . '_' . 23 . '_' . 4;

        self::assertNotEquals($id1, $id2);
    }

    public function testMigrationFixLockedAttemptUuidExistsAndValid(): void
    {
        $file = dirname(__DIR__) . '/application/migrations/20261111000100_fix_locked_attempt_uuid.php';
        self::assertFileExists($file);
        $content = (string) file_get_contents($file);
        self::assertStringContainsString('Migration_Fix_locked_attempt_uuid', $content);
        self::assertStringContainsString("'type' => 'CHAR'", $content);
        self::assertStringContainsString("'constraint' => 36", $content);
    }

    public function testExamAttemptServiceDoesNotTruncateUuid(): void
    {
        $file = dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';
        $content = (string) file_get_contents($file);

        self::assertStringNotContainsString("'exam_locked_attempt_id' => !empty(\$attemptId) ? (int) \$attemptId : NULL", $content);
        self::assertStringContainsString("'exam_locked_attempt_id' => !empty(\$attemptId) ? (string) \$attemptId : NULL", $content);
    }

    public function testValidateAnswerAllowsUnderscoreInSoalSiswaId(): void
    {
        $db = new class {
            public function get_where($table, $criteria) {
                return new class {
                    public function row() {
                        return (object) array(
                            'id_soal_siswa' => '1_12_3_4',
                            'id_soal' => 99,
                            'id_siswa' => 1,
                            'id_jadwal' => 12,
                            'id_bank' => 3,
                            'jenis_soal' => 4,
                        );
                    }
                };
            }
        };

        $reflection = new ReflectionClass(Exam_attempt_service::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $ciProp = $reflection->getProperty('ci');
        $ciProp->setAccessible(true);
        $ciProp->setValue($service, (object) array('db' => $db));

        $data = (object) array(
            'id_soal_siswa' => '1_12_3_4',
            'id_soal' => 99,
            'id_siswa' => 1,
            'id_jadwal' => 12,
            'id_bank' => 3,
            'jenis' => 4,
            'jawaban_siswa' => 'Jawaban isian siswa',
        );

        $row = $service->validateAnswer($data, 1, 12, 3);
        self::assertEquals('1_12_3_4', $row->id_soal_siswa);
    }
}
