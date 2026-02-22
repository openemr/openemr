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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('twig')]
#[CoversClass(TwigExtension::class)]
#[CoversMethod(TwigExtension::class, 'getGlobals')]
class TwigExtensionIsolatedTest extends TestCase
{
    public function testGetGlobals(): void
    {
        $bag = new OEGlobalsBag([
            'assets_static_relative' => 'some-asset-dir',
            'srcdir' => 'some-src-dir',
            'rootdir' => 'some-root-dir',
            'webroot' => 'some-webroot-dir',
        ]);
        $extension = new TwigExtension($bag);

        $expectedTwigGlobals = [
            'assets_dir' => 'some-asset-dir',
            'srcdir' => 'some-src-dir',
            'rootdir' => 'some-root-dir',
            'webroot' => 'some-webroot-dir',
            'assetVersion' => null,
            'session' => [],
        ];

        $twigGlobals = $extension->getGlobals();
        $this->assertEquals($expectedTwigGlobals, $twigGlobals);
    }
}
