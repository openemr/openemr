<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background;

use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceProcessSpawner;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

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

    public function testRunReturnsAlreadyRunningWhenLockFailsDueToRunningProcess(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            lockFailureReason: 'already_running',
        );
        $results = $runner->run('svc1');

        $this->assertSame('already_running', $results[0]['status']);
    }

    public function testRunReturnsNotDueWhenLockFailsDueToInterval(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            lockFailureReason: 'not_due',
        );
        $results = $runner->run('svc1');

        $this->assertSame('not_due', $results[0]['status']);
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

    public function testRunAllDelegatesActiveServicesToSpawnerInOrder(): void
    {
        // Run-all-due isolates each active service behind a subprocess
        // boundary so that exit()/die()/fatals in one service cannot
        // abort subsequent services in the same tick (GH #11794).
        // The parent loop skips inactive services without spawning;
        // inactivity is trivially decidable from the row.
        $spawner = new RecordingSpawner([
            'svc1' => 'executed',
            'svc2' => 'not_due',
        ]);
        $runner = new BackgroundServiceRunnerStub(
            services: [
                self::makeService('svc1'),
                self::makeService('svc2'),
                self::makeService('svc3', active: false),
            ],
            spawner: $spawner,
        );

        $results = $runner->run();

        $this->assertSame(
            [
                ['name' => 'svc1', 'status' => 'executed'],
                ['name' => 'svc2', 'status' => 'not_due'],
                ['name' => 'svc3', 'status' => 'skipped'],
            ],
            $results,
        );
        // Inactive services must never be spawned. Skip is a pure
        // parent-side decision and spawning one wastes a bootstrap.
        $this->assertSame(['svc1', 'svc2'], $spawner->spawnedNames);
    }

    public function testRunAllContinuesAfterSpawnerReportsError(): void
    {
        // Regression guard for GH #11794: a service whose subprocess
        // exits abnormally (surfaced by the spawner as status=error)
        // must not prevent subsequent services from being spawned.
        $spawner = new RecordingSpawner([
            'svc1' => 'error',
            'svc2' => 'executed',
        ]);
        $runner = new BackgroundServiceRunnerStub(
            services: [
                self::makeService('svc1'),
                self::makeService('svc2'),
            ],
            spawner: $spawner,
        );

        $results = $runner->run();

        $this->assertSame('error', $results[0]['status']);
        $this->assertSame('executed', $results[1]['status']);
        $this->assertSame(['svc1', 'svc2'], $spawner->spawnedNames);
    }

    public function testRunAllPassesLeaseDerivedTimeoutToSpawner(): void
    {
        // The spawner must receive a wall-clock cap tied to the service's
        // computed lease so a hung child cannot block the cron slot past
        // its DB-side lease (CWE-400 mitigation from PR review).
        // execute_interval=5 -> max(MIN_LEASE=60, 5*2=10) = 60 min
        // -> (60 * 60) + LEASE_GRACE_SECONDS (60) = 3660s.
        // execute_interval=120 -> max(60, 240) = 240 min
        // -> (240 * 60) + 60 = 14460s.
        $spawner = new RecordingSpawner([
            'short_interval_svc' => 'executed',
            'long_interval_svc' => 'executed',
        ]);
        $runner = new BackgroundServiceRunnerStub(
            services: [
                self::makeService('short_interval_svc', executeInterval: 5),
                self::makeService('long_interval_svc', executeInterval: 120),
            ],
            spawner: $spawner,
        );

        $runner->run();

        $this->assertSame([3660, 14460], $spawner->timeoutArgs);
    }

    public function testRunAllNeverPassesForceToSpawner(): void
    {
        // Even if the --force flag somehow reached run(null, true),
        // the orchestrator deliberately drops it. The run-all-due
        // path must behave identically to a pure cron-equivalent
        // advance to stay consistent with the command's documented
        // "--force is ignored without --name" semantics.
        $spawner = new RecordingSpawner(['svc1' => 'executed']);
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            spawner: $spawner,
        );

        $runner->run(null, force: true);

        $this->assertSame([false], $spawner->forceArgs);
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
        int $executeInterval = 5,
        ?string $lockExpiresAt = null,
    ): array {
        // Use string values to match ADOdb runtime behavior (numeric-string)
        return [
            'name' => $name,
            'title' => $name,
            'active' => $active ? '1' : '0',
            'running' => $lockExpiresAt !== null ? '1' : '0',
            'next_run' => '2020-01-01 00:00:00',
            'execute_interval' => (string) $executeInterval,
            'function' => 'test_function_' . $name,
            'require_once' => null,
            'sort_order' => '100',
            'lock_expires_at' => $lockExpiresAt,
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
     * @param string|null $lockFailureReason Null means lock acquired; a string is the failure reason returned to run()
     * @param (\Closure(BackgroundServicesRow): void)|null $executeCallback
     */
    public function __construct(
        private readonly array $services = [],
        private readonly ?string $lockFailureReason = null,
        private readonly ?\Closure $executeCallback = null,
        ?BackgroundServiceProcessSpawner $spawner = null,
    ) {
        parent::__construct(new NullLogger(), $spawner);
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

    protected function acquireLock(array $service, bool $force): ?string
    {
        return $this->lockFailureReason;
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

/**
 * Test double for BackgroundServiceProcessSpawner that returns canned
 * per-service statuses from a fixture map and records each invocation.
 *
 * Tests use this to exercise the run-all-due orchestrator without
 * spawning actual PHP subprocesses.
 */
class RecordingSpawner implements BackgroundServiceProcessSpawner
{
    /** @var list<string> */
    public array $spawnedNames = [];

    /** @var list<bool> */
    public array $forceArgs = [];

    /** @var list<int> */
    public array $timeoutArgs = [];

    /**
     * @param array<string, string> $statusByName
     */
    public function __construct(private readonly array $statusByName)
    {
    }

    public function spawn(string $name, bool $force, int $timeoutSeconds): array
    {
        $this->spawnedNames[] = $name;
        $this->forceArgs[] = $force;
        $this->timeoutArgs[] = $timeoutSeconds;
        return ['name' => $name, 'status' => $this->statusByName[$name] ?? 'error'];
    }
}
