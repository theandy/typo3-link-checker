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
        $this->render();
    }

    public function render(): void
    {

        $percent = $this->current / $this->total;

        $filled = (int)floor($percent * $this->width);

        $bar =
            str_repeat('█', $filled) .
            str_repeat('░', $this->width - $filled);

        $percentText = (int)floor($percent * 100);

        echo "\rProgress: [$bar] {$percentText}% ({$this->current}/{$this->total})";
    }

    public function finish(): void
    {
        $this->render();
        echo PHP_EOL;
    }
}