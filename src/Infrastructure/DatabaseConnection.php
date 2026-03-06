<?php

namespace LinkChecker\Infrastructure;

use Doctrine\DBAL\DriverManager;
use LinkChecker\Config\Config;

class DatabaseConnection
{

    private $connection;

    public function __construct(Config $config)
    {

        $db = $config->get('database');

        $this->connection = DriverManager::getConnection([
            'dbname' => $db['dbname'],
            'user' => $db['user'],
            'password' => $db['password'],
            'host' => $db['host'],
            'driver' => 'pdo_mysql'
        ]);

    }

    public function getConnection()
    {
        return $this->connection;
    }

}