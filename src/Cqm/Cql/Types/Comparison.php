<?php

/**
 * CQL equality, equivalence and ordering over any CQL value, ported from
 * cql-execution's util/comparison.
 *
 * Equality is three-valued: null when either side is null or when a part of
 * a list or tuple cannot be decided. Equivalence never returns null for null
 * operands, matches codes by code and system, and compares strings ignoring
 * case, accents and the kind of whitespace.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final class Comparison
{
    private static ?\Collator $collator = null;

    public static function equals(mixed $a, mixed $b): ?bool
    {
        if ($a === null || $b === null) {
            return null;
        }
        if ($a instanceof Quantity || $a instanceof Ratio) {
            return $a->equals($b);
        }
        if ($a instanceof Uncertainty) {
            $b = Uncertainty::from($b);
        } elseif ($b instanceof Uncertainty) {
            $a = Uncertainty::from($a);
        }
        if ($a instanceof CqlTemporal || $a instanceof Uncertainty || $a instanceof Interval) {
            return $a->equals($b);
        }
        if (self::isNumber($a)) {
            return self::isNumber($b) && $a == $b;
        }
        if (is_bool($a) || (is_string($a) && is_string($b))) {
            return $a === $b;
        }
        if (is_array($a) && is_array($b)) {
            if (in_array(null, $a, true) || in_array(null, $b, true)) {
                return null;
            }
            return self::everyItem($a, $b, self::equals(...));
        }
        if (is_object($a) && is_object($b)) {
            return self::compareObjects($a, $b, self::equals(...));
        }
        return false;
    }

    public static function equivalent(mixed $a, mixed $b): ?bool
    {
        if ($a === null && $b === null) {
            return true;
        }
        if ($a === null || $b === null) {
            return false;
        }
        if ($a instanceof Code || $a instanceof Concept || $a instanceof ValueSet) {
            return $a->hasMatch($b);
        }
        if ($a instanceof Quantity) {
            // Quantity equivalence is Quantity equality.
            return $a->equals($b);
        }
        if ($a instanceof CqlTemporal || $a instanceof Ratio) {
            return $a->equivalent($b);
        }
        if (is_array($a) && is_array($b)) {
            return self::everyItem($a, $b, self::equivalent(...));
        }
        if (is_array($a) && is_string($b)) {
            // cql-execution compares a list with anything that has a length
            // and indexes, so a string is compared as its characters.
            return self::everyItem($a, mb_str_split($b), self::equivalent(...));
        }
        if (is_object($a) && is_object($b)) {
            return self::compareObjects($a, $b, self::equivalent(...));
        }
        if (is_string($a) && is_string($b)) {
            $normalize = static fn (string $s): string => preg_replace('/\s/u', ' ', $s) ?? $s;
            return self::collator()->compare($normalize($a), $normalize($b)) === 0;
        }
        return self::equals($a, $b);
    }

    public static function lessThan(mixed $a, mixed $b, ?Precision $precision = null): ?bool
    {
        return self::ordered('before', $a, $b, $precision);
    }

    public static function lessThanOrEquals(mixed $a, mixed $b, ?Precision $precision = null): ?bool
    {
        return self::ordered('sameOrBefore', $a, $b, $precision);
    }

    public static function greaterThan(mixed $a, mixed $b, ?Precision $precision = null): ?bool
    {
        return self::ordered('after', $a, $b, $precision);
    }

    public static function greaterThanOrEquals(mixed $a, mixed $b, ?Precision $precision = null): ?bool
    {
        return self::ordered('sameOrAfter', $a, $b, $precision);
    }

    /**
     * @param 'before'|'after'|'sameOrBefore'|'sameOrAfter' $operation
     */
    private static function ordered(string $operation, mixed $a, mixed $b, ?Precision $precision): ?bool
    {
        if (self::isNumber($a) && self::isNumber($b)) {
            return self::applyOrder($operation, $a <=> $b);
        }
        if (is_string($a) && is_string($b)) {
            // JavaScript orders strings by character, never numerically.
            return self::applyOrder($operation, strcmp($a, $b));
        }
        if (
            ($a instanceof CqlDateTime && $b instanceof CqlDateTime)
            || ($a instanceof CqlDate && $b instanceof CqlDate)
        ) {
            return $a->{$operation}($b, $precision);
        }
        if ($a instanceof Quantity && $b instanceof Quantity) {
            return $a->{$operation}($b);
        }
        $uncertain = match ($operation) {
            'before' => 'lessThan',
            'after' => 'greaterThan',
            'sameOrBefore' => 'lessThanOrEquals',
            'sameOrAfter' => 'greaterThanOrEquals',
        };
        if ($a instanceof Uncertainty) {
            return $a->{$uncertain}($b);
        }
        if ($b instanceof Uncertainty) {
            return Uncertainty::from($a)->{$uncertain}($b);
        }
        return null;
    }

    /**
     * @param 'before'|'after'|'sameOrBefore'|'sameOrAfter' $operation
     */
    private static function applyOrder(string $operation, int $order): bool
    {
        return match ($operation) {
            'before' => $order < 0,
            'after' => $order > 0,
            'sameOrBefore' => $order <= 0,
            'sameOrAfter' => $order >= 0,
        };
    }

    /**
     * Lists match when their lengths match and every pair compares true;
     * an undecided pair counts as no match, as Array.every treats null.
     *
     * @param array<mixed> $a
     * @param array<mixed> $b
     * @param \Closure(mixed, mixed): ?bool $compare
     */
    private static function everyItem(array $a, array $b, \Closure $compare): bool
    {
        if (count($a) !== count($b)) {
            return false;
        }
        $b = array_values($b);
        foreach (array_values($a) as $i => $item) {
            if ($compare($item, $b[$i]) !== true) {
                return false;
            }
        }
        return true;
    }

    /**
     * Objects of the same class match when every element matches. Elements
     * null on both sides are ignored; an undecided element makes the result
     * null when nothing else fails.
     *
     * @param \Closure(mixed, mixed): ?bool $compare
     */
    private static function compareObjects(object $a, object $b, \Closure $compare): ?bool
    {
        if ($a::class !== $b::class) {
            return false;
        }
        $aValues = $a instanceof Tuple ? $a->elements : get_object_vars($a);
        $bValues = $b instanceof Tuple ? $b->elements : get_object_vars($b);
        $aKeys = array_keys($aValues);
        $bKeys = array_keys($bValues);
        sort($aKeys);
        sort($bKeys);
        if ($aKeys !== $bKeys) {
            return false;
        }
        $undecided = false;
        foreach ($aKeys as $key) {
            if ($aValues[$key] === null && $bValues[$key] === null) {
                continue;
            }
            $result = $compare($aValues[$key], $bValues[$key]);
            if ($result === null && $aValues[$key] instanceof Quantity && $bValues[$key] !== null && !$bValues[$key] instanceof Quantity) {
                // cql-execution's Quantity equality gives undefined, not
                // null, for a non-quantity, which fails the comparison.
                return false;
            }
            if ($result === null) {
                $undecided = true;
            } elseif ($result === false) {
                return false;
            }
        }
        return $undecided ? null : true;
    }

    private static function isNumber(mixed $value): bool
    {
        return is_int($value) || is_float($value);
    }

    private static function collator(): \Collator
    {
        if (self::$collator === null) {
            $collator = new \Collator('en');
            $collator->setStrength(\Collator::PRIMARY);
            self::$collator = $collator;
        }
        return self::$collator;
    }
}
