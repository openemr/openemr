<?php

/**
 * Isolated MeasurementUtils Test
 *
 * Tests unit conversion methods in MeasurementUtils.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Utils;

use OpenEMR\Common\Utils\MeasurementUtils;
use PHPUnit\Framework\TestCase;

class MeasurementUtilsTest extends TestCase
{
    /**
     * @dataProvider kgToLbProvider
     */
    public function testKgToLb(float $kg, string $expectedLb): void
    {
        $this->assertSame($expectedLb, MeasurementUtils::kgToLb($kg));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function kgToLbProvider(): array
    {
        return [
            'zero' => [0.0, '0.000000'],
            'one kg' => [1.0, '2.204623'],
            'round number' => [10.0, '22.046226'],
            'decimal' => [2.5, '5.511557'],
            'typical adult weight' => [70.0, '154.323584'],
            'small value' => [0.001, '0.002205'],
        ];
    }

    /**
     * @dataProvider lbToKgProvider
     */
    public function testLbToKg(float $lb, string $expectedKg): void
    {
        $this->assertSame($expectedKg, MeasurementUtils::lbToKg($lb));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function lbToKgProvider(): array
    {
        return [
            'zero' => [0.0, '0.000000'],
            'one lb' => [1.0, '0.453592'],
            'round number' => [10.0, '4.535924'],
            'typical adult weight' => [150.0, '68.038856'],
            'decimal' => [2.5, '1.133981'],
        ];
    }

    /**
     * @dataProvider cmToInchesProvider
     */
    public function testCmToInches(float $cm, string $expectedInches): void
    {
        $this->assertSame($expectedInches, MeasurementUtils::cmToInches($cm));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function cmToInchesProvider(): array
    {
        return [
            'zero' => [0.0, '0.000000'],
            'one inch in cm' => [2.54, '1.000000'],
            'typical height' => [175.0, '68.897638'],
            'round number' => [10.0, '3.937008'],
            'decimal' => [5.5, '2.165354'],
        ];
    }

    /**
     * @dataProvider inchesToCmProvider
     */
    public function testInchesToCm(float $inches, string $expectedCm): void
    {
        $this->assertSame($expectedCm, MeasurementUtils::inchesToCm($inches));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function inchesToCmProvider(): array
    {
        return [
            'zero' => [0.0, '0.000000'],
            'one inch' => [1.0, '2.540000'],
            'one foot' => [12.0, '30.480000'],
            'typical height' => [69.0, '175.260000'],
            'decimal' => [5.5, '13.970000'],
        ];
    }

    /**
     * @dataProvider fhToCelsiusProvider
     */
    public function testFhToCelsius(float $fahrenheit, string $expectedCelsius): void
    {
        $this->assertSame($expectedCelsius, MeasurementUtils::fhToCelsius($fahrenheit));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function fhToCelsiusProvider(): array
    {
        return [
            'freezing point' => [32.0, '0.000000'],
            'boiling point' => [212.0, '100.000000'],
            'normal body temp' => [98.6, '37.000000'],
            'absolute zero F' => [-459.67, '-273.150000'],
            'room temperature' => [68.0, '20.000000'],
            'fever temperature' => [102.0, '38.888889'],
        ];
    }

    /**
     * @dataProvider celsiusToFhProvider
     */
    public function testCelsiusToFh(float $celsius, string $expectedFahrenheit): void
    {
        $this->assertSame($expectedFahrenheit, MeasurementUtils::celsiusToFh($celsius));
    }

    /**
     * @return array<string, array{float, string}>
     */
    public static function celsiusToFhProvider(): array
    {
        return [
            'freezing point' => [0.0, '32.000000'],
            'boiling point' => [100.0, '212.000000'],
            'normal body temp' => [37.0, '98.600000'],
            'room temperature' => [20.0, '68.000000'],
            'negative celsius' => [-40.0, '-40.000000'], // -40 is same in both scales
            'fever temperature' => [39.0, '102.200000'],
        ];
    }

    public function testConversionRoundTrip(): void
    {
        // Test that converting back and forth gives approximately the same value
        $originalKg = 75.5;
        /** @var string $lb */
        $lb = MeasurementUtils::kgToLb($originalKg);
        /** @var string $backToKg */
        $backToKg = MeasurementUtils::lbToKg(floatval($lb));
        $this->assertEqualsWithDelta($originalKg, floatval($backToKg), 0.0001);

        $originalCm = 180.0;
        /** @var string $inches */
        $inches = MeasurementUtils::cmToInches($originalCm);
        /** @var string $backToCm */
        $backToCm = MeasurementUtils::inchesToCm(floatval($inches));
        $this->assertEqualsWithDelta($originalCm, floatval($backToCm), 0.0001);

        $originalCelsius = 37.5;
        /** @var string $fh */
        $fh = MeasurementUtils::celsiusToFh($originalCelsius);
        /** @var string $backToCelsius */
        $backToCelsius = MeasurementUtils::fhToCelsius(floatval($fh));
        $this->assertEqualsWithDelta($originalCelsius, floatval($backToCelsius), 0.0001);
    }

    public function testMeasurementPrecisionConstant(): void
    {
        // Verify the precision constant value
        $reflection = new \ReflectionClass(MeasurementUtils::class);
        $this->assertSame(6, $reflection->getConstant('MEASUREMENT_PRECISION'));
    }

    public function testOutputFormatHasSixDecimalPlaces(): void
    {
        // Verify all outputs have exactly 6 decimal places
        $kgResult = MeasurementUtils::kgToLb(1.0);
        $this->assertIsString($kgResult);
        $this->assertMatchesRegularExpression('/^\d+\.\d{6}$/', $kgResult);

        $lbResult = MeasurementUtils::lbToKg(1.0);
        $this->assertIsString($lbResult);
        $this->assertMatchesRegularExpression('/^\d+\.\d{6}$/', $lbResult);

        $cmResult = MeasurementUtils::cmToInches(1.0);
        $this->assertIsString($cmResult);
        $this->assertMatchesRegularExpression('/^\d+\.\d{6}$/', $cmResult);

        $inchResult = MeasurementUtils::inchesToCm(1.0);
        $this->assertIsString($inchResult);
        $this->assertMatchesRegularExpression('/^\d+\.\d{6}$/', $inchResult);

        $fhResult = MeasurementUtils::fhToCelsius(100.0);
        $this->assertIsString($fhResult);
        $this->assertMatchesRegularExpression('/^-?\d+\.\d{6}$/', $fhResult);

        $celsiusResult = MeasurementUtils::celsiusToFh(37.0);
        $this->assertIsString($celsiusResult);
        $this->assertMatchesRegularExpression('/^\d+\.\d{6}$/', $celsiusResult);
    }
}
