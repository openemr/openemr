<?php

/**
 * Mixed-to-typed coercion helpers used across ClaimRev module services.
 *
 * QueryUtils::fetchRecords() returns array<string, mixed>; these helpers
 * narrow individual cells to string/int/float/bool without resorting to
 * raw casts (which PHPStan rejects on mixed at level 10).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brad Sharp <brad.sharp@claimrev.com>
 * @copyright Copyright (c) 2026 Brad Sharp <brad.sharp@claimrev.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\ClaimRevConnector;

final class TypeCoerce
{
    public static function asString(mixed $v, string $default = ''): string
    {
        if (is_string($v)) {
            return $v;
        }
        if (is_int($v) || is_float($v)) {
            return (string) $v;
        }
        return $default;
    }

    public static function asInt(mixed $v, int $default = 0): int
    {
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (int) $v;
        }
        if (is_float($v)) {
            return (int) $v;
        }
        return $default;
    }

    public static function asFloat(mixed $v, float $default = 0.0): float
    {
        if (is_float($v)) {
            return $v;
        }
        if (is_int($v)) {
            return (float) $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (float) $v;
        }
        return $default;
    }

    public static function asBool(mixed $v): bool
    {
        if (is_bool($v)) {
            return $v;
        }
        if (is_int($v)) {
            return $v !== 0;
        }
        if (is_string($v)) {
            return $v === '1' || strcasecmp($v, 'true') === 0 || strcasecmp($v, 'yes') === 0;
        }
        return false;
    }

    public static function asNullableInt(mixed $v): ?int
    {
        if ($v === null) {
            return null;
        }
        if (is_int($v)) {
            return $v;
        }
        if (is_string($v) && is_numeric($v)) {
            return (int) $v;
        }
        return null;
    }
}
