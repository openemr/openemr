<?php

/**
 * Where referrals stand (#14710), as the referral pages show it: the status on a saved
 * referral's page, the Status column of the patient's Transactions list and of the Referrals
 * report, and that report's filters.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    anun333 <anun333@posteo.net>
 * @copyright Copyright (c) 2026 anun333 <anun333@posteo.net>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services;

use OpenEMR\BC\ServiceContainer;
use Psr\Clock\ClockInterface;
use Twig\Environment;

final class ReferralDisplay
{
    /** The Referrals report's Show choices besides All (''). */
    public const SHOW_OPEN = 'open';
    public const SHOW_OVERDUE = 'overdue';

    private readonly \DateTimeImmutable $now;

    /**
     * The referral lookups are closures, not a ReferralService, so the isolated tests can stand in
     * for them: loading BaseService requires custom/code_types.inc.php, which queries the database.
     *
     * @param ?\Closure(int): ?array<mixed> $findReferral a referral by its transaction id; ReferralService::getReferral() when null
     * @param ?\Closure(list<int>): array<int, ?string> $findTaskStatuses referrals' Task statuses by transaction id; ReferralService::getTaskStatuses() when null
     * @param ?int $overdueDays the referral_overdue_days global when null
     * @param ?\Closure(int): ?string $findResendableTask the Task a re-send would follow on from, by transaction id; ReferralTaskService::resendableTask() when null
     * @param ?ReferralNoticeRepository $notices what passed between the practice and recipients (#15137)
     */
    public function __construct(
        private ?Environment $twig = null,
        private ?\Closure $findReferral = null,
        private ?\Closure $findTaskStatuses = null,
        ?ClockInterface $clock = null,
        private ?int $overdueDays = null,
        private ?\Closure $findResendableTask = null,
        private ?ReferralNoticeRepository $notices = null,
    ) {
        $this->now = ($clock ?? ServiceContainer::getClock())->now();
    }

    /**
     * The status block for the transaction the add/edit page shows: empty unless it is a saved
     * referral.
     *
     * @param bool $resendable whether to show the Re-send button, which submits resendForm()
     */
    public function forTransactionPage(mixed $formId, mixed $transactionId, bool $resendable = false): string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId) || !($transactionId > 0)) {
            return '';
        }
        $referral = $this->findReferral()((int)$transactionId);
        if ($referral === null) {
            return '';
        }
        return $this->render('referrals/status_block.html.twig', ReferralStatus::ofReferral($referral, $this->now, $this->overdueDays()), $resendable);
    }

    /**
     * The Task a re-send of the page's referral would follow on from, or null when it isn't a saved
     * referral that can be re-sent.
     */
    public function resendableTask(mixed $formId, mixed $transactionId): ?string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId) || !($transactionId > 0)) {
            return null;
        }
        $find = $this->findResendableTask ??= (new ReferralTaskService(new UserService()))->resendableTask(...);
        return $find((int)$transactionId);
    }

    /**
     * The form the Re-send button submits. It goes outside the page's own form, which it can't be
     * nested in, and posting it doesn't save the referral.
     */
    public function resendForm(int $transactionId, string $taskUuid, string $csrfToken): string
    {
        return $this->twig()->render('referrals/resend_form.html.twig', [
            'transaction_id' => $transactionId,
            'task_uuid' => $taskUuid,
            'csrf_token' => $csrfToken,
        ]);
    }

    /**
     * What passed between the practice and a saved referral's recipient, for its page (#15137): how
     * they were told about their Task, Direct sends with it whose delivery failed, and Direct replies
     * filed with it. Empty unless it is a saved referral with any of those.
     *
     * @param bool $confirmable whether to show each reply's Confirm button, which submits replyConfirmForm()
     */
    public function communicationForTransactionPage(mixed $formId, mixed $transactionId, bool $confirmable = false): string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId) || !($transactionId > 0)) {
            return '';
        }
        $notices = $this->notices ??= new ReferralNoticeRepository();
        $notice = $notices->latest((int)$transactionId);
        $failedSends = $notices->failedDirectSends((int)$transactionId);
        $replies = $notices->directReplies((int)$transactionId);
        if ($notice === null && $failedSends === [] && $replies === []) {
            return '';
        }
        return $this->twig()->render('referrals/communication.html.twig', [
            'notice' => $notice,
            'failed_sends' => $failedSends,
            'replies' => $replies,
            'confirmable' => $confirmable,
        ]);
    }

    /**
     * The Task a Direct reply's Confirm button completes (ReferralTaskService::confirmableTask()), or
     * null when it isn't a saved referral with a Task a reply can complete.
     */
    public function confirmableTask(mixed $formId, mixed $transactionId): ?string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId) || !($transactionId > 0)) {
            return null;
        }
        return (new ReferralTaskService(new UserService()))->confirmableTask((int)$transactionId);
    }

    /**
     * The form a Direct reply's Confirm button submits, outside the page's own form, as resendForm() is.
     */
    public function replyConfirmForm(int $transactionId, string $taskUuid, string $csrfToken): string
    {
        return $this->twig()->render('referrals/reply_confirm_form.html.twig', [
            'transaction_id' => $transactionId,
            'task_uuid' => $taskUuid,
            'csrf_token' => $csrfToken,
        ]);
    }

    /**
     * The Task statuses of the referrals among the patient's transactions, by transaction id, found
     * in one query for the whole list.
     *
     * @param array<mixed> $transactions as getTransByPid() lists them
     * @return array<int, ?string>
     */
    public function taskStatuses(array $transactions): array
    {
        $ids = [];
        foreach ($transactions as $transaction) {
            if (is_array($transaction) && ($transaction['title'] ?? null) === ReferralStatus::FORM_ID && is_numeric($transaction['id'] ?? null)) {
                $ids[] = (int)$transaction['id'];
            }
        }
        if ($ids === []) {
            return [];
        }
        $find = $this->findTaskStatuses ??= (new ReferralService(createMissingUuids: false))->getTaskStatuses(...);
        return $find($ids);
    }

    /**
     * The Status cell for a row of the patient's Transactions list, empty for a transaction
     * that isn't a referral.
     *
     * @param array<int, ?string> $taskStatuses from taskStatuses()
     */
    public function transactionCell(mixed $transaction, array $taskStatuses): string
    {
        return $this->render(
            'referrals/status_cell.html.twig',
            ReferralStatus::ofTransaction($transaction, $taskStatuses, $this->now, $this->overdueDays())
        );
    }

    /**
     * The Status cell for a row of the Referrals report.
     *
     * @param array<mixed> $row
     */
    public function reportCell(array $row): string
    {
        return $this->render('referrals/status_cell.html.twig', $this->statusOf($row));
    }

    /**
     * The Show choice as submitted: open, overdue, or '' for all.
     */
    public static function show(string $submitted): string
    {
        return in_array($submitted, [self::SHOW_OPEN, self::SHOW_OVERDUE], true) ? $submitted : '';
    }

    /**
     * The earliest Referral Date the report fetches: the From date, except under Show: Open and
     * Overdue, the worklists, where it is none (''). An open or overdue referral can be an old one,
     * and the From date defaults to the first of the year.
     *
     * @param mixed $fromDate the report's From date, from DateToYYYYMMDD(), which has no return type
     *     but gives text for the text the report passes it
     */
    public static function fromDate(string $show, mixed $fromDate): string
    {
        return in_array($show, [self::SHOW_OPEN, self::SHOW_OVERDUE], true) || !is_string($fromDate) ? '' : $fromDate;
    }

    /**
     * The report's Show filter, as two table cells.
     */
    public function showFilter(string $show): string
    {
        return $this->twig()->render('referrals/show_filter.html.twig', ['show' => $show]);
    }

    /**
     * Whether the report lists a row under its filters: the Show choice, and the facility. A
     * facility id keeps the rows referred from it, compared loosely as the report always has; a
     * false-like one ('0') keeps those referred from no facility, whose facility_id is null, ''
     * or 0 (an int when the driver returns native types, '0' when it doesn't); '' keeps all.
     *
     * @param array<mixed> $row
     */
    public function keeps(array $row, string $show, mixed $facility): bool
    {
        $status = $this->statusOf($row);
        if (($show === self::SHOW_OPEN && !$status->isOpen()) || ($show === self::SHOW_OVERDUE && !$status->overdue)) {
            return false;
        }
        if ($facility === '') {
            return true;
        }
        $rowFacility = $row['facility_id'] ?? null;
        if ((bool)$facility) {
            return $rowFacility == $facility;
        }
        return in_array($rowFacility, [null, '', '0', 0], true);
    }

    /**
     * Who the report says a referral went to: the recipient's organization when it has one,
     * else their name ('' when the recipient isn't in the address book any more).
     *
     * @param array<mixed> $row with the report's organization and referer_to columns
     */
    public static function referTo(array $row): string
    {
        $organization = $row['organization'] ?? null;
        if (is_string($organization) && $organization !== '') {
            return $organization;
        }
        $name = $row['referer_to'] ?? null;
        return is_string($name) ? $name : '';
    }

    /**
     * @param array<mixed> $row
     */
    private function statusOf(array $row): ReferralStatus
    {
        return ReferralStatus::ofReferral($row, $this->now, $this->overdueDays());
    }

    private function render(string $template, ?ReferralStatus $status, bool $resendable = false): string
    {
        return $this->twig()->render($template, [
            'label' => $status?->label(),
            'overdue' => $status !== null && $status->overdue,
            'resendable' => $resendable,
        ]);
    }

    private function twig(): Environment
    {
        return $this->twig ??= ServiceContainer::getTwig();
    }

    /**
     * @return \Closure(int): ?array<mixed>
     */
    private function findReferral(): \Closure
    {
        return $this->findReferral ??= (new ReferralService(createMissingUuids: false))->getReferral(...);
    }

    private function overdueDays(): int
    {
        return $this->overdueDays ??= ReferralStatus::overdueDays();
    }
}
