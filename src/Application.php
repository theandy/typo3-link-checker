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

            $crawler->fetchMultiple($urls, function ($url, $html, $error, $index) use (
                $checker,
                &$siteLinkCount,
                &$siteInvalidCount,
                &$invalidPages,
                &$failedPages,
                $logger,
                $total
            ) {

                $pos = $index + 1;

                $logger->log("[$pos/$total] $url");

                if ($error) {

                    $reason = $this->normalizeError($error);

                    $logger->log("ERROR loading page");
                    $logger->log("Reason: $reason");

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

        $logger->log("");
        $logger->log("===== SUMMARY =====");

        if (!empty($invalidPages)) {

            $logger->log("Pages with invalid navigation links:");

            foreach ($invalidPages as $url => $count) {

                $logger->log("$url (invalid links: $count)");

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

            }

        } else {

            $logger->log("All pages could be loaded successfully.");

        }

        $logger->log("===== END SUMMARY =====");

        $logger->log("Finished");

    }

    private function normalizeError($error): string
    {

        $msg = $error->getMessage() ?? '';

        if (str_contains($msg, 'cURL error 28')) {
            return 'Timeout after 8 seconds';
        }

        if (str_contains($msg, 'Could not resolve host')) {
            return 'DNS lookup failed';
        }

        if (str_contains($msg, 'Connection refused')) {
            return 'Connection refused';
        }

        if (preg_match('/([0-9]{3})/', $msg, $m)) {
            return 'HTTP ' . $m[1];
        }

        return $msg;

    }

}