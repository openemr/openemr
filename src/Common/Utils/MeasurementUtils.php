<?php

/**
 * MeasurementUtils.php
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

class MeasurementUtils
{
    public static function kgToLb($val)
    {
        return number_format($val * 2.20462262185, 2);
    }
    public static function lbToKg($val)
    {
        return number_format($val *  0.45359237, 2);
    }
    public static function cmToInches($val)
    {
        return round(number_format($val / 2.54, 2), 2);
    }
    public static function inchesToCm($val)
    {
        return round(number_format($val * 2.54, 2), 2);
    }
    public static function fhToCelsius($val)
    {
        return round(number_format(($val - 32) * (5 / 9), 2), 2);
    }

    public static function celsiusToFh($val)
    {
        return round(number_format(((9 / 5) * $val) + 32, 2), 2);
    }
}
