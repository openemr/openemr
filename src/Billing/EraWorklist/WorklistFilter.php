<?php

/**
 * What the biller asked the ERA worklist to show.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\ClaimRecord;
use OpenEMR\Billing\EraPosting\ClaimIssue;
use Symfony\Component\HttpFoundation\InputBag;

final readonly class WorklistFilter
{
    /**
     * @param string              $payerName   the payer as the 835 names it (N1*PR), exactly; '' for any
     * @param string              $checkNumber any part of the check or EFT trace number; '' for any
     * @param ?\DateTimeImmutable $from        the first day to list, at midnight
     * @param ?\DateTimeImmutable $to          the last day to list, at midnight
     * @param ?ClaimIssue         $reason      list only claim payments left for review for this reason
     * @param bool                $showWorked  also list the rows already marked worked
     */
    public function __construct(
        public string $payerName = '',
        public string $checkNumber = '',
        public ?\DateTimeImmutable $from = null,
        public ?\DateTimeImmutable $to = null,
        public ?ClaimIssue $reason = null,
        public bool $showWorked = false,
    ) {
    }

    /**
     * @param InputBag<string> $query the page's query string
     */
    public static function fromQuery(InputBag $query): self
    {
        return new self(
            payerName: trim($query->getString('payer')),
            checkNumber: trim($query->getString('check')),
            from: self::day($query->getString('from')),
            to: self::day($query->getString('to')),
            reason: self::reason($query->getString('reason')),
            showWorked: $query->getString('worked') === '1',
        );
    }

    /**
     * The reasons a claim payment is left for review, as era_auto_post_claim.reasons stores
     * them. A claim already posted from its check is skipped, not left for review.
     *
     * @return list<string>
     */
    public static function reviewReasons(): array
    {
        $reasons = [];
        foreach (ClaimIssue::cases() as $issue) {
            if ($issue !== ClaimIssue::AlreadyPosted) {
                $reasons[] = ClaimRecord::reason($issue);
            }
        }
        return $reasons;
    }

    /**
     * The stored value of the reason asked for; '' for any.
     */
    public function reasonValue(): string
    {
        return $this->reason === null ? '' : ClaimRecord::reason($this->reason);
    }

    private static function reason(string $value): ?ClaimIssue
    {
        foreach (ClaimIssue::cases() as $issue) {
            if ($issue !== ClaimIssue::AlreadyPosted && ClaimRecord::reason($issue) === $value) {
                return $issue;
            }
        }
        return null;
    }

    /**
     * @param string $value yyyy-mm-dd, as the page's date fields send it
     */
    private static function day(string $value): ?\DateTimeImmutable
    {
        $day = \DateTimeImmutable::createFromFormat('!Y-m-d', trim($value));
        // A date that rolled over (2026-02-31) is not what was typed.
        return $day !== false && $day->format('Y-m-d') === trim($value) ? $day : null;
    }
}
