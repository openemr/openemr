<?php

/**
 * @package   OpenEMR
 *
 * @link      http://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Twig;

use OpenEMR\Common\Twig\TwigFactory;
use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Twig\Loader\FilesystemLoader;

#[Group('isolated')]
#[Group('twig')]
#[CoversClass(TwigFactory::class)]
#[CoversMethod(TwigFactory::class, 'createInstance')]
#[CoversMethod(TwigExtension::class, 'getGlobals')]
class TwigFactoryIsolatedTest extends TestCase
{
    protected function setUp(): void
    {
        $globalsBag = OEGlobalsBag::getInstance();

        $globalsBag->set('kernel', new Kernel(new EventDispatcher()));
        $globalsBag->set('fileroot', __DIR__ . '/../../../../..');

        // @todo Remove this workaround after removal from TwigFactory
        $GLOBALS['date_display_format'] ??= 0;
    }

    #[Test]
    public function notSingletonTest(): void
    {
        $this->assertNotSame(
            TwigFactory::createInstance(),
            TwigFactory::createInstance(),
        );
    }

    #[Test]
    #[DataProvider('createInstancePathsDataProvider')]
    public function createInstancePathsTest(
        array $arguments,
        array $expectedPaths,
    ): void {
        $twig = TwigFactory::createInstance(...$arguments);
        $loader = $twig->getLoader();

        if (!$loader instanceof FilesystemLoader) {
            $this->fail('Loader expected to be FilesystemLoader');
        }

        $this->assertEquals(
            $expectedPaths,
            $loader->getPaths(),
        );
    }

    /** @codeCoverageIgnore Data providers run before coverage instrumentation starts. */
    public static function createInstancePathsDataProvider(): iterable
    {
        yield 'No args - default path' => [
            [],
            [__DIR__ . '/../../../../../templates'],
        ];

        yield 'String arg' => [
            [__DIR__ . '/../../../../../library/templates'],
            [
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../templates',
            ],
        ];

        yield 'Array arg - Empty array - Default path' => [
            [[]],
            [__DIR__ . '/../../../../../templates'],
        ];

        yield 'Array arg - Multiple additional paths' => [
            [[
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../interface/super/templates',
            ]],
            [
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../interface/super/templates',
                __DIR__ . '/../../../../../templates',
            ],
        ];

        yield 'Array arg - Multiple additional paths with duplicates' => [
            [[
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../interface/super/templates',
                __DIR__ . '/../../../../../interface/super/templates',
                __DIR__ . '/../../../../../templates',
            ]],
            [
                __DIR__ . '/../../../../../library/templates',
                __DIR__ . '/../../../../../interface/super/templates',
                __DIR__ . '/../../../../../templates',
            ],
        ];
    }

    #[Test]
    #[DataProvider('renderDataProvider')]
    public function renderTest(
        array $globals,
        string $templateAsString,
        string $expectedRenderedHtml,
    ): void {
        $globalsBag = OEGlobalsBag::getInstance();
        foreach ($globals as $key => $value) {
            $globalsBag->set($key, $value);
        }

        $template = TwigFactory::createInstance()->createTemplate($templateAsString);
        $this->assertEquals($expectedRenderedHtml, $template->render());
    }

    /** @codeCoverageIgnore Data providers run before coverage instrumentation starts. */
    public static function renderDataProvider(): iterable
    {
        yield [
            [],
            '{{ srcdir }}',
            ''
        ];

        yield [
            [
                'srcdir' => 'srcdir_value',
            ],
            '{{ srcdir }}',
            'srcdir_value'
        ];

        yield [
            [
                'srcdir' => 'srcdir_value',
                'rootdir' => 'rootdir_value',
                'webroot' => 'webroot_value',
                'assets_static_relative' => 'assets_dir_value',
            ],
            '{{ srcdir }} - {{ rootdir }} - {{ webroot }} - {{ assets_dir }}',
            'srcdir_value - rootdir_value - webroot_value - assets_dir_value'
        ];
    }
}
