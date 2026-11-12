<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Usertu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Administrator yang diberi hak untuk mengakses halaman ini, <a href="' . base_url('dashboard') . '">Kembali ke menu awal</a>', 403, 'Akses Terlarang');
        }

        $this->load->library(['datatables', 'form_validation', 'upload']);
        $this->load->model('Master_model', 'master');
        $this->load->model('Users_model', 'users');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->form_validation->set_error_delimiters('', '');
    }

    public function output_json($data, $encode = true)
    {
        if ($encode) {
            $data = json_encode($data);
        }
        $this->output->set_content_type('application/json')->set_output($data);
    }

    public function index()
    {
        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Tata Usaha (TU)',
            'subjudul' => 'Data User Tata Usaha',
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
        ];
        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('users/tu/data');
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function data()
    {
        $this->output_json($this->users->getDatatu(), false);
    }

    public function create()
    {
        $this->form_validation->set_rules('first_name', 'First Name', 'required');
        $this->form_validation->set_rules('last_name', 'Last Name', 'required');
        $this->form_validation->set_rules('username', 'Username', 'required|is_unique[users.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]|max_length[20]|required');
        $this->form_validation->set_rules('confirm_password', 'Confirm password', 'trim|matches[password]|required');

        if ($this->form_validation->run() === FALSE) {
            $data = [
                'status' => false,
                'errors' => [
                    'first_name' => form_error('first_name'),
                    'last_name' => form_error('last_name'),
                    'email' => form_error('email'),
                    'username' => form_error('username'),
                    'password' => form_error('password'),
                    'confirm_password' => form_error('confirm_password'),
                ]
            ];
        } else {
            $username = $this->input->post('username', true);
            $password = $this->input->post('password', true);
            $email = $this->input->post('email', true);
            $additional_data = [
                'first_name' => $this->input->post('first_name', true),
                'last_name' => $this->input->post('last_name', true),
            ];
            $tuGroup = $this->db->get_where('groups', array('name' => 'tu'))->row();
            $groupId = $tuGroup ? array((int)$tuGroup->id) : array(4);

            $data['status'] = $this->ion_auth->register($username, $password, $email, $additional_data, $groupId) ? true : false;
        }
        $this->output_json($data);
    }

    public function edit($id)
    {
        $user = $this->ion_auth->user()->row();
        $targetUser = $this->ion_auth->user($id)->row();
        if (!$targetUser) {
            show_404();
        }
        $data = [
            'user' => $user,
            'judul' => 'Tata Usaha',
            'subjudul' => 'Edit Data TU',
            'users' => $targetUser,
            'groups' => $this->ion_auth->groups()->result(),
            'level' => $this->ion_auth->get_users_groups($id)->result(),
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
        ];
        $this->load->view('_templates/dashboard/_header.php', $data);
        $this->load->view('users/admin/edit', $data);
        $this->load->view('_templates/dashboard/_footer.php');
    }

    public function edit_level()
    {
        $id = (int) $this->input->post('id', true);
        $levels = $this->input->post('levels');
        if ($levels === null) {
            $levels = $this->input->post('level');
        }
        if (!is_array($levels)) {
            $levels = $levels ? array($levels) : array();
        }

        $res = $this->users->syncUserGroups($id, $levels);
        $this->output_json($res);
    }

    public function delete($id)
    {
        $currentUserId = $this->ion_auth->user()->row()->id;
        if ((int)$id === (int)$currentUserId) {
            $this->output_json(['status' => false, 'message' => 'Tidak dapat menghapus akun sendiri']);
            return;
        }
        $data['status'] = $this->ion_auth->delete_user($id) ? true : false;
        $this->output_json($data);
    }
}
