<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'libraries/Student_exam_filter.php';

class Student_exam_eligibility
{
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function check($studentId, $scheduleId)
    {
        if (!$this->ci->db->field_exists('peminatan_id', 'master_siswa')
            || !$this->ci->db->field_exists('bank_program_mak', 'cbt_bank_soal')) {
            return array(FALSE, 'Modul peminatan siswa belum dimigrasikan.');
        }
        $row = $this->ci->db
            ->select('s.peminatan_id, s.program_mak, b.bank_kelas, b.bank_jurusan_id, b.bank_program_mak, b.id_tp, b.id_smt')
            ->from('master_siswa s')
            ->join('cbt_jadwal j', 'j.id_jadwal = ' . (int) $scheduleId, 'inner')
            ->join('cbt_bank_soal b', 'b.id_bank = j.id_bank', 'inner')
            ->where('s.id_siswa', (int) $studentId)
            ->get()->row();
        if (!$row) {
            return array(FALSE, 'Data siswa atau jadwal tidak ditemukan.');
        }

        $classes = $this->classIds($row->bank_kelas);
        if (!$classes) {
            return array(FALSE, 'Sasaran kelas bank soal tidak valid.');
        }
        $placement = $this->ci->db->select('id_kelas')->get_where('kelas_siswa', array(
            'id_siswa' => (int) $studentId,
            'id_tp' => (int) $row->id_tp,
            'id_smt' => (int) $row->id_smt,
        ))->row();
        if (!$placement || !in_array((int) $placement->id_kelas, $classes, TRUE)) {
            return array(FALSE, 'Ujian tidak ditujukan untuk rombel siswa.');
        }

        $filter = Student_exam_filter::matches($row->peminatan_id, $row->program_mak, $row->bank_jurusan_id, $row->bank_program_mak);
        if (!$filter[0]) {
            return $filter;
        }
        return array(TRUE, '');
    }

    public function isEligible($studentId, $scheduleId)
    {
        list($allowed) = $this->check($studentId, $scheduleId);
        return $allowed;
    }

    private function classIds($serialized)
    {
        $rows = @unserialize((string) $serialized, array('allowed_classes' => FALSE));
        if (!is_array($rows)) {
            return array();
        }
        $ids = array();
        foreach ($rows as $row) {
            $id = is_array($row) ? ($row['kelas_id'] ?? NULL) : NULL;
            if ($id !== NULL && ctype_digit((string) $id)) {
                $ids[] = (int) $id;
            }
        }
        return array_values(array_unique($ids));
    }
}
