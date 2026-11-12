<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_security_hook
{
    private $ci;
    private $service;

    public function beforeController()
    {
        $this->ci =& get_instance();
        $controller = strtolower($this->ci->router->fetch_class());
        $method = strtolower($this->ci->router->fetch_method());

        /* Restore database menahan transaksi ujian selama proses berjalan.
           Marker dibuat dan dihapus oleh Db_restore. */
        if ($this->maintenanceActive()
            && in_array($controller, array('siswa', 'examguard', 'examactivity'), TRUE)) {
            $this->deny('Pemulihan database sedang berjalan. Ujian ditahan sementara. Silakan coba lagi.', 503);
        }

        $this->denyRetiredModules($controller);
        $this->guardRoleAccess($controller, $method);
        $this->guardSebStudentPortal($controller, $method);
        $this->guardLoginPortal($controller, $method);
        $this->guardBankAccess($controller, $method);
        $this->guardScheduleAccess($controller, $method);
        $this->guardFileDeletion($controller, $method);

        if ($controller === 'cbtjadwal' && $method === 'savejadwal') {
            $this->validateScheduleInput();
            $this->validateSebSettings();
            return;
        }
        if ($controller === 'cbtbanksoal' && $method === 'savebank') {
            $this->validateBankInput();
            return;
        }
        if ($controller === 'siswa') {
            $this->denyRetiredStudentPages($method);
        }
        if ($controller === 'dbmanager' && $method === 'truncate') {
            /* Truncate seluruh tabel lewat GET tanpa konfirmasi. Pemulihan
               database sudah ditangani dbpemulihan. */
            $this->deny('Pembersihan massal data dinonaktifkan. Gunakan Backup & Pemulihan Database.', 403);
        }
        if ($controller === 'cbtbanksoal' && $method === 'uploadsoalimage') {
            $this->guardSoalImageUpload();
        }
        if ($controller === 'examactivity') {
            /* Pemantauan aktivitas memakai jadwal semua guru dan aksi proktor
               global. Halaman ini hanya untuk Administrator. */
            $this->requireAdmin('Hanya Administrator yang dapat memantau aktivitas ujian.');
        }
        if ($controller === 'cbtcetak' && in_array($method, array('savekartu', 'savekop', 'savekopberita', 'uploadfile', 'deletefile'), TRUE)) {
            /* Kop kartu, kop absensi, dan logo dipakai seluruh sekolah
               (satu baris setting), jadi bukan milik guru perorangan. */
            $this->requireAdmin('Hanya Administrator yang dapat mengubah kop dan logo cetak.');
        }
        if ($controller === 'update' && !$this->ci->input->is_cli_request()) {
            /* /update menjalankan dbforge (create/edit tabel) tanpa login sama
               sekali. Wajib administrator. Jalur CLI tetap bebas untuk operator. */
            $this->requireAdmin('Hanya Administrator yang dapat menjalankan pembaruan database.');
        }
        if ($controller === 'install') {
            /* Install::index memantulkan ke /update setelah terpasang, tetapi
               createAdmin/createDb/createApp tetap terbuka: siapa pun bisa
               mendaftarkan dirinya sebagai administrator. Kunci seluruh
               controller setelah admin pertama ada. */
            $this->requireAdmin('Instalasi sudah selesai. Hanya Administrator yang dapat membuka pemasang.');
        }
        if ($controller === 'migrate' && !$this->ci->input->is_cli_request()) {
            /* CreateMigration/undoMigration/resetMigration menjalankan dan
               membatalkan migrasi skema. Lewat browser wajib admin. */
            $this->requireAdmin('Hanya Administrator yang dapat menjalankan migrasi skema.');
        }
        if ($controller === 'siswa') {
            $this->guardExamPage($method);
        }
        if ($controller !== 'siswa') {
            return;
        }

        $guarded = array('cbt', 'konfirmasi', 'validasisiswa', 'penilaian', 'checktimer', 'loadsoal', 'loadnomorsoal', 'savejawaban', 'selesaiujian', 'leavecbt', 'gettimer', 'savesoalsiswa', 'savelogujian');
        if (!in_array($method, $guarded, TRUE)) {
            return;
        }

        $this->loadService();
        if (!$this->service->schemaReady()) {
            $this->deny('Modul keamanan ujian belum dimigrasikan.', 503);
        }
        $student = $this->service->authenticatedStudent();
        if (!$student) {
            $this->deny('Akses hanya untuk akun siswa yang aktif.', 403);
        }
        $_POST['siswa'] = (string) $student->id_siswa;

        if (in_array($method, array('savesoalsiswa', 'savelogujian'), TRUE)) {
            $this->deny('Endpoint lama ini dinonaktifkan demi keamanan ujian.', 410);
        }
        if ($method === 'cbt') {
            return;
        }

        $idJadwal = $this->scheduleId($method);
        if ($idJadwal <= 0) {
            $this->deny('Jadwal ujian tidak valid.', 422);
        }
        $schedule = $this->service->schedule($idJadwal);
        if (!$schedule || (string) $schedule->status !== '1') {
            $this->deny('Jadwal ujian tidak aktif.', 403);
        }
        $_POST['jadwal'] = (string) $schedule->id_jadwal;
        $_POST['bank'] = (string) $schedule->id_bank;
        if (!$this->withinScheduleDate($schedule)) {
            $this->deny('Ujian berada di luar rentang jadwal.', 403);
        }
        $this->withinSessionWindow($schedule, $student->id_siswa, $method);

        if ($method === 'konfirmasi') {
            return;
        }

        $seb = $this->service->verifySeb($schedule);
        if (!$seb['ok'] && $method === 'penilaian') {
            $resume = $this->service->attempt($student->id_siswa, $idJadwal);
            list($canResume) = $this->service->validateActiveAttempt($resume);
            if ($canResume && (int) $resume->seb_verified === 1) {
                $seb = array('ok' => TRUE, 'mode' => 'REQUIRED', 'verified' => TRUE, 'reason' => NULL);
            }
        }
        if (!$seb['ok']) {
            $this->deny($seb['reason'], 403, array('seb_required' => TRUE));
        }

        if ($method === 'validasisiswa') {
            if (!$this->service->lockQuestionMapping($student->id_siswa, $idJadwal, $schedule->id_bank)) {
                $this->deny('Persiapan soal sedang diproses. Silakan coba kembali.', 409);
            }
            $existing = $this->service->attempt($student->id_siswa, $idJadwal);
            if ($existing) {
                list($active, $reason) = $this->service->validateActiveAttempt($existing);
                if (!$active) {
                    $this->deny($reason, 403);
                }
                list($bound, $reason) = $this->service->bindSession($existing);
                if (!$bound) {
                    $this->deny($reason, 409);
                }
                if (!$this->ci->db->where(array('id_siswa' => $student->id_siswa,
                    'id_jadwal' => $idJadwal, 'id_bank' => $schedule->id_bank))
                    ->count_all_results('cbt_soal_siswa')) {
                    $this->deny('Mapping ujian tidak ditemukan. Hubungi pengawas.', 409);
                }
            }
            return;
        }
        if ($method === 'penilaian') {
            $attempt = $this->service->createOrResume($student, $schedule, $seb['verified']);
            list($active, $reason) = $this->service->validateActiveAttempt($attempt);
            if (!$active) {
                $this->deny($reason, 403);
            }
            list($bound, $bindReason) = $this->service->bindSession($attempt);
            if (!$bound) {
                $this->deny($bindReason, 409);
            }
            return;
        }

        if ($method === 'leavecbt' || $method === 'gettimer') {
            $idFromUrl = (int) $this->ci->uri->segment(3);
            if ($idFromUrl !== (int) $student->id_siswa && $method === 'gettimer') {
                $this->deny('Identitas siswa tidak sesuai.', 403);
            }
            $idFromUrl = (int) $this->ci->uri->segment(4);
            if ($method === 'leavecbt' && $idFromUrl !== (int) $student->id_siswa) {
                $this->deny('Identitas siswa tidak sesuai.', 403);
            }
        }

        $attempt = $this->service->attempt($student->id_siswa, $idJadwal);
        /* FR-21: auto-submit terjadi tepat saat waktu habis, jadi pengumpulan
           diberi kelonggaran lebih panjang daripada endpoint biasa. */
        $grace = $method === 'selesaiujian' ? 120 : 10;
        list($active, $reason) = $this->service->validateActiveAttempt($attempt, $grace);
        if (!$active) {
            $this->deny($reason, 403);
        }
        list($bound, $bindReason) = $this->service->bindSession($attempt);
        if (!$bound) {
            $this->deny($bindReason, 409);
        }
        $this->service->heartbeat($attempt, $seb['verified']);

        if ($method === 'savejawaban') {
            $_POST['elapsed'] = $this->service->elapsed($attempt);
            $data = json_decode((string) $this->ci->input->post('data'));
            $idBank = (int) $this->ci->input->post('bank');
            try {
                $data = $this->service->validateAnswer($data, $student->id_siswa, $idJadwal, $idBank);
            } catch (InvalidArgumentException $error) {
                $this->deny($error->getMessage(), 422);
            }
            $_POST['data'] = json_encode($data);
        }

        if ($method === 'leavecbt') {
            $this->service->recordEvent($attempt, 'LEAVE_ATTEMPT');
            $this->service->lockStudentExam($student->id_siswa, $attempt->attempt_id, 'Siswa keluar dari sesi ujian');
        }
    }

    /**
     * Hampir semua modul legacy punya deleteFile() yang memanggil
     * unlink() pada path kiriman pengirim tanpa pemeriksaan. Siswa dan guru
     * bisa menghapus application/config/database.php atau berkas lain hanya
     * dengan mengirim src=../../application/config/database.php. Batasi ke
     * folder unggahan masing-masing modul.
     */
    private function guardFileDeletion($controller, $method)
    {
        $map = array(
            'cbtbanksoal' => array('deletefile' => 'uploads/bank_soal/'),
            'cbtcetak' => array('deletefile' => 'uploads/settings/'),
            'dataalumni' => array('deletefile' => 'uploads/foto_siswa/', 'deletefoto' => 'uploads/foto_siswa/'),
            'datasiswa' => array('deletefile' => 'uploads/foto_siswa/'),
            'guruview' => array('deletefile' => 'uploads/profiles/'),
            'kelasmateri' => array('deletefile' => 'uploads/materi/'),
            'settings' => array('deletefile' => 'uploads/settings/'),
            'siswa' => array('deletefile' => 'uploads/file_siswa/'),
            'useradmin' => array('deletefile' => 'uploads/profiles/'),
            'walisiswa' => array('deletefile' => 'uploads/foto_siswa/'),
        );
        if (!isset($map[$controller][$method])) {
            return;
        }
        $prefix = $map[$controller][$method];
        $raw = (string) $this->ci->input->post('src', TRUE);
        if ($raw === '') {
            $raw = (string) $this->ci->input->get('src', TRUE);
        }
        $path = (string) parse_url($raw, PHP_URL_PATH);
        $base = (string) parse_url(base_url(), PHP_URL_PATH);
        if ($base !== '' && strpos($path, $base) === 0) {
            $path = substr($path, strlen($base));
        }
        $path = ltrim(str_replace('\\', '/', rawurldecode($path)), '/');
        $valid = $path !== ''
            && strpos($path, $prefix) === 0
            && strpos($path, '..') === FALSE
            && strpos($path, "\0") === FALSE
            && substr_count($path, '/') >= 2;
        if (!$valid) {
            $this->deny('Berkas tidak diizinkan untuk dihapus.', 403);
        }
    }

    /**
     * uploadSoalImage menulis file_put_contents('./uploads/bank_soal/' . name)
     * dengan name dan isi dari POST. Tanpa pemeriksaan, guru bisa mengirim
     * name=../../shell.php beserta isi PHP lalu mengeksekusinya lewat browser.
     */
    private function guardSoalImageUpload()
    {
        $this->ci->load->library('ion_auth');
        if (!$this->ci->ion_auth->logged_in()) {
            $this->deny('Sesi login telah berakhir.', 401);
        }
        $name = (string) $this->ci->input->post('name');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($name !== basename($name)
            || !preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]{0,119}$/D', $name)
            || !in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'), TRUE)) {
            $this->deny('Nama berkas gambar tidak valid.', 422);
        }
        $raw = (string) $this->ci->input->post('src');
        $decoded = strlen($raw) <= 6 * 1024 * 1024
            ? base64_decode(str_replace('%2B', '+', $raw), TRUE)
            : FALSE;
        if ($decoded === FALSE || @getimagesizefromstring($decoded) === FALSE) {
            $this->deny('Isi berkas bukan gambar.', 422);
        }
    }

    /**
     * Bank soal adalah milik guru pembuatnya. Tanpa pemeriksaan ini guru dapat
     * membuka, mengubah, dan menghapus bank milik guru lain hanya dengan
     * menebak id_bank pada URL atau body. Administrator bebas.
     */
    private function guardBankAccess($controller, $method)
    {
        if ($controller !== 'cbtbanksoal' && $controller !== 'excelsoal') {
            return;
        }
        $this->ci->load->library('ion_auth');
        if (!$this->ci->ion_auth->logged_in()) {
            return;
        }
        if ($controller === 'cbtbanksoal' && in_array($method, array('tambahsoal', 'doimport', 'uploadsoal'), TRUE)) {
            $message = $method === 'tambahsoal'
                ? 'Soal baru disimpan setelah formulir lengkap.'
                : 'Import lama telah dinonaktifkan karena dapat mengganti soal yang sudah ada.';
            $this->deny($message, 410);
        }
        if ($this->ci->ion_auth->is_admin()) {
            return;
        }

        /* data/dataTable menerima id guru dari URI, jadi guru B bisa melihat
           daftar bank soal guru A dengan menebak id_guru. */
        if (in_array($method, array('data', 'datatable'), TRUE)) {
            $requested = (string) $this->ci->uri->segment(3);
            if ($requested !== '' && $requested !== '0') {
                $teacher = $this->authenticatedTeacher();
                if (!$teacher || (int) $requested !== (int) $teacher->id_guru) {
                    $this->deny('Anda hanya dapat melihat bank soal milik sendiri.', 403);
                }
            }
            return;
        }

        /* Alur bank soal: admin yang membuat bank beserta kuota/template.
           Guru hanya mengisi soal dan mengimpor template milik banknya. */
        $adminOnly = array(
            'addbank', 'editbank', 'savebank', 'deletebank',
            'deleteallbank', 'copybanksoal',
        );
        if (in_array($method, $adminOnly, TRUE)) {
            $this->deny('Hanya Administrator yang dapat mengelola bank soal.', 403);
        }

        /* Metode yang mengambil id_bank dari URI: /cbtbanksoal/<method>/<id>. */
        $inUri = array(
            'detail', 'buatsoal', 'importsoal', 'export',
            'copybanksoal', 'previewword', 'getsoalsiswa',
        );
        $byPost = array(
            'savebank' => 'id_bank',
            'savesoal' => 'bank_id',
            'tambahsoal' => 'bank',
            'import' => 'id_bank',
            'saveselected' => 'id_bank',
            'preview' => 'bank_id',
            'create' => 'bank_id',
            'upload' => 'bank_id',
            'uploadfile' => 'id_soal',
        );
        $byQuery = array(
            'deletebank' => 'id_bank',
            'editbank' => 'id_bank',
            'getsoalnomor' => 'bank_id',
            'template' => 'bank_id',
            'latest' => 'bank_id',
        );
        $byPostIds = array('deleteallbank');
        if ($controller === 'excelsoal' && in_array($method, array('session', 'updateitem', 'commit', 'cancel'), TRUE)) {
            $importId = $method === 'session'
                ? (int) $this->ci->input->get('import_id', TRUE)
                : (int) $this->ci->input->post('import_id', TRUE);
            $draft = $this->ci->db->select('bank_id')->get_where('cbt_soal_import', array('id' => $importId))->row();
            if (!$draft || !$this->ownsBank($draft->bank_id)) {
                $this->deny('Anda tidak memiliki akses ke draft import ini.', 403);
            }
            return;
        }
        if ($method === 'deletefile') {
            /* deleteFile memanggil unlink() pada path yang dikirim pengirim.
               Batasi ke folder bank soal dan wajibkan ID soal milik guru. */
            $src = (string) $this->ci->input->post('src', TRUE);
            $path = ltrim(str_replace('\\', '/', str_replace(base_url(), '', $src)), '/');
            $soalId = (int) $this->ci->input->post('soal_id', TRUE);
            $bankId = $this->bankIdFromSoal($soalId);
            if (strpos($path, 'uploads/bank_soal/') !== 0 || $soalId <= 0 || !$bankId || !$this->ownsBank($bankId)) {
                $this->deny('Anda tidak memiliki akses untuk menghapus berkas soal ini.', 403);
            }
            return;
        }
        if ($method === 'hapussoal' || $method === 'uploadfile') {
            $soalId = $method === 'hapussoal'
                ? (int) $this->ci->input->post('soal_id', TRUE)
                : (int) $this->ci->input->get('id_soal', TRUE);
            $bankId = $this->bankIdFromSoal($soalId);
            if ($soalId <= 0 || !$bankId || !$this->ownsBank($bankId)) {
                $this->deny('Anda tidak memiliki akses ke soal ini.', 403);
            }
            return;
        }
        if (in_array($method, $inUri, TRUE)) {
            $bankId = $this->bankIdFromUri();
        } elseif (isset($byPost[$method])) {
            $bankId = $this->ci->input->post($byPost[$method], TRUE);
            if (in_array($method, array('savesoal', 'uploadfile'), TRUE)) {
                $soalId = (int) $this->ci->input->post('soal_id')
                    ?: (int) $this->ci->input->post('id_soal');
                if ($soalId > 0 && !$bankId) {
                    $bankId = $this->bankIdFromSoal($soalId);
                }
            }
        } elseif (isset($byQuery[$method])) {
            $bankId = $this->ci->input->get($byQuery[$method], TRUE);
        } elseif (in_array($method, $byPostIds, TRUE)) {
            $bankId = $this->ci->input->post('ids', TRUE);
        } else {
            return;
        }

        foreach ($this->bankIds($bankId) as $id) {
            if (!$this->ownsBank($id)) {
                $this->deny('Anda tidak memiliki akses ke bank soal ini.', 403);
            }
        }
    }

    private function bankIdFromUri()
    {
        $uri = $this->ci->uri->segment_array();
        foreach ($uri as $index => $segment) {
            if (ctype_digit((string) $segment)) {
                return $segment;
            }
        }
        return '';
    }

    private function bankIdFromSoal($soalId)
    {
        $row = $this->ci->db->select('bank_id')->get_where('cbt_soal', array('id_soal' => $soalId))->row();
        return $row ? $row->bank_id : '';
    }

    /** deleteAllBank mengirim JSON array; metode lain mengirim satu id. */
    private function bankIds($raw)
    {
        if (is_array($raw)) {
            return array_filter(array_map('intval', $raw));
        }
        $decoded = json_decode((string) $raw, TRUE);
        if (is_array($decoded)) {
            return array_filter(array_map('intval', $decoded));
        }
        return ctype_digit((string) $raw) ? array((int) $raw) : array();
    }

    private function ownsBank($bankId)
    {
        $bank = $this->ci->db->select('bank_guru_id')
            ->get_where('cbt_bank_soal', array('id_bank' => (int) $bankId))->row();
        if (!$bank) {
            return TRUE; /* Biarkan controller melaporkan "tidak ditemukan". */
        }
        $user = $this->ci->ion_auth->user()->row();
        if (!$user) {
            return FALSE;
        }
        $this->ci->load->model('Dashboard_model', 'guard_dashboard');
        $tp = $this->ci->guard_dashboard->getTahunActive();
        $smt = $this->ci->guard_dashboard->getSemesterActive();
        if (!$tp || !$smt) {
            return FALSE;
        }
        $teacher = $this->ci->guard_dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
        return $teacher && (string) $bank->bank_guru_id === (string) $teacher->id_guru;
    }

    /**
     * Bank soal milik guru juga menentukan jadwal ujiannya. Tanpa pemeriksaan
     * ini guru B bisa membuka nilai, rekap, analisis, status, dan cetak dari
     * jadwal yang memakai bank guru A hanya dengan menebak id_jadwal.
     */
    private function guardScheduleAccess($controller, $method)
    {
        $olehGuru = array(
            'cbtjadwal' => array(
                'add' => array('uri' => 3),
            'savejadwal' => array('post' => array('id_jadwal', 'bank_id')),
                'deletejadwal' => array('get' => array('id_jadwal')),
                'deletealljadwal' => array('post' => array('checked')),
            ),
            'cbtnilai' => array(
                'index' => array('get' => array('jadwal')),
                'detail' => array('get' => array('jadwal')),
                'inputessai' => array('get' => array('jadwal')),
                'simpankoreksi' => array('post' => array('jadwal')),
                'tandaikoreksi' => array('post' => array('jadwal')),
                'tandaisemua' => array('post' => array('id_jadwal')),
                'simpankoreksiessai' => array('post' => array('jadwal')),
            ),
            'cbtrekap' => array(
                'olahnilai' => array('get' => array('jadwal')),
                'backupnilai' => array('uri' => 3),
                'generatenilaiujian' => array('uri' => 3),
                'bulkbackup' => array('post' => array('ids')),
                'hapusrekap' => array('post' => array('ids')),
            ),
            'cbtanalisis' => array(
                'index' => array('get' => array('jadwal')),
                'kalkulasi' => array('get' => array('jadwal')),
                'getnilaikelas' => array('get' => array('jadwal')),
                'getjadwalujianbyjadwal' => array('post' => array('id_jadwal')),
                'generatenilaiujian' => array('uri' => 3),
            ),
            'cbtstatus' => array(
                'detail' => array('get' => array('jadwal')),
                'status_ruang' => array('get' => array('jadwal')),
                'getjadwalujianbyjadwal' => array('post' => array('id_jadwal')),
                'getsiswakelas' => array('get' => array('jadwal')),
                'getsiswaruang' => array('get' => array('jadwal')),
            ),
            'examactivity' => array(
                'data' => array('uri' => 3),
                'kick' => array('post' => array('jadwal')),
                'unkick' => array('post' => array('jadwal')),
                'extend' => array('post' => array('jadwal')),
                'detail' => array('attempt_uri' => 3),
            ),
            'cbtcetak' => array(
                'getsiswakelas' => array('get' => array('jadwal')),
                'getsiswaruang' => array('get' => array('jadwal')),
                'kartupeserta' => array('post' => array('jadwal')),
            ),
        );

        if (!isset($olehGuru[$controller][$method])) {
            return;
        }
        $this->ci->load->library('ion_auth');
        if (!$this->ci->ion_auth->logged_in() || $this->ci->ion_auth->is_admin()) {
            return;
        }
        if (!$this->ci->ion_auth->in_group('guru')) {
            return;
        }

        $aturan = $olehGuru[$controller][$method];
        if ($controller === 'cbtjadwal' && $method === 'savejadwal') {
            $bankId = (int) $this->ci->input->post('bank_id', TRUE);
            if ($bankId <= 0 || !$this->ownsBank($bankId)) {
                $this->deny('Anda tidak memiliki akses ke bank soal pada jadwal ini.', 403);
            }
            $aturan = array('post' => array('id_jadwal'));
        }
        if (isset($aturan['attempt_uri'])) {
            $attemptId = trim((string) $this->ci->uri->segment($aturan['attempt_uri']));
            if ($attemptId === '') {
                $this->deny('Attempt ujian tidak valid.', 422);
            }
            $attempt = $this->ci->db->select('id_jadwal')
                ->get_where('cbt_exam_attempt', array('attempt_id' => $attemptId))->row();
            if ($attempt && !$this->ownsSchedule((int) $attempt->id_jadwal)) {
                $this->deny('Anda tidak memiliki akses ke aktivitas ujian ini.', 403);
            }
            return;
        }
        if (isset($aturan['uri'])) {
            $rawId = (string) $this->ci->uri->segment($aturan['uri']);
            if ($controller === 'cbtjadwal' && $method === 'add' && $rawId === '0') {
                return;
            }
            if (!preg_match('/^[1-9][0-9]*$/D', $rawId)) {
                $this->deny('ID jadwal tidak valid.', 422);
            }
            $daftar = array((int) $rawId);
        } else {
            $sumber = isset($aturan['get']) ? $this->ci->input->get() : $this->ci->input->post();
            $nilai = array();
            foreach ($aturan['get'] ?? $aturan['post'] ?? array() as $field) {
                $nilai[] = is_array($sumber) && isset($sumber[$field]) ? $sumber[$field] : null;
            }
            $daftar = $this->idsFromList($nilai);
        }
        if (!$daftar) {
            return; /* Tanpa jadwal: halaman daftar, bukan data milik orang lain. */
        }
        foreach ($daftar as $idJadwal) {
            if ($idJadwal <= 0) {
                continue;
            }
            if (!$this->ownsSchedule($idJadwal)) {
                $this->deny('Anda tidak memiliki akses ke jadwal ujian ini.', 403);
            }
        }
    }

    /** id_jadwal bisa datang sebagai angka, JSON array, atau campuran. */
    private function idsFromList($nilai)
    {
        $hasil = array();
        foreach ($nilai as $item) {
            if (is_array($item)) {
                $hasil = array_merge($hasil, $this->idsFromList($item));
                continue;
            }
            $decoded = json_decode((string) $item, TRUE);
            if (is_array($decoded)) {
                $hasil = array_merge($hasil, $this->idsFromList($decoded));
                continue;
            }
            if ($item === null || $item === '') {
                continue;
            }
            if ((is_int($item) && $item > 0) || (is_string($item) && preg_match('/^[1-9][0-9]*$/D', $item))) {
                $hasil[] = (int) $item;
                continue;
            }
            $this->deny('ID jadwal tidak valid.', 422);
        }
        return array_values(array_unique($hasil));
    }

    private function ownsSchedule($idJadwal)
    {
        $lama = $this->ci->db->select('id_bank')
            ->get_where('cbt_jadwal', array('id_jadwal' => (int) $idJadwal))->row();
        if (!$lama) {
            return TRUE; /* Biarkan controller melaporkan "tidak ditemukan". */
        }
        return $this->ownsBank($lama->id_bank);
    }

    /**
     * Pertahanan RBAC terpusat untuk controller legacy yang hanya mengecek
     * "sudah login". Menu yang disembunyikan tidak dianggap sebagai kontrol
     * akses karena URL dan AJAX tetap dapat dipanggil langsung.
     */
    private function guardRoleAccess($controller, $method)
    {
        $protected = array('usersiswa', 'userguru', 'guruview', 'siswa', 'datasiswa', 'datasiswaform');

        $this->ci->load->library('ion_auth');
        if (!$this->ci->ion_auth->logged_in()) {
            if (in_array($controller, $protected, TRUE)) {
                $this->deny('Sesi login telah berakhir.', 401);
            }
            return;
        }

        /* Akun Tata Usaha mandiri (bukan admin dan bukan guru) hanya boleh mengakses modul yang relevan */
        $isPureTu = $this->ci->ion_auth->in_group('tu') && !$this->ci->ion_auth->is_admin() && !$this->ci->ion_auth->in_group('guru');
        if ($isPureTu) {
            $tuAllowedControllers = array('dashboard', 'auth', 'datasiswa', 'datasiswaform', 'usersiswa', 'pengumuman');
            if (!in_array($controller, $tuAllowedControllers, TRUE)) {
                $this->deny('Role Tata Usaha tidak memiliki hak untuk mengakses modul ini.', 403);
            }
        }

        if ($controller === 'usersiswa') {
            if ($this->ci->ion_auth->is_admin()) {
                return;
            }
            if ($this->ci->ion_auth->in_group('tu')) {
                $tuUsersiswaAllowed = array('index', 'data', 'list', 'registersiswa', 'aktifkan', 'activate', 'aktifkansemua', 'nonaktifkan', 'deactivate', 'reset_login');
                if (!in_array(strtolower($method), $tuUsersiswaAllowed, TRUE)) {
                    $this->deny('Aksi ini hanya dapat dilakukan Administrator.', 403);
                }
                return;
            }
            $this->deny('Manajemen akun siswa hanya dapat diakses Administrator dan Tata Usaha.', 403);
        }

        if ($controller === 'datasiswa') {
            if ($this->ci->ion_auth->is_admin()) {
                return;
            }
            if ($this->ci->ion_auth->in_group('tu') || $this->ci->ion_auth->in_group('guru')) {
                $tuDatasiswaForbidden = array('delete', 'deletefile');
                if (in_array(strtolower($method), $tuDatasiswaForbidden, TRUE)) {
                    $this->deny('Penghapusan data siswa hanya dapat dilakukan Administrator.', 403);
                }
                return;
            }
            $this->deny('Anda tidak memiliki hak untuk mengakses halaman ini.', 403);
        }

        if ($controller === 'datasiswaform') {
            if ($this->ci->ion_auth->is_admin() || $this->ci->ion_auth->in_group('tu') || $this->ci->ion_auth->in_group('guru')) {
                return;
            }
            $this->deny('Anda tidak memiliki hak untuk mengakses halaman ini.', 403);
        }

        if ($controller === 'userguru') {
            if ($this->ci->ion_auth->is_admin()) {
                return;
            }
            if (!$this->ci->ion_auth->in_group('guru') || $method !== 'editlogin') {
                $this->deny('Manajemen akun guru hanya dapat diakses Administrator.', 403);
            }
            $teacher = $this->authenticatedTeacher();
            $requested = (int) $this->ci->input->post('id_guru', TRUE);
            if (!$teacher || $requested <= 0 || $requested !== (int) $teacher->id_guru) {
                $this->deny('Guru hanya dapat mengubah akun miliknya sendiri.', 403);
            }
            return;
        }

        if ($controller === 'guruview') {
            if ($this->ci->ion_auth->is_admin()) {
                return;
            }
            if (!$this->ci->ion_auth->in_group('guru')) {
                $this->deny('Halaman ini hanya dapat diakses akun Guru.', 403);
            }
            $teacher = $this->authenticatedTeacher();
            if (!$teacher) {
                $this->deny('Profil guru aktif tidak ditemukan.', 403);
            }
            if ($method === 'save') {
                $requested = (int) $this->ci->input->post('id_guru', TRUE);
                if ($requested <= 0 || $requested !== (int) $teacher->id_guru) {
                    $this->deny('Guru hanya dapat mengubah profil miliknya sendiri.', 403);
                }
            }
            if (in_array($method, array('uploadfile', 'deletefile'), TRUE)) {
                $requested = (int) $this->ci->uri->segment(3);
                if ($requested <= 0 || $requested !== (int) $teacher->id_guru) {
                    $this->deny('Guru hanya dapat mengubah foto miliknya sendiri.', 403);
                }
            }
            return;
        }

        $proctorMethods = array('resettimer', 'ulangiujian', 'applyaction', 'olahnilai');
        if (in_array($method, $proctorMethods, TRUE)) {
            if (!$this->ci->ion_auth->is_admin() && !$this->ci->ion_auth->in_group('guru')) {
                $this->deny('Aksi pengawas hanya dapat dijalankan Administrator atau Guru.', 403);
            }
            if ($this->ci->ion_auth->is_admin() && $method === 'applyaction') {
                $idJadwal = (int) $this->ci->input->post('jadwal', TRUE);
                if ($idJadwal <= 0 || !$this->scheduleExists($idJadwal)) {
                    $this->deny('Jadwal ujian tidak valid.', 422);
                }
                $this->scopeLegacyProctorAction($idJadwal);
            } elseif (!$this->ci->ion_auth->is_admin()) {
                $this->guardLegacyProctorObject($controller, $method);
            }
            return;
        }
        if ($controller === 'siswa') {
            if (!$this->ci->ion_auth->in_group('siswa')) {
                $this->deny('Halaman ini hanya dapat diakses akun Siswa.', 403);
            }
            return;
        }
    }

    private function guardLegacyProctorObject($controller, $method)
    {
        $idJadwal = 0;
        if ($method === 'applyaction') {
            $idJadwal = (int) $this->ci->input->post('jadwal', TRUE);
        } elseif ($method === 'olahnilai') {
            if ($controller === 'cbtrekap') {
                /* Cbtrekap::olahNilai adalah halaman rekap guru dan memakai
                   ?jadwal=; tanpa jadwal ia hanya menampilkan daftar. */
                $rawJadwal = $this->ci->input->get('jadwal', TRUE);
                if ($rawJadwal === NULL || $rawJadwal === '') {
                    return;
                }
                if (!preg_match('/^[1-9][0-9]*$/D', (string) $rawJadwal)) {
                    $this->deny('Jadwal ujian tidak valid.', 422);
                }
                $idJadwal = (int) $rawJadwal;
            } else {
                $idJadwal = (int) $this->ci->uri->segment(4);
            }
        } else {
            $idDurasi = $method === 'resettimer'
                ? (string) $this->ci->input->post('id_durasi', TRUE)
                : (string) $this->ci->uri->segment(3);
            if ($idDurasi === '') {
                $this->deny('Sesi peserta tidak valid.', 422);
            }
            $duration = $this->ci->db->select('id_jadwal')
                ->get_where('cbt_durasi_siswa', array('id_durasi' => $idDurasi))->row();
            $idJadwal = $duration ? (int) $duration->id_jadwal : 0;
        }
        if ($idJadwal <= 0) {
            $this->deny('Jadwal ujian tidak valid.', 422);
        }
        if (!$this->ownsSchedule($idJadwal)) {
            $this->deny('Anda tidak memiliki akses ke jadwal ujian ini.', 403);
        }
        if ($method === 'ulangiujian') {
            $requestedBank = (int) $this->ci->uri->segment(4);
            $schedule = $this->ci->db->select('id_bank')
                ->get_where('cbt_jadwal', array('id_jadwal' => $idJadwal))->row();
            if (!$schedule || $requestedBank <= 0 || $requestedBank !== (int) $schedule->id_bank) {
                $this->deny('Bank soal tidak sesuai dengan jadwal ujian.', 403);
            }
        }
        if ($method === 'applyaction') {
            $this->scopeLegacyProctorAction($idJadwal);
        }
    }

    private function scheduleExists($idJadwal)
    {
        return $this->ci->db->where('id_jadwal', (int) $idJadwal)
            ->count_all_results('cbt_jadwal') === 1;
    }

    /**
     * Siswa::applyAction adalah endpoint legacy yang membawa beberapa daftar
     * ID di dalam JSON. Validasi parent jadwal saja tidak cukup: setiap child
     * harus berasal dari jadwal yang sama. Aksi ulangi lama juga menghapus
     * seluruh cbt_nilai siswa tanpa filter jadwal; lakukan penghapusan aman di
     * sini lalu kosongkan daftar agar kode legacy tidak mengulanginya.
     */
    private function scopeLegacyProctorAction($idJadwal)
    {
        $raw = $this->ci->input->post('aksi');
        $action = is_string($raw) ? json_decode($raw) : NULL;
        if (!$action || !is_object($action)) {
            $this->deny('Aksi pengawas tidak valid.', 422);
        }

        $force = $this->strictPositiveIds($action->force ?? array(), 'force');
        $hapus = $this->strictPositiveIds($action->hapus ?? array(), 'hapus');
        $reset = $this->strictPositiveIds($action->reset ?? array(), 'reset');
        $ulang = $this->strictPositiveIds($action->ulang ?? array(), 'ulang');
        $log = $this->strictPositiveIds($action->log ?? array(), 'log');

        $this->assertChildrenInSchedule('cbt_durasi_siswa', 'id_durasi', array_merge($force, $hapus), $idJadwal);
        $this->assertChildrenInSchedule('log_ujian', 'id_log', $reset, $idJadwal);
        $this->assertChildrenInSchedule('cbt_durasi_siswa', 'id_siswa', array_merge($ulang, $log), $idJadwal);

        if ($ulang) {
            $this->loadService();
            $bank = $this->service->schedule($idJadwal)->id_bank;
            sort($ulang, SORT_NUMERIC);
            foreach ($ulang as $idSiswa) {
                if (!$this->service->lockQuestionMapping($idSiswa, $idJadwal, $bank)) {
                    $this->deny('Sesi ujian sedang diproses. Silakan coba kembali.', 409);
                }
            }
            $this->ci->db->trans_start();
            foreach (array('cbt_nilai', 'cbt_soal_siswa', 'log_ujian') as $table) {
                $this->ci->db->where('id_jadwal', $idJadwal)
                    ->where_in('id_siswa', $ulang)
                    ->delete($table);
            }
            foreach (array('cbt_durasi_siswa', 'cbt_exam_attempt') as $table) {
                $this->ci->db->where('id_jadwal', $idJadwal)->where_in('id_siswa', $ulang)->delete($table);
            }
            $this->ci->db->trans_complete();
            if (!$this->ci->db->trans_status()) {
                $this->deny('Aksi mengulang ujian gagal diproses.', 500);
            }
            $action->ulang = array();
            $_POST['aksi'] = json_encode($action);
        }
    }

    private function strictPositiveIds($values, $field)
    {
        if (!is_array($values)) {
            $this->deny('Daftar ID ' . $field . ' tidak valid.', 422);
        }
        $ids = array();
        foreach ($values as $value) {
            if ((is_int($value) && $value > 0)
                || (is_string($value) && preg_match('/^[1-9][0-9]*$/D', $value))) {
                $ids[] = (int) $value;
                continue;
            }
            $this->deny('Daftar ID ' . $field . ' tidak valid.', 422);
        }
        return array_values(array_unique($ids));
    }

    private function assertChildrenInSchedule($table, $idColumn, $ids, $idJadwal)
    {
        $ids = array_values(array_unique($ids));
        if (!$ids) {
            return;
        }
        $count = $this->ci->db->where('id_jadwal', $idJadwal)
            ->where_in($idColumn, $ids)
            ->count_all_results($table);
        if ($count !== count($ids)) {
            $this->deny('Objek aksi pengawas tidak termasuk dalam jadwal ini.', 403);
        }
    }

    private function authenticatedTeacher()
    {
        $user = $this->ci->ion_auth->user()->row();
        if (!$user) {
            return NULL;
        }
        $this->ci->load->model('Dashboard_model', 'guard_role_dashboard');
        $tp = $this->ci->guard_role_dashboard->getTahunActive();
        $smt = $this->ci->guard_role_dashboard->getSemesterActive();
        if (!$tp || !$smt) {
            return NULL;
        }
        return $this->ci->guard_role_dashboard->getDataGuruByUserId($user->id, $tp->id_tp, $smt->id_smt);
    }

    /**
     * Gerbang halaman /login-siswa: wajib dibuka dari dalam Safe Exam Browser.
     * Halaman staf (/login) tidak terpengaruh.
     */
    private function guardSebStudentPortal($controller, $method)
    {
        if ($controller !== 'portal' || $method !== 'siswa') {
            return;
        }
        if (!$this->ci->config->item('student_portal_requires_seb')) {
            return;
        }
        $this->loadService();
        $seb = $this->service->verifySebClient();
        if ($seb['ok']) {
            return;
        }
        show_error(
            $seb['reason'] . ' Jendela SEB tidak menampilkan address bar; bila Anda masih melihat alamat situs di atas, itu browser biasa.',
            403,
            'Safe Exam Browser Diperlukan'
        );
    }

    /**
     * Pisahkan portal login: /login untuk admin+guru, /login-siswa untuk peserta.
     * Pemeriksaan memakai grup akun sebelum ion_auth memproses, supaya siswa
     * tidak bisa masuk lewat halaman staf (dan sebaliknya).
     */
    private function guardLoginPortal($controller, $method)
    {
        if ($controller !== 'auth' || $method !== 'cek_login') {
            return;
        }
        $portal = strtolower(trim((string) $this->ci->input->post('portal')));
        if ($portal !== 'siswa' && $portal !== 'staf') {
            $portal = '';
        }
        $identity = trim((string) $this->ci->input->post('identity'));
        if ($identity === '') {
            return;
        }

        $groups = $this->ci->db
            ->select('g.name')
            ->from('users u')
            ->join('users_groups ug', 'ug.user_id = u.id')
            ->join('groups g', 'g.id = ug.group_id')
            ->where('u.username', $identity)
            ->get()->result();
        if (!$groups) {
            /* Akun tidak dikenal: biarkan ion_auth yang menjawab. */
            return;
        }

        $isStudent = false;
        foreach ($groups as $row) {
            if (strtolower((string) $row->name) === 'siswa') {
                $isStudent = true;
            }
        }
        if ($portal === 'siswa' && !$isStudent) {
            $this->rejectLogin('Akun ini bukan akun peserta. Gunakan halaman login admin/guru.');
        }
        if ($portal === 'staf' && $isStudent) {
            $this->rejectLogin('Akun peserta tidak dapat masuk di sini. Gunakan halaman login peserta.');
        }
        if ($isStudent) {
            $siswa = $this->ci->db->select('id_siswa, exam_locked_at, exam_locked_reason')
                ->get_where('master_siswa', array('username' => $identity))->row();
            if ($siswa && !empty($siswa->exam_locked_at)) {
                $this->rejectLogin('Login dikunci karena siswa keluar dari sesi ujian. Hubungi pengawas untuk Reset Login.', array('exam_locked' => TRUE));
            }
        }
        if ($isStudent && $portal !== 'staf'
            && $this->ci->config->item('student_portal_requires_seb')) {
            $this->loadService();
            $seb = $this->service->verifySebClient();
            if (!$seb['ok']) {
                $this->rejectLogin($seb['reason'], array('seb_required' => TRUE));
            }
        }
    }

    private function rejectLogin($message, array $extra = array())
    {
        $this->ci->output->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(json_encode(array_merge(
                array('status' => FALSE, 'failed' => $message, 'akses' => 'portal'),
                $extra
            )));
        $this->ci->output->_display();
        exit;
    }

    private function denyRetiredModules($controller)    {
        $retired = array(
            'rapor', 'bukurapor', 'bukuinduk', 'dataalumni', 'dataekstra',
            'kelasmateri', 'kelasmaterijadwal', 'kelascatatan', 'kelasjadwal',
            'kelasstatus', 'kelasnilai',
            'kelasabsensiharian', 'kelasabsensibulanan',
            'walicatatan',
            /* Controller legacy tanpa view/berkas pendukung di paket ini.
               Menu penggantinya: useradmin, userguru, usersiswa, datamapel. */
            'users', 'jurusanmapel',
            /* Data Manager menghapus (drop) semua tabel lewat GET /dbclear.
               Penggantinya: dbpemulihan (backup + restore teruji). */
            'dbclear',
        );
        if (!in_array($controller, $retired, TRUE)) {
            return;
        }
        show_error('Modul ini tidak termasuk dalam lingkup CBT.', 403, 'Modul Dinonaktifkan');
    }

    /**
     * Halaman siswa yang melayani e-Learning / nilai harian / catatan wali.
     * Controller Siswa tidak bisa diblok utuh karena memuat alur ujian.
     */
    private function denyRetiredStudentPages($method)
    {
        $retired = array(
            'materi', 'tugas', 'bukamateri', 'bukatugas',
            'savelogmateri', 'savelogtugas',
            'savefilemateriselesai', 'savefiletugasselesai',
            'kehadiran', 'jadwalpelajaran',
            'catatan', 'detailcatatan', 'readed',
            'seminggu',
        );
        if (!in_array($method, $retired, TRUE)) {
            return;
        }
        show_error('Modul ini tidak termasuk dalam lingkup CBT.', 403, 'Modul Dinonaktifkan');
    }

    public function afterController()
    {
        $this->ci =& get_instance();
        $controller = strtolower($this->ci->router->fetch_class());
        $method = strtolower($this->ci->router->fetch_method());

        if ($controller === 'siswa' && in_array($method, array('loadsoal', 'loadnomorsoal'), TRUE)) {
            $payload = json_decode($this->ci->output->get_output(), TRUE);
            if (is_array($payload)) {
                $this->removeAnswerKeys($payload);
                $this->removeMatchingAnswerMatrix($payload);
                $this->ci->output->set_output(json_encode($payload));
            }
            return;
        }

        if ($controller === 'cbtjadwal' && $method === 'savejadwal') {
            $payload = json_decode($this->ci->output->get_output());
            if ($payload && !empty($payload->success)) {
                $this->persistSebSettings();
            }
            return;
        }
        if ($controller === 'siswa' && $method === 'applyaction') {
            $payload = json_decode($this->ci->output->get_output());
            $action = json_decode((string) $this->ci->input->post('aksi'));
            $force = $action && isset($action->force) && is_array($action->force) ? $action->force : array();
            if ($payload && !empty($payload->update_selesai) && $force
                && $this->ci->db->table_exists('cbt_exam_attempt')) {
                $idJadwal = (int) $this->ci->input->post('jadwal');
                $rows = $this->ci->db->select('id_siswa')->where('id_jadwal', $idJadwal)
                    ->where_in('id_durasi', $force)->get('cbt_durasi_siswa')->result();
                $ids = array_map(function ($row) { return (int) $row->id_siswa; }, $rows);
                if ($ids) {
                    $this->ci->db->where('id_jadwal', $idJadwal)->where_in('id_siswa', $ids)
                        ->where('status', 'IN_PROGRESS')->update('cbt_exam_attempt', array(
                            'status' => 'SUBMITTED',
                            'submitted_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ));
                }
            }
            return;
        }
        if ($controller !== 'siswa' || $method !== 'selesaiujian') {
            return;
        }

        $payload = json_decode($this->ci->output->get_output());
        /* Attempt hanya boleh SUBMITTED setelah grading benar-benar tersimpan. */
        if (!$payload || empty($payload->status) || empty($payload->status_nilai)) {
            return;
        }
        $this->loadService();
        $student = $this->service->authenticatedStudent();
        $idJadwal = (int) $this->ci->input->post('jadwal');
        if ($student && $idJadwal) {
            $this->service->submit($this->service->attempt($student->id_siswa, $idJadwal));
        }
    }

    private function validateScheduleInput()
    {
        $duration = $this->ci->input->post('durasi_ujian', TRUE);
        if (filter_var($duration, FILTER_VALIDATE_INT) === FALSE || (int) $duration < 1) {
            $this->deny('Durasi ujian harus berupa bilangan menit yang lebih dari nol.', 422);
        }
    }

    private function validateBankInput()
    {
        $fields = array('tampil_pg', 'tampil_kompleks', 'tampil_bs', 'tampil_jodohkan', 'tampil_isian', 'tampil_esai');
        $total = 0;
        foreach ($fields as $field) {
            $count = $this->ci->input->post($field, TRUE);
            if (filter_var($count, FILTER_VALIDATE_INT) === FALSE || (int) $count < 0) {
                $this->deny('Jumlah setiap tipe soal harus berupa bilangan nol atau lebih.', 422);
            }
            $total += (int) $count;
        }
        if ($total < 1) {
            $this->deny('Bank soal harus menampilkan minimal satu soal.', 422);
        }
    }

    /**
     * Siswa::penilaian mengakses $durasi->mulai tanpa memeriksa apakah baris
     * cbt_durasi_siswa sudah ada. Membuka /siswa/penilaian/<jadwal> langsung
     * (tanpa lewat validasisiswa) menghasilkan HTTP 500. Kembalikan peserta ke
     * halaman konfirmasi/token supaya baris durasi dibuat oleh alur normal.
     */
    private function guardExamPage($method)
    {
        if ($method !== 'penilaian') {
            return;
        }
        $idJadwal = (int) $this->ci->uri->segment(3);
        if ($idJadwal <= 0) {
            return;
        }
        $this->loadService();
        if (!$this->service->schemaReady()) {
            return;
        }
        $student = $this->service->authenticatedStudent();
        if (!$student) {
            return; /* Bukan siswa: guardRoleAccess yang menolak. */
        }
        $durasi = $this->ci->db->get_where('cbt_durasi_siswa', array(
            'id_siswa' => (int) $student->id_siswa,
            'id_jadwal' => $idJadwal,
        ))->row();
        if ($durasi) {
            return;
        }
        $this->ci->load->helper('url');
        redirect('siswa/konfirmasi/' . $idJadwal);
    }

    private function scheduleId($method)
    {
        if (in_array($method, array('konfirmasi', 'penilaian'), TRUE)) {
            return (int) $this->ci->uri->segment(3);
        }
        if ($method === 'leavecbt') {
            return (int) $this->ci->uri->segment(3);
        }
        if ($method === 'gettimer' || $method === 'checktimer') {
            return (int) $this->ci->uri->segment(4);
        }
        return (int) $this->ci->input->post('jadwal');
    }

    private function withinScheduleDate($schedule)
    {
        $today = date('Y-m-d');
        return $today >= $schedule->tgl_mulai && $today <= $schedule->tgl_selesai;
    }

    /**
     * PRD FR-14: token hanya boleh dipakai di jendela sesi.
     * Jendela diambil dari cbt_sesi yang terhubung ke peserta lewat
     * cbt_sesi_siswa / cbt_kelas_ruang; bila peserta tidak punya sesi
     * (mis. latihan), pengecekan dilewati supaya ujian tidak gagal total.
     */
    private function withinSessionWindow($schedule, $idSiswa, $method)
    {
        if (!in_array($method, array('validasisiswa', 'penilaian'), TRUE)) {
            return;
        }
        if (!$this->ci->db->table_exists('cbt_sesi_siswa')) {
            return;
        }
        $sesis = $this->ci->db->select('s.waktu_mulai, s.waktu_akhir, s.aktif')
            ->from('cbt_sesi_siswa ss')
            ->join('cbt_sesi s', 's.id_sesi = ss.sesi_id', 'inner')
            ->where('ss.siswa_id', (int) $idSiswa)
            ->order_by('s.waktu_mulai', 'ASC')
            ->get()->result();
        if (!$sesis) {
            return;
        }
        $now = date('H:i:s');
        $jendela = array();
        foreach ($sesis as $sesi) {
            if ((string) $sesi->aktif !== '1') {
                continue;
            }
            $jendela[] = substr($sesi->waktu_mulai, 0, 5) . '-' . substr($sesi->waktu_akhir, 0, 5);
            if ($now >= $sesi->waktu_mulai && $now <= $sesi->waktu_akhir) {
                return;
            }
        }
        if (!$jendela) {
            $this->deny('Sesi ujian Anda belum diaktifkan proktor.', 403);
        }
        $this->deny('Token hanya berlaku pada jendela sesi ' . implode(', ', $jendela) . '.', 403);
    }

    private function validateSebSettings()
    {
        $this->ci->config->load('exam_baku', TRUE);
        $mode = strtoupper(trim((string) $this->ci->config->item('exam_seb_mode', 'exam_baku')));
        $startUrl = trim((string) $this->ci->config->item('exam_seb_start_url', 'exam_baku'));
        $configs = $this->normaliseKeys($this->ci->config->item('exam_seb_config_key', 'exam_baku'));
        $keys = $this->normaliseKeys($this->ci->config->item('exam_seb_browser_exam_keys', 'exam_baku'));
        if ($mode !== 'REQUIRED' || $startUrl === '' || (!$configs && !$keys)) {
            $this->deny('Kebijakan SEB global belum dikonfigurasi. Provisikan Start URL dan key SEB server terlebih dahulu.', 422);
        }
    }

    private function persistSebSettings()
    {
        $this->loadService();
        if (!$this->service->schemaReady()) {
            return;
        }
        $idJadwal = (int) $this->ci->input->post('id_jadwal');
        if ($idJadwal <= 0) {
            $row = $this->ci->db->order_by('id_jadwal', 'DESC')->get_where('cbt_jadwal', array(
                'id_bank' => (int) $this->ci->input->post('bank_id'),
                'id_jenis' => (int) $this->ci->input->post('jenis_id')
            ))->row();
            $idJadwal = $row ? (int) $row->id_jadwal : 0;
        }
        if ($idJadwal <= 0) {
            return;
        }
        $this->ci->config->load('exam_baku', TRUE);
        $mode = strtoupper(trim((string) $this->ci->config->item('exam_seb_mode', 'exam_baku')));
        $configs = $this->normaliseKeys($this->ci->config->item('exam_seb_config_key', 'exam_baku'));
        $keys = $this->normaliseKeys($this->ci->config->item('exam_seb_browser_exam_keys', 'exam_baku'));
        $this->ci->db->where('id_jadwal', $idJadwal)->update('cbt_jadwal', array(
            'seb_mode' => $mode,
            'seb_config_key' => $configs ? implode("\n", $configs) : NULL,
            'seb_browser_exam_keys' => $keys ? implode("\n", $keys) : NULL
        ));
    }

    private function normaliseKeys($raw)
    {
        $keys = preg_split('/[\s,;]+/', strtolower(trim((string) $raw)), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($keys as $key) {
            if (!preg_match('/^[a-f0-9]{64}$/', $key)) {
                $this->deny('Setiap Browser Exam Key harus berupa 64 karakter heksadesimal.', 422);
            }
        }
        return array_values(array_unique($keys));
    }

    private function maintenanceActive()
    {
        return defined('FCPATH')
            && is_file(FCPATH . 'backups' . DIRECTORY_SEPARATOR . '.restore.lock');
    }

    /**
     * /update memasang dan mengubah skema tabel lewat dbforge. Tanpa login,
     * siapa pun yang membuka URL itu dapat menjalankannya (PRD Bab 7.1).
     * Pada instalasi baru (belum ada baris users) akses dibiarkan terbuka
     * karena belum ada akun untuk masuk; setelah admin pertama ada, wajib login.
     */
    private function requireAdmin($message)
    {
        if (!$this->ci->db->table_exists('users')) {
            return;
        }
        if ($this->ci->db->count_all('users') < 1) {
            return;
        }
        $this->ci->load->library('ion_auth');
        if ($this->ci->ion_auth->logged_in() && $this->ci->ion_auth->is_admin()) {
            return;
        }
        show_error($message, 403, 'Akses Dilarang');
    }

    private function loadService()
    {
        if (!$this->service) {
            $this->ci->load->library('Exam_attempt_service');
            $this->service = $this->ci->exam_attempt_service;
        }
    }

    private function removeAnswerKeys(&$value)
    {
        foreach ($value as $key => &$item) {
            if (in_array(strtolower((string) $key), array('jawaban_benar', 'correct_answer', 'answer_key'), TRUE)) {
                unset($value[$key]);
            } elseif (is_array($item)) {
                $this->removeAnswerKeys($item);
            }
        }
    }

    /**
     * Soal menjodohkan menyimpan label dan matriks jawaban benar dalam field
     * yang sama. Browser hanya memerlukan header serta label baris; sel relasi
     * di bagian dalam tidak boleh dikirim karena langsung mengungkap kunci.
     */
    private function removeMatchingAnswerMatrix(&$payload)
    {
        if ((string) ($payload['soal_jenis'] ?? '') !== '3'
            || !isset($payload['soal_opsi']['tabel'])
            || !is_array($payload['soal_opsi']['tabel'])) {
            return;
        }

        foreach ($payload['soal_opsi']['tabel'] as $rowIndex => &$row) {
            if ($rowIndex === 0 || !is_array($row)) {
                continue;
            }
            foreach ($row as $columnIndex => &$cell) {
                if ($columnIndex > 0) {
                    $cell = '';
                }
            }
            unset($cell);
        }
        unset($row);
    }

    private function deny($message, $status, $extra = array())
    {
        $payload = array_merge(array('status' => FALSE, 'message' => $message), $extra);
        $this->ci->output->set_status_header($status)->set_content_type('application/json')->set_output(json_encode($payload));
        $this->ci->output->_display();
        exit;
    }
}
