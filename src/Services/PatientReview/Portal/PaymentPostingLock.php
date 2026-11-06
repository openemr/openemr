<?php

/**
 * Serializes changes to one patient's pending portal payment.
 *
 * Posting a portal payment checks that its review request is still pending, writes the ledger
 * rows, then closes the request. Two staff saves of the same payment at once could both pass the
 * check and both write the ledger before either closed it, and a patient replacing the payment
 * in the meantime could cancel it while staff posted it, leaving the replacement pending to post
 * again. The lock is per patient, so a staff posting and a patient's new payment both take it:
 * whichever comes second waits, then sees what the first left.
 *
 * It is a MySQL advisory lock (GET_LOCK), as BackgroundServiceRunner and ReferralTaskService
 * take theirs, rather than a transaction: the ledger writes open their own transactions
 * (Recorder), and ADODB transactions do not nest. The database session holds the lock, and a
 * shutdown function releases it, so a posting that dies part way does not keep it.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

use Doctrine\DBAL\Exception as DbalException;
use OpenEMR\Common\Database\QueryUtils;

final class PaymentPostingLock implements PatientPaymentLock
{
    /** GET_LOCK's name prefix, as core's other advisory locks are prefixed (within MySQL's 64 characters). */
    private const PREFIX = 'openemr.portal_payment.';

    /** How long a save waits for another posting or submission for the same patient. */
    private const WAIT_SECONDS = 30;

    private ?string $held = null;

    private bool $releaseOnShutdown = false;

    /**
     * Waits for, then holds, the lock on one patient's portal payment.
     *
     * @throws \RuntimeException when another save held it past the wait, or GET_LOCK failed
     */
    public function acquire(int $pid): void
    {
        $name = self::PREFIX . 'pid.' . $pid;
        $row = QueryUtils::querySingleRow('SELECT GET_LOCK(?, ?) AS got', [$name, self::WAIT_SECONDS], false);
        $got = is_array($row) ? ($row['got'] ?? null) : null;
        if ($got !== 1 && $got !== '1') {
            throw new \RuntimeException('Another posting of this portal payment is still in progress');
        }
        $this->held = $name;
        if (!$this->releaseOnShutdown) {
            $this->releaseOnShutdown = true;
            register_shutdown_function($this->release(...));
        }
    }

    public function release(): void
    {
        $name = $this->held;
        if ($name === null) {
            return;
        }
        $this->held = null;
        try {
            QueryUtils::fetchRecordsNoLog('SELECT RELEASE_LOCK(?) AS released', [$name]);
        } catch (\RuntimeException | \LogicException | DbalException) {
            // The database releases it anyway when the session ends, and a failure here must not
            // take the place of what the posting returned or threw.
        }
    }
}
