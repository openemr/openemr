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
    /** @return array<string, array{string, ?string, ?string}> unit and code sent, then the unit and UCUM code expected */
    public static function units(): array
    {
        return [
            'a lab unit in UCUM' => ['mg/dL', 'mg/dL', 'mg/dL'],
            'the same unit as many labs send it' => ['MG/DL', 'MG/DL', null],
            'a dispensed form' => ['tablet', 'tablet', null],
            'a cell count as labs send it' => ['Thous/CU.MM', 'Thous/CU.MM', null],
            'a UCUM annotation' => ['{score}', '{score}', '{score}'],
            'pounds by name' => ['lb', 'lb', null],
            'pounds in UCUM' => ['[lb_av]', '[lb_av]', '[lb_av]'],
            'a unit with no code' => ['mL', '', null],
            'a UCUM code with a space after it' => ['mg/dL', 'mg/dL ', null],
        ];
    }

    #[DataProvider('units')]
    public function testCreateQuantity(string $unit, string $code, ?string $expectedCode): void
    {
        $quantity = UtilsService::createQuantity(5, $unit, $code);
        $json = json_decode((string)json_encode($quantity), true);
        $this->assertIsArray($json);

        $this->assertSame($unit, $json['unit'] ?? null, 'the unit text is always sent');
        $this->assertSame($expectedCode, $json['code'] ?? null);
        $this->assertSame($expectedCode === null ? null : 'http://unitsofmeasure.org', $json['system'] ?? null);
    }

    /** @return array<string, array{string, bool}> a code, then whether it is UCUM as written */
    public static function codesAsWritten(): array
    {
        return [
            'UCUM' => ['mg/dL', true],
            'a space before it' => [' mg/dL', false],
            'a newline after it' => ["mg/dL\n", false],
            'only a space' => [' ', false],
        ];
    }

    /**
     * The UCUM engine trims what it checks, so a padded code would pass it and go out with
     * its spaces; it isn't UCUM as written. Observation values and components use this too.
     */
    #[DataProvider('codesAsWritten')]
    public function testIsUcumCodeTakesTheCodeAsWritten(string $code, bool $isUcum): void
    {
        $this->assertSame($isUcum, UtilsService::isUcumCode($code));
    }

    public function testNoUnitIsASimpleQuantity(): void
    {
        $json = json_decode((string)json_encode(UtilsService::createQuantity(5, '', '')), true);

        $this->assertSame(['value' => 5], $json);
    }
}
