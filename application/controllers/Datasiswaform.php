<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Datasiswaform extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }

        if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru') && !$this->ion_auth->in_group('tu')) {
            show_error('Anda tidak memiliki hak untuk mengakses halaman ini.', 403, 'Akses Terlarang');
        }

        $this->load->library('form_validation');
        $this->form_validation->set_error_delimiters('', '<br>');
    }

    public function create()
    {
        $this->form_validation->set_rules('nama_siswa', 'Nama siswa', 'required|trim|max_length[50]');
        $this->form_validation->set_rules(
            'nis',
            'NIS',
            'required|numeric|trim|min_length[6]|max_length[20]|is_unique[master_siswa.nis]'
        );
        $this->form_validation->set_rules(
            'nisn',
            'NISN',
            'required|numeric|trim|min_length[6]|max_length[10]|is_unique[master_siswa.nisn]'
        );
        $this->form_validation->set_rules('jenis_kelamin', 'Jenis kelamin', 'required|in_list[L,P]');
        $this->form_validation->set_rules('kelas_awal', 'Kelas awal', 'required|integer');
        $this->form_validation->set_rules('tahun_masuk', 'Tanggal diterima', 'required|trim');
        $this->form_validation->set_rules(
            'username',
            'Username',
            'required|trim|max_length[50]|is_unique[master_siswa.username]'
        );
        $this->form_validation->set_rules('password', 'Password', 'required');

        if (!$this->form_validation->run()) {
            return $this->outputJson([
                'insert' => false,
                'text' => validation_errors(),
            ]);
        }

        $nis = $this->input->post('nis', true);
        $student = [
            'nama' => $this->input->post('nama_siswa', true),
            'nis' => $nis,
            'nisn' => $this->input->post('nisn', true),
            'jenis_kelamin' => $this->input->post('jenis_kelamin', true),
            'kelas_awal' => $this->input->post('kelas_awal', true),
            'tahun_masuk' => $this->input->post('tahun_masuk', true),
            'username' => $this->input->post('username', true),
            'password' => $this->input->post('password', true),
            'foto' => 'uploads/foto_siswa/' . $nis . '.jpg',
            // These columns are NOT NULL in the bundled database schema.
            'nik' => '',
            'warga_negara' => '',
        ];

        $this->db->trans_begin();
        $this->db->set('uid', 'UUID()', false);
        $studentInserted = $this->db->insert('master_siswa', $student);

        $studentId = $this->db->insert_id();
        $savedStudent = $studentInserted
            ? $this->db->select('uid')->get_where('master_siswa', ['id_siswa' => $studentId])->row()
            : null;

        $profileInserted = $savedStudent && $this->db->insert('buku_induk', [
            'id_siswa' => $studentId,
            'uid' => $savedStudent->uid,
            'status' => 1,
        ]);

        if (!$studentInserted || !$profileInserted || $this->db->trans_status() === false) {
            $this->db->trans_rollback();

            return $this->outputJson([
                'insert' => false,
                'text' => 'Siswa gagal ditambahkan. Silakan coba lagi.',
            ]);
        }

        $this->db->trans_commit();

        return $this->outputJson([
            'insert' => true,
            'text' => 'Siswa berhasil ditambahkan',
        ]);
    }

    private function outputJson($data)
    {
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($data));
    }
}
