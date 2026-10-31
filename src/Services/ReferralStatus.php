<?php

/**
 * Where a referral stands (#14710). The Task that tracks it decides, when it has one: its
 * status is the referral's. A Reply Date also completes it, since the Counter-Referral
 * section records the answer. A referral is open until it is completed, or its Task is
 * cancelled or entered in error, and overdue while open a set number of days after its
 * Referral Date.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\Core\OEGlobalsBag;

final readonly class ReferralStatus
{
    public const OPEN = 'open';
    public const COMPLETED = 'completed';
    public const CLOSED = 'closed';

    /**
     * The Transactions form a referral is saved with: ReferralService::FORM_ID, repeated so that
     * this class and the pages' pieces load without ReferralService, whose parent BaseService
     * reads code_types from the database as its file loads.
     */
    public const FORM_ID = 'LBTref';

    /** Task statuses after which nothing more is expected of the recipient. */
    private const CLOSING_TASK_STATUSES = ['cancelled', 'entered-in-error'];

    /**
     * @param self::OPEN|self::COMPLETED|self::CLOSED $state
     */
    private function __construct(
        public string $state,
        public ?string $taskStatus,
        public ?string $replyDate,
        public bool $overdue,
    ) {
    }

    /**
     * @param ?string $taskStatus the status of the referral's latest Task, if it has one
     * @param ?string $replyDate  Reply Date, as stored (Y-m-d)
     * @param ?string $referDate  Referral Date, as stored (Y-m-d)
     * @param int     $overdueDays days after the Referral Date an open referral is overdue; 0 for never
     */
    public static function of(?string $taskStatus, ?string $replyDate, ?string $referDate, \DateTimeImmutable $today, int $overdueDays): self
    {
        $taskStatus = self::value($taskStatus);
        $replyDate = self::value($replyDate);
        $state = match (true) {
            $replyDate !== null || $taskStatus === 'completed' => self::COMPLETED,
            in_array($taskStatus, self::CLOSING_TASK_STATUSES, true) => self::CLOSED,
            default => self::OPEN,
        };
        $referDate = self::value($referDate);
        $referred = $referDate === null ? false : self::date(substr($referDate, 0, 10));
        $overdue = $state === self::OPEN && $overdueDays > 0 && $referred !== false
            && $referred <= $today->setTime(0, 0)->modify("-$overdueDays days");
        return new self($state, $taskStatus, $replyDate, $overdue);
    }

    /**
     * For a ReferralService record, or any row with its task_status, reply_date and
     * refer_date columns.
     *
     * @param array<mixed> $referral
     */
    public static function ofReferral(array $referral, \DateTimeImmutable $today, int $overdueDays): self
    {
        $text = static fn(mixed $value): ?string => is_string($value) ? $value : null;
        return self::of($text($referral['task_status'] ?? null), $text($referral['reply_date'] ?? null), $text($referral['refer_date'] ?? null), $today, $overdueDays);
    }

    /**
     * For a transaction as getTransByPid() lists it, with its form's fields: a referral's status,
     * or null for any other transaction type.
     *
     * @param array<int, ?string> $taskStatuses referrals' Task statuses by transaction id, as ReferralService::getTaskStatuses() finds them
     */
    public static function ofTransaction(mixed $transaction, array $taskStatuses, \DateTimeImmutable $today, int $overdueDays): ?self
    {
        if (!is_array($transaction) || ($transaction['title'] ?? null) !== self::FORM_ID || !is_numeric($transaction['id'] ?? null)) {
            return null;
        }
        return self::ofReferral(['task_status' => $taskStatuses[(int)$transaction['id']] ?? null] + $transaction, $today, $overdueDays);
    }

    /**
     * The referral_overdue_days global, 14 by default. It is typed in by hand, and
     * OEGlobalsBag::getInt() gives the default for anything that isn't a whole number.
     */
    public static function overdueDays(): int
    {
        return max(0, OEGlobalsBag::getInstance()->getInt('referral_overdue_days', 14));
    }

    public function isOpen(): bool
    {
        return $this->state === self::OPEN;
    }

    /**
     * What to show: Replied once a Reply Date is recorded (unless the Task says Completed),
     * else the Task's status, else Open.
     */
    public function label(): string
    {
        if ($this->replyDate !== null && $this->taskStatus !== 'completed') {
            return xl('Replied');
        }
        return match ($this->taskStatus) {
            null => xl('Open'),
            'draft' => xl('Draft'),
            'requested' => xl('Requested'),
            'received' => xl('Received'),
            'accepted' => xl('Accepted'),
            'rejected' => xl('Rejected'),
            'ready' => xl('Ready'),
            'cancelled' => xl('Cancelled'),
            'in-progress' => xl('In Progress'),
            'on-hold' => xl('On Hold'),
            'failed' => xl('Failed'),
            'completed' => xl('Completed'),
            'entered-in-error' => xl('Entered in Error'),
            default => $this->taskStatus,
        };
    }

    /**
     * A Y-m-d date that exists, or false: createFromFormat() would roll '0000-00-00' or
     * '2026-02-30' over into another date, which a free-text layout field can hold.
     */
    private static function date(string $value): \DateTimeImmutable|false
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        return $date !== false && $date->format('Y-m-d') === $value ? $date : false;
    }

    private static function value(?string $value): ?string
    {
        return $value === null || trim($value) === '' ? null : trim($value);
    }
}
