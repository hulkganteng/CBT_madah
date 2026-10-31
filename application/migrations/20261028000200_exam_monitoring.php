<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRD FR-16 (perpanjang waktu + alasan), FR-18 (penanda ragu-ragu),
 * FR-22 (pemantauan aktivitas & pelanggaran).
 */
class Migration_Exam_monitoring extends CI_Migration
{
    public function up()
    {
        $attempt = array();
        if (!$this->db->field_exists('kicked_at', 'cbt_exam_attempt')) {
            $attempt['kicked_at'] = array('type' => 'DATETIME', 'null' => TRUE, 'after' => 'submitted_at');
        }
        if (!$this->db->field_exists('extra_minutes', 'cbt_exam_attempt')) {
            $attempt['extra_minutes'] = array('type' => 'INT', 'constraint' => 5, 'default' => 0, 'null' => FALSE, 'after' => 'kicked_at');
        }
        if (!$this->db->field_exists('extra_reason', 'cbt_exam_attempt')) {
            $attempt['extra_reason'] = array('type' => 'TEXT', 'null' => TRUE, 'after' => 'extra_minutes');
        }
        if ($attempt) {
            $this->dbforge->add_column('cbt_exam_attempt', $attempt);
        }

        if (!$this->db->field_exists('ragu_ragu', 'cbt_soal_siswa')) {
            $this->dbforge->add_column('cbt_soal_siswa', array(
                'ragu_ragu' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0, 'null' => FALSE, 'after' => 'jawaban_siswa'),
            ));
        }

        $bank = array();
        if (!$this->db->field_exists('tampil_bs', 'cbt_bank_soal')) {
            $bank['tampil_bs'] = array('type' => 'INT', 'constraint' => 11, 'default' => 0, 'null' => FALSE, 'after' => 'tampil_kompleks');
        }
        if (!$this->db->field_exists('bobot_bs', 'cbt_bank_soal')) {
            $bank['bobot_bs'] = array('type' => 'INT', 'constraint' => 11, 'default' => 0, 'null' => FALSE, 'after' => 'tampil_bs');
        }
        if ($bank) {
            $this->dbforge->add_column('cbt_bank_soal', $bank);
        }
    }

    public function down()
    {
        foreach (array('kicked_at', 'extra_minutes', 'extra_reason') as $f) {
            if ($this->db->field_exists($f, 'cbt_exam_attempt')) {
                $this->dbforge->drop_column('cbt_exam_attempt', $f);
            }
        }
        foreach (array('ragu_ragu') as $f) {
            if ($this->db->field_exists($f, 'cbt_soal_siswa')) {
                $this->dbforge->drop_column('cbt_soal_siswa', $f);
            }
        }
        foreach (array('tampil_bs', 'bobot_bs') as $f) {
            if ($this->db->field_exists($f, 'cbt_bank_soal')) {
                $this->dbforge->drop_column('cbt_bank_soal', $f);
            }
        }
    }
}
