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

/**
 * End-to-end subprocess tests for SymfonyBackgroundServiceSpawner using
 * a real PHP child. Complements SymfonyBackgroundServiceSpawnerTest
 * (which exercises parser/logging logic against a /bin/sh fake) by
 * covering the PHP-specific behaviors a bash fixture cannot:
 *
 *   - OPENEMR_BG_NONCE env-var propagation through Symfony Process into
 *     the child's getenv() call.
 *   - Real `die()` vs `exit(N)` vs uncaught exception exit-code
 *     semantics, which surface as different branches in the spawner.
 *   - register_shutdown_function() actually firing after the command's
 *     legitimate output — the precise CWE-345 spoofing vector the
 *     nonce protocol is designed to close.
 *   - JSON_THROW_ON_ERROR actually raising a JsonException at
 *     runtime and propagating out to a non-zero exit.
 *   - Real PHP stdio buffering against the spawner's per-stream cap.
 *
 * The child runs under PHP_BINARY so it uses the same PHP build as the
 * test suite — the same alignment production relies on when the
 * orchestrator spawns `PHP_BINARY bin/console`.
 */
#[Group('isolated')]
#[Group('background-services')]
class SymfonyBackgroundServiceSpawnerPhpChildTest extends TestCase
{
    private string $fakeProjectDir;

    private string $fakeConsoleScript;

    private CapturingLogger $logger;

    protected function setUp(): void
    {
        parent::setUp();
        // The spawner invokes `{phpBinary} {projectDir}/bin/console ...`.
        // We leave phpBinary at its default (PHP_BINARY) and point it
        // at a real PHP fixture script that dispatches on the --name=
        // argument, so each test case can exercise a different PHP-
        // level misbehavior against the actual PHP runtime.
        $this->fakeProjectDir = sys_get_temp_dir() . '/oe-spawner-php-' . uniqid('', true);
        mkdir($this->fakeProjectDir . '/bin', 0755, true);
        $this->fakeConsoleScript = $this->fakeProjectDir . '/bin/console';
        file_put_contents($this->fakeConsoleScript, <<<'PHP_WRAP'
        <?php
        // Fake console for SymfonyBackgroundServiceSpawnerPhpChildTest.
        // Each --name= value exercises a different PHP-level behavior
        // the bash-scripted fake cannot express: real exceptions,
        // register_shutdown_function, JSON_THROW_ON_ERROR, etc.
        declare(strict_types=1);

        $name = '';
        foreach ($argv as $arg) {
            if (str_starts_with($arg, '--name=')) {
                $name = substr($arg, strlen('--name='));
                break;
            }
        }
        $nonceEnv = getenv('OPENEMR_BG_NONCE');
        $nonce = is_string($nonceEnv) ? $nonceEnv : '';

        $emit = function (string $emitName, string $status) use ($nonce): void {
            echo json_encode(
                ['name' => $emitName, 'status' => $status, 'nonce' => $nonce],
                JSON_THROW_ON_ERROR,
            ), "\n";
        };

        switch ($name) {
            case 'ok':
                // Baseline: real PHP reads the env var, encodes JSON,
                // and emits a valid status line. Proves the full
                // nonce protocol works end-to-end through Symfony
                // Process, not just through the bash test's echo.
                $emit($name, 'executed');
                exit(0);

            case 'dies_without_json':
                // die($msg) prints $msg to stdout and exits 0. A
                // service that calls die("something went wrong")
                // therefore produces a clean exit with no JSON
                // trailer, which the spawner must coerce to error.
                die("service called die()\n");

            case 'exits_nonzero':
                // Mirrors GH #11794: a service calls exit(N != 0)
                // before the command's normal return path runs.
                // The spawner's non-zero exit branch must coerce
                // to error and log the service name.
                fwrite(STDERR, "service aborting with exit(3)\n");
                exit(3);

            case 'throws_uncaught':
                // Uncaught exception bubbles to PHP's default handler
                // which prints to stderr and exits 255. Nothing in
                // the child catches \Throwable, so this is the
                // failure mode a register_shutdown_function wrapper
                // inside the real command cannot silently swallow.
                throw new \RuntimeException('service threw uncaught exception');

            case 'shutdown_forges_status':
                // CWE-345: register_shutdown_function fires AFTER
                // the command's legitimate output is written. A
                // naive reverse-scanning parser would prefer the
                // forged line (written last, appears last). The
                // parent's nonce check rejects the forged line
                // because the forge cannot know the per-invocation
                // nonce (random_bytes, never persisted).
                register_shutdown_function(function () use ($name): void {
                    echo json_encode([
                        'name' => $name,
                        'status' => 'executed',
                        'nonce' => 'forged-by-shutdown-handler',
                    ]), "\n";
                });
                $emit($name, 'error');
                exit(0);

            case 'floods_stdout':
                // Writes past the spawner's per-stream cap (64KiB)
                // using real PHP stdio. The spawner must kill the
                // child and return error without attempting to
                // parse the truncated stream.
                for ($i = 0; $i < 200; $i++) {
                    echo str_repeat('A', 1024);
                }
                // Unreachable once the cap fires: spawner calls
                // stop(0) which SIGKILLs immediately.
                $emit($name, 'executed');
                exit(0);

            case 'json_throws_from_non_utf8':
                // Simulates the CWE-20 case the production command
                // now guards against: a status value containing
                // invalid UTF-8 cannot be encoded. With
                // JSON_THROW_ON_ERROR (as emitJsonResult uses),
                // the uncaught JsonException forces exit != 0 so
                // the parent observes a real error instead of a
                // silent empty line.
                json_encode(
                    ['name' => $name, 'status' => "\xFF\xFE"],
                    JSON_THROW_ON_ERROR,
                );
                exit(0);

            default:
                fwrite(STDERR, "unrecognized fixture: $name\n");
                exit(2);
        }
        PHP_WRAP);

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
        // Leave phpBinary at its default (PHP_BINARY) so the fixture
        // runs under the same PHP build as the tests, matching what
        // production does.
        return new SymfonyBackgroundServiceSpawner(
            $this->fakeProjectDir,
            $this->logger,
        );
    }

