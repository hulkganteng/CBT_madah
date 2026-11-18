<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Unique_exam_schedule_bank_type extends CI_Migration
{
    public function up()
    {
        if (!$this->db->table_exists('cbt_jadwal') || $this->indexExists()) {
            return;
        }

        $duplicate = $this->db->query(
            'SELECT id_bank, id_jenis, COUNT(*) AS total FROM cbt_jadwal '
            . 'GROUP BY id_bank, id_jenis HAVING COUNT(*) > 1 LIMIT 1'
        )->row();
        if ($duplicate) {
            throw new RuntimeException(
                'Migrasi jadwal dihentikan: kombinasi bank ' . $duplicate->id_bank
                . ' dan jenis ' . $duplicate->id_jenis . ' memiliki ' . $duplicate->total . ' jadwal.'
            );
        }

        $this->db->query(
            'ALTER TABLE `cbt_jadwal` ADD UNIQUE KEY `uq_jadwal_bank_jenis` (`id_bank`, `id_jenis`)'
        );
    }

    public function down()
    {
        if ($this->db->table_exists('cbt_jadwal') && $this->indexExists()) {
            $this->db->query('ALTER TABLE `cbt_jadwal` DROP INDEX `uq_jadwal_bank_jenis`');
        }
    }

    private function indexExists()
    {
        return !empty($this->db->query(
            "SHOW INDEX FROM `cbt_jadwal` WHERE Key_name = 'uq_jadwal_bank_jenis'"
        )->result());
    }
}
