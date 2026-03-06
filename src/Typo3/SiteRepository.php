<?php

namespace LinkChecker\Typo3;

use Symfony\Component\Yaml\Yaml;
use LinkChecker\Config\Config;

class SiteRepository
{

    private string $sitesPath;

    public function __construct(Config $config)
    {
        $this->sitesPath = rtrim($config->get('typo3')['root_path'], '/') . '/config/sites/';
    }

    public function getSites(): array
    {

        $sites = [];

        foreach (glob($this->sitesPath . '*/config.yaml') as $file) {

            $yaml = Yaml::parseFile($file);

            $sites[] = [
                'base' => $yaml['base'],
                'rootPageId' => $yaml['rootPageId']
            ];

        }

        return $sites;

    }

}