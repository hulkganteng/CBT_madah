<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Controller')) {
    #[AllowDynamicProperties]
    class CI_Controller
    {
    }
}
require_once dirname(__DIR__) . '/application/controllers/Siswa.php';

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
        self::assertTrue($controller->response['status_nilai']);
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
    public function model(...$args)
    {
    }
}

class Phase1SubmitDb
{
    public $status = 1;
    public $updateResult = TRUE;
    public $begun = FALSE;
    public $committed = FALSE;
    public $rolledBack = FALSE;
    public $progressUpdates = 0;

    public function trans_begin()
    {
        $this->begun = TRUE;
        return TRUE;
    }

    public function query($sql, $args)
    {
        return new Phase1SubmitResult((object) array('status' => $this->status));
    }

    public function where($key, $value = NULL)
    {
        return $this;
    }

    public function update($table, $data)
    {
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

    public function saveLog($student, $schedule, $type, $text = '')
    {
        $this->calls++;
    }
}