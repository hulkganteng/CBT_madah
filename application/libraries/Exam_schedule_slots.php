<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_schedule_slots
{
    public const GROUPS = ['rombel' => 'Rombel', 'program_mak' => 'Program MAK', 'peminatan' => 'Peminatan'];
    public const PROGRAMS = ['TAHFIDZ', 'KITAB'];

    private $ci;
    private $scheduleSlots = [];
    private $dailyPlans = [];
    private $legacyWindows = [];

    public function __construct()
    {
        $this->ci =& get_instance();
    }

    public static function now()
    {
        return new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
    }

    public static function normalize(array $row, $start, $end)
    {
        $date = $row['tanggal'] ?? '';
        $parsed = is_string($date) && preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $date)
            ? DateTimeImmutable::createFromFormat('!Y-m-d', $date) : FALSE;
        if (!$parsed || $parsed->format('Y-m-d') !== $date || $date < $start || $date > $end) {
            throw new InvalidArgumentException('Tanggal sesi harus berada dalam rentang tanggal jadwal.');
        }

        $group = $row['jenis_sasaran'] ?? '';
        $target = $row['sasaran'] ?? '';
        $session = $row['id_sesi'] ?? '';
        if (!is_string($group) || !isset(self::GROUPS[$group]) || !is_scalar($target)
            || trim((string) $target) === '' || !is_scalar($session) || trim((string) $session) === '') {
            throw new InvalidArgumentException('Sesi, jenis sasaran, dan sasaran peserta wajib diisi.');
        }

        $order = filter_var($row['urutan'] ?? '', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 65535]]);
        $open = $row['jam_buka'] ?? '';
        if (!$order || !is_string($open) || !preg_match('/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/D', $open)) {
            throw new InvalidArgumentException('Isi urutan mapel mulai dari 1 dan jam buka WIB dengan format HH:MM.');
        }

        $flags = [];
        foreach (['wajib_mapel_pertama', 'ubah_sesi'] as $key) {
            $value = $row[$key] ?? '';
            if (!in_array($value, ['', '0', '1', 0, 1], TRUE)) {
                throw new InvalidArgumentException($key . ' hanya boleh bernilai 0 atau 1.');
            }
            $flags[$key] = (int) $value;
        }
        if ($order === 1 && $flags['wajib_mapel_pertama']) {
            throw new InvalidArgumentException('Syarat mapel pertama hanya berlaku mulai urutan 2.');
        }

        return [
            'tanggal' => $date,
            'id_sesi' => trim((string) $session),
            'jenis_sasaran' => $group,
            'sasaran' => trim((string) $target),
            'urutan' => $order,
            'jam_buka' => $open,
        ] + $flags;
    }

    public function ready()
    {
        return $this->ci->db->table_exists('cbt_jadwal_slot');
    }

    public function lockActivePeriod($year, $semester)
    {
        $db = $this->ci->db;
        $activeYear = $db->query('SELECT id_tp FROM master_tp WHERE id_tp = ? AND active = 1 FOR UPDATE', [$year]);
        if (!$activeYear || !$activeYear->num_rows()) {
            return FALSE;
        }
        $activeSemester = $db->query('SELECT id_smt FROM master_smt WHERE id_smt = ? AND active = 1 FOR UPDATE', [$semester]);
        return $activeSemester && $activeSemester->num_rows() > 0;
    }

    public function options($year, $semester)
    {
        $db = $this->ci->db;
        return [
            'sesi' => $db->select('id_sesi AS id, kode_sesi AS kode, nama_sesi AS nama, waktu_akhir')
                ->order_by('waktu_mulai')->get('cbt_sesi')->result_array(),
            'rombel' => $db->select('id_kelas AS id, kode_kelas AS kode, nama_kelas AS nama')
                ->where(['id_tp' => $year, 'id_smt' => $semester])->order_by('nama_kelas')->get('master_kelas')->result_array(),
            'peminatan' => $db->select('id_jurusan AS id, kode_jurusan AS kode, nama_jurusan AS nama')
                ->order_by('nama_jurusan')->get('master_jurusan')->result_array(),
            'program_mak' => array_map(static function ($program) {
                return ['id' => $program, 'kode' => $program, 'nama' => $program];
            }, self::PROGRAMS),
        ];
    }

    public function resolve(array $slot, array $options, $byCode = FALSE)
    {
        $field = $byCode ? 'kode' : 'id';
        $session = $this->reference($options['sesi'], $field, $slot['id_sesi'], 'sesi');
        $target = $this->reference($options[$slot['jenis_sasaran']], $field, $slot['sasaran'], 'sasaran');
        if ($slot['jam_buka'] . ':00' >= $session['waktu_akhir']) {
            throw new InvalidArgumentException('Jam buka harus sebelum jam akhir sesi.');
        }
        $slot['id_sesi'] = (int) $session['id'];
        $slot['sasaran'] = (string) $target['id'];
        return $slot;
    }

    private function reference(array $rows, $field, $value, $label)
    {
        $matches = array_values(array_filter($rows, static function ($row) use ($field, $value) {
            return strcasecmp(trim((string) $row[$field]), $value) === 0;
        }));
        if (count($matches) !== 1) {
            throw new InvalidArgumentException('Pilihan ' . $label . ' tidak ditemukan atau tidak unik.');
        }
        return $matches[0];
    }

    public function forSchedule($id)
    {
        return $this->ready() && $id ? $this->ci->db->where('id_jadwal', $id)
            ->order_by('tanggal, id_sesi, urutan')->get('cbt_jadwal_slot')->result_array() : [];
    }

    public function replace($scheduleId, array $slots)
    {
        $db = $this->ci->db;
        if (!$this->ready()) {
            return !$slots;
        }
        if (!$db->delete('cbt_jadwal_slot', ['id_jadwal' => $scheduleId])) {
            return FALSE;
        }
        return !$slots || (bool) $db->insert_batch('cbt_jadwal_slot', array_map(static function ($slot) use ($scheduleId) {
            return ['id_jadwal' => $scheduleId] + $slot;
        }, $slots));
    }

    public function periodSlots($year, $semester, $date = NULL)
    {
        if (!$this->ready()) {
            return [];
        }
        $db = $this->ci->db;
        if ($date !== NULL) {
            $db->where('sl.tanggal', $date);
        }
        return $db->select('sl.*, j.id_jenis, j.status, j.tgl_mulai, j.tgl_selesai, '
                . 's.nama_sesi, s.aktif, s.waktu_akhir, b.bank_level, b.status AS bank_aktif, '
                . 'b.status_soal, b.soal_agama, m.nama_mapel, j.durasi_ujian, j.jarak')
            ->from('cbt_jadwal_slot sl')
            ->join('cbt_jadwal j', 'j.id_jadwal = sl.id_jadwal')
            ->join('cbt_sesi s', 's.id_sesi = sl.id_sesi', 'left')
            ->join('cbt_bank_soal b', 'b.id_bank = j.id_bank', 'left')
            ->join('master_mapel m', 'm.id_mapel = b.bank_mapel_id', 'left')
            ->where(['j.id_tp' => $year, 'j.id_smt' => $semester])
            ->order_by('sl.tanggal, sl.urutan, sl.id_jadwal')
            ->get()->result_array();
    }

    public function validatePlan(array $existing, array $incoming)
    {
        $seen = [];
        $orders = [];
        $overrides = [];
        $firstSlots = [];
        $dependentSlots = [];
        foreach (array_merge($existing, $incoming) as $slot) {
            $group = $slot['tanggal'] . '|' . $slot['jenis_sasaran'] . '|' . $slot['sasaran'];
            $session = $group . '|' . $slot['id_sesi'];
            $key = $session . '|' . $slot['id_jadwal'];
            $sequence = $session . '|' . $slot['id_jenis'] . '|' . $slot['bank_level'];
            $order = $sequence . '|' . (int) $slot['urutan'];
            if (isset($seen[$key])) {
                throw new InvalidArgumentException('Ada slot duplikat untuk jadwal, tanggal, sesi, dan sasaran yang sama.');
            }
            if (isset($orders[$order])) {
                throw new InvalidArgumentException('Urutan mapel sudah digunakan pada tanggal, sesi, jenis ujian, dan sasaran yang sama.');
            }
            $seen[$key] = TRUE;
            $orders[$order] = TRUE;
            if ((int) $slot['urutan'] === 1) {
                $firstSlots[$sequence] = $slot;
            } elseif (!empty($slot['wajib_mapel_pertama'])) {
                $dependentSlots[$sequence][] = $slot;
            }
            if ((int) $slot['ubah_sesi']) {
                if (isset($overrides[$group]) && $overrides[$group] !== (int) $slot['id_sesi']) {
                    throw new InvalidArgumentException('Satu sasaran hanya dapat dipindah ke satu sesi pada tanggal yang sama.');
                }
                $overrides[$group] = (int) $slot['id_sesi'];
            }
        }

        $changed = array_fill_keys(array_column($incoming, 'id_jadwal'), TRUE);
        foreach ($dependentSlots as $sequence => $laterSlots) {
            $first = $firstSlots[$sequence] ?? NULL;
            if (!$first || empty($first['waktu_akhir'])) {
                continue;
            }
            $open = strtotime($first['tanggal'] . ' ' . $first['jam_buka']);
            $close = strtotime($first['tanggal'] . ' ' . $first['waktu_akhir']);
            if ($open + (int) ($first['jarak'] ?? 0) * 60 < $close) {
                continue;
            }
            foreach ($laterSlots as $later) {
                if (isset($changed[$first['id_jadwal']]) || isset($changed[$later['id_jadwal']])) {
                    throw new InvalidArgumentException(
                        'Slot tanggal ' . $first['tanggal'] . ', sesi ' . ($first['nama_sesi'] ?? $first['id_sesi'])
                        . ': durasi minimal mapel pertama tidak menyisakan waktu untuk mapel urutan '
                        . $later['urutan'] . '. Kurangi durasi minimal atau majukan jam buka mapel pertama.'
                    );
                }
            }
        }
    }

    public function studentContext($studentId, $year, $semester)
    {
        $db = $this->ci->db;
        $row = $db->select('k.id_kelas, s.peminatan_id, s.program_mak, p.sesi_id')
            ->from('kelas_siswa k')
            ->join('master_siswa s', 's.id_siswa = k.id_siswa')
            ->join('cbt_sesi_siswa p', 'p.siswa_id = k.id_siswa AND p.tp_id = k.id_tp AND p.smt_id = k.id_smt', 'left')
            ->where(['k.id_siswa' => $studentId, 'k.id_tp' => $year, 'k.id_smt' => $semester])
            ->get()->row_array();
        if (!$row) {
            return ['groups' => [], 'session' => 0];
        }
        $session = (int) $row['sesi_id'];
        if (!$session) {
            $placements = $db->distinct()->select('id_sesi')->where([
                'id_kelas' => $row['id_kelas'], 'id_tp' => $year, 'id_smt' => $semester,
            ])->where('id_sesi >', 0)->get('cbt_kelas_ruang')->result_array();
            $session = count($placements) === 1 ? (int) $placements[0]['id_sesi'] : 0;
        }
        return [
            'groups' => [
                'rombel' => (string) $row['id_kelas'],
                'program_mak' => strtoupper(trim((string) $row['program_mak'])),
                'peminatan' => (string) $row['peminatan_id'],
            ],
            'session' => $session,
        ];
    }

    public function assignedSlots(array $slots, array $student)
    {
        $matches = [];
        $overrides = [];
        $priorities = ['rombel' => 3, 'program_mak' => 2, 'peminatan' => 1];
        foreach ($slots as $slot) {
            $date = $slot['tanggal'];
            $type = $slot['jenis_sasaran'];
            if (($student['groups'][$type] ?? '') !== $slot['sasaran']
                || (int) $slot['status'] !== 1 || $date < $slot['tgl_mulai'] || $date > $slot['tgl_selesai']) {
                continue;
            }
            $slot['priority'] = $priorities[$type];
            $matches[] = $slot;
            if ((int) $slot['ubah_sesi'] && $slot['priority'] > ($overrides[$date]['priority'] ?? 0)) {
                $overrides[$date] = $slot;
            }
        }

        $assigned = [];
        foreach ($matches as $slot) {
            $date = $slot['tanggal'];
            $id = $slot['id_jadwal'];
            $session = $overrides[$date]['id_sesi'] ?? $student['session'];
            if ((int) $slot['id_sesi'] === (int) $session
                && $slot['priority'] > ($assigned[$date][$id]['priority'] ?? 0)) {
                $assigned[$date][$id] = $slot;
            }
        }
        return $assigned;
    }

    public function bankAvailable(array $slot, $student)
    {
        return (int) $slot['bank_aktif'] === 1 && (int) $slot['status_soal'] === 1
            && (int) $slot['bank_level'] === (int) $student->level_id
            && ($slot['soal_agama'] === '-' || (string) $slot['soal_agama'] === '0' || $slot['soal_agama'] == $student->agama);
    }

    public function startBlock(array $slot, array $assigned, $student, DateTimeImmutable $now)
    {
        if ((int) $slot['aktif'] !== 1) {
            return 'Sesi ujian belum diaktifkan proktor.';
        }
        $time = $now->setTimezone(new DateTimeZone('Asia/Jakarta'))->format('H:i:s');
        $open = substr($slot['jam_buka'], 0, 5);
        if ($time < $open . ':00') {
            return 'Ujian dibuka pukul ' . $open . ' WIB.';
        }
        if ($time >= $slot['waktu_akhir']) {
            return 'Sesi ujian telah berakhir.';
        }
        if (!(int) $slot['wajib_mapel_pertama']) {
            return NULL;
        }

        $this->ci->load->library('Student_exam_eligibility');
        $prerequisite = NULL;
        $bestPriority = 0;
        foreach ($assigned as $first) {
            if ((int) $first['urutan'] !== 1 || $first['id_jenis'] !== $slot['id_jenis']
                || $first['bank_level'] !== $slot['bank_level'] || $first['id_sesi'] !== $slot['id_sesi']
                || !$this->bankAvailable($first, $student)
                || !$this->ci->student_exam_eligibility->isEligible($student->id_siswa, $first['id_jadwal'])) {
                continue;
            }
            $sameGroup = $first['jenis_sasaran'] === $slot['jenis_sasaran'] && $first['sasaran'] === $slot['sasaran'];
            $priority = $sameGroup ? 4 : $first['priority'];
            if ($priority > $bestPriority) {
                $prerequisite = $first;
                $bestPriority = $priority;
            }
        }
        if (!$prerequisite) {
            return 'Jadwal mapel pertama untuk kelompok Anda belum tersedia. Hubungi pengawas.';
        }
        $done = $this->ci->db->where([
            'id_siswa' => $student->id_siswa, 'id_jadwal' => $prerequisite['id_jadwal'], 'status' => 2,
        ])->count_all_results('cbt_durasi_siswa');
        return $done ? NULL : 'Selesaikan ' . ($prerequisite['nama_mapel'] ?: 'mapel pertama') . ' terlebih dahulu.';
    }

    public function legacySessionWindows($studentId, $year, $semester)
    {
        $key = "$studentId|$year|$semester";
        if (isset($this->legacyWindows[$key])) {
            return $this->legacyWindows[$key];
        }
        $db = $this->ci->db;
        if (!$db->table_exists('cbt_sesi_siswa')) {
            return $this->legacyWindows[$key] = [];
        }
        $windows = $db->select('s.waktu_mulai, s.waktu_akhir, s.aktif')
            ->from('cbt_sesi_siswa ss')->join('cbt_sesi s', 's.id_sesi = ss.sesi_id')
            ->where(['ss.siswa_id' => $studentId, 'ss.tp_id' => $year, 'ss.smt_id' => $semester])
            ->order_by('s.waktu_mulai, s.waktu_akhir, s.id_sesi')->get()->result_array();
        if (!$windows) {
            $session = $this->studentContext($studentId, $year, $semester)['session'];
            if ($session) {
                $windows = $db->select('waktu_mulai, waktu_akhir, aktif')
                    ->get_where('cbt_sesi', ['id_sesi' => $session])->result_array();
            }
        }
        return $this->legacyWindows[$key] = $windows;
    }

    public function sessionEndForAttempt($studentId, $schedule, DateTimeImmutable $started)
    {
        $started = $started->setTimezone(new DateTimeZone('Asia/Jakarta'));
        $date = $started->format('Y-m-d');
        $scheduleId = $schedule->id_jadwal;
        $year = $schedule->id_tp;
        $semester = $schedule->id_smt;
        $this->scheduleSlots[$scheduleId] ??= (bool) $this->forSchedule($scheduleId);
        if ($this->scheduleSlots[$scheduleId]) {
            $key = "$year|$semester|$date";
            $this->dailyPlans[$key] ??= $this->periodSlots($year, $semester, $date);
            $context = $this->studentContext($studentId, $year, $semester);
            $slot = $this->assignedSlots($this->dailyPlans[$key], $context)[$date][$scheduleId] ?? NULL;
            if (!empty($slot['waktu_akhir'])) {
                return $started->modify($slot['waktu_akhir']);
            }
        }

        $time = $started->format('H:i:s');
        $windows = $this->legacySessionWindows($studentId, $year, $semester);
        $inactiveEnd = NULL;
        foreach ($windows as $window) {
            if ($time >= $window['waktu_mulai'] && $time <= $window['waktu_akhir']) {
                $end = $started->modify($window['waktu_akhir']);
                if ((int) $window['aktif'] === 1 && $time < $window['waktu_akhir']) {
                    return $end;
                }
                $inactiveEnd ??= $end;
            }
        }
        // Penonaktifan sesi tidak menghapus batas untuk attempt yang sudah dibuat.
        return $inactiveEnd ?? (count($windows) === 1 ? $started->modify($windows[0]['waktu_akhir']) : NULL);
    }
}
