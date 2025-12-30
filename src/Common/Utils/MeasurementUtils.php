<?php

/**
 * MeasurementUtils.php
 *
 * @package openemr
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Stephen Nielson <stephen@nielson.org>
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Utils;

use OpenEMR\Core\OEGlobalsBag;
use PhpUnitsOfMeasure\PhysicalQuantity\Length;
use PhpUnitsOfMeasure\PhysicalQuantity\Mass;
use PhpUnitsOfMeasure\PhysicalQuantity\Temperature;

/**
 * Utility class for unit conversions and formatting in healthcare measurements.
 *
 * Uses the php-units-of-measure library for accurate conversions.
 * Implements MeasurementUtilsInterface for dependency injection support.
 */
class MeasurementUtils implements MeasurementUtilsInterface
{
    public const MEASUREMENT_PRECISION = 6;

    // Unit display modes (from $GLOBALS['units_of_measurement'])
    public const UNITS_USA_PRIMARY = 1;
    public const UNITS_METRIC_PRIMARY = 2;
    public const UNITS_USA_ONLY = 3;
    public const UNITS_METRIC_ONLY = 4;

    // US weight format (from $GLOBALS['us_weight_format'])
    public const WEIGHT_DECIMAL = 1;
    public const WEIGHT_LBS_OZ = 2;

    private readonly int $unitsMode;
    private readonly int $usWeightFormat;

    /**
     * @param int $precision Number of decimal places for formatted output
     * @param int|null $unitsMode Units display mode (defaults to $GLOBALS['units_of_measurement'])
     * @param int|null $usWeightFormat US weight format (defaults to $GLOBALS['us_weight_format'])
     */
    public function __construct(
        private readonly int $precision = self::MEASUREMENT_PRECISION,
        ?int $unitsMode = null,
        ?int $usWeightFormat = null
    ) {
        $globals = OEGlobalsBag::getInstance();
        $this->unitsMode = $unitsMode ?? ($globals->get('units_of_measurement') ?? self::UNITS_USA_PRIMARY);
        $this->usWeightFormat = $usWeightFormat ?? ($globals->get('us_weight_format') ?? self::WEIGHT_DECIMAL);
    }

    /**
     * @inheritDoc
     */
    public function kgToLb(float $val): string
    {
        $mass = new Mass($val, 'kg');
        return number_format($mass->toUnit('lb'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function lbToKg(float $val): string
    {
        $mass = new Mass($val, 'lb');
        return number_format($mass->toUnit('kg'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function cmToInches(float $val): string
    {
        $length = new Length($val, 'cm');
        return number_format($length->toUnit('in'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function inchesToCm(float $val): string
    {
        $length = new Length($val, 'in');
        return number_format($length->toUnit('cm'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function fhToCelsius(float $val): string
    {
        $temp = new Temperature($val, 'F');
        return number_format($temp->toUnit('C'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function celsiusToFh(float $val): string
    {
        $temp = new Temperature($val, 'C');
        return number_format($temp->toUnit('F'), $this->precision);
    }

    /**
     * @inheritDoc
     */
    public function isMetric(): bool
    {
        return in_array($this->unitsMode, [self::UNITS_METRIC_PRIMARY, self::UNITS_METRIC_ONLY]);
    }

    /**
     * @inheritDoc
     */
    public function formatWeight(float $lbs, bool $primaryOnly = false): string
    {
        $kg = number_format((float)$this->lbToKg($lbs), 2);
        $lbFormatted = $this->formatUsWeight($lbs);

        if ($primaryOnly) {
            return $this->isMetric()
                ? "{$kg} " . $this->xl('kg')
                : $lbFormatted;
        }

        return match ($this->unitsMode) {
            self::UNITS_METRIC_PRIMARY => "{$kg} " . $this->xl('kg') . " ({$lbFormatted})",
            self::UNITS_USA_ONLY => $lbFormatted,
            self::UNITS_METRIC_ONLY => "{$kg} " . $this->xl('kg'),
            default => "{$lbFormatted} ({$kg} " . $this->xl('kg') . ")",  // USA_PRIMARY
        };
    }

    /**
     * @inheritDoc
     */
    public function formatLength(float $inches, bool $primaryOnly = false): string
    {
        $cm = number_format((float)$this->inchesToCm($inches), 2);
        $inFormatted = number_format($inches, 2) . ' ' . $this->xl('in');

        if ($primaryOnly) {
            return $this->isMetric()
                ? "{$cm} " . $this->xl('cm')
                : $inFormatted;
        }

        return match ($this->unitsMode) {
            self::UNITS_METRIC_PRIMARY => "{$cm} " . $this->xl('cm') . " ({$inFormatted})",
            self::UNITS_USA_ONLY => $inFormatted,
            self::UNITS_METRIC_ONLY => "{$cm} " . $this->xl('cm'),
            default => "{$inFormatted} ({$cm} " . $this->xl('cm') . ")",
        };
    }

    /**
     * @inheritDoc
     */
    public function formatTemperature(float $fahrenheit, bool $primaryOnly = false): string
    {
        $celsius = number_format((float)$this->fhToCelsius($fahrenheit), 2);
        $fFormatted = number_format($fahrenheit, 2) . ' ' . $this->xl('F');

        if ($primaryOnly) {
            return $this->isMetric()
                ? "{$celsius} " . $this->xl('C')
                : $fFormatted;
        }

        return match ($this->unitsMode) {
            self::UNITS_METRIC_PRIMARY => "{$celsius} " . $this->xl('C') . " ({$fFormatted})",
            self::UNITS_USA_ONLY => $fFormatted,
            self::UNITS_METRIC_ONLY => "{$celsius} " . $this->xl('C'),
            default => "{$fFormatted} ({$celsius} " . $this->xl('C') . ")",
        };
    }

    /**
     * Format weight in US units (pounds or pounds/ounces).
     *
     * @param float $lbs Value in pounds
     * @return string Formatted US weight
     */
    private function formatUsWeight(float $lbs): string
    {
        if ($this->usWeightFormat === self::WEIGHT_LBS_OZ) {
            $totalOz = (int) round($lbs * 16);
            $wholeLbs = intdiv($totalOz, 16);
            $oz = $totalOz % 16;
            return "{$wholeLbs} " . $this->xl('lb') . " {$oz} " . $this->xl('oz');
        }
        return number_format($lbs, 2) . ' ' . $this->xl('lb');
    }

    /**
     * Translate a string using xl() if the full translation system is available.
     *
     * This allows the class to work in isolated test environments
     * where the translation system is not fully loaded.
     *
     * @param string $text Text to translate
     * @return string Translated text (or original if unavailable)
     */
    private function xl(string $text): string
    {
        // Only call xl() if the full translation infrastructure is available
        // (sqlStatementNoLog is required by the real xl() function)
        if (function_exists('xl') && function_exists('sqlStatementNoLog')) {
            return xl($text);
        }
        return $text;
    }
}
