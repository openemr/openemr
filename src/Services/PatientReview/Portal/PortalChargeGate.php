<?php

/**
 * Decides, under the patient's payment lock, whether a portal card charge may go ahead.
 *
 * The page offers no payment while a charged one is open, but a page loaded before the first
 * charge can still submit a second. So each charge handler (portal/lib/paylib.php) asks here
 * first. The lock is taken before the check and, when the charge may go ahead, left held until
 * the request ends, so the check, the charge and the recording of the payment happen as one; a
 * second submission waits for the first and is then refused. Recording the payment takes the
 * same lock again, which GET_LOCK allows on one connection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

final readonly class PortalChargeGate
{
    public function __construct(private PortalReviewWorkflows $workflows)
    {
    }

    /**
     * Null when the charge may go ahead, with $lock then held; otherwise why not, with $lock
     * released.
     */
    public function open(mixed $formPid, PatientPaymentLock $lock): ?ChargeRefusal
    {
        $pid = filter_var($formPid, FILTER_VALIDATE_INT);
        if (!is_int($pid) || $pid <= 0) {
            return ChargeRefusal::NoPatient;
        }
        try {
            $lock->acquire($pid);
        } catch (\RuntimeException) {
            return ChargeRefusal::PaymentInProgress;
        }
        if ($this->workflows->hasOpenChargedPayment($pid)) {
            $lock->release();

            return ChargeRefusal::ChargedPaymentOpen;
        }

        return null;
    }
}
