<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Optimize_critical_indexes_and_types extends CI_Migration
{
    private function indexExists($table, $indexName)
    {
        $indexes = $this->db->query("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", array($indexName))->result();
        return !empty($indexes);
    }

    private function addIndex($table, $indexName, $columns)
    {
        if ($this->db->table_exists($table) && !$this->indexExists($table, $indexName)) {
            $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columns})");
        }
    }

    private function dropIndex($table, $indexName)
    {
        if ($this->db->table_exists($table) && $this->indexExists($table, $indexName)) {
            $this->db->query("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        }
    }

    public function up()
    {
        // 1. log_ujian: BIGINT(20) UNSIGNED AUTO_INCREMENT and composite indexes
        if ($this->db->table_exists('log_ujian')) {
            $this->db->query("ALTER TABLE `log_ujian` MODIFY `id_log` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT");
            $this->addIndex('log_ujian', 'idx_log_siswa_jadwal', '`id_siswa`, `id_jadwal`');
            $this->addIndex('log_ujian', 'idx_log_jadwal', '`id_jadwal`');
        }

        // 2. cbt_durasi_siswa: BIGINT(20) and composite index
        if ($this->db->table_exists('cbt_durasi_siswa')) {
            $this->db->query("ALTER TABLE `cbt_durasi_siswa` MODIFY `id_durasi` BIGINT(20) NOT NULL");
            $this->addIndex('cbt_durasi_siswa', 'idx_durasi_jadwal_siswa', '`id_jadwal`, `id_siswa`');
        }

        // 3. cbt_nilai: lookup indexes
        if ($this->db->table_exists('cbt_nilai')) {
            $this->addIndex('cbt_nilai', 'idx_cbt_nilai_jadwal_siswa', '`id_jadwal`, `id_siswa`');
            $this->addIndex('cbt_nilai', 'idx_cbt_nilai_siswa', '`id_siswa`');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('cbt_nilai')) {
            $this->dropIndex('cbt_nilai', 'idx_cbt_nilai_siswa');
            $this->dropIndex('cbt_nilai', 'idx_cbt_nilai_jadwal_siswa');
        }

        if ($this->db->table_exists('cbt_durasi_siswa')) {
            $this->dropIndex('cbt_durasi_siswa', 'idx_durasi_jadwal_siswa');
            $this->db->query("ALTER TABLE `cbt_durasi_siswa` MODIFY `id_durasi` INT(7) NOT NULL");
        }

        if ($this->db->table_exists('log_ujian')) {
            $this->dropIndex('log_ujian', 'idx_log_jadwal');
            $this->dropIndex('log_ujian', 'idx_log_siswa_jadwal');
            $this->db->query("ALTER TABLE `log_ujian` MODIFY `id_log` INT(11) NOT NULL AUTO_INCREMENT");
        }
    }
}
