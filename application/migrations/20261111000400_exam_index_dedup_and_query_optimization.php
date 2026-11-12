<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exam_index_dedup_and_query_optimization extends CI_Migration
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

    private function foreignKeyExists($table, $constraintName)
    {
        $sql = "SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS "
             . "WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'";
        $rows = $this->db->query($sql, array($table, $constraintName))->result();
        return !empty($rows);
    }

    private function dropForeignKeyIfExists($table, $constraintName)
    {
        if ($this->db->table_exists($table) && $this->foreignKeyExists($table, $constraintName)) {
            $this->db->query("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$constraintName}`");
        }
    }

    public function up()
    {
        // 1. Drop redundant/duplicate indexes matching PRIMARY KEY
        $this->dropIndex('cbt_jadwal', 'idjawal_relation');
        $this->dropIndex('cbt_bank_soal', 'id_bank_soal');
        $this->dropIndex('cbt_soal', 'id_soal_idx');
        $this->dropIndex('cbt_durasi_siswa', 'Cbt_index_id_durasi');
        $this->dropIndex('cbt_soal_siswa', 'is_soal_siswa');

        // 2. cbt_jadwal: convert UNIQUE KEY id_bank_soal to non-unique index to support multiple schedules per question bank
        if ($this->db->table_exists('cbt_jadwal')) {
            // Ensure regular index exists on id_bank first
            $this->addIndex('cbt_jadwal', 'idx_jadwal_id_bank', '`id_bank`');

            // Drop FK referencing unique key if named id_bank_soal
            $this->dropForeignKeyIfExists('cbt_jadwal', 'id_bank_soal');

            // Drop unique index id_bank_soal
            $this->dropIndex('cbt_jadwal', 'id_bank_soal');

            // Re-establish FK on id_bank pointing to cbt_bank_soal if both exist
            if ($this->db->table_exists('cbt_bank_soal') && !$this->foreignKeyExists('cbt_jadwal', 'fk_jadwal_bank_soal')) {
                $this->db->query("ALTER TABLE `cbt_jadwal` ADD CONSTRAINT `fk_jadwal_bank_soal` FOREIGN KEY (`id_bank`) REFERENCES `cbt_bank_soal` (`id_bank`) ON DELETE NO ACTION ON UPDATE NO ACTION");
            }
        }

        // 3. Add composite and lookup indexes
        if ($this->db->table_exists('cbt_soal_siswa')) {
            $this->addIndex('cbt_soal_siswa', 'idx_soal_siswa_jadwal_siswa', '`id_jadwal`, `id_siswa`');
        }

        if ($this->db->table_exists('log_ujian')) {
            $this->addIndex('log_ujian', 'idx_log_time', '`log_time`');
        }
    }

    public function down()
    {
        if ($this->db->table_exists('log_ujian')) {
            $this->dropIndex('log_ujian', 'idx_log_time');
        }

        if ($this->db->table_exists('cbt_soal_siswa')) {
            $this->dropIndex('cbt_soal_siswa', 'idx_soal_siswa_jadwal_siswa');
            $this->addIndex('cbt_soal_siswa', 'is_soal_siswa', '`id_soal_siswa`');
        }

        if ($this->db->table_exists('cbt_jadwal')) {
            $this->dropForeignKeyIfExists('cbt_jadwal', 'fk_jadwal_bank_soal');
            $this->dropIndex('cbt_jadwal', 'idx_jadwal_id_bank');
            if (!$this->indexExists('cbt_jadwal', 'id_bank_soal')) {
                $this->db->query("ALTER TABLE `cbt_jadwal` ADD UNIQUE KEY `id_bank_soal` (`id_bank`)");
            }
            if ($this->db->table_exists('cbt_bank_soal') && !$this->foreignKeyExists('cbt_jadwal', 'id_bank_soal')) {
                $this->db->query("ALTER TABLE `cbt_jadwal` ADD CONSTRAINT `id_bank_soal` FOREIGN KEY (`id_bank`) REFERENCES `cbt_bank_soal` (`id_bank`) ON DELETE NO ACTION ON UPDATE NO ACTION");
            }
            $this->addIndex('cbt_jadwal', 'idjawal_relation', '`id_jadwal`');
        }

        if ($this->db->table_exists('cbt_durasi_siswa')) {
            $this->addIndex('cbt_durasi_siswa', 'Cbt_index_id_durasi', '`id_durasi`');
        }

        if ($this->db->table_exists('cbt_soal')) {
            $this->addIndex('cbt_soal', 'id_soal_idx', '`id_soal`');
        }

        if ($this->db->table_exists('cbt_bank_soal')) {
            $this->addIndex('cbt_bank_soal', 'id_bank_soal', '`id_bank`');
        }
    }
}
