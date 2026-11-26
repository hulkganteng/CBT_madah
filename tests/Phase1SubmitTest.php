<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Controller')) {
    #[AllowDynamicProperties]
    class CI_Controller
    {
    }
}
require_once dirname(__DIR__) . '/application/controllers/Siswa.php';
require_once dirname(__DIR__) . '/application/controllers/Cbtnilai.php';
require_once dirname(__DIR__) . '/application/controllers/Cbtrekap.php';
require_once dirname(__DIR__) . '/application/controllers/Cbtanalisis.php';

final class Phase1SubmitTest extends TestCase
{
    public function testGradingFailureKeepsAttemptOpenAndRollsBack(): void
    {
        $db = new Phase1SubmitDb();
        $controller = $this->controller($db, FALSE);

        $controller->selesaiUjian();

        self::assertFalse($controller->response['status']);
        self::assertFalse($controller->response['status_nilai']);
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
        self::assertSame(0, $db->progressUpdates);
        self::assertSame(0, $controller->cbt->calls);
    }

    public function testProgressUpdateFailureRollsBackGrading(): void
    {
        $db = new Phase1SubmitDb();
        $db->updateResult = FALSE;
        $controller = $this->controller($db, TRUE);

        $controller->selesaiUjian();

        self::assertFalse($controller->response['status']);
        self::assertFalse($controller->response['status_nilai']);
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
        self::assertSame(0, $controller->cbt->calls);
    }

    public function testSuccessfulSubmitCommitsAndLogsOnce(): void
    {
        $db = new Phase1SubmitDb();
        $controller = $this->controller($db, TRUE);

        $controller->selesaiUjian();

        self::assertTrue($controller->response['status']);
        self::assertTrue($controller->response['status_nilai']);
        self::assertTrue($db->committed);
        self::assertSame(1, $db->progressUpdates);
        self::assertSame(1, $db->attemptUpdates);
        self::assertSame(1, $controller->cbt->calls);
    }

    public function testRepeatedSubmitDoesNotRegrade(): void
    {
        $db = new Phase1SubmitDb();
        $db->status = 2;
        $controller = $this->controller($db, TRUE);

        $controller->selesaiUjian();

        self::assertTrue($controller->response['status']);
        self::assertTrue($controller->response['status_nilai']);
        self::assertTrue($controller->response['sudah_selesai']);
        self::assertSame(0, $controller->gradingCalls);
        self::assertSame(0, $db->progressUpdates);
    }

    public function testAutomaticSubmitWaitsForTheDeadlineAfterAProctorExtendsIt(): void
    {
        $db = new Phase1SubmitDb();
        $db->attempt = (object) [
            'status' => 'IN_PROGRESS',
            'started_at' => date('Y-m-d H:i:s', time() - 600),
            'deadline_at' => date('Y-m-d H:i:s', time() + 300),
            'extra_minutes' => 5,
        ];
        $controller = $this->controller($db, TRUE);
        $controller->input->data['waktu_habis'] = '1';
        require_once dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';
        $controller->exam_attempt_service = (new ReflectionClass(Exam_attempt_service::class))->newInstanceWithoutConstructor();

        $controller->selesaiUjian();

        self::assertFalse($controller->response['status']);
        self::assertGreaterThan(0, $controller->response['timing']['sisa_detik']);
        self::assertSame(0, $controller->gradingCalls);
        self::assertTrue($db->rolledBack);

        $db->attempt->deadline_at = date('Y-m-d H:i:s', time() - 1);
        $controller->selesaiUjian();

        self::assertTrue($controller->response['status_nilai']);
        self::assertSame(1, $controller->gradingCalls);
        self::assertTrue($db->committed);
    }

    public function testFinishedDurationWithoutGradeIsRepaired(): void
    {
        $db = new Phase1SubmitDb();
        $db->status = 2;
        $controller = $this->controller($db, TRUE);
        $controller->cbt->hasGrade = FALSE;

        $controller->selesaiUjian();

        self::assertTrue($controller->response['status']);
        self::assertSame(1, $controller->gradingCalls);
        self::assertSame(0, $db->progressUpdates);
    }

