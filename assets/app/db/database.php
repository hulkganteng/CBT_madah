<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

/* DB_* dari environment/.env menimpa nilai hasil installer. Password tidak
   perlu ditulis ulang saat pindah server. Nilai %...% adalah bawaan aman. */
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
    'hostname' => $dbValue('DB_HOSTNAME', %HOSTNAME%),
    'username' => $dbValue('DB_USERNAME', %USERNAME%),
    'password' => $dbValue('DB_PASSWORD', %PASSWORD%),
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
    'database' => $dbValue('DB_DATABASE_DEVELOPMENT', %DATABASE_DEVELOPMENT%),
    'db_debug' => TRUE,
));
$db['production'] = array_merge($dbCommon, array(
    'database' => $dbValue('DB_DATABASE_PRODUCTION', %DATABASE_PRODUCTION%),
));
$db['default'] = in_array(ENVIRONMENT, array('development', 'testing'), TRUE)
    ? $db['development']
    : $db['production'];
unset($dbCommon, $dbValue, $dbEnv, $dbEnvFile, $dbEnvParsed);