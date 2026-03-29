<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc. <https://www.opencoreemr.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background;

use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
#[Group('isolated')]
#[Group('background-services')]
class BackgroundServiceRunnerTest extends TestCase
{
    public function testRunReturnsNotFoundForUnknownService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: []);
        $results = $runner->run('nonexistent');

        $this->assertCount(1, $results);
        $this->assertSame('nonexistent', $results[0]['name']);
        $this->assertSame('not_found', $results[0]['status']);
    }

    public function testRunSkipsInactiveService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: [
            self::makeService('svc1', active: false),
        ]);
        $results = $runner->run('svc1');

        $this->assertSame('skipped', $results[0]['status']);
    }

    public function testRunSkipsAlreadyRunningService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: [
            self::makeService('svc1', running: true),
        ]);
        $results = $runner->run('svc1');

        $this->assertSame('skipped', $results[0]['status']);
    }

    public function testRunReturnsLockedWhenLockFails(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            lockResult: false,
        );
        $results = $runner->run('svc1');

        $this->assertSame('locked', $results[0]['status']);
    }

    public function testRunExecutesServiceSuccessfully(): void
    {
        $executed = false;
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            executeCallback: function (array $service) use (&$executed): void {
                $executed = true;
            },
        );
        $results = $runner->run('svc1');

        $this->assertSame('executed', $results[0]['status']);
        $this->assertTrue($executed);
        $this->assertContains('svc1', $runner->releasedLocks);
    }

    public function testRunReleasesLockOnException(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            executeCallback: function (array $service): void {
                throw new \RuntimeException('Service failure');
            },
        );
        $results = $runner->run('svc1');

        $this->assertSame('error', $results[0]['status']);
        $this->assertContains('svc1', $runner->releasedLocks);
    }

    public function testRunAllServicesInOrder(): void
    {
        $order = [];
        $runner = new BackgroundServiceRunnerStub(
            services: [
                self::makeService('svc1'),
                self::makeService('svc2'),
                self::makeService('svc3', active: false),
            ],
            executeCallback: function (array $service) use (&$order): void {
                $order[] = $service['name'];
            },
        );
        $results = $runner->run();

        $this->assertCount(3, $results);
        $this->assertSame('executed', $results[0]['status']);
        $this->assertSame('executed', $results[1]['status']);
        $this->assertSame('skipped', $results[2]['status']);
        $this->assertSame(['svc1', 'svc2'], $order);
    }

    public function testRunSkipsManualModeServiceWithoutForce(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('manual_svc', executeInterval: 0)],
        );
        $results = $runner->run('manual_svc');

        $this->assertSame('skipped', $results[0]['status']);
    }

    public function testRunExecutesManualModeServiceWithForce(): void
    {
        $executed = false;
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('manual_svc', executeInterval: 0)],
            executeCallback: function (array $service) use (&$executed): void {
                $executed = true;
            },
        );
        $results = $runner->run('manual_svc', force: true);

        $this->assertSame('executed', $results[0]['status']);
        $this->assertTrue($executed);
    }

    /**
     * @return BackgroundServicesRow
     */
    private static function makeService(
        string $name,
        bool $active = true,
        bool $running = false,
        int $executeInterval = 5,
    ): array {
        return [
            'name' => $name,
            'title' => $name,
            'active' => $active ? 1 : 0,
            'running' => $running ? 1 : 0,
            'next_run' => '2020-01-01 00:00:00',
            'execute_interval' => $executeInterval,
            'function' => 'test_function_' . $name,
            'require_once' => null,
            'sort_order' => 100,
        ];
    }
}

/**
 * Test stub that overrides DB and execution methods.
 *
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServiceRunnerStub extends BackgroundServiceRunner
{
    /** @var list<string> */
    public array $releasedLocks = [];

    /**
     * @param list<BackgroundServicesRow> $services
     * @param (\Closure(BackgroundServicesRow): void)|null $executeCallback
     */
    public function __construct(
        private readonly array $services = [],
        private readonly bool $lockResult = true,
        private readonly ?\Closure $executeCallback = null,
    ) {
    }

    protected function getServices(?string $serviceName, bool $force): array
    {
        if ($serviceName !== null) {
            return array_values(array_filter(
                $this->services,
                fn(array $s) => $s['name'] === $serviceName,
            ));
        }
        return $this->services;
    }

    protected function acquireLock(array $service, bool $force): bool
    {
        return $this->lockResult;
    }

    protected function releaseLock(string $serviceName): void
    {
        $this->releasedLocks[] = $serviceName;
    }

    protected function executeService(array $service): void
    {
        if ($this->executeCallback !== null) {
            ($this->executeCallback)($service);
        }
    }
}
