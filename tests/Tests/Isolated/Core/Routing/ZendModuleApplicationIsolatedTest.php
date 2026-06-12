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

namespace OpenEMR\Tests\Isolated\Core\Routing;

use Laminas\ServiceManager\ServiceManager;
use OpenEMR\Core\Routing\ZendModuleApplication;
use OpenEMR\Core\Routing\ZendModuleRouteLoader;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('isolated')]
#[Group('core')]
class ZendModuleApplicationIsolatedTest extends TestCase
{
    private function application(): ZendModuleApplication
    {
        // matches() only consults the route collection, so an empty
        // ServiceManager and a stub dispatcher are sufficient here. Dispatch
        // (which uses both) is covered by the resolver test against the seam
        // pieces.
        return new ZendModuleApplication(
            new ServiceManager(),
            $this->createStub(EventDispatcherInterface::class),
            new ZendModuleRouteLoader(
                dirname(__DIR__, 5) . '/interface/modules/zend_modules',
            ),
        );
    }

    public function testMatchesKnownModuleRoute(): void
    {
        $app = $this->application();
        $this->assertTrue($app->matches('/application'));
        $this->assertTrue($app->matches('/acl'));
        $this->assertTrue($app->matches('/acl/edit/7'));
    }

    public function testDoesNotMatchUnknownRoute(): void
    {
        $app = $this->application();
        $this->assertFalse($app->matches('/no-such-module-route'));
        $this->assertFalse($app->matches('/acl/edit/not-a-number'));
    }
}
