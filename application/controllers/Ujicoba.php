<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ujicoba extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!$this->ion_auth->logged_in()) {
            redirect('auth');
        }

        if ($this->ion_auth->is_admin() || $this->ion_auth->in_group('guru')) {
            show_error('Halaman ini hanya untuk siswa.', 403, 'Akses Terlarang');
        }

        $this->load->model('Dashboard_model', 'dashboard');
        $this->load->model('Cbt_model', 'cbt');
        $this->load->model('Ujicoba_model', 'ujicoba');
        $this->load->helper('url');
    }

    public function output_json($data, $encode = true)
    {
        if ($encode) {
            $data = json_encode($data);
        }
        $this->output->set_content_type('application/json')->set_output($data);
    }

    private function getDataSiswa()
    {
        $user = $this->ion_auth->user()->row();
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $siswa = $this->cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        return [$user, $tp, $smt, $siswa];
    }

    private function baseData($judul, $user, $tp, $smt, $siswa)
    {
        return [
            'user' => $user,
            'siswa' => $siswa,
            'judul' => $judul,
            'setting' => $this->dashboard->getSetting(),
            'tp' => $this->dashboard->getTahun(),
            'tp_active' => $tp,
            'smt_active' => $smt,
            'running_text' => $this->dashboard->getRunningText(),
        ];
    }

    public function index()
    {
        list($user, $tp, $smt, $siswa) = $this->getDataSiswa();

        $data = $this->baseData('Simulasi / Ujicoba', $user, $tp, $smt, $siswa);
        $data['banks'] = $this->ujicoba->getBankList($tp->id_tp, $smt->id_smt, $siswa->id_kelas);

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/ujicoba/index', $data);
        $this->load->view('members/siswa/templates/footer');
    }

    public function kerjakan($id_bank)
    {
        list($user, $tp, $smt, $siswa) = $this->getDataSiswa();

        $bank = $this->ujicoba->getBank($id_bank);
        if (!$bank) {
            $this->session->set_flashdata('err', 'Bank soal tidak ditemukan.');
            redirect('ujicoba');
        }

        $data = $this->baseData('Simulasi - ' . ($bank->nama_mapel ?? 'Latihan'), $user, $tp, $smt, $siswa);
        $data['bank'] = $bank;

        $this->load->view('members/siswa/templates/header', $data);
        $this->load->view('members/siswa/ujicoba/kerjakan', $data);
        $this->load->view('members/siswa/templates/footer');
    }

    public function loadsoal()
    {
        $bank = $this->input->post('bank', true);
        $nomor = (int)$this->input->post('nomor', true);

        $soals = $this->ujicoba->getSoalList($bank);
        if (empty($soals)) {
            $this->output_json(['error' => 'empty']);
            return;
        }

        if ($nomor < 1) $nomor = 1;
        if ($nomor > count($soals)) {
            $this->output_json(['error' => 'habis']);
            return;
        }

        $idx = $nomor - 1;
        $row = $soals[$idx];
        $soal = $this->ujicoba->getSoalById($row->id_soal);
        $bankData = $this->ujicoba->getBank($bank);

        $this->output_json([
            'nomor' => $idx + 1,
            'total' => count($soals),
            'soal_id' => $soal->id_soal,
            'jenis' => $soal->jenis,
            'soal' => $soal->soal,
            'konten_jawaban' => $this->renderKonten($soal, $bankData),
        ]);
    }

    private function renderKonten($soal, $bank)
    {
        $jenis = (int)$soal->jenis;
        $html = '';

        if ($jenis === 1) {
            $abjad = ['a', 'b', 'c', 'd', 'e'];
            $jumlah = isset($bank->opsi) && (int)$bank->opsi > 0 ? (int)$bank->opsi : 4;
            for ($i = 0; $i < $jumlah; $i++) {
                $key = $abjad[$i];
                $val = $soal->{'opsi_' . $key};
                if ($val === null || trim($val) === '') continue;
                $label = strtoupper($key);
                $html .= '<label class="container-jawaban font-weight-normal">' . $val .
                    '<input type="radio" name="jawaban" value="' . $label . '" data-soal="' . $soal->id_soal . '">' .
                    '<span class="checkmark shadow text-center align-middle">' . $label . '</span></label>';
            }
        } elseif ($jenis === 2) {
            $opsis = @unserialize($soal->opsi_a);
            if (is_array($opsis)) {
                foreach ($opsis as $abjad => $val) {
                    if (trim($val) === '') continue;
                    $label = strtoupper($abjad);
                    $html .= '<label class="container-jawaban font-weight-normal">' . $val .
                        '<input type="checkbox" name="jawaban[]" value="' . $label . '" data-soal="' . $soal->id_soal . '">' .
                        '<span class="boxmark"></span></label>';
                }
            }
        } elseif ($jenis === 4) {
            $html .= '<div class="pr-4"><label>JAWABAN:</label><br>' .
                '<input id="jawaban-essai" class="pl-1 form-control" type="text" name="jawaban" ' .
                'data-soal="' . $soal->id_soal . '" placeholder="Tulis jawaban disini"/></div>';
        } elseif ($jenis === 5) {
            $html .= '<div class="pr-4"><label>JAWABAN:</label><br>' .
                '<textarea id="jawaban-essai" class="w-100 pl-1 form-control" name="jawaban" rows="4" ' .
                'data-soal="' . $soal->id_soal . '" placeholder="Tulis jawaban disini"></textarea></div>';
        } else {
            $html .= '<div class="alert alert-default-warning">' .
                'Soal menjodohkan tidak termasuk penilaian otomatis pada latihan ini.</div>';
        }

        return $html;
    }

    public function selesai()
    {
        $bank = $this->input->post('bank', true);
        $answersRaw = $this->input->post('answers', true);

        $answers = [];
        if ($answersRaw) {
            $decoded = json_decode($answersRaw, true);
            if (is_array($decoded)) {
                $answers = $decoded;
            }
        }

        $hasil = $this->ujicoba->hitungNilai($bank, $answers);
        $this->output_json(['status' => true] + $hasil);
    }
}
