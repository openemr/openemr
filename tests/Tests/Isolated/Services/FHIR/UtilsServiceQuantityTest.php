<?php

/**
 * UtilsService::createQuantity() claims UCUM only for a unit UCUM accepts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR;

use OpenEMR\Services\FHIR\UtilsService;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UtilsServiceQuantityTest extends TestCase
{
    /**
     * @return array<string, array{string, string, string, ?string}> unit and code sent, then the unit and UCUM code expected
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function units(): array
    {
        return [
            'a lab unit in UCUM' => ['mg/dL', 'mg/dL', 'mg/dL', 'mg/dL'],
            'the same unit as many labs send it' => ['MG/DL', 'MG/DL', 'MG/DL', null],
            'a dispensed form' => ['tablet', 'tablet', 'tablet', null],
            'a cell count as labs send it' => ['Thous/CU.MM', 'Thous/CU.MM', 'Thous/CU.MM', null],
            'a UCUM annotation' => ['{score}', '{score}', '{score}', '{score}'],
            'pounds by name' => ['lb', 'lb', 'lb', null],
            'pounds in UCUM' => ['[lb_av]', '[lb_av]', '[lb_av]', '[lb_av]'],
            'a unit with no code' => ['mL', '', 'mL', null],
            'a UCUM code with a space after it' => ['mg/dL', 'mg/dL ', 'mg/dL', 'mg/dL'],
            'a unit with spaces around it' => [' mL ', ' mL ', 'mL', 'mL'],
            'micrograms by their Drug Units title' => ['mcg', 'mcg', 'mcg', 'ug'],
            'grams by their Drug Units title' => ['grams', 'grams', 'grams', 'g'],
            'a unit of one, as UCUM writes it' => ['1', '1', '1', '1'],
            'a no-break space after it' => ["mg/dL\u{00A0}", "mg/dL\u{00A0}", 'mg/dL', 'mg/dL'],
        ];
    }

    #[DataProvider('units')]
    public function testCreateQuantity(string $unit, string $code, string $expectedUnit, ?string $expectedCode): void
    {
        $quantity = UtilsService::createQuantity(5, $unit, $code);
        $json = json_decode((string)json_encode($quantity), true);
        $this->assertIsArray($json);

        $this->assertSame($expectedUnit, $json['unit'] ?? null, 'the unit text is always sent');
        $this->assertSame($expectedCode, $json['code'] ?? null);
        $this->assertSame($expectedCode === null ? null : 'http://unitsofmeasure.org', $json['system'] ?? null);
    }

    /**
     * @return array<string, array{mixed, ?string}> a unit, then its UCUM code
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function ucumCodes(): array
    {
        return [
            'UCUM' => ['mg/dL', 'mg/dL'],
            'a space before it' => [' mg/dL', 'mg/dL'],
            'a newline after it' => ["mg/dL\n", 'mg/dL'],
            'only a space' => [' ', null],
            'micrograms' => ['mcg', 'ug'],
            'micrograms in capitals' => ['MCG', 'ug'],
            'a lab unit in capitals' => ['MG/DL', null],
            'a unit of one' => ['1', '1'],
            'a no-break space after it, which PHP\'s trim() keeps' => ["mg/dL\u{00A0}", 'mg/dL'],
            'not text' => [5, null],
            'nothing' => [null, null],
        ];
    }

    /**
     * One rule for createQuantity(), an immunization's dose (DoseUnit) and HL7 v2's RXA-7: trimmed as
     * the UCUM engine trims, so a code never goes out with spaces under UCUM's system, and the Drug
     * Units aliases.
     */
    #[DataProvider('ucumCodes')]
    public function testUcumCodeFor(mixed $unit, ?string $expected): void
    {
        $this->assertSame($expected, UtilsService::ucumCodeFor($unit));
    }

    public function testNoUnitIsASimpleQuantity(): void
    {
        $this->assertSame(['value' => 5], json_decode((string)json_encode(UtilsService::createQuantity(5, '', '')), true));
        $this->assertSame(['value' => 5], json_decode((string)json_encode(UtilsService::createQuantity(5, '  ', '  ')), true), 'a blank unit');
        $this->assertSame(['value' => 5], json_decode((string)json_encode(UtilsService::createQuantity(5, null, null)), true), 'no unit at all');
        $this->assertSame(['value' => 5], json_decode((string)json_encode(UtilsService::createQuantity(5, '0', '0')), true), 'a unit of 0, left off as before');
    }
}
