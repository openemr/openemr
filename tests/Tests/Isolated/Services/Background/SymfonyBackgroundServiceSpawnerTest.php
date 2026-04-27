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

use OpenEMR\Services\Background\SymfonyBackgroundServiceSpawner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;

/**
 * Covers SymfonyBackgroundServiceSpawner's error/parse paths using a
 * fake `bin/console` shell script as the child. The test injects a
 * controlled project dir and PHP binary (`/bin/sh`) so the spawner
 * shells out to a script we fully control rather than bootstrapping
 * a real OpenEMR child.
 */
#[Group('isolated')]
#[Group('background-services')]
class SymfonyBackgroundServiceSpawnerTest extends TestCase
{
    private string $fakeProjectDir;

    private string $fakeConsoleScript;

    private CapturingLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        // The spawner invokes `{phpBinary} {projectDir}/bin/console ...`.
        // By setting phpBinary to /bin/sh and pointing it at a script
        // we control, the command-line invocation becomes
        //   /bin/sh <script> background:services run --name=... --json [--force]
        // which lets the script decide how to respond per service name.
        $this->fakeProjectDir = sys_get_temp_dir() . '/oe-spawner-' . uniqid('', true);
        mkdir($this->fakeProjectDir . '/bin', 0755, true);
        $this->fakeConsoleScript = $this->fakeProjectDir . '/bin/console';
        file_put_contents($this->fakeConsoleScript, <<<'SH'
            #!/bin/sh
            # Fake console for SymfonyBackgroundServiceSpawnerTest.
            # Selects behavior based on the --name= argument. The spawner
            # passes the per-invocation nonce via OPENEMR_BG_NONCE; each
            # fixture that produces a legitimate status line echoes the
            # same nonce so the parent accepts it.
            n="${OPENEMR_BG_NONCE:-}"
            # Check for --force flag once, used by fixtures that want to
            # branch on it. Looped separately from the per-name dispatch.
            force=0
            for arg in "$@"; do
                if [ "$arg" = "--force" ]; then
                    force=1
                fi
            done
            for arg in "$@"; do
                case "$arg" in
                    --name=clean_exit_no_json)
                        exit 0
                        ;;
                    --name=exits_nonzero)
                        echo "fatal error" >&2
                        exit 137
                        ;;
                    --name=emits_executed)
                        printf '{"name":"emits_executed","status":"executed","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=prints_garbage_then_json)
                        echo "PHP Deprecated: something"
                        printf '{"name":"prints_garbage_then_json","status":"not_due","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=json_missing_status)
                        printf '{"name":"json_missing_status","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=name_mismatch)
                        # Service that prints a forged status line tagged
                        # with the right nonce but the wrong service name.
                        # Parser must still reject it on the name check.
                        printf '{"name":"not_the_expected_one","status":"executed","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=shutdown_forges_status)
                        # Simulates the CWE-345 spoofing vector: the
                        # command emits its legitimate JSON (error), then
                        # a register_shutdown_function in the service's
                        # own code prints a forged "executed" line AFTER
                        # the command's own line. The parser scans from
                        # the end, so without the nonce check the forged
                        # line would win. With the nonce check, the forged
                        # line (no/wrong nonce) is rejected and the
                        # legitimate line's "error" wins.
                        printf '{"name":"shutdown_forges_status","status":"error","nonce":"%s"}\n' "$n"
                        echo '{"name":"shutdown_forges_status","status":"executed","nonce":"forged-by-shutdown-handler"}'
                        exit 0
                        ;;
                    --name=stderr_with_control_chars)
                        # BEL, CR, and an overly long error body to
                        # exercise the log-sanitization path. Includes a
                        # trailing newline + tab in the middle so the
                        # test can assert those are escaped (not stripped)
                        # for line-oriented log backends.
                        printf 'boom\a\rline1\nline2\tcol\n' >&2
                        printf '%.0sA' $(seq 1 3000) >&2
                        exit 3
                        ;;
                    --name=floods_stdout)
                        # Writes well past the spawner's per-stream
                        # buffer cap (64KiB). Used to verify the spawner
                        # enforces the cap, terminates the child, and
                        # returns error.
                        yes A | head -c 200000
                        # Reach here only if yes is terminated by a
                        # broken pipe before we can emit the status.
                        printf '{"name":"floods_stdout","status":"executed","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=reports_force)
                        # Emits "executed" only when --force was passed,
                        # "skipped" otherwise. Lets the test confirm the
                        # spawner actually forwards --force to the child.
                        if [ "$force" = "1" ]; then
                            printf '{"name":"reports_force","status":"executed","nonce":"%s"}\n' "$n"
                        else
                            printf '{"name":"reports_force","status":"skipped","nonce":"%s"}\n' "$n"
                        fi
                        exit 0
                        ;;
                    --name=floods_stderr)
                        # Writes well past the spawner's per-stream buffer
                        # cap (64KiB) to stderr. Used to verify the cap is
                        # enforced on the stderr side too, not just stdout.
                        yes A | head -c 200000 >&2
                        # Reach here only if yes is terminated by a
                        # broken pipe before we can emit the status.
                        printf '{"name":"floods_stderr","status":"executed","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=emits_non_array_json)
                        # A line starting with `{` that does NOT decode to
                        # a JSON object (malformed) exercises the
                        # !is_array($decoded) branch in parseJsonStatus.
                        # The parser must skip past it without erroring.
                        echo '{not valid json at all'
                        printf '{"name":"emits_non_array_json","status":"executed","nonce":"%s"}\n' "$n"
                        exit 0
                        ;;
                    --name=only_non_array_json)
                        # Same as above but WITHOUT a valid trailing line,
                        # so parseJsonStatus falls through to null and the
                        # spawner returns status=error. This covers the
                        # !is_array continue-path without relying on a
                        # subsequent valid line.
                        echo '{ trailing but malformed'
                        exit 0
                        ;;
                    --name=sleeps_forever)
                        # Used only for the timeout test; the test uses
                        # a very short subprocess timeout so this
                        # doesn't actually delay the suite.
                        sleep 30
                        exit 0
                        ;;
                esac
            done
            echo "unrecognized fixture" >&2
            exit 2
            SH);
        chmod($this->fakeConsoleScript, 0755);

        $this->logger = new CapturingLogger();
    }

    protected function tearDown(): void
    {
        if (is_file($this->fakeConsoleScript)) {
            unlink($this->fakeConsoleScript);
        }
        if (is_dir($this->fakeProjectDir . '/bin')) {
            rmdir($this->fakeProjectDir . '/bin');
        }
        if (is_dir($this->fakeProjectDir)) {
            rmdir($this->fakeProjectDir);
        }
        parent::tearDown();
    }

    private function makeSpawner(): SymfonyBackgroundServiceSpawner
    {
        // Shell out to /bin/sh <fake-console> so the spawner's
        // PHP_BINARY-based invocation still produces a runnable
        // command line, and each test case can control exit codes
        // and stdout via the fake console's case statement.
        return new SymfonyBackgroundServiceSpawner(
            $this->fakeProjectDir,
            $this->logger,
            '/bin/sh',
        );
    }

    public function testExecutedStatusParsedFromJson(): void
    {
        $result = $this->makeSpawner()->spawn('emits_executed', false, 60);

        $this->assertSame(['name' => 'emits_executed', 'status' => 'executed'], $result);
        $this->assertSame([], $this->logger->warnings);
    }

    public function testStatusParsedFromTrailingJsonAmongstOtherOutput(): void
    {
        // PHP deprecation notices and similar pre-JSON stdout chatter
        // must not prevent the spawner from finding the status line.
        $result = $this->makeSpawner()->spawn('prints_garbage_then_json', false, 60);

        $this->assertSame(['name' => 'prints_garbage_then_json', 'status' => 'not_due'], $result);
    }

    public function testNonZeroExitReturnsErrorAndLogsService(): void
    {
        // Surfaces the exit()/die()/fatal case described in GH #11794:
        // the child aborted before emitting JSON, so the parent must
        // still get a well-formed result and a log entry naming the
        // offending service.
        $result = $this->makeSpawner()->spawn('exits_nonzero', false, 60);

        $this->assertSame(['name' => 'exits_nonzero', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame('exits_nonzero', $this->logger->warnings[0]['context']['service'] ?? null);
        $this->assertSame(137, $this->logger->warnings[0]['context']['exit_code'] ?? null);
    }

    public function testCleanExitWithoutJsonReturnsError(): void
    {
        // exit(0) with no JSON trailer means the child terminated
        // early (e.g. a service called exit(0) before the command's
        // normal return path). Treat as error. The result set must
        // not silently report "ran successfully" for a process we
        // have no status line from.
        $result = $this->makeSpawner()->spawn('clean_exit_no_json', false, 60);

        $this->assertSame(['name' => 'clean_exit_no_json', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
    }

    public function testMalformedJsonStatusReturnsError(): void
    {
        // JSON line that decodes but lacks a string `status` field is
        // the same failure mode as "no JSON at all": can't trust the
        // result, so flag as error with a log entry.
        $result = $this->makeSpawner()->spawn('json_missing_status', false, 60);

        $this->assertSame(['name' => 'json_missing_status', 'status' => 'error'], $result);
    }

    public function testForgedStatusLineWithWrongNameIsRejected(): void
    {
        // A service that writes `{"name":"something_else","status":"executed"}`
        // to stdout (perhaps from a shutdown handler that fires after
        // the command's own output) must not be able to spoof a
        // successful status. The parser requires name == expected
        // (CWE-345 mitigation from PR review).
        $result = $this->makeSpawner()->spawn('name_mismatch', false, 60);

        $this->assertSame(['name' => 'name_mismatch', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
    }

    public function testShutdownFunctionForgedStatusLineIsRejectedByNonceCheck(): void
    {
        // CWE-345: a service's own register_shutdown_function() fires
        // AFTER the command's legitimate JSON is written. Without the
        // nonce check, the reverse-scanning parser would find the
        // forged "executed" line (written second, appears last) first
        // and accept it. With the nonce check, the forged line has no
        // valid nonce and is skipped, so the parser falls through to
        // the command's own line — whose status is "error" in this
        // fixture — and returns that. A passing test means the forged
        // status was rejected without the forged line being accepted.
        $result = $this->makeSpawner()->spawn('shutdown_forges_status', false, 60);

        $this->assertSame(['name' => 'shutdown_forges_status', 'status' => 'error'], $result);
        $this->assertSame([], $this->logger->warnings, 'Legitimate error status should not log a spawner warning');
    }

    public function testStderrIsSanitizedAndTruncatedBeforeLogging(): void
    {
        // Subprocess stderr can contain PHI, stack traces, and control
        // characters. The spawner must strip control chars, escape
        // newlines/tabs to literal "\n"/"\t" (CWE-117: a single log
        // record must not be split across multiple lines by child
        // output), and truncate long output so one misbehaving service
        // can't flood central logs (CWE-532 mitigation from PR review).
        $result = $this->makeSpawner()->spawn('stderr_with_control_chars', false, 60);

        $this->assertSame(['name' => 'stderr_with_control_chars', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);

        $stderr = $this->logger->warnings[0]['context']['stderr'] ?? '';
        self::assertIsString($stderr);
        $this->assertStringNotContainsString("\x07", $stderr, 'BEL must be stripped');
        $this->assertStringNotContainsString("\r", $stderr, 'CR must be normalized to LF');
        $this->assertStringNotContainsString("\n", $stderr, 'Real LF must be escaped, not left embedded');
        $this->assertStringNotContainsString("\t", $stderr, 'Real TAB must be escaped, not left embedded');
        $this->assertStringContainsString('\\n', $stderr, 'LF must be rendered as literal \\n');
        $this->assertStringContainsString('\\t', $stderr, 'TAB must be rendered as literal \\t');
        $this->assertLessThanOrEqual(2100, strlen($stderr), 'Log snippet must be truncated');
        $this->assertStringContainsString('[truncated]', $stderr);
    }

    public function testServiceNameIsSanitizedInLogContext(): void
    {
        // Service names originate from the `background_services.name`
        // DB column. A misconfigured or malicious row containing
        // CR/LF/BEL must not forge multi-line log records
        // (CWE-117 mitigation from PR review). The fake console
        // doesn't recognize this name so it exits non-zero, which
        // exercises the service-name sanitization in the log context.
        $smuggled = "evil\r\nFAKE: forged line\x07";
        $result = $this->makeSpawner()->spawn($smuggled, false, 60);

        // The result's `name` field is the caller-provided name
        // unchanged; only the *logged* service field is sanitized.
        $this->assertSame($smuggled, $result['name']);
        $this->assertSame('error', $result['status']);
        $this->assertNotEmpty($this->logger->warnings);

        $loggedService = $this->logger->warnings[0]['context']['service'] ?? '';
        self::assertIsString($loggedService);
        $this->assertStringNotContainsString("\r", $loggedService);
        $this->assertStringNotContainsString("\n", $loggedService);
        $this->assertStringNotContainsString("\x07", $loggedService);
        $this->assertSame('evilFAKE: forged line', $loggedService);
    }

    public function testStdoutOverflowTerminatesChildAndReturnsError(): void
    {
        // A service that dumps unbounded output must be killed before
        // the parent buffers gigabytes of it (CWE-400 mitigation from
        // PR review). The fake console writes 200KB to stdout; the
        // spawner's 64KiB per-stream cap must trigger a stop() and an
        // error result with no JSON parsing attempted.
        $start = microtime(true);
        $result = $this->makeSpawner()->spawn('floods_stdout', false, 60);
        $elapsed = microtime(true) - $start;

        $this->assertSame(['name' => 'floods_stdout', 'status' => 'error'], $result);
        $this->assertLessThan(15.0, $elapsed, 'Spawner must kill overflowing child quickly');
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(
            'floods_stdout',
            $this->logger->warnings[0]['context']['service'] ?? null,
        );
        $this->assertSame(
            65536,
            $this->logger->warnings[0]['context']['buffer_max_bytes'] ?? null,
        );
    }

    public function testForceFlagIsForwardedToChildProcess(): void
    {
        // The spawner's `spawn($name, true, ...)` call must translate
        // to a --force argument in argv so the child command bypasses
        // the interval check. Without this, --force was set on the
        // parent's run() but silently dropped before exec.
        $withForce = $this->makeSpawner()->spawn('reports_force', true, 60);
        $withoutForce = $this->makeSpawner()->spawn('reports_force', false, 60);

        $this->assertSame(
            ['name' => 'reports_force', 'status' => 'executed'],
            $withForce,
            '--force must reach the child when the caller requests it',
        );
        $this->assertSame(
            ['name' => 'reports_force', 'status' => 'skipped'],
            $withoutForce,
            '--force must NOT reach the child when the caller does not request it',
        );
    }

    public function testStderrOverflowTerminatesChildAndReturnsError(): void
    {
        // Symmetric to the stdout overflow case: a service that dumps
        // unbounded output to stderr (e.g. a warn/notice storm) must
        // still be killed once the per-stream cap is hit, so stderr
        // alone can't exhaust parent memory (CWE-400).
        $start = microtime(true);
        $result = $this->makeSpawner()->spawn('floods_stderr', false, 60);
        $elapsed = microtime(true) - $start;

        $this->assertSame(['name' => 'floods_stderr', 'status' => 'error'], $result);
        $this->assertLessThan(15.0, $elapsed, 'Spawner must kill overflowing child quickly');
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(
            'floods_stderr',
            $this->logger->warnings[0]['context']['service'] ?? null,
        );
        $this->assertSame(
            65536,
            $this->logger->warnings[0]['context']['buffer_max_bytes'] ?? null,
        );
    }

    public function testNonArrayJsonLineIsSkippedAndLaterValidLineIsAccepted(): void
    {
        // parseJsonStatus must tolerate a line that starts with `{` but
        // doesn't decode to a JSON object (malformed JSON). The parser
        // skips it (continue) and keeps scanning; a subsequent valid
        // status line must still win.
        $result = $this->makeSpawner()->spawn('emits_non_array_json', false, 60);

        $this->assertSame(['name' => 'emits_non_array_json', 'status' => 'executed'], $result);
        $this->assertSame([], $this->logger->warnings);
    }

    public function testOnlyNonArrayJsonReturnsError(): void
    {
        // When the only `{`-prefixed line is malformed, parseJsonStatus
        // scans every line, skips each one at the !is_array($decoded)
        // branch, and returns null — the spawner then surfaces error.
        $result = $this->makeSpawner()->spawn('only_non_array_json', false, 60);

        $this->assertSame(['name' => 'only_non_array_json', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
    }

    public function testLongServiceNameIsTruncatedInLogContext(): void
    {
        // Service names come from the DB so in theory operator-
        // controlled, but pathological rows or tests inserting long
        // strings should not produce unbounded log records. The
        // SERVICE_NAME_LOG_MAX cap trims to 64 chars + a truncation
        // marker before logging (CWE-532 hygiene).
        $longName = str_repeat('a', 100);
        $result = $this->makeSpawner()->spawn($longName, false, 60);

        // The returned `name` is the caller's input unchanged; only
        // the *logged* service context is truncated.
        $this->assertSame($longName, $result['name']);
        $this->assertSame('error', $result['status']);
        $this->assertNotEmpty($this->logger->warnings);

        $loggedService = $this->logger->warnings[0]['context']['service'] ?? '';
        self::assertIsString($loggedService);
        $this->assertStringEndsWith('…[truncated]', $loggedService);
        $this->assertStringStartsWith(str_repeat('a', 64), $loggedService);
    }

    public function testTimeoutReturnsErrorAndLogsService(): void
    {
        // A child that refuses to exit within its lease-derived
        // timeout must not block the orchestrator indefinitely. The
        // spawner kills the process and returns status=error
        // (CWE-400 mitigation from PR review).
        $start = microtime(true);
        $result = $this->makeSpawner()->spawn('sleeps_forever', false, 1);
        $elapsed = microtime(true) - $start;

        $this->assertSame(['name' => 'sleeps_forever', 'status' => 'error'], $result);
        $this->assertLessThan(15.0, $elapsed, 'Spawner must enforce the timeout');
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame('sleeps_forever', $this->logger->warnings[0]['context']['service'] ?? null);
        $this->assertSame(1, $this->logger->warnings[0]['context']['timeout_seconds'] ?? null);
    }
}

/**
 * PSR-3 logger double that records warning calls for assertion.
 */
class CapturingLogger extends AbstractLogger implements LoggerInterface
{
    /** @var list<array{level: mixed, message: string|\Stringable, context: array<mixed>}> */
    public array $warnings = [];

    /**
     * @param array<mixed> $context
     */
    public function log($level, string|\Stringable $message, array $context = []): void
    {
        // PSR-3 permits $level as any scalar; CapturingLogger only needs
        // to match the literal 'warning' level, so compare after a
        // string check rather than casting mixed.
        if (is_string($level) && $level === 'warning') {
            $this->warnings[] = ['level' => $level, 'message' => $message, 'context' => $context];
        }
    }
}
