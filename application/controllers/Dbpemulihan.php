<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRD FR-27: backup terjadwal dan restore yang teruji.
 *
 * - backup  : dijalankan dari Task Scheduler lewat CLI
 *             php index.php dbpemulihan backup
 * - pulihkan: hanya admin, hanya file yang ada di folder backups/,
 *             wajib mengetik ulang nama file sebagai konfirmasi.
 *
 * Restore memakai mysqli langsung, bukan dbutil, supaya file .sql.zip
 * hasil Dbmanager (dbutil->backup) bisa diputar balik apa adanya.
 */
class Dbpemulihan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->library('ion_auth');
        $this->load->helper('file');
        if (!$this->input->is_cli_request()) {
            if (!$this->ion_auth->logged_in() || !$this->ion_auth->is_admin()) {
                show_error('Hanya Administrator yang dapat mengelola backup database.', 403, 'Akses Terlarang');
            }
        }
        if (!is_dir($this->folder())) {
            @mkdir($this->folder(), 0775, TRUE);
        }
    }

    public function index()
    {
        $this->load->model('Dashboard_model', 'dashboard');
        $user = $this->ion_auth->user()->row();
        $data = array(
            'user' => $user,
            'judul' => 'Backup & Pemulihan Database',
            'subjudul' => 'Backup',
            'setting' => $this->db->get('setting')->row(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt_active' => $this->dashboard->getSemesterActive(),
            'tp' => $this->dashboard->getTahun(),
            'smt' => $this->dashboard->getSemester(),
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'berkas' => $this->daftarBerkas(),
        );
        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('cbt/backup/data', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    /**
     * Entry point Task Scheduler. Menulis satu file .sql.zip baru dan
     * menghapus backup harian yang lebih tua dari masa simpan.
     */
    public function backup()
    {
        $this->load->dbutil();
        $this->load->helper('file');

        $this->dbutil->optimize_database();
        $sql = $this->dbutil->backup(array(
            'tables' => $this->db->list_tables(),
            'ignore' => array(),
            'format' => 'zip',
            'filename' => 'backup.sql',
            'add_drop' => TRUE,
            'add_insert' => TRUE,
            'newline' => "\n",
        ));
        $nama = 'backup-db-' . date('Y-m-d-H-i-s') . '.sql.zip';
        if (!write_file($this->folder() . $nama, $sql)) {
            $this->balas('Gagal menulis ' . $nama, 1);
            return;
        }
        $this->bersihkanTua();

        $this->balas('Backup dibuat: ' . $nama, 0);
    }

    /**
     * Pulihkan dari file backup. Konfirmasi berupa nama file yang harus
     * diketik ulang, dan database saat ini dibackup dulu supaya salah
     * pilih file masih bisa dibatalkan.
     */
    public function pulihkan()
    {
        $berkas = basename((string) $this->input->post('berkas', TRUE));
        $konfirmasi = basename((string) $this->input->post('konfirmasi', TRUE));
        if ($berkas === $konfirmasi && $berkas === '') {
            $this->balas('Nama berkas backup wajib dipilih.', 1);
            return;
        }
        if ($berkas !== $konfirmasi) {
            $this->balas('Konfirmasi tidak cocok. Ketik ulang nama berkas persis.', 1);
            return;
        }
        $path = $this->folder() . $berkas;
        if (!is_file($path) || !preg_match('/\.sql\.zip$/', $berkas)) {
            $this->balas('Berkas backup tidak ditemukan.', 1);
            return;
        }

        $this->backup();

        $sql = $this->bacaZip($path);
        if ($sql === NULL) {
            $this->balas('Berkas backup tidak bisa dibaca.', 1);
            return;
        }

        $hasil = $this->jalankan($sql);
        if ($hasil !== NULL) {
            $this->balas('Restore gagal: ' . $hasil, 1);
            return;
        }
        $this->balas('Restore selesai dari ' . $berkas . '. Muat ulang halaman dan periksa data ujian.', 0);
    }

    public function unduh($berkas)
    {
        $berkas = basename((string) $berkas);
        $path = $this->folder() . $berkas;
        if (!is_file($path)) {
            show_404();
        }
        $this->load->helper('download');
        force_download($berkas, file_get_contents($path));
    }

    private function bacaZip($path)
    {
        if (!class_exists('ZipArchive')) {
            return NULL;
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== TRUE) {
            return NULL;
        }
        $sql = NULL;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $nama = $zip->getNameIndex($i);
            if (preg_match('/\.sql$/i', $nama)) {
                $sql = $zip->getFromIndex($i);
                break;
            }
        }
        $zip->close();
        return $sql === FALSE ? NULL : $sql;
    }

    private function jalankan($sql)
    {
        $cfg = array(
            'hostname' => $this->db->hostname,
            'username' => $this->db->username,
            'password' => $this->db->password,
            'database' => $this->db->database,
            'port' => $this->db->port,
        );
        mysqli_report(MYSQLI_REPORT_OFF);
        $link = @new mysqli(
            $cfg['hostname'],
            $cfg['username'],
            $cfg['password'],
            $cfg['database'],
            $cfg['port'] ? (int) $cfg['port'] : NULL
        );
        if ($link->connect_errno) {
            return $link->connect_error;
        }
        $link->set_charset('utf8mb4');
        /* DROP TABLE di dalam dump gagal bila ada foreign key aktif
           (mis. cbt_exam_event -> cbt_exam_attempt). Matikan selama restore. */
        $link->query('SET FOREIGN_KEY_CHECKS=0');
        if (!$link->multi_query($sql)) {
            $err = $link->error;
            $link->close();
            return $err;
        }
        while ($link->more_results() && $link->next_result()) {
            /* habiskan seluruh result set sebelum koneksi ditutup */
        }
        $err = $link->error;
        $link->query('SET FOREIGN_KEY_CHECKS=1');
        $link->close();
        return $err === '' ? NULL : $err;
    }

    private function daftarBerkas()
    {
        $out = array();
        foreach (glob($this->folder() . '*.sql.zip') as $file) {
            $out[] = array(
                'nama' => basename($file),
                'ukuran' => filesize($file),
                'waktu' => filemtime($file),
            );
        }
        usort($out, function ($a, $b) { return $b['waktu'] - $a['waktu']; });
        return $out;
    }

    /**
     * Simpan 30 backup terakhir; sisanya dihapus supaya disk lab tidak penuh.
     */
    private function bersihkanTua($simpan = 30)
    {
        $berkas = $this->daftarBerkas();
        foreach (array_slice($berkas, $simpan) as $row) {
            @unlink($this->folder() . $row['nama']);
        }
    }

    private function folder()
    {
        return FCPATH . 'backups' . DIRECTORY_SEPARATOR;
    }

    private function balas($pesan, $kode = 0)
    {
        if ($this->input->is_cli_request()) {
            echo $pesan . PHP_EOL;
            return;
        }
        $this->output->set_status_header($kode === 0 ? 200 : 422)
            ->set_content_type('application/json')
            ->set_output(json_encode(array('status' => $kode === 0, 'message' => $pesan)));
    }
}
