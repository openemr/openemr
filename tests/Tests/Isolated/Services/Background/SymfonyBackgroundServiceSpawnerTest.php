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
            # Selects behavior based on the --name= argument.
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
                        echo '{"name":"emits_executed","status":"executed"}'
                        exit 0
                        ;;
                    --name=prints_garbage_then_json)
                        echo "PHP Deprecated: something"
                        echo '{"name":"prints_garbage_then_json","status":"not_due"}'
                        exit 0
                        ;;
                    --name=json_missing_status)
                        echo '{"name":"json_missing_status"}'
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
        $result = $this->makeSpawner()->spawn('emits_executed', false);

        $this->assertSame(['name' => 'emits_executed', 'status' => 'executed'], $result);
        $this->assertSame([], $this->logger->warnings);
    }

    public function testStatusParsedFromTrailingJsonAmongstOtherOutput(): void
    {
        // PHP deprecation notices and similar pre-JSON stdout chatter
        // must not prevent the spawner from finding the status line.
        $result = $this->makeSpawner()->spawn('prints_garbage_then_json', false);

        $this->assertSame(['name' => 'prints_garbage_then_json', 'status' => 'not_due'], $result);
    }

    public function testNonZeroExitReturnsErrorAndLogsService(): void
    {
        // Surfaces the exit()/die()/fatal case described in GH #11794:
        // the child aborted before emitting JSON, so the parent must
        // still get a well-formed result and a log entry naming the
        // offending service.
        $result = $this->makeSpawner()->spawn('exits_nonzero', false);

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
        $result = $this->makeSpawner()->spawn('clean_exit_no_json', false);

        $this->assertSame(['name' => 'clean_exit_no_json', 'status' => 'error'], $result);
        $this->assertNotEmpty($this->logger->warnings);
    }

    public function testMalformedJsonStatusReturnsError(): void
    {
        // JSON line that decodes but lacks a string `status` field is
        // the same failure mode as "no JSON at all": can't trust the
        // result, so flag as error with a log entry.
        $result = $this->makeSpawner()->spawn('json_missing_status', false);

        $this->assertSame(['name' => 'json_missing_status', 'status' => 'error'], $result);
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
