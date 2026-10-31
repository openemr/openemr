<?php

/**
 * Converts a vital sign sent in another unit into the unit form_vitals stores it in.
 *
 * form_vitals keeps weight in pounds, height and head circumference in inches and
 * temperature in Fahrenheit, whatever the units_of_measurement display setting. Every
 * write that takes a value with its unit converts it here, so the FHIR API and the
 * C-CDA/QRDA import store the same number for the same measurement.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Common\Utils\MeasurementUtils;

final class VitalsStorageUnits
{
    /**
     * The same arithmetic as convert(), as SQL on a DECIMAL value, for each storage unit
     * and the units that convert to it. Exact decimals here and PHP floats in convert()
     * agree once both are rounded to MeasurementUtils::MEASUREMENT_PRECISION.
     */
    private const SQL_CONVERSIONS = [
        'lb' => ['kg' => '%s * 2.20462262185', 'g' => '(%s / 1000) * 2.20462262185'],
        'in' => ['cm' => '%s / 2.54', 'm' => '(%s * 100) / 2.54'],
        'degf' => ['cel' => '((9 / 5) * %s) + 32', 'c' => '((9 / 5) * %s) + 32'],
    ];

    /**
     * Converts an incoming quantity into the unit the column is stored in.
     *
     * Returns null when the unit is one this service cannot place. Storing the bare
     * number in that case would silently record 70 lb for a 70 kg patient, so callers
     * reject instead.
     */
    public static function convert(float $value, string $fromUnit, string $storageUnit): ?float
    {
        $from = strtolower(trim($fromUnit));
        // UCUM spells several of these more than one way, and the read side emits the
        // short forms, so both are accepted.
        $from = match ($from) {
            '[lb_av]', 'lbs' => 'lb',
            '[in_i]', 'inch', 'inches' => 'in',
            '[degf]', 'f' => 'degf',
            'cel', 'c' => 'cel',
            '1/min', 'bpm', 'beats/min', 'breaths/min' => '/min',
            '%%' => '%',
            default => $from,
        };
        $to = strtolower($storageUnit);

        if ($from === $to) {
            return $value;
        }

        // Computed here rather than through MeasurementUtils' converters: those return
        // number_format() strings with a thousands separator, and casting "1,102.311311" to
        // float stops at the comma and yields 1.0 -- a 500 kg patient stored as 1 lb. The
        // factors and precision are the ones MeasurementUtils uses, so a value converted
        // here reads back through the read-side converters unchanged.
        $precision = MeasurementUtils::MEASUREMENT_PRECISION;
        return match ([$from, $to]) {
            ['kg', 'lb'] => round($value * 2.20462262185, $precision),
            ['g', 'lb'] => round(($value / 1000) * 2.20462262185, $precision),
            ['cm', 'in'] => round($value / 2.54, $precision),
            ['m', 'in'] => round(($value * 100) / 2.54, $precision),
            ['cel', 'degf'] => round(((9 / 5) * $value) + 32, $precision),
            default => null,
        };
    }

    /**
     * SQL giving a stored observation's value in the storage unit, for comparing it with
     * a form_vitals column. A unit that converts is converted as convert() would; any
     * other unit, or none, leaves the value as it is.
     *
     * @param string $value SQL for a plain number, already checked to be one
     * @param string $unit  SQL for its unit
     */
    public static function convertSql(string $value, string $unit, string $storageUnit): string
    {
        $conversions = self::SQL_CONVERSIONS[strtolower($storageUnit)] ?? [];
        if ($conversions === []) {
            return $value;
        }
        $decimal = "CAST($value AS DECIMAL(24,12))";
        $whens = [];
        foreach ($conversions as $from => $arithmetic) {
            $whens[] = "WHEN '$from' THEN ROUND(" . sprintf($arithmetic, $decimal) . ', ' . MeasurementUtils::MEASUREMENT_PRECISION . ')';
        }
        return "CASE LOWER(TRIM($unit)) " . implode(' ', $whens) . " ELSE $value END";
    }
}
