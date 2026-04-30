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

use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\Log\NullLogger;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
#[Group('isolated')]
#[Group('background-services')]
class BackgroundServiceRunnerTest extends TestCase
{
    public function testRunReturnsNotFoundForUnknownService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: []);
        $results = $runner->run('nonexistent');

        $this->assertCount(1, $results);
        $this->assertSame('nonexistent', $results[0]['name']);
        $this->assertSame('not_found', $results[0]['status']);
    }

    public function testRunSkipsInactiveService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: [
            self::makeService('svc1', active: false),
        ]);
        $results = $runner->run('svc1');

        $this->assertSame('skipped', $results[0]['status']);
    }

    public function testRunReturnsAlreadyRunningWhenLockFailsDueToRunningProcess(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            lockFailureReason: 'already_running',
        );
        $results = $runner->run('svc1');

        $this->assertSame('already_running', $results[0]['status']);
    }

    public function testRunReturnsNotDueWhenLockFailsDueToInterval(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            lockFailureReason: 'not_due',
        );
        $results = $runner->run('svc1');

        $this->assertSame('not_due', $results[0]['status']);
    }

    public function testRunExecutesServiceSuccessfully(): void
    {
        $executed = false;
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            executeCallback: function (array $service) use (&$executed): void {
                $executed = true;
            },
        );
        $results = $runner->run('svc1');

        $this->assertSame('executed', $results[0]['status']);
        $this->assertTrue($executed);
        $this->assertContains('svc1', $runner->releasedLocks);
    }

    public function testRunReleasesLockOnException(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            executeCallback: function (array $service): void {
                throw new \RuntimeException('Service failure');
            },
        );
        $results = $runner->run('svc1');

        $this->assertSame('error', $results[0]['status']);
        $this->assertContains('svc1', $runner->releasedLocks);
    }

    public function testRunLogsExceptionWhenExecuteServiceThrows(): void
    {
        // Without this log line the orchestrator records status=error
        // with no exception class, message, file, line, or trace anywhere
        // in the cron logs. Operators have no way to diagnose the
        // failure without attaching to a pod and re-running the
        // service under instrumentation. The catch is intentionally
        // broad (\Throwable) — process-boundary cleanup must happen
        // no matter what — but the diagnostic information must not
        // be discarded along with the exception.
        $logger = new RecordingLogger();
        $thrown = new \RuntimeException('Service failure');
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('svc1')],
            executeCallback: function (array $service) use ($thrown): void {
                throw $thrown;
            },
            logger: $logger,
        );

        $results = $runner->run('svc1');

        $this->assertSame('error', $results[0]['status']);

        $errorRecords = array_values(array_filter(
            $logger->records,
            fn(array $record): bool => $record['level'] === LogLevel::ERROR,
        ));
        $this->assertCount(1, $errorRecords, 'exactly one error log line must be emitted on service failure');
        $record = $errorRecords[0];
        $this->assertSame('Background service execution failed.', (string) $record['message']);
        $this->assertSame('svc1', $record['context']['service'] ?? null);
        $this->assertSame(
            $thrown,
            $record['context']['exception'] ?? null,
            "the thrown exception must be passed via the 'exception' context key so PSR-3 processors can format the trace and class name",
        );
    }

    public function testRunAllServicesInOrder(): void
    {
        $order = [];
        $runner = new BackgroundServiceRunnerStub(
            services: [
                self::makeService('svc1'),
                self::makeService('svc2'),
                self::makeService('svc3', active: false),
            ],
            executeCallback: function (array $service) use (&$order): void {
                $order[] = $service['name'];
            },
        );
        $results = $runner->run();

        $this->assertCount(3, $results);
        $this->assertSame('executed', $results[0]['status']);
        $this->assertSame('executed', $results[1]['status']);
        $this->assertSame('skipped', $results[2]['status']);
        $this->assertSame(['svc1', 'svc2'], $order);
    }

    public function testRunSkipsManualModeServiceWithoutForce(): void
    {
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('manual_svc', executeInterval: 0)],
        );
        $results = $runner->run('manual_svc');

        $this->assertSame('skipped', $results[0]['status']);
    }

    public function testRunExecutesManualModeServiceWithForce(): void
    {
        $executed = false;
        $runner = new BackgroundServiceRunnerStub(
            services: [self::makeService('manual_svc', executeInterval: 0)],
            executeCallback: function (array $service) use (&$executed): void {
                $executed = true;
            },
        );
        $results = $runner->run('manual_svc', force: true);

        $this->assertSame('executed', $results[0]['status']);
        $this->assertTrue($executed);
    }

    /**
     * @return BackgroundServicesRow
     */
    private static function makeService(
        string $name,
        bool $active = true,
        int $executeInterval = 5,
        ?string $lockExpiresAt = null,
    ): array {
        // Use string values to match ADOdb runtime behavior (numeric-string)
        return [
            'name' => $name,
            'title' => $name,
            'active' => $active ? '1' : '0',
            'running' => $lockExpiresAt !== null ? '1' : '0',
            'next_run' => '2020-01-01 00:00:00',
            'execute_interval' => (string) $executeInterval,
            'function' => 'test_function_' . $name,
            'require_once' => null,
            'sort_order' => '100',
            'lock_expires_at' => $lockExpiresAt,
        ];
    }
}

/**
 * Test stub that overrides DB and execution methods.
 *
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
class BackgroundServiceRunnerStub extends BackgroundServiceRunner
{
    /** @var list<string> */
    public array $releasedLocks = [];

    /**
     * @param list<BackgroundServicesRow> $services
     * @param string|null $lockFailureReason Null means lock acquired; a string is the failure reason returned to run()
     * @param (\Closure(BackgroundServicesRow): void)|null $executeCallback
     */
    public function __construct(
        private readonly array $services = [],
        private readonly ?string $lockFailureReason = null,
        private readonly ?\Closure $executeCallback = null,
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($logger ?? new NullLogger());
    }

    protected function getServices(?string $serviceName, bool $force): array
    {
        if ($serviceName !== null) {
            return array_values(array_filter(
                $this->services,
                fn(array $s) => $s['name'] === $serviceName,
            ));
        }
        return $this->services;
    }

    protected function acquireLock(array $service, bool $force): ?string
    {
        return $this->lockFailureReason;
    }

    protected function releaseLock(string $serviceName): void
    {
        $this->releasedLocks[] = $serviceName;
    }

    protected function executeService(array $service): void
    {
        if ($this->executeCallback !== null) {
            ($this->executeCallback)($service);
        }
    }
}

/**
 * Minimal PSR-3 logger that records every log call so tests can assert
 * on level, message, and context without bringing in a Monolog handler.
 */
class RecordingLogger extends AbstractLogger
{
    // PSR-3 leaves the context array open: any keys, any values. Mirror
    // that here so PHPStan doesn't reject callers that pass mixed-keyed
    // context (which is legal under the interface even if our own call
    // sites use string keys).
    /** @var list<array{level: mixed, message: string|\Stringable, context: array<mixed>}> */
    public array $records = [];

    public function log($level, string|\Stringable $message, array $context = []): void
    {
        $this->records[] = [
            'level' => $level,
            'message' => $message,
            'context' => $context,
        ];
    }
}
