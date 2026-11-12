<?php

/**
 * What posting one ERA claim payment did: the claim it matched, what it posted,
 * why any of it wasn't posted, and the report lines for it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraPosting;

final readonly class ClaimPostingResult
{
    /**
     * @param int                                $pid               0 when the claim wasn't found
     * @param int                                $encounter         0 when the claim wasn't found
     * @param int                                $level             insurance level the payer paid at (1-3)
     * @param int                                $sessionId         the payment session posted to
     * @param list<ClaimIssue>                   $issues            in the order found; none when it all posted
     * @param float                              $paymentsPosted    sum of payments posted
     * @param float                              $adjustmentsPosted sum of adjustments posted
     * @param float                              $chargesAdded      sum of charges added for codes not on the invoice
     * @param bool                               $levelClosed       the insurance level was closed
     * @param bool                               $secondaryQueued   the claim was queued for the next payer (false when
     *                                                              it was only reopened, having no payer at that level)
     * @param list<ReportMessage|ReportDetail>   $lines             the report for this claim payment
     */
    public function __construct(
        public int $pid,
        public int $encounter,
        public string $invoiceNumber,
        public int $level,
        public string $claimStatusCode,
        public int $sessionId,
        public array $issues,
        public float $paymentsPosted,
        public float $adjustmentsPosted,
        public float $chargesAdded,
        public bool $levelClosed,
        public bool $secondaryQueued,
        public array $lines,
    ) {
    }

    public function hasIssue(ClaimIssue $issue): bool
    {
        return in_array($issue, $this->issues, true);
    }
}
