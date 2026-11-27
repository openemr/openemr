<?php

/**
 * The HTML helpers the encounter history renderer uses, so it can be tested without
 * the OpenEMR bootstrap.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Steve Wiig <https://github.com/Steve-Wiig>
 * @copyright Copyright (c) 2026 Steve Wiig
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (!function_exists('text')) {
    function text($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('attr')) {
    function attr($text)
    {
        return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('attr_url')) {
    function attr_url($text)
    {
        return urlencode((string) $text);
    }
}