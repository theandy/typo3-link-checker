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
         * erkennt:
         *
         * <!-- navigation-link-href:'' -->
         * <!-- navigation-link-href: '' -->
         * <!--navigation-link-href:''-->
         */

        preg_match_all(
            '/navigation-link-href:\s*[\'"]\s*[\'"]/',
            $html,
            $matches
        );

        return count($matches[0]);

    }

}