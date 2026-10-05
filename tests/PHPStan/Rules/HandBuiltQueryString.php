<?php

/**
 * Shared text matching for the hand-built query string rules
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\PHPStan\Rules;

final class HandBuiltQueryString
{
    public const IDENTIFIER = 'openemr.handBuiltQueryString';

    public const TIP = 'OpenEMR\Common\Http\QueryString::build() encodes every value; escape its result for the output context with attr() or js_escape().';

    /**
     * Characters a query key may contain, including the brackets of array keys.
     */
    private const KEY_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_.-[]';

    /**
     * Characters a key may contain when no '?' or '&' precedes it.
     */
    private const BARE_KEY_CHARS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789_';

    /**
     * The key when $text ends where a parameter's value begins, as in 'x.php?id=' or '&id='.
     */
    public static function keyBeforeValue(string $text): ?string
    {
        if (!str_ends_with($text, '=')) {
            return null;
        }
        $body = substr($text, 0, -1);
        $keyLength = strspn(strrev($body), self::KEY_CHARS);
        if ($keyLength === 0 || $keyLength === strlen($body)) {
            return null;
        }
        return self::isSeparator($body[strlen($body) - $keyLength - 1]) ? substr($body, -$keyLength) : null;
    }

    /**
     * The key when $text is nothing but `key=`, as when it continues a URL
     * that an expression before it left ending in '&': `$this->_link() . 'id='`.
     * With no separator to anchor it, only a word starting with a letter counts,
     * so JavaScript such as `field.value=` and SQL such as `$type . '_id='` are
     * not mistaken for query keys.
     */
    public static function bareKey(string $text): ?string
    {
        if (!str_ends_with($text, '=')) {
            return null;
        }
        $body = substr($text, 0, -1);
        if ($body === '' || !ctype_alpha($body[0]) || strspn($body, self::BARE_KEY_CHARS) !== strlen($body)) {
            return null;
        }
        return $body;
    }

    /**
     * The key when $text ends in JavaScript that joins a PHP-printed value to
     * a query key, as in `'x.php?id=' + ` just before an echo.
     */
    public static function keyBeforeJsConcatenation(string $text): ?string
    {
        $text = rtrim($text);
        if (!str_ends_with($text, '+')) {
            return null;
        }
        $text = rtrim(substr($text, 0, -1));
        $quote = substr($text, -1);
        if ($quote !== "'" && $quote !== '"') {
            return null;
        }
        return self::keyBeforeValue(substr($text, 0, -1));
    }

    /**
     * Whether $text ends where a parameter's key begins, as in 'x.php?' or '&'.
     */
    public static function endsWithSeparator(string $text): bool
    {
        return $text !== '' && self::isSeparator($text[strlen($text) - 1]);
    }

    private static function isSeparator(string $char): bool
    {
        return $char === '?' || $char === '&';
    }
}
