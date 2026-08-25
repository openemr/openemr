<?php

/**
 * Narrow mixed database/request values without empty() or mixed casts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

final class Values
{
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

    public static function asBool(mixed $value): bool
    {
        return $value === true || $value === 1 || $value === '1';
    }

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
