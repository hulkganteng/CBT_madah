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
 * Impor dilakukan ke database staging lebih dulu; lihat Db_restore.
 */
class Dbpemulihan extends CI_Controller
{
    private $pemulih = NULL;

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
        $hasil = $this->buatBackup();
        $this->balas($hasil['pesan'], $hasil['ok'] ? 0 : 1);
    }

    /**
     * Pulihkan dari file backup. Konfirmasi berupa nama file yang harus
     * diketik ulang. Restore dibatalkan bila backup pengaman gagal.
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

        try {
            $this->pemulih()->readBackup($path);
        } catch (Throwable $error) {
            $this->balas('Backup ditolak: ' . $error->getMessage(), 1);
            return;
        }

        $pengaman = $this->buatBackup();
        if (!$pengaman['ok']) {
            $this->balas('Restore dibatalkan karena backup pengaman gagal: ' . $pengaman['pesan'], 1);
            return;
        }

        try {
            $this->pemulih()->restore($path);
        } catch (Throwable $error) {
            $this->balas('Restore gagal: ' . $error->getMessage() . ' Backup pengaman: ' . $pengaman['nama'], 1);
            return;
        }

        $this->balas('Restore selesai dari ' . $berkas . '. Backup pengaman: ' . $pengaman['nama']
            . '. Muat ulang halaman dan periksa data ujian.', 0);
    }

    public function unduh($berkas)
    {
        $berkas = basename((string) $berkas);
        $path = $this->folder() . $berkas;
        if (!preg_match('/\.sql\.zip$/', $berkas) || !is_file($path)) {
            show_404();
        }
        $this->load->helper('download');
        force_download($berkas, file_get_contents($path));
    }

    /**
     * Buat backup dan verifikasi hasilnya sebelum dianggap berhasil.
     * Selalu mengembalikan array('ok', 'nama', 'pesan').
     */
    private function buatBackup()
    {
        $this->load->dbutil();

        $folder = $this->folder();
        if (!is_dir($folder) || !is_writable($folder)) {
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Folder backup tidak dapat ditulis. Periksa izin folder backups.');
        }

        $nama = 'backup-db-' . date('Y-m-d-H-i-s') . '-' . bin2hex(random_bytes(3)) . '.sql.zip';
        $tmp = $folder . $nama . '.part';
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
        if (!is_string($sql) || $sql === '') {
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Backup gagal dibuat oleh dbutil.');
        }
        if (write_file($tmp, $sql) === FALSE) {
            @unlink($tmp);
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Backup gagal ditulis. Disk penuh atau izin ditolak.');
        }
        try {
            $this->pemulih()->readBackup($tmp);
        } catch (Throwable $error) {
            @unlink($tmp);
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Backup baru rusak: ' . $error->getMessage());
        }
        if (!@rename($tmp, $folder . $nama)) {
            @unlink($tmp);
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Backup gagal dipindahkan ke nama akhir.');
        }
        clearstatcache(TRUE, $folder . $nama);
        if (!is_file($folder . $nama) || filesize($folder . $nama) <= 0) {
            @unlink($folder . $nama);
            return array('ok' => FALSE, 'nama' => '', 'pesan' => 'Berkas backup tidak terbentuk sempurna.');
        }

        $this->bersihkanTua();
        return array('ok' => TRUE, 'nama' => $nama, 'pesan' => 'Backup dibuat: ' . $nama);
    }

    private function pemulih()
    {
        if ($this->pemulih === NULL) {
            $this->load->library('db_restore', array(
                'hostname' => $this->db->hostname,
                'username' => $this->db->username,
                'password' => $this->db->password,
                'database' => $this->db->database,
                'port' => isset($this->db->port) ? $this->db->port : NULL,
                'folder' => $this->folder(),
            ));
            $this->pemulih = $this->db_restore;
        }
        return $this->pemulih;
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