<?php

/**
 * Imperial-to-metric conversion for C-CDA vital signs exported in metric
 * units. OpenEMR stores weight in pounds, height in inches and temperature in
 * Fahrenheit, and an unrecorded measurement as 0.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

final class VitalSignConversion
{
    private const KILOGRAMS_PER_POUND = 0.45359237;
    private const CENTIMETRES_PER_INCH = 2.54;

    /**
     * Pounds to kilograms, to 0.01 kg. An unrecorded weight yields ''.
     */
    public static function poundsToKilograms(mixed $pounds): string
    {
        $value = self::recorded($pounds);
        return $value === null ? '' : self::format($value * self::KILOGRAMS_PER_POUND, 2);
    }

    /**
     * Inches to centimetres, to 0.1 cm. An unrecorded height yields ''.
     */
    public static function inchesToCentimetres(mixed $inches): string
    {
        $value = self::recorded($inches);
        return $value === null ? '' : self::format($value * self::CENTIMETRES_PER_INCH, 1);
    }

    /**
     * Fahrenheit to Celsius, converted and then rounded to 0.1 degree. An
     * unrecorded temperature yields '' rather than -17.8.
     */
    public static function fahrenheitToCelsius(mixed $fahrenheit): string
    {
        $value = self::recorded($fahrenheit);
        return $value === null ? '' : self::format(($value - 32) * 5 / 9, 1);
    }

    /**
     * The stored measurement, or null when none was recorded.
     */
    private static function recorded(mixed $stored): ?float
    {
        if (!is_numeric($stored)) {
            return null;
        }
        $value = (float)$stored;
        return $value === 0.0 ? null : $value;
    }

    private static function format(float $value, int $decimals): string
    {
        return number_format($value, $decimals, '.', '');
    }
}
