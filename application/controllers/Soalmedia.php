<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Soalmedia extends CI_Controller
{
    const MAX_FILE_SIZE = 4194304;
    const MAX_DIMENSION = 5000;
    const MAX_PIXELS = 12000000;

    private $accessGranted = false;

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            $this->json(array('status' => false, 'message' => 'Sesi login telah berakhir.'), 401);
            return;
        }
        if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru')) {
            $this->json(array('status' => false, 'message' => 'Anda tidak memiliki akses.'), 403);
            return;
        }
        $this->load->database();
        $this->accessGranted = true;
    }

    public function upload()
    {
        if (!$this->accessGranted) {
            return;
        }
        $bankId = $this->input->post('bank_id', true);
        if (!$this->authorisedBank($bankId)) {
            return;
        }
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(array('status' => false, 'message' => 'Pilih gambar yang valid.'), 422);
            return;
        }

        $file = $_FILES['file'];
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->json(array('status' => false, 'message' => 'Upload gambar tidak valid.'), 422);
            return;
        }
        if ((int) $file['size'] > self::MAX_FILE_SIZE) {
            $this->json(array('status' => false, 'message' => 'Ukuran gambar maksimal 4 MB.'), 422);
            return;
        }

        $info = @getimagesize($file['tmp_name']);
        $allowed = array(IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_GIF => 'gif');
        if (defined('IMAGETYPE_WEBP')) {
            $allowed[IMAGETYPE_WEBP] = 'webp';
        }
        if (!$info || !isset($allowed[$info[2]])) {
            $this->json(array('status' => false, 'message' => 'Format gambar harus JPG, PNG, GIF, atau WebP.'), 422);
            return;
        }
        if ($info[0] > self::MAX_DIMENSION || $info[1] > self::MAX_DIMENSION || ($info[0] * $info[1]) > self::MAX_PIXELS) {
            $this->json(array('status' => false, 'message' => 'Dimensi gambar terlalu besar.'), 422);
            return;
        }

        $contents = @file_get_contents($file['tmp_name']);
        $image = $contents === false ? false : @imagecreatefromstring($contents);
        if (!$image) {
            $this->json(array('status' => false, 'message' => 'Isi file gambar tidak dapat dibaca.'), 422);
            return;
        }

        $relativeDirectory = 'uploads/bank_soal/' . $bankId;
        $directory = FCPATH . $relativeDirectory;
        if (!is_dir($directory) && !mkdir($directory, 0755, true)) {
            imagedestroy($image);
            $this->json(array('status' => false, 'message' => 'Direktori gambar tidak dapat dibuat.'), 500);
            return;
        }

        $extension = $allowed[$info[2]];
        $filename = 'soal_' . bin2hex(random_bytes(12)) . '.' . $extension;
        $absolutePath = $directory . DIRECTORY_SEPARATOR . $filename;
        $saved = $this->writeImage($image, $absolutePath, $info[2]);
        imagedestroy($image);
        if (!$saved) {
            $this->json(array('status' => false, 'message' => 'Gambar gagal disimpan.'), 500);
            return;
        }

        $this->json(array(
            'status' => true,
            'filename' => $relativeDirectory . '/' . $filename,
            'width' => $info[0],
            'height' => $info[1]
        ));
    }

    public function delete()
    {
        if (!$this->accessGranted) {
            return;
        }
        $bankId = $this->input->post('bank_id', true);
        if (!$this->authorisedBank($bankId)) {
            return;
        }
        $source = (string) $this->input->post('src', true);
        $path = (string) parse_url($source, PHP_URL_PATH);
        $basePath = (string) parse_url(base_url(), PHP_URL_PATH);
        if ($basePath !== '' && strpos($path, $basePath) === 0) {
            $path = substr($path, strlen($basePath));
        }
        $path = ltrim(str_replace('\\', '/', rawurldecode($path)), '/');
        $expectedPrefix = 'uploads/bank_soal/' . $bankId . '/';
        $filename = basename($path);
        if (strpos($path, $expectedPrefix) !== 0 || !preg_match('/^soal_[a-f0-9]{24}\.(jpg|png|gif|webp)$/', $filename)) {
            $this->json(array('status' => false, 'message' => 'Lokasi gambar tidak valid.'), 422);
            return;
        }

        if ($this->isReferenced($bankId, $path)) {
            $this->json(array('status' => false, 'message' => 'Gambar masih digunakan oleh soal tersimpan.'), 409);
            return;
        }
        $absolutePath = FCPATH . str_replace('/', DIRECTORY_SEPARATOR, $path);
        $deleted = !is_file($absolutePath) || unlink($absolutePath);
        $this->json(array('status' => $deleted));
    }

    private function writeImage($image, $path, $type)
    {
        if ($type === IMAGETYPE_JPEG) {
            return imagejpeg($image, $path, 88);
        }
        if ($type === IMAGETYPE_PNG) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
            return imagepng($image, $path, 6);
        }
        if ($type === IMAGETYPE_GIF) {
            return imagegif($image, $path);
        }
        if (defined('IMAGETYPE_WEBP') && $type === IMAGETYPE_WEBP && function_exists('imagewebp')) {
            return imagewebp($image, $path, 88);
        }
        return false;
    }

    private function authorisedBank($bankId)
    {
        if (!ctype_digit((string) $bankId)) {
            $this->json(array('status' => false, 'message' => 'Bank soal tidak valid.'), 422);
            return null;
        }
        $bank = $this->db->where('id_bank', $bankId)->get('cbt_bank_soal')->row();
        if (!$bank) {
            $this->json(array('status' => false, 'message' => 'Bank soal tidak ditemukan.'), 404);
            return null;
        }
        if ($this->ion_auth->is_admin()) {
            return $bank;
        }

        $this->load->model('Dashboard_model', 'dashboard');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $user = $this->ion_auth->user()->row();
        $teacher = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        if (!$teacher || (string) $bank->bank_guru_id !== (string) $teacher->id_guru) {
            $this->json(array('status' => false, 'message' => 'Anda tidak memiliki akses ke bank soal ini.'), 403);
            return null;
        }
        return $bank;
    }

    private function isReferenced($bankId, $path)
    {
        $fields = array('soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e', 'jawaban');
        $this->db->from('cbt_soal')->where('bank_id', $bankId)->group_start();
        foreach ($fields as $index => $field) {
            if ($index === 0) {
                $this->db->like($field, $path);
            } else {
                $this->db->or_like($field, $path);
            }
        }
        return $this->db->group_end()->count_all_results() > 0;
    }

    private function json($data, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
