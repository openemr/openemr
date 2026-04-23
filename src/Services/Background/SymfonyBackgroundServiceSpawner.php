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
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

final readonly class SymfonyBackgroundServiceSpawner implements BackgroundServiceProcessSpawner
{
    /**
     * Allowlist of status values the parent accepts from the child. Anything
     * else is treated as a spoofed/corrupt line and the result is coerced to
     * 'error'. Keep aligned with BackgroundServiceRunner::runOne().
     */
    private const ALLOWED_STATUSES = [
        'executed',
        'skipped',
        'already_running',
        'not_due',
        'error',
        'not_found',
    ];

    /**
     * Cap for stderr/stdout snippets persisted in log context. Long enough
     * to capture a typical stack trace header, short enough that a service
     * dumping megabytes of output can't flood central logs.
     */
    private const LOG_SNIPPET_MAX = 2000;

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

    public function spawn(string $name, bool $force, int $timeoutSeconds): array
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
        // Wall-clock cap derived from the service's computed lease so a hung
        // child cannot block the cron slot past its DB-side lease. Idle
        // timeout mirrors the hard cap: a service that produces no output
        // for its entire lease window is indistinguishable from a hang for
        // orchestration purposes.
        $process->setTimeout($timeoutSeconds);
        $process->setIdleTimeout($timeoutSeconds);

        try {
            $process->run();
        } catch (ProcessTimedOutException) {
            // Best-effort terminate. 5s grace for SIGTERM before SIGKILL so
            // a cooperative child can flush buffers / release in-memory
            // resources; we've already exceeded the lease so we don't wait
            // indefinitely for it to clean up.
            $process->stop(5);
            $this->logger->warning(
                'Background service subprocess timed out.',
                [
                    'service' => $name,
                    'timeout_seconds' => $timeoutSeconds,
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }

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
                    'stderr' => $this->safeLogSnippet($process->getErrorOutput()),
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }

        $status = $this->parseJsonStatus($process->getOutput(), $name);
        if ($status === null) {
            // exit(0) without emitting a valid JSON status for the
            // expected service name means either the child terminated
            // cleanly mid-execution or a misbehaving service printed a
            // line we can't trust. Either way, it's an isolation event.
            // Log and flag as error so the orchestrator's result set
            // reflects reality.
            $this->logger->warning(
                'Background service subprocess exited cleanly but emitted no valid JSON status.',
                [
                    'service' => $name,
                    'stdout' => $this->safeLogSnippet($process->getOutput()),
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
     * the command printed its result line are ignored. Validates that
     * the line is tagged with the expected service name and an allowlisted
     * status value so a misbehaving service cannot spoof its own status
     * by printing a crafted JSON line before the command's own.
     */
    private function parseJsonStatus(string $stdout, string $expectedName): ?string
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
            if (!is_array($decoded)) {
                continue;
            }
            $decodedName = $decoded['name'] ?? null;
            if ($decodedName !== $expectedName) {
                continue;
            }
            $status = $decoded['status'] ?? null;
            if (!is_string($status) || !in_array($status, self::ALLOWED_STATUSES, true)) {
                continue;
            }
            return $status;
        }
        return null;
    }

    /**
     * Sanitize a subprocess output stream for inclusion in a log context.
     *
     * Strips ASCII control characters (except newline and tab) so that a
     * malicious or buggy service cannot forge log lines via CR/LF/BEL,
     * and truncates to LOG_SNIPPET_MAX bytes. Full output is not
     * preserved; operators with access to the host can inspect the
     * service directly.
     */
    private function safeLogSnippet(string $raw): string
    {
        $sanitized = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', '', $raw) ?? '';
        $sanitized = str_replace(["\r\n", "\r"], "\n", $sanitized);
        if (strlen($sanitized) > self::LOG_SNIPPET_MAX) {
            $sanitized = substr($sanitized, 0, self::LOG_SNIPPET_MAX) . '…[truncated]';
        }
        return $sanitized;
    }
}
