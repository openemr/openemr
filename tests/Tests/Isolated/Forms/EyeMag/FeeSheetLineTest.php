<?php

/**
 * Isolated tests for the billing lines the eye exam's Populate Fee Sheet writes.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Forms\EyeMag;

use OpenEMR\Billing\HcpcsDrugDefaults;
use OpenEMR\Forms\EyeMag\FeeSheetLine;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
class FeeSheetLineTest extends TestCase
{
    public function testVisitCodeKeepsItsPriceAndTypedModifier(): void
    {
        $line = FeeSheetLine::fromCharge(
            ['code_text' => 'NP eye intermediate', 'units' => '1', 'fee' => '120.00', 'modifier' => ''],
            null,
            '25'
        );

        $this->assertEquals(new FeeSheetLine('NP eye intermediate', 1, '120.00', '', '25'), $line);
    }

    public function testDrugTakesItsUnitsNdcAndTheCodesModifier(): void
    {
        // Iheezo: J2403 is 1 mg of gel, $0.80 a unit; one 800 mg vial is 800 units, no waste.
        $line = FeeSheetLine::fromCharge(
            ['code_text' => 'Chloroprocaine ophthalmic gel, 1 mg', 'units' => '1', 'fee' => '0.80', 'modifier' => 'JZ'],
            new HcpcsDrugDefaults('N482667-0300-01   UN1', 800),
            ''
        );

        $this->assertEquals(
            new FeeSheetLine('Chloroprocaine ophthalmic gel, 1 mg', 800, '640.00', 'N482667-0300-01   UN1', 'JZ'),
            $line
        );
    }

    public function testTypedModifierWinsOverTheCodes(): void
    {
        $line = FeeSheetLine::fromCharge(
            ['code_text' => 'Drug', 'units' => '1', 'fee' => '0.80', 'modifier' => 'JZ'],
            new HcpcsDrugDefaults('N482667-0300-01   UN1', 800),
            ' JW '
        );

        $this->assertSame('JW', $line->modifier);
    }

    public function testDrugWithoutDefaultUnitsUsesTheCodesUnits(): void
    {
        $line = FeeSheetLine::fromCharge(
            ['code_text' => 'Drug', 'units' => '2', 'fee' => '2500.00', 'modifier' => ''],
            new HcpcsDrugDefaults('0009-3073-01', null),
            ''
        );

        $this->assertSame(2, $line->units);
        $this->assertSame('5000.00', $line->fee);
        $this->assertSame('0009-3073-01', $line->ndcInfo);
    }

    public function testNoPriceStaysNoPriceAndUnitsAreAtLeastOne(): void
    {
        $line = FeeSheetLine::fromCharge(
            ['code_text' => 'Unpriced', 'units' => '', 'fee' => null, 'modifier' => ''],
            null,
            ''
        );

        $this->assertSame(1, $line->units);
        $this->assertNull($line->fee);
    }
}
