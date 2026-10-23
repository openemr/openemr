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
    public static function text(mixed $value): string
    {
        return is_scalar($value) ? (string) $value : '';
    }

    public static function integer(mixed $value): int
    {
        return is_scalar($value) && is_numeric($value) ? (int) $value : 0;
    }

    public static function number(mixed $value): float
    {
        return is_scalar($value) && is_numeric($value) ? (float) $value : 0.0;
    }

    /** @return array<string, mixed> */
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

    /** @return array<string, scalar|null> */
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
     * @param array<string, mixed> $value
     * @return array<string, string>
     */
    public static function textRow(array $value): array
    {
        return array_map(self::text(...), $value);
    }
}
