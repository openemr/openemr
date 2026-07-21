<?php

/**
 * Integration tests for BackgroundServicesCommand's protected DB methods.
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

use OpenEMR\Common\Command\BackgroundServicesCommand;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
#[Group('background-services')]
class BackgroundServicesCommandIntegrationTest extends TestCase
{
    private const TEST_SERVICE = '_e2e_cmd_';

    private CommandMethodExposer $command;

    protected function setUp(): void
    {
        parent::setUp();
        $this->command = new CommandMethodExposer();
        $this->cleanup();
    }

    protected function tearDown(): void
    {
        $this->cleanup();
        parent::tearDown();
    }

    private function cleanup(): void
    {
        QueryUtils::sqlStatementThrowException(
            'DELETE FROM `background_services` WHERE `name` LIKE ?',
            [self::TEST_SERVICE . '%'],
            true,
        );
    }

    private function insertService(string $suffix, bool $active = true, int $interval = 5): void
    {
        QueryUtils::sqlStatementThrowException(
            <<<'SQL'
            INSERT INTO `background_services`
                (`name`, `title`, `active`, `running`, `execute_interval`, `function`, `sort_order`)
            VALUES (?, ?, ?, 0, ?, ?, 100)
            SQL,
            [self::TEST_SERVICE . $suffix, 'Test: ' . $suffix, $active ? 1 : 0, $interval, 'phpinfo'],
            true,
        );
    }

    public function testClearLeaseReturnsTrueForExistingService(): void
    {
        $this->insertService('clear');

        $this->assertTrue($this->command->callClearLease(self::TEST_SERVICE . 'clear'));
    }

    public function testClearLeaseReturnsFalseForMissingService(): void
    {
        $this->assertFalse($this->command->callClearLease(self::TEST_SERVICE . 'nonexistent'));
    }

    public function testClearLeaseClearsLockExpiresAt(): void
    {
        $name = self::TEST_SERVICE . 'locked';
        $this->insertService('locked');
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `running` = 1, `lock_expires_at` = NOW() + INTERVAL 60 MINUTE WHERE `name` = ?',
            [$name],
            true,
        );

        $this->command->callClearLease($name);

        /** @var list<array{lock_expires_at: ?string, running: numeric-string}> $rows */
        $rows = QueryUtils::fetchRecordsNoLog(
            'SELECT `lock_expires_at`, `running` FROM `background_services` WHERE `name` = ?',
            [$name],
        );
        $this->assertCount(1, $rows);
        $this->assertNull($rows[0]['lock_expires_at']);
        $this->assertSame(0, (int) $rows[0]['running']);
    }

    public function testFetchServicesComputesLeaseIsLive(): void
    {
        $this->insertService('fetch');

        $rows = $this->command->callFetchServices();
        $names = array_column($rows, 'name');
        $idx = array_search(self::TEST_SERVICE . 'fetch', $names, true);

        $this->assertIsInt($idx);
        // No lease held → lease_is_live must be falsy.
        $this->assertSame(0, (int) ($rows[$idx]['lease_is_live'] ?? -1));
    }

    public function testFetchActiveServicesFiltersCorrectly(): void
    {
        $this->insertService('active', active: true, interval: 5);
        $this->insertService('inactive', active: false, interval: 5);
        $this->insertService('manual', active: true, interval: 0);

        $rows = $this->command->callFetchActiveServices();
        $names = array_column($rows, 'name');

        $this->assertContains(self::TEST_SERVICE . 'active', $names);
        $this->assertNotContains(self::TEST_SERVICE . 'inactive', $names);
        $this->assertNotContains(self::TEST_SERVICE . 'manual', $names);
    }
}

/**
 * Exposes the protected command methods for integration testing.
 *
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
class CommandMethodExposer extends BackgroundServicesCommand
{
    public function __construct()
    {
        parent::__construct();
    }

    public function callClearLease(string $name): bool
    {
        return $this->clearLease($name);
    }

    /** @return list<BackgroundServicesQueryRow> */
    public function callFetchServices(): array
    {
        return $this->fetchServices();
    }

    /** @return list<BackgroundServicesQueryRow> */
    public function callFetchActiveServices(): array
    {
        return $this->fetchActiveServices();
    }
}
