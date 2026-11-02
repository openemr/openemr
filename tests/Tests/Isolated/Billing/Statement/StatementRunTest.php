<?php

/**
 * A statement run: one statement per patient, credits netted, nothing-due
 * patients skipped on Due Pt, counts recorded only when the run records, and
 * the email and portal paths.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\Statement;

use OpenEMR\Billing\Statement\StatementDelivery;
use OpenEMR\Billing\Statement\StatementRun;
use OpenEMR\Billing\Statement\StatementRunResult;
use OpenEMR\Billing\Statement\StatementRunSteps;
use PHPUnit\Framework\TestCase;

class StatementRunTest extends TestCase
{
    private const TODAY = '2026-10-30';

    private FakeStatementRunSteps $steps;

    protected function setUp(): void
    {
        $this->steps = new FakeStatementRunSteps();
    }

    public function testOneStatementPerPatientWithEveryInvoiceAndTheCredits(): void
    {
        // Ann owes 30 and 20 and has a 12.50 credit elsewhere; Bob owes 50.
        $this->steps->owed = [101 => 30.0, 102 => 20.0, 201 => 50.0];
        $this->steps->credits = [1 => [['encounter' => 109, 'date' => '2026-01-01 00:00:00', 'stmt_count' => 0]]];
        $this->steps->owed[109] = -12.5;

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-02-01'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')]);

        $this->assertSame(2, $result->statementsWritten);
        $this->assertSame([], $result->skippedNothingDue);
        $this->assertSame([1, 2], array_column($this->steps->built, 'pid'));
        $ann = $this->steps->built[0];
        $this->assertSame([101, 102, 109], $ann['encounters']);
        $this->assertSame('37.50', $ann['amount']);
        $this->assertSame('Ann Credit', $ann['patient']);
        $this->assertSame('2026-02-01', $ann['duedate'], 'the oldest invoice sets the due date');
        $this->assertEquals(271, $ann['age']);
        // The credit lookup leaves out every checked invoice, not only the patient's.
        $this->assertSame([[1, [11, 12, 21]], [2, [11, 12, 21]]], $this->steps->creditLookups);
        $this->assertSame('statement 1|statement 2|', $result->output);
        $this->assertSame([[1, 'statement 1|'], [2, 'statement 2|']], $this->steps->saved);
        $this->assertSame([[11, 12], [21]], $this->steps->recorded);
    }

    public function testDuePtSkipsAPatientTheCreditsLeaveNothingDue(): void
    {
        $this->steps->owed = [101 => 10.07, 102 => 20.0, 109 => -42.59, 201 => 50.0];
        $this->steps->credits = [1 => [['encounter' => 109, 'date' => '2026-01-21 00:00:00', 'stmt_count' => 0]]];
        $invoices = [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-09-08'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')];

        $result = $this->runStatements($invoices, skipNothingDue: true);

        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame(['Ann Credit'], $result->skippedNothingDue);
        $this->assertSame([2], array_column($this->steps->built, 'pid'));
        $this->assertSame([[21]], $this->steps->recorded, "a skipped patient's invoices aren't counted");
        $this->assertSame([[2, 'statement 2|']], $this->steps->saved);
    }

    public function testOtherCategoriesSendAStatementEvenWithNothingDue(): void
    {
        $this->steps->owed = [101 => 10.0, 109 => -40.0];
        $this->steps->credits = [1 => [['encounter' => 109, 'date' => '2026-01-21 00:00:00', 'stmt_count' => 0]]];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], skipNothingDue: false);

        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame([], $result->skippedNothingDue);
    }

    public function testWithoutUpdateRecordsNothing(): void
    {
        $this->steps->owed = [101 => 30.0];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], recordRun: false);

        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame([], $this->steps->recorded);
        $this->assertSame([[1, 'statement 1|']], $this->steps->saved);
    }

    public function testAnEmptyStatementGoesInTheBatchAsANotice(): void
    {
        $this->steps->owed = [101 => 3.0];
        $this->steps->emptyFor = [1];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')]);

        $this->assertSame('notice 1/101', $result->output);
        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame([[1, 'notice 1/101']], $this->steps->saved);
    }

    public function testEmailRunEmailsEachStatementAndKeepsGoingAfterAFailure(): void
    {
        $this->steps->owed = [101 => 30.0, 201 => 50.0];
        $this->steps->emailFailsFor = [1 => 'Patient email address is invalid'];

        $result = $this->runStatements(
            [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')],
            StatementDelivery::Email
        );

        $this->assertSame([[1, 'statement 1|'], [2, 'statement 2|']], $this->steps->emailed);
        $this->assertSame(['Ann Credit: Patient email address is invalid'], $result->emailFailures);
        $this->assertSame(2, $result->statementsWritten);
    }

    public function testAnEmptyStatementIsNotEmailed(): void
    {
        $this->steps->owed = [101 => 3.0];
        $this->steps->emptyFor = [1];

        $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], StatementDelivery::Email);

        $this->assertSame([], $this->steps->emailed);
    }

    public function testOnlyAnEmailRunEmails(): void
    {
        $this->steps->owed = [101 => 30.0];

        $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], StatementDelivery::Pdf);

        $this->assertSame([], $this->steps->emailed);
    }

    public function testPortalRunSendsEachPatientTheirStatementAfterEveryInvoice(): void
    {
        $this->steps->owed = [101 => 30.0, 102 => 20.0, 201 => 50.0];

        $result = $this->runStatements(
            [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-04-01'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')],
            StatementDelivery::Portal,
            skipNothingDue: true
        );

        $this->assertNull($result->portalError);
        $this->assertSame('', $result->output, 'portal statements are not batched');
        $this->assertSame(0, $result->statementsWritten);
        $this->assertSame([[1, 2, 'statement 1|', '1-101'], [2, 1, 'statement 2|', '2-201']], $this->steps->portal);
        $this->assertSame([[11, 12], [21]], $this->steps->recorded);
        $this->assertSame([], $this->steps->saved);
    }

    public function testPortalRunStopsAtAPatientWithoutPortalAccess(): void
    {
        $this->steps->owed = [101 => 30.0, 201 => 50.0];
        $this->steps->portalAuthorized = [1 => true, 2 => false];

        $result = $this->runStatements(
            [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')],
            StatementDelivery::Portal
        );

        $this->assertSame(StatementRunResult::PORTAL_NOT_AUTHORIZED, $result->portalError);
        $this->assertSame([[11]], $this->steps->recorded);
    }

    public function testPortalRunStopsWhenTheNotificationFails(): void
    {
        $this->steps->owed = [101 => 30.0, 201 => 50.0];
        $this->steps->portalFails = true;

        $result = $this->runStatements(
            [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')],
            StatementDelivery::Portal
        );

        $this->assertSame(StatementRunResult::PORTAL_FAILED, $result->portalError);
        $this->assertSame([], $this->steps->recorded);
        $this->assertCount(1, $this->steps->portal);
    }

    public function testTheOnlyPatientIsNamedForThePdfFileName(): void
    {
        $this->steps->owed = [101 => 30.0, 102 => 20.0, 201 => 50.0];

        $one = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-04-01')]);
        $two = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')]);
        $none = $this->runStatements([]);

        $this->assertSame(['firstName' => 'Ann', 'pid' => 1], $one->onlyPatient);
        $this->assertNull($two->onlyPatient);
        $this->assertNull($none->onlyPatient);
        $this->assertSame(0, $none->statementsWritten);
    }

    public function testTheStatementCarriesTheFirstInvoiceHeaderAndTheGuardian(): void
    {
        $this->steps->owed = [101 => 30.0];
        $invoice = self::invoice(1, 11, 101, '2026-03-04');
        $invoice['guardiansname'] = 'Pat Parent';

        $this->runStatements([$invoice]);

        $stmt = $this->steps->built[0];
        $this->assertSame(11, $stmt['fid']);
        $this->assertSame(101, $stmt['encounter']);
        $this->assertSame(self::TODAY, $stmt['today']);
        $this->assertIsArray($stmt['to']);
        $this->assertSame('Pat Parent', $stmt['to'][0]);
    }

    /**
     * @param list<array<string, mixed>> $invoices
     */
    private function runStatements(
        array $invoices,
        StatementDelivery $delivery = StatementDelivery::Print,
        bool $recordRun = true,
        bool $skipNothingDue = false,
    ): StatementRunResult {
        return (new StatementRun($this->steps))->run($invoices, $delivery, $recordRun, $skipNothingDue, self::TODAY);
    }

    /**
     * @return array<string, mixed>
     */
    private static function invoice(int $pid, int $id, int $encounter, string $date, string $fname = 'Ann', string $lname = 'Credit'): array
    {
        return [
            'id' => $id, 'date' => $date . ' 00:00:00', 'pid' => $pid, 'encounter' => $encounter, 'stmt_count' => 0,
            'last_level_billed' => 1, 'last_level_closed' => 1, 'enc_billing_note' => '', 'provider_id' => 1,
            'fname' => $fname, 'mname' => '', 'lname' => $lname, 'street' => '1 Main St', 'street_line_2' => '',
            'city' => 'Rutland', 'state' => 'VT', 'postal_code' => '05701', 'pat_billing_note' => '',
            'guardiansname' => '', 'guardianaddress' => '', 'guardiancity' => '', 'guardianstate' => '', 'guardianpostalcode' => '',
        ];
    }
}

