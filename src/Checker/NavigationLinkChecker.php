<?php

namespace LinkChecker\Checker;

class NavigationLinkChecker
{

    public function hasInvalidLink(string $html): bool
    {

        if (!$html) {
            return false;
        }

        /*
         * Sucht nach
         * <!-- navigation-link-href:'' -->
         * mit optionalen Leerzeichen
         */

        $pattern = '/<!--\s*navigation-link-href:\s*\'\'\s*-->/i';

        return preg_match($pattern, $html) === 1;

    }

}