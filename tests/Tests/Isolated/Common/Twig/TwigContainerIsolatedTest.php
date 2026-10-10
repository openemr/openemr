<?php

/**
 * @package   OpenEMR
 *
 * @link      https://www.open-emr.org
 *
 * @author    Igor Mukhin <igor.mukhin@gmail.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc <https://opencoreemr.com/>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Isolated\Common\Twig;

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Twig\Loader\FilesystemLoader;

#[Group('isolated')]
#[Group('twig')]
class TwigContainerIsolatedTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['fileroot'] = __DIR__ . '/../../../../../'; // @todo Remove this workaround after removal from TwigContainer
        $GLOBALS['date_display_format'] ??= 0;
    }

    /** @param array<string, string> $globals */
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

        $twigContainer = new TwigContainer();
        $twigEnvironment = $twigContainer->getTwig();
        $template = $twigEnvironment->createTemplate($templateAsString);
        $this->assertEquals($expectedRenderedHtml, $template->render());
    }

    #[Test]
    #[DataProvider('additionalPathDataProvider')]
    public function constructorPreservesAdditionalPathHandling(?string $path, bool $includePath): void
    {
        $container = new TwigContainer($path);
        $loader = $container->getTwig()->getLoader();
        self::assertInstanceOf(FilesystemLoader::class, $loader);

        $expectedPaths = [OEGlobalsBag::getInstance()->getProjectDir() . '/templates'];
        if ($includePath) {
            $expectedPaths[] = $path;
        }
        self::assertSame($expectedPaths, $loader->getPaths());
    }

    /**
     * @return iterable<string, array{?string, bool}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function additionalPathDataProvider(): iterable
    {
        yield 'null' => [null, false];
        yield 'empty string' => ['', false];
        yield 'zero string' => ['0', false];
        yield 'custom directory' => [__DIR__, true];
    }

    #[Test]
    public function addedPathsPreserveTheirOrder(): void
    {
        $container = new TwigContainer(__DIR__);
        $container->addPath(dirname(__DIR__));
        $loader = $container->getTwig()->getLoader();
        self::assertInstanceOf(FilesystemLoader::class, $loader);
        self::assertSame([
            OEGlobalsBag::getInstance()->getProjectDir() . '/templates',
            __DIR__,
            dirname(__DIR__),
        ], $loader->getPaths());
    }

    /**
     * @return iterable<array{array<string, string>, string, string}>
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
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
