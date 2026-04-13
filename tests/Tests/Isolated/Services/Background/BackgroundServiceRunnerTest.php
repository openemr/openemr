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
use OpenEMR\Services\Background\UnsafeIncludePathException;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

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

    public function testRunSkipsAlreadyRunningService(): void
    {
        $runner = new BackgroundServiceRunnerStub(services: [
            self::makeService('svc1', running: true),
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

    public function testValidateIncludePathRejectsNulByte(): void
    {
        $validator = new BackgroundServicePathValidator();

        $this->expectException(UnsafeIncludePathException::class);
        $this->expectExceptionMessage('contains NUL byte');
        $validator->callValidateIncludePath("/var/www/openemr/library/file\0.php", '/var/www/openemr', 'test_svc');
    }

    public function testValidateIncludePathRejectsStreamWrapper(): void
    {
        $validator = new BackgroundServicePathValidator();

        $this->expectException(UnsafeIncludePathException::class);
        $this->expectExceptionMessage('contains stream wrapper');
        $validator->callValidateIncludePath('php://filter/resource=/etc/passwd', '/var/www/openemr', 'test_svc');
    }

    public function testValidateIncludePathRejectsNonexistentFile(): void
    {
        $validator = new BackgroundServicePathValidator();
        $projectDir = dirname(__DIR__, 5); // repository/project root
        $nonexistentPath = $projectDir . DIRECTORY_SEPARATOR . 'nonexistent_file_' . uniqid('', true) . '.php';

        $this->expectException(UnsafeIncludePathException::class);
        $this->expectExceptionMessage('path cannot be resolved');
        $validator->callValidateIncludePath($nonexistentPath, $projectDir, 'test_svc');
    }

    public function testValidateIncludePathRejectsFileOutsideRoot(): void
    {
        $validator = new BackgroundServicePathValidator();
        $projectDir = dirname(__DIR__, 5); // repository/project root
        $tempFile = tempnam(sys_get_temp_dir(), 'openemr-bg-');

        if ($tempFile === false) {
            $this->fail('Failed to create temporary file for outside-root validation test');
        }

        try {
            $this->expectException(UnsafeIncludePathException::class);
            $this->expectExceptionMessage('resolves outside project root');
            $validator->callValidateIncludePath($tempFile, $projectDir, 'test_svc');
        } finally {
            if (is_file($tempFile)) {
                unlink($tempFile);
            }
        }
    }

    public function testValidateIncludePathRejectsDirectory(): void
    {
        $validator = new BackgroundServicePathValidator();

        // __DIR__ is a real directory under the project root — should be rejected as not a file
        $projectDir = dirname(__DIR__, 5); // repository/project root
        $this->expectException(UnsafeIncludePathException::class);
        $this->expectExceptionMessage('path is not a file');
        $validator->callValidateIncludePath(__DIR__, $projectDir, 'test_svc');
    }

    public function testValidateIncludePathAcceptsValidFileAndReturnsResolvedPath(): void
    {
        $validator = new BackgroundServicePathValidator();

        // This test file itself is a valid path under the repository/project root
        $projectDir = dirname(__DIR__, 5); // repository/project root
        $expectedPath = realpath(__FILE__);
        if ($expectedPath === false) {
            $this->fail('Failed to resolve the current test file path');
        }

        $resolvedPath = $validator->callValidateIncludePath(__FILE__, $projectDir, 'test_svc');

        $this->assertSame($expectedPath, $resolvedPath);
    }

    /**
     * @return BackgroundServicesRow
     */
    private static function makeService(
        string $name,
        bool $active = true,
        bool $running = false,
        int $executeInterval = 5,
    ): array {
        // Use string values to match ADOdb runtime behavior (numeric-string)
        return [
            'name' => $name,
            'title' => $name,
            'active' => $active ? '1' : '0',
            'running' => $running ? '1' : '0',
            'next_run' => '2020-01-01 00:00:00',
            'execute_interval' => (string) $executeInterval,
            'function' => 'test_function_' . $name,
            'require_once' => null,
            'sort_order' => '100',
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
    ) {
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
 * Exposes validateIncludePath for direct testing.
 */
class BackgroundServicePathValidator extends BackgroundServiceRunner
{
    public function callValidateIncludePath(string $path, string $projectDir, string $serviceName): string
    {
        return $this->validateIncludePath($path, $projectDir, $serviceName);
    }
}
