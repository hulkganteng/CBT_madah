<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Student_specialization_service
{
    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public function createFromPost()
    {
        $required = array('nama_siswa', 'nis', 'nisn', 'jenis_kelamin', 'kelas_awal', 'tahun_masuk', 'username', 'password');
        foreach ($required as $field) {
            if (trim((string) $this->ci->input->post($field, TRUE)) === '') {
                throw new InvalidArgumentException('Semua data wajib siswa harus diisi.');
            }
        }
        $nis = trim((string) $this->ci->input->post('nis', TRUE));
        $nisn = trim((string) $this->ci->input->post('nisn', TRUE));
        if (!ctype_digit($nis) || !ctype_digit($nisn)) {
            throw new InvalidArgumentException('NIS dan NISN hanya boleh berisi angka.');
        }
        list($majorId, $program) = $this->specialization($this->ci->input->post('kelas_awal', TRUE));
        $username = trim((string) $this->ci->input->post('username', TRUE));
        $duplicate = $this->ci->db->group_start()->where('nis', $nis)->or_where('nisn', $nisn)
            ->or_where('username', $username)->group_end()->count_all_results('master_siswa');
        if ($duplicate) {
            throw new InvalidArgumentException('NIS, NISN, atau username sudah digunakan siswa lain.');
        }

        $this->ci->db->trans_begin();
        $this->ci->db->query(
            'INSERT INTO master_siswa (nisn, nis, nama, jenis_kelamin, username, password, kelas_awal, tahun_masuk, foto, nik, warga_negara, uid, peminatan_id, program_mak) '
            . 'VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, UUID(), ?, ?)',
            array($nisn, $nis, trim((string) $this->ci->input->post('nama_siswa', TRUE)),
                $this->ci->input->post('jenis_kelamin', TRUE), $username, (string) $this->ci->input->post('password'),
                (int) $this->ci->input->post('kelas_awal', TRUE), $this->ci->input->post('tahun_masuk', TRUE),
                'assets/img/siswa.png', '', '', $majorId, $program)
        );
        $studentId = (int) $this->ci->db->insert_id();
        if (!$studentId) {
            $this->rollback('Data siswa gagal disimpan.');
        }
        $this->ci->db->insert('buku_induk', array('id_siswa' => $studentId, 'uid' => $this->uuid()));
        if ($this->ci->db->trans_status() === FALSE) {
            $this->rollback('Data siswa gagal disimpan.');
        }
        $this->ci->db->trans_commit();
        return $studentId;
    }

    public function normalizeForUpdate($studentId)
    {
        $placement = $this->ci->db->select('k.level_id')->from('kelas_siswa ks')
            ->join('master_kelas k', 'k.id_kelas = ks.id_kelas')
            ->join('master_tp tp', 'tp.id_tp = ks.id_tp AND tp.active = 1')
            ->join('master_smt smt', 'smt.id_smt = ks.id_smt AND smt.active = 1')
            ->where('ks.id_siswa', (int) $studentId)->get()->row();
        $student = $this->ci->db->select('kelas_awal')->get_where('master_siswa', array('id_siswa' => (int) $studentId))->row();
        list($majorId, $program) = $this->specialization($placement ? $placement->level_id : ($student ? $student->kelas_awal : NULL));
        return array('peminatan_id' => $majorId, 'program_mak' => $program);
    }

    public function importRows($json)
    {
        $rows = json_decode((string) $json, TRUE);
        if (!is_array($rows) || !$rows) {
            throw new InvalidArgumentException('Data impor siswa kosong atau tidak valid.');
        }
        $allowed = array_flip($this->ci->db->list_fields('master_siswa'));
        unset($allowed['id_siswa'], $allowed['uid'], $allowed['peminatan_id'], $allowed['program_mak']);
        $this->ci->db->trans_begin();
        $period = $this->activePeriod();
        $withoutClass = 0;
        foreach ($rows as $index => $row) {
            $data = is_array($row) ? array_intersect_key($row, $allowed) : array();
            foreach (array('nis', 'nisn', 'nama', 'username', 'password') as $field) {
                if (trim((string) ($data[$field] ?? '')) === '') {
                    $this->rollback('Baris impor ke-' . ($index + 1) . ': ' . strtoupper($field) . ' wajib diisi.');
                }
            }
            $_POST['peminatan_id'] = !empty($row['peminatan_id']) ? $row['peminatan_id'] : $this->ci->input->post('peminatan_id', TRUE);
            $_POST['program_mak'] = !empty($row['program_mak']) ? $row['program_mak'] : $this->ci->input->post('program_mak', TRUE);
            list($majorId, $program) = $this->specialization($data['kelas_awal'] ?? NULL);
            $data['uid'] = $this->uuid();
            $data['peminatan_id'] = $majorId;
            $data['program_mak'] = $program;
            $data['nik'] = isset($data['nik']) ? $data['nik'] : '';
            $data['warga_negara'] = isset($data['warga_negara']) ? $data['warga_negara'] : '';
            $this->ci->db->insert('master_siswa', $data);
            $studentId = (int) $this->ci->db->insert_id();
            if (!$studentId) {
                $this->rollback('Baris impor ke-' . ($index + 1) . ' gagal disimpan; periksa data ganda.');
            }
            $this->ci->db->insert('buku_induk', array('id_siswa' => $studentId, 'uid' => $this->uuid()));
            $classId = (int) ($row['id_kelas'] ?? 0);
            if ($classId) {
                if (!$this->ci->db->where($period)->where('id_kelas', $classId)->count_all_results('master_kelas')) {
                    $this->rollback('Baris impor ke-' . ($index + 1) . ': rombel tidak valid untuk periode aktif.');
                }
                $this->ci->db->insert('kelas_siswa', $period + array('id_siswa' => $studentId, 'id_kelas' => $classId));
            } else {
                $withoutClass++;
            }
        }
        if ($this->ci->db->trans_status() === FALSE) {
            $this->rollback('Impor dibatalkan karena ada data yang gagal disimpan.');
        }
        $this->ci->db->trans_commit();
        return array('total' => count($rows), 'without_class' => $withoutClass);
    }

    private function specialization($levelId)
    {
        if ((int) $levelId === 10) {
            $um = $this->ci->db->select('id_jurusan')->where_in('kode_jurusan', array('UM', 'NON'))
                ->get('master_jurusan')->row();
            return array($um ? (int) $um->id_jurusan : NULL, NULL);
        }
        $majorId = (int) $this->ci->input->post('peminatan_id', TRUE);
        $major = $majorId ? $this->ci->db->select('id_jurusan, kode_jurusan')
            ->get_where('master_jurusan', array('id_jurusan' => $majorId, 'status' => 1))->row() : NULL;
        if (!$major) {
            throw new InvalidArgumentException('Peminatan siswa harus dipilih.');
        }
        $program = strtoupper(trim((string) $this->ci->input->post('program_mak', TRUE)));
        if (strtoupper((string) $major->kode_jurusan) !== 'MAK') {
            $program = NULL;
        } elseif (!in_array($program, array('TAHFIDZ', 'KITAB'), TRUE)) {
            throw new InvalidArgumentException('Program MAK harus Tahfidz atau Kitab.');
        }
        return array((int) $major->id_jurusan, $program);
    }

    private function activePeriod()
    {
        $tp = $this->ci->db->select('id_tp')->get_where('master_tp', array('active' => 1))->row();
        $smt = $this->ci->db->select('id_smt')->get_where('master_smt', array('active' => 1))->row();
        if (!$tp || !$smt) {
            throw new RuntimeException('Tahun pelajaran atau semester aktif belum ditentukan.');
        }
        return array('id_tp' => (int) $tp->id_tp, 'id_smt' => (int) $smt->id_smt);
    }

    private function uuid()
    {
        return $this->ci->db->query('SELECT UUID() AS uid')->row()->uid;
    }

    private function rollback($message)
    {
        $this->ci->db->trans_rollback();
        throw new RuntimeException($message);
    }
}
