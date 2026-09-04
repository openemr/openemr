<?php

/**
 * Whether two numeric bands overlap.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Simon Quigley <squigley@altispeed.com>
 * @copyright Copyright (c) 2026 Simon Quigley <squigley@altispeed.com>
 * @license   GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Modules\LbfStatements;

final class BandOverlap
{
    /**
     * @param array<string, mixed> $a
     * @param array<string, mixed> $b
     */
    public static function rangesOverlap(array $a, array $b): bool
    {
        $aMin = self::bound($a['min_value'] ?? null, true);
        $aMax = self::bound($a['max_value'] ?? null, false);
        $bMin = self::bound($b['min_value'] ?? null, true);
        $bMax = self::bound($b['max_value'] ?? null, false);
        $aMinInc = Values::asBool($a['min_inclusive'] ?? 1);
        $aMaxInc = Values::asBool($a['max_inclusive'] ?? 1);
        $bMinInc = Values::asBool($b['min_inclusive'] ?? 1);
        $bMaxInc = Values::asBool($b['max_inclusive'] ?? 1);

        if ($aMax < $bMin) {
            return false;
        }
        if ($aMax === $bMin && (!$aMaxInc || !$bMinInc)) {
            return false;
        }
        if ($bMax < $aMin) {
            return false;
        }
        if ($bMax === $aMin && (!$bMaxInc || !$aMinInc)) {
            return false;
        }
        return true;
    }

    /**
     * @param array<string, mixed> $rule
     */
    public static function invertedBounds(array $rule): bool
    {
        $min = Values::asFloatOrNull($rule['min_value'] ?? null);
        $max = Values::asFloatOrNull($rule['max_value'] ?? null);
        if ($min === null || $max === null) {
            return false;
        }
        return $min > $max;
    }

    /**
     * Numeric bound, or -INF / INF when the side is open.
     */
    private static function bound(mixed $value, bool $isMin): float
    {
        $n = Values::asFloatOrNull($value);
        if ($n === null) {
            return $isMin ? -INF : INF;
        }
        return $n;
    }
}
