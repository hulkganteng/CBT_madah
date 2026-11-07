<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$active_group = 'default';
$query_builder = TRUE;

$dbCommon = array(
    'dsn'	=> '',
    'hostname' => 'localhost',
    'username' => 'root',
    'password' => '',
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
    'database' => 'garuda_cbt_development',
    'db_debug' => TRUE,
));
$db['production'] = array_merge($dbCommon, array(
    'database' => 'garuda_cbt_production',
));
$db['default'] = in_array(ENVIRONMENT, array('development', 'testing'), TRUE)
    ? $db['development']
    : $db['production'];
unset($dbCommon);
