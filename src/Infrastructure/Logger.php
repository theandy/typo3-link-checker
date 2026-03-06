<?php

namespace LinkChecker\Infrastructure;

class Logger
{
    private string $file;

    public function __construct(string $file, bool $overwrite = false)
    {
        $this->file = $file;

        $dir = dirname($file);

        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        if ($overwrite) {
            file_put_contents($file, '');
        }
    }

    public function info(string $message): void
    {
        $this->write('INFO', $message, "\033[0m");
    }

    public function warn(string $message): void
    {
        $this->write('WARN', $message, "\033[33m");
    }

    public function error(string $message): void
    {
        $this->write('ERROR', $message, "\033[31m");
    }

    public function success(string $message): void
    {
        $this->write('OK', $message, "\033[32m");
    }

    private function write(string $level, string $message, string $color): void
    {
        $timestamp = date('Y-m-d H:i:s');

        $line = "[$timestamp] [$level] $message";

        file_put_contents(
            $this->file,
            $line . PHP_EOL,
            FILE_APPEND
        );

        $reset = "\033[0m";

        echo $color . $line . $reset . PHP_EOL;
    }
}