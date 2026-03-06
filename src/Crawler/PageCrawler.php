<?php

namespace LinkChecker\Crawler;

use GuzzleHttp\Client;

class PageCrawler
{

    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'timeout' => 10,
            'verify' => false
        ]);
    }

    public function fetch(string $url): string
    {

        try {

            $response = $this->client->get($url);

            return (string) $response->getBody();

        } catch (\Exception $e) {

            return '';

        }

    }

}