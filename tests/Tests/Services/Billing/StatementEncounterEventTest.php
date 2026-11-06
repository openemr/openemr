<?php

/**
 * Database tests for leaving an encounter off a patient statement through StatementEncounterEvent.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing;

use OpenEMR\Billing\Statement\PatientCreditEncounters;
use OpenEMR\Billing\Statement\StatementDelivery;
use OpenEMR\Billing\Statement\StatementInvoices;
use OpenEMR\Billing\Statement\StatementLines;
use OpenEMR\Billing\Statement\StatementRun;
use OpenEMR\Billing\Statement\StatementRunResult;
use OpenEMR\Billing\Statement\StatementRunSteps;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Events\Billing\StatementEncounterEvent;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class StatementEncounterEventTest extends TestCase
{
    private const PID = 999902;

    /** @var array<int, int> encounter number => form_encounter.id */
    private array $encounterIds = [];

    protected function setUp(): void
    {
        $this->cleanUp();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO patient_data (pid, pubpid, fname, lname, street, city, state, postal_code, guardiansname) " .
            "VALUES (?, 'stmt-event-test', 'Ann', 'Patient', '1 Main St', 'Randolph', 'VT', '05060', 'Pat Parent')",
            [self::PID]
        );
        // 1: the older visit, 100 charged, nothing paid; its sensitivity is set, for information.
        $this->addEncounter(1, '2026-07-01', 100.00, 'high', 'note 1');
        // 2: the newer visit, 60 charged, nothing paid.
        $this->addEncounter(2, '2026-08-01', 60.00, 'normal', 'note 2');
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testWithNothingLeftOffTheStatementIsTheSameAsWithoutAListener(): void
    {
        $plain = $this->statement();
        StatementLines::append($plain, self::PID, 1, '2026-07-01', 1, new EventDispatcher());
        StatementLines::append($plain, self::PID, 2, '2026-08-01', 1, new EventDispatcher());

        $listening = new EventDispatcher();
        $seen = 0;
        $listening->addListener(StatementEncounterEvent::EVENT_NAME, function () use (&$seen): void {
            $seen++;
        });
        $heard = $this->statement();
        StatementLines::append($heard, self::PID, 1, '2026-07-01', 1, $listening);
        StatementLines::append($heard, self::PID, 2, '2026-08-01', 1, $listening);

        $this->assertSame(2, $seen);
        $this->assertSame($plain, $heard);
        $this->assertSame('160.00', $plain['amount']);
        $this->assertArrayNotHasKey('excluded', $plain);
        $this->assertSame(1, $plain['encounter']);
    }

    public function testALeftOffEncounterAddsNoLinesNoBalanceAndIsKept(): void
    {
        $events = [];
        $dispatcher = $this->leavingOff([1], $events);

        $stmt = $this->statement();
        StatementLines::append($stmt, self::PID, 1, '2026-07-01', 2, $dispatcher);
        StatementLines::append($stmt, self::PID, 2, '2026-08-01', 1, $dispatcher);

        $this->assertSame('60.00', $stmt['amount']);
        $this->assertIsArray($stmt['lines']);
        $this->assertSame(['2026-08-01'], array_column($stmt['lines'], 'dos'));

        $left = StatementLines::excluded($stmt);
        $this->assertCount(1, $left);
        $this->assertSame(1, $left[0]['encounter']);
        $this->assertSame($this->encounterIds[1], $left[0]['id']);
        $this->assertSame('2026-07-01', $left[0]['dos']);
        $this->assertSame('100.00', $left[0]['amount']);
        $this->assertNotEmpty($left[0]['lines']);

        // What the listener was told about the encounter it left off.
        $first = $events[0];
        $this->assertSame(self::PID, $first->getPid());
        $this->assertSame('2026-07-01', $first->getServiceDate());
        $this->assertSame('high', $first->getSensitivity());
        $this->assertSame(2, $first->getNotice());
        $this->assertSame('100.00', $first->getBalance());
        $this->assertSame(['Pat Parent', '1 Main St', 'Randolph, VT 05060'], $first->getRecipientLines());
        $this->assertFalse($first->isAddressedToPatient());

    }

    public function testTheHeaderComesFromAnEncounterOnTheStatement(): void
    {
        $events = [];
        $dispatcher = $this->leavingOff([1], $events);

        // The run starts a statement from its first (oldest) invoice: encounter 1.
        $stmt = $this->statement();
        StatementLines::append($stmt, self::PID, 1, '2026-07-01', 1, $dispatcher);
        StatementLines::append($stmt, self::PID, 2, '2026-08-01', 1, $dispatcher);

        $this->assertSame(2, $stmt['encounter']);
        $this->assertSame($this->encounterIds[2], $stmt['fid']);
        $this->assertSame('note 2', $stmt['enc_bill_note']);
        // The due date is the statement run's to move (StatementRun::run()).
        $this->assertSame('2026-07-01', $stmt['duedate']);
    }

    public function testEverythingLeftOffLeavesNothingToSendOrCount(): void
    {
        $events = [];
        $dispatcher = $this->leavingOff([1, 2], $events);

        $stmt = $this->statement();
        StatementLines::append($stmt, self::PID, 1, '2026-07-01', 1, $dispatcher);
        StatementLines::append($stmt, self::PID, 2, '2026-08-01', 1, $dispatcher);

        $this->assertSame([], $stmt['lines']);
        $this->assertSame('0.00', $stmt['amount']);
        $this->assertSame([1, 2], array_column(StatementLines::excluded($stmt), 'encounter'));
    }

    public function testTheRunCountsOnlyTheInvoicesOnTheStatement(): void
    {
        $events = [];
        $steps = new ListeningStatementRunSteps($this->leavingOff([1], $events));

        $result = $this->runBothInvoices($steps);

        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame([], $result->skippedLeftOff);
        $this->assertCount(1, $steps->built);
        $this->assertSame('60.00', $steps->built[0]['amount']);
        $this->assertSame(2, $steps->built[0]['encounter']);
        $this->assertSame('2026-08-01', $steps->built[0]['duedate'], 'the due date is the oldest invoice on the statement');
        $this->assertEquals(round((strtotime('2026-10-27') - strtotime('2026-08-01')) / 86400), $steps->built[0]['age']);
        $this->assertSame([0, 1], [$this->statementCount(1), $this->statementCount(2)]);
    }

    public function testTheRunSendsNothingWhenEveryInvoiceIsLeftOff(): void
    {
        $events = [];
        $steps = new ListeningStatementRunSteps($this->leavingOff([1, 2], $events));

        $result = $this->runBothInvoices($steps);

        $this->assertSame(0, $result->statementsWritten);
        $this->assertSame('', $result->output);
        $this->assertSame(['Ann Patient'], $result->skippedLeftOff);
        $this->assertSame([], $steps->built);
        $this->assertSame([0, 0], [$this->statementCount(1), $this->statementCount(2)]);
    }

    public function testTheRunWithAListenerThatLeavesNothingOffCountsEveryInvoice(): void
    {
        $events = [];
        $steps = new ListeningStatementRunSteps($this->leavingOff([], $events));

        $result = $this->runBothInvoices($steps);

        $this->assertCount(2, $events);
        $this->assertSame(1, $result->statementsWritten);
        $this->assertSame('160.00', $steps->built[0]['amount']);
        $this->assertSame([1, 1], [$this->statementCount(1), $this->statementCount(2)]);
    }

    /**
     * Both of the patient's invoices checked, run to print, counted as statemented.
     */
    private function runBothInvoices(ListeningStatementRunSteps $steps): StatementRunResult
    {
        return (new StatementRun($steps, new NullLogger()))->run(
            StatementInvoices::forRun([$this->encounterIds[1], $this->encounterIds[2]]),
            StatementDelivery::Print,
            true,
            false,
            '2026-10-27'
        );
    }

    private function statementCount(int $encounter): int
    {
        $row = QueryUtils::querySingleRow("SELECT stmt_count FROM form_encounter WHERE id = ?", [$this->encounterIds[$encounter]]);
        return is_array($row) && is_numeric($row['stmt_count'] ?? null) ? (int) $row['stmt_count'] : -1;
    }

    /**
     * A statement as the run starts it for this patient, from encounter 1.
     *
     * @return array<string, mixed>
     */
    private function statement(): array
    {
        return [
            'fid' => $this->encounterIds[1],
            'cid' => self::PID,
            'pid' => self::PID,
            'encounter' => 1,
            'enc_bill_note' => 'note 1',
            'to' => ['Pat Parent', '1 Main St', 'Randolph, VT 05060'],
            'lines' => [],
            'amount' => '0.00',
            'ins_paid' => 0,
            'today' => '2026-10-27',
            'duedate' => '2026-07-01',
        ];
    }

    /**
     * A dispatcher whose listener leaves the given encounters off, collecting every event.
     *
     * @param list<int> $encounters
     * @param list<StatementEncounterEvent> $events
     */
    private function leavingOff(array $encounters, array &$events): EventDispatcher
    {
        $dispatcher = new EventDispatcher();
        $dispatcher->addListener(StatementEncounterEvent::EVENT_NAME, function (StatementEncounterEvent $event) use ($encounters, &$events): void {
            $events[] = $event;
            if (in_array($event->getEncounter(), $encounters, true)) {
                $event->exclude();
            }
        });
        return $dispatcher;
    }

    private function addEncounter(int $encounter, string $date, float $charge, string $sensitivity, string $note): void
    {
        $this->encounterIds[$encounter] = QueryUtils::sqlInsert(
            "INSERT INTO form_encounter (date, reason, facility_id, pid, encounter, provider_id, sensitivity, billing_note) " .
            "VALUES (?, 'statement event test', 3, ?, ?, 1, ?, ?)",
            [$date . ' 00:00:00', self::PID, $encounter, $sensitivity, $note]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO billing (date, code_type, code, pid, provider_id, user, groupname, authorized, " .
            "encounter, code_text, billed, activity, fee, units) " .
            "VALUES (?, 'CPT4', '99212', ?, 1, 1, 'Default', 1, ?, 'Office visit', 0, 1, ?, 1)",
            [$date . ' 00:00:00', self::PID, $encounter, $charge]
        );
    }

    private function cleanUp(): void
    {
        foreach (['billing', 'form_encounter', 'patient_data'] as $table) {
            QueryUtils::sqlStatementThrowException("DELETE FROM $table WHERE pid = ?", [self::PID]);
        }
        $this->encounterIds = [];
    }
}

