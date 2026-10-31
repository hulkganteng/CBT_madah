<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * PRD Bab 4: Benar/Salah adalah tipe baku ketiga. Nilai rekap harus
 * menyimpan komponennya sendiri agar total rekap tidak menghilangkannya.
 */
class Migration_Rekap_bs_nilai extends CI_Migration
{
    public function up()
    {
        if (!$this->db->field_exists('bs_nilai', 'cbt_rekap_nilai')) {
            $this->dbforge->add_column('cbt_rekap_nilai', array(
                'bs_nilai' => array('type' => 'VARCHAR', 'constraint' => 10, 'default' => '0', 'null' => FALSE, 'after' => 'nilai_pg'),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('bs_nilai', 'cbt_rekap_nilai')) {
            $this->dbforge->drop_column('cbt_rekap_nilai', 'bs_nilai');
        }
    }
}
