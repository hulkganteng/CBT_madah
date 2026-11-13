<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sebconfig extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Administrator yang boleh mengakses halaman ini.', 403, 'Akses Terlarang');
        }
        $this->load->model('Dashboard_model', 'dashboard');
    }

    public function index()
    {
        require_once FCPATH . 'tools/buat-seb.php';

        $user = $this->ion_auth->user()->row();
        $data = [
            'user' => $user,
            'judul' => 'Generator Safe Exam Browser',
            'subjudul' => 'Buat konfigurasi SEB untuk dibagikan kepada peserta',
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            'presets' => $seb_presets,
        ];

        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('setting/seb', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function download()
    {
        require_once FCPATH . 'tools/buat-seb.php';

        $presetName = strtolower((string) $this->input->post('preset', true));
        $startUrl = trim((string) $this->input->post('start_url', true));
        $filename = trim((string) $this->input->post('filename', true));
        $adminPassword = (string) $this->input->post('admin_password', false);
        $quitPassword = (string) $this->input->post('quit_password', false);

        if (!isset($seb_presets[$presetName])) {
            return $this->invalid('Preset SEB tidak tersedia.');
        }
        if ($startUrl === '') {
            $startUrl = $seb_presets[$presetName]['startURL'];
        }

        $url = parse_url($startUrl);
        if (!$url || !in_array($url['scheme'] ?? '', ['http', 'https'], true) || empty($url['host']) || isset($url['user']) || isset($url['pass'])) {
            return $this->invalid('Start URL harus berupa alamat HTTP atau HTTPS yang valid.');
        }
        if (strlen($adminPassword) < 8 || strlen($quitPassword) < 8) {
            return $this->invalid('Password admin dan password keluar minimal 8 karakter.');
        }

        $filename = preg_replace('/[^A-Za-z0-9._-]/', '-', $filename ?: $seb_presets[$presetName]['file']);
        $filename = preg_replace('/\.seb$/i', '', $filename) . '.seb';
        $preset = [
            'startURL' => $startUrl,
            'host' => $url['host'],
            'file' => $filename,
            'filter' => $this->input->post('url_filter') === '1',
        ];
        $temporary = tempnam(sys_get_temp_dir(), 'garuda_seb_');

        try {
            $result = buat_seb_config($preset, $temporary, $adminPassword, $quitPassword);
            $contents = file_get_contents($result['file']);
            if ($contents === false) {
                throw new RuntimeException('Berkas SEB tidak dapat dibaca.');
            }
            $this->saveGlobalPolicy($result['startURL'], $result['configKey']);
        } catch (Throwable $error) {
            log_message('error', 'Generator SEB: ' . $error->getMessage());
            return $this->invalid('Konfigurasi SEB gagal: ' . $error->getMessage());
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }

        $this->output
            ->set_content_type('application/octet-stream')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Cache-Control: no-store, no-cache, must-revalidate')
            ->set_output($contents);
    }

    private function saveGlobalPolicy($startUrl, $configKey)
    {
        $path = APPPATH . 'config/.seb_global.php';
        $browserKeys = '';
        $configKeys = array();
        if (is_file($path)) {
            $current = require $path;
            $browserKeys = is_array($current) && isset($current['browser_exam_keys'])
                ? (string) $current['browser_exam_keys'] : '';
            if (is_array($current) && isset($current['config_key'])) {
                $configKeys = preg_split('/[\s,;]+/', strtolower((string) $current['config_key']), -1, PREG_SPLIT_NO_EMPTY);
            }
        }
        $configKeys[] = strtolower($configKey);
        $settings = array(
            'start_url' => $startUrl,
            'config_key' => implode("\n", array_values(array_unique($configKeys))),
            'browser_exam_keys' => $browserKeys,
        );
        $php = "<?php\ndefined('BASEPATH') OR exit('No direct script access allowed');\n\nreturn "
            . var_export($settings, TRUE) . ";\n";
        if (file_put_contents($path, $php, LOCK_EX) === FALSE) {
            throw new RuntimeException('Kebijakan SEB server tidak dapat ditulis.');
        }
    }

    private function invalid($message)
    {
        $this->session->set_flashdata('seb_error', $message);
        redirect('sebconfig');
    }
}
