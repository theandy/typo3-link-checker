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

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class Application
{

    public function run(): void
    {

        $config = new Config();

        $logger = new Logger(
            $config->get('log')['file'],
            $config->get('log')['overwrite']
        );

        $output = new ConsoleOutput();

        $logger->info("TYPO3 Navigation Link Checker started");

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

            $logger->info("Checking site: " . $site['base']);

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

            $logger->info("Found $total pages");

            $progress = new ProgressBar($output, $total);
            $progress->start();

            $siteLinkCount = 0;
            $siteInvalidCount = 0;

            $crawler->fetchMultiple($urls, function ($url, $html, $error, $index) use (
                $checker,
                &$siteLinkCount,
                &$siteInvalidCount,
                &$invalidPages,
                &$failedPages,
                $logger,
                $progress
            ) {

                /*
                 ProgressBar kurz entfernen,
                 damit Log-Ausgabe sichtbar bleibt
                */

                $progress->clear();

                $logger->info("Checking page: $url");

                if ($error) {

                    $reason = $this->normalizeError($error);

                    $logger->error("ERROR loading page");
                    $logger->error("Reason: $reason");

                    $failedPages[$url] = $reason;

                    $progress->advance();
                    $progress->display();

                    return;

                }

                $linkCount = $checker->countNavigationLinks($html);
                $invalidCount = $checker->countInvalidLinks($html);

                $logger->info("Navigation markers: $linkCount");

                $siteLinkCount += $linkCount;
                $siteInvalidCount += $invalidCount;

                if ($invalidCount > 0) {

                    $logger->warn("INVALID LINK FOUND ($invalidCount)");

                    $invalidPages[$url] = $invalidCount;

                }

                $progress->advance();
                $progress->display();

            });

            $progress->finish();
            echo PHP_EOL;

            $logger->info("Navigation links found: $siteLinkCount");
            $logger->info("Invalid navigation links: $siteInvalidCount");

        }

        /*
        CACHE FLUSH + RECHECK
        */

        if (!empty($invalidPages)) {

            $logger->warn("Invalid pages found: " . count($invalidPages));

            $logger->info("Flushing TYPO3 cache");

            $cacheManager->flush();

            sleep(5);

            $stillBroken = [];

            $crawler->fetchMultiple(array_keys($invalidPages), function ($url, $html, $error) use (
                $checker,
                &$stillBroken,
                $logger
            ) {

                $logger->info("Rechecking page: $url");

                if ($error) {
                    return;
                }

                if ($checker->countInvalidLinks($html) > 0) {

                    $logger->warn("STILL INVALID");

                    $stillBroken[] = $url;

                }

            });

            if (!empty($stillBroken)) {

                $logger->warn("Errors remain after cache flush");

                $mailer->send($stillBroken);

                $logger->success("Mail sent");

            } else {

                $logger->success("Errors disappeared after cache flush");

            }

        }

        /*
        SUMMARY
        */

        $logger->info("");
        $logger->info("===== SUMMARY =====");

        if (!empty($invalidPages)) {

            foreach ($invalidPages as $url => $count) {

                $logger->warn("$url (invalid links: $count)");

            }

        } else {

            $logger->success("No invalid navigation links found.");

        }

        $logger->info("");

        if (!empty($failedPages)) {

            $logger->error("Pages that could not be loaded:");

            foreach ($failedPages as $url => $reason) {

                $logger->error($url);
                $logger->error("Reason: $reason");

            }

        } else {

            $logger->success("All pages could be loaded successfully.");

        }

        $logger->info("===== END SUMMARY =====");

        $logger->success("Finished");

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