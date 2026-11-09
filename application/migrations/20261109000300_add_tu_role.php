<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_tu_role extends CI_Migration
{
    public function up()
    {
        $check = $this->db->get_where('groups', array('name' => 'tu'))->row();
        if (!$check) {
            $this->db->insert('groups', array(
                'name' => 'tu',
                'description' => 'Tata Usaha'
            ));
        }
    }

    public function down()
    {
        $this->db->where('name', 'tu')->delete('groups');
    }
}
