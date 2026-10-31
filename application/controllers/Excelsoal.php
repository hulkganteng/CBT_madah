<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Excelsoal extends CI_Controller
{
    const MAX_FILE_SIZE = 5242880;

    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->ion_auth->is_admin() && !$this->ion_auth->in_group('guru')) {
            show_error('Hanya Administrator dan guru yang dapat mengakses fitur ini.', 403, 'Akses Terlarang');
        }
        $this->load->database();
        $this->load->library('excel_question_importer');
    }

    public function template()
    {
        $bank = null;
        $bankId = $this->input->get('bank_id', true);
        if ($bankId !== null && $bankId !== '') {
            $bank = $this->authorisedBank($bankId);
            if (!$bank) {
                return;
            }
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SOAL');
        $headers = array('NO', 'JENIS', 'SOAL', 'OPSI_A', 'OPSI_B', 'OPSI_C', 'OPSI_D', 'OPSI_E', 'KUNCI', 'KESULITAN', 'TAMPILKAN');
        $sheet->fromArray($headers, null, 'A1');

        $rows = $this->templateRows($bank);
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        } elseif (!$bank) {
            $sheet->fromArray(array(
                array(1, 1, 'Ibukota Indonesia adalah ...', 'Jakarta', 'Bandung', 'Surabaya', 'Medan', '', 'A', 1, 1),
                array(2, 2, 'Pilih bilangan genap.', '2', '3', '4', '5', '', 'A,C', 1, 1),
                array(3, 6, 'Ibukota Indonesia adalah Bandung.', '', '', '', '', '', 'S', 1, 1),
                array(4, 6, 'Air membeku pada suhu 0 derajat Celsius.', '', '', '', '', '', 'B', 1, 1)
            ), null, 'A2');
        }

        $sheet->getStyle('A1:K1')->getFont()->setBold(true);
        $sheet->getStyle('A1:K1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAF7');
        $sheet->freezePane('A2');
        $sheet->setAutoFilter('A1:K' . max(5, count($rows) + 1));
        foreach (range('A', 'K') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('PETUNJUK');
        $guide->fromArray(array(
            array('JENIS', 'KETERANGAN', 'FORMAT KUNCI'),
            array(1, 'Pilihan ganda satu jawaban', 'A'),
            array(2, 'Pilihan ganda kompleks', 'A,C'),
            array(6, 'Benar / Salah per pernyataan. Opsi tidak diisi.', 'B atau S'),
            array('', 'KESULITAN: 1 mudah, 2 sedang, 3 sulit.', ''),
            array('', 'TAMPILKAN: 1/Ya untuk ditampilkan, 0/Tidak untuk cadangan.', ''),
            array('', 'Jangan mengubah nama kolom pada sheet SOAL.', '')
        ), null, 'A1');
        if ($bank) {
            $info = $this->templateBankInfo($bank);
            $guide->fromArray(array(array('', '', '')), null, 'A8');
            $guide->fromArray($info, null, 'A9');
        }
        $guide->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (range('A', 'C') as $column) {
            $guide->getColumnDimension($column)->setAutoSize(true);
        }

        $suffix = $bank ? '_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $bank->bank_kode) : '';
        $filename = 'template_import_soal' . $suffix . '.xlsx';
        $this->output
            ->set_header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Cache-Control: max-age=0');
        (new Xlsx($spreadsheet))->save('php://output');
        $spreadsheet->disconnectWorksheets();
    }

    /**
     * Baris kosong yang sudah diberi NO/JENIS sesuai kuota bank buatan admin.
     * Guru tinggal mengisi SOAL, OPSI, dan KUNCI.
     */
    private function templateRows($bank)
    {
        if (!$bank) {
            return array();
        }
        $quota = array(
            array('jenis' => 1, 'total' => (int) $bank->tampil_pg),
            array('jenis' => 2, 'total' => (int) $bank->tampil_kompleks),
            array('jenis' => 6, 'total' => (int) ($bank->tampil_bs ?? 0)),
        );
        $rows = array();
        $number = 0;
        foreach ($quota as $item) {
            for ($i = 0; $i < $item['total'] && $number < 2000; $i++) {
                $number++;
                $rows[] = array($number, $item['jenis'], '', '', '', '', '', '', '', 1, 1);
            }
        }
        return $rows;
    }

    private function templateBankInfo($bank)
    {
        $mapel = $this->db->select('nama_mapel')->where('id_mapel', $bank->bank_mapel_id)->get('master_mapel')->row();
        $guru = $this->db->select('nama_guru')->where('id_guru', $bank->bank_guru_id)->get('master_guru')->row();
        return array(
            array('BANK SOAL', $bank->bank_kode . ' - ' . $bank->bank_nama, ''),
            array('MAPEL', $mapel ? $mapel->nama_mapel : '-', ''),
            array('GURU', $guru ? $guru->nama_guru : '-', ''),
            array('LEVEL', $bank->bank_level, ''),
            array('KUOTA PG', (int) $bank->tampil_pg, 'baris JENIS 1'),
            array('KUOTA PG KOMPLEKS', (int) $bank->tampil_kompleks, 'baris JENIS 2'),
            array('KUOTA BENAR/SALAH', (int) ($bank->tampil_bs ?? 0), 'baris JENIS 6'),
            array('OPSI JAWABAN', (int) $bank->opsi, 'jumlah pilihan A-E'),
        );
    }

    public function preview()
    {
        $bank = $this->authorisedBank($this->input->post('bank_id', true));
        if (!$bank) {
            return;
        }
        $parsed = $this->parseUpload();
        if (!$parsed) {
            return;
        }

        $preview = array();
        foreach (array_slice($parsed['questions'], 0, 50) as $question) {
            $preview[] = array(
                'no' => $question['nomor_soal'],
                'jenis' => $question['jenis'],
                'soal' => strip_tags($question['soal']),
                'tampilkan' => $question['tampilkan']
            );
        }
        $this->json(array(
            'valid' => count($parsed['errors']) === 0 && count($parsed['questions']) > 0,
            'total' => count($parsed['questions']),
            'preview' => $preview,
            'errors' => $parsed['errors']
        ));
    }

    public function upload()
    {
        $bankId = $this->input->post('bank_id', true);
        $bank = $this->authorisedBank($bankId);
        if (!$bank) {
            return;
        }
        $parsed = $this->parseUpload();
        if (!$parsed) {
            return;
        }
        if ($parsed['errors'] || !$parsed['questions']) {
            $this->json(array('message' => 'File belum valid.', 'errors' => $parsed['errors']), 422);
            return;
        }

        $mode = $this->input->post('mode', true) === 'replace' ? 'replace' : 'append';
        if ($mode === 'append') {
            $duplicates = $this->findDuplicates($bankId, $parsed['questions']);
            if ($duplicates) {
                $this->json(array(
                    'message' => 'Nomor soal sudah ada. Gunakan mode ganti seluruh soal atau ubah nomor.',
                    'errors' => $duplicates
                ), 409);
                return;
            }
        }

        $now = time();
        $rows = array();
        foreach ($parsed['questions'] as $question) {
            $question['bank_id'] = $bankId;
            $question['created_on'] = $now;
            $question['updated_on'] = $now;
            $rows[] = $question;
        }

        $this->db->trans_begin();
        if ($mode === 'replace') {
            $this->db->where('bank_id', $bankId)->delete('cbt_soal');
        }
        foreach (array_chunk($rows, 200) as $batch) {
            $this->db->insert_batch('cbt_soal', $batch);
        }
        $this->updateBankStatus($bankId, $bank);
        if ($this->db->trans_status() === false) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Database gagal menyimpan soal.'), 500);
            return;
        }
        $this->db->trans_commit();

        $this->json(array(
            'message' => count($rows) . ' soal berhasil diimpor.',
            'inserted' => count($rows),
            'mode' => $mode
        ));
    }

    private function parseUpload()
    {
        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->json(array('message' => 'Pilih file Excel yang valid.'), 422);
            return null;
        }
        $file = $_FILES['excel_file'];
        if (!is_uploaded_file($file['tmp_name'])) {
            $this->json(array('message' => 'Upload file tidak valid.'), 422);
            return null;
        }
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($extension, array('xlsx', 'xls'), true)) {
            $this->json(array('message' => 'Format file harus .xlsx atau .xls.'), 422);
            return null;
        }
        if ((int) $file['size'] > self::MAX_FILE_SIZE) {
            $this->json(array('message' => 'Ukuran file maksimal 5 MB.'), 422);
            return null;
        }

        try {
            return $this->excel_question_importer->parse($file['tmp_name']);
        } catch (Throwable $exception) {
            log_message('error', 'Import soal Excel gagal: ' . $exception->getMessage());
            $this->json(array('message' => 'File Excel tidak dapat dibaca. Gunakan template yang disediakan.'), 422);
            return null;
        }
    }

    private function authorisedBank($bankId)
    {
        if (!ctype_digit((string) $bankId)) {
            $this->json(array('message' => 'Bank soal tidak valid.'), 422);
            return null;
        }
        $bank = $this->db->where('id_bank', $bankId)->get('cbt_bank_soal')->row();
        if (!$bank) {
            $this->json(array('message' => 'Bank soal tidak ditemukan.'), 404);
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
            $this->json(array('message' => 'Anda tidak memiliki akses ke bank soal ini.'), 403);
            return null;
        }
        return $bank;
    }

    private function findDuplicates($bankId, $questions)
    {
        $existing = $this->db->select('jenis, nomor_soal')->where('bank_id', $bankId)->get('cbt_soal')->result();
        $keys = array();
        foreach ($existing as $row) {
            $keys[$row->jenis . ':' . $row->nomor_soal] = true;
        }
        $errors = array();
        foreach ($questions as $question) {
            $key = $question['jenis'] . ':' . $question['nomor_soal'];
            if (isset($keys[$key])) {
                $errors[] = 'Jenis ' . $question['jenis'] . ', nomor ' . $question['nomor_soal'] . ' sudah ada.';
            }
        }
        return $errors;
    }

    private function updateBankStatus($bankId, $bank)
    {
        $counts = array('1' => 0, '2' => 0, '6' => 0);
        $result = $this->db->select('jenis, COUNT(*) AS total', false)
            ->where('bank_id', $bankId)->where('tampilkan', '1')->group_by('jenis')->get('cbt_soal')->result();
        foreach ($result as $row) {
            $counts[(string) $row->jenis] = (int) $row->total;
        }
        /* PRD FR-11: hanya tiga tipe baku punya kuota. */
        $targets = array(
            '1' => (int) $bank->tampil_pg,
            '2' => (int) $bank->tampil_kompleks,
            '6' => (int) ($bank->tampil_bs ?? 0)
        );
        $complete = true;
        foreach ($targets as $type => $target) {
            if ($counts[$type] < $target) {
                $complete = false;
                break;
            }
        }
        $this->db->where('id_bank', $bankId)->update('cbt_bank_soal', array('status_soal' => $complete ? '1' : '0'));
    }

    private function json($data, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
