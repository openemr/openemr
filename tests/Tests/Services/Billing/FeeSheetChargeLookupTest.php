<?php

/**
 * The eye form's "Populate Fee Sheet" picks the code row and price the fee sheet would.
 *
 * It looked codes up by code alone (no code type, no active flag, LIMIT 1),
 * so with an older or inactive row for the same code it billed that row's
 * old fee, while the fee sheet billed the current one.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\FeeSheetChargeLookup;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class FeeSheetChargeLookupTest extends TestCase
{
    /** A code no real code set uses; cleanup removes its rows and prices. */
    private const CODE = 'ZZ9015';

    private const PID_STANDARD = 990000201;
    private const PID_DISCOUNT = 990000202;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cleanUp();
        $this->addPatient(self::PID_STANDARD, 'standard');
        $this->addPatient(self::PID_DISCOUNT, 'discount');
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
        parent::tearDown();
    }

    public function testActiveRowWinsOverAnOlderInactiveRow(): void
    {
        $this->addCode('CPT4', 'Refraction (old)', 0, '', ['standard' => '37.00']);
        $this->addCode('CPT4', 'Refraction', 1, '', ['standard' => '44.00']);

        $this->assertSame(
            ['code_text' => 'Refraction', 'units' => '1', 'fee' => '44.00', 'modifier' => ''],
            (new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_STANDARD)
        );
    }

    public function testRowOfAnotherCodeTypeIsNotUsed(): void
    {
        $this->addCode('HCPCS', 'Other type', 1, '', ['standard' => '99.00']);
        $this->addCode('CPT4', 'Refraction', 1, '', ['standard' => '44.00']);

        $this->assertSame('44.00', (new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_STANDARD)['fee'] ?? null);
    }

    public function testRowWithoutAModifierWins(): void
    {
        $this->addCode('CPT4', 'Refraction, modifier', 1, '59', ['standard' => '50.00']);
        $this->addCode('CPT4', 'Refraction', 1, '', ['standard' => '44.00']);

        $this->assertSame('44.00', (new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_STANDARD)['fee'] ?? null);
    }

    public function testTheOnlyRowGivesItsModifier(): void
    {
        // A drug code entered with its usual modifier (JZ, no waste) has no row without one.
        $this->addCode('HCPCS', 'Chloroprocaine ophthalmic gel, 1 mg', 1, 'JZ', ['standard' => '0.80']);

        $this->assertSame(
            ['code_text' => 'Chloroprocaine ophthalmic gel, 1 mg', 'units' => '1', 'fee' => '0.80', 'modifier' => 'JZ'],
            (new FeeSheetChargeLookup())->find('HCPCS', self::CODE, self::PID_STANDARD)
        );
    }

    public function testFeeIsThePriceAtThePatientsPriceLevel(): void
    {
        $this->addCode('CPT4', 'Refraction', 1, '', ['standard' => '44.00', 'discount' => '30.00']);

        $this->assertSame('30.00', (new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_DISCOUNT)['fee'] ?? null);
    }

    public function testNumericCodeTypeIsMatchedByItsId(): void
    {
        $this->addCode('HCPCS', 'Other type', 1, '', ['standard' => '99.00']);
        $this->addCode('CPT4', 'Refraction', 1, '', ['standard' => '44.00']);
        $cptId = QueryUtils::fetchSingleValue('SELECT ct_id FROM code_types WHERE ct_key = ?', 'ct_id', ['CPT4']);
        $this->assertIsNumeric($cptId);

        $this->assertSame('44.00', (new FeeSheetChargeLookup())->find((string) $cptId, self::CODE, self::PID_STANDARD)['fee'] ?? null);
    }

    public function testCodeOfAnotherTypeIsUsedWhenTheGivenTypeHasNone(): void
    {
        // The item still gets a fee, as before, rather than none at all.
        $this->addCode('HCPCS', 'Entered as another type', 1, '', ['standard' => '44.00']);

        $this->assertSame('44.00', (new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_STANDARD)['fee'] ?? null);
    }

    public function testEmptyCodeTypeMatchesAnyType(): void
    {
        $this->addCode('HCPCS', 'Only row', 1, '', ['standard' => '12.00']);

        $this->assertSame('12.00', (new FeeSheetChargeLookup())->find('', self::CODE, self::PID_STANDARD)['fee'] ?? null);
    }

    public function testUnknownCodeIsNull(): void
    {
        $this->assertNull((new FeeSheetChargeLookup())->find('CPT4', self::CODE, self::PID_STANDARD));
    }

    /**
     * @param array<string, string> $prices price by price level
     */
    private function addCode(string $codeType, string $text, int $active, string $modifier, array $prices): void
    {
        $typeId = QueryUtils::fetchSingleValue('SELECT ct_id FROM code_types WHERE ct_key = ?', 'ct_id', [$codeType]);
        $this->assertNotNull($typeId, "code type $codeType is installed");
        $id = QueryUtils::sqlInsert(
            'INSERT INTO `codes` (`code_text`, `code`, `code_type`, `modifier`, `units`, `active`) VALUES (?, ?, ?, ?, 1, ?)',
            [$text, self::CODE, $typeId, $modifier, $active]
        );
        foreach ($prices as $level => $price) {
            QueryUtils::sqlInsert(
                'INSERT INTO `prices` (`pr_id`, `pr_selector`, `pr_level`, `pr_price`) VALUES (?, ?, ?, ?)',
                [$id, '', $level, $price]
            );
        }
    }

    private function addPatient(int $pid, string $priceLevel): void
    {
        QueryUtils::sqlInsert(
            'INSERT INTO `patient_data` (`pid`, `pubpid`, `fname`, `lname`, `pricelevel`, `date`) VALUES (?, ?, ?, ?, ?, NOW())',
            [$pid, 'fee-sheet-charge-lookup-test', 'Fee', 'Lookup', $priceLevel]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `prices` WHERE `pr_selector` = ? AND `pr_id` IN (SELECT `id` FROM `codes` WHERE `code` = ?)',
            ['', self::CODE]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `codes` WHERE `code` = ?', [self::CODE]);
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `patient_data` WHERE `pid` IN (?, ?)',
            [self::PID_STANDARD, self::PID_DISCOUNT]
        );
    }
}
