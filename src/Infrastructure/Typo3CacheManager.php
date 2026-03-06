<?php

namespace LinkChecker\Infrastructure;

use LinkChecker\Config\Config;

class Typo3CacheManager
{

    private string $typo3Path;

    public function __construct(Config $config)
    {
        $this->typo3Path = rtrim($config->get('typo3')['root_path'], '/');
    }

    public function flush(): void
    {

        $command = $this->typo3Path . '/vendor/bin/typo3 cache:flush';

        exec($command);

    }

}