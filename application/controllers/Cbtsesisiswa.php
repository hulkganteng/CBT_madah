<?php
/*   ________________________________________
    |                 GarudaCBT              |
    |    https://github.com/garudacbt/cbt    |
    |________________________________________|
*/
 class Cbtsesisiswa extends CI_Controller { public function __construct() { goto gYO0h; HhCqa: $this->load->library(["\x64\141\164\x61\164\141\142\x6c\x65\163", "\x66\157\x72\x6d\137\x76\x61\x6c\x69\144\141\x74\x69\x6f\156"]); goto FE3fI; WaJ2t: if (!$this->ion_auth->logged_in()) { goto VlezF; } goto Z9glC; QxBrz: iFTcf: goto oZc5W; XljQh: $this->load->model("\x44\x72\157\x70\x64\157\x77\156\x5f\x6d\x6f\x64\x65\x6c", "\144\x72\157\x70\144\x6f\x77\156"); goto W3If0; FE3fI: $this->load->model("\x4d\141\163\x74\145\162\137\155\x6f\x64\145\154", "\155\x61\x73\164\x65\162"); goto agJdk; Jcijy: $this->load->model("\x43\142\164\x5f\155\x6f\144\x65\154", "\x63\142\x74"); goto XljQh; Z9glC: if ($this->ion_auth->is_admin()) { goto iFTcf; } goto hH_z5; d9DTX: redirect("\x61\165\164\150"); goto WUNYR; agJdk: $this->load->model("\104\141\x73\150\x62\157\x61\x72\x64\x5f\x6d\x6f\x64\145\x6c", "\x64\141\x73\150\x62\157\141\162\x64"); goto Jcijy; hH_z5: show_error("\110\x61\x6e\x79\x61\40\x41\144\155\151\156\151\163\x74\x72\x61\x74\x6f\162\40\171\141\156\147\40\x64\x69\142\x65\x72\151\40\x68\141\153\x20\165\156\164\x75\x6b\x20\x6d\145\x6e\x67\141\x6b\163\145\x73\40\x68\x61\x6c\141\x6d\141\x6e\40\151\x6e\151\x2c\40\74\x61\x20\150\162\x65\x66\x3d\x22" . base_url("\144\x61\163\150\x62\x6f\141\162\x64") . "\42\76\x4b\145\155\142\x61\154\x69\x20\x6b\x65\x20\155\x65\156\165\x20\141\167\141\x6c\x3c\x2f\141\x3e", 403, "\101\153\163\145\x73\x20\x54\145\x72\154\141\162\x61\156\147"); goto QxBrz; RejK9: $this->form_validation->set_error_delimiters('', ''); goto N39fg; oZc5W: goto eOwgb; goto WvDm1; WvDm1: VlezF: goto d9DTX; gYO0h: parent::__construct(); goto WaJ2t; WUNYR: eOwgb: goto HhCqa; W3If0: $this->load->model("\x4b\x65\154\141\x73\x5f\x6d\x6f\x64\x65\154", "\153\x65\x6c\141\163"); goto RejK9; N39fg: } public function output_json($data, $encode = true) { goto aO6_u; aO6_u: if (!$encode) { goto FtXA5; } goto CNSqV; NEk2J: FtXA5: goto deD_Q; CNSqV: $data = json_encode($data); goto NEk2J; deD_Q: $this->output->set_content_type("\x61\160\160\x6c\x69\x63\x61\164\151\x6f\156\57\152\163\x6f\156")->set_output($data); goto l0FAL; l0FAL: } public function index() { goto UcVw3; uZZUe: $siswas = []; goto EAu0G; psmqe: $siswas = $this->cbt->getRuangSesiSiswa($kls, $tp->id_tp, $smt->id_smt); goto cUSsA; PpK1e: $data["\163\x69\x73\x77\x61\x73"] = $siswas; goto c8gNc; UcVw3: $tp = $this->dashboard->getTahunActive(); goto Mq_fP; EAu0G: if (!($kelas_selected != "\60")) { goto WlgNk; } goto psmqe; Mq_fP: $smt = $this->dashboard->getSemesterActive(); goto Pz2ZB; Di6GJ: $data = ["\x75\163\x65\x72" => $user, "\152\x75\x64\165\x6c" => "\x41\x74\x75\162\40\122\x75\141\156\147\40\144\x61\x6e\40\x53\x65\x73\x69\40\x53\x69\x73\167\x61", "\x73\165\142\x6a\165\x64\x75\x6c" => "\122\x75\141\156\x67\40\x64\x61\156\40\x53\145\163\x69\40\x53\151\163\167\x61", "\163\x65\x74\164\x69\x6e\x67" => $this->dashboard->getSetting(), "\153\145\x6c\141\x73" => $this->dropdown->getAllKelas($tp->id_tp, $smt->id_smt), "\x72\x75\x61\x6e\x67\x5f\153\x65\x6c\x61\x73" => $this->cbt->getKelasList($tp->id_tp, $smt->id_smt), "\x73\x65\x73\151" => $this->dropdown->getAllSesi(), "\x72\165\141\156\147" => $this->cbt->getAllRuang(), "\x74\160" => $this->dashboard->getTahun(), "\164\x70\x5f\x61\143\164\x69\166\145" => $tp, "\x73\x6d\x74" => $this->dashboard->getSemester(), "\x73\x6d\x74\x5f\x61\143\164\x69\166\x65" => $smt, "\x70\162\x6f\146\151\154\x65" => $this->dashboard->getProfileAdmin($user->id)]; goto JO4pl; toqYH: $this->load->view("\x5f\164\145\155\160\154\141\164\145\x73\x2f\144\141\163\150\142\x6f\x61\162\144\x2f\137\x66\x6f\157\x74\x65\x72"); goto W0VFd; cUSsA: WlgNk: goto PpK1e; c8gNc: $data["\x6b\145\154\141\x73\x5f\163\x65\154\x65\x63\x74\145\x64"] = $kelas_selected; goto M0v1o; cXVn2: $this->load->view("\143\x62\x74\57\x73\x65\163\x69\163\151\x73\167\141\57\144\x61\x74\141"); goto toqYH; l_TrV: $kelas_selected = $kls != null ? $kls : "\x30"; goto uZZUe; Pz2ZB: $user = $this->ion_auth->user()->row(); goto Di6GJ; JO4pl: $kls = $this->input->get("\x6b\x6c\163", true); goto l_TrV; M0v1o: $this->load->view("\137\x74\145\155\160\x6c\141\164\145\x73\x2f\x64\141\163\150\142\157\141\x72\x64\x2f\137\150\x65\141\x64\x65\x72", $data); goto cXVn2; W0VFd: } public function getAllRuang() { $this->output_json($this->cbt->getAllRuang()); } public function getAllSesi() { $this->output_json($this->dropdown->getAllSesi()); } public function add()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->saveExamMaster('sesi', FALSE));
    }

    public function update()
    {
        $this->load->model('Cbt_model', 'cbt');
        $this->output_json($this->cbt->saveExamMaster('sesi', TRUE));
    }

    public function delete()
    {
        $this->load->model('Cbt_model', 'cbt');
        $raw = $this->input->post('checked');
        $ids = is_array($raw) ? array_values(array_unique(array_filter(array_map('intval', $raw)))) : [];
        $message = $this->cbt->deleteExamMaster('sesi', $ids);
        $this->output_json(['status' => $message === NULL, 'total' => $message === NULL ? count($ids) : 0, 'message' => $message]);
    }

    public function editsesisiswa()
    {
        $input = $this->input->post('ruang-sesi');
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $rows = [];
        if (is_array($input) && $tp && $smt) {
            foreach ($input as $student => $classes) {
                if (!is_array($classes) || count($classes) !== 1) {
                    $rows = [];
                    break;
                }
                foreach ($classes as $class => $assignment) {
                    if (!is_array($assignment) || !isset($assignment['ruang'], $assignment['sesi'])
                        || !$this->db->where(['id_siswa' => (int) $student, 'id_kelas' => (int) $class,
                            'id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt])->count_all_results('kelas_siswa')) {
                        $rows = [];
                        break 2;
                    }
                    $rows[] = ['siswa_id' => (int) $student, 'kelas_id' => (int) $class, 'tp_id' => $tp->id_tp,
                        'smt_id' => $smt->id_smt, 'ruang_id' => (int) $assignment['ruang'], 'sesi_id' => (int) $assignment['sesi']];
                }
            }
        }
        $saved = $rows && $this->validAssignments(array_column($rows, 'ruang_id'), array_column($rows, 'sesi_id'))
            && $this->cbt->replaceExamRows(['cbt_sesi_siswa' => $rows]);
        $this->output_json(['status' => (bool) $saved, 'message' => $saved ? '' : 'Penempatan ruang atau sesi tidak valid.']);
    }

    public function editsesikelas()
    {
        $input = json_decode((string) $this->input->post('kelas_sesi'));
        $tp = $this->dashboard->getTahunActive();
        $smt = $this->dashboard->getSemesterActive();
        $classes = [];
        $students = [];
        if (is_array($input) && $tp && $smt) {
            foreach ($input as $row) {
                $class = (int) ($row->kelas_id ?? 0);
                if ($class < 1 || isset($classes[$class]) || !isset($row->ruang_id, $row->sesi_id)) {
                    $classes = [];
                    break;
                }
                $room = (int) $row->ruang_id;
                $session = (int) $row->sesi_id;
                $classes[$class] = ['id_kelas_ruang' => $class . $tp->id_tp . $smt->id_smt, 'id_kelas' => $class,
                    'id_ruang' => $room, 'id_sesi' => $session, 'id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt,
                    'set_siswa' => (int) ($row->set_siswa ?? 0)];
                foreach ($this->kelas->getKelasSiswa($class, $tp->id_tp, $smt->id_smt) as $student) {
                    $students[] = ['siswa_id' => $student->id_siswa, 'kelas_id' => $class, 'ruang_id' => $room,
                        'sesi_id' => $session, 'tp_id' => $tp->id_tp, 'smt_id' => $smt->id_smt];
                }
            }
        }
        $saved = $classes && $this->cbt->hasExamIds('master_kelas', 'id_kelas', array_keys($classes),
                ['id_tp' => $tp->id_tp, 'id_smt' => $smt->id_smt])
            && $this->validAssignments(array_column($classes, 'id_ruang'), array_column($classes, 'id_sesi'))
            && $this->cbt->replaceExamRows(['cbt_sesi_siswa' => $students, 'cbt_kelas_ruang' => $classes]);
        $this->output_json(['status' => (bool) $saved, 'message' => $saved ? '' : 'Penempatan kelas tidak valid.']);
    }

    private function validAssignments(array $rooms, array $sessions)
    {
        return $this->cbt->hasExamIds('cbt_ruang', 'id_ruang', array_filter($rooms))
            && $this->cbt->hasExamIds('cbt_sesi', 'id_sesi', array_filter($sessions));
    }
    }
