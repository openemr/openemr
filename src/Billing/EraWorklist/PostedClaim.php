<?php

/**
 * A claim payment of a check that unattended posting posted, with what was posted for it.
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

final readonly class PostedClaim
{
    /**
     * @param string           $patientName as the payer sent it
     * @param int              $invoiceId   form_encounter.id; 0 when there is none
     * @param list<string>     $reasons     why it needs review or was skipped; none when posted
     * @param list<PostedLine> $lines       the payments and adjustments its check's payment session holds for the encounter
     */
    public function __construct(
        public string $patientName,
        public int $pid,
        public int $encounter,
        public int $invoiceId,
        public string $payerClaimId,
        public int $payerLevel,
        public string $claimStatusCode,
        public ClaimOutcome $outcome,
        public array $reasons,
        public float $claimPayment,
        public float $paymentsPosted,
        public float $adjustmentsPosted,
        public array $lines,
    ) {
    }
}
