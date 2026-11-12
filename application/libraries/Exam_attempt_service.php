<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Exam_attempt_service
{
    /* Batas event per attempt per menit. Event keamanan kritis tetap dicatat
       walau batas terlampaui supaya bukti pelanggaran tidak hilang. */
    const EVENT_LIMIT_PER_MINUTE = 120;
    const EVENT_DUPLICATE_WINDOW = 3;

    private $ci;

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->database();
    }

    public function schemaReady()
    {
        return $this->ci->db->table_exists('cbt_exam_attempt')
            && $this->ci->db->field_exists('seb_mode', 'cbt_jadwal');
    }

    public function authenticatedStudent()
    {
        if (!$this->ci->ion_auth->logged_in() || !$this->ci->ion_auth->in_group('siswa')) {
            return NULL;
        }

        $user = $this->ci->ion_auth->user()->row();
        $tp = $this->ci->db->get_where('master_tp', array('active' => 1))->row();
        $smt = $this->ci->db->get_where('master_smt', array('active' => 1))->row();
        if (!$user || !$tp || !$smt) {
            return NULL;
        }

        $this->ci->load->model('Cbt_model', 'exam_cbt');
        $student = $this->ci->exam_cbt->getDataSiswa($user->username, $tp->id_tp, $smt->id_smt);
        if (!$student) {
            return NULL;
        }
        /* getDataSiswa() men-select b.id_siswa dari LEFT JOIN kelas_siswa, jadi
           kolom itu NULL bila siswa belum punya baris kelas. Ambil ulang dari
           master_siswa supaya identitas tidak pernah kosong. */
        if (empty($student->id_siswa)) {
            $row = $this->ci->db->select('id_siswa')->get_where('master_siswa', array('username' => $user->username))->row();
            if (!$row) {
                return NULL;
            }
            $student->id_siswa = $row->id_siswa;
        }
        return $student;
    }

    public function schedule($idJadwal)
    {
        return $this->ci->db->get_where('cbt_jadwal', array('id_jadwal' => (int) $idJadwal))->row();
    }

    public function attempt($idSiswa, $idJadwal)
    {
        if (!$this->schemaReady()) {
            return NULL;
        }
        return $this->ci->db->get_where('cbt_exam_attempt', array(
            'id_siswa' => (int) $idSiswa,
            'id_jadwal' => (int) $idJadwal
        ))->row();
    }

    public function createOrResume($student, $schedule, $sebVerified)
    {
        $existing = $this->attempt($student->id_siswa, $schedule->id_jadwal);
        if ($existing) {
            if ($existing->status === 'IN_PROGRESS') {
                /* Jangan sentuh last_heartbeat_at di sini: bindSession() memakainya
                   untuk menilai apakah PC lama sudah diam sebelum takeover. */
                if ($sebVerified) {
                    $this->ci->db->where('attempt_id', $existing->attempt_id)->update('cbt_exam_attempt', array(
                        'seb_verified' => 1,
                        'updated_at' => date('Y-m-d H:i:s')
                    ));
                }
                return $this->attempt($student->id_siswa, $schedule->id_jadwal);
            }
            return $existing;
        }

        $started = date('Y-m-d H:i:s');
        $legacy = $this->ci->db->get_where('cbt_durasi_siswa', array(
            'id_siswa' => (int) $student->id_siswa,
            'id_jadwal' => (int) $schedule->id_jadwal
        ))->row();
        if ($legacy && !empty($legacy->mulai) && strtotime($legacy->mulai)) {
            $started = date('Y-m-d H:i:s', strtotime($legacy->mulai));
        }

        $deadline = strtotime($started . ' +' . max(1, (int) $schedule->durasi_ujian) . ' minutes');
        if (!empty($schedule->tgl_selesai)) {
            $scheduleEnd = strtotime($schedule->tgl_selesai . ' 23:59:59');
            if ($scheduleEnd && $scheduleEnd < $deadline) {
                $deadline = $scheduleEnd;
            }
        }

        $now = date('Y-m-d H:i:s');
        $sessionNonce = bin2hex(random_bytes(32));
        $attemptId = $this->uuid();
        $inserted = $this->ci->db->insert('cbt_exam_attempt', array(
            'attempt_id' => $attemptId,
            'id_siswa' => (int) $student->id_siswa,
            'id_jadwal' => (int) $schedule->id_jadwal,
            'status' => 'IN_PROGRESS',
            'started_at' => $started,
            'deadline_at' => date('Y-m-d H:i:s', $deadline),
            'session_nonce' => $sessionNonce,
            'device_hash' => $this->hashValue($this->userAgent()),
            'seb_verified' => $sebVerified ? 1 : 0,
            'last_heartbeat_at' => $now,
            'created_at' => $now,
            'updated_at' => $now
        ));
        /* Attempt baru dibuat oleh sesi ini, jadi ikat nonce pada saat yang
           sama. Browser lain tidak boleh menganggap dirinya sebagai binding
           pertama hanya karena session userdata-nya kosong. */
        if ($inserted) {
            $this->ci->session->set_userdata('exam_nonce_' . $attemptId, $sessionNonce);
        }
        return $this->attempt($student->id_siswa, $schedule->id_jadwal);
    }

    public function validateActiveAttempt($attempt, $graceSeconds = 10)
    {
        if (!$attempt || $attempt->status !== 'IN_PROGRESS') {
            return array(FALSE, 'Sesi ujian tidak aktif.');
        }
        if (!empty($attempt->kicked_at)) {
            return array(FALSE, 'Ujian Anda dihentikan oleh proktor. Hubungi pengawas.');
        }
        if (strtotime($attempt->deadline_at) + (int) $graceSeconds < time()) {
            return array(FALSE, 'Waktu ujian sudah berakhir.');
        }
        return array(TRUE, NULL);
    }

    /**
     * FR-20 + NFR "satu akun satu PC": perangkat TIDAK diikat permanen.
     * PC pengganti boleh mengambil alih, tapi hanya bila PC lama sudah diam
     * (heartbeat basi) — kalau PC lama masih hidup, pengambilalihan ditolak
     * supaya akun tidak bisa dipakai dua PC sekaligus.
     */
    public function bindSession($attempt)
    {
        $key = 'exam_nonce_' . $attempt->attempt_id;
        $bound = (string) $this->ci->session->userdata($key);

        if ($bound !== '' && hash_equals((string) $attempt->session_nonce, $bound)) {
            return array(TRUE, NULL);
        }

        /* Perangkat atau sesi baru tidak lagi boleh mengambil alih attempt
           yang heartbeat-nya basi secara otomatis. Percobaan tersebut mengunci
           login dan ditolak agar proktor/admin harus mereset lebih dulu. */
        $this->recordEvent($attempt, 'SESSION_TAKEOVER');
        $this->lockStudentExam($attempt->id_siswa, $attempt->attempt_id, 'Percobaan login dari perangkat baru saat ujian aktif');
        return array(FALSE, 'Ujian ini terkunci karena terdeteksi perangkat/sesi lain. Hubungi pengawas untuk Reset Login.');
    }

    private function sessionIdle($attempt, $seconds = 60)
    {
        if (empty($attempt->last_heartbeat_at)) {
            return TRUE;
        }
        return (time() - strtotime($attempt->last_heartbeat_at)) > $seconds;
    }

    public function elapsed($attempt)
    {
        $seconds = max(0, time() - strtotime($attempt->started_at) - ((int) ($attempt->extra_minutes ?? 0) * 60));
        return sprintf('%02d:%02d:%02d', floor($seconds / 3600), floor(($seconds % 3600) / 60), $seconds % 60);
    }

    public function ownsQuestion($idSoalSiswa, $idSiswa, $idJadwal, $idBank)
    {
        return $this->ci->db->where(array(
            'id_soal_siswa' => (string) $idSoalSiswa,
            'id_siswa' => (int) $idSiswa,
            'id_jadwal' => (int) $idJadwal,
            'id_bank' => (int) $idBank
        ))->count_all_results('cbt_soal_siswa') === 1;
    }

    public function lockQuestionMapping($idSiswa, $idJadwal, $idBank)
    {
        $name = 'cbt_mapping_' . (int) $idSiswa . '_' . (int) $idJadwal . '_' . (int) $idBank;
        $result = $this->ci->db->query('SELECT GET_LOCK(?, 15) AS acquired', array($name));
        if (!$result || (int) $result->row()->acquired !== 1) {
            return FALSE;
        }
        // Hold through the legacy transaction and release even when the request exits early.
        register_shutdown_function(function () use ($name) {
            $this->ci->db->query('SELECT RELEASE_LOCK(?)', array($name));
        });
        return TRUE;
    }

    public function validateAnswer($data, $idSiswa, $idJadwal, $idBank)
    {
        foreach (array('id_soal_siswa', 'id_soal', 'id_siswa', 'id_jadwal', 'id_bank', 'jenis') as $field) {
            $pattern = ($field === 'id_soal_siswa') ? '/^[0-9A-Za-z_-]+$/D' : '/^[1-9][0-9]*$/D';
            if (!is_object($data) || !isset($data->$field)
                || (!is_int($data->$field) && !is_string($data->$field))
                || !preg_match($pattern, (string) $data->$field)) {
                throw new InvalidArgumentException('Identitas jawaban tidak valid.');
            }
        }
        if (!is_object($data) || !isset($data->id_soal_siswa, $data->id_soal,
            $data->id_siswa, $data->id_jadwal, $data->id_bank, $data->jenis)
            || (string) $data->id_siswa !== (string) $idSiswa
            || (string) $data->id_jadwal !== (string) $idJadwal
            || (string) $data->id_bank !== (string) $idBank) {
            throw new InvalidArgumentException('Identitas jawaban tidak sesuai sesi ujian.');
        }
        $row = $this->ci->db->get_where('cbt_soal_siswa', array(
            'id_soal_siswa' => (string) $data->id_soal_siswa,
            'id_siswa' => (int) $idSiswa,
            'id_jadwal' => (int) $idJadwal,
            'id_bank' => (int) $idBank
        ))->row();
        if (!$row || (string) $row->id_soal !== (string) $data->id_soal
            || (string) $row->jenis_soal !== (string) $data->jenis) {
            throw new InvalidArgumentException('Soal atau jenis jawaban tidak sesuai mapping ujian.');
        }
        $data->jenis = (int) $row->jenis_soal;
        if ($data->jenis === 1 || $data->jenis === 6) {
            $answer = $data->jawaban_siswa ?? NULL;
            $allowed = $data->jenis === 6 ? array('B', 'S') : array();
            if ($data->jenis === 1) {
                $question = $this->ci->db->get_where('cbt_soal', array(
                    'id_soal' => $row->id_soal, 'bank_id' => (int) $idBank
                ))->row();
                foreach (array('A', 'B', 'C', 'D', 'E') as $option) {
                    if ($question && !empty($row->{'opsi_alias_' . strtolower($option)})
                        && trim((string) $question->{'opsi_' . strtolower($option)}) !== '') {
                        $allowed[] = $option;
                    }
                }
            }
            if (!is_string($answer) || !in_array($answer, $allowed, TRUE)) {
                throw new InvalidArgumentException('Pilihan jawaban tidak valid.');
            }
            $data->jawaban_alias = $data->jenis === 6 ? $answer
                : $row->{'opsi_alias_' . strtolower($answer)};
        } elseif ($data->jenis === 2) {
            $question = $this->ci->db->get_where('cbt_soal', array(
                'id_soal' => $row->id_soal, 'bank_id' => (int) $idBank
            ))->row();
            $options = $question ? @unserialize($question->opsi_a, array('allowed_classes' => FALSE)) : FALSE;
            $answers = $data->jawaban_siswa ?? NULL;
            if (!is_array($options) || !is_array($answers)
                || count($answers) !== count(array_unique($answers, SORT_REGULAR))) {
                throw new InvalidArgumentException('Pilihan jawaban kompleks tidak valid.');
            }
            $keys = array_map('strtoupper', array_map('strval', array_keys($options)));
            foreach ($answers as $answer) {
                if (!is_string($answer) || !in_array($answer, $keys, TRUE)) {
                    throw new InvalidArgumentException('Pilihan jawaban kompleks tidak valid.');
                }
            }
            $data->jawaban_alias = '';
        }
        return $data;
    }

    public function verifySeb($schedule)
    {
        $global = $this->globalSebSettings();
        $mode = $global['configured'] ? $global['mode'] : strtoupper(isset($schedule->seb_mode) ? $schedule->seb_mode : 'OFF');
        if ($mode === 'OFF' && !$global['configured']) {
            return array('ok' => FALSE, 'mode' => 'REQUIRED', 'verified' => FALSE,
                'reason' => 'Kebijakan SEB global belum dikonfigurasi oleh administrator.');
        }
        if ($mode === 'OFF') {
            return array('ok' => TRUE, 'mode' => $mode, 'verified' => FALSE, 'reason' => NULL);
        }

        $urls = array($this->absoluteRequestUrl());
        $postedUrl = trim((string) $this->ci->input->post('seb_page_url', TRUE));
        if ($postedUrl !== '' && $this->isAllowedExamUrl($postedUrl, $schedule->id_jadwal)) {
            $urls[] = $postedUrl;
        }

        $configHash = $this->header('X-SafeExamBrowser-ConfigKeyHash');
        if (!$configHash) {
            $configHash = trim((string) $this->ci->input->post('seb_config_key_hash', TRUE));
        }
        /* Satu jadwal dapat berisi beberapa Config Key: satu untuk jalur Online
           dan satu untuk jalur LAN, karena Start URL ikut di-hash SEB. */
        $configuredKeys = $global['configured'] ? $global['config_keys'] : preg_split('/[\s,;]+/', strtolower((string) $schedule->seb_config_key), -1, PREG_SPLIT_NO_EMPTY);
        $configKeys = $configuredKeys;
        $configKeys = array_values(array_filter($configKeys, function ($key) {
            return preg_match('/^[a-f0-9]{64}$/', $key);
        }));
        if ($configKeys && $this->matchesHash($configHash, $urls, $configKeys)) {
            return $this->rememberSeb($schedule, $mode);
        }

        $requestHash = $this->header('X-SafeExamBrowser-RequestHash');
        if (!$requestHash) {
            $requestHash = trim((string) $this->ci->input->post('seb_request_hash', TRUE));
        }
        $configuredBrowserKeys = $global['configured'] ? $global['browser_keys'] : preg_split('/[\s,;]+/', strtolower((string) $schedule->seb_browser_exam_keys), -1, PREG_SPLIT_NO_EMPTY);
        $keys = $configuredBrowserKeys;
        $keys = array_values(array_filter($keys, function ($key) {
            return preg_match('/^[a-f0-9]{64}$/', $key);
        }));
        if ($keys && $this->matchesHash($requestHash, $urls, $keys)) {
            return $this->rememberSeb($schedule, $mode);
        }

        $trustedAt = (int) $this->ci->session->userdata('seb_verified_' . (int) $schedule->id_jadwal);
        if ($trustedAt > 0 && time() - $trustedAt <= 300) {
            return array('ok' => TRUE, 'mode' => $mode, 'verified' => TRUE, 'reason' => NULL);
        }

        return array(
            'ok' => $mode === 'OPTIONAL',
            'mode' => $mode,
            'verified' => FALSE,
            'reason' => $global['configured']
                ? 'Safe Exam Browser global belum terverifikasi atau konfigurasi .seb tidak cocok.'
                : 'Kebijakan SEB global belum dikonfigurasi oleh administrator.'
        );
    }

    private function globalSebSettings()
    {
        $this->ci->config->load('exam_baku', TRUE);
        $mode = strtoupper(trim((string) $this->ci->config->item('exam_seb_mode', 'exam_baku')));
        $configKeys = preg_split('/[\s,;]+/', (string) $this->ci->config->item('exam_seb_config_key', 'exam_baku'), -1, PREG_SPLIT_NO_EMPTY);
        $browserKeys = preg_split('/[\s,;]+/', (string) $this->ci->config->item('exam_seb_browser_exam_keys', 'exam_baku'), -1, PREG_SPLIT_NO_EMPTY);
        $valid = function ($key) { return preg_match('/^[a-f0-9]{64}$/', $key); };
        $configKeys = array_values(array_filter($configKeys, $valid));
        $browserKeys = array_values(array_filter($browserKeys, $valid));
        return array(
            'mode' => in_array($mode, array('OFF', 'OPTIONAL', 'REQUIRED'), TRUE) ? $mode : 'REQUIRED',
            'config_keys' => $configKeys,
            'browser_keys' => $browserKeys,
            'configured' => (bool) ($configKeys || $browserKeys)
        );
    }

    /**
     * Login peserta wajib dari dalam Safe Exam Browser. SEB mengirim
     * X-SafeExamBrowser-ConfigKeyHash = sha256(url + configKey) pada setiap
     * permintaan; kuncinya milik salah satu jadwal yang sudah memakai SEB.
     * Bila belum ada kunci tersimpan, cukup user agent SEB yang diperiksa.
     */
    public function verifySebClient()
    {
        /* SEB menyisipkan penanda "SEB/<versi>" di user agent (lihat
           ConfigurationResponsibility.InitializeUserAgent di source SEB).
           "SafeExamBrowser/" tetap diterima untuk klien uji lama. */
        $uaSeb = preg_match('#(?:safeexambrowser|seb)/\d#i', $this->userAgent())
            || preg_match('#(?:safeexambrowser|seb)[_-]?\d#i', $this->userAgent());
        $headerConfigHash = $this->header('X-SafeExamBrowser-ConfigKeyHash');
        $headerBrowserHash = $this->header('X-SafeExamBrowser-RequestHash');
        $hasSebProofHeader = preg_match('/^[a-f0-9]{64}$/i', $headerConfigHash)
            || preg_match('/^[a-f0-9]{64}$/i', $headerBrowserHash);
        if (!$uaSeb && !$hasSebProofHeader) {
            return array(
                'ok' => FALSE,
                'reason' => 'Halaman peserta hanya dapat dibuka melalui Safe Exam Browser. '
                    . 'Buka berkas .seb dari pengawas (klik dua kali), jangan menyalin alamatnya ke Chrome/Edge.'
            );
        }

        $global = $this->globalSebSettings();
        $keys = $global['configured']
            ? array('config' => $global['config_keys'], 'browser' => $global['browser_keys'])
            : $this->registeredSebKeys();
        if (!$keys['config'] && !$keys['browser']) {
            return array('ok' => FALSE, 'reason' => 'Kebijakan SEB global belum dikonfigurasi oleh administrator.');
        }

        $urls = array($this->absoluteRequestUrl());
        if ($this->matchesHash($headerConfigHash, $urls, $keys['config'])) {
            return array('ok' => TRUE, 'reason' => NULL);
        }
        if ($this->matchesHash($headerBrowserHash, $urls, $keys['browser'])) {
            return array('ok' => TRUE, 'reason' => NULL);
        }

        return array('ok' => FALSE, 'reason' => 'Kunci konfigurasi SEB tidak cocok. Unduh ulang berkas .seb dari pengawas.');
    }

    /** Kumpulan Config Key / Browser Exam Key dari semua jadwal SEB. */
    private function registeredSebKeys()
    {
        $config = array();
        $browser = array();
        if (!$this->ci->db->table_exists('cbt_jadwal')
            || !$this->ci->db->field_exists('seb_config_key', 'cbt_jadwal')) {
            return array('config' => $config, 'browser' => $browser);
        }
        $rows = $this->ci->db->select('seb_config_key, seb_browser_exam_keys')
            ->from('cbt_jadwal')->get()->result();
        foreach ($rows as $row) {
            foreach (array('config' => 'seb_config_key', 'browser' => 'seb_browser_exam_keys') as $bucket => $field) {
                $values = preg_split('/[\s,;]+/', strtolower((string) $row->$field), -1, PREG_SPLIT_NO_EMPTY);
                foreach ($values as $key) {
                    if (preg_match('/^[a-f0-9]{64}$/', $key)) {
                        if ($bucket === 'config') {
                            $config[$key] = TRUE;
                        } else {
                            $browser[$key] = TRUE;
                        }
                    }
                }
            }
        }
        return array('config' => array_keys($config), 'browser' => array_keys($browser));
    }

    public function lockStudentExam($idSiswa, $attemptId, $reason = 'Keluar dari sesi ujian atau heartbeat terputus')
    {
        $idSiswa = (int) $idSiswa;
        if ($idSiswa <= 0) {
            return FALSE;
        }
        $now = date('Y-m-d H:i:s');
        $this->ci->db->where('id_siswa', $idSiswa)->update('master_siswa', array(
            'exam_locked_at' => $now,
            'exam_locked_reason' => (string) $reason,
            'exam_locked_attempt_id' => !empty($attemptId) ? (string) $attemptId : NULL
        ));

        if (!empty($attemptId)) {
            $this->ci->db->where('attempt_id', (string) $attemptId)
                ->where('status', 'IN_PROGRESS')
                ->where('kicked_at IS NULL', NULL, FALSE)
                ->update('cbt_exam_attempt', array(
                    'kicked_at' => $now,
                    'updated_at' => $now
                ));
        }
        return TRUE;
    }

    public function unlockStudentExam($idSiswa, $operator = 'admin', $reason = '')
    {
        $idSiswa = (int) $idSiswa;
        if ($idSiswa <= 0) {
            return FALSE;
        }
        $now = date('Y-m-d H:i:s');
        $siswa = $this->ci->db->get_where('master_siswa', array('id_siswa' => $idSiswa))->row();
        if (!$siswa) {
            return FALSE;
        }

        // Hapus login_attempts
        $this->ci->db->where('login', $siswa->username)->delete('login_attempts');

        // Buka kunci di master_siswa
        $this->ci->db->where('id_siswa', $idSiswa)->update('master_siswa', array(
            'exam_locked_at' => NULL,
            'exam_locked_reason' => NULL,
            'exam_locked_attempt_id' => NULL
        ));

        // Cari attempt yang sedang berjalan atau terkait
        $attempt = NULL;
        if (!empty($siswa->exam_locked_attempt_id)) {
            $attempt = $this->ci->db->get_where('cbt_exam_attempt', array('attempt_id' => (string) $siswa->exam_locked_attempt_id))->row();
        }
        if (!$attempt) {
            $attempt = $this->ci->db->where('id_siswa', $idSiswa)
                ->where('status', 'IN_PROGRESS')
                ->order_by('started_at', 'DESC')
                ->limit(1)
                ->get('cbt_exam_attempt')->row();
        }

        if ($attempt) {
            $newNonce = bin2hex(random_bytes(32));
            $this->ci->db->where('attempt_id', $attempt->attempt_id)->update('cbt_exam_attempt', array(
                'kicked_at' => NULL,
                'last_heartbeat_at' => NULL,
                'session_nonce' => $newNonce,
                'updated_at' => $now
            ));
            $this->recordEvent($attempt, 'PROCTOR_UNKICK', array(
                'operator' => $operator,
                'reason' => (string) $reason
            ));
        }

        // Reset device binding legacy
        $this->ci->db->where('id_siswa', $idSiswa)->update('log_ujian', array('reset' => 1));

        return TRUE;
    }

    public function heartbeat($attempt, $sebVerified)
    {
        $now = time();
        $lastHeartbeat = !empty($attempt->last_heartbeat_at) ? strtotime($attempt->last_heartbeat_at) : 0;

        if ($lastHeartbeat > 0 && ($now - $lastHeartbeat) > 90) {
            $this->recordEvent($attempt, 'HEARTBEAT_MISSED', array(
                'gap_seconds' => $now - $lastHeartbeat,
                'last_heartbeat_at' => $attempt->last_heartbeat_at
            ));
            $this->lockStudentExam($attempt->id_siswa, $attempt->attempt_id, 'Kehilangan heartbeat lebih dari 90 detik');
            return array(FALSE, 'Koneksi heartbeat terputus lebih dari 90 detik. Ujian dikunci, hubungi pengawas.');
        }

        $this->ci->db->where('attempt_id', $attempt->attempt_id)->update('cbt_exam_attempt', array(
            'last_heartbeat_at' => date('Y-m-d H:i:s', $now),
            'seb_verified' => $sebVerified ? 1 : (int) $attempt->seb_verified,
            'updated_at' => date('Y-m-d H:i:s', $now)
        ));
        return array(TRUE, NULL);
    }

    /**
     * PRD FR-16: pengawas menambah waktu peserta tertentu + catatan alasan.
     * Waktu tambahan juga digeser ke cbt_durasi_siswa agar timer client ikut.
     */
    public function extend($attempt, $minutes, $reason)
    {
        if (!$attempt || $attempt->status !== 'IN_PROGRESS') {
            return FALSE;
        }
        $minutes = max(1, min(120, (int) $minutes));

        /* Baca ulang attempt di dalam transaksi supaya dua proktor yang
           menambah waktu bersamaan tidak menimpa total satu sama lain. */
        $this->ci->db->trans_begin();
        $locked = $this->ci->db->query(
            'SELECT deadline_at, extra_minutes FROM cbt_exam_attempt
             WHERE attempt_id = ? AND status = ? FOR UPDATE',
            array($attempt->attempt_id, 'IN_PROGRESS')
        )->row();
        if (!$locked) {
            $this->ci->db->trans_rollback();
            return FALSE;
        }
        $total = (int) $locked->extra_minutes + $minutes;
        $deadline = strtotime($locked->deadline_at . ' +' . $minutes . ' minutes');

        $this->ci->db->where('attempt_id', $attempt->attempt_id)->update('cbt_exam_attempt', array(
            'deadline_at' => date('Y-m-d H:i:s', $deadline),
            'extra_minutes' => $total,
            'extra_reason' => (string) $reason,
            'updated_at' => date('Y-m-d H:i:s')
        ));

        /* Timer client dihitung dari cbt_durasi_siswa.mulai (checkTimer), jadi
           mulai digeser maju — bukan lama_ujian dikurangi, karena checkTimer
           menghitung ulang dari mulai dan akan menimpa nilai itu. */
        $legacy = $this->ci->db->get_where('cbt_durasi_siswa', array(
            'id_siswa' => (int) $attempt->id_siswa,
            'id_jadwal' => (int) $attempt->id_jadwal
        ))->row();
        if ($legacy && !empty($legacy->mulai) && strtotime($legacy->mulai)) {
            $this->ci->db->where('id_durasi', $legacy->id_durasi)->update('cbt_durasi_siswa', array(
                'mulai' => date('Y-m-d H:i:s', strtotime($legacy->mulai . ' +' . $minutes . ' minutes'))
            ));
        }

        $this->recordEvent($attempt, 'TIME_EXTENDED', array('minutes' => $minutes, 'reason' => (string) $reason));

        if ($this->ci->db->trans_status() === FALSE) {
            $this->ci->db->trans_rollback();
            return FALSE;
        }
        $this->ci->db->trans_commit();
        return TRUE;
    }

    /**
     * PRD Bab 7 protokol TKA: server yang memutuskan, client hanya poll.
     * kicked_at dibaca validateActiveAttempt() sehingga semua endpoint ujian
     * langsung menolak sampai proktor membuka kembali.
     */
    public function kick($attempt, $reason = '')
    {
        if (!$attempt) {
            return FALSE;
        }
        $this->ci->db->where('attempt_id', $attempt->attempt_id)->update('cbt_exam_attempt', array(
            'kicked_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ));
        $this->recordEvent($attempt, 'PROCTOR_KICK', array('reason' => (string) $reason));
        return TRUE;
    }

    public function unkick($attempt)
    {
        if (!$attempt) {
            return FALSE;
        }
        /* last_heartbeat_at dikosongkan supaya bindSession() mengizinkan
           perangkat mana pun mengikat ulang setelah proktor membuka tendangan. */
        $this->ci->db->where('attempt_id', $attempt->attempt_id)->update('cbt_exam_attempt', array(
            'kicked_at' => NULL,
            'last_heartbeat_at' => NULL,
            'updated_at' => date('Y-m-d H:i:s')
        ));
        $this->recordEvent($attempt, 'PROCTOR_UNKICK');
        return TRUE;
    }

    public function violationCount($attemptId, $type = 'PAGE_HIDDEN')
    {
        return (int) $this->ci->db->where(array(
            'attempt_id' => $attemptId,
            'event_type' => $type
        ))->count_all_results('cbt_exam_event');
    }

    /**
     * Snapshot untuk dashboard pemantauan (FR-22 + fitur aktivitas siswa).
     */
    public function monitorBySchedule($idJadwal)
    {
        $rows = $this->ci->db->select('a.attempt_id, a.id_siswa, a.status, a.started_at, a.deadline_at,
                a.submitted_at, a.kicked_at, a.extra_minutes, a.extra_reason, a.last_heartbeat_at,
                a.seb_verified, s.nis, s.nama, d.lama_ujian, d.status AS status_lama')
            ->from('cbt_exam_attempt a')
            ->join('master_siswa s', 's.id_siswa = a.id_siswa', 'left')
            ->join('cbt_durasi_siswa d', 'd.id_siswa = a.id_siswa AND d.id_jadwal = a.id_jadwal', 'left')
            ->where('a.id_jadwal', (int) $idJadwal)
            ->order_by('s.nama', 'ASC')
            ->get()->result();

        if (!$rows) {
            return array();
        }

        $ids = array();
        foreach ($rows as $row) {
            $ids[] = $row->attempt_id;
        }
        $events = $this->ci->db->select('attempt_id, event_type, COUNT(*) AS jml, MAX(event_time) AS terakhir')
            ->where_in('attempt_id', $ids)
            ->group_by(array('attempt_id', 'event_type'))
            ->get('cbt_exam_event')->result();

        $agg = array();
        foreach ($events as $ev) {
            if (!isset($agg[$ev->attempt_id])) {
                $agg[$ev->attempt_id] = array();
            }
            $agg[$ev->attempt_id][$ev->event_type] = array((int) $ev->jml, $ev->terakhir);
        }

        $now = time();
        $out = array();
        foreach ($rows as $row) {
            $violations = isset($agg[$row->attempt_id]) ? $agg[$row->attempt_id] : array();
            $hidden = isset($violations['PAGE_HIDDEN']) ? $violations['PAGE_HIDDEN'][0] : 0;
            $fullscreen = isset($violations['FULLSCREEN_EXIT']) ? $violations['FULLSCREEN_EXIT'][0] : 0;
            $lastSeen = $row->last_heartbeat_at ? strtotime($row->last_heartbeat_at) : 0;
            $out[] = array(
                'attempt_id' => $row->attempt_id,
                'id_siswa' => (int) $row->id_siswa,
                'nis' => $row->nis,
                'nama' => $row->nama,
                'status' => $row->status,
                'kicked' => !empty($row->kicked_at),
                'started_at' => $row->started_at,
                'deadline_at' => $row->deadline_at,
                'submitted_at' => $row->submitted_at,
                'extra_minutes' => (int) $row->extra_minutes,
                'extra_reason' => $row->extra_reason,
                'seb_verified' => (int) $row->seb_verified,
                'sisa_detik' => max(0, strtotime($row->deadline_at) - $now),
                'detik_sejak_heartbeat' => $lastSeen ? max(0, $now - $lastSeen) : NULL,
                'pelanggaran_tab' => $hidden,
                'pelanggaran_fullscreen' => $fullscreen,
                'total_pelanggaran' => $hidden + $fullscreen,
                'terakhir_pelanggaran' => $this->lastViolation($violations),
                'online' => $lastSeen > 0 && ($now - $lastSeen) <= 90
            );
        }
        return $out;
    }

    private function lastViolation($violations)
    {
        $last = NULL;
        foreach (array('PAGE_HIDDEN', 'FULLSCREEN_EXIT', 'PASTE_ATTEMPT', 'SESSION_TAKEOVER', 'PROCTOR_KICK', 'TIME_EXTENDED') as $type) {
            if (isset($violations[$type]) && ($last === NULL || $violations[$type][1] > $last)) {
                $last = $violations[$type][1];
            }
        }
        return $last;
    }

    public function recordEvent($attempt, $eventType, $metadata = array())
    {
        $allowed = array('PAGE_HIDDEN', 'PAGE_VISIBLE', 'FULLSCREEN_EXIT', 'FULLSCREEN_ENTER', 'OFFLINE', 'ONLINE', 'HEARTBEAT_MISSED', 'LEAVE_ATTEMPT', 'SEB_UNVERIFIED', 'SESSION_TAKEOVER', 'PASTE_ATTEMPT', 'COPY_ATTEMPT', 'CONTEXT_MENU', 'PROCTOR_KICK', 'PROCTOR_UNKICK', 'TIME_EXTENDED', 'RAGU_SET', 'RAGU_UNSET');
        if (!in_array($eventType, $allowed, TRUE)) {
            $eventType = 'HEARTBEAT_MISSED';
        }
        $json = json_encode($metadata);
        if (strlen($json) > 4000) {
            $json = json_encode(array('truncated' => TRUE));
        }
        if ($this->isDuplicateEvent($attempt, $eventType, $json)) {
            return FALSE;
        }
        if (!$this->eventRateAllowed($attempt, $eventType)) {
            return FALSE;
        }
        $now = date('Y-m-d H:i:s');
        $this->ci->db->insert('cbt_exam_event', array(
            'attempt_id' => $attempt->attempt_id,
            'event_type' => $eventType,
            'event_time' => $now,
            'recorded_at' => $now,
            'metadata' => $json,
            'ip_hash' => $this->hashValue($this->ci->input->ip_address()),
            'user_agent_hash' => $this->hashValue($this->userAgent())
        ));
    }

    /**
     * Pengaman log: event non-kritis dari client dibatasi, tetapi event
     * keamanan penting selalu tercatat agar bukti pelanggaran tidak hilang.
     */
    private function criticalEvent($eventType)
    {
        return in_array($eventType, array(
            'SESSION_TAKEOVER', 'PROCTOR_KICK', 'PROCTOR_UNKICK',
            'TIME_EXTENDED', 'PASTE_ATTEMPT',
        ), TRUE);
    }

    private function isDuplicateEvent($attempt, $eventType, $json)
    {
        if ($this->criticalEvent($eventType)) {
            return FALSE;
        }
        $recent = $this->ci->db->select('event_id')
            ->where('attempt_id', $attempt->attempt_id)
            ->where('event_type', $eventType)
            ->where('recorded_at >=', date('Y-m-d H:i:s', time() - self::EVENT_DUPLICATE_WINDOW))
            ->where('metadata', $json)
            ->count_all_results('cbt_exam_event');
        return $recent > 0;
    }

    private function eventRateAllowed($attempt, $eventType)
    {
        if ($this->criticalEvent($eventType)) {
            return TRUE;
        }
        $count = $this->ci->db->where('attempt_id', $attempt->attempt_id)
            ->where('recorded_at >=', date('Y-m-d H:i:s', time() - 60))
            ->count_all_results('cbt_exam_event');
        return $count < self::EVENT_LIMIT_PER_MINUTE;
    }

    public function submit($attempt)
    {
        if (!$attempt) {
            return;
        }
        $now = date('Y-m-d H:i:s');
        $this->ci->db->where('attempt_id', $attempt->attempt_id)->where('status', 'IN_PROGRESS')->update('cbt_exam_attempt', array(
            'status' => 'SUBMITTED',
            'submitted_at' => $now,
            'updated_at' => $now
        ));
    }

    private function rememberSeb($schedule, $mode)
    {
        $this->ci->session->set_userdata('seb_verified_' . (int) $schedule->id_jadwal, time());
        return array('ok' => TRUE, 'mode' => $mode, 'verified' => TRUE, 'reason' => NULL);
    }

    private function matchesHash($provided, $urls, $keys)
    {
        $provided = strtolower(trim((string) $provided));
        if (!preg_match('/^[a-f0-9]{64}$/', $provided)) {
            return FALSE;
        }
        foreach ($urls as $url) {
            foreach ($keys as $key) {
                if (hash_equals(hash('sha256', $url . $key), $provided)) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function isAllowedExamUrl($url, $idJadwal)
    {
        $parts = parse_url($url);
        $base = parse_url(base_url());
        if (!$parts || !$base || empty($parts['host']) || strcasecmp($parts['host'], $base['host']) !== 0) {
            return FALSE;
        }
        $path = isset($parts['path']) ? trim($parts['path'], '/') : '';
        return (bool) preg_match('#(?:index\.php/)?siswa/(?:konfirmasi|penilaian)/' . (int) $idJadwal . '$#i', $path);
    }

    private function absoluteRequestUrl()
    {
        /* Hash SEB dihitung browser dari URL https yang sebenarnya. Di balik
           Cloudflare Tunnel / reverse proxy, Apache melihat permintaan http,
           jadi skema harus diambil dari X-Forwarded-Proto — hanya bila
           permintaan datang dari proxy yang terdaftar di proxy_ips. */
        $https = !empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off';
        if (!$https && $this->trustedProxy() && isset($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
            $forwarded = strtolower(trim(strtok($_SERVER['HTTP_X_FORWARDED_PROTO'], ',')));
            $https = $forwarded === 'https';
        }
        $scheme = $https ? 'https' : 'http';
        $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : parse_url(base_url(), PHP_URL_HOST);
        $uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '/';
        return $scheme . '://' . $host . $uri;
    }

    private function trustedProxy()
    {
        $remote = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
        $configured = $this->ci->config->item('proxy_ips');
        $list = is_array($configured) ? $configured : explode(',', (string) $configured);
        foreach (array_filter(array_map('trim', $list)) as $proxy) {
            if ($proxy === $remote) {
                return TRUE;
            }
            if (strpos($proxy, '/') !== FALSE) {
                list($subnet, $bits) = explode('/', $proxy, 2);
                $ip = ip2long($remote);
                $net = ip2long($subnet);
                $mask = -1 << (32 - (int) $bits);
                if ($ip !== FALSE && $net !== FALSE && (($ip & $mask) === ($net & $mask))) {
                    return TRUE;
                }
            }
        }
        return FALSE;
    }

    private function header($name)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return isset($_SERVER[$key]) ? trim($_SERVER[$key]) : '';
    }

    private function hashValue($value)
    {
        return hash_hmac('sha256', (string) $value, (string) $this->ci->config->item('encryption_key'));
    }

    private function userAgent()
    {
        return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'unknown';
    }

    private function uuid()
    {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
