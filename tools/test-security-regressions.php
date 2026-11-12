<?php

declare(strict_types=1);

define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);

require_once BASEPATH . 'application/hooks/Exam_security_hook.php';
require_once BASEPATH . 'application/libraries/Exam_attempt_service.php';
require_once BASEPATH . 'application/libraries/Excel_question_importer.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        fwrite(STDERR, "FAIL: {$message}\n");
        exit(1);
    }
}

function privateCall(object $object, string $method, array &$argument): void
{
    $reflection = new ReflectionMethod($object, $method);
    $reflection->setAccessible(true);
    $arguments = array(&$argument);
    $reflection->invokeArgs($object, $arguments);
}

final class SecurityTestSession
{
    public $values = array();

    public function userdata(string $key)
    {
        return $this->values[$key] ?? null;
    }

    public function set_userdata(string $key, string $value): void
    {
        $this->values[$key] = $value;
    }
}

final class SecurityTestDb
{
    public $allowAttemptUpdate = false;
    private $affected = 0;

    public function where(...$arguments): self { return $this; }
    public function or_where(...$arguments): self { return $this; }
    public function group_start(): self { return $this; }
    public function group_end(): self { return $this; }

    public function update(string $table, array $data): bool
    {
        $this->affected = $table === 'cbt_exam_attempt' && $this->allowAttemptUpdate ? 1 : 0;
        return true;
    }

    public function insert(string $table, array $data): bool
    {
        return true;
    }

    public function affected_rows(): int
    {
        return $this->affected;
    }
}

final class SecurityTestConfig
{
    public function item(string $key): string { return 'test-encryption-key'; }
}

final class SecurityTestInput
{
    public function ip_address(): string { return '127.0.0.1'; }
}

function examService(SecurityTestSession $session, SecurityTestDb $db): Exam_attempt_service
{
    $serviceClass = new ReflectionClass(Exam_attempt_service::class);
    $service = $serviceClass->newInstanceWithoutConstructor();
    $ci = (object) array(
        'session' => $session,
        'db' => $db,
        'config' => new SecurityTestConfig(),
        'input' => new SecurityTestInput(),
    );
    $property = $serviceClass->getProperty('ci');
    $property->setAccessible(true);
    $property->setValue($service, $ci);
    return $service;
}

$hook = new Exam_security_hook();
$payload = array(
    'soal_jenis' => '3',
    'jawaban_benar' => 'SECRET',
    'soal_opsi' => array(
        'tabel' => array(
            array('#', 'Kolom 1', 'Kolom 2'),
            array('Baris 1', '1', '0'),
            array('Baris 2', '0', '1'),
        ),
    ),
);

privateCall($hook, 'removeAnswerKeys', $payload);
privateCall($hook, 'removeMatchingAnswerMatrix', $payload);

assertTrue(!isset($payload['jawaban_benar']), 'field kunci jawaban masih ada');
assertTrue($payload['soal_opsi']['tabel'] === array(
    array('#', 'Kolom 1', 'Kolom 2'),
    array('Baris 1', '', ''),
    array('Baris 2', '', ''),
), 'matriks jawaban menjodohkan belum dibersihkan');

$hookSource = file_get_contents(BASEPATH . 'application/hooks/Exam_security_hook.php');
$serviceSource = file_get_contents(BASEPATH . 'application/libraries/Exam_attempt_service.php');
$guardSource = file_get_contents(BASEPATH . 'application/controllers/Examguard.php');
$excelSource = file_get_contents(BASEPATH . 'application/controllers/Excelsoal.php');
$examActivitySource = file_get_contents(BASEPATH . 'application/controllers/Examactivity.php');
$authSource = file_get_contents(BASEPATH . 'application/controllers/Auth.php');
$loginSiswaSource = file_get_contents(BASEPATH . 'application/views/auth/login_siswa.php');
$guruSidebarSource = file_get_contents(BASEPATH . 'application/views/members/guru/templates/sidebar.php');
$adminSidebarSource = file_get_contents(BASEPATH . 'application/views/_templates/dashboard/sidebar.php');

