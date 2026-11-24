<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
 class Cbtrekap extends CI_Controller { public function __construct() { goto KnOUY; OymMq: show_error("\110\141\x6e\x79\141\x20\101\x64\155\151\x6e\151\x73\164\162\x61\x74\x6f\x72\40\171\x61\156\147\x20\x64\x69\142\x65\162\x69\x20\x68\x61\x6b\x20\165\x6e\164\x75\153\40\x6d\x65\x6e\x67\141\x6b\163\x65\x73\x20\x68\x61\154\141\x6d\x61\x6e\x20\151\x6e\151\54\40\x3c\x61\x20\x68\x72\145\146\75\x22" . base_url("\144\141\x73\x68\142\x6f\141\162\x64") . "\x22\76\113\145\x6d\142\141\x6c\x69\40\x6b\x65\40\155\x65\x6e\165\40\141\167\x61\154\74\57\x61\76", 403, "\101\153\x73\x65\x73\x20\x54\x65\162\154\141\162\141\156\x67"); goto xnxJR; z_twg: $this->form_validation->set_error_delimiters('', ''); goto jQmDa; Lg1ph: $this->load->library(["\144\x61\164\141\x74\141\142\x6c\145\163", "\x66\157\x72\155\x5f\166\141\x6c\151\x64\x61\164\x69\x6f\x6e"]); goto TwfzW; PaI_6: dPL_t: goto Lg1ph; QuFVE: redirect("\141\165\x74\x68"); goto PaI_6; xnxJR: U33bc: goto lhxqP; toSx3: if (!$this->ion_auth->logged_in()) { goto uMGw3; } goto oDbfK; TwfzW: $this->load->library("\x75\x70\154\157\x61\x64"); goto z_twg; oDbfK: if (!(!$this->ion_auth->is_admin() && !$this->ion_auth->in_group("\147\165\x72\x75"))) { goto U33bc; } goto OymMq; iWXMK: uMGw3: goto QuFVE; KnOUY: parent::__construct(); goto toSx3; lhxqP: goto dPL_t; goto iWXMK; jQmDa: } public function output_json($data, $encode = true) { goto MCFkv; MCFkv: if (!$encode) { goto X6_QY; } goto KSh3R; KSh3R: $data = json_encode($data); goto AeOUx; xQ2LO: $this->output->set_content_type("\141\x70\160\x6c\x69\x63\x61\x74\151\157\x6e\57\152\163\157\156")->set_output($data); goto dxl1R; AeOUx: X6_QY: goto xQ2LO; dxl1R: } public function index() { goto G6X7W; YzLcb: $this->load->model("\103\x62\164\137\x6d\x6f\144\145\x6c", "\x63\x62\x74"); goto GHaUK; kSzcX: foreach ($data_jadwal as $rekap) { goto xduJu; v_asF: $rekap->dikoreksi = false; goto cnCS6; CpoQ3: gcodk: goto s0029; cnCS6: iLm5L: goto CpoQ3; pOa9N: $rekap->mengerjakan = $terpakai; goto zQtV6; Tuo0a: Pm0Wz: goto v_asF; FaRVo: $rekap->hanya_pg = $hanya_pg; goto MnP90; xduJu: $terpakai = isset($jadwal_dikerjakan[$rekap->id_jadwal]) ? count($jadwal_dikerjakan[$rekap->id_jadwal]) : 0; goto pOa9N; UKOrb: $rekap->dikoreksi = true; goto EZLlT; MnP90: if (!$hanya_pg && isset($koreksi[$rekap->id_jadwal]) && isset($koreksi[$rekap->id_jadwal][0])) { goto Pm0Wz; } goto UKOrb; EZLlT: goto iLm5L; goto Tuo0a; zQtV6: $hanya_pg = $rekap->tampil_pg > 0 && $rekap->tampil_kompleks == 0 && $rekap->tampil_jodohkan == 0 && $rekap->tampil_isian == 0 && $rekap->tampil_esai == 0; goto FaRVo; s0029: } goto xx1om; L0pvj: foreach ($data_jadwal as $rekap) { goto zOz0M; CadA3: $rekap->dikoreksi = true; goto bFYMP; fWnXT: $hanya_pg = $rekap->tampil_pg > 0 && $rekap->tampil_kompleks == 0 && $rekap->tampil_jodohkan == 0 && $rekap->tampil_isian == 0 && $rekap->tampil_esai == 0; goto QUa0u; OK_h_: if (!$hanya_pg && isset($koreksi[$rekap->id_jadwal]) && isset($koreksi[$rekap->id_jadwal][0])) { goto e7BO7; } goto CadA3; x1TcB: aZ5ju: goto TtMLA; QUa0u: $rekap->hanya_pg = $hanya_pg; goto OK_h_; Lj4Rw: e7BO7: goto MnR3Z; vKaTd: s8uZN: goto x1TcB; MnR3Z: $rekap->dikoreksi = false; goto vKaTd; gJ8Zu: $rekap->mengerjakan = $terpakai; goto fWnXT; zOz0M: $terpakai = isset($jadwal_dikerjakan[$rekap->id_jadwal]) ? count($jadwal_dikerjakan[$rekap->id_jadwal]) : 0; goto gJ8Zu; bFYMP: goto s8uZN; goto Lj4Rw; TtMLA: } goto XUSgD; TELlQ: $this->load->view("\x5f\164\145\155\x70\154\141\x74\x65\x73\x2f\x64\141\163\x68\x62\157\x61\162\144\57\x5f\x66\157\157\x74\145\162"); goto CQw2q; BhdEf: $data["\160\x72\157\x66\151\x6c\x65"] = $this->dashboard->getProfileAdmin($user->id); goto QiMmE; WQonO: $this->load->view("\x6d\145\x6d\x62\145\162\x73\57\x67\165\x72\x75\57\x74\x65\155\160\154\x61\x74\145\x73\x2f\150\x65\141\x64\x65\162", $data); goto ByW8J; PB_L0: $data["\x73\x6d\x74"] = $this->dashboard->getSemester(); goto uI8i2; ByW8J: $this->load->view("\x63\142\164\57\162\x65\153\141\x70\x2f\144\141\164\141"); goto xb4qR; U210E: $rekapJadwal = $data_jadwal; goto GoWlB; FZ2xt: $data["\141\144\x61\137\x72\x65\153\x61\x70"] = $this->cbt->getAllRekap(); goto FkCm9; MZdXL: $data["\153\157\x72\x65\x6b\163\151"] = $koreksi; goto flIYe; nTlqX: $data["\153\x65\x6c\x61\163\x65\x73"] = $this->cbt->getKelas(); goto akc9W; d4ZVb: $smt = $this->master->getSemesterActive(); goto zrhdY; jlaQ3: $rekapNilai = $this->cbt->getRekapJadwal(); goto kSzcX; QiMmE: $data_jadwal = $this->cbt->getDataJadwal($tp->id_tp, $smt->id_smt); goto jlaQ3; PSbXS: $data["\x72\x65\153\x61\160\163"] = $rekaps; goto FZ2xt; OXimf: $data["\x74\141\150\165\156\x73"] = $this->cbt->getDistinctTahun($this->rekapGuruId()); goto yaX8m; GoWlB: $rekaps = array_merge($rekapJadwal, $rekapNilai); goto PSbXS; mxC_7: $data["\147\x75\162\x75"] = $guru; goto WQonO; dbMO5: $rekaps = array_merge($rekapJadwal, $rekapNilai); goto RmxiV; CQw2q: PHsQm: goto mQBCj; xx1om: Jtk1w: goto U210E; oEMkS: $data["\152\x65\x6e\151\x73"] = $this->cbt->getDistinctJenisUjian($this->rekapGuruId()); goto B9zY1; B9zY1: $data["\153\x65\154\141\163"] = $this->cbt->getDistinctKelas(null, $this->rekapGuruId()); goto OXimf; hVrpZ: $data = ["\x75\163\x65\x72" => $user, "\152\165\144\165\154" => "\122\145\x6b\x61\160\x20\110\x61\163\151\154\x20\x50\145\156\151\x6c\x61\x69\x61\x6e", "\163\x75\x62\152\x75\x64\165\x6c" => "\120\145\156\x69\x6c\141\x69\141\x6e", "\163\145\x74\x74\x69\x6e\x67" => $this->dashboard->getSetting()]; goto v2xaY; RQcwp: sC7YU: goto BhdEf; G6X7W: $this->load->model("\115\141\163\164\x65\162\137\155\x6f\144\x65\154", "\155\x61\163\x74\x65\x72"); goto u9BMf; RmxiV: $data["\162\x65\153\x61\160\163"] = $rekaps; goto FzTsl; HvD1C: $this->load->view("\143\x62\164\x2f\x72\145\153\141\x70\57\x64\141\x74\x61"); goto TELlQ; xb4qR: $this->load->view("\x6d\x65\x6d\x62\x65\162\163\x2f\147\165\162\165\57\x74\x65\155\160\154\x61\164\x65\163\x2f\x66\157\x6f\164\145\162"); goto n3vyq; gO0vb: $koreksi = $this->cbt->getTotalKoreksi(); goto MZdXL; dC1_L: if ($this->ion_auth->is_admin()) { goto sC7YU; } goto fgFdi; flIYe: $jadwal_dikerjakan = $this->cbt->getJadwalTerpakai(); goto dC1_L; FkCm9: $this->load->view("\137\x74\145\x6d\160\x6c\x61\164\145\163\57\x64\x61\163\150\x62\157\x61\x72\x64\x2f\137\150\145\141\x64\145\162", $data); goto HvD1C; v2xaY: $tp = $this->master->getTahunActive(); goto d4ZVb; akc9W: $data["\142\141\156\153\x73"] = $this->cbt->getAllBankSoal(); goto gO0vb; u9BMf: $this->load->model("\104\141\163\150\x62\157\x61\x72\144\137\155\x6f\x64\145\154", "\144\141\163\150\x62\x6f\141\162\144"); goto YzLcb; lC9_9: $rekapNilai = $this->cbt->getRekapJadwal($guru->id_guru); goto L0pvj; uI8i2: $data["\x73\x6d\x74\137\x61\143\164\151\166\x65"] = $smt; goto oEMkS; Bq7uO: $data["\164\x70\137\x61\x63\x74\151\166\x65"] = $tp; goto PB_L0; fgFdi: $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt); goto eAfI0; rKfl4: $data["\163\x65\x73\x69\163"] = $this->dropdown->getAllSesi(); goto nTlqX; zrhdY: $data["\164\x70"] = $this->dashboard->getTahun(); goto Bq7uO; SUhPG: $user = $this->ion_auth->user()->row(); goto hVrpZ; OHEJE: $rekapJadwal = $data_jadwal; goto dbMO5; FzTsl: $data["\141\x64\x61\137\x72\145\153\141\x70"] = $this->cbt->getAllRekap($guru->id_guru); goto mxC_7; n3vyq: goto PHsQm; goto RQcwp; GHaUK: $this->load->model("\x44\x72\157\160\144\157\x77\156\x5f\155\157\144\145\x6c", "\x64\x72\157\x70\x64\x6f\167\x6e"); goto SUhPG; XUSgD: Fj8km: goto OHEJE; yaX8m: $data["\x73\145\155\x65\163\x74\145\x72"] = $this->cbt->getDistinctSmt($this->rekapGuruId()); goto GzD0_; eAfI0: $data_jadwal = $this->cbt->getDataJadwal($tp->id_tp, $smt->id_smt, $guru->id_guru); goto lC9_9; GzD0_: $data["\162\165\141\x6e\147\163"] = $this->cbt->getAllRuang(); goto rKfl4; mQBCj: } public function perMapel() { goto xCa8L; C35cU: RIO7r: goto qcXRj; xCa8L: $this->load->model("\x4d\x61\x73\x74\145\162\x5f\155\x6f\x64\145\x6c", "\x6d\141\x73\x74\145\x72"); goto jUUoa; nH9Y7: $data["\x73\145\x6d\x65\163\x74\x65\162"] = $this->cbt->getDistinctSmt($this->rekapGuruId()); goto uSivq; zY03a: $data["\164\160\x5f\141\143\164\151\x76\x65"] = $tp; goto J8iq5; rRhA9: $tp = $this->master->getTahunActive(); goto PTkE7; ITmjE: $data["\x67\x75\x72\165"] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt); goto O5a9x; YI_tG: $this->load->view("\x5f\x74\145\155\x70\154\x61\x74\145\163\x2f\x64\x61\x73\150\142\x6f\141\x72\x64\x2f\137\150\145\141\144\x65\162", $data); goto REzKm; jc436: goto RIO7r; goto S8DMh; uSivq: if ($this->ion_auth->is_admin()) { goto dtINr; } goto ITmjE; iFicE: $this->load->view("\143\142\x74\x2f\162\x65\153\x61\x70\57\160\x65\x72\x6d\x61\160\x65\x6c"); goto Sm2Zo; Sm2Zo: $this->load->view("\155\x65\155\x62\145\x72\x73\x2f\147\x75\x72\165\57\164\145\x6d\160\x6c\141\164\145\163\x2f\146\157\157\164\x65\162"); goto jc436; J8iq5: $data["\163\x6d\x74"] = $this->dashboard->getSemester(); goto Du0xP; Lh1Yg: $this->load->model("\103\142\x74\x5f\x6d\x6f\x64\x65\154", "\x63\142\x74"); goto jTj8v; RkRDf: $data = ["\165\163\x65\162" => $user, "\x6a\x75\144\x75\154" => "\110\141\x73\x69\x6c\x20\x53\x69\x73\x77\x61", "\x73\x75\x62\152\165\144\165\154" => "\x53\x74\141\x74\x75\x73\40\123\151\x73\167\x61", "\163\x65\x74\x74\151\x6e\147" => $this->dashboard->getSetting()]; goto rRhA9; Du0xP: $data["\163\155\x74\137\x61\x63\164\x69\x76\x65"] = $smt; goto tCUHf; S8DMh: dtINr: goto H5p2w; REzKm: $this->load->view("\143\142\164\57\x72\x65\153\x61\160\x2f\160\x65\x72\x6d\x61\x70\x65\x6c"); goto zo7iy; zo7iy: $this->load->view("\137\164\x65\x6d\160\154\141\x74\x65\x73\57\x64\x61\x73\150\142\157\x61\x72\x64\x2f\137\146\x6f\157\164\145\162"); goto C35cU; H5p2w: $data["\x70\162\x6f\146\151\154\x65"] = $this->dashboard->getProfileAdmin($user->id); goto YI_tG; jUUoa: $this->load->model("\104\141\x73\150\x62\157\141\162\144\x5f\155\x6f\x64\145\x6c", "\144\141\163\150\x62\157\141\x72\144"); goto Lh1Yg; F9xaC: $data["\164\160"] = $this->dashboard->getTahun(); goto zY03a; M3aQ2: $data["\x6b\145\x6c\141\x73"] = $this->cbt->getDistinctKelas(null, $this->rekapGuruId()); goto bvS97; tCUHf: $data["\152\x65\x6e\x69\x73"] = $this->cbt->getDistinctJenisUjian($this->rekapGuruId()); goto M3aQ2; PTkE7: $smt = $this->master->getSemesterActive(); goto F9xaC; O5a9x: $this->load->view("\x6d\x65\155\142\145\162\x73\x2f\x67\x75\x72\x75\57\164\x65\155\160\154\141\164\x65\163\57\x68\145\141\144\x65\162", $data); goto iFicE; bvS97: $data["\164\141\x68\165\x6e"] = $this->cbt->getDistinctTahun($this->rekapGuruId()); goto nH9Y7; jTj8v: $user = $this->ion_auth->user()->row(); goto RkRDf; qcXRj: }

    public function backupNilai($id_jadwal)
    {
        $this->rekapJadwal([(int) $id_jadwal]);
    }

    public function bulkBackup()
    {
        $ids = json_decode((string) $this->input->post('ids', true));
        $ids = is_array($ids) ? array_unique(array_filter($ids, static function ($id) {
            return (is_int($id) || is_string($id)) && preg_match('/^[1-9][0-9]*$/D', (string) $id);
        })) : [];
        $this->rekapJadwal($ids);
    }

    private function rekapJadwal(array $ids)
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Dropdown_model', 'dropdown');

        $jadwalTersimpan = 0;
        $nilaiTersimpan = 0;
        foreach ($ids as $id) {
            $jumlah = $this->simpanRekapJadwal((int) $id);
            $jadwalTersimpan += (int) ($jumlah > 0);
            $nilaiTersimpan += $jumlah;
        }

        $status = $nilaiTersimpan > 0;
        $label = $status && $jadwalTersimpan === count($ids) ? 'success' : 'warning';
        $pesan = $status
            ? "Berhasil merekap {$nilaiTersimpan} nilai siswa dari {$jadwalTersimpan} jadwal."
            : 'Rekap belum tersimpan. Periksa nilai siswa pada Hasil Ujian.';
        if ($status && $jadwalTersimpan < count($ids)) {
            $pesan .= ' Beberapa jadwal belum berhasil direkap.';
        }
        $this->session->set_flashdata('rekapnilai', '<div id="flashdata" class="alert alert-' . $label
            . ' w-100" role="alert">' . $pesan . '</div>');
        $this->output_json($status);
    }

    private function simpanRekapJadwal(int $idJadwal): int
    {
        if ($idJadwal <= 0 || !$this->db->trans_begin()) {
            return 0;
        }

        $committed = false;
        try {
            $lock = $this->db->query('SELECT id_jadwal FROM cbt_jadwal WHERE id_jadwal = ? FOR UPDATE', [$idJadwal]);
            if (!$lock || !$lock->num_rows()) {
                return 0;
            }

            $jadwal = $this->cbt->getJadwalById($idJadwal);
            $guruId = $this->rekapGuruId();
            if (!$jadwal || ($guruId !== null && ((int) $jadwal->bank_guru_id !== $guruId
                || $this->cbt->hasForeignExamArchive($idJadwal, $guruId)))) {
                return 0;
            }
            $tahun = $this->dashboard->getTahunById($jadwal->id_tp);
            $semester = $this->dashboard->getSemesterById($jadwal->id_smt);
            $kelasBank = @unserialize((string) $jadwal->bank_kelas, ['allowed_classes' => false]);
            $kelas = is_array($kelasBank) ? array_values(array_filter(array_column($kelasBank, 'kelas_id'))) : [];
            if (!$tahun || !$semester || !$kelas) {
                return 0;
            }

            $nilai = $this->cbt->getAllNilaiSiswa($idJadwal);
            $siswa = $this->cbt->getSiswaByKelasArray($jadwal->id_tp, $jadwal->id_smt, $kelas, $idJadwal);
            if (!$nilai || !$siswa) {
                return 0;
            }

            $meta = [
                'id_jadwal' => $idJadwal,
                'id_tp' => $tahun->id_tp,
                'tp' => $tahun->tahun,
                'id_smt' => $semester->id_smt,
                'smt' => $semester->nama_smt,
                'id_jenis' => $jadwal->id_jenis,
                'kode_jenis' => $jadwal->kode_jenis,
                'id_bank' => $jadwal->id_bank,
                'id_mapel' => $jadwal->id_mapel,
                'id_guru' => $jadwal->id_guru,
                'bobot_pg' => $jadwal->bobot_pg,
                'bobot_esai' => $jadwal->bobot_esai
            ];
            $tipe = [1 => 'pg', 2 => 'kompleks', 3 => 'jodohkan', 4 => 'isian', 5 => 'esai'];
            $kunci = array_fill_keys(array_keys($tipe), []);
            foreach ($this->cbt->getNomorSoalByBank($jadwal->id_bank) as $idSoal => $soal) {
                if (isset($tipe[$soal->jenis])) {
                    $kunci[$soal->jenis][] = ['no_soal' => $idSoal, 'jawab' => $soal->jawaban];
                }
            }

            $rekap = $meta + [
                'bank_kode' => $jadwal->bank_kode,
                'bank_kelas' => $jadwal->bank_kelas,
                'nama_kelas' => serialize($this->dropdown->getAllKelasByArrayId($jadwal->id_tp, $jadwal->id_smt, $kelas)),
                'bank_level' => $jadwal->bank_level,
                'nama_mapel' => $jadwal->nama_mapel,
                'kode' => $jadwal->kode,
                'tgl_mulai' => $jadwal->tgl_mulai,
                'tgl_selesai' => $jadwal->tgl_selesai,
                'tampil_pg' => $jadwal->tampil_pg,
                'tampil_esai' => $jadwal->tampil_esai,
                'jawaban_pg' => serialize($kunci[1]),
                'jawaban_esai' => serialize($kunci[5]),
                'nama_guru' => $jadwal->nama_guru
            ];
            $kolom = [2 => 'soal_kompleks', 3 => 'soal_jodohkan', 4 => 'soal_isian', 5 => 'soal_essai'];
            foreach ($kolom as $jenis => $field) {
                $rekap[$field] = serialize([
                    'tampil' => $jadwal->{'tampil_' . $tipe[$jenis]},
                    'bobot' => $jadwal->{'bobot_' . $tipe[$jenis]},
                    'jawaban' => $kunci[$jenis]
                ]);
            }

            $durasi = $this->cbt->getIdSiswaFromDurasiByJadwal($idJadwal);
            $jawaban = $this->cbt->getIdSiswaFromJawabanByJadwal($idJadwal);
            $arsip = [];
            foreach ($siswa as $peserta) {
                $idSiswa = $peserta->id_siswa;
                if (!isset($nilai[$idSiswa])) {
                    continue;
                }
                $skor = $nilai[$idSiswa];
                $jawabanSiswa = array_fill_keys(array_keys($tipe), []);
                foreach ($jawaban[$idSiswa] ?? [] as $item) {
                    if (isset($tipe[$item->jenis_soal])) {
                        $jawabanSiswa[$item->jenis_soal][] = ['no_soal' => $item->id_soal, 'jawab' => $item->jawaban_siswa];
                    }
                }

                $row = $meta + [
                    'id_siswa' => $idSiswa,
                    'nama_siswa' => $peserta->nama,
                    'no_peserta' => $peserta->nomor_peserta,
                    'id_kelas' => $peserta->id_kelas,
                    'kelas' => $peserta->nama_kelas,
                    'mulai' => $durasi[$idSiswa]->mulai ?? '',
                    'selesai' => $durasi[$idSiswa]->selesai ?? '',
                    'durasi' => $durasi[$idSiswa]->lama_ujian ?? '',
                    'jawaban_pg' => serialize($jawabanSiswa[1]),
                    'nilai_pg' => round($skor->pg_nilai, 2),
                    'jawaban_esai' => serialize($jawabanSiswa[5]),
                    'nilai_esai' => round($skor->essai_nilai, 2),
                    'bs_nilai' => round($skor->bs_nilai ?? 0, 2)
                ];
                foreach ($kolom as $jenis => $field) {
                    $fieldNilai = $jenis === 5 ? 'essai_nilai' : $tipe[$jenis] . '_nilai';
                    $row[$field] = serialize([
                        'bobot' => $jadwal->{'bobot_' . $tipe[$jenis]},
                        'jawaban' => $jawabanSiswa[$jenis],
                        'nilai' => $skor->$fieldNilai
                    ]);
                }
                $arsip[$idSiswa] = $row;
            }

            $scope = ['id_jadwal' => $idJadwal];
            $archiveScope = $guruId === null ? $scope : $scope + ['id_guru' => $guruId];
            if (!$arsip || !$this->db->delete('cbt_rekap', $archiveScope)
                || !$this->db->insert('cbt_rekap', $rekap)
                || !$this->db->delete('cbt_rekap_nilai', $archiveScope)
                || $this->db->insert_batch('cbt_rekap_nilai', array_values($arsip)) !== count($arsip)
                || !$this->db->update('cbt_jadwal', ['rekap' => 1], $scope)
                || !$this->db->trans_status()) {
                return 0;
            }

            $committed = $this->db->trans_commit();
            return $committed ? count($arsip) : 0;
        } finally {
            if (!$committed) {
                $this->db->trans_rollback();
            }
        }
    }

    public function hapusRekap()
    {
        $input = json_decode((string) $this->input->post('ids', true));
        $ids = is_array($input) ? array_unique(array_filter($input, static function ($id) {
            return (is_int($id) || is_string($id)) && preg_match('/^[1-9][0-9]*$/D', (string) $id);
        })) : [];
        if (!$ids) {
            $this->output_json(['success' => false, 'total' => 0]);
            return;
        }

        $idGuru = $this->rekapGuruId();
        $this->db->trans_start();
        foreach (['cbt_rekap_nilai', 'cbt_rekap'] as $table) {
            if ($idGuru !== null) {
                $this->db->where('id_guru', $idGuru);
            }
            $this->db->where_in('id_jadwal', $ids)->delete($table);
        }
        $success = $this->db->trans_complete();
        $message = $success ? 'Arsip rekap berhasil dihapus.' : 'Arsip rekap gagal dihapus.';
        $type = $success ? 'success' : 'danger';
        $this->session->set_flashdata('rekapnilai', '<div id="flashdata" class="alert alert-' . $type
            . ' w-100" role="alert">' . $message . '</div>');
        $this->output_json(['success' => $success, 'total' => $success ? count($ids) : 0]);
    }

    private $rekapTeacherId;

    private function rekapGuruId()
    {
        if ($this->ion_auth->is_admin()) {
            return null;
        }
        if ($this->rekapTeacherId !== null) {
            return $this->rekapTeacherId;
        }
        $user = $this->ion_auth->user()->row();
        $teacher = $user ? $this->db->select('id_guru')->get_where('master_guru', ['id_user' => $user->id])->row() : null;
        if (!$teacher || (int) $teacher->id_guru <= 0) {
            show_error('Akses rekap memerlukan profil guru yang terhubung dengan akun ini.', 403);
        }
        return $this->rekapTeacherId = (int) $teacher->id_guru;
    }

    function getDataFromArray1ByUserId($array, $userId) { goto TKNPj; TKNPj: foreach ($array as $key => $data) { goto TvGhy; TvGhy: if (!($data->id_siswa == $userId)) { goto kEkSv; } goto mH9tl; n8SLX: kEkSv: goto JEaXw; mH9tl: return $array; goto n8SLX; JEaXw: aeCr_: goto n6bFh; n6bFh: } goto sntN8; sntN8: xef1d: goto GguZk; GguZk: return array(); goto D7FxD; D7FxD: } public function getJenisPenilaian() { goto q0YqK; dheJ8: $smt = $this->input->get("\x73\155\x74"); goto OHZGt; OHZGt: $jadwals = $this->cbt->getJenisRekap($tahun, $smt); goto Hz7WG; q0YqK: $this->load->model("\103\142\x74\x5f\x6d\x6f\x64\145\x6c", "\x63\142\x74"); goto qdLJD; qdLJD: $tahun = $this->input->get("\x74\x61\x68\165\156"); goto dheJ8; Hz7WG: }
    public function getNilaiKelas()
    {
        $this->load->model('Master_model', 'master');
        $this->load->model('Cbt_model', 'cbt');
        $idGuru = $this->rekapGuruId();
        $kelas = $this->input->get('kelas');
        $jenis = $this->input->get('jenis');
        $tahun = $this->input->get('tahun');
        $semester = $this->input->get('smt');
        $idJadwal = $this->input->get('mapel') ?: '0';
        $data = ['siswa' => [], 'nilai' => [], 'info' => []];
        if (!is_scalar($kelas) || !ctype_digit((string) $kelas)
            || !is_scalar($idJadwal) || !ctype_digit((string) $idJadwal)
            || !is_string($jenis) || !is_string($tahun) || !is_string($semester)) {
            $this->output_json($data);
            return;
        }
        $rombel = $this->master->getKelasById($kelas);
        if (!$rombel) {
            $this->output_json($data);
            return;
        }

        $jadwals = $this->cbt->getAllRekapByJadwal($tahun, $semester, $jenis, $rombel->level_id, $idJadwal, $idGuru);
        $info = [];
        foreach ($jadwals as $jadwal) {
            $classes = @unserialize((string) $jadwal->bank_kelas, ['allowed_classes' => false]);
            $classIds = is_array($classes) ? array_column($classes, 'kelas_id') : [];
            if (!in_array($kelas, $classIds) && !in_array('Semua', $classIds, true)) {
                continue;
            }
            $jadwal->bank_kelas = $classes;
            $jadwal->jawaban_pg = @unserialize((string) $jadwal->jawaban_pg, ['allowed_classes' => false]);
            $jadwal->jawaban_esai = @unserialize((string) $jadwal->jawaban_esai, ['allowed_classes' => false]);
            $info[$jadwal->id_jadwal] = $jadwal;
        }
        $rekaps = $this->cbt->getAllNilaiRekapByJadwal($tahun, $semester, $jenis, $kelas, $idJadwal, $idGuru);
        $siswa = [];
        foreach ($rekaps as $rekap) {
            if (!isset($info[$rekap->id_jadwal])) {
                continue;
            }
            $rekap->jawaban_pg = $this->unserialize_with_key($rekap->jawaban_pg);
            foreach (['soal_kompleks', 'soal_jodohkan', 'soal_isian', 'soal_essai'] as $field) {
                $rekap->$field = @unserialize((string) $rekap->$field, ['allowed_classes' => false]);
            }
            $siswa[$rekap->id_siswa] = ['id_siswa' => $rekap->id_siswa, 'nomor_peserta' => $rekap->nomor_peserta, 'nama' => $rekap->nama];
            $data['nilai'][$rekap->id_siswa][$rekap->id_jadwal] = $rekap;
        }
        usort($siswa, static function ($a, $b) {
            return $a['nama'] <=> $b['nama'];
        });
        ksort($info);
        $data['siswa'] = $siswa;
        $data['info'] = array_values($info);
        $this->output_json($data);
    }

    public function olahNilai() { goto Krnom; ZIUoz: $data["\x6a\x61\x64\x77\141\154\137\x73\x65\154\x65\x63\164\145\144"] = $jadwal; goto CuFuM; QV263: lXLfL: goto OJ9PQ; Ieyb6: $data["\x6a\x61\144\167\141\154"] = $this->dropdown->getAllJadwalGuru($tp->id_tp, $smt->id_smt, $guru->id_guru); goto j2rts; BgjSv: goto lXLfL; goto r0QMe; UGo9z: $data["\156\x61\155\141\x5f\x6b\145\x6c\x61\x73"] = $kelas == null ? "\123\x69\x6c\141\x68\x6b\141\x6e\x20\x70\151\154\x69\150\40\153\145\x6c\141\x73" : $kls[$kelas]; goto C5YYn; nF31L: $data["\143\x6f\x6e\x76\145\x72\164"] = $convert; goto UU6Pp; XgdpL: $xa = $this->input->get("\x78\x61"); goto vJkYN; ydytQ: $jadwal = $this->input->get("\x6a\141\x64\x77\x61\x6c"); goto IY9PK; bRrSs: $data["\x73\151\163\x77\x61\163"] = $siswas; goto pyIC2; vJkYN: $xb = $this->input->get("\x78\142"); goto B8YJ8; tsTq3: tvXtw: goto bRrSs; JhPBX: if (!($kelas != null)) { goto Fbuih; } goto JYVID; hEwct: $this->load->model("\x44\x72\x6f\x70\144\x6f\167\156\137\155\x6f\x64\145\x6c", "\x64\162\x6f\x70\144\x6f\167\x6e"); goto SBaZq; Bcm79: $data["\x6a\x61\x64\x77\x61\154"] = $this->dropdown->getAllJadwal($tp->id_tp, $smt->id_smt); goto phyqX; UNDdA: foreach ($siswas as $siswa) { goto WXDVb; o8ATP: $siswa->soal_isian = json_decode(json_encode(unserialize($siswa->soal_isian))); goto Tlgi6; WXDVb: $siswa->jawaban_pg = $this->unserialize_with_key($siswa->jawaban_pg); goto vsiME; ch3jp: vA3Sw: goto pimmZ; vsiME: $siswa->soal_kompleks = json_decode(json_encode(unserialize($siswa->soal_kompleks))); goto t_36p; t_36p: $siswa->soal_jodohkan = json_decode(json_encode(unserialize($siswa->soal_jodohkan))); goto o8ATP; Tlgi6: $siswa->soal_essai = json_decode(json_encode(unserialize($siswa->soal_essai))); goto ch3jp; pimmZ: } goto tsTq3; UU6Pp: Xg0Eg: goto a75_0; eHHuI: $convert = ["\x79\141" => $ya, "\171\142" => $yb, "\x78\x61" => $xa, "\170\x62" => $xb]; goto nF31L; iJIDx: rgU4Q: goto hfvfZ; B8YJ8: if (!($ya != null)) { goto Xg0Eg; } goto eHHuI; sIoU3: $data["\164\x70\x5f\141\143\x74\151\166\x65"] = $tp; goto pIRBP; qF5Za: GzIn7: goto aEazr; ei5Ke: $this->load->view("\155\145\x6d\x62\x65\x72\163\57\147\165\x72\165\57\164\145\x6d\160\x6c\141\x74\x65\x73\57\x66\x6f\157\164\x65\x72"); goto BgjSv; Cpsrk: $tp = $this->dashboard->getTahunActive(); goto hr0OV; Q6cDr: if (!($ya != null)) { goto Yiciw; } goto a1zFF; pIRBP: $data["\x73\x6d\x74"] = $this->dashboard->getSemester(); goto H8nuZ; CuFuM: $kls = @unserialize($rekap->nama_kelas); goto Rts4x; H8nuZ: $data["\x73\155\x74\x5f\x61\x63\x74\x69\166\145"] = $smt; goto LHVA2; aEKEL: if (!($kelas != null)) { goto rgU4Q; } goto XIORd; a75_0: Fbuih: goto Ieyb6; hr0OV: $smt = $this->dashboard->getSemesterActive(); goto GAK_Q; tUesz: $xb = $this->input->get("\170\142"); goto Q6cDr; bZ0Oe: $this->load->view("\x63\142\x74\x2f\162\145\x6b\141\160\x2f\x6f\154\141\150"); goto ei5Ke; lr4qs: $rekap->soal_essai = json_decode(json_encode(unserialize($rekap->soal_essai))); goto SxfKm; Krnom: $this->load->model("\x4d\141\x73\164\x65\162\x5f\x6d\157\x64\145\x6c", "\x6d\x61\x73\x74\145\x72"); goto Qw9m9; pT_CT: $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt); goto LFOrr; aEazr: $data["\163\x69\163\x77\x61\x73"] = $siswas; goto us2qv; yPVco: $rekap->jawaban_pg = $this->unserialize_with_key($rekap->jawaban_pg); goto wjQ6A; ExoNo: $yb = $this->input->get("\171\x62"); goto XgdpL; T2mDe: $level = $this->master->getKelasById($kelas); goto ydytQ; wjQ6A: $rekap->soal_kompleks = json_decode(json_encode(unserialize($rekap->soal_kompleks))); goto K8FBv; GAK_Q: $data["\164\160"] = $this->dashboard->getTahun(); goto sIoU3; us2qv: $ya = $this->input->get("\x79\x61"); goto ExoNo; a1zFF: $convert = ["\171\x61" => $ya, "\x79\142" => $yb, "\170\x61" => $xa, "\170\142" => $xb]; goto qXuO4; pyIC2: $ya = $this->input->get("\x79\x61"); goto i77uo; c72td: $rekap = $this->cbt->getRekapByJadwalKelas($jadwal, $this->rekapGuruId()); goto KDGZo; LFOrr: $data["\147\165\x72\x75"] = $guru; goto JhPBX; r0QMe: ZrFc0: goto aEKEL; Qw9m9: $this->load->model("\x44\x61\x73\x68\x62\157\x61\162\x64\137\x6d\157\144\x65\154", "\144\141\163\150\142\x6f\x61\162\x64"); goto opdz6; C5YYn: apXRa: goto YEdqm; JMWzw: $xa = $this->input->get("\x78\141"); goto tUesz; SBaZq: $kelas = $this->input->get("\153\x65\x6c\141\x73"); goto T2mDe; K8FBv: $rekap->soal_jodohkan = json_decode(json_encode(unserialize($rekap->soal_jodohkan))); goto tGs6e; opdz6: $this->load->model("\x43\x62\164\x5f\x6d\x6f\x64\145\x6c", "\x63\x62\164"); goto hEwct; KDGZo: $data = ["\165\163\x65\162" => $user, "\x6a\165\x64\165\154" => "\x45\153\x73\160\157\x72\x20\x48\141\163\x69\x6c\x20\x53\151\163\167\x61", "\x73\165\x62\152\165\144\x75\x6c" => "\105\x6b\x73\160\157\162\40\x48\141\x73\151\154\40\123\x69\x73\x77\141", "\163\145\164\164\x69\156\x67" => $this->dashboard->getSetting()]; goto Cpsrk; UfpU3: $data["\x6d\x61\160\145\154"] = $rekap->id_mapel; goto UGo9z; u8za_: foreach ($siswas as $siswa) { goto xkJER; VSzyJ: $siswa->soal_jodohkan = json_decode(json_encode(unserialize($siswa->soal_jodohkan))); goto X7nUo; X7nUo: $siswa->soal_isian = json_decode(json_encode(unserialize($siswa->soal_isian))); goto lPOAg; exre3: $siswa->soal_kompleks = json_decode(json_encode(unserialize($siswa->soal_kompleks))); goto VSzyJ; lPOAg: $siswa->soal_essai = json_decode(json_encode(unserialize($siswa->soal_essai))); goto YiQ2N; xkJER: $siswa->jawaban_pg = $this->unserialize_with_key($siswa->jawaban_pg); goto exre3; YiQ2N: kXw1o: goto TWfdL; TWfdL: } goto qF5Za; phyqX: $this->load->view("\137\164\x65\x6d\x70\x6c\x61\x74\145\x73\57\144\x61\x73\150\142\x6f\141\162\x64\x2f\137\x68\x65\141\144\145\x72", $data); goto w8V6I; i77uo: $yb = $this->input->get("\171\142"); goto JMWzw; w8V6I: $this->load->view("\x63\142\164\x2f\x72\x65\x6b\141\x70\x2f\x6f\x6c\141\x68"); goto D4lDO; D4lDO: $this->load->view("\x5f\x74\145\x6d\x70\154\x61\x74\x65\163\57\144\x61\163\150\142\x6f\x61\x72\x64\x2f\x5f\146\157\157\164\145\162"); goto QV263; tGs6e: $rekap->soal_isian = json_decode(json_encode(unserialize($rekap->soal_isian))); goto lr4qs; wQBfn: if (!($rekap != null)) { goto apXRa; } goto yPVco; IY9PK: $user = $this->ion_auth->user()->row(); goto c72td; LHVA2: $data["\x6b\x65\x6c\x61\x73\x5f\163\145\154\145\143\x74\x65\x64"] = $kelas; goto ZIUoz; j2rts: $this->load->view("\155\x65\155\142\x65\x72\163\57\147\165\162\165\x2f\164\x65\155\160\154\141\164\x65\x73\x2f\x68\x65\x61\x64\145\x72", $data); goto bZ0Oe; YEdqm: if ($this->ion_auth->is_admin()) { goto ZrFc0; } goto pT_CT; XIORd: $siswas = $this->cbt->getAllNilaiRekapByJenis($rekap->tp, $rekap->smt, $rekap->kode_jenis, $kelas, "\x30", $jadwal); goto UNDdA; qXuO4: $data["\143\x6f\x6e\x76\145\x72\x74"] = $convert; goto FSRDk; SxfKm: $data["\162\x65\x6b\141\x70"] = $rekap; goto UfpU3; Rts4x: $data["\153\145\x6c\141\x73"] = $kls; goto wQBfn; JYVID: $siswas = $this->cbt->getAllNilaiRekapByJenis($rekap->tp, $rekap->smt, $rekap->kode_jenis, $kelas, "\60", $jadwal, $guru->id_guru); goto u8za_; hfvfZ: $data["\160\162\157\146\151\x6c\145"] = $this->dashboard->getProfileAdmin($user->id); goto Bcm79; FSRDk: Yiciw: goto iJIDx; OJ9PQ: } function unserialize_with_key($serialized) { goto nww6H; IX2bQ: foreach ($arr as $value) { $result[$value["\156\x6f\x5f\163\157\x61\154"]] = $value["\x6a\141\x77\x61\142"]; MdZT6: } goto a646I; JrJro: return $result; goto f34Cy; x_8pp: $result = []; goto IX2bQ; a646I: kNdD5: goto JrJro; nww6H: $arr = unserialize($serialized); goto x_8pp; f34Cy: } public function export() { goto Uzv0x; tk0o5: $user = $this->ion_auth->user()->row(); goto L2_cw; hQ9kf: $this->load->model("\104\141\x73\x68\x62\157\x61\x72\x64\x5f\x6d\157\x64\145\x6c", "\x64\141\163\x68\x62\157\x61\162\144"); goto Xd_7F; ZjLXU: $this->load->view("\x63\x62\164\x2f\x72\x65\x6b\x61\160\57\145\153\x73\x70\157\x72"); goto JXzQt; L2_cw: $data = ["\165\163\x65\x72" => $user, "\x6a\165\x64\x75\154" => "\105\x6b\x73\160\157\x72\x20\x48\141\x73\x69\x6c\x20\x50\x65\x6e\x69\154\x61\x69\x61\x6e", "\x73\165\x62\x6a\165\x64\x75\154" => "\x45\x6b\163\x70\157\162\40\116\x69\154\x61\151", "\x73\x65\164\x74\151\x6e\x67" => $this->dashboard->getSetting()]; goto iBdG6; tMCgW: $this->load->view("\x6d\145\155\x62\x65\x72\x73\x2f\x67\165\162\x75\57\x74\x65\155\160\x6c\141\164\145\x73\57\x68\x65\x61\144\x65\162", $data); goto ZjLXU; V05s4: goto JDwMt; goto r8SFM; bfA1m: $jadwals = $this->cbt->getAllRekap($guru->id_guru); goto I1r2Y; I4xKI: $smt = $this->master->getSemesterActive(); goto AO4hE; i3ooc: if ($this->ion_auth->is_admin()) { goto QtCSD; } goto wncJX; Uzv0x: $this->load->model("\x4d\x61\163\x74\x65\162\137\155\x6f\x64\x65\154", "\155\141\163\x74\x65\x72"); goto hQ9kf; de7fd: $data["\x74\160\x5f\x61\x63\x74\151\166\x65"] = $tp; goto DuM12; iBdG6: $tp = $this->master->getTahunActive(); goto I4xKI; VSGQ6: $this->load->view("\x5f\x74\x65\x6d\160\154\141\164\145\163\57\144\x61\163\150\x62\x6f\141\x72\x64\57\x5f\x66\157\157\x74\145\x72"); goto j3182; Xd_7F: $this->load->model("\x43\x62\x74\137\155\x6f\144\x65\154", "\143\x62\164"); goto tk0o5; j3182: JDwMt: goto koY1W; Vt3U_: $data["\164\x61\150\x75\156\163"] = $this->cbt->getDistinctTahun($this->rekapGuruId()); goto mP1DR; juUZ6: $data["\162\145\153\x61\x70\x73"] = $jadwals; goto k6TyZ; mP1DR: $data["\163\x65\x6d\x65\x73\164\x65\162"] = $this->cbt->getDistinctSmt($this->rekapGuruId()); goto i3ooc; j2K3z: $data["\x6b\x65\x6c\141\163"] = $this->cbt->getDistinctKelas(null, $this->rekapGuruId()); goto Vt3U_; BLcER: $this->load->view("\143\142\x74\57\x72\x65\153\141\160\57\x65\153\163\x70\157\x72"); goto VSGQ6; I1r2Y: foreach ($jadwals as $key => $jadwal) { goto dZ5bB; dZ5bB: $jadwal->bank_kelas = unserialize($jadwal->bank_kelas); goto D2mzI; q_XWk: V_nGL: goto heQRI; D2mzI: $jadwal->nama_kelas = unserialize($jadwal->nama_kelas); goto q_XWk; heQRI: } goto l6oUt; pWV3V: $this->load->view("\x5f\x74\x65\155\x70\x6c\x61\164\x65\x73\57\x64\141\163\150\x62\x6f\141\162\144\x2f\137\150\145\141\144\145\x72", $data); goto BLcER; Nz4aX: foreach ($jadwals as $jadwal) { goto Jjamd; Jjamd: $jadwal->bank_kelas = unserialize($jadwal->bank_kelas); goto XmDm1; F0JqU: Jt9OX: goto nZzbd; XmDm1: $jadwal->nama_kelas = unserialize($jadwal->nama_kelas); goto F0JqU; nZzbd: } goto odrz0; l6oUt: W1rY6: goto rj8md; DuM12: $data["\163\155\164"] = $this->dashboard->getSemester(); goto LltjI; rj8md: $data["\162\145\x6b\141\x70\x73"] = $jadwals; goto nurIV; wGD8P: $jadwals = $this->cbt->getAllRekap(); goto Nz4aX; k6TyZ: $data["\x70\x72\157\146\x69\154\x65"] = $this->dashboard->getProfileAdmin($user->id); goto pWV3V; LltjI: $data["\163\x6d\164\x5f\141\x63\164\151\166\145"] = $smt; goto erCJt; erCJt: $data["\x6a\x65\156\x69\163"] = $this->cbt->getDistinctJenisUjian($this->rekapGuruId()); goto j2K3z; r8SFM: QtCSD: goto wGD8P; odrz0: qU3Cm: goto juUZ6; AO4hE: $data["\x74\x70"] = $this->dashboard->getTahun(); goto de7fd; JXzQt: $this->load->view("\155\145\x6d\x62\145\162\163\x2f\147\165\x72\x75\57\164\x65\x6d\x70\154\141\x74\x65\163\57\146\157\157\164\x65\x72"); goto V05s4; wncJX: $guru = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt); goto bfA1m; nurIV: $data["\147\165\162\165"] = $this->dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt); goto tMCgW; koY1W: } public function generateNilaiUjian($jadwal)
    {
        $this->load->model('Cbt_model', 'cbt');
        return (bool) $this->cbt->getAllNilaiSiswa($jadwal);
    }
    }
