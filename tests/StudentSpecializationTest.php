<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once dirname(__DIR__) . '/application/libraries/Student_exam_filter.php';

final class StudentSpecializationTest extends TestCase
{
    public function testRequiredExamFilterScenarios(): void
    {
        $ipa = 1;
        $ips = 2;
        $mak = 4;

        self::assertTrue(Student_exam_filter::matches($ipa, null, 0, null)[0]);
        self::assertTrue(Student_exam_filter::matches($ips, null, 0, null)[0]);
        self::assertTrue(Student_exam_filter::matches($mak, 'TAHFIDZ', 0, null)[0]);
        self::assertTrue(Student_exam_filter::matches($mak, 'KITAB', 0, null)[0]);

        self::assertTrue(Student_exam_filter::matches($ipa, null, $ipa, null)[0]);
        self::assertFalse(Student_exam_filter::matches($ips, null, $ipa, null)[0]);
        self::assertTrue(Student_exam_filter::matches($ips, null, $ips, null)[0]);
        self::assertFalse(Student_exam_filter::matches($ipa, null, $ips, null)[0]);
        self::assertTrue(Student_exam_filter::matches($mak, 'TAHFIDZ', $mak, null)[0]);
        self::assertTrue(Student_exam_filter::matches($mak, 'KITAB', $mak, null)[0]);
        self::assertTrue(Student_exam_filter::matches($mak, 'TAHFIDZ', $mak, 'TAHFIDZ')[0]);
        self::assertFalse(Student_exam_filter::matches($mak, 'KITAB', $mak, 'TAHFIDZ')[0]);
        self::assertFalse(Student_exam_filter::matches(null, null, $ipa, null)[0]);
    }

    public function testBackendGuardAndTransactionalStudentWritesAreInstalled(): void
    {
        $hook = file_get_contents(dirname(__DIR__) . '/application/hooks/Exam_security_hook.php');
        $service = file_get_contents(dirname(__DIR__) . '/application/libraries/Student_specialization_service.php');
        self::assertStringContainsString('student_exam_eligibility->check', $hook);
        self::assertStringContainsString("method === 'create'", $hook);
        self::assertStringContainsString("method === 'do_import'", $hook);
        self::assertStringContainsString('trans_begin()', $service);
        self::assertStringContainsString("insert('kelas_siswa'", $service);
    }

    public function testMasterStudentAndPlacementAreSeparated(): void
    {
        $service = file_get_contents(dirname(__DIR__) . '/application/libraries/Student_specialization_service.php');
        $create = substr($service, strpos($service, 'public function createFromPost'), strpos($service, 'public function normalizeForUpdate') - strpos($service, 'public function createFromPost'));
        $placement = file_get_contents(dirname(__DIR__) . '/application/controllers/Penempatansiswa.php');
        $classView = file_get_contents(dirname(__DIR__) . '/application/views/master/kelas/add.php');

        self::assertStringNotContainsString("insert('kelas_siswa'", $create);
        self::assertStringNotContainsString("'id_kelas'", $create);
        self::assertStringContainsString("class Penempatansiswa", $placement);
        self::assertStringContainsString("mode !== 'move'", $placement);
        self::assertStringContainsString('trans_begin()', $placement);
        self::assertStringNotContainsString('name="siswa[]"', $classView);
    }
}
