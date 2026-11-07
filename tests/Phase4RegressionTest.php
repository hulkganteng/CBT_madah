<?php

use PHPUnit\Framework\TestCase;

if (!class_exists('CI_Controller')) {
    #[AllowDynamicProperties]
    class CI_Controller
    {
    }
}
require_once dirname(__DIR__) . '/application/controllers/Siswa.php';
require_once dirname(__DIR__) . '/application/libraries/Exam_attempt_service.php';

/**
 * Regresi Fase 4: kegagalan update pada perpanjangan waktu harus rollback,
 * dan submit ganda tidak boleh menilai dua kali.
 */
final class Phase4RegressionTest extends TestCase
{
    public function testExtendRejectsAttemptThatIsNotInProgress(): void
    {
        $db = new Phase4FakeDb();
        $service = $this->service($db);

        self::assertFalse($service->extend($this->attempt('SUBMITTED', 0), 10, 'uji'));
        self::assertSame(0, $db->updates);
        self::assertFalse($db->begun);
    }

    public function testExtendRollsBackWhenDurationUpdateFails(): void
    {
        $db = new Phase4FakeDb();
        $db->failOn = 'cbt_durasi_siswa';
        $db->legacyRow = (object) array('id_durasi' => 7, 'mulai' => '2026-01-01 09:00:00');
        $service = $this->service($db);

        self::assertFalse($service->extend($this->attempt('IN_PROGRESS', 0), 10, 'uji'));
        self::assertTrue($db->begun);
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
    }

    public function testExtendRollsBackWhenTransactionStatusFails(): void
    {
        $db = new Phase4FakeDb();
        $db->status = FALSE;
        $service = $this->service($db);

        self::assertFalse($service->extend($this->attempt('IN_PROGRESS', 0), 10, 'uji'));
        self::assertTrue($db->rolledBack);
        self::assertFalse($db->committed);
    }

    public function testExtendUpdatesAttemptAndDurationThenRecordsEvent(): void
    {
        $db = new Phase4FakeDb();
        $db->legacyRow = (object) array('id_durasi' => 7, 'mulai' => '2026-01-01 09:00:00');
        $service = $this->service($db);

        self::assertTrue($service->extend($this->attempt('IN_PROGRESS', 5), 10, 'alasan'));
        self::assertTrue($db->committed);
        self::assertFalse($db->rolledBack);
        self::assertSame(array('cbt_exam_attempt', 'cbt_durasi_siswa'), $db->updatedTables);
        // Nilai attempt yang dikirim pemanggil sudah basi; extend memakai ulang
        // baris yang dikunci (extra_minutes=0) supaya dua proktor tidak saling timpa.
        self::assertSame(10, $db->attemptUpdate['extra_minutes']);
        self::assertSame('2026-01-01 10:10:00', $db->attemptUpdate['deadline_at']);
        self::assertSame('2026-01-01 09:10:00', $db->durationUpdate['mulai']);
        self::assertSame('TIME_EXTENDED', $db->lastEvent['event_type']);
    }

    public function testDoubleSubmitShortCircuitsWithoutRegrading(): void
    {
        $db = new Phase4SubmitDb();
        $controller = new Phase4SubmitController();
        $controller->db = $db;
        $controller->input = new Phase4SubmitInput();
        $controller->input->data = array('siswa' => '1', 'jadwal' => '10');
        $controller->load = new Phase4FakeLoader();
        $controller->cbt = new Phase4SubmitLog();

        // Request pertama selesai (status 2), request kedua tidak menilai ulang.
        $controller->selesaiUjian();
        $controller->selesaiUjian();

        self::assertSame(1, $controller->gradingCalls);
        self::assertTrue($controller->response['sudah_selesai']);
        self::assertSame(1, $db->completedUpdates);
    }

    private function service(Phase4FakeDb $db): Exam_attempt_service
    {
        $ci = new stdClass();
        $ci->db = $db;
        $ci->config = new Phase4FakeConfig();
        $ci->input = new Phase4FakeInput();
        $ci->load = new Phase4FakeLoader();

        $reflection = new ReflectionClass(Exam_attempt_service::class);
        $service = $reflection->newInstanceWithoutConstructor();
        $property = $reflection->getProperty('ci');
        $property->setAccessible(TRUE);
        $property->setValue($service, $ci);
        return $service;
    }

