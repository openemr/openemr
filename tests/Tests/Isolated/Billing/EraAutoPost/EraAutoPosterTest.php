<?php

/**
 * Tests for EraAutoPoster with an in-memory log and check poster: which checks post, which are held, and what is recorded.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\AutoPostFileStatus;
use OpenEMR\Billing\EraAutoPost\AutoPostLog;
use OpenEMR\Billing\EraAutoPost\CheckPoster;
use OpenEMR\Billing\EraAutoPost\CheckStatus;
use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\ClaimRecord;
use OpenEMR\Billing\EraAutoPost\EraAutoPoster;
use OpenEMR\Billing\EraAutoPost\HoldReason;
use OpenEMR\Billing\EraAutoPost\PendingRemittance;
use OpenEMR\Billing\EraAutoPost\RemittanceReader;
use PHPUnit\Framework\TestCase;
use Psr\Clock\ClockInterface;
use Psr\Log\NullLogger;

class EraAutoPosterTest extends TestCase
{
    private string $directory = '';

    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir() . '/era_auto_' . bin2hex(random_bytes(6));
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        array_map(unlink(...), glob($this->directory . '/*') ?: []);
        rmdir($this->directory);
    }

    public function testABalancedCheckIsPostedAndRecorded(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(
            Era835::check('CHK1', '150.00', [['10-20', '1', '70.00'], ['11-21', '1', '80.00']]),
        ));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster();

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([['CHK1', '77', 9]], $poster->posted);
        $this->assertSame([5 => AutoPostFileStatus::Done], $log->finished);
        $this->assertCount(1, $log->records);
        [$receivedId, $sequence, $record, $at] = $log->records[0];
        $this->assertSame(5, $receivedId);
        $this->assertSame(1, $sequence);
        $this->assertSame('2026-11-19 12:00:00', $at->format('Y-m-d H:i:s'));
        $this->assertSame(CheckStatus::Posted, $record->status);
        $this->assertNull($record->holdReason);
        $this->assertSame('77', $record->payerId);
        $this->assertSame(300, $record->sessionId);
        $this->assertSame(
            [ClaimOutcome::Posted, ClaimOutcome::Posted],
            array_map(static fn(ClaimRecord $claim): ClaimOutcome => $claim->outcome, $record->claims)
        );
        $this->assertSame(
            ['files' => 1, 'filesUnreadable' => 0, 'filesInterrupted' => 0, 'checksPosted' => 1, 'checksHeld' => 0,
                'claimsPosted' => 2, 'claimsForReview' => 0, 'claimsSkipped' => 0],
            $summary->toArray()
        );
    }

    public function testACheckOutOfBalanceIsHeldWholeAndTheNextCheckStillPosts(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(
            Era835::check('SHORT', '140.00', [['10-20', '1', '70.00'], ['11-21', '1', '80.00']]),
            Era835::check('GOOD', '25.00', [['12-22', '1', '25.00']], control: '0002'),
        ));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster();

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([['GOOD', '77', 9]], $poster->posted);
        $held = $log->records[0][2];
        $this->assertSame(CheckStatus::Held, $held->status);
        $this->assertSame(HoldReason::OutOfBalance, $held->holdReason);
        $this->assertSame(0, $held->sessionId);
        $this->assertSame('', $held->payerId);
        $this->assertSame(
            [[ClaimRecord::CHECK_HELD], [ClaimRecord::CHECK_HELD]],
            array_map(static fn(ClaimRecord $claim): array => $claim->reasons, $held->claims)
        );
        $this->assertSame(2, $log->records[1][1]);
        $this->assertSame(CheckStatus::Posted, $log->records[1][2]->status);
        $this->assertSame(1, $summary->checksHeld);
        $this->assertSame(1, $summary->checksPosted);
        $this->assertSame(2, $summary->claimsForReview);
        $this->assertSame(1, $summary->claimsPosted);
    }

    public function testACheckBalancedByAProviderLevelAdjustmentIsPosted(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(
            Era835::check('CHK1', '60.00', [['10-20', '1', '70.00']], ['WO*10.00']),
        ));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster();

        $this->autoPoster($log, $poster)->run();

        $record = $log->records[0][2];
        $this->assertSame(CheckStatus::Posted, $record->status);
        $this->assertSame(10.0, $record->check->plbTotal);
        $this->assertSame(['WO'], $record->check->plbReasons);
    }

    public function testACheckWithNoPayerIsHeld(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']])));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster(payer: '');

        $this->autoPoster($log, $poster)->run();

        $this->assertSame([], $poster->posted);
        $this->assertSame(HoldReason::NoPayer, $log->records[0][2]->holdReason);
        $this->assertSame([5 => AutoPostFileStatus::Done], $log->finished);
    }

    public function testACheckPostedForAnotherPayerIsHeld(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']])));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster(postedElsewhere: true);

        $this->autoPoster($log, $poster)->run();

        $this->assertSame([], $poster->posted);
        $record = $log->records[0][2];
        $this->assertSame(HoldReason::PostedForAnotherPayer, $record->holdReason);
        $this->assertSame('77', $record->payerId);
    }

    public function testAFileThatCannotBeParsedOrIsMissingPostsNothing(): void
    {
        $whole = Era835::file(Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']]));
        Era835::write($this->directory, 'cut.edi', substr($whole, 0, (int) strpos($whole, 'GE*')));
        $log = $this->log([new PendingRemittance(5, 'cut.edi', 9), new PendingRemittance(6, 'gone.edi', 9)]);
        $poster = $this->poster();

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([], $poster->posted);
        $this->assertSame([], $log->records);
        $this->assertSame([5 => AutoPostFileStatus::Unreadable, 6 => AutoPostFileStatus::Unreadable], $log->finished);
        $this->assertSame(2, $summary->filesUnreadable);
    }

    public function testAFileAnotherRunTookIsLeftAlone(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(Era835::check('CHK1', '70.00', [['10-20', '1', '70.00']])));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $log->taken[5] = true;
        $poster = $this->poster();

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([], $poster->posted);
        $this->assertSame([], $log->finished);
        $this->assertSame(0, $summary->files);
    }

    public function testAnErrorPartWayLeavesTheFileInterruptedAndTheNextFileStillPosts(): void
    {
        Era835::write($this->directory, 'a.edi', Era835::file(Era835::check('BOOM', '70.00', [['10-20', '1', '70.00']])));
        Era835::write($this->directory, 'b.edi', Era835::file(Era835::check('CHK2', '25.00', [['12-22', '1', '25.00']])));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9), new PendingRemittance(6, 'b.edi', 9)]);
        $poster = $this->poster(failOn: 'BOOM');

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([5 => AutoPostFileStatus::Interrupted, 6 => AutoPostFileStatus::Done], $log->finished);
        $this->assertSame([['CHK2', '77', 9]], $poster->posted);
        $this->assertSame(1, $summary->filesInterrupted);
        $this->assertSame(2, $summary->files);
    }

    public function testACheckWithOnlyProviderLevelAdjustmentsIsRecordedAndHeld(): void
    {
        // The payer paid 10.00 of interest and no claims.
        Era835::write($this->directory, 'a.edi', Era835::file(Era835::check('PLBONLY', '10.00', [], ['L6*-10.00'])));
        $log = $this->log([new PendingRemittance(5, 'a.edi', 9)]);
        $poster = $this->poster(payer: '');

        $summary = $this->autoPoster($log, $poster)->run();

        $this->assertSame([], $poster->posted);
        $this->assertCount(1, $log->records);
        $record = $log->records[0][2];
        $this->assertSame('PLBONLY', $record->check->checkNumber);
        $this->assertSame(-10.0, $record->check->plbTotal);
        $this->assertSame(CheckStatus::Held, $record->status);
        $this->assertSame(HoldReason::NoPayer, $record->holdReason);
        $this->assertSame([], $record->claims);
        $this->assertSame(1, $summary->checksHeld);
        $this->assertSame([5 => AutoPostFileStatus::Done], $log->finished);
    }

    private function autoPoster(AutoPostLog $log, CheckPoster $poster): EraAutoPoster
    {
        $clock = new class implements ClockInterface {
            public function now(): \DateTimeImmutable
            {
                return new \DateTimeImmutable('2026-11-19 12:00:00');
            }
        };
        return new EraAutoPoster($log, new RemittanceReader(), $poster, $clock, new NullLogger(), $this->directory);
    }

    /**
     * @param list<PendingRemittance> $pending
     */
    private function log(array $pending): InMemoryAutoPostLog
    {
        return new InMemoryAutoPostLog($pending);
    }

    private function poster(string $payer = '77', bool $postedElsewhere = false, string $failOn = ''): RecordingCheckPoster
    {
        return new RecordingCheckPoster($payer, $postedElsewhere, $failOn);
    }
}
