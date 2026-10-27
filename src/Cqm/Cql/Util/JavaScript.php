<?php

/**
 * The JavaScript number and string behaviour that cql-execution and
 * ucum-lhc rely on, so their PHP ports give identical results: number to
 * string conversion, Math.round, Number() parsing and String.replace.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Util;

final class JavaScript
{
    /** The characters String.prototype.trim removes. */
    private const WHITESPACE = '[\t\n\x{0B}\f\r \x{A0}\x{1680}\x{2000}-\x{200A}\x{2028}\x{2029}\x{202F}\x{205F}\x{3000}\x{FEFF}]';

    /** Number.prototype.toString(): the shortest digits that read back as the same number. */
    public static function numberToString(int|float $value): string
    {
        if (is_int($value)) {
            $value = (float) $value;
        }
        if (is_nan($value)) {
            return 'NaN';
        }
        if (is_infinite($value)) {
            return $value > 0 ? 'Infinity' : '-Infinity';
        }
        if ($value == 0) {
            return '0';
        }
        $sign = $value < 0 ? '-' : '';
        [$digits, $exponent] = self::shortestDigits(abs($value));
        $k = strlen($digits);
        // The decimal point sits after $n digits.
        $n = $exponent + 1;
        if ($k <= $n && $n <= 21) {
            return $sign . $digits . str_repeat('0', $n - $k);
        }
        if (0 < $n && $n <= 21) {
            return $sign . substr($digits, 0, $n) . '.' . substr($digits, $n);
        }
        if (-6 < $n && $n <= 0) {
            return $sign . '0.' . str_repeat('0', -$n) . $digits;
        }
        $mantissa = $k === 1 ? $digits : $digits[0] . '.' . substr($digits, 1);
        return $sign . $mantissa . 'e' . ($n - 1 >= 0 ? '+' : '-') . abs($n - 1);
    }

    /** Math.round(): halves round toward positive infinity. */
    public static function round(float $value): float
    {
        if (is_nan($value) || is_infinite($value)) {
            return $value;
        }
        $floor = floor($value);
        return $value - $floor >= 0.5 ? $floor + 1 : $floor;
    }

    /**
     * Number(string): the whole trimmed string must be a number; an empty
     * one is 0.
     */
    public static function toNumber(string $string): float
    {
        $string = self::trim($string);
        if ($string === '') {
            return 0.0;
        }
        if (preg_match('/^[+-]?(Infinity|(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?)$/D', $string, $m) === 1) {
            if ($m[1] === 'Infinity') {
                return $string[0] === '-' ? -INF : INF;
            }
            return (float) $string;
        }
        if (preg_match('/^0([xX][0-9a-fA-F]+|[oO][0-7]+|[bB][01]+)$/D', $string, $m) === 1) {
            $base = match (strtolower($m[1][0])) {
                'x' => 16,
                'o' => 8,
                default => 2,
            };
            $value = 0.0;
            foreach (str_split(strtolower(substr($m[1], 1))) as $digit) {
                $value = $value * $base + (float) hexdec($digit);
            }
            return $value;
        }
        return NAN;
    }

    /**
     * parseFloat(): the longest leading decimal number, or NaN.
     */
    public static function parseFloat(string $string): float
    {
        $string = self::trimStart($string);
        if (preg_match('/^[+-]?(Infinity|(\d+\.?\d*|\.\d+)([eE][+-]?\d+)?)/', $string, $m) !== 1) {
            return NAN;
        }
        if ($m[1] === 'Infinity') {
            return $m[0][0] === '-' ? -INF : INF;
        }
        return (float) $m[0];
    }

    /** ucum-lhc's isNumericString: !isNaN(Number(s)) && !isNaN(parseFloat(s)). */
    public static function isNumericString(string $string): bool
    {
        return !is_nan(self::toNumber($string)) && !is_nan(self::parseFloat($string));
    }

    public static function trim(string $string): string
    {
        return self::trimEnd(self::trimStart($string));
    }

    /**
     * String.prototype.replace with a string pattern: the first occurrence
     * only, with the replacement's $ patterns expanded.
     */
    public static function replaceFirst(string $subject, string $search, string $replacement): string
    {
        $position = strpos($subject, $search);
        if ($position === false) {
            return $subject;
        }
        $after = substr($subject, $position + strlen($search));
        $expanded = self::expandReplacement($replacement, $search, substr($subject, 0, $position), $after, []);
        return substr($subject, 0, $position) . $expanded . $after;
    }

    /**
     * String.prototype.replace with a regular expression without the g
     * flag: the first match only, with the replacement's $ patterns
     * expanded.
     */
    public static function replaceFirstMatch(string $subject, string $pattern, string $replacement): string
    {
        if (preg_match($pattern, $subject, $m, PREG_OFFSET_CAPTURE | PREG_UNMATCHED_AS_NULL) !== 1) {
            return $subject;
        }
        [$match, $position] = $m[0];
        $match ??= '';
        $groups = [];
        foreach ($m as $i => [$group]) {
            if (is_int($i) && $i > 0) {
                $groups[$i] = $group;
            }
        }
        $before = substr($subject, 0, $position);
        $after = substr($subject, $position + strlen($match));
        return $before . self::expandReplacement($replacement, $match, $before, $after, $groups) . $after;
    }

    /**
     * String.prototype.substring: negative indexes count as 0, and the two
     * indexes are swapped when the first is larger.
     */
    public static function substring(string $string, int $start, ?int $end = null): string
    {
        $length = strlen($string);
        $start = max(0, min($start, $length));
        $end = $end === null ? $length : max(0, min($end, $length));
        if ($start > $end) {
            [$start, $end] = [$end, $start];
        }
        return substr($string, $start, $end - $start);
    }

    /**
     * parseInt() of a string of digits with an optional sign; NaN
     * for anything else.
     */
    public static function parseSignedDigits(string $string): int|float
    {
        if (preg_match('/^[+-]?\d+$/D', $string) !== 1) {
            return NAN;
        }
        $value = (float) $string;
        return abs($value) <= PHP_INT_MAX ? (int) $string : $value;
    }

    /**
     * @param array<int, ?string> $groups
     */
    private static function expandReplacement(string $replacement, string $match, string $before, string $after, array $groups): string
    {
        return preg_replace_callback(
            '/\$(\$|&|`|\'|\d{1,2})/',
            static function (array $m) use ($match, $before, $after, $groups): string {
                $token = $m[1];
                return match ($token) {
                    '$' => '$',
                    '&' => $match,
                    '`' => $before,
                    "'" => $after,
                    default => self::groupReference($token, $groups) ?? $m[0],
                };
            },
            $replacement,
        ) ?? $replacement;
    }

    /**
     * A $n or $nn reference: two digits when that group exists, otherwise
     * one digit followed by a literal digit; null when it names no group.
     *
     * @param array<int, ?string> $groups
     */
    private static function groupReference(string $token, array $groups): ?string
    {
        if (strlen($token) === 2 && array_key_exists((int) $token, $groups) && (int) $token > 0) {
            return $groups[(int) $token] ?? '';
        }
        $first = (int) $token[0];
        if ($first > 0 && array_key_exists($first, $groups)) {
            return ($groups[$first] ?? '') . substr($token, 1);
        }
        return null;
    }

    private static function trimStart(string $string): string
    {
        $trimmed = preg_replace('/^' . self::WHITESPACE . '+/u', '', $string);
        return $trimmed ?? ltrim($string, "\t\n\x0B\f\r ");
    }

    private static function trimEnd(string $string): string
    {
        $trimmed = preg_replace('/' . self::WHITESPACE . '+$/Du', '', $string);
        return $trimmed ?? rtrim($string, "\t\n\x0B\f\r ");
    }

    /**
     * The shortest significant digits that convert back to the value, and
     * the power of ten of the first one.
     *
     * @return array{string, int}
     */
    private static function shortestDigits(float $value): array
    {
        $formatted = sprintf('%.16e', $value);
        for ($precision = 0; $precision < 16; $precision++) {
            $candidate = sprintf('%.' . $precision . 'e', $value);
            if ((float) $candidate === $value) {
                $formatted = $candidate;
                break;
            }
        }
        [$mantissa, $exponent] = explode('e', $formatted);
        $digits = rtrim(str_replace('.', '', $mantissa), '0');
        return [$digits === '' ? '0' : $digits, (int) $exponent];
    }
}