    private function attempt(string $status, int $extra): object
    {
        return (object) array(
            'attempt_id' => 'attempt-1',
            'status' => $status,
            'id_siswa' => 1,
            'id_jadwal' => 10,
            'extra_minutes' => $extra,
            'deadline_at' => '2026-01-01 10:00:00',
        );
    }
}

#[AllowDynamicProperties]
class Phase4FakeDb
{
    public $begun = FALSE;
    public $committed = FALSE;
    public $rolledBack = FALSE;
    public $status = TRUE;
    public $failOn = NULL;
    public $lockedRow = TRUE;
    public $legacyRow = NULL;
    public $updates = 0;
    public $updatedTables = array();
    public $attemptUpdate = array();
    public $durationUpdate = array();
    public $lastEvent = NULL;

    public function trans_begin()
    {
        $this->begun = TRUE;
        return TRUE;
    }

    public function query($sql, $args = NULL)
    {
        return new Phase4FakeResult($this->lockedRow
            ? (object) array('deadline_at' => '2026-01-01 10:00:00', 'extra_minutes' => 0)
            : NULL);
    }

    public function select($what, $escape = NULL)
    {
        return $this;
    }

    public function where($key, $value = NULL)
    {
        return $this;
    }

    public function update($table, $data)
    {
        $this->updates++;
        $this->updatedTables[] = $table;
        if ($this->failOn === $table) {
            // CI3 menandai trans_status FALSE bila query dalam transaksi gagal.
            $this->status = FALSE;
            return FALSE;
        }
        if ($table === 'cbt_exam_attempt') {
            $this->attemptUpdate = $data;
        } elseif ($table === 'cbt_durasi_siswa') {
            $this->durationUpdate = $data;
        }
        return TRUE;
    }

    public function get_where($table, $where)
    {
        return new Phase4FakeResult($this->legacyRow);
    }

    public function count_all_results($table)
    {
        return 0;
    }

    public function insert($table, $data)
    {
        $this->lastEvent = $data;
        return TRUE;
    }

    public function trans_status()
    {
        return $this->status;
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

class Phase4FakeResult
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

class Phase4FakeConfig
{
    public function item($key, $group = NULL)
    {
        return 'test-key';
    }
}

class Phase4FakeInput
{
    public $data = array();

    public function ip_address()
    {
        return '127.0.0.1';
    }

    public function post($key, $clean = FALSE)
    {
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }
}

class Phase4FakeLoader
{
    public function database()
    {
    }

    public function model(...$args)
    {
    }
}

#[AllowDynamicProperties]
class Phase4SubmitController extends Siswa
{
    public $response;
    public $gradingCalls = 0;

    public function __construct()
    {
    }

    public function olahNilai($id_siswa, $id_jadwal)
    {
        $this->gradingCalls++;
        return TRUE;
    }

    public function output_json($data, $encode = true)
    {
        $this->response = $data;
    }
}

class Phase4SubmitInput
{
    public $data = array();

    public function post($key, $clean = FALSE)
    {
        return isset($this->data[$key]) ? $this->data[$key] : NULL;
    }
}

class Phase4SubmitDb
{
    public $completedUpdates = 0;

    public function trans_begin()
    {
        return TRUE;
    }

    public function query($sql, $args)
    {
        // Setelah satu update 'status' => 2, submit berikutnya melihat status 2.
        return new Phase4FakeResult((object) array('status' => $this->completedUpdates > 0 ? 2 : 1));
    }

    public function where($key, $value = NULL)
    {
        return $this;
    }

    public function update($table, $data)
    {
        if (isset($data['status']) && (int) $data['status'] === 2) {
            $this->completedUpdates++;
        }
        return TRUE;
    }

    public function trans_status()
    {
        return TRUE;
    }

    public function trans_commit()
    {
        return TRUE;
    }

    public function trans_rollback()
    {
        return TRUE;
    }
}

class Phase4SubmitLog
{
    public function saveLog($student, $schedule, $type, $text = '')
    {
    }
}