assertTrue(strpos($serviceSource, 'HTTP_X_FORWARDED_PROTO') !== false, 'hash SEB mengabaikan HTTPS di balik reverse proxy');
assertTrue(strpos($hookSource, "'detail' => array('attempt_uri' => 3)") !== false, 'guard IDOR detail hilang');
assertTrue(strpos($hookSource, "preg_match('/^[1-9][0-9]*$/D', \$item)") !== false, 'parser ID jadwal tidak strict');
assertTrue(strpos($hookSource, "\$controller === 'usersiswa'") !== false, 'RBAC manajemen akun siswa hilang');
assertTrue(strpos($hookSource, "\$method !== 'editlogin'") !== false, 'RBAC manajemen akun guru hilang');
assertTrue(strpos($hookSource, "\$controller === 'cbtrekap'") !== false, 'rekap guru dan endpoint olah nilai siswa tercampur');
assertTrue(strpos($hookSource, "array('resettimer', 'ulangiujian', 'applyaction', 'olahnilai')") !== false, 'RBAC aksi pengawas hilang');
assertTrue(strpos($hookSource, "scopeLegacyProctorAction(\$idJadwal)") !== false, 'child object aksi pengawas tidak divalidasi');
assertTrue(strpos($hookSource, "foreach (array('cbt_nilai', 'cbt_soal_siswa', 'log_ujian') as \$table)") !== false, 'penghapusan ulangi ujian tidak dibatasi jadwal');
assertTrue(strpos($hookSource, "\$action->ulang = array()") !== false, 'penghapusan lintas jadwal legacy masih dapat dijalankan');
assertTrue(strpos($hookSource, "\$requestedBank !== (int) \$schedule->id_bank") !== false, 'bank aksi ulangi tidak terikat jadwal');
assertTrue(strpos($hookSource, "\$requested !== (int) \$teacher->id_guru") !== false, 'otorisasi profil guru hilang');
assertTrue(strpos($hookSource, "\$method === 'hapussoal' || \$method === 'uploadfile'") !== false, 'otorisasi objek soal hilang');
assertTrue(strpos($hookSource, "strpos(\$path, 'uploads/bank_soal/')") !== false, 'pembatasan folder lampiran soal hilang');
assertTrue(strpos($hookSource, "\$aturan = array('post' => array('id_jadwal'))") !== false, 'bank jadwal masih diperlakukan sebagai ID jadwal');
assertTrue(strpos($hookSource, "'addbank', 'editbank', 'savebank', 'deletebank'") !== false, 'guru masih dapat mengelola bank soal');
assertTrue(strpos($hookSource, "'template' => 'bank_id'") !== false, 'template bank tidak memakai pemeriksaan kepemilikan');
assertTrue(strpos($hookSource, "\$controller === 'examactivity'") !== false, 'guru masih dapat membuka pemantauan aktivitas ujian');
assertTrue(strpos($examActivitySource, "Hanya Administrator yang dapat memantau aktivitas ujian") !== false, 'controller aktivitas ujian masih menerima guru');
assertTrue(strpos($guruSidebarSource, "link\":\"examactivity") === false, 'menu aktivitas ujian masih tampil untuk guru');
assertTrue(strpos($hookSource, "'dataalumni', 'dataekstra'") !== false, 'modul ekstrakurikuler masih dapat diakses langsung');
assertTrue(strpos($adminSidebarSource, "'link':\"dataekstra\"") === false, 'menu ekstrakurikuler masih tampil di sidebar admin');
assertTrue(strpos($excelSource, "array('jenis' => 1, 'total' => (int) \$bank->tampil_pg)") !== false, 'template tidak mengikuti kuota bank dari admin');
assertTrue(strpos($hookSource, "\$this->guardFileDeletion(\$controller, \$method)") !== false, 'pemeriksaan hapus berkas tidak dipanggil');
assertTrue(strpos($hookSource, "'siswa' => array('deletefile' => 'uploads/file_siswa/')") !== false, 'siswa masih dapat menghapus berkas di luar folder unggahannya');
assertTrue(strpos($hookSource, "'kelasmateri' => array('deletefile' => 'uploads/materi/')") !== false, 'guru masih dapat menghapus berkas materi di luar foldernya');
assertTrue(strpos($hookSource, "strpos(\$path, '..') === FALSE") !== false, 'path traversal pada hapus berkas tidak ditolak');
assertTrue(strpos($hookSource, "'savekopberita', 'uploadfile', 'deletefile'") !== false, 'guru masih dapat mengubah kop dan logo cetak sekolah');
assertTrue(strpos($hookSource, "\$this->guardSoalImageUpload()") !== false, 'nama berkas gambar soal tidak diperiksa');
assertTrue(strpos($hookSource, "\$name !== basename(\$name)") !== false, 'path traversal pada unggah gambar soal tidak ditolak');
assertTrue(strpos($hookSource, "getimagesizefromstring(\$decoded)") !== false, 'isi unggahan gambar soal tidak diverifikasi sebagai gambar');
assertTrue(strpos($hookSource, "array('data', 'datatable')") !== false, 'daftar bank soal guru lain masih dapat dibaca');
assertTrue(strpos($hookSource, "\$this->guardExamPage(\$method)") !== false, 'halaman ujian tanpa baris durasi masih menghasilkan 500');
assertTrue(strpos($hookSource, "redirect('siswa/konfirmasi/' . \$idJadwal)") !== false, 'peserta tidak diarahkan ke konfirmasi saat durasi belum ada');
assertTrue(strpos($hookSource, "\$this->guardSebStudentPortal(\$controller, \$method)") !== false, 'login peserta masih dapat dibuka tanpa Safe Exam Browser');
assertTrue(strpos($hookSource, "\$this->service->verifySebClient()") !== false, 'kunci SEB login peserta tidak diverifikasi');
assertTrue(strpos($serviceSource, "#(?:safeexambrowser|seb)/\\d#i") !== false, 'penanda user agent SEB tidak dikenali');
assertTrue(strpos($authSource, '$grupPeserta ? "login-siswa" : "login"') !== false, 'logout peserta masih diarahkan ke login staf');
assertTrue(strpos($loginSiswaSource, "base_url('login')") === false, 'halaman login peserta masih menautkan login admin/guru');
assertTrue(strpos(file_get_contents(BASEPATH . 'application/config/config.php'), "'student_portal_requires_seb'") !== false, 'sakelar wajib-SEB login peserta hilang');

