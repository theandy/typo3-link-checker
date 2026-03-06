<?php

namespace LinkChecker\Infrastructure;

class Logger
{

    private string $logFile;
    private bool $overwrite;

    public function __construct(string $logFile, bool $overwrite = false)
    {

        $this->logFile = $logFile;
        $this->overwrite = $overwrite;

        $dir = dirname($logFile);

        if (!is_dir($dir)) {

            if (!mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new \RuntimeException("Cannot create log directory: " . $dir);
            }

        }

        if ($overwrite) {
            file_put_contents($this->logFile, '');
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