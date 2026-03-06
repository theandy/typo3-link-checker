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

        preg_match_all(
            '/<!--\s*navigation-link-href:\s*[\'"]\s*[\'"]\s*-->/i',
            $html,
            $matches
        );

        return count($matches[0]);

    }

}