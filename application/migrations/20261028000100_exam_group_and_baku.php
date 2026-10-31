<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRD Bab 4: setiap mata pelajaran wajib punya atribut kelompok
 * Eksata / Non-eksata yang menentukan jumlah soal (25 / 30) dan
 * durasi baku 60 menit.
 */
class Migration_Exam_group_and_baku extends CI_Migration
{
    public function up()
    {
        if (!$this->db->field_exists('kelompok_ujian', 'master_mapel')) {
            $this->dbforge->add_column('master_mapel', array(
                'kelompok_ujian' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 12,
                    'default' => 'NON_EKSATA',
                    'null' => FALSE,
                    'after' => 'kelompok'
                ),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('kelompok_ujian', 'master_mapel')) {
            $this->dbforge->drop_column('master_mapel', 'kelompok_ujian');
        }
    }
}
