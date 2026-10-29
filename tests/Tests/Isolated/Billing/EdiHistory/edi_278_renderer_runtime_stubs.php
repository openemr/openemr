<?php

/**
 * Runtime-only legacy helper stubs for Edi278RendererTest.
 *
 * Keep these declarations guarded: isolated tests share a process and another
 * test may already have supplied a compatible global helper. This file is
 * excluded from PHPStan analysis because PHPStan scans the production
 * declarations for these helpers instead.
 *
 * @license https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

if (!function_exists('text')) {
    function text(string $value): string
    {
        return htmlspecialchars($value, ENT_NOQUOTES);
    }
}

if (!function_exists('attr')) {
    function attr(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES);
    }
}

if (!function_exists('xlt')) {
    function xlt(string $value): string
    {
        return $value;
    }
}

if (!function_exists('edih_format_money')) {
    function edih_format_money(string $value): string
    {
        return $value;
    }
}

if (!function_exists('edih_format_date')) {
    function edih_format_date(string $value, string $format = 'Y-m-d'): string
    {
        return $value;
    }
}
