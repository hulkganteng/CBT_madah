<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pemantauan aktivitas siswa saat ujian (PRD FR-19, FR-22 + fitur tambahan):
 * status per peserta, sisa waktu, detik sejak heartbeat, hitungan pelanggaran
 * pindah tab / keluar fullscreen, serta aksi proktor: tendang, buka tendangan,
 * dan tambah waktu dengan alasan (FR-16).
 */
class Examactivity extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library(array('ion_auth', 'Exam_attempt_service'));
        $this->load->model(array('Dashboard_model' => 'dashboard', 'Cbt_model' => 'cbt', 'Dropdown_model' => 'dropdown'));
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Administrator yang dapat memantau aktivitas ujian.', 403, 'Akses Terlarang');
        }
    }

    public function index()
    {
        $user = $this->ion_auth->user()->row();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $data = array(
            'user' => $user,
            'judul' => 'Pemantauan Aktivitas Ujian',
            'subjudul' => 'Aktivitas Siswa',
            'setting' => $this->dashboard->getSetting(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'tp' => $this->dashboard->getTahun(),
            'smt' => $this->dashboard->getSemester(),
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'jadwal' => $this->ion_auth->is_admin()
                ? $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt)
                : $this->dropdown->getAllJadwalGuru($tp->id_tp, $smt->id_smt, $this->guruId($user, $tp, $smt)),
        );

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('cbt/aktivitas/data', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function data($idJadwal)
    {
        $idJadwal = (int) $idJadwal;
        $jadwal = $this->exam_attempt_service->schedule($idJadwal);
        if (!$jadwal) {
            $this->respond(array('status' => FALSE, 'message' => 'Jadwal tidak ditemukan.'), 404);
        }
        $this->respond(array(
            'status' => TRUE,
            'jadwal' => array(
                'id_jadwal' => (int) $jadwal->id_jadwal,
                'durasi_ujian' => (int) $jadwal->durasi_ujian,
                'tgl_mulai' => $jadwal->tgl_mulai,
                'tgl_selesai' => $jadwal->tgl_selesai,
            ),
            'peserta' => $this->exam_attempt_service->monitorBySchedule($idJadwal),
            'server_time' => date('Y-m-d H:i:s'),
        ));
    }

    public function kick()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $idSiswa = (int) $this->input->post('siswa');
        $attempt = $this->exam_attempt_service->attempt($idSiswa, $idJadwal);
        if (!$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Peserta belum memulai ujian.'), 404);
        }
        $this->exam_attempt_service->kick($attempt, (string) $this->input->post('alasan', TRUE));
        $this->log('Menghentikan ujian siswa id ' . $idSiswa);
        $this->respond(array('status' => TRUE, 'message' => 'Ujian peserta dihentikan.'));
    }

    public function unkick()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $idSiswa = (int) $this->input->post('siswa');
        $attempt = $this->exam_attempt_service->attempt($idSiswa, $idJadwal);
        if (!$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Peserta belum memulai ujian.'), 404);
        }
        $this->exam_attempt_service->unkick($attempt);
        $this->log('Mengizinkan kembali ujian siswa id ' . $idSiswa);
        $this->respond(array('status' => TRUE, 'message' => 'Peserta diizinkan melanjutkan.'));
    }

    /**
     * PRD FR-16: alasan wajib. Tanpa alasan, permintaan ditolak supaya
     * penambahan waktu selalu dapat diaudit di berita acara.
     */
    public function extend()
    {
        $idJadwal = (int) $this->input->post('jadwal');
        $idSiswa = (int) $this->input->post('siswa');
        $menit = (int) $this->input->post('menit');
        $alasan = trim((string) $this->input->post('alasan', TRUE));

        if ($alasan === '') {
            $this->respond(array('status' => FALSE, 'message' => 'Alasan penambahan waktu wajib diisi.'), 422);
        }
        if ($menit < 1 || $menit > 120) {
            $this->respond(array('status' => FALSE, 'message' => 'Penambahan waktu 1 sampai 120 menit.'), 422);
        }
        $attempt = $this->exam_attempt_service->attempt($idSiswa, $idJadwal);
        if (!$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Peserta belum memulai ujian.'), 404);
        }
        if (!$this->exam_attempt_service->extend($attempt, $menit, $alasan)) {
            $this->respond(array('status' => FALSE, 'message' => 'Ujian peserta tidak sedang berjalan.'), 409);
        }
        $this->log('Menambah ' . $menit . ' menit untuk siswa id ' . $idSiswa);
        $this->respond(array('status' => TRUE, 'message' => 'Waktu peserta ditambah ' . $menit . ' menit.'));
    }

    public function detail($attemptId)
    {
        $attempt = $this->db->get_where('cbt_exam_attempt', array('attempt_id' => $attemptId))->row();
        if (!$attempt) {
            $this->respond(array('status' => FALSE, 'message' => 'Attempt tidak ditemukan.'), 404);
        }
        $events = $this->db->select('event_type, event_time, metadata')
            ->where('attempt_id', $attemptId)
            ->order_by('event_time', 'DESC')
            ->limit(100)
            ->get('cbt_exam_event')->result();
        $this->respond(array('status' => TRUE, 'attempt' => $attempt, 'events' => $events));
    }

    private function guruId($user, $tp, $smt)
    {
        $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        return $guru ? $guru->id_guru : 0;
    }

    private function log($pesan)
    {
        $this->load->model('Log_model', 'logging');
        $this->logging->saveLog(4, $pesan);
    }

    private function respond($payload, $status = 200)
    {
        $this->output->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode($payload));
        if ($status >= 400) {
            $this->output->_display();
            exit;
        }
    }
}
