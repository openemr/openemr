<?php

/**
 * Where a referral stands: ReferralStatus (#14710).
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services;

use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\ReferralStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ReferralStatusTest extends TestCase
{
    private const TODAY = '2026-10-19';

    protected function setUp(): void
    {
        // labels are translated, and xl() reaches for the translation tables unless this is set
        OEGlobalsBag::getInstance()->set('disable_translation', true);
    }

    /**
     * @return array<string, array{?string, ?string, ?string, int, string, string, bool}>
     */
    public static function referrals(): array
    {
        // task status, reply date, referral date, overdue days => state, label, overdue
        return [
            'no task, no reply, recent' => [null, null, '2026-10-10', 14, ReferralStatus::OPEN, 'Open', false],
            'no task, no reply, 14 days old' => [null, null, '2026-10-05', 14, ReferralStatus::OPEN, 'Open', true],
            'no task, no reply, 13 days old' => [null, null, '2026-10-06', 14, ReferralStatus::OPEN, 'Open', false],
            'overdue turned off' => [null, null, '2026-01-01', 0, ReferralStatus::OPEN, 'Open', false],
            'blank values count as none' => ['', ' ', '2026-01-01', 14, ReferralStatus::OPEN, 'Open', true],
            'no referral date' => [null, null, null, 14, ReferralStatus::OPEN, 'Open', false],
            'a referral date that is not a date' => [null, null, 'soon', 14, ReferralStatus::OPEN, 'Open', false],
            'a referral date with spaces around it' => [null, null, ' 2026-01-05 ', 14, ReferralStatus::OPEN, 'Open', true],
            'a referral date with a time' => [null, null, '2026-01-05 09:00:00', 14, ReferralStatus::OPEN, 'Open', true],
            'a zero referral date' => [null, null, '0000-00-00', 14, ReferralStatus::OPEN, 'Open', false],
            'a referral date that does not exist' => [null, null, '2026-02-30', 14, ReferralStatus::OPEN, 'Open', false],
            'replied' => [null, '2026-10-12', '2026-01-01', 14, ReferralStatus::COMPLETED, 'Replied', false],
            'replied while the task is accepted' => ['accepted', '2026-10-12', '2026-01-01', 14, ReferralStatus::COMPLETED, 'Replied', false],
            'task requested' => ['requested', null, '2026-10-10', 14, ReferralStatus::OPEN, 'Requested', false],
            'task accepted, old' => ['accepted', null, '2026-01-01', 14, ReferralStatus::OPEN, 'Accepted', true],
            'task in progress' => ['in-progress', null, '2026-10-10', 14, ReferralStatus::OPEN, 'In Progress', false],
            'task rejected, still to act on' => ['rejected', null, '2026-01-01', 14, ReferralStatus::OPEN, 'Rejected', true],
            'task completed' => ['completed', null, '2026-01-01', 14, ReferralStatus::COMPLETED, 'Completed', false],
            'task completed and replied' => ['completed', '2026-10-12', '2026-01-01', 14, ReferralStatus::COMPLETED, 'Completed', false],
            'task cancelled' => ['cancelled', null, '2026-01-01', 14, ReferralStatus::CLOSED, 'Cancelled', false],
            'task entered in error' => ['entered-in-error', null, '2026-01-01', 14, ReferralStatus::CLOSED, 'Entered in Error', false],
        ];
    }

    #[DataProvider('referrals')]
    public function testWhereAReferralStands(?string $taskStatus, ?string $replyDate, ?string $referDate, int $overdueDays, string $state, string $label, bool $overdue): void
    {
        $status = ReferralStatus::of($taskStatus, $replyDate, $referDate, new \DateTimeImmutable(self::TODAY . ' 15:30:00'), $overdueDays);

        $this->assertSame($state, $status->state);
        $this->assertSame($label, $status->label());
        $this->assertSame($overdue, $status->overdue);
        $this->assertSame($state === ReferralStatus::OPEN, $status->isOpen());
    }

    public function testAReferralRecordIsReadByItsColumns(): void
    {
        $status = ReferralStatus::ofReferral(['task_status' => 'accepted', 'reply_date' => null, 'refer_date' => '2026-01-01', 'body' => 'x'], new \DateTimeImmutable(self::TODAY), 14);

        $this->assertSame('Accepted', $status->label());
        $this->assertTrue($status->overdue);
    }

    /**
     * A transaction as getTransByPid() lists it carries its form's fields; the Task status comes
     * from the list's one lookup.
     */
    public function testATransactionHasAStatusOnlyWhenItIsAReferral(): void
    {
        $statuses = [7 => 'requested', 9 => null];
        $today = new \DateTimeImmutable(self::TODAY);

        $this->assertSame('Requested', ReferralStatus::ofTransaction(['id' => '7', 'title' => 'LBTref', 'refer_date' => '2026-10-10'], $statuses, $today, 14)?->label());
        $replied = ReferralStatus::ofTransaction(['id' => '9', 'title' => 'LBTref', 'refer_date' => '2026-01-01', 'reply_date' => '2026-01-09'], $statuses, $today, 14);
        $this->assertSame('Replied', $replied?->label(), 'a referral without a Task');
        $overdue = ReferralStatus::ofTransaction(['id' => '11', 'title' => 'LBTref', 'refer_date' => '2026-01-01'], $statuses, $today, 14);
        $this->assertNotNull($overdue, 'a referral the lookup did not return');
        $this->assertSame('Open', $overdue->label());
        $this->assertTrue($overdue->overdue);
        $this->assertNull(ReferralStatus::ofTransaction(['id' => '8', 'title' => 'LBTptreq'], $statuses, $today, 14));
        $this->assertNull(ReferralStatus::ofTransaction(['title' => 'LBTref'], $statuses, $today, 14));
        $this->assertNull(ReferralStatus::ofTransaction(null, $statuses, $today, 14));
        $taskless = ReferralStatus::ofTransaction(['id' => '7', 'title' => 'LBTref', 'task_status' => 'completed'], [], $today, 14);
        $this->assertSame('Open', $taskless?->label(), 'the lookup decides the Task status, not a field of that name');
    }

    public function testTheOverdueDaysGlobalIsReadLeniently(): void
    {
        $bag = OEGlobalsBag::getInstance();
        $before = $bag->getInt('referral_overdue_days', 14);
        try {
            foreach (['30' => 30, '0' => 0, '' => 14, 'two weeks' => 14, '-5' => 0] as $value => $days) {
                $bag->set('referral_overdue_days', (string)$value);
                $this->assertSame($days, ReferralStatus::overdueDays(), "for '$value'");
            }
        } finally {
            $bag->set('referral_overdue_days', $before);
        }
    }
}
