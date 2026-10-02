<?php

/**
 * Builds URL query strings from typed parameter maps
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Common\Http;

/**
 * Encodes keys and values exactly as urlencode() does (a space becomes '+'),
 * so a hand-concatenated query string can be replaced without changing a byte.
 * The result is URL-encoded, not HTML- or JS-escaped: pass it through attr()
 * or js_escape() at the point of output.
 */
final class QueryString
{
    /**
     * @param array<string, string|int> $params
     */
    public static function build(array $params): string
    {
        // Pass the separator explicitly; the default comes from the arg_separator.output ini setting.
        return http_build_query($params, '', '&', PHP_QUERY_RFC1738);
    }

    /**
     * Appends with '?' or '&' as $url requires, keeping any #fragment last.
     * Returns $url unchanged when $params is empty.
     *
     * @param array<string, string|int> $params
     */
    public static function append(string $url, array $params): string
    {
        $query = self::build($params);
        if ($query === '') {
            return $url;
        }

        $parts = explode('#', $url, 2);
        $base = $parts[0];
        $fragment = isset($parts[1]) ? '#' . $parts[1] : '';

        return $base . self::separatorFor($base) . $query . $fragment;
    }

    /**
     * For values PHPStan cannot type yet, such as database rows. Coerces each
     * value as urlencode() and attr_url() do, so replacing them keeps the URL
     * byte-identical.
     *
     * @param array<string, mixed> $params
     * @throws \InvalidArgumentException when a value is an array or a non-Stringable object
     */
    public static function buildUntyped(array $params): string
    {
        return self::build(self::coerce($params));
    }

    /**
     * append() for untyped values; see buildUntyped().
     *
     * @param array<string, mixed> $params
     * @throws \InvalidArgumentException when a value is an array or a non-Stringable object
     */
    public static function appendUntyped(string $url, array $params): string
    {
        return self::append($url, self::coerce($params));
    }

    /**
     * @param array<string, mixed> $params
     * @return array<string, string>
     */
    private static function coerce(array $params): array
    {
        $coerced = [];
        foreach ($params as $key => $value) {
            $coerced[$key] = match (true) {
                $value === null, $value === false => '',
                $value === true => '1',
                is_string($value) => $value,
                is_int($value), is_float($value), $value instanceof \Stringable => (string) $value,
                default => throw new \InvalidArgumentException(sprintf(
                    'Query parameter "%s" must be a scalar or Stringable, %s given',
                    $key,
                    get_debug_type($value),
                )),
            };
        }
        return $coerced;
    }

    private static function separatorFor(string $base): string
    {
        if (!str_contains($base, '?')) {
            return '?';
        }
        if (str_ends_with($base, '?') || str_ends_with($base, '&')) {
            return '';
        }
        return '&';
    }
}