/**
 * Records what the run asked for; "lines" are the encounters added and the amount is their sum.
 */
final class FakeStatementRunSteps implements StatementRunSteps
{
    /** @var array<int, float> encounter => balance */
    public array $owed = [];
    /** @var array<int, list<array{encounter: int, date: string, stmt_count: int}>> */
    public array $credits = [];
    /** @var list<int> pids whose statement comes out empty */
    public array $emptyFor = [];
    /** @var array<int, string> pid => email error */
    public array $emailFailsFor = [];
    /** @var array<int, bool> */
    public array $portalAuthorized = [];
    public bool $portalFails = false;

    /** @var list<array<string, mixed>> */
    public array $built = [];
    /** @var list<array{int, list<int>}> */
    public array $creditLookups = [];
    /** @var list<array{int, string}> */
    public array $saved = [];
    /** @var list<array{int, string}> */
    public array $emailed = [];
    /** @var list<array{int, int, string, string}> pid, invoices sent, statement, invoice id */
    public array $portal = [];
    /** @var list<list<int>> */
    public array $recorded = [];

    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void
    {
        $encounters = is_array($stmt['encounters'] ?? null) ? $stmt['encounters'] : [];
        $encounters[] = $encounter;
        $stmt['encounters'] = $encounters;
        $amount = is_numeric($stmt['amount'] ?? null) ? (float) $stmt['amount'] : 0.0;
        $stmt['amount'] = sprintf('%.2f', $amount + ($this->owed[$encounter] ?? 0.0));
    }

