<?php

namespace LinkChecker\Infrastructure;

class Logger
{

    private string $logFile;

    public function __construct(string $logFile)
    {

        $this->logFile = $logFile;

        $dir = dirname($logFile);

        if (!is_dir($dir)) {

            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException("Cannot create log directory: " . $dir);
            }

        }

        if (!file_exists($logFile)) {
            touch($logFile);
        }

    }

    public function log(string $message): void
    {

        $time = date('Y-m-d H:i:s');

        $line = "[$time] " . $message;

        echo $line . PHP_EOL;

        file_put_contents(
            $this->logFile,
            $line . PHP_EOL,
            FILE_APPEND
        );

    }

}