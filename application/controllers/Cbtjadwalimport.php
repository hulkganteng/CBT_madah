<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class Cbtjadwalimport extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }
        if (!$this->ion_auth->is_admin()) {
            show_error('Hanya Administrator yang dapat mengimpor jadwal ujian.', 403, 'Akses Terlarang');
        }
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->library('jadwal_import');
    }

    public function index()
    {
        $user = $this->ion_auth->user()->row();
        $data = array(
            'user' => $user,
            'judul' => 'Impor Jadwal Ujian',
            'subjudul' => 'Impor Jadwal Ujian',
            'setting' => $this->dashboard->getSetting(),
            'profile' => $this->dashboard->getProfileAdmin($user->id),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $this->dashboard->getTahunActive(),
            'smt' => $this->dashboard->getSemester(),
            'smt_active' => $this->dashboard->getSemesterActive(),
        );
        $this->load->view('_templates/dashboard/_header', $data);
        $this->load->view('cbt/jadwal/import', $data);
        $this->load->view('_templates/dashboard/_footer');
    }

    public function template()
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Jadwal');
        $sheet->fromArray(Jadwal_import::HEADERS, null, 'A1');
        $sheet->fromArray(array('MTK-XII-01', 'PAS', date('Y-m-d'), date('Y-m-d'), 90, 30, 1, 1, 1, 0, 0, 1), null, 'A2');
        $sheet->getStyle('A1:L1')->getFont()->setBold(true);
        foreach (range('A', 'L') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $file = tempnam(sys_get_temp_dir(), 'jadwal-import-');
        (new Xlsx($spreadsheet))->save($file);
        $contents = file_get_contents($file);
        @unlink($file);
        $spreadsheet->disconnectWorksheets();

        $this->output
            ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="template-impor-jadwal.xlsx"')
            ->set_output($contents);
    }

    public function preview()
    {
        $file = $this->uploadFile();
        if ($file === null) {
            return;
        }

        try {
            $reader = IOFactory::createReaderForFile($file);
            $reader->setReadDataOnly(true);
            $sheetRows = $reader->load($file)->getActiveSheet()->toArray();
            $result = $this->jadwal_import->parseRows($sheetRows);
            if (!$result['errors']) {
                $result = $this->resolveReferences($result['rows']);
            }
            $this->json(array(
                'status' => !$result['errors'],
                'rows' => $result['rows'],
                'errors' => $result['errors'],
            ));
        } catch (Throwable $exception) {
            $this->json(array('status' => false, 'rows' => array(), 'errors' => array('File tidak dapat dibaca. Gunakan template yang tersedia.')));
        } finally {
            @unlink($file);
        }
    }

    public function commit()
    {
        $postedRows = json_decode((string) $this->input->post('rows', true), true);
        if (!is_array($postedRows)) {
            return $this->json(array('status' => false, 'message' => 'Data pratinjau tidak valid.'));
        }

        $sheetRows = array(Jadwal_import::HEADERS);
        foreach ($postedRows as $row) {
            $sheetRows[] = array_map(static function ($header) use ($row) {
                return $row[$header] ?? '';
            }, Jadwal_import::HEADERS);
        }
        $parsed = $this->jadwal_import->parseRows($sheetRows);
        if ($parsed['errors']) {
            return $this->json(array('status' => false, 'message' => implode(' ', $parsed['errors'])));
        }

        $this->db->trans_begin();
        $resolved = $this->resolveReferences($parsed['rows']);
        if ($resolved['errors']) {
            $this->db->trans_rollback();
            return $this->json(array('status' => false, 'message' => implode(' ', $resolved['errors'])));
        }

        $bankIds = array_values(array_unique(array_column($resolved['rows'], 'id_bank')));
        $typeIds = array_values(array_unique(array_column($resolved['rows'], 'id_jenis')));
        if (!$bankIds || !$typeIds) {
            $this->db->trans_rollback();
            return $this->json(['status' => FALSE, 'message' => 'Tidak ada jadwal yang siap diimpor.']);
        }
        $banks = $this->db->query('SELECT id_bank FROM cbt_bank_soal WHERE id_bank IN ? AND status = 1 AND status_soal = 1 ORDER BY id_bank FOR UPDATE', [$bankIds]);
        $types = $this->db->query('SELECT id_jenis FROM cbt_jenis WHERE id_jenis IN ? ORDER BY id_jenis FOR UPDATE', [$typeIds]);
        if (!$banks || $banks->num_rows() !== count($bankIds) || !$types || $types->num_rows() !== count($typeIds)) {
            $this->db->trans_rollback();
            return $this->json(['status' => FALSE, 'message' => 'Bank soal atau jenis ujian berubah. Muat ulang pratinjau.']);
        }
        foreach ($resolved['rows'] as $row) {
            $duplicate = $this->db->query('SELECT id_jadwal FROM cbt_jadwal WHERE id_bank = ? AND id_jenis = ? FOR UPDATE',
                [$row['id_bank'], $row['id_jenis']]);
            if (!$duplicate || $duplicate->num_rows()) {
                $this->db->trans_rollback();
                return $this->json(['status' => FALSE, 'message' => 'Jadwal sudah ada atau gagal diperiksa. Muat ulang pratinjau.']);
            }
        }

        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $insert = array();
        foreach ($resolved['rows'] as $row) {
            $insert[] = array(
                'id_tp' => $tp->id_tp,
                'id_smt' => $smt->id_smt,
                'id_bank' => $row['id_bank'],
                'id_jenis' => $row['id_jenis'],
                'tgl_mulai' => $row['tanggal_mulai'],
                'tgl_selesai' => $row['tanggal_selesai'],
                'durasi_ujian' => $row['durasi'],
                'jarak' => $row['durasi_minimal'],
                'acak_soal' => $row['acak_soal'],
                'acak_opsi' => $row['acak_jawaban'],
                'token' => $row['gunakan_token'],
                'hasil_tampil' => $row['tampilkan_hasil'],
                'reset_login' => $row['reset_izin'],
                'status' => $row['aktif'],
            );
        }
        $dbDebug = $this->db->db_debug;
        $this->db->db_debug = false;
        $saved = $this->db->insert_batch('cbt_jadwal', $insert);
        $this->db->db_debug = $dbDebug;
        if (!$saved || !$this->db->trans_status() || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            return $this->json(array('status' => false, 'message' => 'Jadwal gagal disimpan. Tidak ada data yang diubah.'));
        }
        $this->load->model('Log_model', 'logging');
        $this->logging->saveLog(3, 'mengimpor ' . count($insert) . ' jadwal ujian');
        return $this->json(array('status' => true, 'message' => count($insert) . ' jadwal berhasil diimpor.'));
    }

    private function uploadFile()
    {
        $this->load->library('upload');
        $this->upload->initialize(array(
            'upload_path' => FCPATH . 'uploads/import/',
            'allowed_types' => 'xls|xlsx|csv',
            'max_size' => 2048,
            'encrypt_name' => true,
        ));
        if (!$this->upload->do_upload('upload_file')) {
            $message = trim(strip_tags($this->upload->display_errors('', '')));
            $this->json(array('status' => false, 'rows' => array(), 'errors' => array($message ?: 'File gagal diunggah.')));
            return null;
        }
        return $this->upload->data('full_path');
    }

    private function resolveReferences(array $rows): array
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        if (!$tp || !$smt) {
            return array('rows' => array(), 'errors' => array('Tahun pelajaran atau semester aktif belum ditentukan.'));
        }

        $banks = array();
        $bankRows = $this->db->select('id_bank, bank_kode')
            ->where(array('id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt, 'status' => 1, 'status_soal' => 1))
            ->get('cbt_bank_soal')->result_array();
        foreach ($bankRows as $bank) {
            $key = strtoupper(trim($bank['bank_kode']));
            $banks[$key] = array_key_exists($key, $banks) ? null : (int) $bank['id_bank'];
        }
        $types = array();
        foreach ($this->db->select('id_jenis, kode_jenis')->get('cbt_jenis')->result_array() as $type) {
            $types[strtoupper(trim($type['kode_jenis']))] = (int) $type['id_jenis'];
        }
        $existing = array();
        foreach ($this->db->select('id_bank, id_jenis')->get('cbt_jadwal')->result_array() as $schedule) {
            $existing[$schedule['id_bank'] . '|' . $schedule['id_jenis']] = true;
        }

        $errors = array();
        foreach ($rows as &$row) {
            if (!array_key_exists($row['kode_bank'], $banks)) {
                $errors[] = 'Baris ' . $row['_row'] . ': kode bank ' . $row['kode_bank'] . ' tidak tersedia pada periode aktif.';
                continue;
            }
            if ($banks[$row['kode_bank']] === null) {
                $errors[] = 'Baris ' . $row['_row'] . ': kode bank ' . $row['kode_bank'] . ' tidak unik.';
                continue;
            }
            if (!isset($types[$row['kode_jenis']])) {
                $errors[] = 'Baris ' . $row['_row'] . ': kode jenis ' . $row['kode_jenis'] . ' tidak ditemukan.';
                continue;
            }
            $row['id_bank'] = $banks[$row['kode_bank']];
            $row['id_jenis'] = $types[$row['kode_jenis']];
            if (isset($existing[$row['id_bank'] . '|' . $row['id_jenis']])) {
                $errors[] = 'Baris ' . $row['_row'] . ': jadwal untuk kombinasi bank dan jenis tersebut sudah ada.';
            }
        }
        unset($row);
        return array('rows' => $rows, 'errors' => $errors);
    }

    private function json(array $data)
    {
        return $this->output->set_content_type('application/json')->set_output(json_encode($data));
    }
}