    public function creditEncounters(int $pid, array $excludedEncounterIds): array
    {
        $this->creditLookups[] = [$pid, $excludedEncounterIds];
        return $this->credits[$pid] ?? [];
    }

    public function buildStatement(array $stmt): string
    {
        $this->built[] = $stmt;
        $pid = is_numeric($stmt['pid'] ?? null) ? (int) $stmt['pid'] : 0;
        return in_array($pid, $this->emptyFor, true) ? '' : 'statement ' . $pid . '|';
    }

    public function emptyStatementNotice(int $pid, string $encounter): string
    {
        return 'notice ' . $pid . '/' . $encounter;
    }

    public function saveDocument(int $pid, string $statement): void
    {
        $this->saved[] = [$pid, $statement];
    }

    public function email(int $pid, string $statement): void
    {
        $this->emailed[] = [$pid, $statement];
        if (isset($this->emailFailsFor[$pid])) {
            throw new \RuntimeException($this->emailFailsFor[$pid]);
        }
    }

    public function isPortalAuthorized(int $pid): bool
    {
        return $this->portalAuthorized[$pid] ?? true;
    }

    public function notifyPortal(int $pid, array $invoices, string $statement, string $invoiceId): bool
    {
        $this->portal[] = [$pid, count($invoices), $statement, $invoiceId];
        return !$this->portalFails;
    }

    public function recordStatementRun(array $encounterIds): void
    {
        $this->recorded[] = $encounterIds;
    }
}
