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

        $logger = new Logger(
            $config->get('log')['file'],
            $config->get('log')['overwrite']
        );

        $logger->log("TYPO3 Navigation Link Checker started");

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

            $logger->log("Checking site: " . $site['base']);

            $pages = $pageRepo->getPagesByRoot(
                $site['rootPageId'],
                $site['languageId']
            );

            $totalPages = count($pages);

            $logger->log("Found $totalPages pages");

            $siteLinkCount = 0;
            $siteInvalidCount = 0;

            $i = 1;

            foreach ($pages as $page) {

                $url = rtrim($site['base'], '/') . '/' . ltrim($page['slug'], '/');

                $logger->log("[$i/$totalPages] $url");

                $html = $crawler->fetch($url);

                $linkCount = $checker->countNavigationLinks($html);
                $invalidCount = $checker->countInvalidLinks($html);

                $siteLinkCount += $linkCount;
                $siteInvalidCount += $invalidCount;

                if ($invalidCount > 0) {

                    $logger->log("INVALID LINK FOUND ($invalidCount)");

                    $invalidPages[] = $url;

                }

                $i++;

            }

            $logger->log("Navigation links found: $siteLinkCount");
            $logger->log("Invalid navigation links: $siteInvalidCount");

        }

        if (!empty($invalidPages)) {

            $logger->log("Invalid pages found: " . count($invalidPages));

            $logger->log("Flushing TYPO3 cache");

            $cacheManager->flush();

            sleep(5);

            $logger->log("Rechecking pages");

            $stillBroken = [];

            foreach ($invalidPages as $url) {

                $logger->log("Recheck: $url");

                $html = $crawler->fetch($url);

                if ($checker->countInvalidLinks($html) > 0) {

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