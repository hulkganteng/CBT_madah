<?php
// Installer hanya boleh dibuka selama database aplikasi belum bisa dihubungi.
// Setelah terpasang, akses ditolak agar config database tidak bisa diarahkan ulang.
$config = dirname(__DIR__) . '/application/config/database.php';
if (is_file($config)) {
    defined('BASEPATH') OR define('BASEPATH', dirname(__DIR__) . '/system/');
    if (!defined('ENVIRONMENT')) {
        $guard_env = getenv('CI_ENV') ?: NULL;
        $guard_env_file = dirname(__DIR__) . '/.env';
        if ($guard_env === NULL && is_file($guard_env_file)) {
            $guard_local = parse_ini_file($guard_env_file, FALSE, INI_SCANNER_RAW);
            $guard_env = isset($guard_local['CI_ENV']) ? trim($guard_local['CI_ENV']) : NULL;
        }
        define('ENVIRONMENT', $guard_env ?: 'production');
    }
    include $config;
    if (version_compare(PHP_VERSION, '8.1', '>=')) {
        $guard_driver = new mysqli_driver();
        $guard_driver->report_mode = MYSQLI_REPORT_OFF;
    }
    $target = isset($db['default']) ? $db['default'] : array();
    $probe = @new mysqli(
        isset($target['hostname']) ? $target['hostname'] : '',
        isset($target['username']) ? $target['username'] : '',
        isset($target['password']) ? $target['password'] : '',
        isset($target['database']) ? $target['database'] : ''
    );
    if ($probe && !$probe->connect_errno) {
        $probe->close();
        http_response_code(403);
        exit('Installer terkunci: aplikasi sudah terpasang. Gunakan /update untuk pembaruan.');
    }
}