    public function testLateRecoveryGradesUsingStoredSessionDeadline(): void
    {
        $db = new Phase1SubmitDb();
        $db->attempt = (object) [
            'status' => 'IN_PROGRESS',
            'started_at' => date('Y-m-d H:i:s', time() - 1800),
            'deadline_at' => date('Y-m-d H:i:s', time() + 300),
            'session_deadline_at' => date('Y-m-d H:i:s', time() - 300),
        ];
        $controller = $this->controller($db, TRUE);
        $controller->input->data['waktu_habis'] = '1';
        require_once dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';
        $controller->exam_attempt_service = (new ReflectionClass(Exam_attempt_service::class))->newInstanceWithoutConstructor();

        $controller->selesaiUjian();

        self::assertTrue($controller->response['status_nilai']);
        self::assertSame(1, $controller->gradingCalls);
        self::assertTrue($db->committed);
        self::assertSame(2, $db->durationUpdate['status']);
        self::assertSame(1, $db->attemptUpdates);
    }

    public function testForcedFinishReportsGradingFailureAndKeepsAttemptOpen(): void
    {
        $db = new Phase1SubmitDb();
        $controller = $this->controller($db, FALSE);
        $controller->input->data['aksi'] = json_encode(['force' => ['1010'], 'log' => []]);

        $controller->applyAction();

        self::assertFalse($controller->response['update_selesai']);
        self::assertFalse($controller->response['status']);
        self::assertSame(1, $controller->gradingCalls);
        self::assertSame(0, $db->attemptUpdates);
        self::assertTrue($db->rolledBack);
    }

    public function testForcedFinishUsesTheParticipantFromTheDuration(): void
    {
        $db = new Phase1SubmitDb();
        $controller = $this->controller($db, TRUE);
        $controller->input->data['aksi'] = json_encode(['force' => ['1010']]);

        $controller->applyAction();

        self::assertTrue($controller->response['status']);
        self::assertTrue($db->committed);
        self::assertSame(3, $db->durationUpdate['reset']);
        self::assertSame(1, $db->attemptUpdates);
    }

    public function testAttemptUpdateFailureRollsBackGradeAndDuration(): void
    {
        $db = new Phase1SubmitDb();
        $db->attemptUpdateResult = FALSE;
        $controller = $this->controller($db, TRUE);

        $controller->selesaiUjian();

        self::assertFalse($controller->response['status']);
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
        self::assertSame(0, $controller->cbt->calls);
    }

    public function testCorrectionSavesZeroAndRollsBackWhenTheFinalGradeCannotBeUpdated(): void
    {
        $controller = new Phase1CorrectionController();
        $controller->input = new Phase1SubmitInput();
        $controller->input->data = ['siswa' => 1, 'jadwal' => 10, 'jenis' => 'kompleks_nilai',
            'nilai' => json_encode([['id_soal' => '10101', 'koreksi' => '0']])];
        $db = new Phase1SubmitDb();
        $controller->db = $db;
        $db->updateResult = FALSE;
        $controller->simpanKoreksi();
        self::assertSame(0, $controller->response['success']);
        self::assertTrue($db->rolledBack);

        $db->updateResult = TRUE;
        $controller->simpanKoreksi();
        self::assertSame(1, $controller->response['success']);
        self::assertSame(['kompleks_nilai' => 0.0], $db->gradeUpdate);
        self::assertTrue($db->committed);
    }

    public function testArchiveAndAnalysisAcceptSavedZeroWithoutRegrading(): void
    {
        foreach ([Cbtrekap::class, Cbtanalisis::class] as $class) {
            $controller = (new ReflectionClass($class))->newInstanceWithoutConstructor();
            $controller->load = new Phase1SubmitLoader();
            $controller->cbt = new Phase1SubmitLog();
            self::assertTrue($controller->generateNilaiUjian(10));
            $controller->cbt->hasGrade = FALSE;
            self::assertFalse($controller->generateNilaiUjian(10));
        }
    }

    public function testGradeSummaryUsesSavedZeroAndSumsAllQuestionTypes(): void
    {
        $controller = new Phase1CorrectionController();
        $applyScore = new ReflectionMethod(Cbtnilai::class, 'applyStoredScore');
        $summary = (object) ['skor_kompleks' => 25];
        $grade = (object) ['pg_nilai' => '33.33', 'kompleks_nilai' => '0.00', 'jodohkan_nilai' => '1.25',
            'isian_nilai' => '15.00', 'essai_nilai' => '12.00', 'bs_nilai' => '4.20'];

        self::assertSame(65.78, $applyScore->invoke($controller, $summary, $grade, 99));
        self::assertSame(0.0, $summary->skor_kompleks);
    }

