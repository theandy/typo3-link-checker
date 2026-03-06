<?php

namespace LinkChecker\Infrastructure;

use LinkChecker\Config\Config;

class DatabaseConnection
{

    private \mysqli $connection;

    public function __construct(Config $config)
    {
        $db = $config->get('database');

        $this->connection = new \mysqli(
            $db['host'],
            $db['user'],
            $db['password'],
            $db['dbname']
        );

        if ($this->connection->connect_error) {
            throw new \RuntimeException(
                'MySQL connection failed: ' . $this->connection->connect_error
            );
        }

        $this->connection->set_charset('utf8mb4');
    }

    public function getConnection(): \mysqli
    {
        return $this->connection;
    }
}