<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Fix_locked_attempt_uuid extends CI_Migration
{
    public function up()
    {
        if ($this->db->field_exists('exam_locked_attempt_id', 'master_siswa')) {
            $this->dbforge->modify_column('master_siswa', array(
                'exam_locked_attempt_id' => array('type' => 'CHAR', 'constraint' => 36, 'null' => TRUE, 'default' => NULL),
            ));
        } else {
            $this->dbforge->add_column('master_siswa', array(
                'exam_locked_attempt_id' => array('type' => 'CHAR', 'constraint' => 36, 'null' => TRUE, 'default' => NULL),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('exam_locked_attempt_id', 'master_siswa')) {
            $this->dbforge->modify_column('master_siswa', array(
                'exam_locked_attempt_id' => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'default' => NULL),
            ));
        }
    }
}