$importer = new Excel_question_importer();
$blankRow = new ReflectionMethod('Excel_question_importer', 'isBlankQuestionRow');
$blankRow->setAccessible(true);
$blank = array('no' => '1', 'jenis' => '1', 'soal' => '', 'kunci' => '',
    'opsi_a' => '', 'opsi_b' => '', 'opsi_c' => '', 'opsi_d' => '', 'opsi_e' => '');
assertTrue($blankRow->invoke($importer, $blank) === true, 'baris kerangka template kosong tidak dilewati');
$blank['soal'] = 'Soal terisi';
assertTrue($blankRow->invoke($importer, $blank) === false, 'baris soal terisi ikut dilewati');
assertTrue(strpos($serviceSource, "\$bound === '' ||") === false, 'browser baru masih dapat takeover langsung');
assertTrue(strpos($serviceSource, "->where('session_nonce', (string) \$attempt->session_nonce)") !== false, 'takeover nonce tidak atomik');

$attempt = (object) array(
    'attempt_id' => 'attempt-test',
    'session_nonce' => str_repeat('a', 64),
    'last_heartbeat_at' => date('Y-m-d H:i:s'),
);
$session = new SecurityTestSession();
$db = new SecurityTestDb();
list($freshBound) = examService($session, $db)->bindSession($attempt);
assertTrue($freshBound === false, 'browser kedua mengambil alih heartbeat yang masih aktif');
assertTrue(!isset($session->values['exam_nonce_attempt-test']), 'nonce browser kedua tersimpan saat takeover ditolak');

$attempt->last_heartbeat_at = date('Y-m-d H:i:s', time() - 61);
$staleSession = new SecurityTestSession();
$staleDb = new SecurityTestDb();
$staleDb->allowAttemptUpdate = true;
list($staleBound) = examService($staleSession, $staleDb)->bindSession($attempt);
assertTrue($staleBound === true, 'browser pengganti tidak dapat takeover setelah heartbeat basi');
assertTrue(isset($staleSession->values['exam_nonce_attempt-test']), 'nonce takeover yang sah tidak tersimpan');

$eventStart = strpos($guardSource, 'public function event()');
$raguStart = strpos($guardSource, 'public function ragu()');
$eventSource = substr($guardSource, $eventStart, $raguStart - $eventStart);
$raguSource = substr($guardSource, $raguStart);
foreach (array('verifySeb', 'validateActiveAttempt', 'bindSession') as $control) {
    assertTrue(strpos($eventSource, $control) !== false, "kontrol {$control} hilang dari event");
    assertTrue(strpos($raguSource, $control) !== false, "kontrol {$control} hilang dari ragu");
}

echo "Security regression checks: OK\n";
