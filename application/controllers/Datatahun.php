<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
 defined("\102\101\123\105\x50\x41\124\110") or exit("\x4e\157\40\144\x69\162\x65\x63\164\x20\163\x63\x72\151\160\x74\40\141\x63\143\x65\x73\x73\x20\141\x6c\154\157\x77\x65\x64"); class Datatahun extends CI_Controller { public function __construct() { goto R2tV7; ueDjH: iNDt0: goto tyU8k; HZjR6: $this->load->model("\114\157\147\x5f\x6d\x6f\x64\145\x6c", "\x6c\x6f\147\147\x69\156\147"); goto r1H7d; tyU8k: $this->load->library(["\x64\141\164\x61\164\141\142\154\x65\x73", "\x66\x6f\162\x6d\137\x76\x61\154\151\x64\141\x74\151\x6f\x6e"]); goto w0BW6; fwD1Q: show_error("\110\141\156\x79\x61\x20\101\x64\x6d\x69\x6e\x69\x73\164\162\141\x74\157\162\x20\x79\x61\x6e\147\40\144\151\142\x65\x72\151\x20\150\141\153\40\x75\x6e\x74\x75\x6b\x20\x6d\x65\x6e\147\141\x6b\x73\145\163\40\x68\x61\154\141\155\x61\x6e\40\151\x6e\151\54\40\x3c\x61\40\150\162\145\x66\75\42" . base_url("\x64\141\x73\150\x62\157\141\162\x64") . "\42\x3e\x4b\145\155\x62\x61\x6c\x69\x20\x6b\x65\x20\x6d\145\x6e\x75\x20\x61\x77\x61\x6c\74\x2f\141\x3e", 403, "\101\153\x73\x65\x73\x20\x54\x65\162\154\x61\x72\x61\x6e\147"); goto ZtJq4; clAzh: if ($this->ion_auth->is_admin()) { goto A9Ouk; } goto fwD1Q; R2tV7: parent::__construct(); goto rLkw5; r1H7d: $this->form_validation->set_error_delimiters('', ''); goto R16mp; lrGwJ: goto iNDt0; goto o1DC_; LXY6S: redirect("\x61\x75\164\150"); goto ueDjH; sTnza: $this->load->model("\x44\x61\163\x68\x62\x6f\141\x72\144\x5f\155\x6f\144\x65\x6c", "\x64\141\163\x68\x62\x6f\x61\x72\x64"); goto HZjR6; rLkw5: if (!$this->ion_auth->logged_in()) { goto VUNcT; } goto clAzh; w0BW6: $this->load->model("\x4d\141\x73\x74\145\162\x5f\155\157\144\x65\154", "\155\x61\163\x74\x65\x72"); goto sTnza; o1DC_: VUNcT: goto LXY6S; ZtJq4: A9Ouk: goto lrGwJ; R16mp: } public function output_json($data, $encode = true) { goto uRonG; uRonG: if (!$encode) { goto YJ2A7; } goto DFaDw; DFaDw: $data = json_encode($data); goto l2YkS; l2YkS: YJ2A7: goto ZQnnC; ZQnnC: $this->output->set_content_type("\x61\160\x70\x6c\151\x63\141\164\x69\157\156\x2f\x6a\163\157\x6e")->set_output($data); goto xq4Xi; xq4Xi: } public function index() { goto Gw3uc; bie4v: $data = ["\165\x73\x65\x72" => $user, "\152\x75\x64\165\154" => "\124\x61\x68\x75\x6e\x20\x50\x65\x6c\141\x6a\x61\x72\141\x6e\40\144\x61\x6e\40\x53\x65\155\145\x73\x74\145\162", "\x73\x75\x62\152\165\144\165\154" => "\101\x74\x75\x72\40\x54\141\x68\165\x6e\x20\120\145\154\x61\152\x61\x72\141\156\40\x64\141\156\x20\x53\145\155\145\163\164\x65\x72", "\x70\162\157\x66\x69\154\x65" => $this->dashboard->getProfileAdmin($user->id), "\x73\145\x74\164\x69\156\147" => $this->dashboard->getSetting()]; goto y01bU; zVwGT: $data["\x73\155\x74"] = $this->dashboard->getSemester(); goto T6rmS; HOl2D: $data["\152\155\x6c\137\150\141\x72\151"] = $jml == null ? "\60" : $jml->jml_hari_efektif; goto mYntJ; Ah2YM: $data["\164\x70\x5f\x61\x63\164\x69\166\145"] = $tp; goto zVwGT; Gw3uc: $user = $this->ion_auth->user()->row(); goto bie4v; T6rmS: $data["\x73\x6d\164\137\141\x63\164\151\166\145"] = $smt; goto K0iCZ; PoS34: $this->load->view("\x5f\x74\145\155\x70\154\141\164\145\x73\57\x64\x61\x73\x68\x62\x6f\141\162\x64\x2f\x5f\146\x6f\x6f\164\x65\162"); goto UTR03; f2cZA: $this->load->view("\155\x61\163\164\x65\162\57\164\x61\150\165\156\x2f\144\141\164\x61"); goto PoS34; K0iCZ: $jml = $this->master->getJmlHariEfektif($tp->id_tp . $smt->id_smt); goto HOl2D; EYT54: $data["\164\160"] = $this->dashboard->getTahun(); goto Ah2YM; mYntJ: $this->load->view("\x5f\x74\145\155\160\x6c\141\164\145\x73\x2f\144\141\163\x68\x62\157\141\x72\144\57\x5f\150\145\x61\x64\x65\162", $data); goto f2cZA; y01bU: $tp = $this->dashboard->getTahunActive(); goto rTt6c; rTt6c: $smt = $this->dashboard->getSemesterActive(); goto EYT54; UTR03: } public function data() { $this->output_json($this->dashboard->getDataTahun(), false); } public function gantiTahun()
    {
        $this->activatePeriod('master_tp', 'id_tp', 'tahun pelajaran');
    } public function gantiSemester()
    {
        $this->activatePeriod('master_smt', 'id_smt', 'semester');
    }

    private function activatePeriod($table, $key, $label)
    {
        $id = filter_var($this->input->post('active'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id || !$this->db->where($key, $id)->count_all_results($table)) {
            $this->output_json(['status' => false, 'msg' => ucfirst($label) . ' tidak ditemukan.']);
            return;
        }

        $saved = $this->db->set('active', $key . ' = ' . $id, false)->update($table);
        if ($saved) {
            $this->logging->saveLog(4, 'mengganti ' . $label . ' aktif');
        }
        $this->output_json([
            'status' => (bool) $saved,
            'msg' => $saved ? ucfirst($label) . ' aktif berhasil diubah.' : 'Gagal mengubah ' . $label . ' aktif.',
        ]);
    } public function add()
    {
        $method = $this->input->post('method', true);
        $tahun = trim((string) $this->input->post('tahun', true));
        $id = (int) $this->input->post('id_tahun', true);
        if (!in_array($method, ['add', 'edit'], true) || !preg_match('/^\d{4}\/\d{4}$/D', $tahun)) {
            $this->output_json(['status' => false, 'message' => 'Format tahun pelajaran harus YYYY/YYYY.']);
            return;
        }

        if ($method === 'edit' && !$this->db->where('id_tp', $id)->count_all_results('master_tp')) {
            $this->output_json(['status' => false, 'message' => 'Tahun pelajaran tidak ditemukan.']);
            return;
        }
        $duplicate = $this->db->where('tahun', $tahun);
        if ($method === 'edit') {
            $duplicate->where('id_tp !=', $id);
        }
        if ($duplicate->count_all_results('master_tp') > 0) {
            $this->output_json(['status' => false, 'message' => 'Tahun pelajaran sudah tersedia.']);
            return;
        }

        $saved = $method === 'edit'
            ? $this->db->where('id_tp', $id)->update('master_tp', ['tahun' => $tahun])
            : $this->db->insert('master_tp', ['tahun' => $tahun, 'active' => 0]);
        if ($saved) {
            $this->logging->saveLog($method === 'edit' ? 4 : 3, $method === 'edit' ? 'mengedit tahun pelajaran' : 'menambah tahun pelajaran');
        }
        $this->output_json(['status' => (bool) $saved, 'message' => $saved ? 'Tahun pelajaran tersimpan.' : 'Gagal menyimpan tahun pelajaran.']);
    } public function saveHariEfektif()
    {
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $days = filter_var($this->input->post('jml_hari'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if (!$tp || !$smt || $days === false) {
            $this->output_json(['status' => false, 'message' => 'Pilih tahun dan semester aktif, lalu isi jumlah hari efektif yang valid.']);
            return;
        }
        $saved = $this->db->replace('master_hari_efektif', [
            'id_hari_efektif' => $tp->id_tp . $smt->id_smt,
            'jml_hari_efektif' => $days,
        ]);
        $this->output_json(['status' => (bool) $saved, 'message' => $saved ? '' : 'Gagal menyimpan jumlah hari efektif.']);
    } public function hapusTahun()
    {
        $id = (int) $this->input->post('hapus', true);
        $saved = $id > 0 && $this->db->where('id_tp', $id)->where('active', 0)->delete('master_tp')
            && $this->db->affected_rows() === 1;
        if ($saved) {
            $this->logging->saveLog(5, 'menghapus tahun pelajaran');
        }
        $this->output_json([
            'status' => $saved,
            'msg' => $saved ? 'Tahun pelajaran berhasil dihapus.' : 'Tahun pelajaran aktif atau tidak ditemukan.',
        ]);
    } public function hapus()
    {
        $ids = $this->input->post('checked', true);
        if (!is_array($ids) || !$ids || $this->db->where_in('id_tp', $ids)->where('active', 1)->count_all_results('master_tp')) {
            $this->output_json(['status' => false, 'total' => 0]);
            return;
        }
        $saved = $this->db->where_in('id_tp', $ids)->where('active', 0)->delete('master_tp');
        $total = $saved ? $this->db->affected_rows() : 0;
        if ($total > 0) {
            $this->logging->saveLog(5, 'menghapus tahun pelajaran');
        }
        $this->output_json(['status' => $total > 0, 'total' => $total]);
    } }
