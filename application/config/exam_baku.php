<?php
defined('BASEPATH') OR exit('No direct script access allowed');

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
if (isset($sebGlobal['config_key'])) {
    $config['exam_seb_config_key'] = trim($config['exam_seb_config_key'] . "\n"
        . strtolower(trim((string) $sebGlobal['config_key'])));
}
if (isset($sebGlobal['browser_exam_keys'])) {
    $config['exam_seb_browser_exam_keys'] = trim($config['exam_seb_browser_exam_keys'] . "\n"
        . strtolower(trim((string) $sebGlobal['browser_exam_keys'])));
}
unset($sebGlobal, $sebGlobalPath, $localSeb);
