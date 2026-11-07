<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

/*
 * Kredensial diambil dari environment (DB_*) atau .env lokal supaya
 * password tidak perlu ditulis di repositori. Nilai bawaan dipertahankan
 * agar instalasi LAN lama tetap berjalan. Jangan pernah mencetak password
 * ke log atau halaman.
 */
$dbEnv = array();
$dbEnvFile = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . '.env';
if (is_file($dbEnvFile) && is_readable($dbEnvFile)) {
    $dbEnvParsed = parse_ini_file($dbEnvFile, FALSE, INI_SCANNER_RAW);
    if (is_array($dbEnvParsed)) {
        $dbEnv = $dbEnvParsed;
    }
}
$dbValue = function ($key, $fallback) use ($dbEnv) {
    $value = getenv($key);
    if ($value === FALSE || $value === '') {
        $value = isset($dbEnv[$key]) ? $dbEnv[$key] : NULL;
    }
    return ($value === NULL || $value === '') ? $fallback : $value;
};

$dbCommon = array(
    'dsn'	=> '',
    'hostname' => $dbValue('DB_HOSTNAME', 'localhost'),
    'username' => $dbValue('DB_USERNAME', 'root'),
    'password' => $dbValue('DB_PASSWORD', ''),
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => FALSE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8',
    'dbcollat' => 'utf8_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);

$db['development'] = array_merge($dbCommon, array(
    'database' => $dbValue('DB_DATABASE_DEVELOPMENT', 'garuda_cbt_development'),
    'db_debug' => TRUE,
));
$db['production'] = array_merge($dbCommon, array(
    'database' => $dbValue('DB_DATABASE_PRODUCTION', 'garuda_cbt_production'),
));
$db['default'] = in_array(ENVIRONMENT, array('development', 'testing'), TRUE)
    ? $db['development']
    : $db['production'];

if (ENVIRONMENT === 'production' && $dbCommon['username'] === 'root') {
    /* Tidak mengubah kredensial secara otomatis. Peringatkan operator agar
       membuat akun khusus dengan privilege minimum (lihat .env.example). */
    $dbWarning = 'GarudaCBT: database produksi masih memakai akun root. Set DB_USERNAME/DB_PASSWORD.';
    if (function_exists('log_message')) {
        log_message('error', $dbWarning);
    } else {
        error_log($dbWarning);
    }
    unset($dbWarning);
}
unset($dbCommon, $dbValue, $dbEnv, $dbEnvFile, $dbEnvParsed);