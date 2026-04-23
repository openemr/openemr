<?php

/**
 * Default BackgroundServiceProcessSpawner implementation. Launches each
 * service via `php bin/console background:services run --name=<name>
 * --json` using symfony/process and parses a single JSON result line
 * from the child's stdout.
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Process;

final readonly class SymfonyBackgroundServiceSpawner implements BackgroundServiceProcessSpawner
{
    private LoggerInterface $logger;

    /**
     * @param string      $projectDir Absolute path to the OpenEMR project
     *                                root (used to locate bin/console).
     * @param string      $phpBinary  Absolute path to the PHP binary the
     *                                child should run under. Defaults to
     *                                PHP_BINARY, which matches the PHP
     *                                currently running the parent,
     *                                aligning FPM/CLI extensions, INI
     *                                settings, and version.
     */
    public function __construct(
        private string $projectDir,
        ?LoggerInterface $logger = null,
        private string $phpBinary = PHP_BINARY,
    ) {
        $this->logger = $logger ?? ServiceContainer::getLogger();
    }

    public function spawn(string $name, bool $force): array
    {
        $args = [
            $this->phpBinary,
            $this->projectDir . '/bin/console',
            'background:services',
            'run',
            '--name=' . $name,
            '--json',
        ];
        if ($force) {
            $args[] = '--force';
        }

        $process = new Process($args);
        // Disable the default 60s timeout. Service runtime is bounded by
        // the lease (see BackgroundServiceRunner::computeLeaseMinutes) and
        // expected durations vary widely across services. A hung service
        // is a lease problem, not a process-timeout problem.
        $process->setTimeout(null);
        $process->run();

        $exitCode = $process->getExitCode();
        if ($exitCode !== 0) {
            // Non-zero exit is how exit(N != 0), die(), and PHP fatals
            // surface. Log the service name loudly so operators can find
            // the offender without cross-referencing stdout/stderr.
            $this->logger->warning(
                'Background service subprocess exited non-zero.',
                [
                    'service' => $name,
                    'exit_code' => $exitCode,
                    'stderr' => $process->getErrorOutput(),
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }

        $status = $this->parseJsonStatus($process->getOutput());
        if ($status === null) {
            // exit(0) without emitting the expected JSON line means the
            // child terminated cleanly mid-execution without going
            // through the command's return path. Still an isolation
            // event. Log and flag as error so the orchestrator's
            // result set reflects reality.
            $this->logger->warning(
                'Background service subprocess exited cleanly but emitted no JSON status.',
                [
                    'service' => $name,
                    'stdout' => $process->getOutput(),
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }
        return ['name' => $name, 'status' => $status];
    }

    /**
     * Locate and decode the trailing JSON status line produced by
     * `background:services run --name=<name> --json`.
     *
     * Scans from the end so any pre-bootstrap notices written to stdout
     * (deprecation warnings, misconfigured session_start, etc.) before
     * the command printed its result line are ignored.
     */
    private function parseJsonStatus(string $stdout): ?string
    {
        $lines = preg_split('/\R/', trim($stdout));
        if ($lines === false) {
            return null;
        }

        for ($i = count($lines) - 1; $i >= 0; $i--) {
            $line = trim($lines[$i]);
            if ($line === '' || $line[0] !== '{') {
                continue;
            }
            $decoded = json_decode($line, true);
            if (is_array($decoded) && isset($decoded['status']) && is_string($decoded['status'])) {
                return $decoded['status'];
            }
        }
        return null;
    }
}
