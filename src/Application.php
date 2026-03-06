<?php

namespace LinkChecker;

use LinkChecker\Config\Config;
use LinkChecker\Infrastructure\DatabaseConnection;
use LinkChecker\Infrastructure\Mailer;
use LinkChecker\Infrastructure\Typo3CacheManager;
use LinkChecker\Typo3\SiteRepository;
use LinkChecker\Typo3\PageRepository;
use LinkChecker\Crawler\PageCrawler;
use LinkChecker\Checker\NavigationLinkChecker;

class Application
{

    public function run(): void
    {

        $config = new Config();

        $db = new DatabaseConnection($config);

        $siteRepo = new SiteRepository($config);

        $pageRepo = new PageRepository($db);

        $crawler = new PageCrawler();

        $checker = new NavigationLinkChecker();

        $cacheManager = new Typo3CacheManager($config);

        $mailer = new Mailer($config);


        $sites = $siteRepo->getSites();

        $invalidPages = [];


        foreach ($sites as $site) {

            $pages = $pageRepo->getPagesByRoot($site['rootPageId']);

            foreach ($pages as $page) {

                $url = rtrim($site['base'], '/') . '/' . ltrim($page['slug'], '/');

                echo "Checking: $url\n";

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    echo "   → INVALID LINK FOUND\n";

                    $invalidPages[] = $url;

                }
            }

        }


        if (!empty($invalidPages)) {

            $cacheManager->flush();

            sleep(5);

            $stillBroken = [];

            foreach ($invalidPages as $url) {

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    $stillBroken[] = $url;

                }
            }


            if (!empty($stillBroken)) {

                $mailer->send($stillBroken);

            }

        }

    }

}