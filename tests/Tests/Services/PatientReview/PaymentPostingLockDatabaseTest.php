<?php

/**
 * PaymentPostingLock against the database: the lock is held between acquire() and release().
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\PatientReview;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\PatientReview\Portal\PaymentPostingLock;
use PHPUnit\Framework\TestCase;

class PaymentPostingLockDatabaseTest extends TestCase
{
    /** A patient id no fixture uses. */
    private const PID = 990091;

    private const LOCK_NAME = 'openemr.portal_payment.pid.990091';

    public function testTheLockIsHeldFromAcquireUntilRelease(): void
    {
        $lock = new PaymentPostingLock();
        $this->assertTrue($this->isFree(), 'The lock must start free');

        $lock->acquire(self::PID);
        $heldAfterAcquire = !$this->isFree();
        $lock->release();
        $freeAfterRelease = $this->isFree();

        $this->assertTrue($heldAfterAcquire, 'acquire() must hold the lock');
        $this->assertTrue($freeAfterRelease, 'release() must free the lock');
    }

    public function testReleasingTwiceOrWithoutTheLockIsHarmless(): void
    {
        $lock = new PaymentPostingLock();
        $lock->release();
        $lock->acquire(self::PID);
        $lock->release();
        $lock->release();

        $this->assertTrue($this->isFree());
    }

    /**
     * Asks the database each time; the answer changes with acquire() and release().
     *
     * @phpstan-impure
     */
    private function isFree(): bool
    {
        $free = QueryUtils::fetchSingleValue('SELECT IS_FREE_LOCK(?) AS free', 'free', [self::LOCK_NAME]);
        return $free === 1 || $free === '1';
    }
}
