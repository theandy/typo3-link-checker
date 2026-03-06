<?php

namespace LinkChecker;

use LinkChecker\Config\Config;
use LinkChecker\Infrastructure\DatabaseConnection;
use LinkChecker\Infrastructure\Mailer;
use LinkChecker\Infrastructure\Typo3CacheManager;
use LinkChecker\Infrastructure\Logger;
use LinkChecker\Typo3\SiteRepository;
use LinkChecker\Typo3\PageRepository;
use LinkChecker\Crawler\PageCrawler;
use LinkChecker\Checker\NavigationLinkChecker;

class Application
{

    public function run(): void
    {

        $config = new Config();

        $logger = new Logger($config->get('log')['file']);

        $logger->log("TYPO3 Navigation Link Checker started");


        $db = new DatabaseConnection($config);
        $siteRepo = new SiteRepository($config);
        $pageRepo = new PageRepository($db);
        $crawler = new PageCrawler();
        $checker = new NavigationLinkChecker();
        $cacheManager = new Typo3CacheManager($config);
        $mailer = new Mailer($config);


        $sites = $siteRepo->getSites();

        if (!$sites) {

            $logger->log("No sites found.");
            return;

        }

        $invalidPages = [];


        foreach ($sites as $site) {

            $logger->log("Checking site: " . $site['base']);

            $pages = $pageRepo->getPagesByRoot($site['rootPageId']);

            $total = count($pages);

            $logger->log("Found $total pages");

            $i = 1;

            foreach ($pages as $page) {

                $url = rtrim($site['base'], '/') . '/' . ltrim($page['slug'], '/');

                $logger->log("[$i/$total] $url");

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    $logger->log("INVALID LINK FOUND");

                    $invalidPages[] = $url;

                }

                $i++;

            }

        }


        if (!empty($invalidPages)) {

            $logger->log("Invalid links found: " . count($invalidPages));

            $logger->log("Flushing TYPO3 cache");

            $cacheManager->flush();

            sleep(5);

            $logger->log("Rechecking pages");

            $stillBroken = [];

            foreach ($invalidPages as $url) {

                $logger->log("Recheck: $url");

                $html = $crawler->fetch($url);

                if ($checker->hasInvalidLink($html)) {

                    $logger->log("STILL INVALID");

                    $stillBroken[] = $url;

                }

            }

            if (!empty($stillBroken)) {

                $logger->log("Errors remain after cache flush");

                $mailer->send($stillBroken);

                $logger->log("Mail sent");

            } else {

                $logger->log("Errors disappeared after cache flush");

            }

        } else {

            $logger->log("No invalid navigation links found");

        }

        $logger->log("Finished");

    }

}