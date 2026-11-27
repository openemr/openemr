<?php

/**
 * A check unattended posting held: nothing from it was posted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2026 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Billing\EraWorklist;

use OpenEMR\Billing\EraAutoPost\HoldReason;

final readonly class HeldCheck
{
    /**
     * @param string      $checkDate yyyy-mm-dd, or '' when the 835 gave none
     * @param ?HoldReason $reason    null when the stored reason is not one this version knows
     * @param int         $claims    how many claim payments the check carries
     */
    public function __construct(
        public string $checkNumber,
        public string $checkDate,
        public string $payerName,
        public float $checkAmount,
        public float $claimTotal,
        public float $plbTotal,
        public ?HoldReason $reason,
        public int $claims,
    ) {
    }
}
