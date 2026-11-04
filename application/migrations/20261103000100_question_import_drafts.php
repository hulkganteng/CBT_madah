<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Question_import_drafts extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('cbt_soal_import')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
                'bank_id' => array('type' => 'INT', 'constraint' => 11, 'null' => FALSE),
                'user_id' => array('type' => 'INT', 'constraint' => 11, 'null' => FALSE),
                'source_type' => array('type' => 'VARCHAR', 'constraint' => 20, 'null' => FALSE),
                'original_filename' => array('type' => 'VARCHAR', 'constraint' => 255, 'null' => TRUE),
                'total_items' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                'valid_items' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                'invalid_items' => array('type' => 'INT', 'constraint' => 11, 'default' => 0),
                'status' => array('type' => 'VARCHAR', 'constraint' => 20, 'default' => 'draft'),
                'created_at' => array('type' => 'DATETIME', 'null' => FALSE),
                'updated_at' => array('type' => 'DATETIME', 'null' => FALSE),
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('bank_id');
            $this->dbforge->add_key('user_id');
            $this->dbforge->create_table('cbt_soal_import', TRUE);
        }

        if (!$this->db->table_exists('cbt_soal_import_item')) {
            $this->dbforge->add_field(array(
                'id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'auto_increment' => TRUE),
                'import_id' => array('type' => 'INT', 'constraint' => 11, 'unsigned' => TRUE, 'null' => FALSE),
                'row_number' => array('type' => 'INT', 'constraint' => 11, 'null' => FALSE),
                'question_type' => array('type' => 'VARCHAR', 'constraint' => 30, 'null' => TRUE),
                'raw_data' => array('type' => 'LONGTEXT', 'null' => TRUE),
                'normalized_data' => array('type' => 'LONGTEXT', 'null' => TRUE),
                'is_valid' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
                'validation_errors' => array('type' => 'LONGTEXT', 'null' => TRUE),
                'imported' => array('type' => 'TINYINT', 'constraint' => 1, 'default' => 0),
                'created_at' => array('type' => 'DATETIME', 'null' => FALSE),
                'updated_at' => array('type' => 'DATETIME', 'null' => FALSE),
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('import_id');
            $this->dbforge->add_key(array('is_valid', 'imported'));
            $this->dbforge->create_table('cbt_soal_import_item', TRUE);
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('cbt_soal_import_item', TRUE);
        $this->dbforge->drop_table('cbt_soal_import', TRUE);
    }
}
