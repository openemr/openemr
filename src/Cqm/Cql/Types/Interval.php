<?php

/**
 * The CQL Interval type, ported from cql-execution 3.3.2.
 *
 * A bound may be open or closed, and a null bound is unknown when open
 * and unbounded (the type's limit) when closed. Most operations first
 * close the interval: an open bound moves to its successor or
 * predecessor, and a closed null bound to the type's limit, so an
 * Integer interval (1, 5) is [2, 4].
 *
 * cql-execution keeps numbers as JavaScript doubles, so a whole Decimal
 * (1.0) is treated as an Integer here as well. Where it relies on
 * JavaScript's `&&`, `===` or `<` rather than three-valued logic, this
 * port does the same, and where it calls a method on a value that has
 * none (a TypeError there), this throws.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Types;

use OpenEMR\Cqm\Cql\Util\CqlMath;
use OpenEMR\Cqm\Cql\Util\JavaScript;

final readonly class Interval implements \Stringable
{
    /**
     * JavaScript's undefined, which cql-execution's Quantity equality gives
     * for a non-quantity: three-valued and() counts it as true and or() as
     * false. Only the operations that combine such results see it.
     */
    private const UNDEFINED = 'undefined';

    public bool $lowClosed;
    public bool $highClosed;

    /**
     * @param ?string $defaultPointType the ELM point type, used when both bounds are null
     */
    public function __construct(
        public mixed $low,
        public mixed $high,
        ?bool $lowClosed = null,
        ?bool $highClosed = null,
        public ?string $defaultPointType = null,
    ) {
        $this->lowClosed = $lowClosed ?? true;
        $this->highClosed = $highClosed ?? true;
    }

    /** [null, null]: every value. */
    public function isBoundless(): bool
    {
        return $this->low === null && $this->lowClosed && $this->high === null && $this->highClosed;
    }

    /** (null, null): nothing is known. */
    public function isUnknown(): bool
    {
        return $this->low === null && !$this->lowClosed && $this->high === null && !$this->highClosed;
    }

    /** The ELM type of the points, from the low bound, else the high, else the default. */
    public function pointType(): ?string
    {
        $point = $this->low ?? $this->high;
        $type = match (true) {
            is_int($point), is_float($point) => CqlMath::isWhole($point) ? CqlMath::INTEGER_TYPE : CqlMath::DECIMAL_TYPE,
            $point instanceof CqlDateTime => $point->isTime() ? CqlMath::TIME_TYPE : CqlMath::DATETIME_TYPE,
            $point instanceof CqlDate => CqlMath::DATE_TYPE,
            $point instanceof Quantity => CqlMath::QUANTITY_TYPE,
            default => null,
        };
        return $type ?? $this->defaultPointType;
    }

    /** A copy; as in cql-execution, it does not keep the default point type. */
    public function copy(): self
    {
        return new self($this->low, $this->high, $this->lowClosed, $this->highClosed);
    }

    /**
     * Whether a point is in the interval.
     *
     * @throws \InvalidArgumentException for an interval argument
     */
    public function contains(mixed $item, ?Precision $precision = null): ?bool
    {
        // A point equal to a closed bound is in, whatever its type.
        if ($this->lowClosed && $this->low !== null && Comparison::equals($this->low, $item) === true) {
            return true;
        }
        if ($this->highClosed && $this->high !== null && Comparison::equals($this->high, $item) === true) {
            return true;
        }
        if ($item instanceof self) {
            throw new \InvalidArgumentException('Argument to contains must be a point');
        }
        $lowResult = match (true) {
            $this->lowClosed && $this->low === null => true,
            $this->lowClosed => Comparison::lessThanOrEquals($this->low, $item, $precision),
            default => Comparison::lessThan($this->low, $item, $precision),
        };
        $highResult = match (true) {
            $this->highClosed && $this->high === null => true,
            $this->highClosed => Comparison::greaterThanOrEquals($this->high, $item, $precision),
            default => Comparison::greaterThan($this->high, $item, $precision),
        };
        return ThreeValuedLogic::and($lowResult, $highResult);
    }

    /**
     * @throws \InvalidArgumentException for a point argument
     */
    public function properlyIncludes(mixed $other, ?Precision $precision = null): ?bool
    {
        if (!$other instanceof self) {
            throw new \InvalidArgumentException('Argument to properlyIncludes must be an interval');
        }
        return ThreeValuedLogic::and(
            $this->includes($other, $precision),
            ThreeValuedLogic::not($other->includes($this, $precision)),
        );
    }

    /** Whether another interval is within this one; for a point, contains. */
    public function includes(mixed $other, ?Precision $precision = null): ?bool
    {
        if (!$other instanceof self) {
            return $this->contains($other, $precision);
        }
        $a = $this->toClosed();
        $b = $other->toClosed();
        return ThreeValuedLogic::and(
            Comparison::lessThanOrEquals($a->low, $b->low, $precision),
            Comparison::greaterThanOrEquals($a->high, $b->high, $precision),
        );
    }

    /**
     * Whether this interval is within another; for a point, contains. As in
     * cql-execution, the precision is not passed on for an interval.
     */
    public function includedIn(mixed $other, ?Precision $precision = null): ?bool
    {
        if (!$other instanceof self) {
            return $this->contains($other, $precision);
        }
        return $other->includes($this);
    }

    public function overlaps(mixed $item, ?Precision $precision = null): ?bool
    {
        if ($this->isUnknown() || $item === null || ($item instanceof self && $item->isUnknown())) {
            return null;
        }
        if ($this->isBoundless() || ($item instanceof self && $item->isBoundless())) {
            return true;
        }
        $closed = $this->toClosed();
        if ($item instanceof self) {
            $itemClosed = $item->toClosed();
            [$low, $high] = [$itemClosed->low, $itemClosed->high];
        } else {
            [$low, $high] = [$item, $item];
        }
        return ThreeValuedLogic::and(
            Comparison::lessThanOrEquals($closed->low, $high, $precision),
            Comparison::greaterThanOrEquals($closed->high, $low, $precision),
        );
    }

    public function overlapsAfter(mixed $item, ?Precision $precision = null): ?bool
    {
        if ($this->isUnknown() || $item === null || ($item instanceof self && $item->isUnknown())) {
            return null;
        }
        $high = $item instanceof self ? $item->toClosed()->high : $item;
        if ($this->isBoundless()) {
            return Comparison::lessThan($high, CqlMath::maxValueForInstance($high), $precision);
        }
        if ($item instanceof self && $item->isBoundless()) {
            return false;
        }
        $closed = $this->toClosed();
        return ThreeValuedLogic::and(
            Comparison::lessThanOrEquals($closed->low, $high, $precision),
            Comparison::greaterThan($closed->high, $high, $precision),
        );
    }

    public function overlapsBefore(mixed $item, ?Precision $precision = null): ?bool
    {
        if ($this->isUnknown() || $item === null || ($item instanceof self && $item->isUnknown())) {
            return null;
        }
        $low = $item instanceof self ? $item->toClosed()->low : $item;
        if ($this->isBoundless()) {
            return Comparison::greaterThan($low, CqlMath::minValueForInstance($low), $precision);
        }
        if ($item instanceof self && $item->isBoundless()) {
            return false;
        }
        $closed = $this->toClosed();
        return ThreeValuedLogic::and(
            Comparison::lessThan($closed->low, $low, $precision),
            Comparison::greaterThanOrEquals($closed->high, $low, $precision),
        );
    }

    /**
     * The interval covering both, when they overlap or meet; null otherwise.
     *
     * @throws \InvalidArgumentException for a point argument
     */
    public function union(mixed $other): ?self
    {
        if (!$other instanceof self) {
            throw new \InvalidArgumentException('Argument to union must be an interval');
        }
        if ($this->overlaps($other) !== true && $this->meets($other) !== true) {
            return null;
        }
        $a = $this->toClosed();
        $b = $other->toClosed();
        if (Comparison::lessThanOrEquals($a->low, $b->low) === true) {
            [$low, $lowClosed] = [$this->low, $this->lowClosed];
        } elseif (Comparison::greaterThanOrEquals($a->low, $b->low) === true) {
            [$low, $lowClosed] = [$other->low, $other->lowClosed];
        } elseif (self::areNumeric($a->low, $b->low)) {
            [$low, $lowClosed] = [self::numericUncertainty($a->low, $b->low, true), true];
        } elseif ($a->low instanceof CqlDateTime && $b->low instanceof CqlDateTime && $a->low->isMorePrecise($b->low)) {
            [$low, $lowClosed] = [$other->low, $other->lowClosed];
        } else {
            [$low, $lowClosed] = [$this->low, $this->lowClosed];
        }
        if (Comparison::greaterThanOrEquals($a->high, $b->high) === true) {
            [$high, $highClosed] = [$this->high, $this->highClosed];
        } elseif (Comparison::lessThanOrEquals($a->high, $b->high) === true) {
            [$high, $highClosed] = [$other->high, $other->highClosed];
        } elseif (self::areNumeric($a->high, $b->high)) {
            [$high, $highClosed] = [self::numericUncertainty($a->high, $b->high, false), true];
        } elseif ($a->high instanceof CqlDateTime && $b->high instanceof CqlDateTime && $a->high->isMorePrecise($b->high)) {
            [$high, $highClosed] = [$other->high, $other->highClosed];
        } else {
            [$high, $highClosed] = [$this->high, $this->highClosed];
        }
        return new self($low, $high, $lowClosed, $highClosed);
    }

    /**
     * The interval both cover, when they overlap; null otherwise.
     *
     * @throws \InvalidArgumentException for a point argument
     */
    public function intersect(mixed $other): ?self
    {
        if (!$other instanceof self) {
            throw new \InvalidArgumentException('Argument to union must be an interval');
        }
        if ($this->overlaps($other) !== true) {
            return null;
        }
        $a = $this->toClosed();
        $b = $other->toClosed();
        if (Comparison::greaterThanOrEquals($a->low, $b->low) === true) {
            [$low, $lowClosed] = [$this->low, $this->lowClosed];
        } elseif (Comparison::lessThanOrEquals($a->low, $b->low) === true) {
            [$low, $lowClosed] = [$other->low, $other->lowClosed];
        } elseif (self::areNumeric($a->low, $b->low)) {
            [$low, $lowClosed] = [self::numericUncertainty($a->low, $b->low, false), true];
        } elseif ($a->low instanceof CqlDateTime && $b->low instanceof CqlDateTime && $b->low->isMorePrecise($a->low)) {
            [$low, $lowClosed] = [$other->low, $other->lowClosed];
        } else {
            [$low, $lowClosed] = [$this->low, $this->lowClosed];
        }
        if (Comparison::lessThanOrEquals($a->high, $b->high) === true) {
            [$high, $highClosed] = [$this->high, $this->highClosed];
        } elseif (Comparison::greaterThanOrEquals($a->high, $b->high) === true) {
            [$high, $highClosed] = [$other->high, $other->highClosed];
        } elseif (self::areNumeric($a->high, $b->high)) {
            [$high, $highClosed] = [self::numericUncertainty($a->high, $b->high, true), true];
        } elseif ($a->high instanceof CqlDateTime && $b->high instanceof CqlDateTime && $b->high->isMorePrecise($a->high)) {
            [$high, $highClosed] = [$other->high, $other->highClosed];
        } else {
            [$high, $highClosed] = [$this->high, $this->highClosed];
        }
        return new self($low, $high, $lowClosed, $highClosed);
    }

    /**
     * This interval less another that overlaps one end of it; null when
     * the other is inside it or the overlap is unknown.
     *
     * @throws \InvalidArgumentException for a point argument
     */
    public function except(mixed $other): ?self
    {
        if ($other === null) {
            return null;
        }
        if (!$other instanceof self) {
            throw new \InvalidArgumentException('Argument to except must be an interval');
        }
        $overlaps = $this->overlaps($other);
        if ($overlaps === false) {
            return $this;
        }
        if ($overlaps === null) {
            return null;
        }
        $before = $this->overlapsBefore($other);
        $after = $this->overlapsAfter($other);
        if ($before === true && $after === false) {
            return new self($this->low, $other->low, $this->lowClosed, !$other->lowClosed);
        }
        if ($after === true && $before === false) {
            return new self($other->high, $this->high, !$other->highClosed, $this->highClosed);
        }
        return null;
    }

    public function sameAs(mixed $other, ?Precision $precision = null): ?bool
    {
        $other = self::interval($other);
        $numeric = is_int($this->low) || is_float($this->low);
        // With one open null high and both lows known, the starts decide a mismatch.
        if (
            ($this->low !== null && $other->low !== null && $this->high === null && $other->high !== null && !$this->highClosed)
            || ($this->low !== null && $other->low !== null && $this->high !== null && $other->high === null && !$other->highClosed)
            || ($this->low !== null && $other->low !== null && $this->high === null && $other->high === null && !$other->highClosed && !$this->highClosed)
        ) {
            $same = $numeric ? self::strictEquals($this->start(), $other->start()) : self::pointSameAs($this->start(), $other->start(), $precision);
            if ($same !== true) {
                return false;
            }
        } elseif (
            ($this->low !== null && $other->low === null && $this->high !== null && $other->high !== null)
            || ($this->low === null && $other->low !== null && $this->high !== null && $other->high !== null)
            || ($this->low === null && $other->low === null && $this->high !== null && $other->high !== null)
        ) {
            $highNumeric = is_int($this->high) || is_float($this->high);
            $same = $highNumeric ? self::strictEquals($this->end(), $other->end()) : self::pointSameAs($this->end(), $other->end(), $precision);
            if ($same !== true) {
                return false;
            }
        }
        if (
            ($this->low === null && !$this->lowClosed) || ($this->high === null && !$this->highClosed)
            || ($other->low === null && !$other->lowClosed) || ($other->high === null && !$other->highClosed)
        ) {
            return null;
        }
        if ($this->isBoundless()) {
            return $other->isBoundless();
        }
        if ($other->isBoundless()) {
            return false;
        }
        if ($numeric) {
            return self::strictEquals($this->start(), $other->start()) && self::strictEquals($this->end(), $other->end());
        }
        return self::jsAnd(
            self::pointSameAs($this->start(), $other->start(), $precision),
            fn (): ?bool => self::pointSameAs($this->end(), $other->end(), $precision),
        );
    }

    public function sameOrBefore(mixed $other, ?Precision $precision = null): ?bool
    {
        $end = $this->end();
        if ($end === null || $other === null) {
            return null;
        }
        $otherStart = self::interval($other)->start();
        return $otherStart === null ? null : Comparison::lessThanOrEquals($end, $otherStart, $precision);
    }

    public function sameOrAfter(mixed $other, ?Precision $precision = null): ?bool
    {
        $start = $this->start();
        if ($start === null || $other === null) {
            return null;
        }
        $otherEnd = self::interval($other)->end();
        return $otherEnd === null ? null : Comparison::greaterThanOrEquals($start, $otherEnd, $precision);
    }

    public function equals(mixed $other): ?bool
    {
        if (!$other instanceof self) {
            return false;
        }
        $a = $this->toClosed();
        $b = $other->toClosed();
        return self::jsAnd3(self::jsEquals($a->low, $b->low), self::jsEquals($a->high, $b->high));
    }

    /** Whether this interval starts after another interval or a point. */
    public function after(mixed $other, ?Precision $precision = null): ?bool
    {
        $closed = $this->toClosed();
        $other = self::notNull($other);
        $point = $other instanceof self ? $other->toClosed()->high : $other;
        return Comparison::greaterThan($closed->low, $point, $precision);
    }

    /** Whether this interval ends before another interval or a point. */
    public function before(mixed $other, ?Precision $precision = null): ?bool
    {
        $closed = $this->toClosed();
        $other = self::notNull($other);
        $point = $other instanceof self ? $other->toClosed()->low : $other;
        return Comparison::lessThan($closed->high, $point, $precision);
    }

    public function meets(mixed $other, ?Precision $precision = null): ?bool
    {
        return self::jsOr3($this->meetsBeforeOrUndefined($other, $precision), $this->meetsAfterOrUndefined($other, $precision));
    }

    /** Whether this interval starts just after another ends; false when that cannot be decided. */
    public function meetsAfter(mixed $other, ?Precision $precision = null): ?bool
    {
        return self::definedOrNull($this->meetsAfterOrUndefined($other, $precision));
    }

    /** Whether this interval ends just before another starts; false when that cannot be decided. */
    public function meetsBefore(mixed $other, ?Precision $precision = null): ?bool
    {
        return self::definedOrNull($this->meetsBeforeOrUndefined($other, $precision));
    }

    /**
     * @return bool|self::UNDEFINED|null
     */
    private function meetsAfterOrUndefined(mixed $other, ?Precision $precision): bool|string|null
    {
        try {
            $otherHigh = self::interval($other)->toClosed()->high;
            if ($precision !== null && $this->low instanceof CqlDateTime) {
                $next = $otherHigh === null ? null : self::temporal($otherHigh)->add(1, $precision);
                return self::pointSameAs($this->toClosed()->low, $next, $precision);
            }
            return self::jsEquals($this->toClosed()->low, CqlMath::successor($otherHigh));
        } catch (\LogicException | \RuntimeException) {
            return false;
        }
    }

    /**
     * @return bool|self::UNDEFINED|null
     */
    private function meetsBeforeOrUndefined(mixed $other, ?Precision $precision): bool|string|null
    {
        try {
            $otherLow = self::interval($other)->toClosed()->low;
            if ($precision !== null && $this->high instanceof CqlDateTime) {
                $previous = $otherLow === null ? null : self::temporal($otherLow)->add(-1, $precision);
                return self::pointSameAs($this->toClosed()->high, $previous, $precision);
            }
            return self::jsEquals($this->toClosed()->high, CqlMath::predecessor($otherLow));
        } catch (\LogicException | \RuntimeException) {
            return false;
        }
    }

    /** The first point: the closed low bound, the type's minimum, or null when unknown. */
    public function start(): mixed
    {
        if ($this->low === null) {
            return $this->lowClosed ? CqlMath::minValueForInstance($this->high) : null;
        }
        return $this->toClosed()->low;
    }

    /** The last point: the closed high bound, the type's maximum, or null when unknown. */
    public function end(): mixed
    {
        if ($this->high === null) {
            return $this->highClosed ? CqlMath::maxValueForInstance($this->low) : null;
        }
        return $this->toClosed()->high;
    }

    public function starts(mixed $other, ?Precision $precision = null): ?bool
    {
        $other = self::interval($other);
        if ($precision !== null && $this->low instanceof CqlDateTime) {
            $startEqual = self::pointSameAs($this->low, $other->low, $precision);
        } else {
            $startEqual = Comparison::equals($this->low, $other->low);
        }
        $endLessThanOrEqual = Comparison::lessThanOrEquals($this->high, $other->high, $precision);
        return self::jsAnd($startEqual, static fn (): ?bool => $endLessThanOrEqual);
    }

    public function ends(mixed $other, ?Precision $precision = null): ?bool
    {
        $other = self::interval($other);
        $startGreaterThanOrEqual = Comparison::greaterThanOrEquals($this->low, $other->low, $precision);
        if ($precision !== null && $this->low instanceof CqlDateTime) {
            $endEqual = self::pointSameAs($this->high, $other->high, $precision);
        } else {
            $endEqual = Comparison::equals($this->high, $other->high);
        }
        return self::jsAnd($startGreaterThanOrEqual, static fn (): ?bool => $endEqual);
    }

    /**
     * The distance between the closed bounds, rounded to 8 decimal places;
     * null when a bound is uncertain.
     *
     * @throws \InvalidArgumentException for date intervals, or quantities of different units
     */
    public function width(): Quantity|float|null
    {
        return $this->measure(null);
    }

    /**
     * The width plus one point (1 for Integers, 10^-8 for Decimals).
     *
     * @throws \InvalidArgumentException for date intervals, or quantities of different units
     */
    public function size(): Quantity|float|null
    {
        return $this->measure($this->getPointSize());
    }

    /**
     * The size of one point: 1 for an Integer, 10^-8 for a Decimal, one of a
     * date's own precision, a Quantity's step.
     *
     * @throws \InvalidArgumentException when both bounds are null
     */
    public function getPointSize(): Quantity
    {
        $point = $this->low ?? $this->high ?? throw new \InvalidArgumentException('Point type of intervals cannot be determined.');
        if ($point instanceof CqlTemporal) {
            return new Quantity(1, $point->getPrecision()?->value);
        }
        if ($point instanceof Quantity) {
            $size = Quantity::doSubtraction(self::quantity(CqlMath::successor($point)), $point);
            return $size instanceof Quantity ? $size : throw new \UnexpectedValueException('No point size');
        }
        $successor = CqlMath::successor($point);
        if ((!is_int($successor) && !is_float($successor)) || (!is_int($point) && !is_float($point))) {
            throw new \InvalidArgumentException('Point type of intervals cannot be determined.');
        }
        return new Quantity($successor - $point, '1');
    }

    /**
     * The interval with open bounds moved in to closed ones, and closed null
     * bounds set to the type's limits; an open null bound stays null.
     */
    public function toClosed(): self
    {
        $lowClosed = $this->lowClosed || $this->low !== null;
        $highClosed = $this->highClosed || $this->high !== null;
        $type = $this->pointType();
        if ($type === null) {
            return new self($this->low, $this->high, $lowClosed, $highClosed);
        }
        $low = match (true) {
            $this->lowClosed && $this->low === null => CqlMath::minValueForType($type),
            !$this->lowClosed && $this->low !== null => CqlMath::successor($this->low),
            default => $this->low,
        };
        $high = match (true) {
            $this->highClosed && $this->high === null => CqlMath::maxValueForType($type),
            !$this->highClosed && $this->high !== null => CqlMath::predecessor($this->high),
            default => $this->high,
        };
        // An unknown bound is anywhere between the type's limit and the other bound.
        $low ??= new Uncertainty(CqlMath::minValueForType($type), $high);
        $high ??= new Uncertainty($low, CqlMath::maxValueForType($type));
        return new self($low, $high, $lowClosed, $highClosed);
    }

    public function toString(): string
    {
        return ($this->lowClosed ? '[' : '(') . self::pointToString($this->low) . ', '
            . self::pointToString($this->high) . ($this->highClosed ? ']' : ')');
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private function measure(?Quantity $pointSize): Quantity|float|null
    {
        if (
            $this->low instanceof CqlTemporal || $this->high instanceof CqlTemporal
        ) {
            $what = $pointSize === null ? 'Width' : 'Size';
            throw new \InvalidArgumentException("$what of Date, DateTime, and Time intervals is not supported");
        }
        $closed = $this->toClosed();
        if ($closed->low instanceof Uncertainty || $closed->high instanceof Uncertainty) {
            return null;
        }
        $extra = $pointSize === null ? 0 : $pointSize->value;
        if ($closed->low instanceof Quantity) {
            if (!$closed->high instanceof Quantity || $closed->low->unit !== $closed->high->unit) {
                $what = $pointSize === null ? 'width' : 'size';
                throw new \InvalidArgumentException("Cannot calculate $what of Quantity Interval with different units");
            }
            return new Quantity(self::roundTo8(abs($closed->high->value - $closed->low->value) + $extra), $closed->low->unit);
        }
        if ($closed->low === null) {
            throw new \InvalidArgumentException('Point type of intervals cannot be determined.');
        }
        // JavaScript subtraction: null counts as 0, and a date as NaN.
        return self::roundTo8(abs(self::jsNumber($closed->high) - self::jsNumber($closed->low)) + $extra);
    }

    /** A value as JavaScript's arithmetic reads it. */
    private static function jsNumber(mixed $value): int|float
    {
        return match (true) {
            is_int($value), is_float($value) => $value,
            $value === null, $value === false => 0,
            $value === true => 1,
            is_string($value) => JavaScript::toNumber($value),
            default => NAN,
        };
    }

    /**
     * cql-execution's equality, keeping the undefined its Quantity gives
     * for anything other than a quantity.
     *
     * @return bool|self::UNDEFINED|null
     */
    private static function jsEquals(mixed $a, mixed $b): bool|string|null
    {
        if ($a instanceof Quantity && $b !== null && !$b instanceof Quantity) {
            return self::UNDEFINED;
        }
        return Comparison::equals($a, $b);
    }

    /**
     * ThreeValuedLogic.and, where undefined is neither false nor null.
     *
     * @param bool|self::UNDEFINED|null ...$values
     */
    private static function jsAnd3(bool|string|null ...$values): ?bool
    {
        if (in_array(false, $values, true)) {
            return false;
        }
        return in_array(null, $values, true) ? null : true;
    }

    /**
     * ThreeValuedLogic.or, where undefined is neither true nor null.
     *
     * @param bool|self::UNDEFINED|null ...$values
     */
    private static function jsOr3(bool|string|null ...$values): ?bool
    {
        if (in_array(true, $values, true)) {
            return true;
        }
        return in_array(null, $values, true) ? null : false;
    }

    /**
     * @param bool|self::UNDEFINED|null $value
     */
    private static function definedOrNull(bool|string|null $value): ?bool
    {
        return is_bool($value) ? $value : null;
    }

    /** Math.round(x * 10^8) / 10^8. */
    private static function roundTo8(int|float $value): float
    {
        return JavaScript::round($value * 100000000.0) / 100000000.0;
    }

    private static function areNumeric(mixed $x, mixed $y): bool
    {
        foreach ([$x, $y] as $z) {
            if (!is_int($z) && !is_float($z) && !($z instanceof Uncertainty && (is_int($z->low) || is_float($z->low)))) {
                return false;
            }
        }
        return true;
    }

    /**
     * The lowest (or highest) of two numbers or numeric uncertainties, as
     * an uncertainty when its ends differ.
     */
    private static function numericUncertainty(mixed $x, mixed $y, bool $lowest): mixed
    {
        $x = $x instanceof Uncertainty ? $x : new Uncertainty($x, $x);
        $y = $y instanceof Uncertainty ? $y : new Uncertainty($y, $y);
        $pick = static fn (mixed $a, mixed $b): mixed => ($lowest ? self::jsLess($a, $b) : self::jsLess($b, $a)) ? $a : $b;
        $low = $pick($x->low, $y->low);
        $high = $pick($x->high, $y->high);
        return self::strictEquals($low, $high) ? $low : new Uncertainty($low, $high);
    }

    /** JavaScript's `<` on numbers, where null counts as 0. */
    private static function jsLess(mixed $a, mixed $b): bool
    {
        $a ??= 0;
        $b ??= 0;
        return (is_int($a) || is_float($a)) && (is_int($b) || is_float($b)) && $a < $b;
    }

    /** JavaScript's `===`: numbers by value, anything else by identity. */
    private static function strictEquals(mixed $a, mixed $b): bool
    {
        if ((is_int($a) || is_float($a)) && (is_int($b) || is_float($b))) {
            return (float) $a === (float) $b;
        }
        return $a === $b;
    }

    /**
     * JavaScript's `a && b` over three-valued results: a false or null left
     * side is the result.
     *
     * @param \Closure(): ?bool $b
     */
    private static function jsAnd(?bool $a, \Closure $b): ?bool
    {
        return $a === true ? $b() : $a;
    }

    /**
     * A date's sameAs; cql-execution calls it on whatever the bound is, so
     * anything else, or a null argument, fails there as here.
     */
    private static function pointSameAs(mixed $a, mixed $b, ?Precision $precision): ?bool
    {
        if ($b === null) {
            throw new \UnexpectedValueException('Cannot compare with null');
        }
        return self::temporal($a)->sameAs($b, $precision);
    }

    private static function temporal(mixed $value): CqlTemporal
    {
        return $value instanceof CqlTemporal ? $value : throw new \UnexpectedValueException('Not a date or DateTime');
    }

    private static function quantity(mixed $value): Quantity
    {
        return $value instanceof Quantity ? $value : throw new \UnexpectedValueException('Not a quantity');
    }

    private static function interval(mixed $value): self
    {
        return $value instanceof self ? $value : throw new \UnexpectedValueException('Not an interval');
    }

    private static function notNull(mixed $value): mixed
    {
        return $value ?? throw new \UnexpectedValueException('Missing argument');
    }

    private static function pointToString(mixed $value): string
    {
        return match (true) {
            is_int($value), is_float($value) => JavaScript::numberToString($value),
            $value instanceof \Stringable => (string) $value,
            is_string($value) => $value,
            default => throw new \UnexpectedValueException('Interval bound has no string form'),
        };
    }
}
