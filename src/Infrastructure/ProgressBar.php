<?php

namespace LinkChecker\Infrastructure;

class ProgressBar
{

    private int $total;
    private int $current = 0;
    private int $width = 30;

    public function __construct(int $total)
    {
        $this->total = max(1, $total);
    }

    public function advance(): void
    {

        $this->current++;

        $percent = $this->current / $this->total;

        $filled = floor($percent * $this->width);

        $bar =
            str_repeat('█', $filled) .
            str_repeat('░', $this->width - $filled);

        $percentText = floor($percent * 100);

        echo "\rProgress: [$bar] {$percentText}% ({$this->current}/{$this->total})";

        if ($this->current >= $this->total) {
            echo PHP_EOL;
        }

    }

}