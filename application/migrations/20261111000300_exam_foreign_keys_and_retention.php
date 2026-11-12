<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exam_foreign_keys_and_retention extends CI_Migration
{
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
        if (!$this->db->table_exists('cbt_jadwal')) {
            return;
        }

        // 1. Clean up orphaned rows before applying foreign keys
        if ($this->db->table_exists('cbt_soal_siswa')) {
            $this->db->query("DELETE FROM `cbt_soal_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)");
        }
        if ($this->db->table_exists('cbt_durasi_siswa')) {
            $this->db->query("DELETE FROM `cbt_durasi_siswa` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)");
        }
        if ($this->db->table_exists('cbt_nilai')) {
            $this->db->query("DELETE FROM `cbt_nilai` WHERE `id_jadwal` IS NOT NULL AND `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`)");
        }
        if ($this->db->table_exists('log_ujian')) {
            $this->db->query("UPDATE `log_ujian` SET `id_jadwal` = NULL WHERE `id_jadwal` IS NOT NULL AND (`id_jadwal` = 0 OR `id_jadwal` NOT IN (SELECT `id_jadwal` FROM `cbt_jadwal`))");
        }

        // 2. cbt_soal_siswa: replace legacy NO ACTION constraint with CASCADE
        if ($this->db->table_exists('cbt_soal_siswa')) {
            $this->dropForeignKeyIfExists('cbt_soal_siswa', 'id_jadwal_fc');
            $this->dropForeignKeyIfExists('cbt_soal_siswa', 'fk_soal_siswa_jadwal');
            $this->db->query("ALTER TABLE `cbt_soal_siswa` ADD CONSTRAINT `fk_soal_siswa_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE");
        }

        // 3. cbt_durasi_siswa: cascade when schedule deleted
        if ($this->db->table_exists('cbt_durasi_siswa')) {
            $this->dropForeignKeyIfExists('cbt_durasi_siswa', 'fk_durasi_siswa_jadwal');
            $this->db->query("ALTER TABLE `cbt_durasi_siswa` ADD CONSTRAINT `fk_durasi_siswa_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE");
        }

        // 4. cbt_nilai: cascade when schedule deleted
        if ($this->db->table_exists('cbt_nilai')) {
            $this->dropForeignKeyIfExists('cbt_nilai', 'fk_nilai_jadwal');
            $this->db->query("ALTER TABLE `cbt_nilai` ADD CONSTRAINT `fk_nilai_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE CASCADE ON UPDATE CASCADE");
        }

        // 5. log_ujian: preserve audit log by setting id_jadwal to NULL
        if ($this->db->table_exists('log_ujian')) {
            $this->dropForeignKeyIfExists('log_ujian', 'fk_log_ujian_jadwal');
            $this->db->query("ALTER TABLE `log_ujian` ADD CONSTRAINT `fk_log_ujian_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE SET NULL ON UPDATE CASCADE");
        }
    }

    public function down()
    {
        $this->dropForeignKeyIfExists('log_ujian', 'fk_log_ujian_jadwal');
        $this->dropForeignKeyIfExists('cbt_nilai', 'fk_nilai_jadwal');
        $this->dropForeignKeyIfExists('cbt_durasi_siswa', 'fk_durasi_siswa_jadwal');
        $this->dropForeignKeyIfExists('cbt_soal_siswa', 'fk_soal_siswa_jadwal');

        // Restore original legacy constraint if cbt_soal_siswa exists
        if ($this->db->table_exists('cbt_soal_siswa') && $this->db->table_exists('cbt_jadwal') && !$this->foreignKeyExists('cbt_soal_siswa', 'id_jadwal_fc')) {
            $this->db->query("ALTER TABLE `cbt_soal_siswa` ADD CONSTRAINT `id_jadwal_fc` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON DELETE NO ACTION ON UPDATE NO ACTION");
        }
    }
}
