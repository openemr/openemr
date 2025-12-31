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
 *
 * This class registers UCUM (Unified Code for Units of Measure) aliases to ensure
 * compatibility with FHIR and HL7 standards used in healthcare interoperability.
 *
 * @see https://ucum.org/ UCUM - Unified Code for Units of Measure
 * @see https://ucum.org/ucum UCUM Specification
 * @see https://hl7.org/fhir/R4/valueset-ucum-units.html FHIR UCUM Units ValueSet
 * @see https://build.fhir.org/ig/HL7/UTG/ValueSet-v3-UnitsOfMeasureCaseSensitive.html HL7 Units ValueSet
 * @see https://unitsofmeasure.org/ucum Unit codes reference
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

    /**
     * @var bool Flag to ensure UCUM aliases are only registered once
     */
    private static bool $ucumAliasesRegistered = false;

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
        self::registerUcumAliases();

        $globals = OEGlobalsBag::getInstance();
        $this->unitsMode = $unitsMode ?? ($globals->get('units_of_measurement') ?? self::UNITS_USA_PRIMARY);
        $this->usWeightFormat = $usWeightFormat ?? ($globals->get('us_weight_format') ?? self::WEIGHT_DECIMAL);
    }

    /**
     * Register UCUM (Unified Code for Units of Measure) aliases for FHIR/HL7 compatibility.
     *
     * UCUM is the standard unit system used in healthcare interoperability standards
     * including FHIR and HL7. This method registers UCUM codes as aliases so that
     * code using standard UCUM codes can work seamlessly with this library.
     *
     * UCUM codes registered:
     * - Mass: [lb_av] (avoirdupois pound), [oz_av] (avoirdupois ounce)
     * - Length: [in_i] (international inch)
     * - Temperature: Cel (degree Celsius), [degF] (degree Fahrenheit)
     *
     * @see https://ucum.org/ucum#section-Derived-Unit-Atoms UCUM Derived Units
     * @see https://hl7.org/fhir/R4/valueset-ucum-units.html FHIR UCUM ValueSet
     * @see urn:oid:2.16.840.1.113883.1.11.12839 HL7 UnitsOfMeasureCaseSensitive OID
     */
    private static function registerUcumAliases(): void
    {
        if (self::$ucumAliasesRegistered) {
            return;
        }

        /*
         * Mass unit UCUM aliases
         * @see https://ucum.org/ucum#para-30 UCUM Mass Units
         *
         * [lb_av] = avoirdupois pound (international pound)
         * [oz_av] = avoirdupois ounce
         */
        Mass::getUnit('lb')->addAlias('[lb_av]');
        Mass::getUnit('oz')->addAlias('[oz_av]');

        /*
         * Length unit UCUM aliases
         * @see https://ucum.org/ucum#para-28 UCUM Length Units
         *
         * [in_i] = international inch (exactly 2.54 cm)
         */
        Length::getUnit('in')->addAlias('[in_i]');

        /*
         * Temperature unit UCUM aliases
         * @see https://ucum.org/ucum#para-32 UCUM Temperature Units
         *
         * Cel = degree Celsius
         * [degF] = degree Fahrenheit
         */
        Temperature::getUnit('C')->addAlias('Cel');
        Temperature::getUnit('F')->addAlias('[degF]');

        self::$ucumAliasesRegistered = true;
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
    public function convertKgToLb(float $val): float
    {
        $mass = new Mass($val, 'kg');
        return $mass->toUnit('lb');
    }

    /**
     * @inheritDoc
     */
    public function convertLbToKg(float $val): float
    {
        $mass = new Mass($val, 'lb');
        return $mass->toUnit('kg');
    }

    /**
     * @inheritDoc
     */
    public function convertCmToInches(float $val): float
    {
        $length = new Length($val, 'cm');
        return $length->toUnit('in');
    }

    /**
     * @inheritDoc
     */
    public function convertInchesToCm(float $val): float
    {
        $length = new Length($val, 'in');
        return $length->toUnit('cm');
    }

    /**
     * @inheritDoc
     */
    public function convertFhToCelsius(float $val): float
    {
        $temp = new Temperature($val, 'F');
        return $temp->toUnit('C');
    }

    /**
     * @inheritDoc
     */
    public function convertCelsiusToFh(float $val): float
    {
        $temp = new Temperature($val, 'C');
        return $temp->toUnit('F');
    }

    /**
     * @inheritDoc
     */
    public function convertLbToOz(float $val): float
    {
        $mass = new Mass($val, 'lb');
        return $mass->toUnit('oz');
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
            // Use library to convert lbs to oz (16 oz per lb)
            $ozPerLb = (int) $this->convertLbToOz(1);
            $totalOz = (int) round($this->convertLbToOz($lbs));
            $wholeLbs = intdiv($totalOz, $ozPerLb);
            $oz = $totalOz % $ozPerLb;
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
