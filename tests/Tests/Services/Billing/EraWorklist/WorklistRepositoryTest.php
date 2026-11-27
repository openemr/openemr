<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\AutoPostRepository;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\HoldReason;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraWorklist\PostedCheck;
use OpenEMR\Billing\EraWorklist\PostedLine;
use OpenEMR\Billing\EraWorklist\ReceivedFile;
use OpenEMR\Billing\EraWorklist\ReviewClaim;
use OpenEMR\Billing\EraWorklist\WorklistFilter;
use OpenEMR\Billing\EraWorklist\WorklistRepository;
use OpenEMR\Billing\EraWorklist\WorklistUser;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\TestCase;

class WorklistRepositoryTest extends TestCase
{
    /** Synthetic ids and names, clear of the demo data. */
    private const PARTNER = 99990071;
    private const USER = 99990471;
    private const NAMELESS_USER = 99990472;
    private const PID = 99990871;
    private const ENC = 99990971;
    private const SESSION = 99990301;
    private const PAYER = 'WORKLIST TEST PAYER';
    private const OTHER_PAYER = 'WORKLIST OTHER PAYER';

    private WorklistRepository $worklist;

    protected function setUp(): void
    {
        $this->cleanUp();
        $this->worklist = new WorklistRepository();
        foreach ([[self::USER, 'Billie', 'Biller'], [self::NAMELESS_USER, '', '']] as [$id, $first, $last]) {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `users` (`id`, `uuid`, `username`, `fname`, `lname`, `active`) VALUES (?, ?, ?, ?, ?, 1)',
                [$id, random_bytes(16), 'worklist' . $id, $first, $last]
            );
        }
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO `x12_partners` (`id`, `name`) VALUES (?, 'Worklist Clearinghouse')",
            [self::PARTNER]
        );
        QueryUtils::sqlInsert(
            'INSERT INTO form_encounter (pid, encounter, date, last_level_billed, last_level_closed) VALUES (?, ?, ?, 1, 0)',
            [self::PID, self::ENC, '2026-09-15 00:00:00']
        );
    }

    protected function tearDown(): void
    {
        $this->cleanUp();
    }

    public function testClaimPaymentsLeftForReviewAreListedNewestCheckFirst(): void
    {
        $older = $this->check($this->received('older.edi'), 'CHK-OLD', processedAt: '2026-11-20 08:00:00');
        $newer = $this->check($this->received('newer.edi'), 'CHK-NEW', processedAt: '2026-11-21 08:00:00', checkDate: '');
        $this->claim($older, 1, 'posted');
        $denied = $this->claim($older, 2, 'review', 'denied,lines_pended', claimPayment: 0.0);
        $this->claim($older, 3, 'skipped', 'already_posted');
        $notFound = $this->claim($newer, 1, 'review', 'claim_not_found', pid: 0, encounter: 0, claimPayment: 55.5);
        // A held check is worked from its file, not one claim payment at a time.
        $held = $this->check($this->received('held.edi'), 'CHK-HELD', status: 'held', holdReason: 'out_of_balance');
        $this->claim($held, 1, 'review', 'check_held', pid: 0, encounter: 0);

        $page = $this->worklist->reviewClaims(new WorklistFilter(payerName: self::PAYER), 10);

        $this->assertSame(2, $page->total);
        $this->assertSame([$notFound, $denied], array_map(static fn(ReviewClaim $claim): int => $claim->id, $page->rows));
        [$first, $second] = $page->rows;
        $this->assertSame('CHK-NEW', $first->checkNumber);
        $this->assertSame('', $first->checkDate);
        $this->assertSame(0, $first->invoiceId);
        $this->assertSame(['claim_not_found'], $first->reasons);
        $this->assertSame(55.5, $first->claimPayment);
        $this->assertNull($first->worked);
        $this->assertSame('2026-10-20', $second->checkDate);
        $this->assertSame(self::PAYER, $second->payerName);
        $this->assertSame('2026-11-20 08:00:00', $second->processedAt->format('Y-m-d H:i:s'));
        $this->assertSame('ANNE POSTING', $second->patientName);
        $this->assertSame([self::PID, self::ENC], [$second->pid, $second->encounter]);
        $this->assertSame($this->invoiceId(), $second->invoiceId);
        $this->assertSame(self::PID . '-' . self::ENC, $second->ourClaimId);
        $this->assertSame('ICN2', $second->payerClaimId);
        $this->assertSame(1, $second->payerLevel);
        $this->assertSame('4', $second->claimStatusCode);
        $this->assertSame(['denied', 'lines_pended'], $second->reasons);
        $this->assertSame([12.0, 3.0], [$second->paymentsPosted, $second->adjustmentsPosted]);
    }

    public function testTheReviewListIsNarrowedByReasonCheckPayerAndDay(): void
    {
        $first = $this->check($this->received('a.edi'), 'EFT1001', processedAt: '2026-11-20 23:59:59');
        $second = $this->check($this->received('b.edi'), 'EFT2002', processedAt: '2026-11-21 00:00:00');
        $other = $this->check($this->received('c.edi'), 'EFT1001X', processedAt: '2026-11-22 12:00:00', payer: self::OTHER_PAYER);
        $denied = $this->claim($first, 1, 'review', 'denied');
        $pended = $this->claim($first, 2, 'review', 'charge_mismatch,lines_pended');
        $later = $this->claim($second, 1, 'review', 'denied');
        $otherPayer = $this->claim($other, 1, 'review', 'denied');
        $ours = [$denied, $pended, $later, $otherPayer];
        $day = static fn(string $date): \DateTimeImmutable => new \DateTimeImmutable($date);

        $this->assertSame([$later, $denied], $this->reviewIds(new WorklistFilter(payerName: self::PAYER, reason: ClaimIssue::Denied), $ours));
        // A reason matches whole, among several.
        $this->assertSame([$pended], $this->reviewIds(new WorklistFilter(reason: ClaimIssue::LinesPended), $ours));
        $this->assertSame([$otherPayer, $denied, $pended], $this->reviewIds(new WorklistFilter(checkNumber: 'T100'), $ours));
        $this->assertSame([$otherPayer], $this->reviewIds(new WorklistFilter(payerName: self::OTHER_PAYER), $ours));
        $this->assertSame([$denied, $pended], $this->reviewIds(new WorklistFilter(to: $day('2026-11-20')), $ours));
        $this->assertSame([$otherPayer, $later], $this->reviewIds(new WorklistFilter(from: $day('2026-11-21')), $ours));
        $this->assertSame([$later], $this->reviewIds(new WorklistFilter(from: $day('2026-11-21'), to: $day('2026-11-21')), $ours));
    }

    public function testTheListStopsAtItsLimitAndStillCountsEveryMatch(): void
    {
        $check = $this->check($this->received('a.edi'), 'CHK1');
        $first = $this->claim($check, 1, 'review', 'denied');
        $this->claim($check, 2, 'review', 'denied');
        $this->claim($check, 3, 'review', 'denied');

        $page = $this->worklist->reviewClaims(new WorklistFilter(payerName: self::PAYER), 1);

        $this->assertSame(3, $page->total);
        $this->assertSame([$first], array_map(static fn(ReviewClaim $claim): int => $claim->id, $page->rows));
    }

    public function testAClaimPaymentIsMarkedWorkedOnceWithWhoAndWhen(): void
    {
        $check = $this->check($this->received('a.edi'), 'CHK1');
        $review = $this->claim($check, 1, 'review', 'denied');
        $posted = $this->claim($check, 2, 'posted');
        $filter = new WorklistFilter(payerName: self::PAYER);
        $at = new \DateTimeImmutable('2026-11-27 09:30:00');
        $open = $this->worklist->openCounts()->review;

        $this->assertTrue($this->worklist->markClaimWorked($review, new WorklistUser(self::USER), $at));
        // Already marked: the first mark stays.
        $this->assertFalse($this->worklist->markClaimWorked($review, new WorklistUser(self::NAMELESS_USER), $at->modify('+1 hour')));
        // Nothing to work on a claim payment that posted.
        $this->assertFalse($this->worklist->markClaimWorked($posted, new WorklistUser(self::USER), $at));

        $this->assertSame($open - 1, $this->worklist->openCounts()->review);
        $this->assertSame([], $this->worklist->reviewClaims($filter, 10)->rows);
        $shown = $this->worklist->reviewClaims(new WorklistFilter(payerName: self::PAYER, showWorked: true), 10)->rows;
        $this->assertCount(1, $shown);
        $mark = $shown[0]->worked;
        $this->assertNotNull($mark);
        $this->assertSame('Billie Biller', $mark->by);
        $this->assertSame('2026-11-27 09:30:00', $mark->at->format('Y-m-d H:i:s'));

        $this->assertTrue($this->worklist->clearClaimWorked($review));
        $this->assertFalse($this->worklist->clearClaimWorked($review));
        $this->assertSame($open, $this->worklist->openCounts()->review);
        $this->assertNull($this->worklist->reviewClaims($filter, 10)->rows[0]->worked);
    }

    public function testAUserWithNoNameOnFileIsShownByUsername(): void
    {
        $review = $this->claim($this->check($this->received('a.edi'), 'CHK1'), 1, 'review', 'denied');

        $this->worklist->markClaimWorked($review, new WorklistUser(self::NAMELESS_USER), new \DateTimeImmutable('2026-11-27 09:30:00'));

        $shown = $this->worklist->reviewClaims(new WorklistFilter(payerName: self::PAYER, showWorked: true), 10)->rows;
        $this->assertSame('worklist' . self::NAMELESS_USER, $shown[0]->worked?->by);
    }

    public function testAPostedCheckIsListedWithItsClaimPaymentsAndTheLinesPostedForThem(): void
    {
        $file = $this->received('a.edi');
        $check = $this->check($file, 'CHK1', checkAmount: 60.0, claimTotal: 70.0, plbTotal: 10.0, plbReasons: 'WO,L6', sessionId: self::SESSION);
        $this->claim($check, 1, 'posted', claimPayment: 45.0, paymentsPosted: 45.0, adjustmentsPosted: 20.0);
        // The payer split the encounter's claim in two: its lines are listed once.
        $this->claim($check, 2, 'review', 'charge_mismatch', claimPayment: 25.0, paymentsPosted: 0.0, adjustmentsPosted: 0.0);
        $this->claim($check, 3, 'skipped', 'already_posted', pid: 0, encounter: 0, claimPayment: 0.0, paymentsPosted: 0.0, adjustmentsPosted: 0.0);
        $this->line(1, '99213', '25', pay: 45.0);
        $this->line(2, '99213', '25', adj: 20.0, memo: 'Adjust code 45');
        $this->line(3, '99213', '25', adj: 5.0, memo: 'voided', deleted: true);
        // Posted to the same encounter under another payment session.
        $this->line(4, '99213', '25', pay: 9.0, session: self::SESSION + 1);
        $this->check($this->received('held.edi'), 'CHK-HELD', status: 'held', holdReason: 'no_payer');

        $page = $this->worklist->postedChecks(new WorklistFilter(payerName: self::PAYER), 10);

        $this->assertSame(1, $page->total);
        $this->assertCount(1, $page->rows);
        $posted = $page->rows[0];
        $this->assertSame($check, $posted->id);
        $this->assertSame(['CHK1', '2026-10-20', self::PAYER, 'a.edi'], [$posted->checkNumber, $posted->checkDate, $posted->payerName, $posted->fileName]);
        $this->assertSame([60.0, 70.0, 10.0, 'WO,L6'], [$posted->checkAmount, $posted->claimTotal, $posted->plbTotal, $posted->plbReasons]);
        $this->assertSame(self::SESSION, $posted->sessionId);
        $this->assertSame(45.0, $posted->postedTotal());
        $this->assertSame(25.0, $posted->notPostedTotal());
        $this->assertSame([1, 1, 1], [$posted->count(ClaimOutcome::Posted), $posted->count(ClaimOutcome::Review), $posted->count(ClaimOutcome::Skipped)]);

        [$first, $second, $third] = $posted->claims;
        $this->assertSame(ClaimOutcome::Posted, $first->outcome);
        $this->assertSame([], $first->reasons);
        $this->assertSame($this->invoiceId(), $first->invoiceId);
        $this->assertSame('ANNE POSTING', $first->patientName);
        $this->assertSame([45.0, 45.0, 20.0], [$first->claimPayment, $first->paymentsPosted, $first->adjustmentsPosted]);
        $this->assertEquals(
            [new PostedLine('99213', '25', 45.0, 0.0, ''), new PostedLine('99213', '25', 0.0, 20.0, 'Adjust code 45')],
            $first->lines
        );
        $this->assertSame(ClaimOutcome::Review, $second->outcome);
        $this->assertSame(['charge_mismatch'], $second->reasons);
        $this->assertSame([], $second->lines);
        $this->assertSame(ClaimOutcome::Skipped, $third->outcome);
        $this->assertSame([0, 0, 0], [$third->pid, $third->encounter, $third->invoiceId]);
        $this->assertSame([], $third->lines);
    }

    public function testPostedChecksAreNarrowedByCheckNumberAndListedNewestFirst(): void
    {
        $first = $this->check($this->received('a.edi'), 'EFT1001', processedAt: '2026-11-20 08:00:00');
        $second = $this->check($this->received('b.edi'), 'EFT2002', processedAt: '2026-11-21 08:00:00');

        $this->assertSame([$second, $first], $this->checkIds(new WorklistFilter(payerName: self::PAYER)));
        $this->assertSame([$first], $this->checkIds(new WorklistFilter(payerName: self::PAYER, checkNumber: '1001')));
        $this->assertSame([], $this->worklist->postedChecks(new WorklistFilter(payerName: 'WORKLIST NO SUCH PAYER'), 10)->rows);
    }

    public function testFilesUnattendedPostingDidNotFinishOrHeldACheckOfAreListed(): void
    {
        $off = $this->received('off.edi', autoPostStatus: '', receivedAt: '2026-11-19 10:00:00');
        $pending = $this->received('pending.edi', autoPostStatus: 'pending', receivedAt: '2026-11-19 11:00:00');
        $unreadable = $this->received('unreadable.edi', autoPostStatus: 'unreadable', receivedAt: '2026-11-19 12:00:00');
        $held = $this->received('held.edi', receivedAt: '2026-11-19 13:00:00');
        $heldCheck = $this->check($held, 'CHK-HELD', status: 'held', holdReason: 'out_of_balance', checkAmount: 60.0, claimTotal: 70.0, plbTotal: 5.0);
        $this->claim($heldCheck, 1, 'review', 'check_held', pid: 0, encounter: 0);
        $this->claim($heldCheck, 2, 'review', 'check_held', pid: 0, encounter: 0);
        $this->check($held, 'CHK-OK', sequence: 2);
        // Posted in full, a claim status file, and a second copy of a stored file: nothing to do.
        $done = $this->received('done.edi');
        $this->check($done, 'CHK-DONE');
        $status = $this->received('status.277', type: 'f277', autoPostStatus: '');
        $duplicate = $this->received('dup.edi', status: 'duplicate', autoPostStatus: '');
        $ours = [$off, $pending, $unreadable, $held, $done, $status, $duplicate];

        $files = $this->files(new WorklistFilter(), $ours);

        $this->assertSame([$held, $unreadable, $pending, $off], array_map(static fn(ReceivedFile $file): int => $file->id, $files));
        [$heldFile, $unreadableFile, , $offFile] = $files;
        $this->assertSame(AutoPostFileStatus::Done, $heldFile->autoPostStatus);
        $this->assertCount(1, $heldFile->heldChecks);
        $heldRow = $heldFile->heldChecks[0];
        $this->assertSame(['CHK-HELD', '2026-10-20', self::PAYER], [$heldRow->checkNumber, $heldRow->checkDate, $heldRow->payerName]);
        $this->assertSame([60.0, 70.0, 5.0], [$heldRow->checkAmount, $heldRow->claimTotal, $heldRow->plbTotal]);
        $this->assertSame(HoldReason::OutOfBalance, $heldRow->reason);
        $this->assertSame(2, $heldRow->claims);
        $this->assertSame(AutoPostFileStatus::Unreadable, $unreadableFile->autoPostStatus);
        $this->assertSame([], $unreadableFile->heldChecks);
        $this->assertNull($offFile->autoPostStatus);
        $this->assertSame('Worklist Clearinghouse', $offFile->partnerName);
        $this->assertSame(['off.edi.RMT', 'off.edi', 'off'], [$offFile->remoteName, $offFile->storedName, $offFile->eraName()]);
        $this->assertSame('2026-11-19 10:00:00', $offFile->receivedAt->format('Y-m-d H:i:s'));

        $day = new \DateTimeImmutable('2026-11-19');
        $this->assertCount(4, $this->files(new WorklistFilter(from: $day, to: $day), $ours));
        $this->assertSame([], $this->files(new WorklistFilter(from: $day->modify('+1 day')), $ours));
        $this->assertSame([], $this->files(new WorklistFilter(to: $day->modify('-1 day')), $ours));
    }

    public function testAFileIsMarkedWorkedOnceAndOnlyWhileItIsOnTheWorklist(): void
    {
        $off = $this->received('off.edi', autoPostStatus: '');
        $done = $this->received('done.edi');
        $by = new WorklistUser(self::USER);
        $at = new \DateTimeImmutable('2026-11-27 09:30:00');
        $open = $this->worklist->openCounts()->files;

        $this->assertTrue($this->worklist->markFileWorked($off, $by, $at));
        $this->assertFalse($this->worklist->markFileWorked($off, $by, $at));
        $this->assertFalse($this->worklist->markFileWorked($done, $by, $at));

        $this->assertSame($open - 1, $this->worklist->openCounts()->files);
        $this->assertSame([], $this->files(new WorklistFilter(), [$off, $done]));
        $shown = $this->files(new WorklistFilter(showWorked: true), [$off, $done]);
        $this->assertCount(1, $shown);
        $mark = $shown[0]->worked;
        $this->assertNotNull($mark);
        $this->assertSame('Billie Biller', $mark->by);
        $this->assertSame('2026-11-27 09:30:00', $mark->at->format('Y-m-d H:i:s'));

        $this->assertTrue($this->worklist->clearFileWorked($off));
        $this->assertFalse($this->worklist->clearFileWorked($off));
        $this->assertSame($open, $this->worklist->openCounts()->files);
    }

    public function testAFileAPostingRunHasNotFinishedIsTakenFromTheRunBeforeItIsWorked(): void
    {
        $pending = $this->received('pending.edi', autoPostStatus: 'pending');
        $running = $this->received('running.edi', autoPostStatus: 'running', receivedAt: '2026-11-19 11:00:00');
        $off = $this->received('off.edi', autoPostStatus: '', receivedAt: '2026-11-19 12:00:00');
        $status = $this->received('status.277', type: 'f277', autoPostStatus: 'pending');
        $by = new WorklistUser(self::USER);
        $at = new \DateTimeImmutable('2026-11-27 09:30:00');

        // What the run finds is still to come: a mark now would hide it.
        $this->assertFalse($this->worklist->markFileWorked($pending, $by, $at));
        $this->assertFalse($this->worklist->markFileWorked($running, $by, $at));

        // Only from the state the person saw: a run that took the file in between keeps it.
        $this->assertFalse($this->worklist->takeFromUnattended($pending, AutoPostFileStatus::Running));
        $this->assertFalse($this->worklist->takeFromUnattended($running, AutoPostFileStatus::Pending));
        $this->assertFalse($this->worklist->takeFromUnattended($off, AutoPostFileStatus::Pending));
        $this->assertFalse($this->worklist->takeFromUnattended($status, AutoPostFileStatus::Pending));
        foreach ([AutoPostFileStatus::Done, AutoPostFileStatus::Unreadable, AutoPostFileStatus::Interrupted] as $notTaken) {
            $this->assertFalse($this->worklist->takeFromUnattended($running, $notTaken));
        }

        $this->assertTrue($this->worklist->takeFromUnattended($pending, AutoPostFileStatus::Pending));
        $this->assertFalse($this->worklist->takeFromUnattended($pending, AutoPostFileStatus::Pending));
        $this->assertTrue($this->worklist->takeFromUnattended($running, AutoPostFileStatus::Running));

        [$runningFile, $pendingFile] = $this->files(new WorklistFilter(), [$pending, $running]);
        // No longer for unattended posting, so no run takes it.
        $this->assertNull($pendingFile->autoPostStatus);
        $this->assertFalse($pendingFile->awaitsRun());
        $this->assertSame(AutoPostFileStatus::Interrupted, $runningFile->autoPostStatus);
        $this->assertFalse($runningFile->awaitsRun());
        $this->assertTrue($this->worklist->markFileWorked($pending, $by, $at));
        $this->assertTrue($this->worklist->markFileWorked($running, $by, $at));
    }

    public function testAMarkDoesNotOutlastAPostingRunThatWasStillGoing(): void
    {
        // The file was taken from a run that had not stopped after all, and marked worked.
        $file = $this->received('running.edi', autoPostStatus: 'running');
        $this->assertTrue($this->worklist->takeFromUnattended($file, AutoPostFileStatus::Running));
        $this->assertTrue($this->worklist->markFileWorked($file, new WorklistUser(self::USER), new \DateTimeImmutable('2026-11-27 09:30:00')));
        $open = $this->worklist->openCounts()->files;

        // The run then holds a check of the file and finishes it.
        $this->check($file, 'CHK-HELD', status: 'held', holdReason: 'out_of_balance');
        (new AutoPostRepository())->finish($file, AutoPostFileStatus::Done);

        $files = $this->files(new WorklistFilter(), [$file]);
        $this->assertCount(1, $files);
        $this->assertNull($files[0]->worked);
        $this->assertSame(AutoPostFileStatus::Done, $files[0]->autoPostStatus);
        $this->assertCount(1, $files[0]->heldChecks);
        $this->assertSame($open + 1, $this->worklist->openCounts()->files);
    }

    public function testThePayersOfTheChecksAreListedOnceEachInOrder(): void
    {
        $this->check($this->received('a.edi'), 'CHK1');
        $this->check($this->received('b.edi'), 'CHK2');
        $this->check($this->received('c.edi'), 'CHK3', payer: self::OTHER_PAYER);
        $this->check($this->received('d.edi'), 'CHK4', payer: '');

        $ours = array_values(array_filter(
            $this->worklist->payerNames(),
            static fn(string $name): bool => str_starts_with($name, 'WORKLIST ')
        ));

        $this->assertSame([self::OTHER_PAYER, self::PAYER], $ours);
        $this->assertNotContains('', $this->worklist->payerNames());
    }

    /**
     * @return list<int> the ids of the posted checks listed
     */
    private function checkIds(WorklistFilter $filter): array
    {
        return array_map(static fn(PostedCheck $check): int => $check->id, $this->worklist->postedChecks($filter, 10)->rows);
    }

    /**
     * @param list<int> $ours
     * @return list<int> the ids listed, of the claim payments this test made
     */
    private function reviewIds(WorklistFilter $filter, array $ours): array
    {
        $ids = array_map(static fn(ReviewClaim $claim): int => $claim->id, $this->worklist->reviewClaims($filter, 500)->rows);
        return array_values(array_filter($ids, static fn(int $id): bool => in_array($id, $ours, true)));
    }

    /**
     * @param list<int> $ours
     * @return list<ReceivedFile> the files listed, of those this test made
     */
    private function files(WorklistFilter $filter, array $ours): array
    {
        return array_values(array_filter(
            $this->worklist->receivedFiles($filter, 500)->rows,
            static fn(ReceivedFile $file): bool => in_array($file->id, $ours, true)
        ));
    }

    private function invoiceId(): int
    {
        $id = QueryUtils::fetchSingleValue('SELECT `id` FROM `form_encounter` WHERE `pid` = ? AND `encounter` = ?', 'id', [self::PID, self::ENC]);
        return is_numeric($id) ? (int) $id : 0;
    }

    private function received(
        string $name,
        string $type = 'f835',
        string $status = 'stored',
        string $autoPostStatus = 'done',
        string $receivedAt = '2026-11-19 10:00:00',
    ): int {
        return QueryUtils::sqlInsert(
            'INSERT INTO `x12_remote_received` (`remote_key`, `x12_partner_id`, `remote_dir`, `remote_name`, `remote_size`,'
            . ' `file_type`, `status`, `stored_name`, `received_at`, `auto_post_status`) VALUES (?, ?, ?, ?, 1, ?, ?, ?, ?, ?)',
            [hash('sha256', self::PARTNER . $name), self::PARTNER, '/remits', $name . '.RMT', $type, $status, $name, $receivedAt, $autoPostStatus]
        );
    }

    private function check(
        int $receivedId,
        string $number,
        string $status = 'posted',
        string $holdReason = '',
        string $processedAt = '2026-11-20 08:00:00',
        string $checkDate = '20261020',
        string $payer = self::PAYER,
        float $checkAmount = 70.0,
        float $claimTotal = 70.0,
        float $plbTotal = 0.0,
        string $plbReasons = '',
        int $sessionId = 0,
        int $sequence = 1,
    ): int {
        return QueryUtils::sqlInsert(
            'INSERT INTO `era_auto_post_check` (`received_id`, `sequence`, `check_number`, `check_date`, `payer_name`,'
            . ' `check_amount`, `claim_total`, `plb_total`, `plb_reasons`, `status`, `hold_reason`, `session_id`,'
            . ' `processed_at`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
            [$receivedId, $sequence, $number, $checkDate, $payer, $checkAmount, $claimTotal, $plbTotal, $plbReasons, $status, $holdReason, $sessionId, $processedAt]
        );
    }

    private function claim(
        int $checkId,
        int $sequence,
        string $outcome,
        string $reasons = '',
        int $pid = self::PID,
        int $encounter = self::ENC,
        float $claimPayment = 70.0,
        float $paymentsPosted = 12.0,
        float $adjustmentsPosted = 3.0,
    ): int {
        return QueryUtils::sqlInsert(
            'INSERT INTO `era_auto_post_claim` (`check_id`, `sequence`, `our_claim_id`, `payer_claim_id`, `patient_name`,'
            . ' `pid`, `encounter`, `payer_level`, `claim_status_code`, `outcome`, `reasons`, `claim_payment`,'
            . ' `payments_posted`, `adjustments_posted`) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?)',
            [
                $checkId,
                $sequence,
                self::PID . '-' . self::ENC,
                'ICN' . $sequence,
                'ANNE POSTING',
                $pid,
                $encounter,
                str_contains($reasons, 'denied') ? '4' : '1',
                $outcome,
                $reasons,
                $claimPayment,
                $paymentsPosted,
                $adjustmentsPosted,
            ]
        );
    }

    private function line(
        int $sequence,
        string $code,
        string $modifier,
        float $pay = 0.0,
        float $adj = 0.0,
        string $memo = '',
        bool $deleted = false,
        int $session = self::SESSION,
    ): void {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `ar_activity` (`pid`, `encounter`, `sequence_no`, `code_type`, `code`, `modifier`, `payer_type`,'
            . ' `post_time`, `post_user`, `session_id`, `memo`, `pay_amount`, `adj_amount`, `modified_time`, `follow_up`,'
            . " `account_code`, `deleted`) VALUES (?, ?, ?, 'CPT4', ?, ?, 1, NOW(), ?, ?, ?, ?, ?, NOW(), '', '', ?)",
            [self::PID, self::ENC, $sequence, $code, $modifier, self::USER, $session, $memo, $pay, $adj, $deleted ? '2026-11-21 00:00:00' : null]
        );
    }

    private function cleanUp(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE c FROM `era_auto_post_claim` AS c JOIN `era_auto_post_check` AS k ON k.`id` = c.`check_id`'
            . ' JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id` WHERE r.`x12_partner_id` = ?',
            [self::PARTNER]
        );
        QueryUtils::sqlStatementThrowException(
            'DELETE k FROM `era_auto_post_check` AS k JOIN `x12_remote_received` AS r ON r.`id` = k.`received_id`'
            . ' WHERE r.`x12_partner_id` = ?',
            [self::PARTNER]
        );
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_remote_received` WHERE `x12_partner_id` = ?', [self::PARTNER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `x12_partners` WHERE `id` = ?', [self::PARTNER]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `ar_activity` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `form_encounter` WHERE `pid` = ?', [self::PID]);
        QueryUtils::sqlStatementThrowException('DELETE FROM `users` WHERE `id` IN (?, ?)', [self::USER, self::NAMELESS_USER]);
    }
}
