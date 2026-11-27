<?php

/**
 * A check that unattended posting posted, with its claim payments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\ClaimOutcome;

final readonly class PostedCheck
{
    /**
     * @param string            $checkDate  yyyy-mm-dd, or '' when the 835 gave none
     * @param float             $claimTotal the claim payments (CLP04) added up
     * @param float             $plbTotal   the provider-level adjustments, which the payer takes off the claim payments
     * @param string            $plbReasons their reason codes, comma separated
     * @param string            $fileName   the name the 835 is stored under
     * @param list<PostedClaim> $claims     in the file's order
     */
    public function __construct(
        public int $id,
        public string $checkNumber,
        public string $checkDate,
        public string $payerName,
        public float $checkAmount,
        public float $claimTotal,
        public float $plbTotal,
        public string $plbReasons,
        public int $sessionId,
        public \DateTimeImmutable $processedAt,
        public string $fileName,
        public array $claims,
    ) {
    }

    /**
     * The payments the run posted from this check.
     */
    public function postedTotal(): float
    {
        return round(array_sum(array_map(static fn(PostedClaim $claim): float => $claim->paymentsPosted, $this->claims)), 2);
    }

    /**
     * The claim payments the run did not post: those left for review, less what was
     * posted of them, and those an earlier posting of the check had already posted.
     */
    public function notPostedTotal(): float
    {
        return round($this->claimTotal - $this->postedTotal(), 2);
    }

    public function count(ClaimOutcome $outcome): int
    {
        return count(array_filter($this->claims, static fn(PostedClaim $claim): bool => $claim->outcome === $outcome));
    }
}
