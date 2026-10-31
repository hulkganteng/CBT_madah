<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ujicoba_model extends CI_Model
{
    public function getBankList($id_tp, $id_smt, $id_kelas)
    {
        $this->db->select('b.id_bank, b.bank_kode, b.bank_nama, b.bank_mapel_id, b.bank_jenis_id, '
            . 'b.status, b.status_soal, b.opsi, m.nama_mapel, j.nama_jenis, '
            . '(SELECT COUNT(*) FROM cbt_soal s WHERE s.bank_id = b.id_bank) AS jml_soal');
        $this->db->from('cbt_bank_soal b');
        $this->db->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id', 'left');
        $this->db->join('cbt_jenis j', 'j.id_jenis = b.bank_jenis_id', 'left');
        $this->db->where('b.id_tp', $id_tp);
        $this->db->where('b.id_smt', $id_smt);
        $this->db->where('b.status', 1);
        $this->db->where('b.status_soal', 1);
        $this->db->order_by('m.nama_mapel', 'ASC');
        $banks = $this->db->get()->result();

        $result = [];
        foreach ($banks as $bank) {
            if ($this->bankTersediaKelas($bank->id_bank, $id_kelas)) {
                $result[] = $bank;
            }
        }
        return $result;
    }

    private function bankTersediaKelas($id_bank, $id_kelas)
    {
        $row = $this->db->select('bank_kelas')->get_where('cbt_bank_soal', ['id_bank' => $id_bank])->row();
        if (!$row) return false;

        $kk = @unserialize($row->bank_kelas);
        if (!is_array($kk) || empty($kk)) return true;

        foreach ($kk as $k) {
            if (isset($k['kelas_id']) && (int)$k['kelas_id'] === (int)$id_kelas) {
                return true;
            }
        }
        return false;
    }

    public function getBank($id_bank)
    {
        return $this->db->select('b.*, m.nama_mapel, j.nama_jenis')
            ->from('cbt_bank_soal b')
            ->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id', 'left')
            ->join('cbt_jenis j', 'j.id_jenis = b.bank_jenis_id', 'left')
            ->where('b.id_bank', $id_bank)
            ->get()->row();
    }

    public function getSoalList($id_bank)
    {
        return $this->db->select('id_soal, nomor_soal, jenis')
            ->from('cbt_soal')
            ->where('bank_id', $id_bank)
            ->order_by('nomor_soal', 'ASC')
            ->order_by('id_soal', 'ASC')
            ->get()->result();
    }

    public function getSoalById($id_soal)
    {
        return $this->db->get_where('cbt_soal', ['id_soal' => $id_soal])->row();
    }

    public function hitungNilai($id_bank, $answers)
    {
        $soals = $this->db->select('id_soal, jenis, jawaban')
            ->from('cbt_soal')
            ->where('bank_id', $id_bank)
            ->get()->result();

        $benar = 0;
        $total = 0;

        foreach ($soals as $soal) {
            if (!in_array((int)$soal->jenis, [1, 2, 4, 5])) continue;
            $total++;
            $jawaban = isset($answers[$soal->id_soal]) ? $answers[$soal->id_soal] : '';
            if ($this->isCorrect($soal, $jawaban)) {
                $benar++;
            }
        }

        $salah = $total - $benar;
        $nilai = $total > 0 ? round(($benar / $total) * 100) : 0;

        return ['benar' => $benar, 'salah' => $salah, 'total' => $total, 'nilai' => $nilai];
    }

    private function isCorrect($soal, $jawabanSiswa)
    {
        $jenis = (int)$soal->jenis;
        $kunci = $soal->jawaban;

        if ($jenis === 1) {
            return strcasecmp(trim($kunci), trim((string)$jawabanSiswa)) === 0;
        }

        if ($jenis === 2) {
            $kunciArr = @unserialize($kunci);
            if (!is_array($kunciArr)) return false;
            $kunciArr = array_map('strtoupper', array_map('trim', $kunciArr));
            sort($kunciArr);

            $jawaban = is_array($jawabanSiswa) ? $jawabanSiswa : [$jawabanSiswa];
            $jawaban = array_map('strtoupper', array_map('trim', $jawaban));
            sort($jawaban);

            return $kunciArr === $jawaban;
        }

        if ($jenis === 4 || $jenis === 5) {
            return strcasecmp($this->normalize($kunci), $this->normalize($jawabanSiswa)) === 0;
        }

        return false;
    }

    private function normalize($s)
    {
        $s = trim(html_entity_decode(strip_tags((string)$s), ENT_QUOTES, 'UTF-8'));
        $s = preg_replace('/\s+/u', ' ', $s);
        return mb_strtolower($s, 'UTF-8');
    }
}
