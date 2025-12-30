<?php

/**
 * Isolated MeasurementUtils Test
 *
 * Tests MeasurementUtils functionality using PhpUnitsOfMeasure library.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @link      https://opencoreemr.com
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\MeasurementUtils;
use OpenEMR\Common\Utils\MeasurementUtilsInterface;
use PHPUnit\Framework\TestCase;

class MeasurementUtilsTest extends TestCase
{
    private MeasurementUtils $utils;

    protected function setUp(): void
    {
        // Default to USA primary mode for tests
        $this->utils = new MeasurementUtils(
            MeasurementUtils::MEASUREMENT_PRECISION,
            MeasurementUtils::UNITS_USA_PRIMARY
        );
    }

    /**
     * Test that MeasurementUtils implements the interface
     */
    public function testImplementsInterface(): void
    {
        $this->assertInstanceOf(MeasurementUtilsInterface::class, $this->utils);
    }

    /**
     * Test pounds to kilograms conversion
     */
    public function testLbToKg(): void
    {
        // 1 lb = 0.45359237 kg (exact)
        $result = $this->utils->lbToKg(1);
        $this->assertEquals('0.453592', $result);

        // 150 lbs
        $result = $this->utils->lbToKg(150);
        $this->assertEqualsWithDelta(68.039, (float)$result, 0.001);
    }

    /**
     * Test kilograms to pounds conversion
     */
    public function testKgToLb(): void
    {
        // 1 kg = 2.20462262185 lbs
        $result = $this->utils->kgToLb(1);
        $this->assertEqualsWithDelta(2.20462, (float)$result, 0.00001);

        // 68 kg
        $result = $this->utils->kgToLb(68);
        $this->assertEqualsWithDelta(149.914, (float)$result, 0.001);
    }

    /**
     * Test inches to centimeters conversion
     */
    public function testInchesToCm(): void
    {
        // 1 inch = 2.54 cm (exact)
        $result = $this->utils->inchesToCm(1);
        $this->assertEquals('2.540000', $result);

        // 68 inches (5'8")
        $result = $this->utils->inchesToCm(68);
        $this->assertEqualsWithDelta(172.72, (float)$result, 0.01);
    }

    /**
     * Test centimeters to inches conversion
     */
    public function testCmToInches(): void
    {
        // 2.54 cm = 1 inch
        $result = $this->utils->cmToInches(2.54);
        $this->assertEqualsWithDelta(1.0, (float)$result, 0.0001);

        // 172.72 cm
        $result = $this->utils->cmToInches(172.72);
        $this->assertEqualsWithDelta(68.0, (float)$result, 0.01);
    }

    /**
     * Test Fahrenheit to Celsius conversion
     */
    public function testFhToCelsius(): void
    {
        // 32°F = 0°C (freezing point)
        $result = $this->utils->fhToCelsius(32);
        $this->assertEqualsWithDelta(0.0, (float)$result, 0.001);

        // 212°F = 100°C (boiling point)
        $result = $this->utils->fhToCelsius(212);
        $this->assertEqualsWithDelta(100.0, (float)$result, 0.001);

        // 98.6°F = 37°C (body temperature)
        $result = $this->utils->fhToCelsius(98.6);
        $this->assertEqualsWithDelta(37.0, (float)$result, 0.001);
    }

    /**
     * Test Celsius to Fahrenheit conversion
     */
    public function testCelsiusToFh(): void
    {
        // 0°C = 32°F
        $result = $this->utils->celsiusToFh(0);
        $this->assertEqualsWithDelta(32.0, (float)$result, 0.001);

        // 100°C = 212°F
        $result = $this->utils->celsiusToFh(100);
        $this->assertEqualsWithDelta(212.0, (float)$result, 0.001);

        // 37°C = 98.6°F
        $result = $this->utils->celsiusToFh(37);
        $this->assertEqualsWithDelta(98.6, (float)$result, 0.01);
    }

    /**
     * Test round-trip conversion accuracy for weight
     */
    public function testWeightRoundTrip(): void
    {
        $originalLb = 150.5;
        $kg = (float)$this->utils->lbToKg($originalLb);
        $backToLb = (float)$this->utils->kgToLb($kg);

        $this->assertEqualsWithDelta($originalLb, $backToLb, 0.001);
    }

    /**
     * Test round-trip conversion accuracy for length
     */
    public function testLengthRoundTrip(): void
    {
        $originalIn = 72.5;
        $cm = (float)$this->utils->inchesToCm($originalIn);
        $backToIn = (float)$this->utils->cmToInches($cm);

        $this->assertEqualsWithDelta($originalIn, $backToIn, 0.001);
    }

    /**
     * Test round-trip conversion accuracy for temperature
     */
    public function testTemperatureRoundTrip(): void
    {
        $originalF = 98.6;
        $c = (float)$this->utils->fhToCelsius($originalF);
        $backToF = (float)$this->utils->celsiusToFh($c);

        $this->assertEqualsWithDelta($originalF, $backToF, 0.01);
    }

    /**
     * Test zero value conversions
     */
    public function testZeroValues(): void
    {
        $this->assertEquals('0.000000', $this->utils->lbToKg(0));
        $this->assertEquals('0.000000', $this->utils->kgToLb(0));
        $this->assertEquals('0.000000', $this->utils->inchesToCm(0));
        $this->assertEquals('0.000000', $this->utils->cmToInches(0));
    }

    /**
     * Test custom precision
     */
    public function testCustomPrecision(): void
    {
        $utils = new MeasurementUtils(2, MeasurementUtils::UNITS_USA_PRIMARY);

        // With precision of 2, should return fewer decimal places
        $result = $utils->lbToKg(1);
        $this->assertEquals('0.45', $result);
    }

    /**
     * Test isMetric() returns true for metric modes
     */
    public function testIsMetricTrueForMetricModes(): void
    {
        $metricPrimary = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_PRIMARY);
        $metricOnly = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_ONLY);

        $this->assertTrue($metricPrimary->isMetric());
        $this->assertTrue($metricOnly->isMetric());
    }

    /**
     * Test isMetric() returns false for USA modes
     */
    public function testIsMetricFalseForUsaModes(): void
    {
        $usaPrimary = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_PRIMARY);
        $usaOnly = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_ONLY);

        $this->assertFalse($usaPrimary->isMetric());
        $this->assertFalse($usaOnly->isMetric());
    }

    /**
     * Test formatWeight in USA Primary mode (shows both units, US first)
     */
    public function testFormatWeightUsaPrimary(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_PRIMARY);
        $result = $utils->formatWeight(150.0);

        // Should show US first, then metric in parentheses
        $this->assertStringContainsString('150.00', $result);
        $this->assertStringContainsString('lb', $result);
        $this->assertStringContainsString('68.04', $result);
        $this->assertStringContainsString('kg', $result);
        $this->assertStringContainsString('(', $result);
    }

    /**
     * Test formatWeight in Metric Primary mode (shows both units, metric first)
     */
    public function testFormatWeightMetricPrimary(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_PRIMARY);
        $result = $utils->formatWeight(150.0);

        // Should show metric first, then US in parentheses
        $this->assertStringContainsString('68.04', $result);
        $this->assertStringContainsString('kg', $result);
        $this->assertStringContainsString('150.00', $result);
        $this->assertStringContainsString('lb', $result);
        // Metric should come before the parenthesis
        $this->assertMatchesRegularExpression('/kg.*\(/', $result);
    }

    /**
     * Test formatWeight in USA Only mode (shows only US units)
     */
    public function testFormatWeightUsaOnly(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_ONLY);
        $result = $utils->formatWeight(150.0);

        // Should show only US units
        $this->assertStringContainsString('150.00', $result);
        $this->assertStringContainsString('lb', $result);
        $this->assertStringNotContainsString('kg', $result);
    }

    /**
     * Test formatWeight in Metric Only mode (shows only metric units)
     */
    public function testFormatWeightMetricOnly(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_ONLY);
        $result = $utils->formatWeight(150.0);

        // Should show only metric units
        $this->assertStringContainsString('68.04', $result);
        $this->assertStringContainsString('kg', $result);
        $this->assertStringNotContainsString('lb', $result);
    }

    /**
     * Test formatWeight with primaryOnly=true
     */
    public function testFormatWeightPrimaryOnly(): void
    {
        $usaPrimary = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_PRIMARY);
        $metricPrimary = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_PRIMARY);

        $usaResult = $usaPrimary->formatWeight(150.0, true);
        $metricResult = $metricPrimary->formatWeight(150.0, true);

        // USA primary should show only lb
        $this->assertStringContainsString('lb', $usaResult);
        $this->assertStringNotContainsString('kg', $usaResult);

        // Metric primary should show only kg
        $this->assertStringContainsString('kg', $metricResult);
        $this->assertStringNotContainsString('lb', $metricResult);
    }

    /**
     * Test formatLength in USA Primary mode
     */
    public function testFormatLengthUsaPrimary(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_PRIMARY);
        $result = $utils->formatLength(68.0);

        // Should show US first, then metric in parentheses
        $this->assertStringContainsString('68.00', $result);
        $this->assertStringContainsString('in', $result);
        $this->assertStringContainsString('172.72', $result);
        $this->assertStringContainsString('cm', $result);
    }

    /**
     * Test formatLength in Metric Only mode
     */
    public function testFormatLengthMetricOnly(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_ONLY);
        $result = $utils->formatLength(68.0);

        // Should show only metric
        $this->assertStringContainsString('172.72', $result);
        $this->assertStringContainsString('cm', $result);
        $this->assertStringNotContainsString('in', $result);
    }

    /**
     * Test formatTemperature in USA Primary mode
     */
    public function testFormatTemperatureUsaPrimary(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_USA_PRIMARY);
        $result = $utils->formatTemperature(98.6);

        // Should show F first, then C in parentheses
        $this->assertStringContainsString('98.60', $result);
        $this->assertStringContainsString('F', $result);
        $this->assertStringContainsString('37.00', $result);
        $this->assertStringContainsString('C', $result);
    }

    /**
     * Test formatTemperature in Metric Only mode
     */
    public function testFormatTemperatureMetricOnly(): void
    {
        $utils = new MeasurementUtils(6, MeasurementUtils::UNITS_METRIC_ONLY);
        $result = $utils->formatTemperature(98.6);

        // Should show only metric
        $this->assertStringContainsString('37.00', $result);
        $this->assertStringContainsString('C', $result);
        $this->assertStringNotContainsString('F', $result);
    }

    /**
     * Test weight formatting with lb/oz mode
     */
    public function testFormatWeightLbsOzMode(): void
    {
        $utils = new MeasurementUtils(
            6,
            MeasurementUtils::UNITS_USA_ONLY,
            MeasurementUtils::WEIGHT_LBS_OZ
        );
        $result = $utils->formatWeight(150.5);

        // 150.5 lbs = 150 lbs 8 oz
        $this->assertStringContainsString('150', $result);
        $this->assertStringContainsString('lb', $result);
        $this->assertStringContainsString('8', $result);
        $this->assertStringContainsString('oz', $result);
    }

    /**
     * Test weight lb/oz conversion accuracy
     */
    public function testFormatWeightLbsOzAccuracy(): void
    {
        $utils = new MeasurementUtils(
            6,
            MeasurementUtils::UNITS_USA_ONLY,
            MeasurementUtils::WEIGHT_LBS_OZ
        );

        // 150.25 lbs = 150 lbs 4 oz
        $result = $utils->formatWeight(150.25);
        $this->assertStringContainsString('150', $result);
        $this->assertStringContainsString('4', $result);

        // 150.75 lbs = 150 lbs 12 oz
        $result = $utils->formatWeight(150.75);
        $this->assertStringContainsString('150', $result);
        $this->assertStringContainsString('12', $result);
    }
}
