<?php
// Runs the real legacy query, shuffle, resume, load, save and grading paths without a live DB.
declare(strict_types=1);
define('BASEPATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
#[AllowDynamicProperties] class CI_Model {}
#[AllowDynamicProperties] class CI_Controller {}
require BASEPATH . 'application/models/Cbt_model.php';
require BASEPATH . 'application/controllers/Siswa.php';
require BASEPATH . 'application/libraries/Exam_attempt_service.php';

function check($ok, $message) {
    if (!$ok) { throw new RuntimeException($message); }
}
class Rows {
    private $rows;
    function __construct($rows) { $this->rows = $rows; }
    function result() { return array_map(function ($r) { return (object) $r; }, $this->rows); }
    function row() { return $this->result()[0] ?? null; }
    function num_rows() { return count($this->rows); }
}
class MemoryDb {
    public $tables = [], $filters = [], $source, $joins = [], $sets = [], $inserts = 0;
    public $locks = [], $lockAvailable = true;
    function select($s) { return $this; }
    function from($s) { $this->source = $s; return $this; }
    function join($table, $condition, $kind = '') { $this->joins[] = [$table, $condition]; return $this; }
    function where($key, $value = null) {
        foreach (is_array($key) ? $key : [$key => $value] as $k => $v) { $this->filters[rtrim($k, '=')] = $v; }
        return $this;
    }
    function order_by($key) { return $this; }
    function get_where($table, $filters) { return $this->where($filters)->get($table); }
    function get($table = null) {
        $table = $table ?? $this->source;
        [$name, $alias] = array_pad(explode(' ', $table), 2, '');
        $rows = [];
        foreach ($this->tables[$name] ?? [] as $row) {
            $item = $row;
            foreach ($row as $k => $v) { $item[$alias . '.' . $k] = $v; }
            $rows[] = $item;
        }
        foreach ($this->joins as [$join, $condition]) {
            [$name, $alias] = explode(' ', $join);
            [$left, $right] = explode('=', str_replace(' ', '', $condition));
            $next = [];
            foreach ($rows as $row) {
                foreach ($this->tables[$name] as $other) {
                    $item = array_merge($row, $other);
                    foreach ($other as $k => $v) { $item[$alias . '.' . $k] = $v; }
                    if ($item[$left] == $item[$right]) { $next[] = $item; }
                }
            }
            $rows = $next;
        }
        $rows = array_values(array_filter($rows, function ($row) {
            foreach ($this->filters as $key => $value) {
                if (!array_key_exists($key, $row) || (string) $row[$key] !== (string) $value) { return false; }
            }
            return true;
        }));
        usort($rows, function ($a, $b) { return ($a['no_soal_alias'] ?? 0) <=> ($b['no_soal_alias'] ?? 0); });
        $this->filters = $this->joins = [];
        return new Rows($rows);
    }
    function count_all_results($table) { return $this->get($table)->num_rows(); }
    function insert($table, $row) { $this->tables[$table][] = $row; return true; }
    function insert_batch($table, $rows) {
        $this->inserts++;
        foreach ($rows as $row) {
            $row += ['jawaban_siswa' => null, 'jawaban_alias' => '', 'ragu_ragu' => 0,
                'nilai_koreksi' => 0, 'nilai_otomatis' => 0];
            $this->insert($table, $row);
        }
        return true;
    }
    function set($key, $value) { $this->sets[$key] = $value; return $this; }
    function update($table) {
        foreach ($this->tables[$table] as &$row) {
            $matches = true;
            foreach ($this->filters as $key => $value) { if ((string) $row[$key] !== (string) $value) { $matches = false; } }
            if ($matches) { $row = array_merge($row, $this->sets); }
        }
        $this->filters = $this->sets = [];
        return true;
    }
    function replace($table, $row) { $this->tables[$table][$row['id_nilai']] = $row; return true; }
    function delete($table) {
        $this->tables[$table] = array_filter($this->tables[$table] ?? [], function ($row) {
            foreach ($this->filters as $key => $value) {
                if ((string) $row[$key] !== (string) $value) { return true; }
            }
            return false;
        });
        $this->filters = [];
        return true;
    }
    function trans_start() {}
    function trans_complete() {}
    function trans_status() { return true; }
    function query($sql, $args) {
        $this->locks[] = [$sql, $args];
        return new Rows([['acquired' => $this->lockAvailable ? 1 : 0]]);
    }
}
class Input {
    public $data = [];
    function post($key, $clean = false) { return $this->data[$key] ?? null; }
    function ip_address() { return '127.0.0.1'; }
}
class Output {
    public $status = 200;
    function set_status_header($status) { $this->status = $status; return $this; }
}
class Loader { function model(...$args) {} function library(...$args) {} }
class Agent {
    function is_browser() { return true; }
    function browser() { return 'Test'; }
    function version() { return '1'; }
    function platform() { return 'Test'; }
}
class Dashboard {
    function getTahunActive() { return (object) ['id_tp' => 1]; }
    function getSemesterActive() { return (object) ['id_smt' => 1]; }
}
class ExamModel extends Cbt_model {
    function getJadwalById($id, $sesi = null) { return $this->db->get_where('cbt_jadwal', ['id_jadwal' => $id])->row(); }
    function getElapsed($id) { return $this->db->get_where('cbt_durasi_siswa', ['id_durasi' => $id])->row(); }
    function saveLog($student, $schedule, $type, $text = '') {
        return $this->db->insert('log_ujian', ['id_log' => $student . '0' . $schedule . $type,
            'id_siswa' => $student, 'id_jadwal' => $schedule]);
    }
    function getDataSiswaById($tp, $smt, $id) { return (object) ['id_siswa' => $id]; }
}
class ExamController extends Siswa {
    public $response;
    function __construct() {}
    function output_json($data, $encode = true) { $this->response = $data; }
    function checkTimer($student, $schedule) { return (object) ['mulai' => date('Y-m-d H:i:s')]; }
}

$db = new MemoryDb();
$bank = ['id_bank' => 7, 'opsi' => 4, 'tampil_pg' => 3, 'bobot_pg' => 100];
foreach (['kompleks', 'jodohkan', 'isian', 'esai', 'bs'] as $type) { $bank['tampil_' . $type] = $bank['bobot_' . $type] = 0; }
$db->tables['cbt_bank_soal'] = [$bank];
foreach ([10, 11] as $id) {
    $db->tables['cbt_jadwal'][] = array_merge($bank, ['id_jadwal' => $id,
        'acak_soal' => $id === 10 ? '1' : '0', 'acak_opsi' => $id === 10 ? '1' : '0',
        'reset_login' => '0', 'token' => '0', 'durasi_ujian' => 60]);
}
foreach ([101, 102, 103] as $id) {
    $db->tables['cbt_soal'][] = ['id_soal' => $id, 'bank_id' => 7, 'jenis' => '1',
        'tampilkan' => '1', 'jawaban' => 'A', 'nomor_soal' => $id - 100, 'soal' => 'Question',
        'opsi_a' => 'Correct', 'opsi_b' => 'Wrong', 'opsi_c' => 'Wrong', 'opsi_d' => 'Wrong', 'opsi_e' => ''];
}
$db->tables += ['cbt_soal_siswa' => [], 'cbt_durasi_siswa' => [], 'log_ujian' => [], 'cbt_nilai' => []];
$model = new ExamModel(); $model->db = $db;
$exam = new ExamController();
$exam->db = $db; $exam->cbt = $model; $exam->load = new Loader();
$exam->input = new Input(); $exam->output = new Output(); $exam->agent = new Agent(); $exam->dashboard = new Dashboard();
$class = new ReflectionClass(Exam_attempt_service::class);
$service = $class->newInstanceWithoutConstructor();
$property = $class->getProperty('ci'); $property->setAccessible(true); $property->setValue($service, (object) ['db' => $db]);
function startExam($exam, $schedule) {
    $exam->input->data = ['siswa' => '1', 'jadwal' => (string) $schedule, 'bank' => '7'];
    $exam->validasiSiswa();
    check(!empty($exam->response['izinkan']), 'start/resume rejected');
}
startExam($exam, 10); startExam($exam, 11);
check($model->getJumlahSoalSiswa(7, 1, 10) === 3 && $model->getJumlahSoalSiswa(7, 1, 11) === 3, 'mapping crossed schedules');
check($model->getInfoJadwal(7, 11)->acak_opsi === '0', 'wrong schedule randomization config');
foreach ($model->getALLSoalSiswa(7, 1, 11) as $row) {
    check($row->opsi_alias_a === 'A', 'schedule 11 inherited randomized options');
}
$mapping = serialize($db->tables['cbt_soal_siswa']);
$insertCount = $db->inserts;
for ($i = 0; $i < 5; $i++) { startExam($exam, 10); startExam($exam, 11); }
check(serialize($db->tables['cbt_soal_siswa']) === $mapping && $db->inserts === $insertCount, 'resume regenerated mapping');
echo "PASS: initial mapping, schedule config, repeated resume preserve question/option mappings\n";

$row = $model->getALLSoalSiswa(7, 1, 10)[0];
$data = (object) ['id_soal_siswa' => $row->id_soal_siswa, 'id_soal' => $row->id_soal,
    'id_siswa' => '1', 'id_jadwal' => '10', 'id_bank' => '7', 'jenis' => '1',
    'jawaban_siswa' => 'A', 'jawaban_alias' => 'FORGED'];
$normalized = $service->validateAnswer(clone $data, 1, 10, 7);
check($normalized->jawaban_alias === $row->opsi_alias_a, 'client alias trusted');
foreach (['id_soal_siswa' => '999999', 'id_soal' => '999', 'id_siswa' => '2',
    'id_jadwal' => '11', 'id_bank' => '8', 'jenis' => '2', 'jawaban_siswa' => 'E'] as $field => $bad) {
    $forged = clone $data; $forged->$field = $bad;
    try { $service->validateAnswer($forged, 1, 10, 7); throw new RuntimeException('accepted forged ' . $field); }
    catch (InvalidArgumentException $expected) {}
}
$forged = clone $data;
$forged->id_soal_siswa = $model->getALLSoalSiswa(7, 1, 11)[0]->id_soal_siswa;
try { $service->validateAnswer($forged, 1, 10, 7); throw new RuntimeException('accepted question from another schedule'); }
catch (InvalidArgumentException $expected) {}
$exam->input->data = ['bank' => '7', 'siswa' => '1', 'jadwal' => '10', 'elapsed' => '0',
    'data' => json_encode($normalized)];
$exam->saveJawaban();
check($exam->response['status'] === true && $exam->response['soal_terjawab'] === 1, 'save failed');
check(count(array_filter($model->getJumlahJawaban(7, 1, 11), function ($r) { return $r->jawaban_siswa !== null; })) === 0, 'answer count crossed schedules');
$exam->olahNilai(1, 10); $exam->olahNilai(1, 11);
check($db->tables['cbt_nilai']['1010']['pg_benar'] === 1 && $db->tables['cbt_nilai']['1011']['pg_benar'] === 0, 'grading crossed schedules');
check($db->tables['cbt_nilai']['1010']['pg_nilai'] === 33.33 && $db->tables['cbt_nilai']['1011']['pg_nilai'] === 0.0, 'score crossed schedules');
check(count($model->getJawabanByBank(7, 1, 10)) === 3, 'grading query unscoped');
check(count($model->getJawabanByBank(7)) === 6, 'legacy bank aggregation changed');
echo "PASS: answer validation, server alias, answer counts and actual grading isolated by schedule\n";

$saved = serialize($db->tables['cbt_soal_siswa']);
$exam->input->data = ['siswa' => '1', 'bank' => '7', 'jadwal' => '10', 'nomor' => '1', 'timer' => '0'];
for ($i = 0; $i < 3; $i++) {
    $exam->loadNomorSoal();
    check($exam->response['soal_id'] == $row->id_soal, 'refresh changed question');
    check($exam->response['soal_jawaban_siswa'] === 'A', 'refresh lost saved answer');
}
$exam->input->data['nomor'] = '999';
$exam->loadNomorSoal();
check($exam->output->status === 404 && $exam->response['status'] === false && !isset($exam->response['soal_id']), 'invalid question fell back to first row');
startExam($exam, 10);
check(serialize($db->tables['cbt_soal_siswa']) === $saved, 'resume lost saved answers');
// The old branch deleted mappings when a start log was missing and reset=3.
$db->tables['log_ujian'] = [];
foreach ($db->tables['cbt_durasi_siswa'] as &$duration) { $duration['reset'] = '3'; }
unset($duration);
startExam($exam, 10);
check(serialize($db->tables['cbt_soal_siswa']) === $saved && $db->inserts === $insertCount, 'duration reset regenerated mapping');
echo "PASS: repeated load, invalid-question rejection and saved-answer resume\n";

check($service->lockQuestionMapping(1, 10, 7) === true, 'lock acquisition failed');
$db->lockAvailable = false;
check($service->lockQuestionMapping(1, 10, 7) === false, 'unavailable lock accepted');
check($db->locks[0][1] === ['cbt_mapping_1_10_7'], 'lock scope incorrect');
echo "PASS: lock SQL scope and lock-failure handling (real concurrent DB test still required)\n";

$db->lockAvailable = true;
$exam->exam_attempt_service = $service;
$db->tables['cbt_exam_attempt'] = [['id_siswa' => 1, 'id_jadwal' => 10], ['id_siswa' => 1, 'id_jadwal' => 11]];
$otherSchedule = serialize($model->getALLSoalSiswa(7, 1, 11));
$exam->ulangiUjian('1010', '7');
check($exam->response['status'] === true && $model->getJumlahSoalSiswa(7, 1, 10) === 0, 'proctor reset failed');
check(serialize($model->getALLSoalSiswa(7, 1, 11)) === $otherSchedule, 'proctor reset crossed schedules');
check(count($db->tables['cbt_exam_attempt']) === 1 && reset($db->tables['cbt_exam_attempt'])['id_jadwal'] === 11, 'reset left old attempt or removed another schedule');
startExam($exam, 10);
check($model->getJumlahSoalSiswa(7, 1, 10) === 3, 'explicit proctor reset cannot start fresh');
echo "PASS: explicit proctor reset is scoped and permits fresh mapping\n";
