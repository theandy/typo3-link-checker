<?php

namespace LinkChecker\Crawler;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;

class PageCrawler
{

    private Client $client;

    public function __construct()
    {

        $this->client = new Client([
            'timeout' => 8,
            'connect_timeout' => 3,
            'verify' => false,
            'headers' => [
                'User-Agent' => 'TYPO3-LinkChecker'
            ]
        ]);

    }

    public function fetchMultiple(array $urls, callable $callback, int $concurrency = 20): void
    {

        $requests = function ($urls) {
            foreach ($urls as $i => $url) {
                yield $i => new Request('GET', $url);
            }
        };

        $pool = new Pool($this->client, $requests($urls), [

            'concurrency' => $concurrency,

            'fulfilled' => function ($response, $index) use ($urls, $callback) {

                $html = (string)$response->getBody();

                $callback($urls[$index], $html, null, $index);

            },

            'rejected' => function ($reason, $index) use ($urls, $callback) {

                $callback($urls[$index], '', $reason, $index);

            }

        ]);

        $pool->promise()->wait();

    }

}