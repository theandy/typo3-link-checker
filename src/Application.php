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

        echo "\nTYPO3 Navigation Link Checker\n";
        echo "---------------------------------\n\n";

        $config = new Config();

        $db = new DatabaseConnection($config);

        $siteRepo = new SiteRepository($config);

        $pageRepo = new PageRepository($db);

        $crawler = new PageCrawler();

        $checker = new NavigationLinkChecker();

        $cacheManager = new Typo3CacheManager($config);

        $mailer = new Mailer($config);


        $sites = $siteRepo->getSites();

        if (!$sites) {

            echo "No sites found.\n";
            return;

        }

        $invalidPages = [];


        foreach ($sites as $site) {

            echo "Checking site: " . $site['base'] . "\n";

            $pages = $pageRepo->getPagesByRoot($site['rootPageId']);

            $total = count($pages);

            echo "Found $total pages\n\n";

            $i = 1;

            foreach ($pages as $page) {

                $url = rtrim($site['base'], '/') . '/' . ltrim($page['slug'], '/');

                echo "[$i/$total] $url\n";

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    echo "   → INVALID LINK FOUND\n";

                    $invalidPages[] = $url;

                }

                $i++;

            }

            echo "\n";

        }


        if (!empty($invalidPages)) {

            echo "---------------------------------\n";
            echo "Invalid links found: " . count($invalidPages) . "\n";
            echo "Flushing TYPO3 cache...\n";

            $cacheManager->flush();

            sleep(5);

            echo "Rechecking pages...\n\n";

            $stillBroken = [];

            foreach ($invalidPages as $url) {

                echo "Recheck: $url\n";

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    echo "   → STILL INVALID\n";

                    $stillBroken[] = $url;

                }

            }


            if (!empty($stillBroken)) {

                echo "\nErrors remain after cache flush\n";

                $mailer->send($stillBroken);

                echo "Mail sent.\n";

            } else {

                echo "\nErrors disappeared after cache flush.\n";

            }

        } else {

            echo "No invalid navigation links found.\n";

        }

        echo "\nFinished.\n\n";

    }

}