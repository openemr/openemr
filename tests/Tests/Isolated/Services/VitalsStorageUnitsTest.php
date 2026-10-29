<?php

/**
 * Isolated tests for VitalsStorageUnits.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Services\VitalsStorageUnits;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VitalsStorageUnitsTest extends TestCase
{
    /**
     * @return array<string, array{float, string, string, ?float}>
     */
    public static function conversionProvider(): array
    {
        return [
            'kilograms to pounds' => [70.0, 'kg', 'lb', 154.323584],
            'grams to pounds' => [3500.0, 'g', 'lb', 7.716179],
            'centimetres to inches' => [170.0, 'cm', 'in', 66.929134],
            'metres to inches' => [1.7, 'm', 'in', 66.929134],
            'Celsius to Fahrenheit' => [37.0, 'Cel', 'degF', 98.6],
            'C is read as Celsius' => [37.0, 'C', 'degF', 98.6],
            'the unit is read without case or spaces' => [70.0, ' KG ', 'lb', 154.323584],
            'pounds in UCUM stay pounds' => [154.5, '[lb_av]', 'lb', 154.5],
            'inches in UCUM stay inches' => [66.5, '[in_i]', 'in', 66.5],
            'Fahrenheit in UCUM stays Fahrenheit' => [98.6, '[degF]', 'degF', 98.6],
            'a unit with no conversion gives no value' => [10.0, '[oz_av]', 'lb', null],
            'a unit of another kind gives no value' => [70.0, 'kg', 'in', null],
        ];
    }

    #[DataProvider('conversionProvider')]
    public function testConvert(float $value, string $fromUnit, string $storageUnit, ?float $expected): void
    {
        $this->assertSame($expected, VitalsStorageUnits::convert($value, $fromUnit, $storageUnit));
    }

    public function testConvertSqlLeavesAColumnWithNoConversionAlone(): void
    {
        $this->assertSame('o.ob_value', VitalsStorageUnits::convertSql('o.ob_value', 'o.ob_unit', 'mm[Hg]'));
    }

    public function testConvertSqlConvertsOnlyTheUnitsThatConvertToTheStorageUnit(): void
    {
        $sql = VitalsStorageUnits::convertSql('o.ob_value', 'o.ob_unit', 'lb');

        $this->assertStringStartsWith('CASE LOWER(TRIM(o.ob_unit)) ', $sql);
        $this->assertStringContainsString("WHEN 'kg' THEN ROUND(CAST(o.ob_value AS DECIMAL(24,12)) * 2.20462262185, 6)", $sql);
        $this->assertStringContainsString("WHEN 'g' THEN", $sql);
        $this->assertStringNotContainsString("WHEN 'cm'", $sql);
        $this->assertStringEndsWith(' ELSE o.ob_value END', $sql);
    }
}
