<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Repair_question_import_draft_schema extends CI_Migration
{
    private function indexExists($table, $indexName)
    {
        return (bool) $this->db->query(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            array($indexName)
        )->result();
    }

    private function addIndex($table, $indexName, $columns)
    {
        if (!$this->indexExists($table, $indexName)) {
            $this->db->query("ALTER TABLE `{$table}` ADD INDEX `{$indexName}` ({$columns})");
        }
    }

    public function up()
    {
        if (!$this->db->table_exists('cbt_soal_import')
            || !$this->db->table_exists('cbt_soal_import_item')) {
            throw new RuntimeException(
                'Tabel draft import belum tersedia. Jalankan migration 20261103000100 terlebih dahulu.'
            );
        }

        /*
         * Beberapa hasil restore lama membuat ulang tabel dari metadata tetapi
         * kehilangan AUTO_INCREMENT, NOT NULL, default, dan secondary index.
         * Tanpa AUTO_INCREMENT insert draft pertama selalu gagal karena id tidak
         * pernah dikirim oleh aplikasi.
         */
        $this->db->query(
            'ALTER TABLE `cbt_soal_import`
             MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
             MODIFY `bank_id` INT(11) NOT NULL,
             MODIFY `user_id` INT(11) NOT NULL,
             MODIFY `source_type` VARCHAR(20) NOT NULL,
             MODIFY `original_filename` VARCHAR(255) NULL,
             MODIFY `total_items` INT(11) NOT NULL DEFAULT 0,
             MODIFY `valid_items` INT(11) NOT NULL DEFAULT 0,
             MODIFY `invalid_items` INT(11) NOT NULL DEFAULT 0,
             MODIFY `status` VARCHAR(20) NOT NULL DEFAULT \'draft\',
             MODIFY `created_at` DATETIME NOT NULL,
             MODIFY `updated_at` DATETIME NOT NULL'
        );
        $this->addIndex('cbt_soal_import', 'bank_id', '`bank_id`');
        $this->addIndex('cbt_soal_import', 'user_id', '`user_id`');

        $this->db->query(
            'ALTER TABLE `cbt_soal_import_item`
             MODIFY `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
             MODIFY `import_id` INT(11) UNSIGNED NOT NULL,
             MODIFY `row_number` INT(11) NOT NULL,
             MODIFY `question_type` VARCHAR(30) NULL,
             MODIFY `raw_data` LONGTEXT NULL,
             MODIFY `normalized_data` LONGTEXT NULL,
             MODIFY `is_valid` TINYINT(1) NOT NULL DEFAULT 0,
             MODIFY `validation_errors` LONGTEXT NULL,
             MODIFY `imported` TINYINT(1) NOT NULL DEFAULT 0,
             MODIFY `created_at` DATETIME NOT NULL,
             MODIFY `updated_at` DATETIME NOT NULL'
        );
        $this->addIndex('cbt_soal_import_item', 'import_id', '`import_id`');
        $this->addIndex('cbt_soal_import_item', 'is_valid_imported', '`is_valid`, `imported`');
    }

    public function down()
    {
        // Perbaikan integritas skema tidak aman untuk dibalik.
    }
}
