<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Penempatansiswa extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ion_auth');
        $this->load->model('Dashboard_model', 'dashboard');
        if (!$this->ion_auth->logged_in()) redirect('auth');
        if (!$this->ion_auth->is_admin()) show_error('Hanya Administrator yang dapat mengelola penempatan siswa.', 403);
    }

    public function index()
    {
        $period = $this->period();
        $user = $this->ion_auth->user()->row();
        $data = array(
            'user' => $user, 'judul' => 'Rombel', 'subjudul' => 'Penempatan Siswa',
            'setting' => $this->dashboard->getSetting(),
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'kelas' => $this->db->where($period)->order_by('level_id, nama_kelas')->get('master_kelas')->result(),
            'peminatan' => $this->db->where('status', 1)->order_by('nama_jurusan')->get('master_jurusan')->result(),
        );
        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('master/kelas/penempatan', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function data()
    {
        $period = $this->period();
        $target = (int) $this->input->post('id_kelas', TRUE);
        $mode = $this->input->post('mode', TRUE) === 'move' ? 'move' : 'unassigned';
        $query = $this->db->select('s.id_siswa,s.nis,s.nisn,s.nama,s.peminatan_id,s.program_mak,j.nama_jurusan,k.id_kelas,k.nama_kelas')
            ->from('master_siswa s')->join('master_jurusan j', 'j.id_jurusan=s.peminatan_id', 'left')
            ->join('kelas_siswa ks', 'ks.id_siswa=s.id_siswa AND ks.id_tp=' . $period['id_tp'] . ' AND ks.id_smt=' . $period['id_smt'], 'left')
            ->join('master_kelas k', 'k.id_kelas=ks.id_kelas', 'left');
        if ($mode === 'move') $query->where('ks.id_kelas IS NOT NULL', NULL, FALSE)->where('ks.id_kelas !=', $target);
        else $query->where('ks.id_siswa IS NULL', NULL, FALSE);
        if ($this->input->post('peminatan_id', TRUE)) $query->where('s.peminatan_id', (int) $this->input->post('peminatan_id', TRUE));
        if ($this->input->post('program_mak', TRUE)) $query->where('s.program_mak', $this->input->post('program_mak', TRUE));
        $search = trim((string) $this->input->post('search', TRUE));
        if ($search !== '') $query->group_start()->like('s.nama', $search)->or_like('s.nis', $search)->or_like('s.nisn', $search)->group_end();
        $candidates = $query->order_by('s.nama')->limit(500)->get()->result();
        $roster = $target ? $this->db->select('s.id_siswa,s.nis,s.nama,j.nama_jurusan,s.program_mak')->from('kelas_siswa ks')
            ->join('master_siswa s', 's.id_siswa=ks.id_siswa')->join('master_jurusan j', 'j.id_jurusan=s.peminatan_id', 'left')
            ->where($period)->where('ks.id_kelas', $target)->order_by('s.nama')->get()->result() : array();
        $this->json(array('candidates' => $candidates, 'roster' => $roster));
    }

    public function save()
    {
        $period = $this->period();
        $target = (int) $this->input->post('id_kelas', TRUE);
        $ids = array_values(array_filter(array_map('intval', (array) $this->input->post('siswa'))));
        $mode = $this->input->post('mode', TRUE) === 'move' ? 'move' : 'unassigned';
        if (!$target || !$ids || !$this->db->where($period)->where('id_kelas', $target)->count_all_results('master_kelas')) {
            return $this->json(array('status' => FALSE, 'message' => 'Rombel tujuan dan siswa wajib dipilih.'));
        }
        $this->db->trans_begin();
        foreach ($ids as $id) {
            $existing = $this->db->get_where('kelas_siswa', $period + array('id_siswa' => $id))->row();
            if ($existing && $mode !== 'move') {
                $this->db->trans_rollback();
                return $this->json(array('status' => FALSE, 'message' => 'Ada siswa yang sudah mempunyai rombel. Gunakan mode Pindahkan Siswa.'));
            }
            if ($existing) $this->db->where('id_kelas_siswa', $existing->id_kelas_siswa)->update('kelas_siswa', array('id_kelas' => $target));
            else $this->db->insert('kelas_siswa', $period + array('id_siswa' => $id, 'id_kelas' => $target));
        }
        if ($this->db->trans_status() === FALSE) {
            $this->db->trans_rollback();
            return $this->json(array('status' => FALSE, 'message' => 'Penempatan gagal; seluruh perubahan dibatalkan.'));
        }
        $this->db->trans_commit();
        $this->json(array('status' => TRUE, 'message' => count($ids) . ' siswa berhasil ditempatkan.'));
    }

    public function remove()
    {
        $period = $this->period();
        $id = (int) $this->input->post('id_siswa', TRUE);
        $this->db->trans_begin();
        $this->db->where($period)->where('id_siswa', $id)->delete('kelas_siswa');
        $ok = $this->db->affected_rows() === 1 && $this->db->trans_status();
        $ok ? $this->db->trans_commit() : $this->db->trans_rollback();
        $this->json(array('status' => $ok, 'message' => $ok ? 'Siswa dikeluarkan dari rombel.' : 'Penempatan siswa tidak ditemukan.'));
    }

    private function period()
    {
        $tp = $this->db->select('id_tp')->get_where('master_tp', array('active' => 1))->row();
        $smt = $this->db->select('id_smt')->get_where('master_smt', array('active' => 1))->row();
        if (!$tp || !$smt) show_error('Tahun pelajaran atau semester aktif belum ditentukan.', 503);
        return array('id_tp' => (int) $tp->id_tp, 'id_smt' => (int) $smt->id_smt);
    }

    private function json($data)
    {
        $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
