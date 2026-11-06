<?php

/**
 * A lock on one patient's portal payments, held while a payment is charged, recorded or posted.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\PatientReview\Portal;

interface PatientPaymentLock
{
    /**
     * Waits for, then holds, the lock for this patient.
     *
     * @throws \RuntimeException when it could not be had
     */
    public function acquire(int $pid): void;

    public function release(): void;
}
