<?php

/**
 * Fahrenheit-to-Celsius conversion for C-CDA vital signs exported in metric
 * units.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Cda;

final class TemperatureConversion
{
    /**
     * Convert a stored Fahrenheit temperature to Celsius, rounded to one
     * decimal after conversion. OpenEMR stores an unrecorded temperature as 0,
     * which yields '' rather than -17.8.
     */
    public static function fahrenheitToCelsius(mixed $fahrenheit): string
    {
        $value = is_numeric($fahrenheit) ? (float)$fahrenheit : 0.0;
        if ($value === 0.0) {
            return '';
        }
        return number_format(($value - 32) * 5 / 9, 1, '.', '');
    }
}
