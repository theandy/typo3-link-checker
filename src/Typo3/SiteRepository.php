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
        $seen = [];

        foreach (glob($this->sitesPath . '*/config.yaml') as $file) {

            $yaml = Yaml::parseFile($file);

            if (empty($yaml['base']) || empty($yaml['rootPageId'])) {
                continue;
            }

            $siteBase = rtrim($yaml['base'], '/');
            $rootPageId = (int)$yaml['rootPageId'];

            if (empty($yaml['languages'])) {
                continue;
            }

            foreach ($yaml['languages'] as $language) {

                $languageId = (int)($language['languageId'] ?? 0);

                $langBase = $language['base'] ?? '/';

                $langBase = trim($langBase, '/');

                if ($langBase === '') {
                    $base = $siteBase;
                } else {
                    $base = $siteBase . '/' . $langBase;
                }

                $base = rtrim($base, '/');

                $key = $base . '_' . $languageId;

                if (!isset($seen[$key])) {

                    $sites[] = [
                        'base' => $base,
                        'rootPageId' => $rootPageId,
                        'languageId' => $languageId
                    ];

                    $seen[$key] = true;

                }

            }

        }

        return $sites;

    }

}