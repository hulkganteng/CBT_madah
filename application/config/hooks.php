<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$hook['post_controller_constructor'][] = array(
    'class' => 'Exam_security_hook',
    'function' => 'beforeController',
    'filename' => 'Exam_security_hook.php',
    'filepath' => 'hooks',
    'params' => array()
);

$hook['post_controller'][] = array(
    'class' => 'Exam_security_hook',
    'function' => 'afterController',
    'filename' => 'Exam_security_hook.php',
    'filepath' => 'hooks',
    'params' => array()
);

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/user_guide/general/hooks.html
|
*/