    public function testNonceIsPropagatedAndStatusParsedFromRealPhpChild(): void
    {
        // End-to-end baseline: parent generates nonce, child reads it
        // via getenv(), echoes it in JSON, parent verifies with
        // hash_equals and accepts the status. A failure here means
        // either Symfony Process isn't propagating env vars the way
        // the spawner assumes, or the nonce protocol diverges between
        // parent and child.
        $result = $this->makeSpawner()->spawn('ok', false, 60);

        $this->assertSame(['name' => 'ok', 'status' => 'executed'], $result);
        $this->assertSame([], $this->logger->warnings);
    }

    public function testDieWithoutJsonIsReportedAsMissingStatus(): void
    {
        // `die("msg")` exits 0 with msg on stdout. The spawner's
        // "no valid JSON" branch must coerce this to error and log
        // the service name, so the orchestrator result set is
        // honest about the child's silent abort.
        $result = $this->makeSpawner()->spawn('dies_without_json', false, 60);

        $this->assertSame(['name' => 'dies_without_json', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(
            'dies_without_json',
            $this->logger->warnings[0]['context']['service'] ?? null,
        );
    }

    public function testExitNonzeroFromRealPhpChildIsReportedAsError(): void
    {
        // Matches the GH #11794 failure mode (exit/die/fatal before
        // the command's legitimate return) but with a real PHP child
        // instead of a bash `exit 137`. Confirms PHP's exit-code
        // surfacing lines up with Process::getExitCode() as the
        // spawner expects.
        $result = $this->makeSpawner()->spawn('exits_nonzero', false, 60);

        $this->assertSame(['name' => 'exits_nonzero', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(3, $this->logger->warnings[0]['context']['exit_code'] ?? null);
    }

    public function testUncaughtExceptionFromRealPhpChildIsReportedAsError(): void
    {
        // An uncaught exception in the child invokes PHP's default
        // handler and exits 255. The spawner treats this the same as
        // any other non-zero exit. A service that threw without the
        // subprocess boundary would take down the orchestrator with
        // it under the old inline runner.
        $result = $this->makeSpawner()->spawn('throws_uncaught', false, 60);

        $this->assertSame(['name' => 'throws_uncaught', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(255, $this->logger->warnings[0]['context']['exit_code'] ?? null);
    }

    public function testShutdownFunctionForgedStatusLineIsRejectedWithRealRegister(): void
    {
        // CWE-345: the forged line is produced by a real PHP
        // register_shutdown_function, which runs after the command's
        // legitimate stdout is flushed. The parent's reverse scan
        // therefore sees the forged line *first*. The nonce check
        // rejects it (the forge uses a bogus nonce string) and the
        // parser continues to the legitimate line, whose status is
        // "error" in this fixture. A passing test means the forged
        // "executed" status was not accepted.
        $result = $this->makeSpawner()->spawn('shutdown_forges_status', false, 60);

        $this->assertSame(['name' => 'shutdown_forges_status', 'status' => 'error'], $result);
        $this->assertSame(
            [],
            $this->logger->warnings,
            'Legitimate error status should not log a spawner warning',
        );
    }

    public function testFloodedStdoutFromRealPhpChildTerminatesChildAndReturnsError(): void
    {
        // Exercises the BUFFER_MAX_BYTES (64KiB) cap against real PHP
        // stdio instead of `yes A | head`. The spawner must kill the
        // child once its stdout buffer reaches the cap, regardless
        // of whether the child ever emits a JSON status line.
        $start = microtime(true);
        $result = $this->makeSpawner()->spawn('floods_stdout', false, 60);
        $elapsed = microtime(true) - $start;

        $this->assertSame(['name' => 'floods_stdout', 'status' => 'error'], $result);
        $this->assertLessThan(15.0, $elapsed, 'Spawner must kill overflowing child quickly');
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(
            65536,
            $this->logger->warnings[0]['context']['buffer_max_bytes'] ?? null,
        );
    }

    public function testJsonEncodeFailureInChildSurfacesAsNonzeroExit(): void
    {
        // CWE-20 end-to-end: BackgroundServicesCommand::emitJsonResult
        // now uses JSON_THROW_ON_ERROR. A status that fails UTF-8
        // validation used to silently become `false` and be written
        // as the empty string (clean exit, no JSON — parser coerced
        // to error, but with no useful diagnostic). With
        // JSON_THROW_ON_ERROR the uncaught JsonException forces
        // exit 255, so the spawner logs a real exit code and stderr
        // snippet instead of a ghost. This fixture reproduces that
        // path by invoking the same flag with invalid UTF-8.
        $result = $this->makeSpawner()->spawn('json_throws_from_non_utf8', false, 60);

        $this->assertSame(['name' => 'json_throws_from_non_utf8', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
        $this->assertSame(
            255,
            $this->logger->warnings[0]['context']['exit_code'] ?? null,
            'Uncaught JsonException must surface as PHP\'s standard 255 exit code',
        );
    }
}
