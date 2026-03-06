<?php

namespace LinkChecker\Checker;

class NavigationLinkChecker
{

    public function countNavigationLinks(string $html): int
    {

        if (!$html) {
            return 0;
        }

        preg_match_all('/navigation-link-href:/i', $html, $matches);

        return count($matches[0]);

    }

    public function countInvalidLinks(string $html): int
    {

        if (!$html) {
            return 0;
        }

        /*
         * Alle HTML Kommentare holen
         */

        preg_match_all('/<!--(.*?)-->/s', $html, $comments);

        $invalid = 0;

        foreach ($comments[1] as $comment) {

            if (preg_match('/navigation-link-href\s*:\s*[\'"]\s*[\'"]/', $comment)) {
                $invalid++;
            }

        }

        return $invalid;

    }

}