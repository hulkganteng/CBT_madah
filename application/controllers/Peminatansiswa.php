<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Peminatansiswa extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) redirect('auth');
        if (!$this->ion_auth->is_admin()) show_error('Hanya Administrator yang dapat mengelola peminatan siswa.', 403);
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->library('student_specialization_service');
    }

    public function index()
    {
        $search = trim((string) $this->input->get('q', TRUE));
        $major = (string) $this->input->get('major', TRUE);
        $major = $major === 'none' ? 'none' : (ctype_digit($major) && (int) $major > 0 ? $major : '');
        $class = (string) $this->input->get('class', TRUE);
        $class = $class === 'none' ? 'none' : (ctype_digit($class) && (int) $class > 0 ? $class : '');
        $level = (string) $this->input->get('level', TRUE);
        if (!in_array($level, array('10', '11', '12'), TRUE)) $level = '';
        $program = (string) $this->input->get('program', TRUE);
        if (!in_array($program, array('TAHFIDZ', 'KITAB'), TRUE)) $program = '';
        $perPage = (int) $this->input->get('per_page', TRUE);
        if (!in_array($perPage, array(10, 25, 50, 100), TRUE)) $perPage = 25;
        $filters = compact('search', 'major', 'class', 'level', 'program', 'perPage');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $tpId = $tp ? (int) $tp->id_tp : 0;
        $smtId = $smt ? (int) $smt->id_smt : 0;
        $this->studentQuery($filters, $tpId, $smtId);
        $total = $this->db->count_all_results();
        $pages = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $this->input->get('page', TRUE)), $pages);
        $this->studentQuery($filters, $tpId, $smtId);
        $students = $this->db->select('s.id_siswa, s.nama, s.nis, s.nisn, s.peminatan_id, s.program_mak, j.nama_jurusan, k.nama_kelas')
            ->order_by('s.nama')->order_by('s.id_siswa')->limit($perPage, ($page - 1) * $perPage)->get()->result();
        $user = $this->ion_auth->user()->row();
        $query = array('q' => $search, 'major' => $major, 'class' => $class, 'level' => $level, 'program' => $program, 'per_page' => $perPage);
        $data = array('user' => $user, 'judul' => 'Peminatan Siswa', 'subjudul' => 'Peminatan Siswa',
            'setting' => $this->dashboard->getSetting(), 'profile' => $this->dashboard->getProfileAdmin($user->id),
            'students' => $students, 'majors' => $this->db->order_by('nama_jurusan')->get('master_jurusan')->result(),
            'classes' => $this->db->where('id_tp', $tpId)->where('id_smt', $smtId)->order_by('level_id, nama_kelas')->get('master_kelas')->result(),
            'filters' => $filters, 'query' => $query, 'page' => $page, 'pages' => $pages, 'total' => $total,
            'periodAvailable' => (bool) ($tp && $smt));
        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/siswa/peminatan', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    private function studentQuery($filters, $tpId, $smtId)
    {
        $this->db->from('master_siswa s')->join('master_jurusan j', 'j.id_jurusan = s.peminatan_id', 'left')
            ->join('kelas_siswa ks', 'ks.id_siswa = s.id_siswa AND ks.id_tp = ' . $tpId . ' AND ks.id_smt = ' . $smtId, 'left')
            ->join('master_kelas k', 'k.id_kelas = ks.id_kelas AND k.id_tp = ks.id_tp AND k.id_smt = ks.id_smt', 'left');
        if ($filters['search'] !== '') $this->db->group_start()->like('s.nama', $filters['search'])->or_like('s.nis', $filters['search'])->or_like('s.nisn', $filters['search'])->group_end();
        if ($filters['major'] === 'none') $this->db->where('s.peminatan_id IS NULL', NULL, FALSE);
        elseif ($filters['major'] !== '') $this->db->where('s.peminatan_id', (int) $filters['major']);
        if ($filters['class'] === 'none') {
            if (!$tpId || !$smtId) $this->db->where('1 = 0', NULL, FALSE);
            else $this->db->where('k.id_kelas IS NULL', NULL, FALSE);
        }
        elseif ($filters['class'] !== '') $this->db->where('k.id_kelas', (int) $filters['class']);
        if ($filters['level'] !== '') $this->db->where('k.level_id', (int) $filters['level']);
        if ($filters['program'] !== '') $this->db->where('s.program_mak', $filters['program']);
    }

    public function save()
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $id = (int) $this->input->post('id_siswa', TRUE);
        if (!$id || !$this->db->where('id_siswa', $id)->count_all_results('master_siswa')) show_404();
        try {
            $data = $this->student_specialization_service->normalizeForUpdate($id);
            if (!$this->db->where('id_siswa', $id)->update('master_siswa', $data)) throw new RuntimeException('Peminatan gagal disimpan.');
            $this->session->set_flashdata('success', 'Peminatan siswa berhasil disimpan.');
        } catch (InvalidArgumentException $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        } catch (RuntimeException $e) {
            $this->session->set_flashdata('error', $e->getMessage());
        }
        redirect('peminatansiswa' . ($this->input->server('QUERY_STRING') ? '?' . $this->input->server('QUERY_STRING') : ''));
    }

    public function remove()
    {
        if ($this->input->method(TRUE) !== 'POST') show_error('Metode tidak diizinkan.', 405);
        $id = (int) $this->input->post('id_siswa', TRUE);
        if (!$id || !$this->db->where('id_siswa', $id)->count_all_results('master_siswa')) show_404();
        $ok = $this->db->where('id_siswa', $id)->update('master_siswa', array('peminatan_id' => NULL, 'program_mak' => NULL));
        $this->session->set_flashdata($ok ? 'success' : 'error', $ok ? 'Peminatan siswa berhasil dihapus.' : 'Peminatan gagal dihapus.');
        redirect('peminatansiswa' . ($this->input->server('QUERY_STRING') ? '?' . $this->input->server('QUERY_STRING') : ''));
    }
}
