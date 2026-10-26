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
use Twig\Environment;

final class ReferralDisplay
{
    /** The Referrals report's Show choices besides All (''). */
    public const SHOW_OPEN = 'open';
    public const SHOW_OVERDUE = 'overdue';

    private readonly \DateTimeImmutable $today;

    /**
     * @param ?\Closure(int): ?array<mixed> $findReferral a referral by its transaction id; ReferralService::getReferral() when null
     * @param ?int $overdueDays the referral_overdue_days global when null
     */
    public function __construct(
        private ?Environment $twig = null,
        private ?\Closure $findReferral = null,
        ?\DateTimeImmutable $today = null,
        private ?int $overdueDays = null,
    ) {
        $this->today = $today ?? new \DateTimeImmutable('today');
    }

    /**
     * The status block for the transaction the add/edit page shows: empty unless it is a saved
     * referral.
     */
    public function forTransactionPage(mixed $formId, mixed $transactionId): string
    {
        if ($formId !== ReferralStatus::FORM_ID || !is_numeric($transactionId) || !($transactionId > 0)) {
            return '';
        }
        $referral = $this->findReferral()((int)$transactionId);
        if ($referral === null) {
            return '';
        }
        return $this->render('referrals/status_block.html.twig', ReferralStatus::ofReferral($referral, $this->today, $this->overdueDays()));
    }

    /**
     * The Status cell for a row of the patient's Transactions list, empty for a transaction
     * that isn't a referral.
     */
    public function transactionCell(mixed $transaction): string
    {
        return $this->render(
            'referrals/status_cell.html.twig',
            ReferralStatus::ofTransaction($transaction, $this->findReferral(), $this->today, $this->overdueDays())
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
     * The report's Show filter, as two table cells.
     */
    public function showFilter(string $show): string
    {
        return $this->twig()->render('referrals/show_filter.html.twig', ['show' => $show]);
    }

    /**
     * Whether the report lists a row under its filters: the Show choice, and the facility. A
     * facility id keeps the rows referred from it; a false-like one ('0') keeps those referred
     * from no facility; '' keeps all. The facility is compared loosely, as the report always has.
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
        return in_array($rowFacility, [null, '', '0'], true);
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
        return ReferralStatus::ofReferral($row, $this->today, $this->overdueDays());
    }

    private function render(string $template, ?ReferralStatus $status): string
    {
        return $this->twig()->render($template, [
            'label' => $status?->label(),
            'overdue' => $status !== null && $status->overdue,
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
