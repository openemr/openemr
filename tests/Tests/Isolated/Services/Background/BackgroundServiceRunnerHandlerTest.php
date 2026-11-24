<?php

/**
 * Isolated tests for how BackgroundServiceRunner calls the handler a service row names.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Services\Background;

use OpenEMR\Common\Database\TableTypes;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\Background\BackgroundServiceRunner;
use OpenEMR\Tests\Isolated\Services\Background\Fixtures\RecordingBackgroundHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
#[Group('isolated')]
#[Group('background-services')]
final class BackgroundServiceRunnerHandlerTest extends TestCase
{
    private const HANDLER = RecordingBackgroundHandler::class;

    private ?string $previousFileroot = null;

    /**
     * The runner resolves require_once against the project directory, which
     * falls back to `fileroot` when no kernel is booted, as in isolated tests.
     */
    protected function setUp(): void
    {
        RecordingBackgroundHandler::$calls = [];
        $globals = OEGlobalsBag::getInstance();
        $this->previousFileroot = $globals->has('fileroot') ? $globals->getString('fileroot') : null;
        $globals->set('fileroot', dirname(__DIR__, 5));
    }

    /**
     * OEGlobalsBag is one instance for the whole process, so leave `fileroot`
     * as the next test expects it.
     */
    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->previousFileroot === null) {
            $globals->remove('fileroot');
            return;
        }
        $globals->set('fileroot', $this->previousFileroot);
    }

    public function testRunsAStaticMethodHandlerWithoutRequireOnce(): void
    {
        $runner = new HandlerRunner(self::service(self::HANDLER . '::run'));

        self::assertSame([['name' => 'svc', 'status' => 'executed']], $runner->run('svc'));
        self::assertSame(['run'], RecordingBackgroundHandler::$calls);
    }

    public function testRunsAStaticMethodHandlerNamedWithALeadingBackslash(): void
    {
        $runner = new HandlerRunner(self::service('\\' . self::HANDLER . '::run'));

        self::assertSame([['name' => 'svc', 'status' => 'executed']], $runner->run('svc'));
        self::assertSame(['run'], RecordingBackgroundHandler::$calls);
    }

    public function testRunsAFunctionHandlerLoadedFromRequireOnce(): void
    {
        $runner = new HandlerRunner(self::service(
            'OpenEMR\\Tests\\Isolated\\Services\\Background\\Fixtures\\recordBackgroundHandlerFunction',
            '/tests/Tests/Isolated/Services/Background/Fixtures/background-handler-function.php',
        ));

        self::assertSame([['name' => 'svc', 'status' => 'executed']], $runner->run('svc'));
        self::assertSame(['function'], RecordingBackgroundHandler::$calls);
    }

    public function testReportsAThrowingHandlerAsAnError(): void
    {
        $runner = new HandlerRunner(self::service(self::HANDLER . '::fail'));

        self::assertSame([['name' => 'svc', 'status' => 'error']], $runner->run('svc'));
        self::assertSame(['fail'], RecordingBackgroundHandler::$calls);
        self::assertSame(['svc'], $runner->releasedLocks);
    }

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function uncallableHandlerProvider(): array
    {
        return [
            'instance method' => [self::HANDLER . '::instanceRun'],
            'protected static method' => [self::HANDLER . '::hiddenRun'],
            'missing method' => [self::HANDLER . '::missing'],
            'missing class' => ['OpenEMR\\Tests\\Isolated\\Services\\Background\\Fixtures\\Missing::run'],
            'missing function' => ['openemr_missing_background_handler'],
        ];
    }

    /**
     * Checks the refusal twice: executeService() names the bad handler, and
     * run() turns that into status error without calling anything.
     */
    #[DataProvider('uncallableHandlerProvider')]
    public function testRefusesAnUncallableHandler(string $handler): void
    {
        $runner = new HandlerRunner(self::service($handler));

        $failure = null;
        try {
            $runner->executeHandler(self::service($handler));
        } catch (\RuntimeException $e) {
            $failure = $e;
        }
        self::assertInstanceOf(\RuntimeException::class, $failure, 'executeService() accepted an uncallable handler');
        self::assertSame(
            sprintf('Background service "svc" is misconfigured: handler "%s" is not callable.', $handler),
            $failure->getMessage(),
        );

        self::assertSame([['name' => 'svc', 'status' => 'error']], $runner->run('svc'));
        self::assertSame([], RecordingBackgroundHandler::$calls);
    }

    /**
     * @return BackgroundServicesRow
     */
    private static function service(string $function, ?string $requireOnce = null): array
    {
        return [
            'name' => 'svc',
            'title' => 'svc',
            'active' => '1',
            'running' => '0',
            'next_run' => '2020-01-01 00:00:00',
            'execute_interval' => '5',
            'function' => $function,
            'require_once' => $requireOnce,
            'sort_order' => '100',
            'lock_expires_at' => null,
        ];
    }
}

/**
 * Stubs the database calls and keeps the real executeService().
 *
 * @phpstan-import-type BackgroundServicesRow from TableTypes
 */
final class HandlerRunner extends BackgroundServiceRunner
{
    /** @var list<string> */
    public array $releasedLocks = [];

    /**
     * @param BackgroundServicesRow $service
     */
    public function __construct(private readonly array $service)
    {
        parent::__construct(new NullLogger());
    }

    /**
     * @param BackgroundServicesRow $service
     */
    public function executeHandler(array $service): void
    {
        $this->executeService($service);
    }

    protected function getServices(?string $serviceName, bool $force): array
    {
        return [$this->service];
    }

    protected function acquireLock(array $service, bool $force): ?string
    {
        return null;
    }

    protected function releaseLock(string $serviceName): void
    {
        $this->releasedLocks[] = $serviceName;
    }
}
