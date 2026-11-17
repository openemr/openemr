<?php

/**
 * The Weno module bootstrap runs on every request. It must not write module or
 * background service state that is already current, and it must not run the
 * per-user lookups when no user is signed in.
 *
 * ModuleService::stateDiffers() decides whether setTaskState() and
 * setModuleState() write. The Bootstrap case relies on the isolated suite
 * loading no database layer: any query would fail.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    James DuRant <8346344+jmdurant@users.noreply.github.com>
 * @copyright Copyright (c) 2026 James DuRant <8346344+jmdurant@users.noreply.github.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace {
    $wenoModuleSrc = dirname(__DIR__, 5) . '/interface/modules/custom_modules/oe-module-weno/src/';
    if (!is_dir($wenoModuleSrc)) {
        throw new RuntimeException(
            'Weno module source not found at ' . $wenoModuleSrc
            . ' - this test moved and the relative path needs updating.'
        );
    }
    spl_autoload_register(static function (string $class) use ($wenoModuleSrc): void {
        $prefix = 'OpenEMR\\Modules\\WenoModule\\';
        if (!str_starts_with($class, $prefix)) {
            return;
        }
        $file = $wenoModuleSrc . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';
        if (is_file($file)) {
            require_once $file;
        }
    });
}

namespace OpenEMR\Tests\Isolated\Modules\Weno {

    use OpenEMR\BC\ServiceContainer;
    use OpenEMR\Common\Crypto\CryptoInterface;
    use OpenEMR\Common\Session\SessionWrapperFactory;
    use OpenEMR\Modules\WenoModule\Bootstrap;
    use OpenEMR\Modules\WenoModule\Services\ModuleService;
    use PHPUnit\Framework\Attributes\DataProvider;
    use PHPUnit\Framework\Attributes\Group;
    use PHPUnit\Framework\Attributes\Test;
    use PHPUnit\Framework\TestCase;
    use Psr\Log\NullLogger;
    use ReflectionClass;
    use Symfony\Component\EventDispatcher\EventDispatcher;
    use Symfony\Component\HttpFoundation\Session\Session;
    use Symfony\Component\HttpFoundation\Session\SessionInterface;
    use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
    use Symfony\Component\HttpFoundation\Session\Storage\SessionStorageInterface;

    #[Group('isolated')]
    #[Group('weno')]
    class WenoStateWritesTest extends TestCase
    {
        private ?SessionInterface $priorSession = null;

        private ?SessionStorageInterface $priorStorage = null;

        private bool $sessionReplaced = false;

        /**
         * @return array<string, array{list<array<mixed>>, array<string, mixed>, bool}>
         *
         * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
         */
        public static function stateProvider(): array
        {
            $tasks = static fn(mixed ...$active): array => array_map(
                static fn(mixed $value): array => ['active' => $value],
                array_values($active)
            );

            return [
                'tasks already active' => [$tasks('1', '1'), ['active' => '1'], false],
                'tasks already inactive' => [$tasks('0', '0'), ['active' => '0'], false],
                'int stored value matches string flag' => [$tasks(1, 1), ['active' => '1'], false],
                'string stored value matches int flag' => [$tasks('1', '1'), ['active' => 1], false],
                'one task inactive' => [$tasks('1', '0'), ['active' => '1'], true],
                'tasks being disabled' => [$tasks('1', '1'), ['active' => '0'], true],
                'null stored value' => [$tasks(null, '1'), ['active' => '1'], true],
                'no task rows' => [[], ['active' => '1'], false],
                'module already enabled' => [
                    [['mod_active' => '1', 'mod_ui_active' => '0']],
                    ['mod_active' => '1', 'mod_ui_active' => '0'],
                    false,
                ],
                'module ui flag differs' => [
                    [['mod_active' => '1', 'mod_ui_active' => '1']],
                    ['mod_active' => '1', 'mod_ui_active' => '0'],
                    true,
                ],
                'module being disabled' => [
                    [['mod_active' => '1', 'mod_ui_active' => '0']],
                    ['mod_active' => '0', 'mod_ui_active' => '1'],
                    true,
                ],
                'module column missing' => [
                    [['mod_active' => '1']],
                    ['mod_active' => '1', 'mod_ui_active' => '0'],
                    true,
                ],
            ];
        }

        /**
         * @param list<array<mixed>>   $rows
         * @param array<string, mixed> $wanted
         */
        #[Test]
        #[DataProvider('stateProvider')]
        public function stateDiffersOnlyWhenAnUpdateWouldChangeARow(array $rows, array $wanted, bool $expected): void
        {
            self::assertSame($expected, ModuleService::stateDiffers($rows, $wanted));
        }

        #[Test]
        public function bootstrapWithoutASignedInUserRunsNoUserLookupOrAclCheck(): void
        {
            $factory = SessionWrapperFactory::getInstance();
            if ($factory->isSessionActive()) {
                $this->priorSession = $factory->getActiveSession();
                $this->priorStorage = $factory->getActiveStorage();
            }
            $this->sessionReplaced = true;
            $storage = new MockArraySessionStorage();
            $factory->setActiveSession(new Session($storage), $storage);
            ServiceContainer::override(CryptoInterface::class, self::createStub(CryptoInterface::class));

            // Both lookups query the database, which the isolated suite does not load.
            $bootstrap = new Bootstrap(new EventDispatcher(), new NullLogger());

            self::assertFalse($bootstrap->isAuthorized);
            self::assertFalse($bootstrap->isWenoUser);
            self::assertSame('', $bootstrap->isWenoUser());
        }

        protected function tearDown(): void
        {
            if (!$this->sessionReplaced) {
                return;
            }
            ServiceContainer::reset();
            $reflection = new ReflectionClass(SessionWrapperFactory::class);
            $factory = SessionWrapperFactory::getInstance();
            $reflection->getProperty('activeSession')->setValue($factory, $this->priorSession);
            $reflection->getProperty('activeStorage')->setValue($factory, $this->priorStorage);
        }
    }
}
