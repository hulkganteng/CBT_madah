<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master_data_preview
{
    public function read(array $fields, bool $word): array
    {
        $ci = get_instance();
        $ci->load->library('upload');
        $ci->upload->initialize([
            'upload_path' => FCPATH . 'uploads/import/',
            'allowed_types' => $word ? 'docx' : 'xls|xlsx|csv',
            'max_size' => 2048,
            'encrypt_name' => true,
        ]);
        if (!$ci->upload->do_upload('upload_file')) {
            return ['status' => false, 'message' => strip_tags($ci->upload->display_errors())];
        }

        $file = $ci->upload->data('full_path');
        try {
            $rows = $word ? $this->wordRows($file) : $this->sheetRows($file);
            $data = [];
            foreach (array_slice($rows, 1) as $row) {
                $record = [];
                foreach ($fields as $index => $field) {
                    $record[$field] = trim((string) ($row[$index + 1] ?? ''));
                }
                if (count(array_filter($record, static function ($value) { return $value !== ''; })) > 0) {
                    $data[] = $record;
                }
            }
            return $data ? ['status' => true, 'rows' => $data]
                : ['status' => false, 'message' => 'File tidak berisi data. Gunakan template yang tersedia.'];
        } catch (Throwable $e) {
            return ['status' => false, 'message' => 'File tidak dapat dibaca. Gunakan template yang tersedia.'];
        } finally {
            unlink($file);
        }
    }

    private function sheetRows(string $file): array
    {
        $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($file, ['Xls', 'Xlsx', 'Csv']);
        if ($reader instanceof \PhpOffice\PhpSpreadsheet\Reader\Csv) {
            $reader->setValueBinder(new \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder());
        }
        $spreadsheet = $reader->load($file);
        try {
            return $spreadsheet->getActiveSheet()->toArray(null, false);
        } finally {
            $spreadsheet->disconnectWorksheets();
        }
    }

    private function wordRows(string $file): array
    {
        $word = \PhpOffice\PhpWord\IOFactory::load($file);
        $writer = new \PhpOffice\PhpWord\Writer\HTML($word);
        $doc = new DOMDocument();
        $doc->loadHTML($writer->getContent(), LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING);
        $table = $doc->getElementsByTagName('table')->item(0);
        $rows = [];
        if ($table) {
            foreach ($table->getElementsByTagName('tr') as $row) {
                $cells = [];
                foreach ($row->getElementsByTagName('td') as $cell) {
                    $cells[] = $cell->textContent;
                }
                $rows[] = $cells;
            }
        }
        return $rows;
    }
}
