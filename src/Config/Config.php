<?php

namespace LinkChecker\Config;

class Config
{

    private array $config;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/config.php';
    }

    public function get(string $key)
    {
        return $this->config[$key] ?? null;
    }

}