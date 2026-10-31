<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Secure_exam_attempts extends CI_Migration
{
    public function up()
    {
        if (!$this->db->field_exists('seb_mode', 'cbt_jadwal')) {
            $this->dbforge->add_column('cbt_jadwal', array(
                'seb_mode' => array(
                    'type' => 'VARCHAR',
                    'constraint' => 10,
                    'default' => 'OFF',
                    'null' => FALSE,
                    'after' => 'reset_login'
                ),
                'seb_config_key' => array(
                    'type' => 'CHAR',
                    'constraint' => 64,
                    'null' => TRUE,
                    'after' => 'seb_mode'
                ),
                'seb_browser_exam_keys' => array(
                    'type' => 'TEXT',
                    'null' => TRUE,
                    'after' => 'seb_config_key'
                )
            ));
        }

        if (!$this->db->table_exists('cbt_exam_attempt')) {
            $this->db->query("CREATE TABLE `cbt_exam_attempt` (
                `attempt_id` CHAR(36) NOT NULL,
                `id_siswa` INT(7) NOT NULL,
                `id_jadwal` INT(6) NOT NULL,
                `status` VARCHAR(20) NOT NULL DEFAULT 'IN_PROGRESS',
                `started_at` DATETIME NOT NULL,
                `deadline_at` DATETIME NOT NULL,
                `submitted_at` DATETIME NULL,
                `session_nonce` CHAR(64) NOT NULL,
                `device_hash` CHAR(64) NOT NULL,
                `seb_verified` TINYINT(1) NOT NULL DEFAULT 0,
                `last_heartbeat_at` DATETIME NULL,
                `created_at` DATETIME NOT NULL,
                `updated_at` DATETIME NOT NULL,
                PRIMARY KEY (`attempt_id`),
                UNIQUE KEY `uq_exam_attempt_student_schedule` (`id_siswa`, `id_jadwal`),
                KEY `idx_exam_attempt_schedule_status` (`id_jadwal`, `status`),
                CONSTRAINT `fk_exam_attempt_student` FOREIGN KEY (`id_siswa`) REFERENCES `master_siswa` (`id_siswa`) ON UPDATE CASCADE,
                CONSTRAINT `fk_exam_attempt_schedule` FOREIGN KEY (`id_jadwal`) REFERENCES `cbt_jadwal` (`id_jadwal`) ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }

        if (!$this->db->table_exists('cbt_exam_event')) {
            $this->db->query("CREATE TABLE `cbt_exam_event` (
                `event_id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `attempt_id` CHAR(36) NOT NULL,
                `event_type` VARCHAR(40) NOT NULL,
                `event_time` DATETIME NOT NULL,
                `recorded_at` DATETIME NOT NULL,
                `metadata` TEXT NULL,
                `ip_hash` CHAR(64) NULL,
                `user_agent_hash` CHAR(64) NULL,
                PRIMARY KEY (`event_id`),
                KEY `idx_exam_event_attempt_time` (`attempt_id`, `recorded_at`),
                KEY `idx_exam_event_type_time` (`event_type`, `recorded_at`),
                CONSTRAINT `fk_exam_event_attempt` FOREIGN KEY (`attempt_id`) REFERENCES `cbt_exam_attempt` (`attempt_id`) ON DELETE CASCADE ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('cbt_exam_event', TRUE);
        $this->dbforge->drop_table('cbt_exam_attempt', TRUE);

        foreach (array('seb_browser_exam_keys', 'seb_config_key', 'seb_mode') as $field) {
            if ($this->db->field_exists($field, 'cbt_jadwal')) {
                $this->dbforge->drop_column('cbt_jadwal', $field);
            }
        }
    }
}
