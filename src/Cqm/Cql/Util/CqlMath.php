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

    /** decimalAdjust's step: "<mantissa>e<exponent + shift>" read back as a number. */
    private static function withShiftedExponent(string $number, int $shift): float
    {
        $parts = explode('e', $number);
        $exponent = isset($parts[1]) ? (int) $parts[1] + $shift : $shift;
        return JavaScript::toNumber($parts[0] . 'e' . $exponent);
    }
}
