<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Seb_login_lock_token_rotation extends CI_Migration
{
    public function up()
    {
        if (!$this->db->field_exists('exam_locked_at', 'master_siswa')) {
            $this->dbforge->add_column('master_siswa', array(
                'exam_locked_at' => array('type' => 'DATETIME', 'null' => TRUE, 'default' => NULL),
                'exam_locked_reason' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE, 'default' => NULL),
                'exam_locked_attempt_id' => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE, 'default' => NULL),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('exam_locked_at', 'master_siswa')) {
            $this->dbforge->drop_column('master_siswa', 'exam_locked_at');
            $this->dbforge->drop_column('master_siswa', 'exam_locked_reason');
            $this->dbforge->drop_column('master_siswa', 'exam_locked_attempt_id');
        }
    }
}
