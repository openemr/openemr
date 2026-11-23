<?php

/**
 * Tests for ClaimRecord: the stored outcome and reasons for a posting result.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Billing\EraAutoPost;

use OpenEMR\Billing\EraAutoPost\ClaimOutcome;
use OpenEMR\Billing\EraAutoPost\ClaimRecord;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use OpenEMR\Billing\EraPosting\ClaimPayment;
use OpenEMR\Billing\EraPosting\ClaimPostingResult;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ClaimRecordTest extends TestCase
{
    /**
     * @param list<ClaimIssue> $issues
     * @param list<string> $reasons
     */
    #[DataProvider('outcomeProvider')]
    public function testOutcomeFollowsTheIssues(array $issues, ClaimOutcome $outcome, array $reasons): void
    {
        $record = ClaimRecord::fromResult(self::claim('1'), self::postingResult($issues));

        $this->assertSame($outcome, $record->outcome);
        $this->assertSame($reasons, $record->reasons);
    }

    /**
     * @return array<string, array{list<ClaimIssue>, ClaimOutcome, list<string>}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function outcomeProvider(): array
    {
        return [
            'nothing wrong' => [[], ClaimOutcome::Posted, []],
            'posted before' => [[ClaimIssue::AlreadyPosted], ClaimOutcome::Skipped, ['already_posted']],
            'not found' => [[ClaimIssue::ClaimNotFound], ClaimOutcome::Review, ['claim_not_found']],
            'denied' => [[ClaimIssue::Denied], ClaimOutcome::Review, ['denied']],
            'reversal' => [[ClaimIssue::Reversal], ClaimOutcome::Review, ['reversal']],
            'pended lines, the rest posted' => [[ClaimIssue::LinesPended], ClaimOutcome::Review, ['lines_pended']],
            'two issues' => [
                [ClaimIssue::ChargeMismatch, ClaimIssue::CodeNotOnInvoice],
                ClaimOutcome::Review,
                ['charge_mismatch', 'code_not_on_invoice'],
            ],
            'unpaid adjustment' => [[ClaimIssue::UnpaidAdjustment], ClaimOutcome::Review, ['unpaid_adjustment']],
        ];
    }

    public function testTheResultsAmountsAndClaimAreCarried(): void
    {
        $record = ClaimRecord::fromResult(self::claim('2'), self::postingResult([]));

        $this->assertSame(41, $record->pid);
        $this->assertSame(141, $record->encounter);
        $this->assertSame(2, $record->level);
        $this->assertSame(70.0, $record->paymentsPosted);
        $this->assertSame(20.0, $record->adjustmentsPosted);
        $this->assertSame(5.0, $record->chargesAdded);
        $this->assertTrue($record->levelClosed);
        $this->assertTrue($record->secondaryQueued);
    }

    public function testAClaimOfAHeldCheckIsForReviewAtTheLevelItsStatusNames(): void
    {
        $record = ClaimRecord::held(self::claim('3'));

        $this->assertSame(ClaimOutcome::Review, $record->outcome);
        $this->assertSame([ClaimRecord::CHECK_HELD], $record->reasons);
        $this->assertSame(0, $record->pid);
        $this->assertSame(3, $record->level);
        $this->assertSame(0.0, $record->paymentsPosted);
        $this->assertFalse($record->levelClosed);
    }

    private static function claim(string $status): ClaimPayment
    {
        return new ClaimPayment('CHK1', '20261020', '20261019', '41-141', $status, 'ICN1', '20260915', 'ANNE', 'POSTING', '', 0, [], 70.0);
    }

    /**
     * @param list<ClaimIssue> $issues
     */
    private static function postingResult(array $issues): ClaimPostingResult
    {
        return new ClaimPostingResult(41, 141, '41.141', 2, '2', 7, $issues, 70.0, 20.0, 5.0, true, true, []);
    }
}
