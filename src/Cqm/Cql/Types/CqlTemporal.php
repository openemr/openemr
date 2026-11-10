<?php

/**
 * Shared behaviour of the CQL Date and DateTime types, ported from
 * cql-execution's AbstractDate.
 *
 * A value may be imprecise: its fields are filled from the year down and
 * stop at some precision. Comparisons walk the fields from the year down and
 * return null (unknown) when one value stops before an answer is reached.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

abstract class CqlTemporal
{
    /**
     * The outcome of each ordering operation at each step of the field walk:
     * [this field is smaller, this field is larger, both values end before
     * any precision was asked for, every field compared was equal].
     */
    private const ORDERING = [
        'sameAs' => [false, false, true, true],
        'sameOrBefore' => [true, false, true, true],
        'sameOrAfter' => [false, true, true, true],
        'before' => [true, false, false, false],
        'after' => [false, true, false, false],
    ];

    /**
     * @return list<Precision> the fields of this type, most significant first
     */
    abstract public static function fields(): array;

    abstract public function field(Precision $field): ?int;

    abstract public function getPrecision(): ?Precision;

    /** @return static */
    abstract public function reducedPrecision(?Precision $precision = null): static;

    abstract protected function toZoned(): ZonedInstant;

    abstract protected static function fromZoned(ZonedInstant $instant): static;

    abstract public function toString(): string;

    /**
     * The number of whole unit boundaries crossed between this value and
     * another (CQL "difference in"); a range when either is imprecise.
     */
    abstract public function differenceBetween(mixed $other, Precision $unit): ?Uncertainty;

    /**
     * The number of whole units elapsed between this value and another (CQL
     * "duration in"); a range when either is imprecise.
     */
    abstract public function durationBetween(mixed $other, Precision $unit): ?Uncertainty;

    public function isPrecise(): bool
    {
        foreach (static::fields() as $field) {
            if ($this->field($field) === null) {
                return false;
            }
        }
        return true;
    }

    public function isImprecise(): bool
    {
        return !$this->isPrecise();
    }

    public function isSamePrecision(self|Precision $other): bool
    {
        if ($other instanceof Precision) {
            return $other === $this->getPrecision();
        }
        foreach (static::fields() as $field) {
            if (($this->field($field) === null) !== ($other->field($field) === null)) {
                return false;
            }
        }
        return true;
    }

    public function isMorePrecise(self|Precision $other): bool
    {
        if ($other instanceof Precision) {
            if (in_array($other, static::fields(), true) && $this->field($other) === null) {
                return false;
            }
        } else {
            foreach (static::fields() as $field) {
                if ($other->field($field) !== null && $this->field($field) === null) {
                    return false;
                }
            }
        }
        return !$this->isSamePrecision($other);
    }

    public function isLessPrecise(self|Precision $other): bool
    {
        return !$this->isSamePrecision($other) && !$this->isMorePrecise($other);
    }

    public function sameAs(mixed $other, ?Precision $precision = null): ?bool
    {
        return $this->ordered('sameAs', $other, $precision);
    }

    public function sameOrBefore(mixed $other, ?Precision $precision = null): ?bool
    {
        return $this->ordered('sameOrBefore', $other, $precision);
    }

    public function sameOrAfter(mixed $other, ?Precision $precision = null): ?bool
    {
        return $this->ordered('sameOrAfter', $other, $precision);
    }

    public function before(mixed $other, ?Precision $precision = null): ?bool
    {
        return $this->ordered('before', $other, $precision);
    }

    public function after(mixed $other, ?Precision $precision = null): ?bool
    {
        return $this->ordered('after', $other, $precision);
    }

    public function equals(mixed $other): ?bool
    {
        return self::compareWithDefault($this, $other, null);
    }

    public function equivalent(mixed $other): ?bool
    {
        return self::compareWithDefault($this, $other, false);
    }

    /**
     * Adds an amount of a unit. A value less precise than the unit is moved
     * from its earliest possible moment when adding, or its latest when
     * subtracting, and keeps its own precision. Null when the result passes
     * the largest DateTime.
     */
    public function add(int|float $amount, Precision $unit): ?static
    {
        if ($amount == 0 || $this->field(Precision::Year) === null) {
            return $this->reducedPrecision($this->getPrecision());
        }
        $start = $this->toZoned();
        // Week is never a field, so adding weeks always counts as more precise.
        $offsetIsMorePrecise = $unit === Precision::Week || $this->field($unit) === null;
        $precision = $this->getPrecision();
        if ($offsetIsMorePrecise && $amount < 0 && $precision !== null) {
            $start = $start->endOf($precision);
        }
        $result = static::fromZoned($start->plus($unit, $amount))->reducedPrecision($precision);
        $result = $this->keepTimezoneOffsetOf($result);
        // cql-execution checks only the upper bound here.
        return $result->after(CqlDateTime::maximum()) === true ? null : $result;
    }

    public function successor(): ?static
    {
        $precision = $this->getPrecision();
        return $precision === null ? null : $this->add(1, $precision);
    }

    public function predecessor(): ?static
    {
        $precision = $this->getPrecision();
        return $precision === null ? null : $this->add(-1, $precision);
    }

    public function getFieldFloor(Precision $field): int
    {
        return match ($field) {
            Precision::Month, Precision::Day => 1,
            Precision::Hour, Precision::Minute, Precision::Second, Precision::Millisecond => 0,
            Precision::Year, Precision::Week => throw new \InvalidArgumentException('No floor value for ' . $field->value),
        };
    }

    public function getFieldCeiling(Precision $field): int
    {
        return match ($field) {
            Precision::Month => 12,
            Precision::Day => ZonedInstant::daysInMonth(
                $this->field(Precision::Year) ?? throw new \LogicException('No year'),
                $this->field(Precision::Month) ?? throw new \LogicException('No month'),
            ),
            Precision::Hour => 23,
            Precision::Minute, Precision::Second => 59,
            Precision::Millisecond => 999,
            Precision::Year, Precision::Week => throw new \InvalidArgumentException('No ceiling value for ' . $field->value),
        };
    }

    /**
     * Hook for DateTime: a value with no timezone offset gives its result
     * none either.
     *
     * @param static $result
     * @return static
     */
    protected function keepTimezoneOffsetOf(self $result): static
    {
        return $result;
    }

    /**
     * @return static the other value shifted to this value's offset
     */
    protected function convertedForComparison(self $other): static
    {
        return $other instanceof static ? $other : throw new \LogicException('Type mismatch');
    }

    /**
     * @param key-of<self::ORDERING> $operation
     */
    private function ordered(string $operation, mixed $other, ?Precision $precision): ?bool
    {
        if (!$other instanceof self) {
            return null;
        }
        if ($this instanceof CqlDate && $other instanceof CqlDateTime) {
            return $this->getDateTime()->ordered($operation, $other, $precision);
        }
        if ($this instanceof CqlDateTime && $other instanceof CqlDate) {
            $other = $other->getDateTime();
        }
        if ($precision !== null && !in_array($precision, static::fields(), true)) {
            throw new \InvalidArgumentException('Invalid precision: ' . $precision->value);
        }
        if (Precision::normalizesOffsets($precision)) {
            $other = $this->convertedForComparison($other);
        }

        [$whenLess, $whenGreater, $whenBothEndUnasked, $whenAllEqual] = self::ORDERING[$operation];
        foreach (static::fields() as $field) {
            $mine = $this->field($field);
            $theirs = $other->field($field);
            if ($mine !== null && $theirs !== null) {
                if ($mine < $theirs) {
                    return $whenLess;
                }
                if ($mine > $theirs) {
                    return $whenGreater;
                }
            } elseif ($mine === null && $theirs === null) {
                return $precision === null ? $whenBothEndUnasked : null;
            } else {
                return null;
            }
            if ($precision === $field) {
                break;
            }
        }
        return $whenAllEqual;
    }

    private static function compareWithDefault(self $a, mixed $b, ?bool $default): ?bool
    {
        if (!$b instanceof self || $a::class !== $b::class) {
            return false;
        }
        $b = $a->convertedForComparison($b);
        foreach ($a::fields() as $field) {
            $mine = $a->field($field);
            $theirs = $b->field($field);
            if ($mine !== null && $theirs !== null) {
                // Seconds and milliseconds compare as one decimal value.
                if ($field === Precision::Second) {
                    return $mine * 1000 + ($a->field(Precision::Millisecond) ?? 0)
                        === $theirs * 1000 + ($b->field(Precision::Millisecond) ?? 0);
                }
                if ($mine !== $theirs) {
                    return false;
                }
            } elseif ($mine === null && $theirs === null) {
                return true;
            } else {
                return $default;
            }
        }
        return true;
    }
}
