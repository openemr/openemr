<?php

/**
 * Isolated tests for the Weno prescription log sync lock.
 *
 * Two syncs at once (the widget's auto-sync and a Sync click) wrote the same
 * logsync.csv and the second one failed. SyncLock lets only one run.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Modules\Weno;

use OpenEMR\Modules\WenoModule\Services\SyncLock;
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../../../../interface/modules/custom_modules/oe-module-weno/src/Services/SyncLock.php';

class SyncLockTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/weno-synclock-' . bin2hex(random_bytes(6));
    }

    protected function tearDown(): void
    {
        $lockPath = $this->dir . '/logsync.lock';
        if (is_dir($lockPath)) {
            rmdir($lockPath);
        } elseif (is_file($lockPath)) {
            unlink($lockPath);
        }
        if (is_dir($this->dir)) {
            rmdir($this->dir);
        } elseif (is_file($this->dir)) {
            unlink($this->dir);
        }
    }

    public function testAcquireCreatesMissingDirectory(): void
    {
        $lock = new SyncLock($this->dir);

        self::assertTrue($lock->tryAcquire());
        self::assertFileExists($lock->lockPath());
        $lock->release();
    }

    public function testSecondSyncCannotAcquireWhileFirstHoldsLock(): void
    {
        $first = new SyncLock($this->dir);
        $second = new SyncLock($this->dir);

        self::assertTrue($first->tryAcquire());
        self::assertFalse($second->tryAcquire());
        $first->release();
    }

    public function testLockIsAvailableAgainAfterRelease(): void
    {
        $first = new SyncLock($this->dir);
        $second = new SyncLock($this->dir);

        self::assertTrue($first->tryAcquire());
        $first->release();

        self::assertTrue($second->tryAcquire());
        $second->release();
    }

    public function testDestroyingHolderReleasesLock(): void
    {
        $first = new SyncLock($this->dir);
        self::assertTrue($first->tryAcquire());
        unset($first);

        $second = new SyncLock($this->dir);
        self::assertTrue($second->tryAcquire());
        $second->release();
    }

    public function testAcquireIsIdempotentForHolder(): void
    {
        $lock = new SyncLock($this->dir);

        self::assertTrue($lock->tryAcquire());
        self::assertTrue($lock->tryAcquire());
        $lock->release();
    }

    public function testUncreatableDirectoryThrowsInsteadOfReportingContention(): void
    {
        // A regular file where the lock directory should be: mkdir() fails.
        file_put_contents($this->dir, '');
        $lock = new SyncLock($this->dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('lock directory cannot be created');
        $lock->tryAcquire();
    }

    public function testUnopenableLockFileThrowsInsteadOfReportingContention(): void
    {
        // A directory where the lock file should be: fopen() fails.
        mkdir($this->dir . '/logsync.lock', 0775, true);
        $lock = new SyncLock($this->dir);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('lock file cannot be opened');
        $lock->tryAcquire();
    }

    public function testForSiteUsesWenoLogDirectory(): void
    {
        self::assertSame(
            '/var/sites/default/documents/logs_and_misc/weno/logsync.lock',
            SyncLock::forSite('/var/sites/default/')->lockPath()
        );
    }
}
