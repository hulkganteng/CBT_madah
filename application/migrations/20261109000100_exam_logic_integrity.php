<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exam_logic_integrity extends CI_Migration
{
    public function up()
    {
        $this->dbforge->modify_column('cbt_nilai', array(
            'id_nilai' => array('type' => 'VARCHAR', 'constraint' => 64, 'null' => FALSE),
            'bs_nilai' => array('type' => 'DECIMAL', 'constraint' => '6,2', 'default' => '0.00', 'null' => FALSE),
        ));
        $this->dbforge->modify_column('cbt_soal_siswa', array(
            'id_soal_siswa' => array('type' => 'VARCHAR', 'constraint' => 64, 'null' => FALSE),
        ));
    }

    public function down()
    {
        $this->dbforge->modify_column('cbt_nilai', array(
            'id_nilai' => array('type' => 'INT', 'constraint' => 11, 'null' => FALSE),
            'bs_nilai' => array('type' => 'DECIMAL', 'constraint' => '6,0', 'default' => 0, 'null' => FALSE),
        ));
        $this->dbforge->modify_column('cbt_soal_siswa', array(
            'id_soal_siswa' => array('type' => 'VARCHAR', 'constraint' => 15, 'null' => FALSE),
        ));
    }
}
