<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/*
| -------------------------------------------------------------------
| Konfigurasi baku ujian (PRD Bab 4)
| -------------------------------------------------------------------
| Nilai hasil rapat sekolah 28 Oktober 2026. Ubah di sini bila sekolah
| menetapkan angka baru lewat rapat; perubahan wajib dicatat di berita
| acara. Jangan menyebar angka ini ke controller / view.
*/
$config['exam_baku_eksata']     = 25; // jumlah soal mapel Eksata
$config['exam_baku_non_eksata'] = 30; // jumlah soal mapel Non-eksata
$config['exam_baku_durasi']     = 60; // menit per mata pelajaran
$config['exam_baku_kapasitas']  = 25; // maksimal peserta per sesi (lab)

/* Kebijakan SEB global. Rahasia wajib diprovisikan di environment server;
 * jangan menaruh key di repository atau input per jadwal. */
$sebGlobal = array();
$sebGlobalPath = APPPATH . 'config/.seb_global.php';
if (is_file($sebGlobalPath)) {
    $localSeb = require $sebGlobalPath;
    if (is_array($localSeb)) {
        $sebGlobal = $localSeb;
    }
}
$config['exam_seb_mode'] = 'REQUIRED';
$config['exam_seb_start_url'] = trim((string) getenv('GARUDACBT_SEB_START_URL'));
$config['exam_seb_config_key'] = strtolower(trim((string) getenv('GARUDACBT_SEB_CONFIG_KEY')));
$config['exam_seb_browser_exam_keys'] = strtolower(trim((string) getenv('GARUDACBT_SEB_BROWSER_EXAM_KEYS')));
if ($config['exam_seb_start_url'] === '' && isset($sebGlobal['start_url'])) {
    $config['exam_seb_start_url'] = trim((string) $sebGlobal['start_url']);
}
if ($config['exam_seb_config_key'] === '' && isset($sebGlobal['config_key'])) {
    $config['exam_seb_config_key'] = strtolower(trim((string) $sebGlobal['config_key']));
}
if ($config['exam_seb_browser_exam_keys'] === '' && isset($sebGlobal['browser_exam_keys'])) {
    $config['exam_seb_browser_exam_keys'] = strtolower(trim((string) $sebGlobal['browser_exam_keys']));
}
unset($sebGlobal, $sebGlobalPath, $localSeb);
