<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Exam_schedule_slots extends CI_Migration
{
    public function up()
    {
        $this->db->query('CREATE TABLE IF NOT EXISTS cbt_jadwal_slot (
            id_jadwal INT NOT NULL,
            tanggal DATE NOT NULL,
            id_sesi INT NOT NULL,
            jenis_sasaran VARCHAR(16) NOT NULL,
            sasaran VARCHAR(20) NOT NULL,
            urutan SMALLINT UNSIGNED NOT NULL,
            jam_buka TIME NOT NULL,
            wajib_mapel_pertama TINYINT NOT NULL DEFAULT 0,
            ubah_sesi TINYINT NOT NULL DEFAULT 0,
            PRIMARY KEY (id_jadwal, tanggal, id_sesi, jenis_sasaran, sasaran),
            KEY idx_jadwal_slot_tanggal (tanggal, jenis_sasaran, sasaran, id_sesi, urutan),
            CONSTRAINT fk_jadwal_slot_jadwal FOREIGN KEY (id_jadwal)
                REFERENCES cbt_jadwal (id_jadwal) ON DELETE CASCADE,
            CONSTRAINT fk_jadwal_slot_sesi FOREIGN KEY (id_sesi)
                REFERENCES cbt_sesi (id_sesi)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4');
    }

    public function down()
    {
        $this->dbforge->drop_table('cbt_jadwal_slot', TRUE);
    }
}
