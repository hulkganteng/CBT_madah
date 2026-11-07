<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Install_model extends CI_Model
{
    public function install_success()
    {
        return $this->check_installer();
    }

    public function check_installer()
    {
        include APPPATH . 'config/database.php';
        $database = $db['development']['database'];
        if ($database === '') {
            return '1';
        }

        $this->load->dbutil();
        if (!$this->dbutil->database_exists($database)) {
            return '5';
        }
        if (!$this->db->table_exists('users')) {
            return '2';
        }
        if (!$this->db->get('users')->row()) {
            return '3';
        }
        if (!$this->db->get('setting')->row()) {
            return '4';
        }
        return '0';
    }

    public function finalizeDualInstallation($adminId)
    {
        include APPPATH . 'config/database.php';
        $this->load->library('environment_database_installer');
        return $this->environment_database_installer->finalize(
            $db['development'],
            $db['production'],
            $adminId
        );
    }
}
