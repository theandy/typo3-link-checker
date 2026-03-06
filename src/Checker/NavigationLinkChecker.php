<?php

namespace LinkChecker\Checker;

class NavigationLinkChecker
{

    public function hasInvalidLink(string $html): bool
    {

        if (!$html) {
            return false;
        }

        return preg_match("/navigation-link-href:'\\s*'/", $html) === 1;

    }

}