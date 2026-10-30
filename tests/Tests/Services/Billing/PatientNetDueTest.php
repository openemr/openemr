<?php

/**
 * Database tests for finding the patients a Due Pt statement run will skip.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Statement\PatientCreditEncounters;
use OpenEMR\Billing\Statement\PatientNetDue;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class PatientNetDueTest extends TestCase
{
    /** Owes 30.07 on two visits and is owed 42.59 on a third: nets to -12.52. */
    private const NET_CREDIT_PID = 999911;
    /** Owes 30.00 and is owed 10.00: still owes 20.00. */
    private const STILL_OWES_PID = 999912;
    /** Owes 25.00 and is owed exactly 25.00: nets to zero. */
    private const NETS_TO_ZERO_PID = 999913;
    /** Owes 10.00 for the visit and 100.00 for a product sale on it, and is owed 20.00: still owes 90.00. */
    private const PRODUCT_SALE_PID = 999914;

    /** @var array<int, array<int, int>> pid => [encounter number => form_encounter.id] */
    private array $ids = [];

    protected function setUp(): void
    {
        $this->cleanUp();

        $this->addEncounter(self::NET_CREDIT_PID, 1, '2026-01-21', 100.00, 142.59);
        $this->addEncounter(self::NET_CREDIT_PID, 2, '2026-03-04', 20.07, 10.00);
        $this->addEncounter(self::NET_CREDIT_PID, 3, '2026-09-08', 20.00, 0.00);

        $this->addEncounter(self::STILL_OWES_PID, 1, '2026-02-01', 50.00, 60.00);
        $this->addEncounter(self::STILL_OWES_PID, 2, '2026-04-01', 30.00, 0.00);

        $this->addEncounter(self::NETS_TO_ZERO_PID, 1, '2026-02-01', 40.00, 65.00);
        $this->addEncounter(self::NETS_TO_ZERO_PID, 2, '2026-04-01', 25.00, 0.00);

        $this->addEncounter(self::PRODUCT_SALE_PID, 1, '2026-02-01', 30.00, 50.00);
        $this->addEncounter(self::PRODUCT_SALE_PID, 2, '2026-04-01', 10.00, 0.00);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO drug_sales (drug_id, pid, encounter, user, sale_date, quantity, fee) " .
            "VALUES (1, ?, 2, 1, '2026-04-01', 1, 100.00)",
            [self::PRODUCT_SALE_PID]
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testFindsThePatientsWhoseCreditsCoverTheirOpenInvoices(): void
    {
        $nothingDue = PatientNetDue::nothingDue([
            self::NET_CREDIT_PID => [$this->ids[self::NET_CREDIT_PID][2], $this->ids[self::NET_CREDIT_PID][3]],
            self::STILL_OWES_PID => [$this->ids[self::STILL_OWES_PID][2]],
            self::NETS_TO_ZERO_PID => [$this->ids[self::NETS_TO_ZERO_PID][2]],
        ]);

        $this->assertSame([
            self::NET_CREDIT_PID => '-12.52',
            self::NETS_TO_ZERO_PID => '0.00',
        ], $nothingDue);
    }

    public function testAListedCreditIsNotCountedTwice(): void
    {
        // The credit encounter is itself in the list (e.g. category All), so its
        // balance is already among the open ones and the lookup must skip it.
        $nothingDue = PatientNetDue::nothingDue([
            self::STILL_OWES_PID => [$this->ids[self::STILL_OWES_PID][1], $this->ids[self::STILL_OWES_PID][2]],
        ]);

        $this->assertSame([], $nothingDue);
    }

    public function testProductSalesCountAsTheStatementCountsThem(): void
    {
        // The search's balance leaves out product sales: 10.00 owed against a 20.00
        // credit would look like nothing due, but the statement shows 90.00.
        $nothingDue = PatientNetDue::nothingDue([
            self::PRODUCT_SALE_PID => [$this->ids[self::PRODUCT_SALE_PID][2]],
        ]);

        $this->assertSame([], $nothingDue);
    }

    public function testBalancesUseTheStatementArithmetic(): void
    {
        $productSale = $this->ids[self::PRODUCT_SALE_PID];
        $stillOwes = $this->ids[self::STILL_OWES_PID];

        $balances = PatientCreditEncounters::balancesByPatient([$productSale[1], $productSale[2], $stillOwes[2]]);

        ksort($balances);
        $this->assertSame([
            self::STILL_OWES_PID => [$stillOwes[2] => 30.0],
            self::PRODUCT_SALE_PID => [$productSale[1] => -20.0, $productSale[2] => 110.0],
        ], $balances);
        $this->assertSame([], PatientCreditEncounters::balancesByPatient([]));
    }

    public function testCreditsAreFoundForSeveralPatientsAtOnce(): void
    {
        $credits = PatientCreditEncounters::creditsByPatient(
            [self::NET_CREDIT_PID, self::STILL_OWES_PID, self::NETS_TO_ZERO_PID],
            [$this->ids[self::NETS_TO_ZERO_PID][1]]
        );

        ksort($credits);
        $this->assertSame([
            self::NET_CREDIT_PID => [-42.59],
            self::STILL_OWES_PID => [-10.0],
        ], $credits);
        $this->assertSame([], PatientCreditEncounters::creditsByPatient([], []));
    }

    public function testNoPatientsGivenFindsNone(): void
    {
        $this->assertSame([], PatientNetDue::nothingDue([]));
    }

    public function testNetRoundsToCents(): void
    {
        $this->assertSame(-12.52, PatientNetDue::net([10.07, 20.0], [-42.59]));
        $this->assertSame(0.0, PatientNetDue::net([0.1, 0.2], [-0.3]));
    }

    private function addEncounter(int $pid, int $encounter, string $date, float $charge, float $paid): void
    {
        $this->ids[$pid][$encounter] = QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (date, reason, facility_id, pid, encounter, provider_id) " .
            "VALUES (?, 'net due test', 3, ?, ?, 1)",
            [$date . ' 00:00:00', $pid, $encounter]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, " .
            "encounter, code_text, billed, activity, fee, units) " .
            "VALUES (?, 'CPT4', '99212', ?, 1, 1, 'Default', 1, ?, 'Office visit', 0, 1, ?, 1)",
            [$date . ' 00:00:00', $pid, $encounter, $charge]
        );
        if ($paid != 0.0) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, " .
                "post_time, post_user, session_id, memo, pay_amount, adj_amount, account_code, follow_up, " .
                "modified_time) " .
                "VALUES (?, ?, 1, 'CPT4', '99212', '', 0, ?, 1, 0, 'Pt Paid', ?, 0, 'PCP', 'n', NOW())",
                [$pid, $encounter, $date . ' 00:05:00', $paid]
            );
        }
    }

    private function cleanUp(): void
    {
        foreach ([self::NET_CREDIT_PID, self::STILL_OWES_PID, self::NETS_TO_ZERO_PID, self::PRODUCT_SALE_PID] as $pid) {
            foreach (['ar_activity', 'drug_sales', 'billing', 'form_encounter'] as $table) {
                QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE pid = ?", [$pid]);
            }
        }
        $this->ids = [];
    }
}
