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

            if (!isset($yaml['base'])) {
                continue;
            }

            $base = rtrim($yaml['base'], '/');
            $rootPageId = $yaml['rootPageId'] ?? 0;

            if (!$rootPageId) {
                continue;
            }

            /*
             * Default Sprache
             */

            $sites[] = [
                'base' => $base,
                'rootPageId' => $rootPageId
            ];

            /*
             * Weitere Sprachen
             */

            if (!empty($yaml['languages'])) {

                foreach ($yaml['languages'] as $language) {

                    if (!empty($language['base'])) {

                        $langBase = trim($language['base'], '/');

                        $sites[] = [
                            'base' => $base . '/' . $langBase,
                            'rootPageId' => $rootPageId
                        ];
                    }
                }

            }

        }

        return $sites;

    }

}