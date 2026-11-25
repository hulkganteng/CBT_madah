<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once __DIR__ . '/Exam_schedule_slots.php';

class Jadwal_import
{
    public const HEADERS = array(
        'kode_bank', 'kode_jenis', 'tanggal_mulai', 'tanggal_selesai',
        'durasi', 'durasi_minimal', 'acak_soal', 'acak_jawaban',
        'gunakan_token', 'tampilkan_hasil', 'reset_izin', 'aktif'
    );

    public const SLOT_HEADERS = [
        'tanggal_sesi', 'kode_sesi', 'jenis_sasaran', 'kode_sasaran',
        'urutan_mapel', 'jam_buka_wib', 'wajib_mapel_pertama', 'ubah_sesi',
    ];

    public static function headers()
    {
        return array_merge(self::HEADERS, self::SLOT_HEADERS);
    }

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
            foreach (self::headers() as $header) {
                $value = isset($positions[$header]) ? ($sheetRow[$positions[$header]] ?? '') : '';
                if (!is_scalar($value)) {
                    $errors[] = 'Baris ' . ($index + 2) . ': nilai kolom ' . $header . ' tidak valid.';
                    continue 2;
                }
                $values[$header] = trim((string) $value);
            }
            if (implode('', $values) === '') {
                continue;
            }

            foreach (['tanggal_mulai', 'tanggal_selesai', 'tanggal_sesi'] as $field) {
                $values[$field] = $this->excelDate($values[$field]);
            }
            $rowNumber = $index + 2;
            $rowErrors = $this->validate($values);
            $key = strtoupper($values['kode_bank']) . '|' . strtoupper($values['kode_jenis']);
            $hasSlot = implode('', array_intersect_key($values, array_flip(self::SLOT_HEADERS))) !== '';
            if ($hasSlot) {
                $values['jam_buka_wib'] = $this->excelTime($values['jam_buka_wib']);
                try {
                    $this->slot($values);
                } catch (InvalidArgumentException $error) {
                    $rowErrors[] = $error->getMessage();
                }
            }
            if (isset($seen[$key]) && (!$hasSlot || !$seen[$key])) {
                $rowErrors[] = 'kombinasi kode bank dan jenis duplikat dalam file. Tambahkan slot pada semua baris untuk menjadwalkan ulang bank yang sama.';
            }
            $seen[$key] = $hasSlot;

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

    public function slot(array $row)
    {
        if (implode('', array_intersect_key($row, array_flip(self::SLOT_HEADERS))) === '') {
            return NULL;
        }
        return Exam_schedule_slots::normalize([
            'tanggal' => $row['tanggal_sesi'],
            'id_sesi' => $row['kode_sesi'],
            'jenis_sasaran' => strtolower($row['jenis_sasaran']),
            'sasaran' => $row['kode_sasaran'],
            'urutan' => $row['urutan_mapel'],
            'jam_buka' => $row['jam_buka_wib'],
            'wajib_mapel_pertama' => $row['wajib_mapel_pertama'],
            'ubah_sesi' => $row['ubah_sesi'],
        ], $row['tanggal_mulai'], $row['tanggal_selesai']);
    }

    private function excelDate($value)
    {
        if (ctype_digit($value) && (int) $value > 0 && (int) $value <= 2958465) {
            return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((int) $value)->format('Y-m-d');
        }
        return $value;
    }

    private function excelTime($value)
    {
        if (is_numeric($value) && (float) $value >= 0 && (float) $value < 1) {
            $minutes = (float) $value * 1440;
            if (abs($minutes - round($minutes)) < 0.00001 && round($minutes) < 1440) {
                return sprintf('%02d:%02d', intdiv((int) round($minutes), 60), (int) round($minutes) % 60);
            }
        }
        return preg_replace('/^(\d{2}:\d{2}):00$/D', '$1', $value);
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
            $date = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $row[$field])
                ? DateTimeImmutable::createFromFormat('!Y-m-d', $row[$field]) : FALSE;
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
