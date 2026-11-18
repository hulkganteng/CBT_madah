<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal_import
{
    public const HEADERS = array(
        'kode_bank', 'kode_jenis', 'tanggal_mulai', 'tanggal_selesai',
        'durasi', 'durasi_minimal', 'acak_soal', 'acak_jawaban',
        'gunakan_token', 'tampilkan_hasil', 'reset_izin', 'aktif'
    );

    public function parseRows(array $sheetRows): array
    {
        if (count($sheetRows) < 2) {
            return array('rows' => array(), 'errors' => array('File tidak berisi data jadwal.'));
        }

        $headers = array_map(static function ($value) {
            return strtolower(trim((string) $value));
        }, array_shift($sheetRows));
        $positions = array_flip($headers);
        $missing = array_values(array_diff(self::HEADERS, $headers));
        if ($missing) {
            return array('rows' => array(), 'errors' => array('Kolom wajib tidak ditemukan: ' . implode(', ', $missing) . '.'));
        }

        $rows = array();
        $errors = array();
        $seen = array();
        foreach ($sheetRows as $index => $sheetRow) {
            $values = array();
            foreach (self::HEADERS as $header) {
                $values[$header] = trim((string) ($sheetRow[$positions[$header]] ?? ''));
            }
            if (implode('', $values) === '') {
                continue;
            }

            $rowNumber = $index + 2;
            $rowErrors = $this->validate($values);
            $key = strtoupper($values['kode_bank']) . '|' . strtoupper($values['kode_jenis']);
            if (isset($seen[$key])) {
                $rowErrors[] = 'kombinasi kode bank dan jenis duplikat dalam file';
            }
            $seen[$key] = true;

            if ($rowErrors) {
                $errors[] = 'Baris ' . $rowNumber . ': ' . implode('; ', $rowErrors) . '.';
                continue;
            }

            $values['kode_bank'] = strtoupper($values['kode_bank']);
            $values['kode_jenis'] = strtoupper($values['kode_jenis']);
            foreach (array('durasi', 'durasi_minimal', 'acak_soal', 'acak_jawaban', 'gunakan_token', 'tampilkan_hasil', 'reset_izin', 'aktif') as $field) {
                $values[$field] = (int) $values[$field];
            }
            $values['_row'] = $rowNumber;
            $rows[] = $values;
        }

        if (count($rows) + count($errors) > 500) {
            return array('rows' => array(), 'errors' => array('Maksimal 500 jadwal dalam satu file.'));
        }
        if (!$rows && !$errors) {
            $errors[] = 'File tidak berisi data jadwal.';
        }

        return array('rows' => $rows, 'errors' => $errors);
    }

    private function validate(array $row): array
    {
        $errors = array();
        if ($row['kode_bank'] === '') {
            $errors[] = 'kode_bank wajib diisi';
        }
        if ($row['kode_jenis'] === '') {
            $errors[] = 'kode_jenis wajib diisi';
        }
        foreach (array('tanggal_mulai', 'tanggal_selesai') as $field) {
            $date = DateTimeImmutable::createFromFormat('!Y-m-d', $row[$field]);
            if (!$date || $date->format('Y-m-d') !== $row[$field]) {
                $errors[] = $field . ' harus berformat YYYY-MM-DD';
            }
        }
        if (!$errors && $row['tanggal_selesai'] < $row['tanggal_mulai']) {
            $errors[] = 'tanggal_selesai tidak boleh sebelum tanggal_mulai';
        }
        if (!ctype_digit($row['durasi']) || (int) $row['durasi'] < 1) {
            $errors[] = 'durasi harus bilangan bulat lebih dari 0';
        }
        if (!ctype_digit($row['durasi_minimal']) || (int) $row['durasi_minimal'] > (int) $row['durasi']) {
            $errors[] = 'durasi_minimal harus 0 sampai durasi';
        }
        foreach (array('acak_soal', 'acak_jawaban', 'gunakan_token', 'tampilkan_hasil', 'reset_izin', 'aktif') as $field) {
            if ($row[$field] !== '0' && $row[$field] !== '1') {
                $errors[] = $field . ' hanya boleh 0 atau 1';
            }
        }
        return $errors;
    }
}
