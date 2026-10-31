<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use PhpOffice\PhpSpreadsheet\IOFactory;

class Excel_question_importer
{
    const MAX_ROWS = 2000;

    public function parse($path)
    {
        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getSheetByName('SOAL') ?: $spreadsheet->getSheet(0);
        $rows = $sheet->toArray('', true, true, false);
        $spreadsheet->disconnectWorksheets();

        if (count($rows) < 2) {
            return array('questions' => array(), 'errors' => array('File tidak memiliki baris soal.'));
        }

        $headers = array_map(array($this, 'normaliseHeader'), $rows[0]);
        $required = array('no', 'jenis', 'soal', 'kunci');
        foreach ($required as $column) {
            if (!in_array($column, $headers, true)) {
                return array('questions' => array(), 'errors' => array('Kolom wajib tidak ditemukan: ' . strtoupper($column) . '.'));
            }
        }

        $questions = array();
        $errors = array();
        $seen = array();
        $limit = min(count($rows), self::MAX_ROWS + 1);

        for ($index = 1; $index < $limit; $index++) {
            $values = array_pad($rows[$index], count($headers), '');
            $row = array_combine($headers, array_slice($values, 0, count($headers)));
            if ($this->isEmptyRow($row)) {
                continue;
            }
            /* Baris kerangka template dari admin yang belum diisi guru
               dilewati, sehingga file boleh diimpor sebagian. */
            if ($this->isBlankQuestionRow($row)) {
                continue;
            }

            $result = $this->parseRow($row, $index + 1);
            if ($result['error']) {
                $errors[] = $result['error'];
                continue;
            }

            $key = $result['question']['jenis'] . ':' . $result['question']['nomor_soal'];
            if (isset($seen[$key])) {
                $errors[] = 'Baris ' . ($index + 1) . ': kombinasi NO dan JENIS duplikat.';
                continue;
            }
            $seen[$key] = true;
            $questions[] = $result['question'];
        }

        if (count($rows) > self::MAX_ROWS + 1) {
            $errors[] = 'Maksimal ' . self::MAX_ROWS . ' baris soal per file.';
        }

        return array('questions' => $questions, 'errors' => $errors);
    }

    private function parseRow($row, $excelRow)
    {
        $number = filter_var(trim((string) $this->value($row, 'no')), FILTER_VALIDATE_INT);
        $type = (int) trim((string) $this->value($row, 'jenis'));
        $questionText = trim((string) $this->value($row, 'soal'));
        $answer = trim((string) $this->value($row, 'kunci'));

        if ($number === false || $number < 1) {
            return $this->error($excelRow, 'NO harus berupa bilangan bulat positif.');
        }
        /* PRD FR-11: hanya PG (1), PG Kompleks (2), dan Benar/Salah (6). */
        if (!in_array($type, array(1, 2, 6), true)) {
            return $this->error($excelRow, 'JENIS harus 1 (PG), 2 (PG Kompleks), atau 6 (Benar/Salah).');
        }
        if ($questionText === '') {
            return $this->error($excelRow, 'SOAL wajib diisi.');
        }

        $options = array();
        foreach (array('a', 'b', 'c', 'd', 'e') as $letter) {
            $options[$letter] = trim((string) $this->value($row, 'opsi_' . $letter));
        }

        $question = array(
            'jenis' => (string) $type,
            'nomor_soal' => $number,
            'soal' => $this->html($questionText),
            'deskripsi' => '',
            'kesulitan' => $this->difficulty($this->value($row, 'kesulitan')),
            'timer' => '0',
            'timer_menit' => '0',
            'file' => serialize(array()),
            'opsi_a' => '',
            'opsi_b' => '',
            'opsi_c' => '',
            'opsi_d' => '',
            'opsi_e' => '',
            'jawaban' => '',
            'tampilkan' => $this->booleanValue($this->value($row, 'tampilkan'), true) ? '1' : '0'
        );

        if ($type === 1 || $type === 2) {
            return $this->parseChoice($question, $options, $answer, $excelRow, $type === 2);
        }
        if ($type === 6) {
            return $this->parseTrueFalse($question, $answer, $excelRow);
        }

        return $this->error($excelRow, 'Jenis soal tidak dikenal.');
    }

    private function parseChoice($question, $options, $answer, $excelRow, $multiple)
    {
        if ($options['a'] === '' || $options['b'] === '') {
            return $this->error($excelRow, 'Pilihan A dan B wajib diisi.');
        }

        $available = array();
        foreach ($options as $letter => $text) {
            if ($text !== '') {
                $available[] = $letter;
            }
        }
        $answers = preg_split('/\s*[,;]\s*/', strtolower($answer), -1, PREG_SPLIT_NO_EMPTY);
        $answers = array_values(array_unique($answers));
        if (!$answers || array_diff($answers, $available)) {
            return $this->error($excelRow, 'KUNCI harus merujuk ke opsi yang tersedia.');
        }
        if (!$multiple && count($answers) !== 1) {
            return $this->error($excelRow, 'Pilihan ganda biasa hanya memiliki satu KUNCI.');
        }

        if ($multiple) {
            $serialisedOptions = array();
            foreach ($available as $letter) {
                $serialisedOptions[$letter] = $this->html($options[$letter]);
            }
            $question['opsi_a'] = serialize($serialisedOptions);
            $question['jawaban'] = serialize($answers);
        } else {
            foreach ($available as $letter) {
                $question['opsi_' . $letter] = $this->html($options[$letter]);
            }
            $question['jawaban'] = $answers[0];
        }

        return array('question' => $question, 'error' => null);
    }

    private function parseTrueFalse($question, $answer, $excelRow)
    {
        $value = strtolower(trim($answer));
        $map = array('b' => 'b', 'benar' => 'b', 'true' => 'b', 's' => 's', 'salah' => 's', 'false' => 's');
        if (!isset($map[$value])) {
            return $this->error($excelRow, 'KUNCI Benar/Salah harus B atau S.');
        }
        /* Sama dengan form: jenis 6 hanya menyimpan kunci, opsi tetap kosong. */
        $question['jawaban'] = $map[$value];
        return array('question' => $question, 'error' => null);
    }

    private function normaliseHeader($header)
    {
        $header = strtolower(trim((string) $header));
        $header = preg_replace('/[^a-z0-9]+/', '_', $header);
        return trim($header, '_');
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

    private function isBlankQuestionRow($row)
    {
        if (trim((string) $this->value($row, 'soal')) !== '' || trim((string) $this->value($row, 'kunci')) !== '') {
            return false;
        }
        foreach (array('a', 'b', 'c', 'd', 'e') as $letter) {
            if (trim((string) $this->value($row, 'opsi_' . $letter)) !== '') {
                return false;
            }
        }
        return true;
    }

    private function html($value)
    {
        return nl2br(htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8'));
    }

    private function value($row, $key)
    {
        return isset($row[$key]) ? $row[$key] : '';
    }

    private function difficulty($value)
    {
        $value = trim((string) $value);
        return in_array($value, array('1', '2', '3'), true) ? $value : '1';
    }

    private function booleanValue($value, $default)
    {
        $value = strtolower(trim((string) $value));
        if ($value === '') {
            return $default;
        }
        return in_array($value, array('1', 'ya', 'yes', 'y', 'true'), true);
    }

    private function error($row, $message)
    {
        return array('question' => null, 'error' => 'Baris ' . $row . ': ' . $message);
    }
}
