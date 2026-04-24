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

namespace OpenEMR\Tests\Isolated\Common\Command;

use OpenEMR\Common\Command\BackgroundServicesCommand;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Background\BackgroundServiceDefinition;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
#[Group('isolated')]
#[Group('background-services')]
class BackgroundServicesCommandTest extends TestCase
{
    private function createTester(BackgroundServicesCommandStub $command): CommandTester
    {
        $app = new Application();
        $app->addCommand($command);
        return new CommandTester($app->find('background:services'));
    }

    /**
     * @return BackgroundServicesQueryRow
     */
    private static function makeService(
        string $name,
        string $title,
        bool $active = true,
        int $running = 0,
        int $executeInterval = 5,
        string $nextRun = '2026-03-28 10:00:00',
        ?string $lockExpiresAt = null,
        bool $leaseIsLive = false,
    ): array {
        // `leaseIsLive` is intentionally independent of `running` and
        // `lockExpiresAt` — production SQL derives it from
        // `lock_expires_at > NOW()`, but tests need to exercise
        // inconsistent-state rows too (e.g. stuck `running = 1` with an
        // expired/NULL lease, which is the whole point of GH #11661).
        // Use string values to match ADOdb runtime behavior (numeric-string).
        return [
            'name' => $name,
            'title' => $title,
            'active' => $active ? '1' : '0',
            'running' => (string) $running,
            'execute_interval' => (string) $executeInterval,
            'next_run' => $nextRun,
            'function' => 'test_fn',
            'require_once' => null,
            'sort_order' => '100',
            'lock_expires_at' => $lockExpiresAt,
            'lease_is_live' => $leaseIsLive ? '1' : '0',
        ];
    }

    public function testListDisplaysServices(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('phimail', 'phiMail Service', executeInterval: 5),
            self::makeService('Email_Service', 'Email Service', executeInterval: 2),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $output = $tester->getDisplay();
        $this->assertStringContainsString('phimail', $output);
        $this->assertStringContainsString('Email_Service', $output);
        $this->assertStringContainsString('5 min', $output);
        $this->assertStringContainsString('2 min', $output);
    }

    public function testListEmptyServices(): void
    {
        $command = new BackgroundServicesCommandStub([]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('No background services', $tester->getDisplay());
    }

    public function testRunWithoutNameRunsAllDue(): void
    {
        // Without --name, the command invokes BackgroundServiceRunner::run(null, false)
        // which advances every service whose interval has elapsed. Used by cron
        // and Kubernetes CronJob.
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [
                ['name' => 'phimail', 'status' => 'executed'],
                ['name' => 'Email_Service', 'status' => 'not_due'],
            ],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('phimail', $tester->getDisplay());
        $this->assertStringContainsString('Email_Service', $tester->getDisplay());
    }

    public function testRunWithoutNameAndNoDueServices(): void
    {
        $command = new BackgroundServicesCommandStub(services: [], runnerResults: []);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString('No background services were due', $tester->getDisplay());
    }

    public function testRunExecutedExitsZero(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'executed']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    /**
     * Regression for #11677: a service that isn't due to run on this tick
     * must exit 0 so Kubernetes CronJob doesn't treat the no-op as failure.
     */
    public function testRunNotDueExitsZero(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'not_due']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    /**
     * Regression for #11677: a service whose previous tick is still running
     * is an expected state, not a failure.
     */
    public function testRunAlreadyRunningExitsZero(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'already_running']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    public function testRunSkippedExitsZero(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'skipped']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    public function testRunNotFoundExitsOne(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'nonexistent', 'status' => 'not_found']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'nonexistent']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testRunErrorExitsOne(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'error']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testRunAllDueExitsOneIfAnyServiceErrored(): void
    {
        // Mixed multi-service result: one executed cleanly, one errored.
        // The run-all path must surface the error via a non-zero exit.
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [
                ['name' => 'phimail', 'status' => 'executed'],
                ['name' => 'Email_Service', 'status' => 'error'],
            ],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
    }

    public function testRunAllDueExitsZeroWhenAllNonErrorOutcomes(): void
    {
        // Mixed non-error outcomes across multiple services must still be
        // success — a cron tick that finds everything already running or
        // not yet due is the normal steady state.
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [
                ['name' => 'phimail', 'status' => 'executed'],
                ['name' => 'Email_Service', 'status' => 'not_due'],
                ['name' => 'UUID_Service', 'status' => 'already_running'],
                ['name' => 'MedEx', 'status' => 'skipped'],
            ],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
    }

