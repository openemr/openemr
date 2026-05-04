<?php

/**
 * End-to-end integration test that shells out to `bin/console
 * background:services` the same way cron and the Kubernetes CronJob do.
 *
 * Every other test in this area drives the command class in-process via
 * Symfony's `CommandTester`, which short-circuits the `cron -> php ->
 * bin/console -> kernel boot -> command dispatch` chain. Bugs that live
 * between the shell and the command class (missing CLI-only globals,
 * kernel-boot regressions, autoload misconfiguration, wrong exit-code
 * mapping surfacing to the supervisor) never surface in those tests.
 *
 * This test registers a probe service, invokes the production binary in
 * a child process, and asserts both that the probe ran and that the
 * exit status reflects the outcome the way a supervisor would see it.
 *
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services\Background;

use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRegistry;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

use function OpenEMR\Tests\Services\Background\Probe\cliProbeSentinelPath;

#[Group('background-services')]
class BackgroundServicesCliIntegrationTest extends TestCase
{
    private const PROBE_NAME = '_e2e_cli_probe';
    private const PROBE_FUNCTION = 'OpenEMR\\Tests\\Services\\Background\\Probe\\markCliProbeSentinel';
    /**
     * Path is project-relative because the runner resolves it through
     * `SafeIncludeResolver` against the project directory.
     */
    private const PROBE_REQUIRE_ONCE = 'tests/Tests/Services/Background/Probe/CliIntegrationProbe.php';
    private const CONSOLE_TIMEOUT_SECONDS = 60.0;

    private BackgroundServiceRegistry $registry;
    private string $projectDir;
    private string $sentinelPath;

    protected function setUp(): void
    {
        parent::setUp();
        // Load the probe file in the test process so the sentinel-path
        // helper is available when asserting. The runner re-evaluates
        // the same file via `require_once` in the child process.
        require_once __DIR__ . '/Probe/CliIntegrationProbe.php';

        $this->registry = new BackgroundServiceRegistry();
        // tests/Tests/Services/Background -> project root (four levels up).
        $this->projectDir = dirname(__DIR__, 4);
        $this->sentinelPath = cliProbeSentinelPath();

        $this->removeSentinel();
        $this->registry->unregister(self::PROBE_NAME);
        $this->registerProbe();
    }

    protected function tearDown(): void
    {
        $this->registry->unregister(self::PROBE_NAME);
        $this->removeSentinel();
        parent::tearDown();
    }

    public function testRunByNameExecutesProbeViaShell(): void
    {
        $result = $this->runConsole(['run', '--name=' . self::PROBE_NAME]);

        $this->assertSame(
            0,
            $result['exit'],
            "Expected exit 0 for executed run.\nstdout:\n{$result['stdout']}\nstderr:\n{$result['stderr']}",
        );
        $this->assertStringContainsString(
            'executed successfully',
            $result['stdout'],
            'Executed run should print the success marker from handleRun().',
        );
        $this->assertSame(
            1,
            $this->sentinelLineCount(),
            'Probe function should have executed exactly once in the child process.',
        );
    }

    public function testImmediateRerunIsNotDueAndDoesNotExecuteAgain(): void
    {
        $first = $this->runConsole(['run', '--name=' . self::PROBE_NAME]);
        $this->assertSame(0, $first['exit'], 'First run should succeed.');
        $this->assertSame(1, $this->sentinelLineCount(), 'First run should mark the sentinel once.');

        $second = $this->runConsole(['run', '--name=' . self::PROBE_NAME]);

        // Per #11677 (fixed in #11687), not_due is a no-op, not an error:
        // it must exit 0 so generic supervisors (Kubernetes CronJob, systemd)
        // don't treat a scheduled no-op as a crash. This assertion guards
        // against regressions in either direction — exit 1 (generic failure)
        // or exit 2 (Command::INVALID, the pre-#11687 behavior).
        $this->assertSame(
            0,
            $second['exit'],
            "Expected exit 0 for not_due.\nstdout:\n{$second['stdout']}\nstderr:\n{$second['stderr']}",
        );
        $this->assertStringContainsString(
            'not yet due to run',
            $second['stdout'],
            'Second run should print the not-due warning.',
        );
        $this->assertSame(
            1,
            $this->sentinelLineCount(),
            'Probe must not re-execute when the interval has not elapsed.',
        );
    }

    private function registerProbe(): void
    {
        $this->registry->register(new BackgroundServiceDefinition(
            name: self::PROBE_NAME,
            title: 'CLI Integration Probe',
            function: self::PROBE_FUNCTION,
            requireOnce: self::PROBE_REQUIRE_ONCE,
            executeInterval: 5,
            sortOrder: 999,
            active: true,
        ));
        // Force the probe to be due immediately. `register()` inserts a
        // fresh row with next_run = '0000-00-00 00:00:00' (the column
        // default), which MySQL in strict mode can interpret as an
        // invalid date and reject comparisons against it. Set an
        // unambiguous past timestamp so NOW() > next_run always holds.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `background_services` SET `next_run` = ? WHERE `name` = ?',
            ['1970-01-01 00:00:00', self::PROBE_NAME],
            true,
        );
    }

    /**
     * @param list<string> $args
     * @return array{exit: int, stdout: string, stderr: string}
     */
    private function runConsole(array $args): array
    {
        $process = new Process(
            [PHP_BINARY, $this->projectDir . '/bin/console', 'background:services', ...$args],
            $this->projectDir,
            // Inherit the parent env rather than scrubbing it: the
            // console bootstrap needs HOME, PATH, and (in the services
            // container) MYSQL_* variables that point at the test DB.
            null,
        );
        $process->setTimeout(self::CONSOLE_TIMEOUT_SECONDS);
        $process->run();

        return [
            'exit' => $process->getExitCode() ?? -1,
            'stdout' => $process->getOutput(),
            'stderr' => $process->getErrorOutput(),
        ];
    }

    private function removeSentinel(): void
    {
        if (is_file($this->sentinelPath)) {
            unlink($this->sentinelPath);
        }
    }

    private function sentinelLineCount(): int
    {
        // Precondition asserted here (rather than defensively handled with a
        // silent `return 0`) so a missing sentinel surfaces as a clear test
        // failure — "subprocess didn't run the probe" — instead of an
        // unhelpful "expected 1, got 0" one level up.
        $this->assertFileExists(
            $this->sentinelPath,
            'Sentinel file must exist before counting probe executions.',
        );
        $contents = file_get_contents($this->sentinelPath);
        $this->assertNotFalse($contents, 'Failed to read sentinel file.');
        return substr_count($contents, "\n");
    }
}
