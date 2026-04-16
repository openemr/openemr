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
        $this->setLeaseExpiry(date('Y-m-d H:i:s', time() + 600)); // 10 min future

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: true);

        $this->assertSame('already_running', $result);
    }

    public function testAcquireStealsExpiredLease(): void
    {
        $this->insertService();
        $this->setLeaseExpiry(date('Y-m-d H:i:s', time() - 600)); // 10 min past

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: true);

        $this->assertNull($result, 'Expired lease must be stolen on next acquire');
        $row = $this->fetchRow();
        $this->assertNotNull($row['lock_expires_at']);
        // New lease must be in the future, not the stale one we set.
        $this->assertGreaterThan(time(), (int) strtotime((string) $row['lock_expires_at']));
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
        $this->insertService(nextRun: date('Y-m-d H:i:s', time() + 600));

        $result = $this->runner->callAcquireLock($this->fetchRow(), force: false);

        $this->assertSame('not_due', $result);
    }

    private function insertService(string $nextRun = '1970-01-01 00:00:00'): void
    {
        QueryUtils::sqlStatementThrowException(
            'INSERT INTO `background_services`'
            . ' (`name`, `title`, `active`, `running`, `next_run`, `execute_interval`, `function`, `sort_order`)'
            . ' VALUES (?, ?, 1, 0, ?, 5, ?, 100)',
            [self::TEST_SERVICE, 'Runner Lease Test', $nextRun, 'phpinfo'],
            true,
        );
    }

    private function setLeaseExpiry(string $expiresAt): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `running` = 1, `lock_expires_at` = ? WHERE `name` = ?',
            [$expiresAt, self::TEST_SERVICE],
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
