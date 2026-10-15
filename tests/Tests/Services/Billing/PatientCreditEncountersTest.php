<?php

/**
 * Database tests for the statement run's credit lookup and line builder.
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
use OpenEMR\Billing\Statement\StatementLines;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class PatientCreditEncountersTest extends TestCase
{
    private const PID = 999901;

    /** @var array<int, int> encounter number => form_encounter.id */
    private array $encounterIds = [];

    protected function setUp(): void
    {
        $this->cleanUp();

        // 1: open, 100 charged and nothing paid.
        $this->addEncounter(1, '2026-08-01', 100.00, 0.00);
        // 2: overpaid, 60 charged and 110 paid by the patient: a 50 credit.
        $this->addEncounter(2, '2026-07-01', 60.00, 110.00);
        // 3: paid exactly.
        $this->addEncounter(3, '2026-06-01', 60.00, 60.00);
        // 4: a product sale of 20 against a 30 payment: a 10 credit.
        $this->addEncounter(4, '2026-05-01', 0.00, 30.00);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO drug_sales (drug_id, pid, encounter, user, sale_date, quantity, fee) " .
            "VALUES (1, ?, ?, 1, '2026-05-01', 1, 20.00)",
            [self::PID, 4]
        );
        // 5: looks overpaid only through a deleted payment, so it is not a credit.
        $this->addEncounter(5, '2026-04-01', 60.00, 100.00, '2026-04-02 00:00:00');
        // 6: charge voided (activity 0), the 25 payment stands: a 25 credit.
        $this->addEncounter(6, '2026-03-01', 80.00, 25.00);
        QueryUtils::sqlStatementThrowException("UPDATE billing SET activity = 0 WHERE pid = ? AND encounter = ?", [self::PID, 6]);
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testFindsOnlyTheEncountersWithACreditOldestFirst(): void
    {
        $credits = PatientCreditEncounters::find(self::PID, []);

        $this->assertSame([6, 4, 2], array_column($credits, 'encounter'));
        $this->assertSame(['-25.00', '-10.00', '-50.00'], array_column($credits, 'balance'));
        $this->assertSame($this->encounterIds[2], $credits[2]['id']);
        $this->assertSame('2026-07-01 00:00:00', $credits[2]['date']);
        $this->assertSame(0, $credits[2]['stmt_count']);
    }

    public function testExcludesTheEncountersAlreadyOnTheStatement(): void
    {
        $credits = PatientCreditEncounters::find(self::PID, [$this->encounterIds[1], $this->encounterIds[2]]);

        $this->assertSame([6, 4], array_column($credits, 'encounter'));
    }

    public function testAnotherPatientHasNoCredits(): void
    {
        $this->assertSame([], PatientCreditEncounters::find(self::PID + 1, []));
    }

    public function testAppendingACreditEncounterNetsTheAmountDue(): void
    {
        $stmt = ['lines' => [], 'amount' => '0.00', 'ins_paid' => 0];

        StatementLines::append($stmt, self::PID, 1, '2026-08-01', 1);
        $this->assertSame('100.00', $stmt['amount']);

        StatementLines::append($stmt, self::PID, 2, '2026-07-01', 1);
        $this->assertSame('50.00', $stmt['amount']);
        $lines = $stmt['lines'];
        $this->assertIsArray($lines);
        $this->assertCount(2, $lines);

        $credit = $lines[1] ?? null;
        $this->assertIsArray($credit);
        $this->assertSame('2026-07-01', $credit['dos'] ?? null);
        $this->assertSame('60.00', $credit['amount'] ?? null);
        $this->assertSame('110.00', $credit['paid'] ?? null);
        $this->assertSame('0.00', $credit['adjust'] ?? null);
        $this->assertSame(1, $credit['notice'] ?? null);
        $this->assertIsArray($credit['detail'] ?? null);
        $this->assertNotEmpty($credit['detail']);
    }

    private function addEncounter(int $encounter, string $date, float $charge, float $paid, ?string $deleted = null): void
    {
        $this->encounterIds[$encounter] = QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (date, reason, facility_id, pid, encounter, provider_id) " .
            "VALUES (?, 'credit test', 3, ?, ?, 1)",
            [$date . ' 00:00:00', self::PID, $encounter]
        );
        if ($charge != 0.0) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, " .
                "encounter, code_text, billed, activity, fee, units) " .
                "VALUES (?, 'CPT4', '99212', ?, 1, 1, 'Default', 1, ?, 'Office visit', 0, 1, ?, 1)",
                [$date . ' 00:00:00', self::PID, $encounter, $charge]
            );
        }
        if ($paid != 0.0) {
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ar_activity (pid, encounter, sequence_no, code_type, code, modifier, payer_type, " .
                "post_time, post_user, session_id, memo, pay_amount, adj_amount, account_code, follow_up, " .
                "modified_time, deleted) " .
                "VALUES (?, ?, 1, 'CPT4', '99212', '', 0, ?, 1, 0, 'Pt Paid', ?, 0, 'PCP', 'n', NOW(), ?)",
                [self::PID, $encounter, $date . ' 00:05:00', $paid, $deleted]
            );
        }
    }

    private function cleanUp(): void
    {
        foreach (['ar_activity', 'drug_sales', 'billing', 'form_encounter'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE pid = ?", [self::PID]);
        }
        $this->encounterIds = [];
    }
}
