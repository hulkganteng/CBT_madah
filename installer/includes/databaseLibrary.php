<?php

require_once dirname(__DIR__, 2) . '/application/libraries/Environment_database_installer.php';

class Database
{
    private $installer;

    public function __construct()
    {
        $this->installer = new Environment_database_installer();
    }

    public function install($data)
    {
        return $this->installer->installAndConfigure($data);
    }
}
