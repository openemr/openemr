<?php

/**
 * Isolated tests for DoseUnit: the Drug Units titles UCUM accepts, and the option a doseQuantity names.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\FHIR\Immunization;

use OpenEMR\Services\FHIR\Immunization\DoseUnit;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class DoseUnitTest extends TestCase
{
    /** The Drug Units options as the installer seeds them (sql/database.sql). */
    private const SEEDED = ['1' => 'mg', '2' => 'mg/1cc', '3' => 'mg/2cc', '4' => 'mg/3cc', '5' => 'mg/4cc',
        '6' => 'mg/5cc', '7' => 'mcg', '8' => 'grams', '9' => 'mL'];

    /** @return array<string, array{string, ?string}> */
    public static function titles(): array
    {
        return [
            'mL is UCUM' => ['mL', 'mL'],
            'mg is UCUM' => ['mg', 'mg'],
            'mcg is ug in UCUM' => ['mcg', 'ug'],
            'grams is g in UCUM' => ['grams', 'g'],
            'mg/1cc is no unit' => ['mg/1cc', null],
            'mg/5cc is no unit' => ['mg/5cc', null],
            'an option id is no unit' => ['9', null],
            'blank is no unit' => ['', null],
        ];
    }

    #[DataProvider('titles')]
    public function testUcumFor(string $title, ?string $expected): void
    {
        $this->assertSame($expected, DoseUnit::ucumFor($title));
    }

    private const UCUM = 'http://unitsofmeasure.org';

    /** @return array<string, array{?string, ?string, ?string, ?string}> system, code, unit text, then the option expected */
    public static function doses(): array
    {
        return [
            'a UCUM code that is a title' => [self::UCUM, 'mL', null, '9'],
            'ug is the mcg option' => [self::UCUM, 'ug', 'micrograms', '7'],
            'g is the grams option' => [self::UCUM, 'g', null, '8'],
            'a code with no system' => [null, 'mL', null, '9'],
            'with no code, the unit text, in any case' => [null, null, 'ML', '9'],
            'with no code, unit text that is a UCUM alias' => [null, null, 'ug', '7'],
            'a code the list has no unit for' => [self::UCUM, '[iU]', 'international units', null],
            'a code the list has no unit for, with unit text it has' => [self::UCUM, '[iU]', 'mg', null],
            'a code from another system' => ['http://snomed.info/sct', 'g', 'grams', null],
            'nothing sent' => [null, null, null, null],
        ];
    }

    #[DataProvider('doses')]
    public function testOptionFor(?string $system, ?string $code, ?string $unit, ?string $expected): void
    {
        $this->assertSame($expected, DoseUnit::optionFor($system, $code, $unit, self::SEEDED));
    }
}
