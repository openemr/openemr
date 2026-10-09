<?php

/**
 * Number limits and rounding of CQL Integer and Decimal values, ported from
 * cql-execution 3.3.2's util/math.
 *
 * cql-execution keeps every number as a JavaScript double, and treats any
 * whole-valued one as an Integer when checking limits; this port does the
 * same.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Util;

use OpenEMR\Cqm\Cql\Types\CqlDate;
use OpenEMR\Cqm\Cql\Types\CqlDateTime;
use OpenEMR\Cqm\Cql\Types\Quantity;
use OpenEMR\Cqm\Cql\Types\Uncertainty;

final class CqlMath
{
    public const MAX_INT_VALUE = 2147483647;
    public const MIN_INT_VALUE = -2147483648;
    public const MAX_FLOAT_VALUE = 99999999999999999999.99999999;
    public const MIN_FLOAT_VALUE = -99999999999999999999.99999999;
    public const MIN_FLOAT_PRECISION_VALUE = 1e-8;

    /** ELM type names of the point types with limits. */
    public const INTEGER_TYPE = '{urn:hl7-org:elm-types:r1}Integer';
    public const DECIMAL_TYPE = '{urn:hl7-org:elm-types:r1}Decimal';
    public const DATETIME_TYPE = '{urn:hl7-org:elm-types:r1}DateTime';
    public const DATE_TYPE = '{urn:hl7-org:elm-types:r1}Date';
    public const TIME_TYPE = '{urn:hl7-org:elm-types:r1}Time';
    public const QUANTITY_TYPE = '{urn:hl7-org:elm-types:r1}Quantity';

    public static function isValidInteger(int|float $value): bool
    {
        return !is_nan((float) $value) && $value <= self::MAX_INT_VALUE && $value >= self::MIN_INT_VALUE;
    }

    public static function isValidDecimal(int|float $value): bool
    {
        return !is_nan((float) $value) && $value <= self::MAX_FLOAT_VALUE && $value >= self::MIN_FLOAT_VALUE;
    }

    /** Number.isInteger: finite and whole. */
    public static function isWhole(int|float $value): bool
    {
        return is_int($value) || (is_finite($value) && floor($value) === $value);
    }

    /**
     * Whether a value is outside the range of its CQL type: Integer when
     * whole, Decimal otherwise, a Quantity by its value, a date or
     * DateTime outside years 1 to 9999, and an Uncertainty by either end.
     * As in cql-execution, any other object counts as overflowing, since
     * it is not a number.
     */
    public static function overflowsOrUnderflows(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        if ($value instanceof Quantity) {
            return !self::isValidDecimal($value->value);
        }
        if ($value instanceof CqlDateTime) {
            if ($value->isTime()) {
                throw new \LogicException('CQL Time values are not supported');
            }
            return $value->after(CqlDateTime::maximum()) === true || $value->before(CqlDateTime::minimum()) === true;
        }
        if ($value instanceof CqlDate) {
            return $value->after(new CqlDate(9999, 12, 31)) === true || $value->before(new CqlDate(1, 1, 1)) === true;
        }
        if ($value instanceof Uncertainty) {
            return self::overflowsOrUnderflows($value->low) || self::overflowsOrUnderflows($value->high);
        }
        if (is_string($value)) {
            return !self::isValidDecimal(JavaScript::toNumber($value));
        }
        if (is_bool($value)) {
            return false;
        }
        if (!is_int($value) && !is_float($value)) {
            return true;
        }
        return self::isWhole($value) ? !self::isValidInteger($value) : !self::isValidDecimal($value);
    }

    /**
     * Cuts a decimal to 8 places by its string form; a number written
     * with an exponent is left as it is.
     */
    public static function limitDecimalPrecision(float $value): float
    {
        $text = JavaScript::numberToString($value);
        if (str_contains($text, 'e')) {
            return $value;
        }
        $parts = explode('.', $text);
        if (isset($parts[1]) && strlen($parts[1]) > 8) {
            $text = $parts[0] . '.' . substr($parts[1], 0, 8);
        }
        return JavaScript::parseFloat($text);
    }

    /**
     * Rounds a number to 10^exponent the way cql-execution's decimalAdjust
     * does: by shifting the decimal exponent of its string form, rounding,
     * and shifting back. Infinite and NaN values give NaN.
     */
    public static function decimalAdjust(float $value, int $exponent): float
    {
        if ($exponent === 0) {
            return JavaScript::round($value);
        }
        if (is_nan($value)) {
            return NAN;
        }
        $shifted = JavaScript::round(self::withShiftedExponent(JavaScript::numberToString($value), -$exponent));
        return self::withShiftedExponent(JavaScript::numberToString($shifted), $exponent);
    }

    /**
     * The next value of a point's type: an Integer plus 1, a Decimal plus
     * 10^-8, a date or DateTime at its own precision, a Quantity by its
     * value. Null for null and for values without a successor.
     *
     * @throws CqlOverflowException past the largest value
     */
    public static function successor(mixed $value): mixed
    {
        return self::step($value, 1);
    }

    /**
     * @throws CqlOverflowException past the smallest value
     */
    public static function predecessor(mixed $value): mixed
    {
        return self::step($value, -1);
    }

    /** The largest value of a point's type; null when it has none. */
    public static function maxValueForInstance(mixed $value): mixed
    {
        return self::limitForInstance($value, true);
    }

    public static function minValueForInstance(mixed $value): mixed
    {
        return self::limitForInstance($value, false);
    }

    /**
     * The largest value of an ELM type; a Quantity takes its unit from an
     * instance, and is null without one.
     */
    public static function maxValueForType(?string $type, ?Quantity $quantity = null): mixed
    {
        return self::limitForType($type, $quantity, true);
    }

    public static function minValueForType(?string $type, ?Quantity $quantity = null): mixed
    {
        return self::limitForType($type, $quantity, false);
    }

    private static function step(mixed $value, int $direction): mixed
    {
        if (is_int($value) || is_float($value)) {
            if (self::isWhole($value)) {
                if ($direction > 0 ? $value >= self::MAX_INT_VALUE : $value <= self::MIN_INT_VALUE) {
                    throw new CqlOverflowException('Integer overflow');
                }
                return $value + $direction;
            }
            if ($direction > 0 ? $value >= self::MAX_FLOAT_VALUE : $value <= self::MIN_FLOAT_VALUE) {
                throw new CqlOverflowException('Decimal overflow');
            }
            return $value + $direction * self::MIN_FLOAT_PRECISION_VALUE;
        }
        if ($value instanceof CqlDateTime) {
            if ($value->isTime()) {
                throw new \LogicException('CQL Time values are not supported');
            }
            $limit = $direction > 0 ? CqlDateTime::maximum() : CqlDateTime::minimum();
            if ($value->sameAs($limit) === true) {
                throw new CqlOverflowException('DateTime overflow');
            }
            return $direction > 0 ? $value->successor() : $value->predecessor();
        }
        if ($value instanceof CqlDate) {
            $limit = $direction > 0 ? new CqlDate(9999, 12, 31) : new CqlDate(1, 1, 1);
            if ($value->sameAs($limit) === true) {
                throw new CqlOverflowException('Date overflow');
            }
            return $direction > 0 ? $value->successor() : $value->predecessor();
        }
        if ($value instanceof Uncertainty) {
            // The bound that moves toward the limit keeps its value at the limit.
            try {
                $far = self::step($direction > 0 ? $value->high : $value->low, $direction);
            } catch (CqlOverflowException) {
                $far = $direction > 0 ? $value->high : $value->low;
            }
            $near = self::step($direction > 0 ? $value->low : $value->high, $direction);
            return $direction > 0 ? new Uncertainty($near, $far) : new Uncertainty($far, $near);
        }
        if ($value instanceof Quantity) {
            $stepped = self::step($value->value, $direction);
            return new Quantity(is_int($stepped) || is_float($stepped) ? $stepped : null, $value->unit);
        }
        return null;
    }

    private static function limitForInstance(mixed $value, bool $max): mixed
    {
        if (is_int($value) || is_float($value)) {
            return self::numberLimit($value, $max);
        }
        if ($value instanceof CqlDateTime) {
            if ($value->isTime()) {
                throw new \LogicException('CQL Time values are not supported');
            }
            return $max ? CqlDateTime::maximum() : CqlDateTime::minimum();
        }
        if ($value instanceof CqlDate) {
            return $max ? new CqlDate(9999, 12, 31) : new CqlDate(1, 1, 1);
        }
        if ($value instanceof Quantity) {
            return new Quantity(self::numberLimit($value->value, $max), $value->unit);
        }
        return null;
    }

    /** The Integer limit for a whole number, the Decimal limit otherwise. */
    private static function numberLimit(int|float $value, bool $max): int|float
    {
        if (self::isWhole($value)) {
            return $max ? self::MAX_INT_VALUE : self::MIN_INT_VALUE;
        }
        return $max ? self::MAX_FLOAT_VALUE : self::MIN_FLOAT_VALUE;
    }

    private static function limitForType(?string $type, ?Quantity $quantity, bool $max): mixed
    {
        return match ($type) {
            self::INTEGER_TYPE => $max ? self::MAX_INT_VALUE : self::MIN_INT_VALUE,
            self::DECIMAL_TYPE => $max ? self::MAX_FLOAT_VALUE : self::MIN_FLOAT_VALUE,
            self::DATETIME_TYPE => $max ? CqlDateTime::maximum() : CqlDateTime::minimum(),
            self::DATE_TYPE => $max ? new CqlDate(9999, 12, 31) : new CqlDate(1, 1, 1),
            self::TIME_TYPE => throw new \LogicException('CQL Time values are not supported'),
            self::QUANTITY_TYPE => $quantity === null ? null : self::limitForInstance($quantity, $max),
            default => null,
        };
    }

    /** decimalAdjust's step: "<mantissa>e<exponent + shift>" read back as a number. */
    private static function withShiftedExponent(string $number, int $shift): float
    {
        $parts = explode('e', $number);
        $exponent = isset($parts[1]) ? (int) $parts[1] + $shift : $shift;
        return JavaScript::toNumber($parts[0] . 'e' . $exponent);
    }
}
