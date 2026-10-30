<?php

/**
 * Isolated tests for Hl7Units: UCUM named as the coding system only for UCUM units.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Common\Hl7;

use OpenEMR\Common\Hl7\Hl7Units;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class Hl7UnitsTest extends TestCase
{
    /** @return array<string, array{string, string, string}> unit, then OBX-6 and RXA-7 */
    public static function units(): array
    {
        return [
            'a UCUM unit' => ['mL', 'mL^^UCUM', 'mL^mL^UCUM^^^'],
            'a UCUM rate' => ['/min', '/min^^UCUM', '/min^/min^UCUM^^^'],
            'free text from the observation form' => ['bpm', '^bpm', '^bpm^^^^'],
            'mcg, which is ug in UCUM' => ['mcg', '^mcg', 'ug^mcg^UCUM^^^'],
            'grams, which is g in UCUM' => ['grams', '^grams', 'g^grams^UCUM^^^'],
            'an option id, not a unit' => ['9', '9^^UCUM', '^9^^^^'],
            'mg/1cc' => ['mg/1cc', '^mg/1cc', '^mg/1cc^^^^'],
            'no unit' => ['', '', ''],
            'a UCUM unit with spaces around it' => [' mL ', 'mL^^UCUM', 'mL^mL^UCUM^^^'],
            'free text with spaces around it' => [' bpm ', '^bpm', '^bpm^^^^'],
            'only spaces' => ['  ', '', ''],
            'a delimiter in free text' => ['foo^UCUM', '^foo\\S\\UCUM', '^foo\\S\\UCUM^^^^'],
            'each delimiter escaped' => ['a|b~c&d\\e', '^a\\F\\b\\R\\c\\T\\d\\E\\e', '^a\\F\\b\\R\\c\\T\\d\\E\\e^^^^'],
        ];
    }

    #[DataProvider('units')]
    public function testObx6(string $unit, string $obx6, string $rxa7): void
    {
        $this->assertSame($obx6, Hl7Units::obx6($unit));
    }

    #[DataProvider('units')]
    public function testRxa7(string $unit, string $obx6, string $rxa7): void
    {
        $this->assertSame($rxa7, Hl7Units::rxa7($unit));
    }
}
