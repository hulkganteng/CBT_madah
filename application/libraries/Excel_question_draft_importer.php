<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel_question_draft_importer
{
    const MAX_ROWS = 2000;

    private $normalizer;

    public function __construct()
    {
        $ci =& get_instance();
        $ci->load->library('question_import');
        $this->normalizer = $ci->question_import;
    }

    public function parse($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName('SOAL') ?: $spreadsheet->getSheet(0);
        $rows = $sheet->toArray('', true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            return array('items' => array(), 'file_errors' => array('File belum berisi soal.'));
        }

        $headers = array_map(array($this, 'normaliseHeader'), $rows[0]);
        if (!$this->hasAnyHeader($headers, array('pertanyaan', 'soal'))) {
            return array('items' => array(), 'file_errors' => array('Kolom Pertanyaan tidak ditemukan. Gunakan template yang disediakan.'));
        }
        if (!$this->hasAnyHeader($headers, array('jenis', 'question_type'))) {
            return array('items' => array(), 'file_errors' => array('Kolom Jenis tidak ditemukan. Gunakan template yang disediakan.'));
        }

        $items = array();
        $limit = min(count($rows), self::MAX_ROWS + 1);
        for ($index = 1; $index < $limit; $index++) {
            $values = array_pad($rows[$index], count($headers), '');
            $raw = array_combine($headers, array_slice($values, 0, count($headers)));
            if ($this->isEmptyRow($raw) || $this->isBlankQuestionRow($raw) || $this->isTemplateExample($raw)) {
                continue;
            }

            $normalized = $this->normalizer->normalizeQuestion($raw);
            $items[] = array(
                'row_number' => $index + 1,
                'raw_data' => $raw,
                'normalized_data' => $normalized,
                'errors' => $this->normalizer->validateQuestion($normalized),
                'warnings' => array(),
            );
        }

        $fileErrors = array();
        if (count($rows) > self::MAX_ROWS + 1) {
            $fileErrors[] = 'Maksimal ' . self::MAX_ROWS . ' soal per file.';
        }
        if (!$items && !$fileErrors) {
            $fileErrors[] = 'Tidak ada soal yang dapat dibaca dari file.';
        }
        return array('items' => $items, 'file_errors' => $fileErrors);
    }

    private function normaliseHeader($header)
    {
        $aliases = array(
            'nomor' => 'no', 'tipe' => 'jenis',
            'pilihan_a' => 'a', 'pilihan_b' => 'b', 'pilihan_c' => 'c', 'pilihan_d' => 'd', 'pilihan_e' => 'e',
            'jawaban_benar' => 'jawaban',
        );
        $header = trim(preg_replace('/[^a-z0-9]+/', '_', strtolower(trim((string) $header))), '_');
        return isset($aliases[$header]) ? $aliases[$header] : $header;
    }

    private function hasAnyHeader($headers, $expected)
    {
        return (bool) array_intersect($headers, $expected);
    }

    private function isEmptyRow($row)
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }
        return true;
    }

    private function isTemplateExample($row)
    {
        $number = isset($row['no']) ? strtoupper(trim((string) $row['no'])) : '';
        return $number === 'CONTOH' || $number === 'EXAMPLE';
    }

    private function isBlankQuestionRow($row)
    {
        foreach (array('pertanyaan', 'soal', 'a', 'b', 'c', 'd', 'e', 'jawaban', 'kunci', 'pembahasan') as $key) {
            if (isset($row[$key]) && trim((string) $row[$key]) !== '') {
                return false;
            }
        }
        return true;
    }
}
