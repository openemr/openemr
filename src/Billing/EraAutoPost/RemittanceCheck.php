<?php

/**
 * One check (transaction set) of an 835, with its claim payments.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraAutoPost;

use OpenEMR\Billing\EraPosting\ClaimPayment;

final readonly class RemittanceCheck
{
    /**
     * @param string             $checkNumber TRN02
     * @param string             $checkDate   BPR16, yyyymmdd
     * @param float              $amount      BPR02
     * @param string             $payerName   N1*PR
     * @param list<ClaimPayment> $claims
     * @param float              $plbTotal    sum of the provider-level adjustments (PLB)
     * @param list<string>       $plbReasons  their adjustment reason codes
     */
    public function __construct(
        public string $checkNumber,
        public string $checkDate,
        public float $amount,
        public string $payerName,
        public array $claims,
        public float $plbTotal,
        public array $plbReasons,
    ) {
    }

    /**
     * The sum of the claim payments (CLP04).
     */
    public function claimTotal(): float
    {
        return round(array_sum(array_map(static fn(ClaimPayment $claim): float => $claim->paymentAmount, $this->claims)), 2);
    }

    /**
     * The 835's own rule: the check amount is the claim payments less the provider-level
     * adjustments (a positive PLB amount is money the payer kept back).
     */
    public function isBalanced(): bool
    {
        return round($this->claimTotal() - $this->plbTotal - $this->amount, 2) === 0.0;
    }
}
