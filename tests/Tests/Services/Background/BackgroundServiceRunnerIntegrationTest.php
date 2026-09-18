<?php

/**
 * Integration tests for BackgroundServiceRunner's lease-based locking.
 *
 * These tests exercise the real SQL path (not mocked) against the test
 * database. They cover the issue fixed by GH #11661: that a lease left
 * behind by a crashed worker is automatically recovered on the next
 * acquire attempt.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Background;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
#[Group('background-services')]
class BackgroundServiceRunnerIntegrationTest extends TestCase
{
    private const TEST_SERVICE = '_e2e_runner_lease';

    private LeaseExposingRunner $runner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->runner = new LeaseExposingRunner();
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `background_services` WHERE `name` = ?',
            [self::TEST_SERVICE],
            true,
        );
    }

    protected function tearDown(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `background_services` WHERE `name` = ?',
            [self::TEST_SERVICE],
            true,
        );
        parent::tearDown();
    }

    public function testAcquireOnFreshServiceSucceedsAndSetsLease(): void
    {
        $this->insertService();

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: false);

        $this->assertNull($result, 'Acquire should succeed on a fresh service');

        $row = $this->fetchRow();
        $this->assertNotNull(
            $row['lock_expires_at'],
            'Successful acquire must set lock_expires_at',
        );
        // The `running` column is derived/legacy; compare as int because
        // Doctrine DBAL 4 may return tinyint as int rather than numeric-string.
        $this->assertSame(1, (int) $row['running']);
    }

    public function testAcquireFailsWhenLiveLeaseHeld(): void
    {
        $this->insertService();
        $this->setLeaseExpiryMinutesFromNow(10); // live

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: true);

        $this->assertSame('already_running', $result);
    }

    public function testAcquireStealsExpiredLease(): void
    {
        $this->insertService();
        $this->setLeaseExpiryMinutesFromNow(-10); // expired

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: true);

        $this->assertNull($result, 'Expired lease must be stolen on next acquire');
        $row = $this->fetchRow();
        $this->assertNotNull($row['lock_expires_at']);
        // New lease must be live by the DB's own clock. Issue NOW() in
        // SQL rather than comparing against PHP's time() to avoid
        // clock/timezone drift between the app and the DB session.
        $liveRow = QueryUtils::querySingleRow(
            <<<'SQL'
            SELECT 1 AS live
              FROM `background_services`
             WHERE `name` = ? AND `lock_expires_at` > NOW()
            SQL,
            [self::TEST_SERVICE],
            false,
        );
        $this->assertIsArray($liveRow, 'New lease must be in the future per DB clock');
    }

    public function testReleaseClearsLease(): void
    {
        $this->insertService();
        $this->runner->callAcquireLock($this->fetchRow(), force: true);

        $this->runner->callReleaseLock(self::TEST_SERVICE);

        $row = $this->fetchRow();
        $this->assertNull($row['lock_expires_at']);
        $this->assertSame(0, (int) $row['running']);
    }

    public function testAcquireReturnsNotDueWhenIntervalNotElapsed(): void
    {
        $this->insertService(nextRunMinutesFromNow: 10);

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: false);

        $this->assertSame('not_due', $result);
    }

    public function testAcquireReportsNotDueEvenWhenExpiredLeasePresent(): void
    {
        // Regression guard: a service with an expired (stale) lease AND
        // a future next_run should report 'not_due' rather than
        // 'already_running'. The stale timestamp is not a live lease.
        $this->insertService(nextRunMinutesFromNow: 10);
        $this->setLeaseExpiryMinutesFromNow(-10);

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: false);

        $this->assertSame('not_due', $result);
    }

    /**
     * Insert the test service with next_run relative to the DB's NOW().
     * Uses SQL arithmetic rather than PHP's time() so the test shares a
     * clock with acquireLock().
     *
     * @param int $nextRunMinutesFromNow Positive for future, negative for
     *   past (e.g. -100000 for "long past, always due").
     */
    private function insertService(int $nextRunMinutesFromNow = -100000): void
    {
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `sort_order`)
            VALUES (?, ?, 1, 0, NOW() + INTERVAL ? MINUTE, 5, ?, 100)
            SQL,
            [self::TEST_SERVICE, 'Runner Lease Test', $nextRunMinutesFromNow, 'phpinfo'],
            true,
        );
    }

    /**
     * Set the lease expiration relative to the DB's NOW(). Positive for a
     * live lease, negative for an expired one. Uses SQL arithmetic so the
     * test shares a clock with acquireLock().
     */
    private function setLeaseExpiryMinutesFromNow(int $minutesFromNow): void
    {
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            UPDATE `background_services`
               SET `running` = 1,
                   `lock_expires_at` = NOW() + INTERVAL ? MINUTE
             WHERE `name` = ?
            SQL,
            [$minutesFromNow, self::TEST_SERVICE],
            true,
        );
    }

    /**
     * @return BackgroundServicesRow
     */
    private function fetchRow(): array
    {
        /** @var list<BackgroundServicesRow> $rows */
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT * FROM `background_services` WHERE `name` = ?',
            [self::TEST_SERVICE],
        );
        $this->assertNotEmpty($rows, 'Test service row must exist');
        return $rows[0];
    }
}

/**
 * Exposes the protected acquireLock/releaseLock methods for testing.
 *
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class LeaseExposingRunner extends BackgroundServiceRunner
{
    /**
     * @param BackgroundServicesRow $service
     */
    public function callAcquireLock(array $service, bool $force): ?string
    {
        return $this->acquireLock($service, $force);
    }

    public function callReleaseLock(string $serviceName): void
    {
        $this->releaseLock($serviceName);
    }
}
