<?php

/**
 * Database-backed coverage for the prepayment balance query.
 *
 * The arithmetic on a single row is pinned by the isolated
 * PrepaymentBalanceTest. What can only be verified against a real database is
 * the query itself: which sessions it selects, how it aggregates distributions,
 * and how each filter narrows the result.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (C) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\PrepaymentBalance;
use OpenEMR\Billing\PrepaymentBalanceService;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrepaymentBalanceServiceTest extends TestCase
{
    private PrepaymentBalanceService $service;

    /** @var list<int> Session ids created by a test, torn down afterwards. */
    private array $sessionIds = [];

    private int $pid;

    protected function setUp(): void
    {
        $this->service = new PrepaymentBalanceService();
        // patient_data.pid is not auto-increment (id is), so pick one above the
        // current max and insert it explicitly.
        $this->pid = $this->fetchInt(
            "SELECT COALESCE(MAX(pid), 0) + 1 AS next FROM patient_data",
            'next',
            []
        );
        QueryUtils::sqlInsert(
            "INSERT INTO patient_data (pid, lname, fname, mname) VALUES (?, ?, ?, ?)",
            [$this->pid, 'Prepay', 'Fixture', '']
        );
    }

    protected function tearDown(): void
    {
        foreach ($this->sessionIds as $sessionId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM ar_activity WHERE session_id = ?",
                [$sessionId]
            );
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM ar_session WHERE session_id = ?",
                [$sessionId]
            );
        }
        $this->sessionIds = [];

        QueryUtils::sqlStatementThrowException("DELETE FROM patient_data WHERE pid = ?", [$this->pid]);
    }

    #[Test]
    public function anUndistributedPrepaymentIsReportedInFull(): void
    {
        $sessionId = $this->createPrepayment(500.00);

        $balance = $this->findSession($sessionId);

        $this->assertNotNull($balance, 'an open prepayment with no distributions must be listed');
        $this->assertSame(500.00, $balance->received);
        $this->assertSame(0.0, $balance->applied);
        $this->assertSame(500.00, $balance->unapplied);
        $this->assertSame('Prepay, Fixture', $balance->patientName);
    }

    #[Test]
    public function partialDistributionLeavesTheRemainderUnapplied(): void
    {
        $sessionId = $this->createPrepayment(500.00);
        $this->distribute($sessionId, 200.00);

        $balance = $this->findSession($sessionId);

        $this->assertNotNull($balance);
        $this->assertSame(200.00, $balance->applied);
        $this->assertSame(300.00, $balance->unapplied);
    }

    /**
     * The applied total is pre-aggregated in a derived table. Joining
     * ar_activity directly would repeat the ar_session row once per
     * distribution and multiply pay_total across the copies, so a session with
     * two distributions is the case that catches a regression there.
     */
    #[Test]
    public function multipleDistributionsDoNotFanOutTheSessionRow(): void
    {
        $sessionId = $this->createPrepayment(500.00);
        $this->distribute($sessionId, 100.00);
        $this->distribute($sessionId, 150.00);

        $matches = array_filter(
            $this->service->getOpenBalances(),
            static fn(PrepaymentBalance $b): bool => $b->sessionId === $sessionId
        );

        $this->assertCount(1, $matches, 'the session must appear exactly once regardless of distribution count');
        $balance = array_values($matches)[0];
        $this->assertSame(250.00, $balance->applied);
        $this->assertSame(250.00, $balance->unapplied);
    }

    #[Test]
    public function fullyDistributedSessionsAreExcluded(): void
    {
        $sessionId = $this->createPrepayment(500.00);
        $this->distribute($sessionId, 500.00);

        $this->assertNull($this->findSession($sessionId));
    }

    /**
     * ar_activity.deleted is set when a distribution is voided rather than the
     * row being removed, so voided money must return to the unapplied total.
     */
    #[Test]
    public function voidedDistributionsReturnToUnapplied(): void
    {
        $sessionId = $this->createPrepayment(500.00);
        $this->distribute($sessionId, 500.00);

        // A fully applied session drops off the report; that is pinned by
        // fullyDistributedSessionsAreExcluded() rather than re-asserted here.
        QueryUtils::sqlStatementThrowException(
            "UPDATE ar_activity SET deleted = NOW() WHERE session_id = ?",
            [$sessionId]
        );

        $balance = $this->findSession($sessionId);
        $this->assertNotNull($balance, 'voiding the distribution must put the session back on the report');
        $this->assertSame(0.0, $balance->applied);
        $this->assertSame(500.00, $balance->unapplied);
    }

    #[Test]
    public function closedSessionsAreExcluded(): void
    {
        $sessionId = $this->createPrepayment(500.00, closed: 1);

        $this->assertNull($this->findSession($sessionId));
    }

    #[Test]
    public function nonPrepaymentCategoriesAreExcluded(): void
    {
        $sessionId = $this->createPrepayment(500.00, adjustmentCode: 'patient_payment');

        $this->assertNull($this->findSession($sessionId));
    }

    /**
     * Sub-cent residue from rounding is not a balance worth chasing; the query
     * filters on an epsilon rather than on > 0.
     */
    #[Test]
    public function subCentResidueIsBelowTheReportingThreshold(): void
    {
        $sessionId = $this->createPrepayment(500.00);
        $this->distribute($sessionId, 499.999);

        $this->assertNull($this->findSession($sessionId));
    }

    #[Test]
    public function moneyParkedInGlobalStillCountsAsUnapplied(): void
    {
        $sessionId = $this->createPrepayment(500.00, globalAmount: 120.00);

        $balance = $this->findSession($sessionId);

        $this->assertNotNull($balance);
        $this->assertSame(120.00, $balance->inGlobal, 'parked credit is reported in its own column');
        $this->assertSame(500.00, $balance->unapplied, 'and is not subtracted from the unapplied total');
    }

    #[Test]
    public function parkedOnlyFilterExcludesSessionsWithNothingInGlobal(): void
    {
        $plain = $this->createPrepayment(500.00);
        $parked = $this->createPrepayment(300.00, globalAmount: 80.00);

        $sessionIds = array_map(
            static fn(PrepaymentBalance $b): int => $b->sessionId,
            $this->service->getOpenBalances(parkedOnly: true)
        );

        $this->assertContains($parked, $sessionIds);
        $this->assertNotContains($plain, $sessionIds);
    }

    #[Test]
    public function checkDateRangeNarrowsTheResultSet(): void
    {
        $old = $this->createPrepayment(100.00, checkDate: '2020-01-15');
        $recent = $this->createPrepayment(200.00, checkDate: '2020-06-15');

        $inRange = array_map(
            static fn(PrepaymentBalance $b): int => $b->sessionId,
            $this->service->getOpenBalances('2020-06-01', '2020-06-30')
        );

        $this->assertContains($recent, $inRange);
        $this->assertNotContains($old, $inRange);
    }

    #[Test]
    public function patientFilterLimitsToOnePatient(): void
    {
        $sessionId = $this->createPrepayment(500.00);

        $mine = $this->service->getOpenBalances(patientId: $this->pid);
        $others = $this->service->getOpenBalances(patientId: $this->pid + 100000);

        $this->assertContains(
            $sessionId,
            array_map(static fn(PrepaymentBalance $b): int => $b->sessionId, $mine)
        );
        $this->assertSame([], $others, 'a pid with no prepayments returns nothing');
    }

    #[Test]
    public function resultsAreOrderedByUnappliedDescending(): void
    {
        $this->createPrepayment(100.00, checkDate: '2020-03-01');
        $this->createPrepayment(900.00, checkDate: '2020-03-02');
        $this->createPrepayment(400.00, checkDate: '2020-03-03');

        $unapplied = array_map(
            static fn(PrepaymentBalance $b): float => $b->unapplied,
            $this->service->getOpenBalances(patientId: $this->pid)
        );

        $sorted = $unapplied;
        rsort($sorted);
        $this->assertSame($sorted, $unapplied);
    }

    private function createPrepayment(
        float $payTotal,
        string $adjustmentCode = 'pre_payment',
        int $closed = 0,
        float $globalAmount = 0.0,
        string $checkDate = '2020-02-01',
    ): int {
        $sessionId = (int) QueryUtils::sqlInsert(
            "INSERT INTO ar_session
                (payer_id, patient_id, user_id, closed, reference, check_date, deposit_date,
                 pay_total, modified_time, global_amount, payment_type, description,
                 adjustment_code, post_to_date, payment_method)
             VALUES (0, ?, 1, ?, 'TESTFIXTURE', ?, ?, ?, NOW(), ?, 'patient', 'test fixture', ?, ?, 'check')",
            [$this->pid, $closed, $checkDate, $checkDate, $payTotal, $globalAmount, $adjustmentCode, $checkDate]
        );
        $this->sessionIds[] = $sessionId;

        return $sessionId;
    }

    private function distribute(int $sessionId, float $amount): void
    {
        $nextSeq = $this->fetchInt(
            "SELECT COALESCE(MAX(sequence_no), 0) + 1 AS seq FROM ar_activity WHERE pid = ? AND encounter = 0",
            'seq',
            [$this->pid]
        );

        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ar_activity
                (pid, encounter, sequence_no, code_type, code, modifier, payer_type, post_time,
                 post_user, session_id, memo, pay_amount, adj_amount, modified_time, follow_up,
                 account_code, deleted)
             VALUES (?, 0, ?, '', '', '', 0, NOW(), 1, ?, '', ?, 0, NOW(), '', 'PP', NULL)",
            [$this->pid, $nextSeq, $sessionId, $amount]
        );
    }

    /**
     * fetchSingleValue() is untyped, so narrow rather than cast mixed.
     *
     * @param list<mixed> $binds
     */
    private function fetchInt(string $sql, string $column, array $binds): int
    {
        $value = QueryUtils::fetchSingleValue($sql, $column, $binds);

        return is_numeric($value) ? (int) $value : 0;
    }

    private function findSession(int $sessionId): ?PrepaymentBalance
    {
        foreach ($this->service->getOpenBalances() as $balance) {
            if ($balance->sessionId === $sessionId) {
                return $balance;
            }
        }

        return null;
    }
}
