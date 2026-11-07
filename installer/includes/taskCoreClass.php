<?php

require_once dirname(__DIR__, 2) . '/application/libraries/Environment_database_installer.php';

class Core
{
    public function checkEmpty($data)
    {
        return !empty($data['hostname']) && !empty($data['username']) && !empty($data['database']);
    }

    public function show_message($type, $message)
    {
        return $message;
    }

    public function getAllData($data)
    {
        return $data;
    }

    public function write_db_config($data)
    {
        try {
            return (new Environment_database_installer())->writeConfiguration($data);
        } catch (Throwable $e) {
            return false;
        }
    }

    public function checkFile()
    {
        return file_exists(dirname(__DIR__, 2) . '/application/config/database.php');
    }
}
