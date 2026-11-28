<?php

/**
 * A claim payment unattended posting left for a person, as the worklist lists it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

final readonly class ReviewClaim
{
    /**
     * @param int          $id          era_auto_post_claim.id
     * @param string       $checkDate   yyyy-mm-dd, or '' when the 835 gave none
     * @param string       $patientName as the payer sent it
     * @param int          $pid         0 when the claim was not found
     * @param int          $encounter   0 when the claim was not found
     * @param int          $invoiceId   form_encounter.id, which the invoice posting page opens by; 0 when there is none
     * @param list<string> $reasons     as era_auto_post_claim.reasons stores them
     */
    public function __construct(
        public int $id,
        public string $checkNumber,
        public string $checkDate,
        public string $payerName,
        public \DateTimeImmutable $processedAt,
        public string $patientName,
        public int $pid,
        public int $encounter,
        public int $invoiceId,
        public string $ourClaimId,
        public string $payerClaimId,
        public int $payerLevel,
        public string $claimStatusCode,
        public array $reasons,
        public float $claimPayment,
        public float $paymentsPosted,
        public float $adjustmentsPosted,
        public ?WorkedMark $worked,
    ) {
    }
}
