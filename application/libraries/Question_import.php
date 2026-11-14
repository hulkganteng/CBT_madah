<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Question_import
{
    public function normalizeQuestion($data)
    {
        $options = array();
        foreach (array('A', 'B', 'C', 'D', 'E') as $key) {
            $value = $this->firstValue($data, array($key, strtolower($key), 'opsi_' . strtolower($key), 'pilihan_' . strtolower($key)));
            if (trim((string) $value) !== '') {
                $options[$key] = trim((string) $value);
            }
        }

        $type = $this->normalizeQuestionType($this->firstValue($data, array('type', 'jenis', 'question_type')));
        $question = array(
            'type' => $type,
            'question' => trim((string) $this->firstValue($data, array('question', 'pertanyaan', 'soal'))),
            'options' => $options,
            'correct_answers' => $this->normalizeAnswer(
                $this->firstValue($data, array('correct_answers', 'answer', 'jawaban', 'kunci')),
                $options,
                $type
            ),
            'difficulty' => $this->normalizeDifficulty($this->firstValue($data, array('difficulty', 'kesulitan'))),
            'explanation' => $this->nullableText($this->firstValue($data, array('explanation', 'pembahasan', 'deskripsi'))),
        );

        return $question;
    }

    public function validateQuestion($question)
    {
        $errors = array();
        if (!in_array($question['type'], array('pg', 'pg_complex', 'true_false'), true)) {
            $errors[] = 'Jenis soal tidak dikenali. Gunakan Pilihan Ganda, Pilihan Ganda Kompleks, atau Benar/Salah.';
        }
        if ($this->plainText($question['question']) === '') {
            $errors[] = 'Pertanyaan belum diisi.';
        }

        if ($question['type'] === 'pg' || $question['type'] === 'pg_complex') {
            if (count($question['options']) < 2) {
                $errors[] = 'Isi sedikitnya dua pilihan jawaban.';
            }
            if (!$question['correct_answers']) {
                $errors[] = 'Jawaban benar belum dipilih.';
            } elseif (array_diff($question['correct_answers'], array_keys($question['options']))) {
                $errors[] = 'Jawaban benar tidak ditemukan pada pilihan yang tersedia.';
            }
            if ($question['type'] === 'pg' && count($question['correct_answers']) > 1) {
                $errors[] = 'Pilihan Ganda hanya boleh memiliki satu jawaban benar.';
            }
        }

        if ($question['type'] === 'true_false') {
            if (count($question['correct_answers']) !== 1 || !in_array($question['correct_answers'][0], array('B', 'S'), true)) {
                $errors[] = 'Pilih jawaban Benar atau Salah.';
            }
        }

        return $errors;
    }

    public function normalizeAnswer($answer, $options = array(), $type = null)
    {
        $values = is_array($answer) ? $answer : preg_split('/\s*[,;]\s*/', trim((string) $answer), -1, PREG_SPLIT_NO_EMPTY);
        $normalized = array();
        foreach ($values as $value) {
            $value = trim((string) $value);
            $key = strtoupper(rtrim($value, ". \t\n\r\0\x0B"));
            if ($type === 'true_false') {
                $truth = array('B' => 'B', 'BENAR' => 'B', 'TRUE' => 'B', 'S' => 'S', 'SALAH' => 'S', 'FALSE' => 'S');
                if (isset($truth[$key])) {
                    $key = $truth[$key];
                }
            } elseif (!isset($options[$key])) {
                foreach ($options as $optionKey => $optionText) {
                    if ($this->comparisonKey($optionText) === $this->comparisonKey($value)) {
                        $key = $optionKey;
                        break;
                    }
                }
            }
            if ($key !== '') {
                $normalized[] = $key;
            }
        }
        return array_values(array_unique($normalized));
    }

    public function normalizeQuestionType($type)
    {
        $value = $this->comparisonKey($type);
        $types = array(
            '1' => 'pg', 'pg' => 'pg', 'pilihan ganda' => 'pg',
            '2' => 'pg_complex', 'pg kompleks' => 'pg_complex', 'pilihan ganda kompleks' => 'pg_complex',
            '6' => 'true_false', 'benar salah' => 'true_false', 'benar/salah' => 'true_false', 'bs' => 'true_false',
        );
        return isset($types[$value]) ? $types[$value] : null;
    }

    public function normalizeBoolean($value, $default = true)
    {
        $value = $this->comparisonKey($value);
        if ($value === '') {
            return $default;
        }
        return in_array($value, array('1', 'ya', 'yes', 'y', 'true'), true);
    }

    public function comparisonKey($value)
    {
        $value = html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value));
        return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
    }

    public function toDatabaseRow($question, $number, $visible = true)
    {
        $typeMap = array('pg' => '1', 'pg_complex' => '2', 'true_false' => '6');
        $row = array(
            'jenis' => $typeMap[$question['type']],
            'nomor_soal' => (int) $number,
            'soal' => $this->html($question['question']),
            'deskripsi' => $question['explanation'] === null ? '' : $this->html($question['explanation']),
            'kesulitan' => $question['difficulty'] ?: '1',
            'timer' => '0',
            'timer_menit' => '0',
            'file' => serialize(array()),
            'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'opsi_e' => '',
            'jawaban' => '',
            'tampilkan' => $visible ? '1' : '0',
        );

        if ($question['type'] === 'pg') {
            foreach ($question['options'] as $key => $value) {
                $row['opsi_' . strtolower($key)] = $this->html($value);
            }
            $row['jawaban'] = strtolower($question['correct_answers'][0]);
        } elseif ($question['type'] === 'pg_complex') {
            $options = array();
            foreach ($question['options'] as $key => $value) {
                $options[strtolower($key)] = $this->html($value);
            }
            $row['opsi_a'] = serialize($options);
            $row['jawaban'] = serialize(array_map('strtolower', $question['correct_answers']));
        } else {
            $row['jawaban'] = strtolower($question['correct_answers'][0]);
        }
        return $row;
    }

    private function firstValue($data, $keys)
    {
        foreach ($keys as $key) {
            if (array_key_exists($key, $data)) {
                return $data[$key];
            }
        }
        return '';
    }

    private function normalizeDifficulty($value)
    {
        $value = trim((string) $value);
        return in_array($value, array('1', '2', '3'), true) ? $value : null;
    }

    private function nullableText($value)
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function plainText($value)
    {
        return trim(html_entity_decode(strip_tags((string) $value), ENT_QUOTES, 'UTF-8'));
    }

    private function html($value)
    {
        $html = nl2br(htmlspecialchars(trim((string) $value), ENT_QUOTES, 'UTF-8'));
        // Skema lama memakai charset utf8 MySQL (maksimal 3 byte). Ubah
        // karakter 4-byte menjadi numeric entity; tampilannya tetap sama di
        // browser dan query tidak gagal dengan "Incorrect string value".
        return preg_replace_callback('/[\x{10000}-\x{10FFFF}]/u', function ($match) {
            $bytes = unpack('C*', $match[0]);
            $codepoint = (($bytes[1] & 0x07) << 18)
                | (($bytes[2] & 0x3F) << 12)
                | (($bytes[3] & 0x3F) << 6)
                | ($bytes[4] & 0x3F);
            return '&#' . $codepoint . ';';
        }, $html);
    }
}
