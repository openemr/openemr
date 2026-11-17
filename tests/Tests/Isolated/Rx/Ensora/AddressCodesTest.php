<?php

/**
 * Isolated tests for turning stored facility states and countries into Ensora codes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Rx\Ensora;

use OpenEMR\Rx\Ensora\AddressCodes;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class AddressCodesTest extends TestCase
{
    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function stateProvider(): array
    {
        return [
            'code' => ['AR', 'AR'],
            'lower-case code' => ['ar', 'AR'],
            'name' => ['Arkansas', 'AR'],
            'name in another case with spaces' => ['  new YORK ', 'NY'],
            'code missing from the list' => ['ZZ', 'ZZ'],
            'name missing from the list' => ['Ontario', null],
            'longer code from the list' => ['Puerto Rico Commonwealth', null],
            'digits' => ['12', null],
        ];
    }

    #[DataProvider('stateProvider')]
    public function testState(string $value, ?string $expected): void
    {
        $codes = AddressCodes::fromStateOptions([
            ['option_id' => 'AR', 'title' => 'Arkansas'],
            ['option_id' => 'NY', 'title' => 'New York'],
            ['option_id' => 'PRC', 'title' => 'Puerto Rico Commonwealth'],
            ['option_id' => null, 'title' => 'No Code'],
        ]);

        $this->assertSame($expected, $codes->state($value));
    }

    /**
     * @return array<string, array{string, ?string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function countryProvider(): array
    {
        return [
            'code' => ['US', 'US'],
            'lower-case code' => [' us ', 'US'],
            'another two-letter code' => ['CA', 'CA'],
            'three-letter United States' => ['USA', 'US'],
            'dotted' => ['U.S.A.', 'US'],
            'name' => ['United States', 'US'],
            'full name' => ['United States of America', 'US'],
            'another three-letter code' => ['AUT', null],
            'another name' => ['Canada', null],
        ];
    }

    #[DataProvider('countryProvider')]
    public function testCountry(string $value, ?string $expected): void
    {
        $this->assertSame($expected, AddressCodes::country($value));
    }
}
