<?php

/**
 * Cast mixed SQL and request values.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\LBF;

final class Values
{
    /**
     * String form of a scalar, or $default.
     */
    public static function asString(mixed $value, string $default = ''): string
    {
        if ($value === null) {
            return $default;
        }
        if (is_string($value)) {
            return $value;
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        return $default;
    }

    /**
     * Integer form of a numeric scalar, or $default.
     */
    public static function asInt(mixed $value, int $default = 0): int
    {
        if (is_int($value)) {
            return $value;
        }
        if (is_string($value) && is_numeric($value)) {
            return (int) $value;
        }
        if (is_float($value)) {
            return (int) $value;
        }
        return $default;
    }

    /**
     * True for boolean true, integer 1, or string "1".
     */
    public static function asBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

    /**
     * Closed span of a measurement box.
     *
     * A plain number, a trailing percent, or a mmHg suffix is one value.
     * A hyphenated range is kept as both ends. Words are not a measurement.
     *
     * @return array{0: float, 1: float}|null
     */
    public static function measurementSpan(string $value): ?array
    {
        $text = trim($value);
        if ($text === '') {
            return null;
        }
        $stripped = preg_replace('/(?:\s*%|\s*mm\s*hg)\s*$/i', '', $text);
        if (!is_string($stripped)) {
            return null;
        }
        $text = trim($stripped);
        if (preg_match('/^(\d+(?:\.\d+)?)(?:\s*-\s*(\d+(?:\.\d+)?))?$/', $text, $matches) !== 1) {
            return null;
        }
        $low = (float) $matches[1];
        $high = array_key_exists(2, $matches) ? (float) $matches[2] : $low;
        if ($high < $low) {
            return null;
        }

        return [$low, $high];
    }

    /**
     * Float form of a numeric scalar, or null when empty or non-numeric.
     */
    public static function asFloatOrNull(mixed $value): ?float
    {
        if ($value === null) {
            return null;
        }
        if (is_float($value)) {
            return $value;
        }
        if (is_int($value)) {
            return (float) $value;
        }
        if (is_string($value)) {
            $trim = trim($value);
            if ($trim === '' || !is_numeric($trim)) {
                return null;
            }
            return (float) $trim;
        }
        return null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function assocRow(mixed $row): ?array
    {
        if (!is_array($row)) {
            return null;
        }
        $out = [];
        foreach ($row as $key => $value) {
            if (!is_string($key)) {
                continue;
            }
            $out[$key] = $value;
        }
        return $out;
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function rowString(array $row, string $key, string $default = ''): string
    {
        return self::asString($row[$key] ?? $default, $default);
    }

    /**
     * @param array<string, mixed> $row
     */
    public static function rowInt(array $row, string $key, int $default = 0): int
    {
        return self::asInt($row[$key] ?? $default, $default);
    }
}
