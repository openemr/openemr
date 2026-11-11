<?php

/**
 * Validate values crossing the legacy Eye Form's request and database boundaries.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Ray Magauran <rmagauran@gmail.com>
 * @copyright Copyright (c) 2026 Ray Magauran <rmagauran@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Forms\EyeMag;

final class EyeFormData
{
    /** Normalize scalar legacy values to text and discard arrays or objects. */
    public static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    /** Convert numeric scalar request or database values to integers, using zero for other input. */
    public static function integer(mixed $value): int
    {
        return is_scalar($value) && is_numeric($value) ? (int) $value : 0;
    }

    /**
     * Parse a positive decimal identifier; absent, malformed and overflowing values return zero.
     */
    public static function identifier(mixed $value): int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : 0;
        }
        if (!is_string($value) || preg_match('/\A[0-9]+\z/D', $value) !== 1) {
            return 0;
        }
        $digits = ltrim($value, '0');
        $maximum = (string) PHP_INT_MAX;
        if ($digits === '' || strlen($digits) > strlen($maximum) || (strlen($digits) === strlen($maximum) && strcmp($digits, $maximum) > 0)) {
            return 0;
        }
        return (int) $digits;
    }

    /** Convert numeric scalar request or database values to decimals, using zero for other input. */
    public static function number(mixed $value): float
    {
        return is_scalar($value) && is_numeric($value) ? (float) $value : 0.0;
    }

    /**
     * Keep only string-keyed fields from a database or request row.
     *
     * @return array<string, mixed>
     */
    public static function row(mixed $value): array
    {
        if (!is_array($value)) {
            return [];
        }
        $row = [];
        foreach ($value as $key => $item) {
            if (is_string($key)) {
                $row[$key] = $item;
            }
        }
        return $row;
    }

    /**
     * Discard nested and object values before a legacy row is used in scalar fields.
     *
     * @return array<string, scalar|null>
     */
    public static function scalarRow(mixed $value): array
    {
        $row = [];
        foreach (self::row($value) as $key => $item) {
            if (is_scalar($item) || $item === null) {
                $row[$key] = $item;
            }
        }
        return $row;
    }

    /**
     * Normalize each legacy field to scalar text, using blanks for nested values.
     *
     * @param array<string, mixed> $value
     * @return array<string, string>
     */
    public static function textRow(array $value): array
    {
        return array_map(self::text(...), $value);
    }
}
