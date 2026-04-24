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

    /**
     * Cap on in-memory buffering of each of the child's output streams.
     * Symfony Process's default buffering grows unbounded; a misbehaving
     * service that writes gigabytes to stdout/stderr would force the
     * parent to allocate all of it before we ever look at it. The parent
     * only needs a small tail of each stream (final JSON line, stack
     * trace header) so we stream-read with a ceiling and stop the child
     * on overflow. 64KiB is large enough to cover a verbose stack trace
     * header but small enough to be cheap.
     */
    private const BUFFER_MAX_BYTES = 65536;

    /**
     * Cap on how many characters of the service name are persisted in
     * log context. Service names originate from the DB so in theory they
     * are operator-controlled, but treating them as untrusted for logging
     * lets this class stay defensible in isolation.
     */
    private const SERVICE_NAME_LOG_MAX = 64;

    /**
     * Env var the parent passes to the child carrying a per-invocation
     * nonce. The child command emits the nonce as part of its JSON
     * result and the parent rejects any JSON line whose nonce doesn't
     * match. This closes the spoofing window where a
     * `register_shutdown_function()` in the service's code prints a
     * forged `{name, status}` line AFTER the command's legitimate one;
     * without the nonce check the reverse-scanning parser would find
     * the forged line first (CWE-345).
     */
    private const NONCE_ENV_VAR = 'OPENEMR_BG_NONCE';

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

        // Fresh nonce per invocation. 128 bits of randomness is enough to
        // make guessing infeasible; the child reads it from env and echoes
        // it in the JSON result, and the parent accepts the result only if
        // the nonce matches.
        $nonce = bin2hex(random_bytes(16));

        // Symfony Process merges the supplied env with the parent's env by
        // default (unlike the raw `proc_open` contract). So passing just
        // the nonce here is enough; PATH, HOME, database credentials, etc.
        // all still propagate.
        $process = new Process($args, env: [self::NONCE_ENV_VAR => $nonce]);
        // Wall-clock cap derived from the service's computed lease so a hung
        // child cannot block the cron slot past its DB-side lease. Idle
        // timeout mirrors the hard cap: a service that produces no output
        // for its entire lease window is indistinguishable from a hang for
        // orchestration purposes.
        $process->setTimeout($timeoutSeconds);
        $process->setIdleTimeout($timeoutSeconds);

        // Stream each pipe into a local buffer with a hard per-stream
        // ceiling. Symfony's internal buffering is unbounded, so without
        // this a child dumping gigabytes of output would force the parent
        // to allocate all of it. On overflow we kill the child immediately
        // (stop(0)): we already have enough bytes for diagnostics, and
        // continuing to read wastes CPU and RAM on output we'll never use.
        $stdout = '';
        $stderr = '';
        $overflowed = false;
        $callback = function (string $type, string $buffer) use (&$stdout, &$stderr, &$overflowed, $process): void {
            if ($type === Process::OUT) {
                if (strlen($stdout) < self::BUFFER_MAX_BYTES) {
                    $stdout .= $buffer;
                    if (strlen($stdout) >= self::BUFFER_MAX_BYTES) {
                        $overflowed = true;
                        $process->stop(0);
                    }
                }
            } elseif ($type === Process::ERR) {
                if (strlen($stderr) < self::BUFFER_MAX_BYTES) {
                    $stderr .= $buffer;
                    if (strlen($stderr) >= self::BUFFER_MAX_BYTES) {
                        $overflowed = true;
                        $process->stop(0);
                    }
                }
            }
        };

        try {
            $process->run($callback);
        } catch (ProcessTimedOutException) {
            // Best-effort terminate. 5s grace for SIGTERM before SIGKILL so
            // a cooperative child can flush buffers / release in-memory
            // resources; we've already exceeded the lease so we don't wait
            // indefinitely for it to clean up.
            $process->stop(5);
            $this->logger->warning(
                'Background service subprocess timed out.',
                [
                    'service' => $this->safeServiceName($name),
                    'timeout_seconds' => $timeoutSeconds,
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }

        if ($overflowed) {
            // Child exceeded the per-stream output cap. Treat as an
            // isolation event: the subprocess was killed mid-stream, so
            // the remaining output and (most likely) the final JSON
            // status line are lost. Log and flag as error.
            $this->logger->warning(
                'Background service subprocess exceeded output buffer cap and was terminated.',
                [
                    'service' => $this->safeServiceName($name),
                    'buffer_max_bytes' => self::BUFFER_MAX_BYTES,
                    'stderr' => $this->safeLogSnippet($stderr),
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
                    'service' => $this->safeServiceName($name),
                    'exit_code' => $exitCode,
                    'stderr' => $this->safeLogSnippet($stderr),
                ],
            );
            return ['name' => $name, 'status' => 'error'];
        }

        $status = $this->parseJsonStatus($stdout, $name, $nonce);
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
                    'service' => $this->safeServiceName($name),
                    'stdout' => $this->safeLogSnippet($stdout),
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
     * the line is tagged with the expected service name, an allowlisted
     * status value, AND the per-invocation nonce the parent passed to the
     * child via env. Without the nonce check a `register_shutdown_function`
     * in the service's own code could print a forged status line AFTER
     * the command's legitimate one and win the reverse scan (CWE-345).
     */
    private function parseJsonStatus(string $stdout, string $expectedName, string $expectedNonce): ?string
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
            // hash_equals is not strictly required here (the nonce is
            // freshly generated, never persisted, and not a secret
            // protecting anything else) but costs little and communicates
            // intent: the comparison is for authentication, not lookup.
            $decodedNonce = $decoded['nonce'] ?? null;
            if (!is_string($decodedNonce) || !hash_equals($expectedNonce, $decodedNonce)) {
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
     * Strips ASCII control characters (except newline and tab), then
     * escapes remaining newlines and tabs to their literal `\n`/`\t`
     * forms so a single call to a line-oriented log backend (syslog,
     * journald, plain-text files) cannot be split across multiple
     * records by embedded whitespace. Truncates to LOG_SNIPPET_MAX
     * bytes. Full output is not preserved; operators with access to
     * the host can inspect the service directly.
     */
    private function safeLogSnippet(string $raw): string
    {
        $sanitized = preg_replace('/[\x00-\x08\x0B-\x1F\x7F]/', '', $raw) ?? '';
        $sanitized = str_replace(["\r\n", "\r"], "\n", $sanitized);
        // Escape after control-char strip/normalize so the only newlines
        // and tabs still present are the legitimate ones from child
        // output, and every one of them becomes visible as a literal
        // `\n`/`\t` in the log record rather than a control byte.
        $sanitized = str_replace(["\n", "\t"], ['\\n', '\\t'], $sanitized);
        if (strlen($sanitized) > self::LOG_SNIPPET_MAX) {
            $sanitized = substr($sanitized, 0, self::LOG_SNIPPET_MAX) . '…[truncated]';
        }
        return $sanitized;
    }

    /**
     * Sanitize a service name for inclusion in a log context.
     *
     * Service names come from the `background_services.name` column,
     * which is operator-controlled in normal operation. Treat them as
     * untrusted for logging anyway: strip all ASCII control characters
     * (including newline and tab) and truncate to SERVICE_NAME_LOG_MAX
     * so a misconfigured row can't smuggle a forged log line into the
     * warning record. Unlike `safeLogSnippet`, no embedded whitespace
     * is preserved since a legitimate service name never contains any.
     */
    private function safeServiceName(string $name): string
    {
        $sanitized = preg_replace('/[\x00-\x1F\x7F]/', '', $name) ?? '';
        if (strlen($sanitized) > self::SERVICE_NAME_LOG_MAX) {
            $sanitized = substr($sanitized, 0, self::SERVICE_NAME_LOG_MAX) . '…[truncated]';
        }
        return $sanitized;
    }
}
