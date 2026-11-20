<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
 class Cbtjenis extends CI_Controller { public function __construct() { goto dQ7YV; cSy1L: if ($this->ion_auth->is_admin()) { goto anduE; } goto V3Apj; V3Apj: show_error("\x48\x61\x6e\x79\141\40\101\x64\x6d\x69\156\x69\163\164\162\141\x74\157\162\x20\x79\141\x6e\x67\40\x64\x69\x62\x65\x72\x69\40\150\141\153\x20\x75\156\164\165\x6b\40\155\145\156\147\141\x6b\x73\x65\163\40\150\x61\154\141\x6d\141\156\40\x69\156\151\54\x20\x3c\141\x20\150\x72\x65\146\x3d\42" . base_url("\x64\x61\x73\x68\142\x6f\141\162\144") . "\42\76\x4b\x65\x6d\x62\141\154\151\40\153\x65\40\x6d\x65\156\x75\40\x61\x77\x61\x6c\x3c\57\x61\x3e", 403, "\x41\x6b\163\145\163\40\x54\145\x72\154\141\162\141\x6e\x67"); goto A4Jaz; LTmCd: $this->form_validation->set_error_delimiters('', ''); goto nou6H; oWvVa: if (!$this->ion_auth->logged_in()) { goto XLvzX; } goto cSy1L; Zd0j8: goto UC9UK; goto RSPRe; dQ7YV: parent::__construct(); goto oWvVa; A4Jaz: anduE: goto Zd0j8; W3pJY: redirect("\x61\x75\x74\x68"); goto c59u8; c59u8: UC9UK: goto KRoiL; KRoiL: $this->load->library(["\144\141\164\x61\x74\141\142\154\x65\163", "\146\157\162\x6d\137\x76\141\x6c\x69\144\141\164\151\x6f\x6e"]); goto LTmCd; RSPRe: XLvzX: goto W3pJY; nou6H: } public function output_json($data, $encode = true) { goto D651M; a7hVh: $data = json_encode($data); goto qsEN0; D651M: if (!$encode) { goto uoxiz; } goto a7hVh; WMC6D: $this->output->set_content_type("\141\x70\160\154\151\143\x61\164\x69\157\156\x2f\x6a\163\x6f\x6e")->set_output($data); goto TXheG; qsEN0: uoxiz: goto WMC6D; TXheG: } public function index() { goto SvXT8; Hufjh: $data = ["\x75\163\x65\x72" => $user, "\x6a\165\x64\x75\x6c" => "\112\145\x6e\x69\163\x20\125\x6a\151\x61\x6e", "\163\x75\x62\x6a\165\x64\165\x6c" => "\x44\141\164\x61\40\112\145\x6e\151\163\40\125\152\x69\141\x6e", "\x70\x72\x6f\x66\151\x6c\x65" => $this->dashboard->getProfileAdmin($user->id), "\x73\145\x74\164\x69\156\147" => $this->dashboard->getSetting()]; goto ZzdSF; Ln8hx: $data["\163\x6d\x74"] = $this->dashboard->getSemester(); goto ATF_m; edv91: $user = $this->ion_auth->user()->row(); goto Hufjh; ATF_m: $data["\163\155\x74\137\x61\143\164\151\x76\x65"] = $this->dashboard->getSemesterActive(); goto q4xJ4; ZzdSF: $data["\164\160"] = $this->dashboard->getTahun(); goto uXPQu; il8tR: $this->load->view("\137\164\x65\x6d\x70\154\141\x74\145\x73\57\x64\141\x73\x68\x62\157\x61\x72\144\57\137\146\157\157\x74\x65\162"); goto W6H9s; uXPQu: $data["\x74\160\x5f\x61\x63\164\x69\166\x65"] = $this->dashboard->getTahunActive(); goto Ln8hx; SvXT8: $this->load->model("\104\141\163\x68\142\x6f\x61\162\144\x5f\x6d\x6f\144\x65\x6c", "\x64\x61\x73\x68\142\157\141\x72\x64"); goto edv91; q4xJ4: $this->load->view("\137\164\145\155\x70\154\141\x74\x65\163\x2f\144\141\163\x68\x62\x6f\x61\x72\x64\57\137\x68\145\x61\144\x65\162", $data); goto CClZh; CClZh: $this->load->view("\x63\x62\164\x2f\x6a\x65\x6e\x69\x73\x2f\144\x61\x74\141"); goto il8tR; W6H9s: } public function data() { $this->load->model("\x43\142\x74\137\155\x6f\144\145\x6c", "\143\142\164"); $this->output_json($this->cbt->getJenis(), false); } public function add()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->saveExamMaster('jenis', FALSE));
    }

    public function update()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->saveExamMaster('jenis', TRUE));
    }

    public function delete()
    {
        $this->load->model('Cbt_model', 'cbt');
        $raw = $this->input->post('checked');
        $ids = is_array($raw) ? array_values(array_unique(array_filter(array_map('intval', $raw)))) : [];
        $message = $this->cbt->deleteExamMaster('jenis', $ids);
        $this->output_json(['status' => $message === NULL, 'total' => $message === NULL ? count($ids) : 0, 'message' => $message]);
    }

    public function saveLog($type, $desc) { goto YmyUQ; exWXB: $user = $this->ion_auth->user()->row(); goto Nfyrv; YmyUQ: $this->load->model("\x4c\x6f\147\137\155\x6f\x64\x65\154", "\154\157\147\147\x69\156\x67"); goto exWXB; Nfyrv: $this->logging->saveLog($type, $desc); goto g1JWi; g1JWi: } }