/**
 * The statement run's steps with the real lines, credits and counts, and the given
 * dispatcher in place of the kernel's; the statement text is not built.
 */
final class ListeningStatementRunSteps implements StatementRunSteps
{
    /** @var list<array<string, mixed>> */
    public array $built = [];

    public function __construct(private readonly EventDispatcherInterface $dispatcher)
    {
    }

    public function appendInvoice(array &$stmt, int $pid, int $encounter, string $serviceDate, int $notice): void
    {
        StatementLines::append($stmt, $pid, $encounter, $serviceDate, $notice, $this->dispatcher);
    }

    public function creditEncounters(int $pid, array $excludedEncounterIds): array
    {
        return PatientCreditEncounters::find($pid, $excludedEncounterIds);
    }

    public function buildStatement(array $stmt): string
    {
        $this->built[] = $stmt;
        return 'statement|';
    }

    public function saveDocument(int $pid, string $statement): void
    {
    }

    public function email(int $pid, string $statement): void
    {
    }

    public function isPortalAuthorized(int $pid): bool
    {
        return false;
    }

    public function notifyPortal(int $pid, array $invoices, string $statement, string $invoiceId): bool
    {
        return false;
    }

    public function recordStatementRun(array $encounterIds): void
    {
        StatementInvoices::recordStatementRun($encounterIds, '2026-10-27');
    }
}
