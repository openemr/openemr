<?php

/**
 * Fahrenheit-to-Celsius conversion for exported C-CDA vital signs.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Cda;

use OpenEMR\Services\Cda\TemperatureConversion;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TemperatureConversionTest extends TestCase
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
    public function testConvertsThenRounds(mixed $fahrenheit, string $celsius): void
    {
        self::assertSame($celsius, TemperatureConversion::fahrenheitToCelsius($fahrenheit));
    }
}
