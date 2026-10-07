<?php

/**
 * Imperial-to-metric conversion for exported C-CDA vital signs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\VitalSignConversion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VitalSignConversionTest extends TestCase
{
    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function temperatureProvider(): array
    {
        return [
            'normal body temperature' => ['98.6', '37.0'],
            'fever' => ['100.4', '38.0'],
            'stored as float' => [96.8, '36.0'],
            'stored with trailing zeros' => ['99.50', '37.5'],
            'unrecorded is empty, not -17.8' => ['0.00', ''],
            'null is empty' => [null, ''],
            'non-numeric is empty' => ['n/a', ''],
        ];
    }

    #[DataProvider('temperatureProvider')]
    public function testFahrenheitToCelsiusConvertsThenRounds(mixed $fahrenheit, string $celsius): void
    {
        self::assertSame($celsius, VitalSignConversion::fahrenheitToCelsius($fahrenheit));
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function weightProvider(): array
    {
        return [
            'adult' => ['220.00', '99.79'],
            'infant' => ['7.5', '3.40'],
            'unrecorded is empty, not 0.00' => ['0.00', ''],
            'null is empty' => [null, ''],
        ];
    }

    #[DataProvider('weightProvider')]
    public function testPoundsToKilograms(mixed $pounds, string $kilograms): void
    {
        self::assertSame($kilograms, VitalSignConversion::poundsToKilograms($pounds));
    }

    /**
     * @return array<string, array{mixed, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function heightProvider(): array
    {
        return [
            'adult' => ['70.00', '177.8'],
            'fractional inches' => ['65.5', '166.4'],
            'unrecorded is empty, not 0.00' => ['0', ''],
            'null is empty' => [null, ''],
        ];
    }

    #[DataProvider('heightProvider')]
    public function testInchesToCentimetres(mixed $inches, string $centimetres): void
    {
        self::assertSame($centimetres, VitalSignConversion::inchesToCentimetres($inches));
    }
}
