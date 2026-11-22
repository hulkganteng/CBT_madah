<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
 defined("\x42\101\123\x45\x50\x41\124\x48") or exit("\x4e\x6f\x20\144\151\x72\x65\143\164\40\163\x63\162\151\x70\164\x20\141\x63\x63\145\x73\163\x20\x61\154\154\x6f\x77\145\144"); use PhpOffice\PhpSpreadsheet\Spreadsheet; use PhpOffice\PhpSpreadsheet\Writer\Xlsx; use PhpOffice\PhpSpreadsheet\Writer\Xls; use PhpOffice\PhpSpreadsheet\Writer\Csv; use PhpOffice\PhpWord\PhpWord; class Datajurusan extends CI_Controller { public function __construct() { goto ZyJMG; K4SZP: if ($this->ion_auth->is_admin()) { goto cKEe4; } goto hAPKE; dGo1b: $this->form_validation->set_error_delimiters('', ''); goto ztsBf; a7tMc: redirect("\141\x75\x74\150"); goto TjD0C; x3V7C: $this->load->model("\115\x61\x73\x74\145\x72\137\155\x6f\144\x65\x6c", "\155\x61\x73\x74\x65\162"); goto Sjihw; TjD0C: B1A7y: goto mle8t; ZyJMG: parent::__construct(); goto JkurS; VBcmf: $this->load->model("\x44\x72\x6f\x70\144\157\x77\x6e\x5f\155\157\x64\x65\154", "\144\x72\157\160\x64\x6f\167\156"); goto dGo1b; JkurS: if (!$this->ion_auth->logged_in()) { goto zTNdr; } goto K4SZP; wH2OG: cKEe4: goto btnFs; Cjuyf: zTNdr: goto a7tMc; hAPKE: show_error("\110\x61\x6e\x79\x61\x20\x41\144\x6d\x69\x6e\151\163\x74\162\x61\164\x6f\x72\40\171\141\156\x67\x20\x64\x69\142\145\162\151\x20\x68\141\x6b\40\x75\156\164\x75\153\40\155\145\156\x67\141\153\163\x65\163\x20\x68\x61\x6c\141\x6d\141\156\x20\151\156\x69\54\x20\74\x61\40\x68\162\145\x66\75\42" . base_url("\x64\x61\x73\x68\x62\157\x61\x72\144") . "\42\x3e\113\x65\x6d\142\x61\x6c\151\40\153\145\x20\x6d\x65\x6e\165\x20\141\x77\x61\x6c\x3c\x2f\141\76", 403, "\x41\153\163\145\x73\40\124\x65\162\x6c\141\162\141\x6e\147"); goto wH2OG; btnFs: goto B1A7y; goto Cjuyf; Sjihw: $this->load->model("\104\x61\163\x68\x62\x6f\x61\162\x64\137\155\x6f\x64\145\154", "\144\141\163\150\x62\x6f\141\x72\144"); goto VBcmf; mle8t: $this->load->library(["\144\x61\164\141\x74\x61\x62\154\x65\163", "\146\157\162\x6d\x5f\x76\x61\x6c\x69\144\141\x74\x69\157\x6e"]); goto x3V7C; ztsBf: } public function output_json($data, $encode = true) { goto pGVuC; j5W4Q: $this->output->set_content_type("\x61\160\x70\x6c\x69\x63\x61\164\x69\x6f\156\57\152\163\x6f\156")->set_output($data); goto wDAXb; pGVuC: if (!$encode) { goto XJzpP; } goto IKuiO; Pe75s: XJzpP: goto j5W4Q; IKuiO: $data = json_encode($data); goto Pe75s; wDAXb: } public function index() { goto m5bjX; kTuTW: $data["\x74\160\x5f\x61\x63\x74\151\166\x65"] = $this->dashboard->getTahunActive(); goto W3_YH; m5bjX: $user = $this->ion_auth->user()->row(); goto oxxQs; W3_YH: $data["\x73\x6d\x74"] = $this->dashboard->getSemester(); goto N6ssU; Wxpku: $data["\155\141\x70\145\154\137\x70\145\x6d\151\156\x61\x74\x61\156"] = $this->dropdown->getAllMapelPeminatan(); goto sJY2c; pJaiD: $this->load->view("\x6d\141\x73\x74\145\x72\57\x6a\165\x72\x75\x73\141\x6e\x2f\x64\141\x74\x61"); goto OtzsI; oxxQs: $data = ["\x75\x73\x65\162" => $user, "\152\x75\144\x75\x6c" => "\x4a\x75\x72\x75\163\x61\156", "\x73\x75\x62\x6a\165\x64\165\x6c" => "\104\x61\x66\164\141\162\40\x4a\x75\x72\x75\163\x61\x6e", "\160\162\x6f\x66\x69\154\x65" => $this->dashboard->getProfileAdmin($user->id), "\x73\145\164\x74\x69\156\147" => $this->dashboard->getSetting()]; goto Am9YE; vOD2P: $data["\x6a\x75\x72\165\163\141\156\x73"] = $jurusans; goto vPQ7V; mNLDL: $this->load->view("\137\164\x65\155\x70\x6c\x61\164\145\x73\x2f\144\141\163\150\x62\x6f\141\162\x64\x2f\137\x68\x65\141\144\x65\x72", $data); goto pJaiD; Am9YE: $data["\164\160"] = $this->dashboard->getTahun(); goto kTuTW; OtzsI: $this->load->view("\x5f\x74\145\x6d\160\154\x61\x74\145\163\x2f\x64\x61\x73\x68\142\x6f\x61\162\144\x2f\137\x66\x6f\x6f\164\145\162"); goto naUj8; AldKO: uo1wN: goto vOD2P; N6ssU: $data["\x73\155\x74\137\141\x63\x74\151\166\x65"] = $this->dashboard->getSemesterActive(); goto Wxpku; vPQ7V: $data["\x6a\165\162\165\163\x61\x6e\137\x6d\141\x70\145\154\x73"] = $jurusan_mapels; goto mNLDL; sJY2c: $jurusans = $this->master->getDataJurusan(); goto jcgrR; jcgrR: $jurusan_mapels = []; goto IdibS; IdibS: foreach ($jurusans as $jurusan) { $jurusan_mapels[$jurusan->id_jurusan] = $this->master->getDataJurusanMapel(explode("\x2c", $jurusan->mapel_peminatan ?? '')); LGJNR: } goto AldKO; naUj8: } public function add()
    {
        $this->saveMajor();
    }

    private function saveMajor($id = null)
    {
        $nama = trim((string) $this->input->post('nama_jurusan', true));
        $kode = trim((string) $this->input->post('kode_jurusan', true));
        $mapels = $this->input->post('mapel', true) ?? [];
        if ($nama === '' || $kode === '' || mb_strlen($nama) > 30 || mb_strlen($kode) > 10 || !is_array($mapels)) {
            $this->output_json(['status' => false, 'message' => 'Isi nama jurusan (maksimal 30 karakter) dan kode (maksimal 10 karakter).']);
            return;
        }
        $duplicate = $this->db->where('kode_jurusan', $kode);
        if ($id !== null) {
            $duplicate->where('id_jurusan !=', $id);
        }
        if ($duplicate->count_all_results('master_jurusan') > 0) {
            $this->output_json(['status' => false, 'message' => 'Kode jurusan sudah digunakan.']);
            return;
        }

        $data = [
            'nama_jurusan' => $nama,
            'kode_jurusan' => $kode,
            'mapel_peminatan' => implode(',', array_unique(array_filter($mapels, 'ctype_digit'))),
        ];
        $created = $id === null
            ? $this->master->create('master_jurusan', $data)
            : $this->db->where('id_jurusan', $id)->update('master_jurusan', $data);
        $data['status'] = (bool) $created;
        $this->output_json(['status' => $data['status'], 'message' => $created ? 'Jurusan tersimpan.' : 'Gagal menyimpan jurusan.']);
    } public function data() { $this->output_json($this->master->getDataTableJurusan(), false); } public function save() { goto rRNIO; FrW1E: xk39E: goto eWW80; XZ9Di: $data["\x73\x74\x61\164\x75\x73"] = $status; goto XRf0B; h9btJ: if (!($i <= $rows)) { goto gJ3n_; } goto z9NC0; oI9bz: $insert[] = ["\x6e\x61\155\141\137\152\x75\162\165\163\x61\156" => $this->input->post($nama_jurusan, true)]; goto l6n4L; OpzFi: $this->form_validation->set_message("\x72\x65\x71\165\151\162\x65\144", "\173\146\x69\x65\154\x64\175\x20\127\x61\152\x69\142\40\144\x69\x69\163\x69"); goto zAhnv; eWW80: if ($mode == "\141\x64\144") { goto YEOO8; } goto dQyXL; SRanI: if ($mode == "\x61\144\144") { goto nOtDO; } goto EDdgc; xBj7s: goto YIksU; goto B0YRB; ZvRfw: nbn6C: goto tVC4R; ECQuy: nOtDO: goto oI9bz; XCKkG: $mode = $this->input->post("\155\157\x64\x65", true); goto NmHf0; mXTdA: goto MTQPz; goto FrW1E; rRNIO: $rows = count($this->input->post("\156\x61\155\141\137\x6a\x75\x72\x75\x73\141\x6e", true)); goto XCKkG; oy8ON: zZ7zk: goto yveTr; Ygn3s: $this->form_validation->set_rules($nama_jurusan, "\112\x75\x72\165\x73\x61\156", "\162\x65\161\165\151\162\145\x64"); goto OpzFi; tVC4R: $i++; goto xBj7s; B0YRB: gJ3n_: goto VvV5Z; VvV5Z: if ($status) { goto xk39E; } goto BjR0X; EDdgc: if (!($mode == "\x65\144\x69\164")) { goto zZ7zk; } goto rdk2Z; RPbUL: YEOO8: goto jUGW5; hdyiu: KPW9H: goto v7zuR; OVuzp: $data["\165\x70\x64\141\164\145"] = $update; goto ESk7l; NmHf0: $i = 1; goto FBoBD; zAhnv: if ($this->form_validation->run() === FALSE) { goto RyxuY; } goto SRanI; q2Un2: $data["\151\x6e\163\145\162\x74"] = $insert; goto hdyiu; hPy5h: $status = FALSE; goto FT3Pl; z9NC0: $nama_jurusan = "\156\141\155\x61\x5f\152\x75\x72\165\163\x61\156\133" . $i . "\135"; goto Ygn3s; dQyXL: if (!($mode == "\x65\x64\151\x74")) { goto CtfTw; } goto u5CCf; FT3Pl: K78vW: goto ZvRfw; u5CCf: $this->master->update("\155\141\x73\x74\145\162\137\152\x75\162\x75\x73\141\x6e", $update, "\x69\x64\137\152\165\162\165\x73\141\156", null, true); goto OVuzp; zawLo: $error[] = [$nama_jurusan => form_error($nama_jurusan)]; goto hPy5h; YjdUa: goto K78vW; goto hW4if; jUGW5: $this->master->create("\155\141\163\164\145\x72\x5f\152\165\x72\165\163\141\x6e", $insert, true); goto q2Un2; BjR0X: if (!isset($error)) { goto Dvdgd; } goto imY2G; Nzs3k: Dvdgd: goto mXTdA; rdk2Z: $update[] = array("\x69\x64\137\x6a\x75\162\165\x73\141\x6e" => $this->input->post("\151\x64\137\152\x75\162\x75\x73\141\156\x5b" . $i . "\135", true), "\x6e\x61\155\141\x5f\x6a\x75\162\165\x73\141\156" => $this->input->post($nama_jurusan, true)); goto oy8ON; hW4if: RyxuY: goto zawLo; OeGOa: goto KPW9H; goto RPbUL; yveTr: goto DS_Lk; goto ECQuy; v4CqH: $status = TRUE; goto YjdUa; v7zuR: MTQPz: goto XZ9Di; FBoBD: YIksU: goto h9btJ; imY2G: $data["\x65\x72\x72\157\x72\163"] = $error; goto Nzs3k; XRf0B: $this->output_json($data); goto kSShg; l6n4L: DS_Lk: goto v4CqH; ESk7l: CtfTw: goto OeGOa; kSShg: } public function update()
    {
        $id = (int) $this->input->post('id_jurusan', true);
        if (!$id || !$this->db->where('id_jurusan', $id)->count_all_results('master_jurusan')) {
            $this->output_json(['status' => false, 'message' => 'Jurusan tidak ditemukan.']);
            return;
        }
        $this->saveMajor($id);
    } public function delete() {
        $selected = $this->input->post('checked', true);
        if (is_array($selected) && $selected && $this->db->field_exists('peminatan_id', 'master_siswa')
            && $this->db->where_in('peminatan_id', $selected)->count_all_results('master_siswa')) {
            $this->output_json(['status' => false, 'total' => 'Jurusan masih digunakan sebagai peminatan siswa.']);
            return;
        }
        if (is_array($selected) && $selected
            && $this->db->where_in('bank_jurusan_id', $selected)->count_all_results('cbt_bank_soal')) {
            $this->output_json(['status' => false, 'total' => 'Jurusan masih digunakan oleh bank soal.']);
            return;
        }
         goto xzyPZ; fA0B0: $this->output_json(["\x73\x74\141\164\x75\163" => false, "\x74\157\164\141\x6c" => "\x54\151\144\x61\x6b\40\x61\144\141\40\x64\x61\x74\141\x20\x79\x61\156\147\x20\x64\x69\x70\x69\x6c\151\x68\x21"]); goto h7K6j; xzyPZ: $chk = $this->input->post("\143\150\145\143\153\145\144", true); goto UgWwG; aRQlW: $tabless = $this->db->list_tables(); goto YQIOn; ULNcf: $tables = []; goto aRQlW; CPXj3: m2abN: goto aOUGf; yyank: K2I40: goto DnmSI; uLqQr: Ce4pm: goto VctH2; fYdYw: vTBxx: goto hwWhi; YQIOn: foreach ($tabless as $table) { goto KtJr8; Dysdt: foreach ($fields as $field) { goto VhW5c; FAHgm: xK0S2: goto wwoSq; PzB7P: array_push($tables, $table); goto JfFLh; VhW5c: if (!($field->name == "\151\x64\137\152\x75\162\x75\163\x61\x6e" || $field->name == "\152\x75\x72\x75\x73\x61\x6e\x5f\151\144")) { goto oWXY5; } goto PzB7P; JfFLh: oWXY5: goto FAHgm; wwoSq: } goto f0ILD; f0ILD: LJXx6: goto vfx9u; vfx9u: sT6vV: goto xDnUM; KtJr8: $fields = $this->db->field_data($table); goto Dysdt; xDnUM: } goto K6CEm; aOUGf: goto MqJ1k; goto Taq40; PYKsz: $messages = []; goto ULNcf; Ur_3t: $this->output_json(["\163\x74\x61\164\x75\163" => true, "\164\x6f\164\141\x6c" => count($chk)]); goto uLqQr; EJjMw: foreach ($tables as $table) { goto N30sG; cybjo: if (!($num > 0)) { goto uy8E9; } goto ZR7_x; b9Wqt: EGYDF: goto cybjo; CnHSq: goto EGYDF; goto i3n2Q; KN1ZQ: tRRQz: goto zCpQ3; tnHt7: $this->db->where_in("\x6a\165\162\x75\x73\x61\156\x5f\x69\144", $chk); goto dBGX3; zCpQ3: hUasC: goto vxwa5; fMi_K: $this->db->where_in("\151\144\137\x6a\x75\162\x75\x73\x61\x6e", $chk); goto BnSWL; BnSWL: $num = $this->db->count_all_results($table); goto CnHSq; ZR7_x: array_push($messages, $table); goto ZHAmI; N30sG: if (!($table != "\x6d\x61\x73\164\145\x72\x5f\x6a\165\x72\x75\x73\141\156")) { goto tRRQz; } goto Bm0Na; Bm0Na: if ($table == "\x6d\141\x73\x74\145\x72\x5f\x6b\145\x6c\x61\163") { goto k3gUK; } goto fMi_K; dBGX3: $num = $this->db->count_all_results($table); goto b9Wqt; i3n2Q: k3gUK: goto tnHt7; ZHAmI: uy8E9: goto KN1ZQ; vxwa5: } goto yyank; sdM4w: if (!$this->master->delete("\x6d\x61\x73\x74\145\162\x5f\152\165\162\165\x73\x61\156", $chk, "\151\x64\x5f\x6a\x75\162\x75\x73\141\x6e")) { goto Ce4pm; } goto Ur_3t; K6CEm: oVsSW: goto EJjMw; DnmSI: if (count($messages) > 0) { goto vTBxx; } goto sdM4w; VctH2: goto m2abN; goto fYdYw; UgWwG: if (!$chk) { goto z7b3_; } goto PYKsz; Taq40: z7b3_: goto fA0B0; h7K6j: MqJ1k: goto WWVhR; hwWhi: $this->output_json(["\x73\164\x61\x74\x75\163" => false, "\164\x6f\164\141\x6c" => "\104\141\x74\x61\x20\x4a\165\x72\x75\x73\141\x6e\x20\144\151\x67\x75\156\x61\153\141\x6e\40\144\151\40" . count($messages) . "\40\x74\141\142\x65\x6c\72\74\142\x72\76" . implode("\74\142\x72\x3e", $messages)]); goto CPXj3; WWVhR: } public function load_jurusan() { $data = $this->master->getJurusan(); $this->output_json($data); } public function import($import_data = null) { goto X6GJD; pXD7s: $data["\164\160\x5f\x61\143\x74\151\x76\145"] = $this->dashboard->getTahunActive(); goto ESJRp; MO9iZ: $this->load->view("\x6d\x61\163\x74\145\x72\x2f\152\165\x72\x75\x73\141\156\x2f\151\x6d\160\157\162\164"); goto dpB6X; ESJRp: $data["\163\155\x74"] = $this->dashboard->getSemester(); goto hkM2o; CT9rh: fgAgT: goto TPO0y; K6bNm: $data = ["\x75\x73\x65\162" => $user, "\x6a\x75\144\165\x6c" => "\x49\155\x70\x6f\162\x74\x20\x4a\x75\x72\x75\163\141\156", "\x73\x75\142\x6a\x75\144\165\154" => "\x49\155\x70\x6f\162\x74\x20\x4a\165\162\165\163\141\156", "\160\x72\x6f\146\x69\154\x65" => $this->dashboard->getProfileAdmin($user->id), "\x73\145\164\x74\x69\156\x67" => $this->dashboard->getSetting()]; goto ZUUBD; mnuMC: $this->load->view("\137\164\145\155\x70\154\141\x74\x65\x73\x2f\x64\141\x73\x68\142\157\x61\x72\144\57\137\150\145\x61\144\145\x72", $data); goto MO9iZ; hkM2o: $data["\163\x6d\164\x5f\141\x63\x74\151\166\145"] = $this->dashboard->getSemesterActive(); goto mnuMC; ZUUBD: if (!($import_data != null)) { goto fgAgT; } goto k1S0n; TPO0y: $data["\164\160"] = $this->dashboard->getTahun(); goto pXD7s; X6GJD: $user = $this->ion_auth->user()->row(); goto K6bNm; dpB6X: $this->load->view("\137\164\145\x6d\x70\154\x61\x74\x65\x73\x2f\x64\x61\163\150\142\157\x61\x72\144\57\x5f\146\x6f\157\x74\145\162"); goto OJQY5; k1S0n: $data["\151\155\x70\157\162\164"] = $import_data; goto CT9rh; OJQY5: } public function preview()
    {
        $this->load->library('master_data_preview');
        $this->output_json($this->master_data_preview->read(['nama', 'kode'], false));
    } public function previewWord()
    {
        $this->load->library('master_data_preview');
        $this->output_json($this->master_data_preview->read(['nama', 'kode'], true));
    } public function do_import()
    {
        $rows = json_decode((string) $this->input->post('jurusan'), true);
        if (!is_array($rows) || !$rows) {
            $this->output_json(['status' => false, 'message' => 'Tidak ada data jurusan untuk diimpor.']);
            return;
        }
        $knownCodes = [];
        foreach ($this->db->select('kode_jurusan')->get('master_jurusan')->result() as $major) {
            $knownCodes[mb_strtolower(trim((string) $major->kode_jurusan))] = true;
        }
        $data = [];
        foreach (array_values($rows) as $index => $row) {
            $name = trim((string) ($row['nama'] ?? ''));
            $code = trim((string) ($row['kode'] ?? ''));
            $key = mb_strtolower($code);
            if ($name === '' || mb_strlen($name) > 30 || $code === '' || mb_strlen($code) > 10 || isset($knownCodes[$key])) {
                $this->output_json(['status' => false, 'message' => 'Periksa nama, panjang isian, atau kode jurusan duplikat pada baris ' . ($index + 2) . '.']);
                return;
            }
            $knownCodes[$key] = true;
            $data[] = ['nama_jurusan' => $name, 'kode_jurusan' => $code];
        }
        $this->db->trans_begin();
        $saved = $this->db->insert_batch('master_jurusan', $data) === count($data)
            && $this->db->trans_status() && $this->db->trans_commit();
        if (!$saved) {
            $this->db->trans_rollback();
        }
        $this->output_json(['status' => $saved, 'message' => $saved ? 'Data jurusan berhasil diimpor.' : 'Gagal menyimpan data jurusan.']);
    } public function hapusById() { goto BbFwM; C4y50: $this->db->where("\151\144\x5f\x6a\x75\162\x75\163\141\x6e", $id); goto Kjhd1; Kjhd1: return $this->db->delete("\155\141\163\164\x65\162\x5f\x6a\165\162\165\163\141\x6e"); goto WPTH3; BbFwM: $id = $this->input->post("\151\144"); goto C4y50; WPTH3: } function exist($table, $data) { goto MA7jU; kb5cB: sNFvA: goto WSaNj; AlOpx: $count = $query->num_rows(); goto QOk0Y; T6x13: return false; goto kb5cB; TCzet: XxU5b: goto T6x13; o4f2E: goto sNFvA; goto TCzet; MA7jU: $query = $this->db->get_where($table, $data); goto AlOpx; QOk0Y: if ($count === 0) { goto XxU5b; } goto JQxgX; JQxgX: return true; goto o4f2E; WSaNj: } }
