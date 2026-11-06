<?php

/**
 * A statement run: one statement per patient, credits netted, nothing-due
 * patients skipped on Due Pt, statements the print rules leave empty skipped
 * and logged, counts recorded only when the run records, and
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
use Psr\Log\AbstractLogger;

class StatementRunTest extends TestCase
{
    private const TODAY = '2026-10-30';

    private FakeStatementRunSteps $steps;

    private RecordingLogger $logger;

    protected function setUp(): void
    {
        $this->steps = new FakeStatementRunSteps();
        $this->logger = new RecordingLogger();
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

    public function testAStatementThePrintRulesLeaveEmptyIsNotSentRecordedOrSaved(): void
    {
        // Ann's 3.00 is under the site's minimum to print; Bob's 50.00 goes out.
        $this->steps->owed = [101 => 3.0, 201 => 50.0];
        $this->steps->emptyFor = [1];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')]);

        $this->assertSame('statement 2|', $result->output, 'no placeholder page in the batch');
        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame(['Ann Credit'], $result->skippedBelowMinimum);
        $this->assertSame([], $result->skippedNothingDue);
        $this->assertSame([[2, 'statement 2|']], $this->steps->saved);
        $this->assertSame([[21]], $this->steps->recorded, "the skipped patient's invoices aren't counted");
        $this->assertSame([[
            'warning',
            'Statement not sent: the print rules leave it empty',
            ['pid' => 1, 'encounters' => [11], 'amount' => 3.0, 'delivery' => 'Print'],
        ]], $this->logger->records);
    }

    public function testAnInvoiceAModuleLeftOffIsNotCountedAsStatemented(): void
    {
        // A module leaves Ann's 102 off (StatementEncounterEvent); her 101 goes out.
        $this->steps->owed = [101 => 30.0, 102 => 20.0];
        $this->steps->leftOff = [102 => 12];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-04-01')]);

        $this->assertSame([[11]], $this->steps->recorded, 'the invoice left off is not counted');
        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame([], $result->skippedLeftOff);
        $this->assertSame([101], $this->steps->built[0]['encounters']);
        $this->assertSame('30.00', $this->steps->built[0]['amount']);
        $this->assertSame([], $this->logger->records);
    }

    public function testAPatientWithEveryInvoiceLeftOffGetsNoStatementNotEvenTheirCredits(): void
    {
        // Ann's only checked invoice is left off; her 12.50 credit alone doesn't make a statement.
        $this->steps->owed = [101 => 30.0, 109 => -12.5, 201 => 50.0];
        $this->steps->credits = [1 => [['encounter' => 109, 'date' => '2026-01-01 00:00:00', 'stmt_count' => 0]]];
        $this->steps->leftOff = [101 => 11];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')]);

        $this->assertSame('statement 2|', $result->output);
        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame(['Ann Credit'], $result->skippedLeftOff);
        $this->assertSame([], $result->skippedBelowMinimum);
        $this->assertSame([2], array_column($this->steps->built, 'pid'));
        $this->assertSame([[2, 'statement 2|']], $this->steps->saved);
        $this->assertSame([[21]], $this->steps->recorded);
        $this->assertSame([[
            'info',
            'Statement not sent: a module left every invoice off',
            ['pid' => 1, 'encounters' => [11], 'delivery' => 'Print'],
        ]], $this->logger->records);
    }

    public function testTheDueDateIsTheOldestInvoiceOnTheStatement(): void
    {
        // Ann's oldest invoice (101) is left off, and her credit (109) is older still.
        $this->steps->owed = [101 => 30.0, 102 => 20.0, 109 => -12.5];
        $this->steps->credits = [1 => [['encounter' => 109, 'date' => '2026-01-01 00:00:00', 'stmt_count' => 0]]];
        $this->steps->leftOff = [101 => 11];

        $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-04-01')]);

        $this->assertSame('2026-04-01', $this->steps->built[0]['duedate']);
        $this->assertEquals(212, $this->steps->built[0]['age']);
    }

    public function testAPortalRunKeepsWhatWasLeftOffOutOfThePortalsCopy(): void
    {
        $this->steps->owed = [101 => 30.0, 102 => 20.0];
        $this->steps->leftOff = [101 => 11];

        $this->runStatements([self::invoice(1, 11, 101, '2026-03-04'), self::invoice(1, 12, 102, '2026-04-01')], StatementDelivery::Portal);

        // One copy, after the invoice on the statement, with nothing of the one left off.
        $this->assertCount(1, $this->steps->portalInvoices);
        $this->assertSame([102], $this->steps->portalInvoices[0]['encounters'] ?? null);
        $this->assertArrayNotHasKey('excluded', $this->steps->portalInvoices[0]);
        $this->assertSame([[12]], $this->steps->recorded);
    }

    public function testWithoutUpdateAPatientWithEveryInvoiceLeftOffStillGetsNoStatement(): void
    {
        $this->steps->owed = [101 => 30.0];
        $this->steps->leftOff = [101 => 11];

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], recordRun: false);

        $this->assertSame(0, $result->statementsWritten);
        $this->assertSame(['Ann Credit'], $result->skippedLeftOff);
        $this->assertSame([], $this->steps->built);
    }

    public function testAPortalRunSendsNothingForAPatientWithEveryInvoiceLeftOff(): void
    {
        $this->steps->owed = [101 => 30.0, 201 => 50.0];
        $this->steps->leftOff = [101 => 11];
        $this->steps->portalAuthorized = [1 => false, 2 => true];

        $result = $this->runStatements(
            [self::invoice(1, 11, 101, '2026-03-04'), self::invoice(2, 21, 201, '2026-05-01', 'Bob', 'Owes')],
            StatementDelivery::Portal
        );

        // Ann has no portal access, but nothing was going to her, so the run doesn't stop there.
        $this->assertNull($result->portalError);
        $this->assertSame(['Ann Credit'], $result->skippedLeftOff);
        $this->assertSame([[2, 1, 'statement 2|', '2-201']], $this->steps->portal);
        $this->assertSame([[21]], $this->steps->recorded);
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

        $result = $this->runStatements([self::invoice(1, 11, 101, '2026-03-04')], StatementDelivery::Email);

        $this->assertSame([], $this->steps->emailed);
        $this->assertSame([], $this->steps->saved);
        $this->assertSame(0, $result->statementsWritten);
        $this->assertSame(['Ann Credit'], $result->skippedBelowMinimum);
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
        return (new StatementRun($this->steps, $this->logger))->run($invoices, $delivery, $recordRun, $skipNothingDue, self::TODAY);
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
    /** @var array<int, int> encounter => form_encounter.id, for the encounters a module leaves off */
    public array $leftOff = [];
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
    /** @var list<array<string, mixed>> the statement after each invoice, as the portal got it */
    public array $portalInvoices = [];
    /** @var list<list<int>> */
    public array $recorded = [];

    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void
    {
        if (isset($this->leftOff[$encounter])) {
            // Kept the way StatementLines::append() keeps an encounter a module left off.
            $kept = is_array($stmt['excluded'] ?? null) ? $stmt['excluded'] : ['pid' => $pid, 'encounters' => []];
            $left = is_array($kept['encounters'] ?? null) ? $kept['encounters'] : [];
            $left[] = ['encounter' => $encounter, 'id' => $this->leftOff[$encounter], 'dos' => $serviceDate, 'lines' => [], 'amount' => '0.00'];
            $stmt['excluded'] = ['pid' => $pid, 'encounters' => $left];
            return;
        }
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
        array_push($this->portalInvoices, ...$invoices);
        return !$this->portalFails;
    }

    public function recordStatementRun(array $encounterIds): void
    {
        $this->recorded[] = $encounterIds;
    }
}

final class RecordingLogger extends AbstractLogger
{
    /** @var list<array{mixed, string, array<mixed>}> */
    public array $records = [];

    /**
     * @param array<mixed> $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [$level, (string) $message, $context];
    }
}
