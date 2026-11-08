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

use OpenEMR\Common\Twig\TwigExtension;
use OpenEMR\Core\Kernel;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('twig')]
class TwigExtensionIsolatedTest extends TestCase
{
    public function testGetGlobals(): void
    {
        $kernel = new Kernel('/var/www/openemr', '/openemr');
        $bag = new OEGlobalsBag([]);
        $extension = new TwigExtension($bag, $kernel);

        $expectedTwigGlobals = [
            'assets_dir' => '/openemr/public/assets',
            'srcdir' => '/var/www/openemr/library',
            'rootdir' => '/openemr/interface',
            'webroot' => '/openemr',
            'assetVersion' => null,
            'session' => [],
        ];

        $twigGlobals = $extension->getGlobals();
        $this->assertEquals($expectedTwigGlobals, $twigGlobals);
    }
}
