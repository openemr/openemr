<?php

/**
 * A value known only to lie in a range, such as the age of a person whose
 * birth date has only a year. Ported from cql-execution's Uncertainty.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

final readonly class Uncertainty
{
    public mixed $low;
    public mixed $high;

    /**
     * Bounds out of order are swapped. Codes, concepts and value sets cannot
     * be ordered, so either one makes both bounds null.
     */
    public function __construct(mixed $low, mixed $high)
    {
        if (self::isNonEnumerable($low) || self::isNonEnumerable($high)) {
            $low = $high = null;
        }
        if ($low !== null && $high !== null && self::greater($low, $high)) {
            [$low, $high] = [$high, $low];
        }
        $this->low = $low;
        $this->high = $high;
    }

    /** A known value, or the uncertainty itself. */
    public static function from(mixed $value): self
    {
        return $value instanceof self ? $value : new self($value, $value);
    }

    public function isPoint(): bool
    {
        return $this->low !== null
            && $this->high !== null
            && (self::compare('sameOrBefore', $this->low, $this->high) ?? false)
            && (self::compare('sameOrAfter', $this->low, $this->high) ?? false);
    }

    public function equals(mixed $other): ?bool
    {
        if ($this->isPoint()) {
            if (!$other instanceof self) {
                return Comparison::equals($this->low, $other);
            }
            if ($other->isPoint()) {
                return Comparison::equals($this->low, $other->low);
            }
        }
        $other = self::from($other);
        return ThreeValuedLogic::not(ThreeValuedLogic::or($this->lessThan($other), $this->greaterThan($other)));
    }

    public function lessThan(mixed $other): ?bool
    {
        $other = self::from($other);
        $optimistic = $this->low === null || $other->high === null
            ? true
            : self::compare('before', $this->low, $other->high);
        $pessimistic = $this->high === null || $other->low === null
            ? false
            : self::compare('before', $this->high, $other->low);
        return $optimistic === $pessimistic ? $optimistic : null;
    }

    public function greaterThan(mixed $other): ?bool
    {
        return self::from($other)->lessThan($this);
    }

    public function lessThanOrEquals(mixed $other): ?bool
    {
        return ThreeValuedLogic::not($this->greaterThan(self::from($other)));
    }

    public function greaterThanOrEquals(mixed $other): ?bool
    {
        return ThreeValuedLogic::not($this->lessThan(self::from($other)));
    }

    private static function isNonEnumerable(mixed $value): bool
    {
        return $value instanceof Code || $value instanceof Concept || $value instanceof ValueSet;
    }

    /**
     * cql-execution's ordering inside an Uncertainty: values of different
     * kinds are not comparable, dates and quantities use their own
     * methods, and anything else compares with the language operators.
     *
     * @param 'before'|'after'|'sameOrBefore'|'sameOrAfter' $operation
     */
    private static function compare(string $operation, mixed $a, mixed $b): ?bool
    {
        if (!self::sameKind($a, $b)) {
            return null;
        }
        if ($a instanceof CqlTemporal || $a instanceof Quantity) {
            return $a->{$operation}($b);
        }
        if (is_object($a)) {
            // Other objects compare as their string form, "[object Object]".
            return $operation === 'sameOrBefore' || $operation === 'sameOrAfter';
        }
        if (is_string($a) && is_string($b)) {
            // JavaScript orders strings by character, never numerically.
            $order = strcmp($a, $b);
            return match ($operation) {
                'before' => $order < 0,
                'after' => $order > 0,
                'sameOrBefore' => $order <= 0,
                'sameOrAfter' => $order >= 0,
            };
        }
        return match ($operation) {
            'before' => $a < $b,
            'after' => $a > $b,
            'sameOrBefore' => $a <= $b,
            'sameOrAfter' => $a >= $b,
        };
    }

    /** cql-execution's swap test: false, not unknown, for different kinds. */
    private static function greater(mixed $a, mixed $b): bool
    {
        return self::sameKind($a, $b) && (self::compare('after', $a, $b) ?? false);
    }

    /** JavaScript typeof and constructor equality; every number is one kind. */
    private static function sameKind(mixed $a, mixed $b): bool
    {
        if (is_int($a) || is_float($a)) {
            return is_int($b) || is_float($b);
        }
        if (is_object($a) && is_object($b)) {
            return $a::class === $b::class;
        }
        return get_debug_type($a) === get_debug_type($b);
    }
}
