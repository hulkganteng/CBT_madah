<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\NamedRange;
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
        $this->load->library('excel_question_draft_importer');
        $this->load->library('question_import');
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
        if ($bank) {
            $spreadsheet->getProperties()->setCustomProperty(Excel_question_draft_importer::BANK_CODE_PROPERTY, $bank->bank_kode);
        }
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('SOAL');
        $headers = array('NO', 'JENIS', 'PERTANYAAN', 'A', 'B', 'C', 'D', 'E', 'JAWABAN', 'PEMBAHASAN');
        $sheet->fromArray($headers, null, 'A1');
        $rows = $this->templateRows($bank);
        if ($rows) {
            $sheet->fromArray($rows, null, 'A2');
        }
        $lastRow = $bank ? max(2, count($rows) + 1) : 101;

        $sheet->getStyle('A1:J1')->getFont()->setName('Carlito')->setSize(11)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle('A1:J1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF17365D');
        $sheet->getStyle('A1:J1')->getAlignment()->setHorizontal('center')->setVertical('center');
        $sheet->getStyle('A2:J' . $lastRow)->getFont()->setName('Carlito')->setSize(11);
        $sheet->getStyle('A2:J' . $lastRow)->getAlignment()->setWrapText(true)->setVertical('top');
        $sheet->getRowDimension(1)->setRowHeight(32);
        $sheet->freezePane('A2');
        $widths = array('A' => 7, 'B' => 26, 'C' => 65, 'D' => 25, 'E' => 25, 'F' => 25, 'G' => 25, 'H' => 25, 'I' => 16, 'J' => 42);
        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $guide = $spreadsheet->createSheet();
        $guide->setTitle('PETUNJUK');
        $guide->fromArray(array(
            array('JENIS SOAL', 'CARA MENGISI', 'JAWABAN'),
            array('Pilihan Ganda', 'Isi pertanyaan dan sedikitnya pilihan A dan B.', 'Pilih satu huruf pada dropdown.'),
            array('Pilihan Ganda Kompleks', 'Isi pertanyaan dan pilihan jawaban yang diperlukan.', 'Pilih kombinasi huruf pada dropdown.'),
            array('Benar/Salah', 'Isi pertanyaan; kolom pilihan A-E dikosongkan.', 'Pilih Benar atau Salah.'),
            array('', 'Gunakan dropdown pada kolom JENIS dan JAWABAN.', ''),
            array('', 'Baris kosong tidak akan diimport.', ''),
            array('', 'Kolom PEMBAHASAN boleh dikosongkan.', ''),
            array('', 'Jangan mengubah nama kolom pada sheet SOAL.', '')
        ), null, 'A1');
        if ($bank) {
            $info = $this->templateBankInfo($bank);
            $guide->fromArray(array(array('', '', '')), null, 'A9');
            $guide->fromArray($info, null, 'A10');
        }
        $guide->getStyle('A1:C1')->getFont()->setName('Carlito')->setSize(11)->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $guide->getStyle('A1:C1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF17365D');
        $guide->getStyle('A1:C20')->getAlignment()->setWrapText(true)->setVertical('top');
        $guide->getColumnDimension('A')->setWidth(28);
        $guide->getColumnDimension('B')->setWidth(58);
        $guide->getColumnDimension('C')->setWidth(38);

        $letters = array_slice(array('A', 'B', 'C', 'D', 'E'), 0, $bank ? max(2, min(5, (int) $bank->opsi)) : 5);
        $complexAnswers = $this->complexAnswerCombinations($letters);
        $guide->fromArray(array('Pilihan Ganda', 'Pilihan Ganda Kompleks', 'Benar/Salah', 'Jenis Soal'), null, 'L1');
        $guide->fromArray(array_map(function ($value) { return array($value); }, $letters), null, 'L2');
        $guide->fromArray(array_map(function ($value) { return array($value); }, $complexAnswers), null, 'M2');
        $guide->fromArray(array(array('Benar'), array('Salah')), null, 'N2');
        $guide->fromArray(array(array('Pilihan Ganda'), array('Pilihan Ganda Kompleks'), array('Benar/Salah')), null, 'O2');
        foreach (range('L', 'O') as $column) {
            $guide->getColumnDimension($column)->setVisible(false);
        }

        $spreadsheet->addNamedRange(new NamedRange('JAWABAN_PG', $guide, '$L$2:$L$' . (count($letters) + 1)));
        $spreadsheet->addNamedRange(new NamedRange('JAWABAN_KOMPLEKS', $guide, '$M$2:$M$' . (count($complexAnswers) + 1)));
        $spreadsheet->addNamedRange(new NamedRange('JAWABAN_BS', $guide, '$N$2:$N$3'));
        $spreadsheet->addNamedRange(new NamedRange('JENIS_SOAL', $guide, '$O$2:$O$4'));

        $typeValidation = (new DataValidation())
            ->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)->setShowDropDown(true)->setShowErrorMessage(true)
            ->setErrorTitle('Jenis soal tidak valid')->setError('Pilih jenis soal dari dropdown.')
            ->setFormula1('=JENIS_SOAL');
        $sheet->setDataValidation('B2:B' . $lastRow, $typeValidation);

        $answerValidation = (new DataValidation())
            ->setType(DataValidation::TYPE_LIST)->setErrorStyle(DataValidation::STYLE_STOP)
            ->setAllowBlank(true)->setShowDropDown(true)->setShowErrorMessage(true)
            ->setErrorTitle('Jawaban tidak valid')->setError('Pilih jawaban dari dropdown sesuai jenis soal.')
            ->setFormula1('=INDIRECT(IF($B2="Pilihan Ganda","JAWABAN_PG",IF($B2="Pilihan Ganda Kompleks","JAWABAN_KOMPLEKS",IF($B2="Benar/Salah","JAWABAN_BS","JAWABAN_PG"))))');
        $sheet->setDataValidation('I2:I' . $lastRow, $answerValidation);

        $suffix = $bank ? '_' . preg_replace('/[^A-Za-z0-9_-]+/', '_', $bank->bank_kode) : '';
        $filename = 'template_import_soal' . $suffix . '.xlsx';
        $stream = fopen('php://temp', 'w+b');
        (new Xlsx($spreadsheet))->save($stream);
        rewind($stream);
        $contents = stream_get_contents($stream);
        fclose($stream);
        $spreadsheet->disconnectWorksheets();

        $this->output
            ->set_content_type('application/vnd.openxmlformats-officedocument.spreadsheetml.sheet')
            ->set_header('Content-Disposition: attachment; filename="' . $filename . '"')
            ->set_header('Content-Length: ' . strlen($contents))
            ->set_header('Cache-Control: max-age=0')
            ->set_output($contents);
    }

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
        $labels = array(1 => 'Pilihan Ganda', 2 => 'Pilihan Ganda Kompleks', 6 => 'Benar/Salah');
        foreach ($quota as $item) {
            for ($i = 0; $i < $item['total'] && count($rows) < Excel_question_draft_importer::MAX_ROWS; $i++) {
                $rows[] = array(count($rows) + 1, $labels[$item['jenis']], '', '', '', '', '', '', '', '');
            }
        }
        return $rows;
    }

    private function complexAnswerCombinations($letters)
    {
        $answers = array();
        for ($mask = 1; $mask < (1 << count($letters)); $mask++) {
            $selected = array();
            foreach ($letters as $index => $letter) {
                if ($mask & (1 << $index)) {
                    $selected[] = $letter;
                }
            }
            $answers[] = implode(',', $selected);
        }
        usort($answers, function ($left, $right) {
            return substr_count($left, ',') <=> substr_count($right, ',') ?: strcmp($left, $right);
        });
        return $answers;
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
            array('KUOTA PILIHAN GANDA', (int) $bank->tampil_pg, 'soal'),
            array('KUOTA PILIHAN GANDA KOMPLEKS', (int) $bank->tampil_kompleks, 'soal'),
            array('KUOTA BENAR/SALAH', (int) ($bank->tampil_bs ?? 0), 'soal'),
            array('OPSI JAWABAN', (int) $bank->opsi, 'jumlah pilihan A-E'),
        );
    }

    public function create()
    {
        $bankId = $this->input->post('bank_id', true);
        if (!$this->authorisedBank($bankId)) {
            return;
        }
        $parsed = $this->parseUpload();
        if (!$parsed) {
            return;
        }
        if ($parsed['file_errors']) {
            $this->json(array('message' => 'File belum dapat diproses.', 'errors' => $parsed['file_errors']), 422);
            return;
        }

        $now = date('Y-m-d H:i:s');
        if (!$this->db->trans_begin()) {
            $this->json(['message' => 'Draf impor belum dapat dibuat. Silakan coba lagi.'], 500);
            return;
        }
        $lockedBank = $this->db->query('SELECT bank_kode FROM cbt_bank_soal WHERE id_bank = ? FOR UPDATE', [(int) $bankId]);
        if (!$lockedBank || !($bank = $lockedBank->row())) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Bank soal tidak ditemukan.'], 404);
            return;
        }
        if ($parsed['bank_code'] !== '' && strcasecmp($parsed['bank_code'], (string) $bank->bank_kode) !== 0) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Template berasal dari bank lain. Buka bank sumber atau gunakan template khusus bank tujuan.'], 422);
            return;
        }
        $draftSaved = $this->db->insert('cbt_soal_import', array(
            'bank_id' => (int) $bankId,
            'user_id' => (int) $this->ion_auth->user()->row()->id,
            'source_type' => 'excel',
            'original_filename' => $this->databaseSafeFilename($_FILES['excel_file']['name']),
            'status' => 'draft',
            'created_at' => $now,
            'updated_at' => $now,
        ));
        if (!$draftSaved) {
            $this->failDraftTransaction('header');
            return;
        }
        $importId = (int) $this->db->insert_id();
        $existing = $this->existingQuestionKeys($bankId);
        $rows = array();
        foreach ($parsed['items'] as $item) {
            $warning = $this->duplicateWarning($item['normalized_data'], $existing);
            if ($warning) {
                $item['warnings'][] = $warning;
            }
            $rows[] = array(
                'import_id' => $importId,
                'row_number' => $item['row_number'],
                'question_type' => $item['normalized_data']['type'],
                'raw_data' => $this->jsonForDatabase($item['raw_data']),
                'normalized_data' => $this->jsonForDatabase($item['normalized_data']),
                'is_valid' => $item['errors'] ? 0 : 1,
                'validation_errors' => $this->jsonForDatabase(array('errors' => $item['errors'], 'warnings' => $item['warnings'])),
                'imported' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            );
            $questionKey = $this->question_import->comparisonKey($item['normalized_data']['question']);
            if ($questionKey !== '') {
                $existing[$questionKey] = true;
            }
        }
        if (!$this->insertRowsInSafeBatches('cbt_soal_import_item', $rows)) {
            $this->failDraftTransaction('items');
            return;
        }
        if (!$this->refreshCounts($importId) || $this->db->trans_status() === false) {
            $this->failDraftTransaction('counts');
            return;
        }
        $this->db->trans_commit();
        $this->json(array('message' => 'File berhasil diperiksa.', 'import_id' => $importId));
    }

    public function preview()
    {
        $this->create();
    }

    public function latest()
    {
        $bankId = $this->input->get('bank_id', true);
        if (!$this->authorisedBank($bankId)) {
            return;
        }
        $userId = (int) $this->ion_auth->user()->row()->id;
        $draft = $this->db->where(array('bank_id' => (int) $bankId, 'user_id' => $userId, 'status' => 'draft'))
            ->order_by('id', 'DESC')->get('cbt_soal_import')->row();
        $this->json(array('import_id' => $draft ? (int) $draft->id : null));
    }

    public function session()
    {
        $import = $this->authorisedImport($this->input->get('import_id', true));
        if (!$import) {
            return;
        }
        $page = max(1, (int) $this->input->get('page', true));
        $perPage = (int) $this->input->get('per_page', true) === 50 ? 50 : 20;
        $filter = $this->input->get('filter', true);

        $this->db->where('import_id', $import->id);
        $this->db->where('imported', 0);
        if ($filter === 'ready') {
            $this->db->where('is_valid', 1);
        } elseif ($filter === 'problems') {
            $this->db->where('is_valid', 0);
        }
        $totalFiltered = $this->db->count_all_results('cbt_soal_import_item');

        $this->db->where('import_id', $import->id);
        $this->db->where('imported', 0);
        if ($filter === 'ready') {
            $this->db->where('is_valid', 1);
        } elseif ($filter === 'problems') {
            $this->db->where('is_valid', 0);
        }
        $items = $this->db->order_by('row_number', 'ASC')->limit($perPage, ($page - 1) * $perPage)
            ->get('cbt_soal_import_item')->result();

        $responseItems = array();
        foreach ($items as $item) {
            $responseItems[] = $this->itemForResponse($item);
        }
        $this->json(array(
            'import' => $import,
            'items' => $responseItems,
            'pagination' => array(
                'page' => $page,
                'per_page' => $perPage,
                'total' => $totalFiltered,
                'pages' => max(1, (int) ceil($totalFiltered / $perPage)),
            ),
        ));
    }

    public function updateItem()
    {
        $import = $this->authorisedImport($this->input->post('import_id', true));
        if (!$import) {
            return;
        }
        if ($import->status !== 'draft') {
            $this->json(array('message' => 'Draft import ini sudah selesai atau dibatalkan.'), 409);
            return;
        }
        $itemId = (int) $this->input->post('item_id', true);
        if (!$this->db->trans_begin()) {
            $this->json(['message' => 'Perbaikan draf belum dapat dimulai.'], 500);
            return;
        }
        $locked = $this->db->query('SELECT status FROM cbt_soal_import WHERE id = ? FOR UPDATE', [(int) $import->id]);
        $draft = $locked ? $locked->row() : null;
        if (!$draft || $draft->status !== 'draft') {
            $this->db->trans_rollback();
            $this->json(['message' => 'Draf sudah selesai atau dibatalkan.'], 409);
            return;
        }
        $scope = ['id' => $itemId, 'import_id' => $import->id, 'imported' => 0];
        $item = $this->db->where($scope)->get('cbt_soal_import_item')->row();
        if (!$item) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Soal draft tidak ditemukan.'), 404);
            return;
        }

        $raw = array(
            'jenis' => $this->input->post('type', true),
            'pertanyaan' => $this->input->post('question', false),
            'a' => $this->input->post('option_a', false),
            'b' => $this->input->post('option_b', false),
            'c' => $this->input->post('option_c', false),
            'd' => $this->input->post('option_d', false),
            'e' => $this->input->post('option_e', false),
            'jawaban' => $this->input->post('correct_answers', true) ?: array(),
            'pembahasan' => $this->input->post('explanation', false),
        );
        $normalized = $this->question_import->normalizeQuestion($raw);
        $errors = $this->question_import->validateQuestion($normalized);
        $warnings = array();
        $warning = $this->duplicateWarning($normalized, $this->existingQuestionKeys($import->bank_id));
        if ($warning) {
            $warnings[] = $warning;
        }
        $saved = $this->db->where($scope)->update('cbt_soal_import_item', array(
            'question_type' => $normalized['type'],
            'raw_data' => $this->jsonForDatabase($raw),
            'normalized_data' => $this->jsonForDatabase($normalized),
            'is_valid' => $errors ? 0 : 1,
            'validation_errors' => $this->jsonForDatabase(array('errors' => $errors, 'warnings' => $warnings)),
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $result = $saved && $this->refreshCounts($import->id) ? $this->db->where($scope)->get('cbt_soal_import_item') : null;
        $updated = $result ? $result->row() : null;
        if (!$updated || !$this->db->trans_status() || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Perbaikan draf gagal disimpan. Silakan coba lagi.'], 500);
            return;
        }
        $this->json(array('message' => $errors ? 'Soal masih perlu diperbaiki.' : 'Perbaikan berhasil disimpan.', 'item' => $this->itemForResponse($updated)));
    }

    public function commit()
    {
        $import = $this->authorisedImport($this->input->post('import_id', true));
        if (!$import) {
            return;
        }
        if ($import->status !== 'draft') {
            $this->json(array('message' => 'Draft import ini sudah selesai atau dibatalkan.'), 409);
            return;
        }
        $mode = $this->input->post('mode', true) ?? 'append';
        if (!in_array($mode, ['append', 'activate_package'], true)) {
            $this->json(['message' => 'Cara impor tidak valid.'], 422);
            return;
        }
        $activatePackage = $mode === 'activate_package';
        $expectedBank = $this->input->post('bank_id', true);
        if ($expectedBank !== null && (string) $expectedBank !== (string) $import->bank_id) {
            $this->json(['message' => 'Draf berasal dari bank lain. Muat ulang halaman impor.'], 409);
            return;
        }

        if (!$this->db->trans_begin()) {
            $this->json(['message' => 'Transaksi impor belum dapat dimulai. Silakan coba lagi.'], 500);
            return;
        }
        $lockedBank = $this->db->query('SELECT * FROM cbt_bank_soal WHERE id_bank = ? FOR UPDATE', [(int) $import->bank_id]);
        if (!$lockedBank || !($bank = $lockedBank->row())) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Bank soal tidak ditemukan.'], 404);
            return;
        }
        if ($activatePackage
            && ($this->db->where(['id_bank' => $bank->id_bank, 'status' => 1])->count_all_results('cbt_jadwal')
                || $this->db->where('id_bank', $bank->id_bank)->count_all_results('cbt_soal_siswa'))) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Bank memiliki jadwal aktif atau sudah digunakan peserta. Gunakan bank baru untuk paket pengganti.'], 409);
            return;
        }
        $this->db->where(array('id' => $import->id, 'status' => 'draft'))->update('cbt_soal_import', array(
            'status' => 'committing',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        if ($this->db->affected_rows() !== 1) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Draft import sedang diproses atau sudah selesai.'), 409);
            return;
        }

        $pending = $this->db->where(array('import_id' => $import->id, 'is_valid' => 1, 'imported' => 0))
            ->order_by('row_number', 'ASC')->get('cbt_soal_import_item');
        if (!$pending) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Soal dalam draf gagal dibaca. Silakan coba lagi.'], 500);
            return;
        }
        $items = $pending->result();
        if (!$items) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Belum ada soal yang siap diimport.'), 422);
            return;
        }

        $quota = [1 => (int) $bank->tampil_pg, 2 => (int) $bank->tampil_kompleks, 6 => (int) ($bank->tampil_bs ?? 0)];
        $existing = $this->db->where('bank_id', $bank->id_bank)->order_by('tampilkan', 'DESC')
            ->order_by('id_soal', 'ASC')->get('cbt_soal');
        if (!$existing) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Soal pada bank tujuan gagal dibaca.'], 500);
            return;
        }
        $plan = $this->planImport($items, $existing->result_array(), $quota, $activatePackage);
        if (!$plan['itemIds']) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Belum ada soal yang siap diimport.'), 422);
            return;
        }
        if (!$activatePackage && $plan['rows'] && count($plan['itemIds']) === (int) $import->total_items
            && $this->wasPackageImported($import, $items)) {
            $this->db->trans_rollback();
            $this->json(['message' => 'Paket ini pernah diimpor. Periksa soal yang sudah tersimpan atau pilih Ganti paket soal aktif jika ingin memakai kembali versi dari file ini.'], 409);
            return;
        }
        if ($activatePackage && (count($plan['itemIds']) !== (int) $import->total_items
            || $plan['selectedCounts'] !== $quota
            || (int) ($bank->tampil_jodohkan ?? 0) + (int) ($bank->tampil_isian ?? 0) + (int) ($bank->tampil_esai ?? 0) > 0)) {
            $this->db->trans_rollback();
            $this->json(['message' => sprintf(
                'Paket pengganti harus berupa draf lengkap dengan %d PG, %d PG Kompleks, dan %d Benar/Salah unik, sesuai kuota bank.',
                $quota[1], $quota[2], $quota[6]
            )], 422);
            return;
        }

        if ($activatePackage) {
            $this->db->where(['bank_id' => $bank->id_bank, 'tampilkan' => 1])->update('cbt_soal', ['tampilkan' => 0]);
            if ($plan['reusedIds']) {
                $this->db->where('bank_id', $bank->id_bank)->where_in('id_soal', $plan['reusedIds'])
                    ->update('cbt_soal', ['tampilkan' => 1]);
            }
        }
        $now = time();
        $rows = array_map(static function ($row) use ($bank, $now) {
            return $row + ['bank_id' => (int) $bank->id_bank, 'created_on' => $now, 'updated_on' => $now];
        }, $plan['rows']);
        if (!$this->db->trans_status() || !$this->insertRowsInSafeBatches('cbt_soal', $rows)) {
            $this->db->trans_rollback();
            $this->logDatabaseError('commit-items');
            $this->json(array('message' => 'Soal gagal disimpan. Draft tetap tersedia untuk dicoba kembali.'), 500);
            return;
        }
        $this->db->where_in('id', $plan['itemIds'])->update('cbt_soal_import_item', array('imported' => 1, 'updated_at' => date('Y-m-d H:i:s')));
        $remaining = $this->db->where(array('import_id' => $import->id, 'imported' => 0))->count_all_results('cbt_soal_import_item');
        $this->db->where('id', $import->id)->update('cbt_soal_import', array(
            'status' => $remaining ? 'draft' : 'committed',
            'updated_at' => date('Y-m-d H:i:s'),
        ));
        $this->load->model('Cbt_model', 'cbt');
        $updated = $this->cbt->refreshBankQuestionStatus($bank);
        $this->refreshCounts($import->id);
        if (!$updated || $this->db->trans_status() === false || !$this->db->trans_commit()) {
            $this->db->trans_rollback();
            $this->json(array('message' => 'Soal gagal disimpan. Draft tetap tersedia untuk dicoba kembali.'), 500);
            return;
        }
        $this->json(array('message' => count($rows) . ' soal baru ditambahkan.', 'inserted' => count($rows),
            'duplicates' => $plan['duplicates'], 'activated' => $plan['activated'], 'package_activated' => $activatePackage,
            'remaining' => $remaining));
    }

    private function planImport(array $items, array $existing, array $quota, $activatePackage)
    {
        $numbers = array_fill_keys(array_keys($quota), 0);
        $visibleRemaining = $quota;
        $existingIds = [];
        foreach ($existing as $row) {
            $type = (int) $row['jenis'];
            if (!isset($numbers[$type])) {
                continue;
            }
            $numbers[$type] = max($numbers[$type], (int) $row['nomor_soal']);
            $visibleRemaining[$type] -= (int) $row['tampilkan'] === 1 ? 1 : 0;
            $key = $this->question_import->databaseQuestionKey($row);
            $existingIds[$key] = $existingIds[$key] ?? (int) $row['id_soal'];
        }

        $plan = ['rows' => [], 'itemIds' => [], 'reusedIds' => [], 'selectedCounts' => array_fill_keys(array_keys($quota), 0),
            'duplicates' => 0, 'activated' => 0];
        $seen = [];
        foreach ($items as $item) {
            $question = json_decode($item->normalized_data, true);
            if (!is_array($question) || $this->question_import->validateQuestion($question)) {
                continue;
            }
            $row = $this->question_import->toDatabaseRow($question, 0, false);
            $type = (int) $row['jenis'];
            $key = $this->question_import->databaseQuestionKey($row);
            $plan['itemIds'][] = (int) $item->id;
            if (isset($seen[$key])) {
                $plan['duplicates']++;
                continue;
            }
            $seen[$key] = true;
            $plan['selectedCounts'][$type]++;
            if (isset($existingIds[$key])) {
                $plan['duplicates']++;
                if ($activatePackage) {
                    $plan['reusedIds'][] = $existingIds[$key];
                }
                continue;
            }
            $visible = $activatePackage || $visibleRemaining[$type] > 0;
            if ($visible) {
                $visibleRemaining[$type]--;
                $plan['activated']++;
            }
            $row['nomor_soal'] = ++$numbers[$type];
            $row['tampilkan'] = $visible ? '1' : '0';
            $plan['rows'][] = $row;
        }
        $plan['activated'] += count($plan['reusedIds']);
        return $plan;
    }

    private function wasPackageImported($import, array $items)
    {
        $history = $this->db->select('d.id, i.normalized_data')->from('cbt_soal_import d')
            ->join('cbt_soal_import_item i', 'i.import_id = d.id')
            ->where(['d.bank_id' => $import->bank_id, 'd.status' => 'committed', 'd.total_items' => count($items),
                'i.imported' => 1, 'i.is_valid' => 1])->get();
        if (!$history) {
            return false;
        }
        $incoming = array_column($items, 'normalized_data');
        sort($incoming, SORT_STRING);
        $packages = [];
        foreach ($history->result() as $item) {
            $packages[$item->id][] = $item->normalized_data;
        }
        foreach ($packages as $previous) {
            sort($previous, SORT_STRING);
            if ($previous === $incoming) {
                return true;
            }
        }
        return false;
    }

    public function cancel()
    {
        $import = $this->authorisedImport($this->input->post('import_id', true));
        if (!$import) {
            return;
        }
        if ($import->status !== 'draft') {
            $this->json(array('message' => 'Import yang sedang diproses atau sudah selesai tidak dapat dibatalkan.'), 409);
            return;
        }
        $this->db->where(array('id' => $import->id, 'status' => 'draft'))->update('cbt_soal_import', array('status' => 'cancelled', 'updated_at' => date('Y-m-d H:i:s')));
        if ($this->db->affected_rows() !== 1) {
            $this->json(array('message' => 'Import sedang diproses atau sudah selesai.'), 409);
            return;
        }
        $this->json(array('message' => 'Draft import dibatalkan.'));
    }

    public function upload()
    {
        $this->json(array('message' => 'Import langsung telah dinonaktifkan. Upload kembali untuk membuat draft.'), 410);
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
            return $this->excel_question_draft_importer->parse($file['tmp_name']);
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

    private function authorisedImport($importId)
    {
        if (!ctype_digit((string) $importId)) {
            $this->json(array('message' => 'Draft import tidak valid.'), 422);
            return null;
        }
        $import = $this->db->where('id', (int) $importId)->get('cbt_soal_import')->row();
        if (!$import) {
            $this->json(array('message' => 'Draft import tidak ditemukan.'), 404);
            return null;
        }
        if (!$this->ion_auth->is_admin() && (int) $import->user_id !== (int) $this->ion_auth->user()->row()->id) {
            $this->json(array('message' => 'Anda tidak memiliki akses ke draft import ini.'), 403);
            return null;
        }
        if (!$this->authorisedBank($import->bank_id)) {
            return null;
        }
        return $import;
    }

    private function refreshCounts($importId)
    {
        $total = $this->db->where('import_id', $importId)->count_all_results('cbt_soal_import_item');
        $valid = $this->db->where(array('import_id' => $importId, 'is_valid' => 1, 'imported' => 0))->count_all_results('cbt_soal_import_item');
        $invalid = $this->db->where(array('import_id' => $importId, 'is_valid' => 0, 'imported' => 0))->count_all_results('cbt_soal_import_item');
        return $this->db->where('id', $importId)->update('cbt_soal_import', array(
            'total_items' => $total,
            'valid_items' => $valid,
            'invalid_items' => $invalid,
            'updated_at' => date('Y-m-d H:i:s'),
        ));
    }

    /**
     * Jangan mengandalkan jumlah baris tetap untuk insert_batch. Instalasi
     * XAMPP umumnya memakai max_allowed_packet 1 MB, sedangkan dua kolom JSON
     * pada draft dapat membuat 200 soal melewati batas tersebut.
     */
    private function insertRowsInSafeBatches($table, $rows, $maxBytes = 262144)
    {
        $batch = array();
        $batchBytes = 0;
        foreach ($rows as $row) {
            $rowBytes = strlen(serialize($row));
            if ($batch && $batchBytes + $rowBytes > $maxBytes) {
                if (!$this->db->insert_batch($table, $batch)) {
                    $this->logDatabaseError($table);
                    return false;
                }
                $batch = array();
                $batchBytes = 0;
            }
            $batch[] = $row;
            $batchBytes += $rowBytes;
        }
        if ($batch && !$this->db->insert_batch($table, $batch)) {
            $this->logDatabaseError($table);
            return false;
        }
        return true;
    }

    private function jsonForDatabase($value)
    {
        // Simpan Unicode sebagai escape JSON ASCII agar tabel utf8 MySQL lama
        // tetap dapat menampung karakter 4-byte tanpa mengubah isi soal.
        return json_encode($value);
    }

    private function databaseSafeFilename($filename)
    {
        $filename = basename((string) $filename);
        $safe = preg_replace('/[\x{10000}-\x{10FFFF}]/u', '_', $filename);
        return $safe === NULL ? 'import_soal.xlsx' : $safe;
    }

    private function failDraftTransaction($stage)
    {
        $this->logDatabaseError('draft-' . $stage);
        $this->db->trans_rollback();
        $this->json(array('message' => 'Draft import gagal disimpan. Silakan coba lagi.'), 500);
    }

    private function logDatabaseError($stage)
    {
        $error = $this->db->error();
        $code = isset($error['code']) ? (int) $error['code'] : 0;
        $message = isset($error['message']) ? $error['message'] : 'unknown database error';
        log_message('error', 'Import soal Excel gagal pada ' . $stage . ' [DB ' . $code . ']: ' . $message);
    }

    private function itemForResponse($item)
    {
        $messages = json_decode($item->validation_errors, true) ?: array();
        return array(
            'id' => (int) $item->id,
            'row_number' => (int) $item->row_number,
            'question_type' => $item->question_type,
            'question' => json_decode($item->normalized_data, true),
            'is_valid' => (bool) $item->is_valid,
            'imported' => (bool) $item->imported,
            'errors' => isset($messages['errors']) ? $messages['errors'] : array(),
            'warnings' => isset($messages['warnings']) ? $messages['warnings'] : array(),
        );
    }

    private function existingQuestionKeys($bankId)
    {
        $rows = $this->db->select('soal')->where('bank_id', $bankId)->get('cbt_soal')->result();
        $keys = array();
        foreach ($rows as $row) {
            $key = $this->question_import->comparisonKey($row->soal);
            if ($key !== '') {
                $keys[$key] = true;
            }
        }
        return $keys;
    }

    private function duplicateWarning($question, $existing)
    {
        $key = $this->question_import->comparisonKey(isset($question['question']) ? $question['question'] : '');
        return $key !== '' && isset($existing[$key]) ? 'Ada soal dengan teks serupa. Periksa opsi dan kunci; salinan yang identik akan dilewati.' : null;
    }

    private function json($data, $status = 200)
    {
        $this->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($data));
    }
}
