<?php

/**
 * Abstraction for launching a background service in a separate process.
 *
 * Process isolation is the only boundary that survives `exit()`, `die()`,
 * or fatal errors inside a service function. No amount of `try`/`catch`
 * in PHP can recover from them because they abort the process itself.
 * `BackgroundServiceRunner::run(null, false)` delegates to an implementation
 * of this interface for every active service so a single misbehaving
 * service cannot abort the remaining services scheduled for the same tick.
 * See GH issue #11794.
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

interface BackgroundServiceProcessSpawner
{
    /**
     * Execute a single background service in a separate process and return
     * its result.
     *
     * Implementations must NEVER let a subprocess failure propagate as an
     * exception. Any termination reason (signal, non-zero exit, timeout,
     * failure to parse child output) is converted into a result with
     * `status => 'error'` and a log entry identifying the offending
     * service. The orchestrating loop relies on this contract to advance
     * to the next service without a catch block.
     *
     * @param int $timeoutSeconds Hard wall-clock cap on how long the
     *                            subprocess is allowed to run. Must be
     *                            positive. The orchestrator derives this
     *                            from the service's computed lease so a
     *                            hung child cannot block the cron slot
     *                            past the DB-side lease it holds.
     *
     * @return array{name: string, status: string}
     */
    public function spawn(string $name, bool $force, int $timeoutSeconds): array;
}
