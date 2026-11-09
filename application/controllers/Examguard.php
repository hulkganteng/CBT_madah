<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Examguard extends CI_Controller
{
    private $student;

    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('ion_auth', 'Exam_attempt_service'));
        $this->student = $this->exam_attempt_service->authenticatedStudent();
        if (!$this->student) {
            $this->respond(array('status' => FALSE, 'message' => 'Sesi siswa tidak valid.'), 403);
        }
    }

    public function status($idJadwal)
    {
        $schedule = $this->exam_attempt_service->schedule((int) $idJadwal);
        if (!$schedule) {
            $this->respond(array('status' => FALSE, 'message' => 'Jadwal tidak ditemukan.'), 404);
        }
        $seb = $this->exam_attempt_service->verifySeb($schedule);
        $attempt = $this->exam_attempt_service->attempt($this->student->id_siswa, $idJadwal);
        $this->respond(array(
            'status' => $seb['ok'],
            'seb_mode' => $seb['mode'],
            'seb_verified' => $seb['verified'],
            'message' => $seb['reason'],
            'attempt_status' => $attempt ? $attempt->status : NULL,
            'deadline_at' => $attempt ? $attempt->deadline_at : NULL
        ), $seb['ok'] ? 200 : 403);
    }

    public function heartbeat()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $schedule = $this->exam_attempt_service->schedule($idJadwal);
        $attempt = $this->exam_attempt_service->attempt($this->student->id_siswa, $idJadwal);
        if (!$schedule || !$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Sesi ujian tidak ditemukan.'), 404);
        }
        /* Auto-submit mendapat grace yang sama agar heartbeat paralel tidak
           mengusir browser ketika pengumpulan sedang berjalan. */
        list($active, $reason) = $this->exam_attempt_service->validateActiveAttempt($attempt, 120);
        $seb = $this->exam_attempt_service->verifySeb($schedule);
        if (!$active || !$seb['ok']) {
            $this->respond(array('status' => FALSE, 'message' => $reason ?: $seb['reason']), 403);
        }
        /* Bind dulu (pakai heartbeat lama), baru heartbeat: kalau PC lain
           mengambil alih, PC ini yang harus ditolak, bukan sebaliknya. */
        list($bound, $bindReason) = $this->exam_attempt_service->bindSession($attempt);
        if (!$bound) {
            $this->respond(array('status' => FALSE, 'message' => $bindReason), 409);
        }
        list($hbOk, $hbReason) = $this->exam_attempt_service->heartbeat($attempt, $seb['verified']);
        if (!$hbOk) {
            $this->respond(array('status' => FALSE, 'message' => $hbReason), 403);
        }
        $this->respond(array(
            'status' => TRUE,
            'deadline_at' => $attempt->deadline_at,
            'sisa_detik' => max(0, strtotime($attempt->deadline_at) - time())
        ));
    }

    public function event()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $schedule = $this->exam_attempt_service->schedule($idJadwal);
        $attempt = $this->exam_attempt_service->attempt($this->student->id_siswa, $idJadwal);
        if (!$schedule || !$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Sesi ujian tidak ditemukan.'), 404);
        }
        list($active, $reason) = $this->exam_attempt_service->validateActiveAttempt($attempt);
        $seb = $this->exam_attempt_service->verifySeb($schedule);
        if (!$active || !$seb['ok']) {
            $this->respond(array('status' => FALSE, 'message' => $reason ?: $seb['reason']), 403);
        }
        list($bound, $bindReason) = $this->exam_attempt_service->bindSession($attempt);
        if (!$bound) {
            $this->respond(array('status' => FALSE, 'message' => $bindReason), 409);
        }
        $metadata = $this->input->post('metadata');
        $metadata = is_string($metadata) ? json_decode($metadata, TRUE) : array();
        $this->exam_attempt_service->recordEvent($attempt, strtoupper((string) $this->input->post('event_type', TRUE)), is_array($metadata) ? $metadata : array());
        $this->respond(array('status' => TRUE));
    }

    /**
     * PRD FR-18: penanda ragu-ragu per soal. Disimpan di server supaya
     * tetap ada saat peserta pindah PC / jalur.
     */
    public function ragu()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $idSoalSiswa = (string) $this->input->post('id_soal_siswa', TRUE);
        $schedule = $this->exam_attempt_service->schedule($idJadwal);
        $attempt = $this->exam_attempt_service->attempt($this->student->id_siswa, $idJadwal);
        if (!$schedule || !$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Sesi ujian tidak ditemukan.'), 404);
        }
        list($active, $reason) = $this->exam_attempt_service->validateActiveAttempt($attempt);
        $seb = $this->exam_attempt_service->verifySeb($schedule);
        if (!$active || !$seb['ok']) {
            $this->respond(array('status' => FALSE, 'message' => $reason ?: $seb['reason']), 403);
        }
        list($bound, $bindReason) = $this->exam_attempt_service->bindSession($attempt);
        if (!$bound) {
            $this->respond(array('status' => FALSE, 'message' => $bindReason), 409);
        }
        if (!$this->exam_attempt_service->ownsQuestion($idSoalSiswa, $this->student->id_siswa, $idJadwal, (int) $this->input->post('bank'))) {
            $this->respond(array('status' => FALSE, 'message' => 'Soal bukan milik peserta ini.'), 403);
        }
        $ragu = (int) $this->input->post('ragu') === 1 ? 1 : 0;
        $this->exam_attempt_service->heartbeat($attempt, $seb['verified']);
        $this->db->where('id_soal_siswa', $idSoalSiswa)->update('cbt_soal_siswa', array('ragu_ragu' => $ragu));
        $this->exam_attempt_service->recordEvent($attempt, $ragu ? 'RAGU_SET' : 'RAGU_UNSET', array('soal' => $idSoalSiswa));
        $this->respond(array('status' => TRUE, 'ragu' => $ragu));
    }

    private function respond($payload, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($payload));
        if ($status >= 400) {
            $this->output->_display();
            exit;
        }
    }
}
