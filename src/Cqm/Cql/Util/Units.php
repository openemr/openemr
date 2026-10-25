<?php

/**
 * Unit handling for CQL quantities, ported from cql-execution 3.3.2's
 * util/units: validation and conversion through UCUM, CQL calendar unit
 * names, and the units of products and quotients.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Cqm\Cql\Util;

use OpenEMR\Cqm\Cql\Ucum\Ucum;

final class Units
{
    /**
     * CQL calendar units and the UCUM units they mean; years and months are
     * the Gregorian ones, as the CQL specification bases dates on that
     * calendar.
     */
    private const CQL_TO_UCUM_DATE_UNITS = [
        'years' => 'a_g',
        'year' => 'a_g',
        'months' => 'mo_g',
        'month' => 'mo_g',
        'weeks' => 'wk',
        'week' => 'wk',
        'days' => 'd',
        'day' => 'd',
        'hours' => 'h',
        'hour' => 'h',
        'minutes' => 'min',
        'minute' => 'min',
        'seconds' => 's',
        'second' => 's',
        'milliseconds' => 'ms',
        'millisecond' => 'ms',
    ];

    private const UCUM_TO_CQL_DATE_UNITS = [
        'a' => 'year',
        'a_j' => 'year',
        'a_g' => 'year',
        'mo' => 'month',
        'mo_j' => 'month',
        'mo_g' => 'month',
        'wk' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'min' => 'minute',
        's' => 'second',
        'ms' => 'millisecond',
    ];

    /** @var array<string, bool> */
    private static array $validity = [];

    /**
     * Whether a unit is valid UCUM; an empty unit counts as 1 and a CQL
     * calendar unit as its UCUM unit.
     */
    public static function checkUnit(?string $unit): bool
    {
        $unit = self::fixUnit($unit);
        return self::$validity[$unit] ??= Ucum::instance()->isValid($unit);
    }

    /**
     * Converts a value between units, rounded to 8 decimal places; null when
     * the units do not convert.
     */
    public static function convertUnit(float $value, ?string $fromUnit, ?string $toUnit, bool $adjustPrecision = true): ?float
    {
        $result = Ucum::instance()->convert(self::fixUnit($fromUnit), $value, self::fixUnit($toUnit));
        if ($result === null) {
            return null;
        }
        return $adjustPrecision ? CqlMath::decimalAdjust($result, -8) : $result;
    }

    /**
     * Brings two values to the same unit when they convert, preferring the
     * smaller unit. Two CQL calendar units come back as CQL unit names.
     *
     * @return array{float, ?string, float, ?string}
     */
    public static function normalizeUnitsWhenPossible(float $value1, ?string $unit1, float $value2, ?string $unit2): array
    {
        $useCqlDateUnits = isset(self::CQL_TO_UCUM_DATE_UNITS[$unit1 ?? ''], self::CQL_TO_UCUM_DATE_UNITS[$unit2 ?? '']);
        $result = static fn (string $unit): ?string => $useCqlDateUnits ? self::convertToCqlDateUnit($unit) : $unit;
        $unit1 = self::fixUnit($unit1);
        $unit2 = self::fixUnit($unit2);
        if ($unit1 === $unit2) {
            return [$value1, $unit1, $value2, $unit2];
        }
        $baseUnit1 = self::getBaseUnitAndPower($unit1)[0];
        $baseUnit2 = self::getBaseUnitAndPower($unit2)[0];
        $converted2 = self::convertToBaseUnit($value2, $unit2, $baseUnit1);
        if ($converted2 === null) {
            return [$value1, $result($unit1), $value2, $result($unit2)];
        }
        [$newValue2, $newUnit2] = $converted2;
        if ($newValue2 >= $value2) {
            return [$value1, $result($unit1), $newValue2, $result($newUnit2)];
        }
        // A conversion to a larger unit, so go the other way.
        $converted1 = self::convertToBaseUnit($value1, $unit1, $baseUnit2);
        if ($converted1 === null) {
            return [$value1, $result($unit1), $newValue2, $result($newUnit2)];
        }
        [$newValue1, $newUnit1] = $converted1;
        return [$newValue1, $result($newUnit1), $value2, $result($unit2)];
    }

    /** The CQL calendar unit name for a CQL or UCUM time unit, singular. */
    public static function convertToCqlDateUnit(?string $unit): ?string
    {
        if ($unit === null) {
            return null;
        }
        if (isset(self::CQL_TO_UCUM_DATE_UNITS[$unit])) {
            return preg_replace('/s$/D', '', $unit) ?? $unit;
        }
        return self::UCUM_TO_CQL_DATE_UNITS[$unit] ?? null;
    }

    /**
     * 1 when the first unit is the larger, -1 when the smaller, 0 when they
     * are the same or do not convert.
     */
    public static function compareUnits(?string $unit1, ?string $unit2): int
    {
        $c = self::convertUnit(1, $unit1, $unit2);
        if ($c !== null && $c > 1) {
            return 1;
        }
        if ($c !== null && $c != 0 && $c < 1) {
            return -1;
        }
        return 0;
    }

    /**
     * The unit of a product, combining the powers of like factors; null
     * when either unit is invalid.
     */
    public static function getProductOfUnits(?string $unit1, ?string $unit2): ?string
    {
        $unit1 = self::fixEmptyUnit($unit1);
        $unit2 = self::fixEmptyUnit($unit2);
        if (!self::checkUnit($unit1) || !self::checkUnit($unit2)) {
            return null;
        }
        if (str_contains($unit1, '/') || str_contains($unit2, '/')) {
            // Combine the numerators and the denominators, then divide.
            [$numerator1, $denominator1] = self::splitQuotient($unit1);
            [$numerator2, $denominator2] = self::splitQuotient($unit2);
            return self::getQuotientOfUnits(
                self::getProductOfUnits($numerator1, $numerator2),
                self::getProductOfUnits($denominator1, $denominator2),
            );
        }
        $powers = [];
        foreach ([...explode('.', $unit1), ...explode('.', $unit2)] as $factor) {
            [$base, $power] = self::getBaseUnitAndPower($factor);
            if ($base === '1' || $power == 0) {
                continue;
            }
            $powers[$base] = self::accumulated($powers, $base) + $power;
        }
        $factors = [];
        foreach ($powers as $base => $power) {
            $factors[] = $base . ($power > 1 ? JavaScript::numberToString($power) : '');
        }
        return self::fixUnit(implode('.', $factors));
    }

    /**
     * The unit of a quotient: simplified when neither unit is itself a
     * quotient, otherwise built from the parts. Null when either unit is
     * invalid.
     */
    public static function getQuotientOfUnits(?string $unit1, ?string $unit2): ?string
    {
        $unit1 = self::fixEmptyUnit($unit1);
        $unit2 = self::fixEmptyUnit($unit2);
        if (!self::checkUnit($unit1) || !self::checkUnit($unit2)) {
            return null;
        }
        if (!str_contains($unit1, '/') && !str_contains($unit2, '/')) {
            $powers = [];
            foreach (explode('.', $unit1) as $factor) {
                [$base, $power] = self::getBaseUnitAndPower($factor);
                $powers[$base] = self::accumulated($powers, $base) + $power;
            }
            foreach (explode('.', $unit2) as $factor) {
                [$base, $power] = self::getBaseUnitAndPower($factor);
                $powers[$base] = self::accumulated($powers, $base) - $power;
            }
            $numerator = [];
            $denominator = [];
            foreach ($powers as $base => $power) {
                $base = (string) $base;
                if ($base === '1') {
                    continue;
                }
                if ($power > 0) {
                    $numerator[] = $base . ($power > 1 ? JavaScript::numberToString($power) : '');
                } elseif ($power < 0) {
                    $denominator[] = $base . ($power < -1 ? JavaScript::numberToString($power * -1) : '');
                }
            }
            $denominatorText = implode('.', $denominator);
            if (str_contains($denominatorText, '.')) {
                $denominatorText = "($denominatorText)";
            }
            return self::fixUnit(implode('.', $numerator) . ($denominatorText !== '' ? '/' . $denominatorText : ''));
        }
        if ($unit1 === $unit2) {
            return '1';
        }
        if ($unit2 === '1') {
            return $unit1;
        }
        $denominator = preg_match('/[.\/]/', $unit2) === 1 ? "($unit2)" : $unit2;
        return $unit1 === '1' ? "/$denominator" : "$unit1/$denominator";
    }

    /**
     * The power so far, where JavaScript's `|| 0` also turns NaN into 0.
     *
     * @param array<int|string, int|float> $powers
     */
    private static function accumulated(array $powers, string $base): int|float
    {
        $power = $powers[$base] ?? 0;
        return is_float($power) && is_nan($power) ? 0 : $power;
    }

    /**
     * The numerator and denominator of a unit, split at its first "/".
     *
     * @return array{string, ?string}
     */
    private static function splitQuotient(string $unit): array
    {
        $slash = strpos($unit, '/');
        return $slash === false ? [$unit, null] : [substr($unit, 0, $slash), substr($unit, $slash + 1)];
    }

    /**
     * @return array{float, string}|null
     */
    private static function convertToBaseUnit(float $value, string $fromUnit, string $toBaseUnit): ?array
    {
        $fromPower = self::getBaseUnitAndPower($fromUnit)[1];
        $toUnit = $fromPower === 1 ? $toBaseUnit : $toBaseUnit . JavaScript::numberToString($fromPower);
        $newValue = self::convertUnit($value, $fromUnit, $toUnit);
        return $newValue === null ? null : [$newValue, $toUnit];
    }

    /**
     * A simple unit's base and power ("m2" is m to the 2); a unit with an
     * operator is its own base, to the 1. The power is NaN for a lone "-".
     *
     * @return array{string, int|float}
     */
    private static function getBaseUnitAndPower(string $unit): array
    {
        if (preg_match('/[.\/]/', $unit) === 1) {
            return [$unit, 1];
        }
        $unit = self::fixUnit($unit);
        if (preg_match('/^(.*[^\-\d])?([-]?\d*)$/D', $unit, $m, PREG_UNMATCHED_AS_NULL) !== 1) {
            throw new \UnexpectedValueException('Unit has a line break');
        }
        $term = $m[1];
        $power = $m[2];
        if ($term === null) {
            $term = $power;
            $power = '1';
        } elseif ($power === '') {
            $power = '1';
        }
        return [$term, JavaScript::parseSignedDigits($power)];
    }

    private static function fixEmptyUnit(?string $unit): string
    {
        return $unit === null || JavaScript::trim($unit) === '' ? '1' : $unit;
    }

    private static function fixUnit(?string $unit): string
    {
        $unit = self::fixEmptyUnit($unit);
        return self::CQL_TO_UCUM_DATE_UNITS[$unit] ?? $unit;
    }
}
