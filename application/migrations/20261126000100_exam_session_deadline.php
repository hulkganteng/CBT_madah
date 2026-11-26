<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exam_session_deadline extends CI_Migration
{
    public function up()
    {
        if (!$this->db->field_exists('session_deadline_at', 'cbt_exam_attempt')) {
            if (!$this->dbforge->add_column('cbt_exam_attempt', [
                'session_deadline_at' => ['type' => 'DATETIME', 'null' => TRUE],
            ])) {
                throw new RuntimeException('Kolom batas sesi ujian gagal dibuat.');
            }
            unset($this->db->data_cache['field_names']['cbt_exam_attempt']);
        }

        $this->load->library('Exam_attempt_service');
        $cursor = '';
        do {
            $result = $this->db->select('attempt_id, id_siswa, id_jadwal, started_at')
                ->where(['status' => 'IN_PROGRESS', 'session_deadline_at' => NULL, 'attempt_id >' => $cursor])
                ->order_by('attempt_id')->limit(250)->get('cbt_exam_attempt');
            if (!$result) {
                throw new RuntimeException('Percobaan ujian lama gagal dibaca.');
            }
            $attempts = $result->result();
            foreach ($attempts as $attempt) {
                $cursor = $attempt->attempt_id;
                $schedule = $this->exam_attempt_service->schedule($attempt->id_jadwal);
                $end = $schedule ? $this->exam_attempt_service->resolveSessionDeadline(
                    $attempt->id_siswa, $schedule, $attempt->started_at
                ) : NULL;
                if ($end !== NULL && !$this->db
                    ->where(['attempt_id' => $cursor, 'status' => 'IN_PROGRESS', 'session_deadline_at' => NULL])
                    ->update('cbt_exam_attempt', ['session_deadline_at' => date('Y-m-d H:i:s', $end)])) {
                    throw new RuntimeException('Batas sesi ujian lama gagal disimpan.');
                }
            }
        } while ($attempts);
    }

    public function down()
    {
        if ($this->db->field_exists('session_deadline_at', 'cbt_exam_attempt')) {
            $this->dbforge->drop_column('cbt_exam_attempt', 'session_deadline_at');
        }
    }
}
