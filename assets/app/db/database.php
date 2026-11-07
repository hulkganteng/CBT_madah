<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$dbCommon = array(
    'dsn'	=> '',
    'hostname' => %HOSTNAME%,
    'username' => %USERNAME%,
    'password' => %PASSWORD%,
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
    'database' => %DATABASE_DEVELOPMENT%,
    'db_debug' => TRUE,
));
$db['production'] = array_merge($dbCommon, array(
    'database' => %DATABASE_PRODUCTION%,
));
$db['default'] = in_array(ENVIRONMENT, array('development', 'testing'), TRUE)
    ? $db['development']
    : $db['production'];
unset($dbCommon);
