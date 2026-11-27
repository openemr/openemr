<?php

/**
 * PatientBalanceLedger and EncounterBalance: which amounts each balance
 * counts.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\PatientBalance;

use OpenEMR\Billing\PatientBalance\EncounterBalance;
use OpenEMR\Billing\PatientBalance\PatientBalanceLedger;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class PatientBalanceLedgerTest extends TestCase
{
    private static function encounter(
        int $lastLevelClosed = 0,
        int $insuranceCount = 0,
        bool $inCollection = false,
    ): EncounterBalance {
        return new EncounterBalance(
            lastLevelClosed: $lastLevelClosed,
            insuranceCount: $insuranceCount,
            inCollection: $inCollection,
            charges: 120.0,
            copayCharges: 20.0,
            drugSales: 5.0,
            payments: 65.0,
            adjustments: 10.0,
            patientPayments: 15.0,
        );
    }

    #[Test]
    public function outToInsuranceCountsOnlyCopayLessPatientPaymentsForThePatient(): void
    {
        $encounter = self::encounter(lastLevelClosed: 0, insuranceCount: 1);
        self::assertSame(5.0, $encounter->amountDue(false));
    }

    #[Test]
    public function outToInsuranceCountsEverythingWhenInsuranceIsIncluded(): void
    {
        $encounter = self::encounter(lastLevelClosed: 0, insuranceCount: 1);
        self::assertSame(50.0, $encounter->amountDue(true));
    }

    #[Test]
    public function allLevelsClosedCountsEverythingForThePatient(): void
    {
        $encounter = self::encounter(lastLevelClosed: 2, insuranceCount: 2);
        self::assertSame(50.0, $encounter->amountDue(false));
    }

    #[Test]
    public function noInsuranceCountsEverythingForThePatient(): void
    {
        self::assertSame(50.0, self::encounter()->amountDue(false));
    }

    #[Test]
    public function balanceSumsEncountersToTwoDecimals(): void
    {
        $ledger = new PatientBalanceLedger([
            self::encounter(lastLevelClosed: 0, insuranceCount: 1),
            self::encounter(),
        ]);
        self::assertSame('55.00', $ledger->balance(false));
        self::assertSame('100.00', $ledger->balance(true));
    }

    #[Test]
    public function collectionBalanceSkipsEncountersNotInCollection(): void
    {
        $ledger = new PatientBalanceLedger([
            self::encounter(inCollection: true),
            self::encounter(),
        ]);
        self::assertSame('50.00', $ledger->balance(false, true));
    }

    #[Test]
    public function noEncountersIsZero(): void
    {
        $ledger = new PatientBalanceLedger([]);
        self::assertSame('0.00', $ledger->balance(false));
        self::assertSame('0.00', $ledger->balance(true, true));
    }

    #[Test]
    public function creditIsNegative(): void
    {
        $credit = new EncounterBalance(0, 0, false, 10.0, 0.0, 0.0, 25.5, 0.0, 25.5);
        self::assertSame('-15.50', (new PatientBalanceLedger([$credit]))->balance(false));
    }
}
