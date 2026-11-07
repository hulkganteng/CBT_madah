<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Install extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        include APPPATH . 'config/database.php';
        if (!empty($db['development']['database'])) {
            $this->db = $this->load->database('development', TRUE);
            $this->load->dbforge($this->db);
        }
        $this->load->model('Install_model', 'install');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->library('environment_database_installer');
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
        $result = $this->install->check_installer();
        if ($result === '0') {
            redirect('update');
            return;
        }
        $data = $this->getSaved();
        $data->error = $result;
        $this->load->view('install/header', array('data' => $data));
        $this->load->view('install/step');
        $this->load->view('install/footer');
    }

    public function steps()
    {
        $data = $this->getSaved();
        $this->load->view('install/header', array('data' => $data));
        $this->load->view('install/step');
        $this->load->view('install/footer');
    }

    public function getSaved()
    {
        include APPPATH . 'config/database.php';
        $config = $db['development'];
        $admin = null;
        $setting = null;
        if (isset($this->db) && $this->db->table_exists('users')) {
            $admin = $this->db
                ->select('users.*')
                ->from('users')
                ->join('users_groups', 'users_groups.user_id=users.id AND users_groups.group_id=1')
                ->get()
                ->row();
            $setting = $this->dashboard->getSetting();
        }

        $data = array(
            'hostname' => $config['hostname'],
            'username' => $config['username'],
            'password' => $config['password'],
            'database' => preg_replace('/_development$/', '', $config['database']),
            'nama_admin' => '',
            'user_admin' => '',
            'pass_admin' => '',
            'aplikasi' => '',
            'sekolah' => '',
            'jenjang' => '',
            'satuan' => '',
            'kepsek' => '',
            'alamat' => '',
            'desa' => '',
            'kec' => '',
            'kota' => '',
            'prov' => '',
        );
        if ($admin) {
            $data['nama_admin'] = trim($admin->first_name . ' ' . $admin->last_name);
            $data['user_admin'] = $admin->username;
            $data['pass_admin'] = $admin->password;
        }
        if ($setting) {
            $data['aplikasi'] = $setting->nama_aplikasi;
            $data['sekolah'] = $setting->sekolah;
            $data['jenjang'] = $setting->jenjang;
            $data['satuan'] = $setting->satuan_pendidikan;
            $data['kepsek'] = $setting->kepsek;
            $data['alamat'] = $setting->alamat;
            $data['desa'] = $setting->desa;
            $data['kec'] = $setting->kecamatan;
            $data['kota'] = $setting->kota;
            $data['prov'] = $setting->provinsi;
        }
        $data['current_page'] = !$admin ? 2 : (!$setting ? 3 : 4);
        return json_decode(json_encode($data));
    }

    public function checkDatabase()
    {
        $config = $this->postedDatabaseConfig();
        try {
            $this->environment_database_installer->installAndConfigure($config);
            $this->output_json(array(
                'host' => true,
                'host_msg' => 'berhasil',
                'database' => true,
                'table' => true,
            ));
        } catch (Throwable $e) {
            $this->output_json(array(
                'host' => false,
                'host_msg' => $e->getMessage(),
                'database' => false,
                'table' => false,
            ));
        }
    }

    public function createDb()
    {
        if ((string) $this->input->post('page', true) !== '0') {
            $this->output_json(array('host' => true, 'database' => false, 'table' => false, 'host_msg' => 'step salah'));
            return;
        }
        $this->checkDatabase();
    }

    public function createApp()
    {
        $name = trim((string) $this->input->post('nama_lengkap', true));
        $username = trim((string) $this->input->post('username', true));
        $password = (string) $this->input->post('password', true);
        $school = trim((string) $this->input->post('nama_sekolah', true));
        if ($name === '' || $username === '' || $password === '' || $school === '') {
            $this->output_json(array('admin' => false, 'insert' => false));
            return;
        }

        $parts = preg_split('/\s+/', $name);
        $additional = array('first_name' => $parts[0], 'last_name' => end($parts));
        $email = strtolower(preg_replace('/\s+/', '.', $name)) . '@admin.com';
        $adminId = $this->ion_auth->register($username, $password, $email, $additional, array('1'));
        if (!$adminId) {
            $this->output_json(array('admin' => false, 'insert' => false));
            return;
        }

        $setting = array(
            'id_setting' => 1,
            'sekolah' => $school,
            'jenjang' => $this->input->post('jenjang', true),
            'satuan_pendidikan' => $this->input->post('satuan', true),
            'alamat' => $this->input->post('alamat', true),
            'desa' => $this->input->post('desa', true),
            'kota' => $this->input->post('kota', true),
            'kecamatan' => $this->input->post('kec', true),
            'provinsi' => $this->input->post('prov', true),
            'kepsek' => $this->input->post('kepsek', true),
            'nama_aplikasi' => $this->input->post('nama_aplikasi', true),
        );
        $inserted = $this->db->insert('setting', $setting);
        $finalized = $inserted && $this->install->finalizeDualInstallation($adminId);
        if (!$finalized && $inserted) {
            $this->db->where('id_setting', 1)->delete('setting');
            $this->ion_auth->delete_user($adminId);
        }
        $this->output_json(array('admin' => $finalized ? $adminId : false, 'insert' => $finalized));
    }

    public function createAdmin()
    {
        $this->output_json(array('admin' => false, 'message' => 'Gunakan langkah pemasangan aplikasi lengkap.'));
    }

    public function createSetting()
    {
        $this->output_json(array('insert' => false, 'message' => 'Gunakan langkah pemasangan aplikasi lengkap.'));
    }

    private function postedDatabaseConfig()
    {
        return array(
            'hostname' => trim((string) $this->input->post('hostname', true)),
            'username' => trim((string) $this->input->post('hostuser', true)),
            'password' => (string) $this->input->post('hostpass', true),
            'database' => trim((string) $this->input->post('database', true)),
        );
    }
}
