<?php

/**
 * The CQL Quantity type, a decimal value with a UCUM unit, ported from
 * cql-execution 3.3.2. Comparisons convert the other quantity to this one's
 * unit and are null when the units do not convert; arithmetic results are
 * rounded to 8 decimal places.
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
use OpenEMR\Cqm\Cql\Util\Units;

final readonly class Quantity implements \Stringable
{
    public float $value;

    /**
     * @throws \InvalidArgumentException for a NaN or out-of-range value, or an invalid unit
     */
    public function __construct(int|float|null $value, public ?string $unit)
    {
        if ($value === null || is_nan((float) $value)) {
            throw new \InvalidArgumentException('Cannot create a quantity with an undefined value');
        }
        if (!CqlMath::isValidDecimal($value)) {
            throw new \InvalidArgumentException('Cannot create a quantity with an invalid decimal value');
        }
        if ($unit !== null && !Units::checkUnit($unit)) {
            throw new \InvalidArgumentException("Invalid UCUM unit: '$unit'.");
        }
        $this->value = (float) $value;
    }

    /**
     * Reads "<number> '<unit>'" or a bare number (unit ""); null when there
     * is no number or it is out of range.
     */
    public static function parse(string $string): ?self
    {
        if (preg_match("/([+|-]?\\d+\\.?\\d*)\\s*('(.+)')?/", $string, $m, PREG_UNMATCHED_AS_NULL) !== 1) {
            return null;
        }
        $value = JavaScript::parseFloat($m[1]);
        if (!CqlMath::isValidDecimal($value)) {
            return null;
        }
        return new self($value, $m[3] !== null ? JavaScript::trim($m[3]) : '');
    }

    public function sameOrBefore(mixed $other): ?bool
    {
        $otherValue = $this->convertedValueOf($other);
        return $otherValue === null ? null : $this->value <= $otherValue;
    }

    public function sameOrAfter(mixed $other): ?bool
    {
        $otherValue = $this->convertedValueOf($other);
        return $otherValue === null ? null : $this->value >= $otherValue;
    }

    public function after(mixed $other): ?bool
    {
        $otherValue = $this->convertedValueOf($other);
        return $otherValue === null ? null : $this->value > $otherValue;
    }

    public function before(mixed $other): ?bool
    {
        $otherValue = $this->convertedValueOf($other);
        return $otherValue === null ? null : $this->value < $otherValue;
    }

    /**
     * Equal when the other converts to this unit and matches this value
     * rounded to 8 decimal places. A quantity without a unit equals only
     * another without one.
     */
    public function equals(mixed $other): ?bool
    {
        if (!$other instanceof self) {
            return null;
        }
        $hasUnit = self::hasUnit($this->unit);
        $otherHasUnit = self::hasUnit($other->unit);
        if ($hasUnit !== $otherHasUnit) {
            return false;
        }
        if (!$hasUnit) {
            return $this->value === $other->value;
        }
        $otherValue = Units::convertUnit($other->value, $other->unit, $this->unit);
        return $otherValue === null ? null : CqlMath::decimalAdjust($this->value, -8) === $otherValue;
    }

    /**
     * @throws \InvalidArgumentException when the units do not convert
     */
    public function convertUnit(?string $toUnit): self
    {
        return new self(Units::convertUnit($this->value, $this->unit, $toUnit), $toUnit);
    }

    public function dividedBy(self|int|float|null $other): ?self
    {
        if ($other === null || $other === 0 || $other === 0.0 || ($other instanceof self && $other->value == 0)) {
            return null;
        }
        $other = $other instanceof self ? $other : new self($other, '1');
        [$value1, $unit1, $value2, $unit2] = Units::normalizeUnitsWhenPossible($this->value, $this->unit, $other->value, $other->unit);
        $resultValue = fdiv($value1, $value2);
        $resultUnit = Units::getQuotientOfUnits($unit1, $unit2);
        if ($resultUnit === null || CqlMath::overflowsOrUnderflows($resultValue)) {
            return null;
        }
        return new self(CqlMath::decimalAdjust($resultValue, -8), $resultUnit);
    }

    public function multiplyBy(self|int|float|null $other): ?self
    {
        if ($other === null) {
            return null;
        }
        $other = $other instanceof self ? $other : new self($other, '1');
        [$value1, $unit1, $value2, $unit2] = Units::normalizeUnitsWhenPossible($this->value, $this->unit, $other->value, $other->unit);
        $resultValue = $value1 * $value2;
        $resultUnit = Units::getProductOfUnits($unit1, $unit2);
        if ($resultUnit === null || CqlMath::overflowsOrUnderflows($resultValue)) {
            return null;
        }
        return new self(CqlMath::decimalAdjust($resultValue, -8), $resultUnit);
    }

    /**
     * a + b for two quantities, or a date plus a time quantity.
     *
     * @throws \InvalidArgumentException for any other operands
     */
    public static function doAddition(Quantity|CqlTemporal $a, mixed $b): Quantity|CqlTemporal|null
    {
        return self::doScaledAddition($a, $b, 1);
    }

    /**
     * a - b for two quantities, or a date minus a time quantity.
     *
     * @throws \InvalidArgumentException for any other operands
     */
    public static function doSubtraction(Quantity|CqlTemporal $a, mixed $b): Quantity|CqlTemporal|null
    {
        return self::doScaledAddition($a, $b, -1);
    }

    public function toString(): string
    {
        return JavaScript::numberToString($this->value) . " '" . ($this->unit ?? 'null') . "'";
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    private static function doScaledAddition(Quantity|CqlTemporal $a, mixed $b, int $scale): Quantity|CqlTemporal|null
    {
        if (!$b instanceof self) {
            throw new \InvalidArgumentException('Unsupported argument types.');
        }
        if ($a instanceof self) {
            [$value1, $unit1, $value2, $unit2] = Units::normalizeUnitsWhenPossible($a->value, $a->unit, $b->value * $scale, $b->unit);
            if ($unit1 !== $unit2) {
                return null;
            }
            $sum = $value1 + $value2;
            return CqlMath::overflowsOrUnderflows($sum) ? null : new self($sum, $unit1);
        }
        // A date or DateTime takes the CQL name of a time unit.
        $unit = Units::convertToCqlDateUnit($b->unit) ?? $b->unit ?? '';
        $amount = $b->value * $scale;
        if ($amount == 0 || $a->field(Precision::Year) === null) {
            // An unchanged copy, whatever the unit, as cql-execution returns
            // before it reads the unit.
            return $a->add(0, Precision::Year);
        }
        $precision = Precision::tryFrom($unit)
            ?? throw new \InvalidArgumentException("Invalid unit $unit");
        return $a->add($amount, $precision);
    }

    /** JavaScript truthiness of a unit: null and "" count as none. */
    private static function hasUnit(?string $unit): bool
    {
        return $unit !== null && $unit !== '';
    }

    private function convertedValueOf(mixed $other): ?float
    {
        if (!$other instanceof self) {
            return null;
        }
        return Units::convertUnit($other->value, $other->unit, $this->unit);
    }
}