    public function testInvalidIdentifiersAreRejected(): void
    {
        $db = new Phase1SubmitDb();
        $controller = $this->controller($db, TRUE);
        $controller->input->data = array('siswa' => '0', 'jadwal' => 'abc');

        $controller->selesaiUjian();

        self::assertFalse($controller->response['status']);
        self::assertFalse($db->begun);
    }

    private function controller(Phase1SubmitDb $db, $grading): Phase1SubmitController
    {
        $controller = new Phase1SubmitController();
        $controller->gradingResult = $grading;
        $controller->db = $db;
        $controller->input = new Phase1SubmitInput();
        $controller->input->data = array('siswa' => '1', 'jadwal' => '10');
        $controller->load = new Phase1SubmitLoader();
        $controller->cbt = new Phase1SubmitLog();
        return $controller;
    }
}

#[AllowDynamicProperties]
class Phase1SubmitController extends Siswa
{
    public $response;
    public $gradingResult = TRUE;
    public $gradingCalls = 0;

    public function __construct()
    {
    }

    public function olahNilai($id_siswa, $id_jadwal)
    {
        $this->gradingCalls++;
        return $this->gradingResult;
    }

    public function output_json($data, $encode = true)
    {
        $this->response = $data;
    }
}

#[AllowDynamicProperties]
class Phase1CorrectionController extends Cbtnilai
{
    public $response;

    public function __construct()
    {
    }

    public function output_json($data, $encode = true)
    {
        $this->response = $data;
    }
}

class Phase1SubmitInput
{
    public $data = array();

    public function post($key, $clean = FALSE)
    {
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }
}

class Phase1SubmitLoader
{
    public function library($name) {}

    public function model(...$args)
    {
    }
}

class Phase1SubmitDb
{
    public $attempt = NULL;
    public $status = 1;
    public $updateResult = TRUE;
    public $attemptUpdateResult = TRUE;
    public $begun = FALSE;
    public $committed = FALSE;
    public $rolledBack = FALSE;
    public $progressUpdates = 0;
    public $attemptUpdates = 0;
    public $gradeUpdate = [];
    public $durationUpdate = [];

    public function trans_begin()
    {
        $this->begun = TRUE;
        return TRUE;
    }

    public function query($sql, $args)
    {
        if (strpos($sql, 'FROM cbt_exam_attempt') !== FALSE) {
            return new Phase1SubmitResult($this->attempt);
        }
        return new Phase1SubmitResult((object) ['id_siswa' => 1, 'status' => $this->status, 'id_nilai' => '1010']);
    }

    public function table_exists($table)
    {
        return $table === 'cbt_exam_attempt';
    }

    public function where($key, $value = NULL)
    {
        return $this;
    }

    public function where_in($key, $values)
    {
        return $this;
    }

    public function count_all_results($table)
    {
        return 1;
    }

    public function update_batch($table, $data, $key)
    {
        return 0;
    }

    public function update($table, $data)
    {
        if ($table === 'cbt_exam_attempt') {
            $this->attemptUpdates++;
            return $this->attemptUpdateResult;
        }
        if ($table === 'cbt_nilai') {
            $this->gradeUpdate = $data;
        }
        if ($table === 'cbt_durasi_siswa') {
            $this->durationUpdate = $data;
        }
        $this->progressUpdates++;
        return $this->updateResult;
    }

    public function trans_status()
    {
        return TRUE;
    }

    public function trans_commit()
    {
        $this->committed = TRUE;
        return TRUE;
    }

    public function trans_rollback()
    {
        $this->rolledBack = TRUE;
        return TRUE;
    }
}

class Phase1SubmitResult
{
    private $row;

    public function __construct($row)
    {
        $this->row = $row;
    }

    public function row()
    {
        return $this->row;
    }
}

class Phase1SubmitLog
{
    public $calls = 0;
    public $hasGrade = TRUE;

    public function getNilaiSiswaByJadwal($schedule, $student)
    {
        return $this->hasGrade ? (object) ['pg_nilai' => 0] : NULL;
    }

    public function getAllNilaiSiswa($schedule)
    {
        return array_filter([$this->getNilaiSiswaByJadwal($schedule, 1)]);
    }

    public function saveLog($student, $schedule, $type, $text = '')
    {
        $this->calls++;
    }
}
