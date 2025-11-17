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

use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('twig')]
#[CoversClass(TwigContainer::class)]
#[CoversMethod(TwigContainer::class, 'getTwig')]
#[CoversMethod(TwigExtension::class, 'getGlobals')]
class TwigContainerIsolatedTest extends TestCase
{
    protected function setUp(): void
    {
        $GLOBALS['fileroot'] = __DIR__ . '/../../../../../'; // @todo Remove this workaround after removal from TwigContainer
        $GLOBALS['date_display_format'] ??= 0;
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

        $twigContainer = new TwigContainer();
        $twigEnvironment = $twigContainer->getTwig();
        $template = $twigEnvironment->createTemplate($templateAsString);
        $this->assertEquals($expectedRenderedHtml, $template->render());
    }

    public static function renderDataProvider(): iterable
    {
        yield [
            [],
            '{{ srcdir }}',
            ''
        ];

        yield [
            [
                'srcdir' => 'srcdir',
            ],
            '{{ srcdir }}',
            'srcdir'
        ];
    }
}
