<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Satu jadwal dapat dibuka lewat dua jalur (Online dan LAN), dan tiap jalur
 * punya Config Key sendiri karena Start URL ikut di-hash. Kolom lama char(64)
 * tidak cukup, jadi diubah menjadi TEXT agar boleh memuat beberapa key.
 */
class Migration_Multi_seb_config_key extends CI_Migration
{
    public function up()
    {
        if ($this->db->field_exists('seb_config_key', 'cbt_jadwal')) {
            $this->dbforge->modify_column('cbt_jadwal', array(
                'seb_config_key' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                ),
            ));
        }
    }

    public function down()
    {
        if ($this->db->field_exists('seb_config_key', 'cbt_jadwal')) {
            $this->dbforge->modify_column('cbt_jadwal', array(
                'seb_config_key' => array(
                    'type' => 'CHAR',
                    'constraint' => 64,
                    'null' => TRUE,
                ),
            ));
        }
    }
}
