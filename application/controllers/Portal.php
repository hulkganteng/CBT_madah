<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Pintu masuk terpisah: admin/guru lewat /login, peserta lewat /login-siswa.
 * Halaman staf tetap Auth::index (tampilan lama); kelas ini hanya melayani
 * halaman peserta yang rata tengah dan gerbang peran di hook.
 */
class Portal extends CI_Controller
{
    public function index()
    {
        redirect('login');
    }

    public function siswa()
    {
        $this->load->model('Settings_model', 'settings');

        if (count($this->db->list_tables()) == 0) {
            redirect('install');
        }
        $setting = $this->settings->getSetting();
        if ($setting == null) {
            redirect('install');
        }

        if ($this->ion_auth->logged_in()) {
            redirect($this->ion_auth->in_group('siswa') ? 'siswa/cbt' : 'dashboard');
        }

        $data = array(
            'setting' => $setting,
            'judul' => 'Masuk Peserta',
            'identity' => array(
                'name' => 'identity', 'id' => 'identity', 'type' => 'text',
                'placeholder' => 'Username peserta', 'autofocus' => 'autofocus',
                'class' => 'form-control', 'autocomplete' => 'off',
            ),
            'password' => array(
                'name' => 'password', 'id' => 'password', 'type' => 'password',
                'placeholder' => 'Kata sandi', 'class' => 'form-control',
            ),
            'message' => validation_errors() ? validation_errors() : $this->session->flashdata('message'),
        );

        $this->load->view('_templates/auth/_header', $data);
        $this->load->view('auth/login_siswa', $data);
        $this->load->view('_templates/auth/_footer');
    }
}
