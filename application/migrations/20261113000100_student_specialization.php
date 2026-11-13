<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Student_specialization extends CI_Migration
{
    private function indexExists($table, $name)
    {
        return (bool) $this->db->query(
            "SHOW INDEX FROM `{$table}` WHERE Key_name = ?",
            array($name)
        )->row();
    }

    private function ensureMajor($code, $name)
    {
        $row = $this->db->where_in('kode_jurusan', array($code, $code === 'UM' ? 'NON' : $code))
            ->get('master_jurusan')->row();
        if ($row) {
            $this->db->where('id_jurusan', $row->id_jurusan)->update('master_jurusan', array(
                'kode_jurusan' => $code,
                'nama_jurusan' => $name,
                'status' => 1,
            ));
            return (int) $row->id_jurusan;
        }
        $this->db->insert('master_jurusan', array(
            'kode_jurusan' => $code,
            'nama_jurusan' => $name,
            'status' => 1,
            'deletable' => 0,
        ));
        return (int) $this->db->insert_id();
    }

    public function up()
    {
        $duplicates = $this->db->query(
            'SELECT id_tp, id_smt, id_siswa FROM kelas_siswa '
            . 'WHERE id_siswa IS NOT NULL GROUP BY id_tp, id_smt, id_siswa HAVING COUNT(*) > 1 LIMIT 1'
        )->row();
        if ($duplicates) {
            throw new RuntimeException(
                'Migration peminatan dibatalkan: kelas_siswa memiliki penempatan siswa ganda. '
                . 'Tinjau data tahun ' . $duplicates->id_tp . ', semester ' . $duplicates->id_smt
                . ', siswa ' . $duplicates->id_siswa . ' sebelum menjalankan ulang.'
            );
        }

        if (!$this->db->field_exists('peminatan_id', 'master_siswa')) {
            $this->dbforge->add_column('master_siswa', array(
                'peminatan_id' => array('type' => 'INT', 'constraint' => 11, 'null' => TRUE),
                'program_mak' => array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE),
            ));
        } elseif (!$this->db->field_exists('program_mak', 'master_siswa')) {
            $this->dbforge->add_column('master_siswa', array(
                'program_mak' => array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE),
            ));
        }
        if (!$this->db->field_exists('bank_program_mak', 'cbt_bank_soal')) {
            $this->dbforge->add_column('cbt_bank_soal', array(
                'bank_program_mak' => array('type' => 'VARCHAR', 'constraint' => 20, 'null' => TRUE),
            ));
        }

        $umum = $this->ensureMajor('UM', 'UMUM');
        $ipa = $this->ensureMajor('IPA', 'IPA');
        $ips = $this->ensureMajor('IPS', 'IPS');
        $mak = $this->ensureMajor('MAK', 'MAK');

        $this->db->query(
            'UPDATE master_siswa s JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa '
            . 'JOIN master_kelas k ON k.id_kelas = ks.id_kelas '
            . 'SET s.peminatan_id = ? WHERE s.peminatan_id IS NULL AND k.level_id = 10',
            array($umum)
        );
        $this->backfillClass('XII-A', $ipa);
        $this->backfillClass('XII-B', $ips);
        $this->backfillClass('XII-C', $mak, 'TAHFIDZ');
        $this->backfillClass('XII-D', $mak, 'KITAB');

        $this->db->query(
            'ALTER TABLE kelas_siswa MODIFY id_kelas_siswa INT(11) NOT NULL AUTO_INCREMENT'
        );
        if (!$this->indexExists('kelas_siswa', 'uq_kelas_siswa_period_student')) {
            $this->db->query(
                'ALTER TABLE kelas_siswa ADD UNIQUE KEY uq_kelas_siswa_period_student (id_tp, id_smt, id_siswa)'
            );
        }
    }

    private function backfillClass($name, $majorId, $program = NULL)
    {
        $sql = 'UPDATE master_siswa s JOIN kelas_siswa ks ON ks.id_siswa = s.id_siswa '
            . 'JOIN master_kelas k ON k.id_kelas = ks.id_kelas '
            . 'SET s.peminatan_id = COALESCE(s.peminatan_id, ?)';
        $params = array($majorId);
        if ($program !== NULL) {
            $sql .= ', s.program_mak = COALESCE(s.program_mak, ?)';
            $params[] = $program;
        }
        $needsBackfill = $program === NULL
            ? 's.peminatan_id IS NULL'
            : '(s.peminatan_id IS NULL OR s.program_mak IS NULL)';
        $sql .= " WHERE {$needsBackfill} AND UPPER(REPLACE(REPLACE(TRIM(k.nama_kelas), ' ', '-'), '_', '-')) = ?";
        $params[] = $name;
        $this->db->query($sql, $params);
    }

    public function down()
    {
        if ($this->indexExists('kelas_siswa', 'uq_kelas_siswa_period_student')) {
            $this->db->query('ALTER TABLE kelas_siswa DROP INDEX uq_kelas_siswa_period_student');
        }
        if ($this->db->field_exists('bank_program_mak', 'cbt_bank_soal')) {
            $this->dbforge->drop_column('cbt_bank_soal', 'bank_program_mak');
        }
        if ($this->db->field_exists('program_mak', 'master_siswa')) {
            $this->dbforge->drop_column('master_siswa', 'program_mak');
        }
        if ($this->db->field_exists('peminatan_id', 'master_siswa')) {
            $this->dbforge->drop_column('master_siswa', 'peminatan_id');
        }
        // Rollback tidak menghapus master UM/MAK dan tidak dapat memulihkan nilai backfill sebelumnya.
    }
}