    /**
     * --force without --name must be dropped before reaching the runner. The
     * CLI help documents --force as "ignored without --name" because honoring
     * it in the run-all path would flip BackgroundServiceRunner::getServices()
     * to `WHERE 1` (including manual-mode services) and bypass the interval
     * check for every service, contradicting the run-all semantics.
     */
    public function testRunAllDueIgnoresForceFlag(): void
    {
        $command = new BackgroundServicesCommandStub(services: [], runnerResults: []);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--force' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertNotNull($command->runner);
        $this->assertNull($command->runner->lastServiceName);
        $this->assertFalse($command->runner->lastForce);
        $this->assertStringContainsString('--force is ignored without --name', $tester->getDisplay());
    }

    /**
     * --force with an explicit --name flows through to the runner unchanged;
     * this is the documented escape hatch for manual-mode services.
     */
    public function testRunWithNameForwardsForceFlag(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'executed']],
        );
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--name' => 'phimail', '--force' => true]);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertNotNull($command->runner);
        $this->assertSame('phimail', $command->runner->lastServiceName);
        $this->assertTrue($command->runner->lastForce);
    }

    /**
     * With --name and --json, the command emits exactly one JSON result
     * line on stdout and suppresses human-readable output. This is the
     * contract consumed by SymfonyBackgroundServiceSpawner in the
     * run-all-due orchestrator.
     *
     * The JSON also reflects the per-invocation nonce from OPENEMR_BG_NONCE
     * so the parent orchestrator can authenticate the status line against
     * forged lines printed by the service's own shutdown handlers (CWE-345).
     */
    public function testRunEmitsJsonLineWhenJsonOptionGiven(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'executed']],
        );
        $tester = $this->createTester($command);

        putenv('OPENEMR_BG_NONCE=nonce-under-test');
        try {
            $tester->execute(['action' => 'run', '--name' => 'phimail', '--json' => true]);
        } finally {
            putenv('OPENEMR_BG_NONCE');
        }

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $output = trim($tester->getDisplay());
        // Drop any banner spacing. The JSON line should be the only
        // non-empty content emitted in --json mode.
        $lines = array_values(array_filter(
            preg_split('/\R/', $output) ?: [],
            static fn($line) => trim((string) $line) !== '',
        ));
        $this->assertCount(1, $lines, "Expected exactly one output line in --json mode, got: {$output}");
        $decoded = json_decode($lines[0], true);
        $this->assertSame(
            ['name' => 'phimail', 'status' => 'executed', 'nonce' => 'nonce-under-test'],
            $decoded,
        );
    }

    public function testRunJsonModePropagatesErrorExitCode(): void
    {
        $command = new BackgroundServicesCommandStub(
            services: [],
            runnerResults: [['name' => 'phimail', 'status' => 'error']],
        );
        $tester = $this->createTester($command);

        // No nonce set: the command emits an empty-string nonce, which
        // the parent parser rejects. A direct CLI operator calling the
        // command outside the orchestrator never parses the JSON, so
        // the empty nonce is harmless in that path.
        $tester->execute(['action' => 'run', '--name' => 'phimail', '--json' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $output = trim($tester->getDisplay());
        $lines = array_values(array_filter(
            preg_split('/\R/', $output) ?: [],
            static fn($line) => trim((string) $line) !== '',
        ));
        $this->assertCount(1, $lines);
        $this->assertSame(
            ['name' => 'phimail', 'status' => 'error', 'nonce' => ''],
            json_decode($lines[0], true),
        );
    }

    public function testRunJsonWithoutNameIsRejected(): void
    {
        // --json only makes sense for a single-service run. The
        // run-all-due orchestrator never invokes this command without
        // a name, so --json without --name is almost certainly a
        // misuse. Fail fast instead of silently honoring it.
        $command = new BackgroundServicesCommandStub(services: [], runnerResults: []);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run', '--json' => true]);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('--json requires --name', $tester->getDisplay());
    }

    public function testUnknownAction(): void
    {
        $command = new BackgroundServicesCommandStub([]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'invalid']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('Unknown action', $tester->getDisplay());
    }

    public function testFormatIntervalManual(): void
    {
        // Use a name/title that does NOT contain "manual" so the assertion
        // actually validates the formatInterval(0) output.
        $command = new BackgroundServicesCommandStub([
            self::makeService('zero_interval_svc', 'Zero Interval', executeInterval: 0),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $this->assertStringContainsString('manual', $tester->getDisplay());
    }

    public function testFormatIntervalHours(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('uuid_svc', 'UUID Service', executeInterval: 240),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $this->assertStringContainsString('4h', $tester->getDisplay());
    }

    public function testRunningColumnShowsNoWhenLeaseNotLive(): void
    {
        // The "Running" column in `list` renders from `lease_is_live`, not
        // from the legacy `running` column. A row with `running = -1` (the
        // DB default) and no live lease should show "no".
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc', 'Service', running: -1, leaseIsLive: false),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $output = $tester->getDisplay();
        // Active is "yes" (default), running should be "no" when lease is not live
        $this->assertMatchesRegularExpression('/svc.*yes\s+no/s', $output);
    }

    public function testRunningColumnShowsNoForStuckLegacyFlagWithoutLiveLease(): void
    {
        // Regression guard for GH #11661: a row with the legacy `running`
        // column stuck at 1 but no live lease (crashed worker) must render
        // as "no" — the whole point of the fix is that stuck locks don't
        // display as running forever.
        $command = new BackgroundServicesCommandStub([
            self::makeService(
                'svc',
                'Service',
                running: 1,
                lockExpiresAt: '2020-01-01 00:00:00',
                leaseIsLive: false,
            ),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'list']);

        $this->assertMatchesRegularExpression('/svc.*yes\s+no/s', $tester->getDisplay());
    }

    public function testMinutesToCronSubHour(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc5', 'Five Min', executeInterval: 5),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'crontab']);

        $this->assertStringContainsString('*/5 * * * *', $tester->getDisplay());
    }

    public function testMinutesToCronWholeHours(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc4h', 'Four Hours', executeInterval: 240),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'crontab']);

        $this->assertStringContainsString('0 */4 * * *', $tester->getDisplay());
    }

    public function testMinutesToCronNonDivisibleSubHourEmitsComment(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc7', 'Seven Min', executeInterval: 7),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'crontab']);

        $this->assertStringContainsString('# cannot represent', $tester->getDisplay());
    }

    public function testMinutesToCronNonRoundHoursEmitsComment(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc90', 'Ninety Min', executeInterval: 90),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'crontab']);

        $this->assertStringContainsString('# cannot represent', $tester->getDisplay());
    }

    public function testMinutesToCronDailyInterval(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc1d', 'Daily', executeInterval: 1440),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'crontab']);

        $this->assertStringContainsString('0 0 * * *', $tester->getDisplay());
    }

    public function testUnlockRequiresName(): void
    {
        $command = new BackgroundServicesCommandStub([]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'unlock']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('--name', $tester->getDisplay());
    }

    public function testUnlockReportsNotFound(): void
    {
        $command = new BackgroundServicesCommandStub([]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'unlock', '--name' => 'ghost']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('not found', $tester->getDisplay());
    }

    public function testUnlockClearsExistingService(): void
    {
        $command = new BackgroundServicesCommandStub([
            self::makeService('svc', 'Service', running: 1, lockExpiresAt: '2026-03-28 11:00:00'),
        ]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'unlock', '--name' => 'svc']);

        $this->assertSame(Command::SUCCESS, $tester->getStatusCode());
        $this->assertStringContainsString("Lease cleared for service 'svc'", $tester->getDisplay());
        $this->assertContains('svc', $command->unlockedServices);
    }
}

/**
 * Stub that provides fixture data instead of hitting the database.
 *
 * @phpstan-import-type BackgroundServicesQueryRow from BackgroundServiceDefinition
 */
class BackgroundServicesCommandStub extends BackgroundServicesCommand
{
    public ?BackgroundServiceRunnerFixture $runner = null;

    /** @var list<string> */
    public array $unlockedServices = [];

    /**
     * @param list<BackgroundServicesQueryRow> $services
     * @param list<array{name: string, status: string}> $runnerResults
     */
    public function __construct(
        private readonly array $services = [],
        private readonly array $runnerResults = [],
    ) {
        parent::__construct();
        $this->setGlobalsBag(new OEGlobalsBag(['fileroot' => '/var/www/openemr']));
    }

    protected function fetchServices(): array
    {
        return $this->services;
    }

    protected function fetchActiveServices(): array
    {
        return array_values(array_filter(
            $this->services,
            fn(array $s) => (int) $s['active'] !== 0 && (int) $s['execute_interval'] > 0,
        ));
    }

    protected function createRunner(): BackgroundServiceRunner
    {
        $this->runner ??= new BackgroundServiceRunnerFixture($this->runnerResults);
        return $this->runner;
    }

    protected function clearLease(string $name): bool
    {
        $exists = array_filter($this->services, fn(array $s) => $s['name'] === $name);
        if ($exists === []) {
            return false;
        }
        $this->unlockedServices[] = $name;
        return true;
    }
}

/**
 * Runner fixture that returns a canned results array without touching the DB.
 *
 * Records the arguments of the last run() call so tests can assert that the
 * command layer forwarded them correctly (e.g., dropping --force when no
 * --name was given).
 */
class BackgroundServiceRunnerFixture extends BackgroundServiceRunner
{
    public ?string $lastServiceName = null;

    public ?bool $lastForce = null;

    /**
     * @param list<array{name: string, status: string}> $results
     */
    public function __construct(private readonly array $results)
    {
    }

    public function run(?string $serviceName = null, bool $force = false): array
    {
        $this->lastServiceName = $serviceName;
        $this->lastForce = $force;
        return $this->results;
    }
}
