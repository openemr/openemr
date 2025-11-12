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
#[CoversClass(TwigExtension::class)]
#[CoversMethod(TwigExtension::class, 'getGlobals')]
class TwigExtensionIsolatedTest extends TestCase
{
    #[Test]
    #[DataProvider('getGlobalsDataProvider')]
    public function getGlobalsTest(
        array $globals,
        array $expectedTwigGlobals,
    ): void {
        $extension = new TwigExtension(
            new OEGlobalsBag($globals),
        );

        $twigGlobals = $extension->getGlobals();
        $this->assertEquals($expectedTwigGlobals, $twigGlobals);
    }

    public static function getGlobalsDataProvider(): iterable
    {
        yield [[], [
            'assets_dir' => null,
            'srcdir' => null,
            'rootdir' => null,
            'webroot' => null,
            'assetVersion' => null,
            'session' => [],
        ]];

        yield [[
            'srcdir' => 'srcdir',
        ], [
            'assets_dir' => null,
            'srcdir' => 'srcdir',
            'rootdir' => null,
            'webroot' => null,
            'assetVersion' => null,
            'session' => [],
        ]];

        yield [[
            'srcdir' => 'srcdir',
            'rootdir' => 'rootdir',
        ], [
            'assets_dir' => null,
            'srcdir' => 'srcdir',
            'rootdir' => 'rootdir',
            'webroot' => null,
            'assetVersion' => null,
            'session' => [],
        ]];
    }
}
