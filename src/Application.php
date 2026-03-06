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
        $mailer = new Mailer($config, $logger);

        $sites = $siteRepo->getSites();

        $invalidPages = [];
        $failedPages = [];

        foreach ($sites as $site) {

            $logger->log("Checking site: " . $site['base']);

            $pages = $pageRepo->getPagesByRoot(
                $site['rootPageId'],
                $site['languageId']
            );

            $urls = [];

            foreach ($pages as $page) {

                $urls[] =
                    rtrim($site['base'], '/') .
                    '/' .
                    ltrim($page['slug'], '/');

            }

            $total = count($urls);

            $logger->log("Found $total pages");

            $siteLinkCount = 0;
            $siteInvalidCount = 0;

            $crawler->fetchMultiple($urls, function ($url, $html, $error, $index = null) use (
                $checker,
                &$siteLinkCount,
                &$siteInvalidCount,
                &$invalidPages,
                &$failedPages,
                $logger,
                $total
            ) {

                $pos = $index !== null ? $index + 1 : "?";

                $logger->log("[$pos/$total] $url");

                if ($error) {

                    $reason = (string)$error;

                    $logger->log("ERROR loading page: $reason");

                    $failedPages[$url] = $reason;

                    return;

                }

                $linkCount = $checker->countNavigationLinks($html);
                $invalidCount = $checker->countInvalidLinks($html);

                $logger->log("Navigation markers: $linkCount");

                $siteLinkCount += $linkCount;
                $siteInvalidCount += $invalidCount;

                if ($invalidCount > 0) {

                    $logger->log("INVALID LINK FOUND ($invalidCount)");

                    $invalidPages[$url] = $invalidCount;

                }

            });

            $logger->log("Navigation links found: $siteLinkCount");
            $logger->log("Invalid navigation links: $siteInvalidCount");

        }

        /*
        CACHE FLUSH + RECHECK
        */

        if (!empty($invalidPages)) {

            $logger->log("Invalid pages found: " . count($invalidPages));

            $logger->log("Flushing TYPO3 cache");

            $cacheManager->flush();

            sleep(5);

            $stillBroken = [];

            $crawler->fetchMultiple(array_keys($invalidPages), function ($url, $html) use (
                $checker,
                &$stillBroken,
                $logger
            ) {

                $logger->log("Rechecking page: $url");

                if ($checker->countInvalidLinks($html) > 0) {

                    $logger->log("STILL INVALID");

                    $stillBroken[] = $url;

                }

            });

            if (!empty($stillBroken)) {

                $logger->log("Errors remain after cache flush");

                $mailer->send($stillBroken);

                $logger->log("Mail sent");

            } else {

                $logger->log("Errors disappeared after cache flush");

            }

        }

        /*
        SUMMARY
        */

        $logger->log("");
        $logger->log("===== SUMMARY =====");

        if (!empty($invalidPages)) {

            $logger->log("Pages with invalid navigation links:");

            foreach ($invalidPages as $url => $count) {

                $logger->log("$url  (invalid links: $count)");

            }

        } else {

            $logger->log("No invalid navigation links found.");

        }

        $logger->log("");

        if (!empty($failedPages)) {

            $logger->log("Pages that could not be loaded:");

            foreach ($failedPages as $url => $reason) {

                $logger->log("$url");
                $logger->log("Reason: $reason");
                $logger->log("");

            }

        } else {

            $logger->log("All pages could be loaded successfully.");

        }

        $logger->log("===== END SUMMARY =====");

        $logger->log("Finished");

    }

}