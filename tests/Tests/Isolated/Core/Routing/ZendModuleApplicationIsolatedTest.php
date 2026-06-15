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
        // matches() only consults the route collection, so a stub dispatcher is
        // sufficient here. The ServiceManager only needs ApplicationConfig: the
        // app reads its `modules` list to decide which module configs the loader
        // may require (mirroring the legacy ModulesApplication enablement).
        // Dispatch (which uses the full ServiceManager) is covered by the
        // resolver test against the seam pieces.
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', [
            'modules' => ['Application', 'Acl'],
        ]);

        return new ZendModuleApplication(
            $serviceManager,
            $this->createStub(EventDispatcherInterface::class),
            new ZendModuleRouteLoader(
                dirname(__DIR__, 5) . '/interface/modules/zend_modules',
            ),
        );
    }

    public function testMatchesCanaryRoute(): void
    {
        // The canary (/application) is on the allowlist and resolves to a route.
        $this->assertTrue($this->application()->matches('/application'));
    }

    public function testDoesNotMatchNonCanaryModuleRoute(): void
    {
        // /acl resolves to a real route but is intentionally NOT on the canary
        // allowlist: its controller needs the Laminas MVC context the resolver
        // shim does not set up, so it must stay on the legacy runtime.
        $app = $this->application();
        $this->assertFalse($app->matches('/acl'));
        $this->assertFalse($app->matches('/acl/edit/7'));
    }

    public function testDoesNotMatchUnknownRoute(): void
    {
        $app = $this->application();
        $this->assertFalse($app->matches('/no-such-module-route'));
        $this->assertFalse($app->matches('/acl/edit/not-a-number'));
    }
}
