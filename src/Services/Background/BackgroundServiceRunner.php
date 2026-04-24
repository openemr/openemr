<?php

/**
 * Orchestrates execution of registered background services.
 *
 * Extracted from library/ajax/execute_background_services.php to enable
 * reuse from CLI tooling and REST API endpoints.
 *
 * Locking is lease-based: each acquire sets `lock_expires_at` to a future
 * timestamp and clears it on release. If a worker crashes before releasing
 * (SIGKILL, OOM, container restart, DB disconnect), the next tick atomically
 * steals the expired lease, so background services self-recover without
 * operator intervention. See GH issue #11661.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Services\Background;

use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Common\Filesystem\SafeIncludeResolver;
use OpenEMR\Core\OEGlobalsBag;
use Psr\Log\LoggerInterface;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServiceRunner
{
    /**
     * Default lease duration floor. Every acquired lease is at least this
     * long, giving a reasonable recovery window even for short-interval
     * services (e.g. interval = 1 min should not expire after 2 min).
     */
    private const MIN_LEASE_MINUTES = 60;

    /**
     * Lease duration ceiling. Caps a pathological configuration (e.g.
     * interval = 1 week) from making a stuck lock unrecoverable for an
     * unreasonable amount of time.
     */
    private const MAX_LEASE_MINUTES = 1440;

    /**
     * Slack added to the lease when computing the subprocess wall-clock
     * timeout. The child's lease expires at lease_minutes; the parent
     * waits a little longer so a child that's legitimately finishing up
     * (flushing logs, closing connections) isn't killed on the boundary.
     */
    private const LEASE_GRACE_SECONDS = 60;

    /**
     * Name of the MySQL session-level advisory lock used to ensure only
     * one run-all-due orchestrator runs concurrently across the whole
     * database. An authenticated caller that repeatedly hits the HTTP
     * run-all-due endpoint would otherwise be able to spawn
     * N-services-worth of subprocesses per request; this lock makes the
     * second concurrent invocation a no-op instead.
     *
     * GET_LOCK is session-scoped, so when the orchestrator process dies
     * (SIGKILL, container restart, fatal), the DB session closes and the
     * lock auto-releases. The name is prefixed with `openemr.` so it
     * doesn't collide with anything else using GET_LOCK in the same DB.
     */
    private const ORCHESTRATOR_LOCK_NAME = 'openemr.bg_orchestrator';

    private ?string $currentServiceName = null;

    private bool $shutdownRegistered = false;

    private readonly LoggerInterface $logger;

    public function __construct(
        ?LoggerInterface $logger = null,
        private ?BackgroundServiceProcessSpawner $spawner = null,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    /**
     * Run one or all background services.
     *
     * ## Isolation model
     *
     * - **Single service (`$serviceName !== null`):** executed inline in
     *   the current process. `acquireLock`/`executeService` failures are
     *   caught as normal exceptions, but `exit()`/`die()`/fatals in the
     *   service function will terminate this process just like any other
     *   PHP script. Callers (REST, AJAX, CLI with --name) that invoke a
     *   single named service accept that blast radius.
     * - **Run-all-due (`$serviceName === null`):** each active, due
     *   service is executed in its own subprocess via
     *   `BackgroundServiceProcessSpawner`. This is the only safe way to
     *   survive `exit()`/`die()`/fatal errors from one service. No
     *   catch block in PHP can recover from them, so process boundaries
     *   are the isolation mechanism. See GH #11794.
     *
     * @param string|null $serviceName Specific service name, or null for all
     * @param bool $force Bypass interval check
     * @return list<array{name: string, status: string}> Results per service
     *   Possible status values:
     *   - 'executed'        — service ran successfully
     *   - 'skipped'         — inactive, or manual-mode without --force
     *   - 'already_running' — another process holds an unexpired lease
     *   - 'not_due'         — interval has not elapsed yet (NOW() <= next_run)
     *   - 'error'           — exception during lock acquisition or execution,
     *                         or subprocess terminated abnormally (run-all-due)
     *   - 'not_found'       — requested service name does not exist
     */
    public function run(?string $serviceName = null, bool $force = false): array
    {
        if ($serviceName === '') {
            $serviceName = null;
        }

        if ($serviceName === null) {
            return $this->runAllDueIsolated();
        }

        $this->registerShutdownHandler();

        $services = $this->getServices($serviceName, $force);

        if ($services === []) {
            return [['name' => $serviceName, 'status' => 'not_found']];
        }

        // Single named service: always exactly one row.
        return [$this->runOne($services[0], $force)];
    }

    /**
     * Orchestrate the run-all-due path with per-service subprocess
     * isolation. Each active service scheduled on an interval is
     * delegated to the process spawner; inactive services are reported
     * as 'skipped' without spawning (skip-vs-run is trivially decidable
     * from the row, no need to pay bootstrap cost to confirm).
     *
     * The spawner is responsible for turning any abnormal subprocess
     * termination into `status => 'error'` with a log entry. This loop
     * therefore advances through every service regardless of what the
     * previous one did.
     *
     * @return list<array{name: string, status: string}>
     */
    private function runAllDueIsolated(): array
    {
        // A single authenticated caller can trigger run-all-due via the
        // HTTP endpoint. Without a global guard, N repeated requests
        // would spawn N × (active services) subprocesses concurrently;
        // each child's DB-level per-service lock would quickly reject
        // the duplicate work, but the parent would still pay the cost
        // of spawning, bootstrapping, and killing each child. The
        // orchestrator lock collapses that into a single no-op result.
        if (!$this->acquireOrchestratorLock()) {
            return [['name' => 'orchestrator', 'status' => 'already_running']];
        }

        try {
            return $this->runAllDueIsolatedUnlocked();
        } finally {
            $this->releaseOrchestratorLock();
        }
    }

    /**
     * @return list<array{name: string, status: string}>
     */
    private function runAllDueIsolatedUnlocked(): array
    {
        $services = $this->getServices(null, false);
        $results = [];
        $spawner = null;

        foreach ($services as $service) {
            // ADOdb returns string values; use loose comparison for DB row checks.
            if ($service['active'] == 0) {
                $results[] = ['name' => $service['name'], 'status' => 'skipped'];
                continue;
            }
            // Lazy-construct the spawner only when at least one active
            // service needs it. Installs with every service disabled
            // shouldn't have to resolve PHP_BINARY / project dir.
            $spawner ??= $this->resolveSpawner();
            $timeoutSeconds = $this->computeLeaseMinutes($service) * 60 + self::LEASE_GRACE_SECONDS;
            $results[] = $spawner->spawn($service['name'], false, $timeoutSeconds);
        }
        return $results;
    }

    /**
     * Try to acquire the DB-wide orchestrator lock. Returns true on
     * success (caller must release), false if another orchestrator is
     * already running. Uses a session-level advisory lock (GET_LOCK)
     * with a zero-second wait so the second concurrent caller fails
     * fast rather than queueing.
     */
    protected function acquireOrchestratorLock(): bool
    {
        $row = QueryUtils::querySingleRow(
            'SELECT GET_LOCK(?, 0) AS got',
            [self::ORCHESTRATOR_LOCK_NAME],
            false,
        );
        // GET_LOCK returns 1 on acquire, 0 on timeout, NULL on error.
        // ADOdb may return either the native int or a numeric string
        // depending on driver mode; accept both forms without casting
        // through mixed (which PHPStan flags at level 9).
        if (!is_array($row)) {
            return false;
        }
        $got = $row['got'] ?? null;
        return $got === 1 || $got === '1';
    }

    /**
     * Release the orchestrator lock. Swallows errors because the lock
     * auto-releases when the DB session closes; a failure here would
     * at worst delay the next orchestrator tick by up to one cron
     * interval (when FPM recycles the connection).
     */
    protected function releaseOrchestratorLock(): void
    {
        try {
            QueryUtils::fetchRecordsNoLog(
                'SELECT RELEASE_LOCK(?) AS released',
                [self::ORCHESTRATOR_LOCK_NAME],
            );
        } catch (SqlQueryException) {
            // Best-effort: GET_LOCK is session-scoped and will auto-release
            // when the PHP process (and therefore the DB connection) ends.
        }
    }

    /**
     * Execute one already-fetched service row inline in the current process.
     *
     * Extracted from run() so that the run-all-due orchestrator can
     * isolate each service via subprocess spawning while the single-
     * named-service path continues to run inline. Called by the child
     * side of a spawned subprocess (via run() with --name), not directly
     * by the parent orchestrator.
     *
     * @param BackgroundServicesRow $service
     * @return array{name: string, status: string}
     */
    private function runOne(array $service, bool $force): array
    {
        $name = $service['name'];

        // ADOdb returns string values; use loose comparison for DB row checks.
        // Note: the `running` column is not consulted here. acquireLock()
        // is authoritative and can steal a lease left by a crashed worker.
        if ($service['active'] == 0) {
            return ['name' => $name, 'status' => 'skipped'];
        }

        // Manual-mode services (execute_interval = 0) require --force
        if ($service['execute_interval'] == 0 && !$force) {
            return ['name' => $name, 'status' => 'skipped'];
        }

        try {
            $lockFailureReason = $this->acquireLock($service, $force);
        } catch (SqlQueryException) {
            return ['name' => $name, 'status' => 'error'];
        }

        if ($lockFailureReason !== null) {
            return ['name' => $name, 'status' => $lockFailureReason];
        }

        // Only track for shutdown cleanup after lock is acquired
        $this->currentServiceName = $name;

        try {
            $this->executeService($service);
            return ['name' => $name, 'status' => 'executed'];
        } catch (\Throwable) {
            return ['name' => $name, 'status' => 'error'];
        } finally {
            $this->safeReleaseLock($name);
            $this->currentServiceName = null;
        }
    }

    /**
     * Return the configured spawner, constructing the default
     * SymfonyBackgroundServiceSpawner lazily if one was not injected.
     *
     * Kept separate so tests can inject a fake spawner via the
     * constructor and avoid any PHP_BINARY / project-dir resolution.
     */
    private function resolveSpawner(): BackgroundServiceProcessSpawner
    {
        if ($this->spawner === null) {
            $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
            $this->spawner = new SymfonyBackgroundServiceSpawner($projectDir, $this->logger);
        }
        return $this->spawner;
    }

    /**
     * Register a shutdown handler that releases the lock if the process exits
     * abnormally during service execution. Called automatically by run().
     *
     * Shutdown handlers do NOT fire on SIGKILL, OOM kill, container restart,
     * host crash, or fatal DB connection loss. For those cases, the lease
     * expires naturally and acquireLock() steals it on the next tick.
     */
    public function registerShutdownHandler(): void
    {
        if ($this->shutdownRegistered) {
            return;
        }
        $this->shutdownRegistered = true;

        register_shutdown_function(function (): void {
            if ($this->currentServiceName !== null) {
                $this->safeReleaseLock($this->currentServiceName);
                $this->currentServiceName = null;
            }
        });
    }

    /**
     * @return list<BackgroundServicesRow>
     */
    protected function getServices(?string $serviceName, bool $force): array
    {
        // When a specific service is requested, always fetch by name so manual-mode
        // services (execute_interval = 0) are distinguished from truly missing ones.
        if ($serviceName !== null && $serviceName !== '') {
            /** @var list<BackgroundServicesRow> */
            return QueryUtils::fetchRecordsNoLog(
                'SELECT * FROM background_services WHERE name = ?',
                [$serviceName],
            );
        }

        $sql = 'SELECT * FROM background_services WHERE ' . ($force ? '1' : 'execute_interval > 0');

        /** @var list<BackgroundServicesRow> */
        return QueryUtils::fetchRecordsNoLog($sql . ' ORDER BY sort_order', []);
    }

    /**
     * Attempt to acquire a lease on the service.
     *
     * The acquire is atomic: the UPDATE matches only when no lease is held,
     * or the existing lease has expired. An expired lease is stolen (a
     * previous worker crashed before releasing) and a warning is logged so
     * operators get a signal instead of silent self-healing.
     *
     * Returns null on success (lock acquired), or a reason string when the
     * lock could not be acquired:
     *   - 'already_running' — another process holds an unexpired lease
     *   - 'not_due'         — the service interval has not elapsed yet
     *
     * @param BackgroundServicesRow $service
     * @return string|null Null on success, reason string on failure
     */
    protected function acquireLock(array $service, bool $force): ?string
    {
        $leaseMinutes = $this->computeLeaseMinutes($service);
        // Best-effort "prior lease" signal reused from the row we already
        // have. If the value turns out to be stale (another worker cleared
        // or acquired between our fetch and the UPDATE below), the only
        // cost is a missed or extra warning log — the atomic UPDATE still
        // arbitrates who wins. A dedicated pre-read would not be fully
        // race-free either, and it costs an extra round-trip per tick.
        $priorExpiry = $service['lock_expires_at'];

        // Atomic acquire-or-steal. The UPDATE matches only when:
        //   - no lease is held (lock_expires_at IS NULL), or
        //   - the existing lease has expired (prior worker crashed)
        // and, unless --force, the service is due (NOW() > next_run).
        $sql = <<<'SQL'
            UPDATE background_services
               SET running = 1,
                   lock_expires_at = NOW() + INTERVAL ? MINUTE,
                   next_run = NOW() + INTERVAL ? MINUTE
             WHERE name = ?
               AND (lock_expires_at IS NULL OR lock_expires_at < NOW())
            SQL;
        if (!$force) {
            $sql .= ' AND NOW() > next_run';
        }

        QueryUtils::sqlStatementThrowException(
            $sql,
            [$leaseMinutes, (int) $service['execute_interval'], $service['name']],
            true,
        );

        if (QueryUtils::affectedRows() >= 1) {
            if ($priorExpiry !== null) {
                // We stole a lease from a crashed worker. Operators should
                // see a signal, not silent self-healing — almost always this
                // indicates an OOM kill, SIGKILL, or container restart.
                $this->logger->warning(
                    'Background service lease recovered from crashed worker.',
                    [
                        'service' => $service['name'],
                        'prior_lease_expired_at' => $priorExpiry,
                    ],
                );
            }
            return null;
        }

        // Distinguish why the UPDATE failed: is a LIVE lease held, or
        // is the lease expired/null and the service simply not yet due?
        // Checking `lock_expires_at > NOW()` in SQL ensures an expired
        // lease does not get misreported as 'already_running' when the
        // true reason is 'not_due'.
        $liveLease = QueryUtils::querySingleRow(
            <<<'SQL'
            SELECT 1 AS live
              FROM background_services
             WHERE name = ? AND lock_expires_at > NOW()
            SQL,
            [$service['name']],
            false,
        );
        if (is_array($liveLease)) {
            return 'already_running';
        }
        return $force ? 'already_running' : 'not_due';
    }

    /**
     * Release the lease for a service by clearing `lock_expires_at` (and
     * the legacy `running` flag).
     *
     * @throws \OpenEMR\Common\Database\SqlQueryException when the UPDATE
     *         cannot be executed — e.g. the DB connection is dead, the
     *         table/column is missing, or a deadlock aborted the statement.
     *         Orchestration callers that must survive cleanup failures
     *         should call safeReleaseLock() instead.
     */
    protected function releaseLock(string $serviceName): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE background_services SET running = 0, lock_expires_at = NULL WHERE name = ?',
            [$serviceName],
            true,
        );
    }

    /**
     * Compute the lease duration for a service. At least MIN_LEASE_MINUTES,
     * at most MAX_LEASE_MINUTES, otherwise 2x the execution interval so that
     * a normal run comfortably fits inside its lease.
     *
     * @param BackgroundServicesRow $service
     */
    private function computeLeaseMinutes(array $service): int
    {
        $proposed = max(self::MIN_LEASE_MINUTES, ((int) $service['execute_interval']) * 2);
        return min($proposed, self::MAX_LEASE_MINUTES);
    }

    /**
     * Release a lock, swallowing the expected cleanup-path failures from
     * `releaseLock()` so they don't break orchestration or crash shutdown
     * handlers. Specifically, this absorbs:
     *
     *   - `SqlQueryException` — transient DB errors (dropped connection,
     *     deadlock, lock-wait timeout) that happen mid-cleanup.
     *   - `\Error` subclasses from QueryUtils initialization during a
     *     fatal shutdown (PDO gone, container half-torn-down, etc.)
     *     where the runtime is already unwinding.
     *
     * The catch is `\Throwable` intentionally — shutdown handlers run in
     * contexts where even a TypeError must not propagate, and losing the
     * release is not a correctness problem: the lease expires on its own
     * and the next tick recovers it.
     */
    private function safeReleaseLock(string $serviceName): void
    {
        try {
            $this->releaseLock($serviceName);
        } catch (\Throwable) {
            // Best-effort only: the lease expires naturally and is stolen
            // on the next tick, so no operator intervention is required.
        }
    }

    /**
     * @param BackgroundServicesRow $service
     */
    protected function executeService(array $service): void
    {
        $requireOnce = $service['require_once'];
        if ($requireOnce !== null && $requireOnce !== '') {
            $projectDir = OEGlobalsBag::getInstance()->getProjectDir();
            $resolvedPath = SafeIncludeResolver::resolve($projectDir, ltrim($requireOnce, '/'));
            if ($resolvedPath === false) {
                throw new UnsafeIncludePathException(sprintf(
                    'Background service "%s" has an invalid require_once path.',
                    $service['name'],
                ));
            }

            require_once($resolvedPath);
        }

        $function = $service['function'];
        if (!function_exists($function)) {
            throw new \RuntimeException(sprintf(
                'Background service "%s" is misconfigured: function "%s" does not exist.',
                $service['name'],
                $function,
            ));
        }

        $function();
    }
}
