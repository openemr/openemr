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

use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceProcessSpawner;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use OpenEMR\Services\Background\SymfonyBackgroundServiceSpawner;
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

    public function testRunAllSkipsWhenOrchestratorLockHeld(): void
    {
        // CWE-400 mitigation from PR review: run-all-due is reachable
        // from an authenticated HTTP endpoint; repeated invocations
        // must not multiply subprocess spawns. A second concurrent
        // orchestrator returns a single "already_running" no-op row
        // rather than re-spawning each active service.
        $spawner = new RecordingSpawner(['svc1' => 'executed']);
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            spawner: $spawner,
            orchestratorLockAcquirable: false,
        );

        $results = $runner->run();

        $this->assertSame(
            [['name' => 'orchestrator', 'status' => 'already_running']],
            $results,
        );
        $this->assertSame([], $spawner->spawnedNames, 'No subprocesses must be spawned when the orchestrator lock is held');
        $this->assertSame(0, $runner->orchestratorLockReleases, 'Must not release a lock we did not acquire');
    }

    public function testRunAllAcquiresAndReleasesOrchestratorLock(): void
    {
        // Paired with the previous test: the happy path acquires the
        // orchestrator lock, spawns children, and releases on the way
        // out regardless of per-service outcomes. The release is in a
        // finally block so an exception in the inner loop still frees
        // the lock for the next cron tick.
        $spawner = new RecordingSpawner(['svc1' => 'executed']);
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            spawner: $spawner,
        );

        $runner->run();

        $this->assertSame(1, $runner->orchestratorLockAcquires);
        $this->assertSame(1, $runner->orchestratorLockReleases);
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

    public function testRunTreatsEmptyStringServiceNameAsRunAllDue(): void
    {
        // The HTTP layer (and some CLI callers) can't always distinguish
        // "no service name given" from "service name given as empty
        // string". Both must funnel into the run-all-due path; any other
        // handling would either spawn no subprocesses or try to look up
        // a zero-length service name.
        $spawner = new RecordingSpawner(['svc1' => 'executed']);
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            spawner: $spawner,
        );

        $results = $runner->run('');

        // Run-all-due path was taken (single active service spawned)
        // rather than the single-named path (which would have returned
        // 'not_found' for the empty-string lookup).
        $this->assertSame([['name' => 'svc1', 'status' => 'executed']], $results);
        $this->assertSame(['svc1'], $spawner->spawnedNames);
    }

    public function testRunOneReturnsErrorWhenAcquireLockThrowsSqlQueryException(): void
    {
        // acquireLock raises SqlQueryException on transient DB failure
        // (deadlock, dropped connection, lock-wait timeout). The runOne
        // catch must surface that as status=error rather than
        // propagating out of the orchestrator and aborting the
        // remaining services in the same tick.
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            acquireLockThrows: new SqlQueryException(
                sqlStatement: 'UPDATE background_services …',
                message: 'db died',
            ),
        );

        $results = $runner->run('svc1');

        $this->assertSame([['name' => 'svc1', 'status' => 'error']], $results);
        // The lock was never acquired, so releaseLock must not be called.
        // A release against an un-acquired lease would be wrong: it could
        // free another worker's in-flight lease if two stubs shared state.
        $this->assertSame([], $runner->releasedLocks);
    }

    public function testResolveSpawnerLazilyConstructsDefaultSymfonySpawner(): void
    {
        // When no spawner is injected, the runner must defer constructing
        // the default SymfonyBackgroundServiceSpawner until at least one
        // active service needs it. This avoids resolving PHP_BINARY /
        // project dir on an all-disabled install. Verified by invoking
        // the private resolveSpawner() via reflection and asserting the
        // returned instance is memoized across calls.
        $priorFileroot = $GLOBALS['fileroot'] ?? null;
        $GLOBALS['fileroot'] = sys_get_temp_dir();
        try {
            $runner = new BackgroundServiceRunner(new NullLogger());
            $method = new \ReflectionMethod($runner, 'resolveSpawner');
            $first = $method->invoke($runner);
            $second = $method->invoke($runner);

            $this->assertInstanceOf(SymfonyBackgroundServiceSpawner::class, $first);
            $this->assertSame($first, $second, 'resolveSpawner must memoize so PHP_BINARY/project dir are resolved at most once');
        } finally {
            if ($priorFileroot === null) {
                unset($GLOBALS['fileroot']);
            } else {
                $GLOBALS['fileroot'] = $priorFileroot;
            }
        }
    }

    public function testResolveSpawnerReturnsInjectedSpawnerWithoutConstructingDefault(): void
    {
        // When a spawner is injected via the constructor, resolveSpawner
        // must return it unchanged — never fall through to the default
        // construction path (which would resolve PHP_BINARY and project
        // dir, needlessly).
        $injected = new RecordingSpawner([]);
        $runner = new BackgroundServiceRunner(new NullLogger(), $injected);
        $method = new \ReflectionMethod($runner, 'resolveSpawner');

        $this->assertSame($injected, $method->invoke($runner));
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

    public int $orchestratorLockAcquires = 0;

    public int $orchestratorLockReleases = 0;

    /**
     * @param list<BackgroundServicesRow> $services
     * @param string|null $lockFailureReason Null means lock acquired; a string is the failure reason returned to run()
     * @param (\Closure(BackgroundServicesRow): void)|null $executeCallback
     * @param bool $orchestratorLockAcquirable False simulates a second concurrent orchestrator attempt
     * @param \Throwable|null $acquireLockThrows Exception to throw from acquireLock()
     *        (simulates DB errors during lease acquire). When non-null, takes
     *        precedence over $lockFailureReason.
     */
    public function __construct(
        private readonly array $services = [],
        private readonly ?string $lockFailureReason = null,
        private readonly ?\Closure $executeCallback = null,
        ?BackgroundServiceProcessSpawner $spawner = null,
        private readonly bool $orchestratorLockAcquirable = true,
        private readonly ?\Throwable $acquireLockThrows = null,
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
        if ($this->acquireLockThrows !== null) {
            throw $this->acquireLockThrows;
        }
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

    protected function acquireOrchestratorLock(): bool
    {
        $this->orchestratorLockAcquires++;
        return $this->orchestratorLockAcquirable;
    }

    protected function releaseOrchestratorLock(): void
    {
        $this->orchestratorLockReleases++;
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
