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

    public function testRunRequiresName(): void
    {
        $command = new BackgroundServicesCommandStub([]);
        $tester = $this->createTester($command);

        $tester->execute(['action' => 'run']);

        $this->assertSame(Command::FAILURE, $tester->getStatusCode());
        $this->assertStringContainsString('--name', $tester->getDisplay());
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
    /** @var list<string> */
    public array $unlockedServices = [];

    /**
     * @param list<BackgroundServicesQueryRow> $services
     */
    public function __construct(private readonly array $services = [])
    {
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
