<?php

/**
 * Orchestrates execution of registered background services.
 *
 * Extracted from library/ajax/execute_background_services.php to enable
 * reuse from CLI tooling and REST API endpoints.
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

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Common\Filesystem\SafeIncludeResolver;
use OpenEMR\Core\OEGlobalsBag;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServiceRunner
{
    private ?string $currentServiceName = null;

    private bool $shutdownRegistered = false;

    /**
     * Run one or all background services.
     *
     * @param string|null $serviceName Specific service name, or null for all
     * @param bool $force Bypass interval check
     * @return list<array{name: string, status: string}> Results per service
     *   Possible status values:
     *   - 'executed'        — service ran successfully
     *   - 'skipped'         — inactive, already running (in-memory check), or manual-mode without --force
     *   - 'already_running' — another process holds the DB lock (running = 1)
     *   - 'not_due'         — interval has not elapsed yet (NOW() <= next_run)
     *   - 'error'           — exception during lock acquisition or execution
     *   - 'not_found'       — requested service name does not exist
     */
    public function run(?string $serviceName = null, bool $force = false): array
    {
        $this->registerShutdownHandler();

        if ($serviceName === '') {
            $serviceName = null;
        }

        $results = [];
        $services = $this->getServices($serviceName, $force);

        if ($serviceName !== null && $services === []) {
            return [['name' => $serviceName, 'status' => 'not_found']];
        }

        foreach ($services as $service) {
            $name = $service['name'];

            // ADOdb returns string values; use loose comparison for DB row checks
            if ($service['active'] == 0 || $service['running'] == 1) {
                $results[] = ['name' => $name, 'status' => 'skipped'];
                continue;
            }

            // Manual-mode services (execute_interval = 0) require --force
            if ($service['execute_interval'] == 0 && !$force) {
                $results[] = ['name' => $name, 'status' => 'skipped'];
                continue;
            }

            try {
                $lockFailureReason = $this->acquireLock($service, $force);
            } catch (\Throwable) {
                $results[] = ['name' => $name, 'status' => 'error'];
                continue;
            }

            if ($lockFailureReason !== null) {
                $results[] = ['name' => $name, 'status' => $lockFailureReason];
                continue;
            }

            // Only track for shutdown cleanup after lock is acquired
            $this->currentServiceName = $name;

            try {
                $this->executeService($service);
                $results[] = ['name' => $name, 'status' => 'executed'];
            } catch (\Throwable) {
                $results[] = ['name' => $name, 'status' => 'error'];
            } finally {
                $this->safeReleaseLock($name);
                $this->currentServiceName = null;
            }
        }
        return $results;
    }

    /**
     * Register a shutdown handler that releases the lock if the process exits
     * abnormally during service execution. Called automatically by run().
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
     * Attempt to acquire the running lock for a service.
     *
     * Returns null on success (lock acquired), or a reason string when the
     * lock could not be acquired:
     *   - 'already_running' — another process holds the lock (running = 1)
     *   - 'not_due'         — the service interval has not elapsed yet
     *
     * @param BackgroundServicesRow $service
     * @return string|null Null on success, reason string on failure
     */
    protected function acquireLock(array $service, bool $force): ?string
    {
        $sql = 'UPDATE background_services SET running = 1, next_run = NOW() + INTERVAL ?'
            . ' MINUTE WHERE running < 1 ' . ($force ? '' : 'AND NOW() > next_run ') . 'AND name = ?';

        QueryUtils::sqlStatementThrowException($sql, [$service['execute_interval'], $service['name']], true);

        if (QueryUtils::affectedRows() >= 1) {
            return null;
        }

        // Distinguish why the lock was not acquired: is the service already
        // running (another process holds the lock) or is it simply not yet due?
        $row = QueryUtils::querySingleRow(
            'SELECT running FROM background_services WHERE name = ?',
            [$service['name']],
            false,
        );

        if ($row === false) {
            return 'error';
        }

        if (($row['running'] ?? '0') === '1') {
            return 'already_running';
        }

        return $force ? 'already_running' : 'not_due';
    }

    protected function releaseLock(string $serviceName): void
    {
        QueryUtils::sqlStatementThrowException(
            'UPDATE background_services SET running = 0 WHERE name = ?',
            [$serviceName],
            true,
        );
    }

    /**
     * Release a lock, swallowing exceptions so cleanup failures don't break
     * orchestration or crash shutdown handlers.
     *
     * Note: If the underlying DB update to clear the `running` flag fails,
     * the lock will remain set and this runner will continue to skip the
     * service on subsequent runs. In that case, the stuck lock must be
     * cleared manually or by a separate lock-recovery mechanism.
     */
    private function safeReleaseLock(string $serviceName): void
    {
        try {
            $this->releaseLock($serviceName);
        } catch (\Throwable) {
            // Best-effort only: on failure the `running` flag remains set and
            // must be cleared manually or by a separate recovery mechanism.
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
