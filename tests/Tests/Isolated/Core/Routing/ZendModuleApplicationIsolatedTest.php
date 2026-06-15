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

use Laminas\EventManager\EventManager;
use Laminas\EventManager\SharedEventManager;
use Laminas\Mvc\Controller\ControllerManager;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\ServiceManager\ServiceManager;
use Laminas\View\Renderer\RendererInterface;
use Laminas\View\Resolver\ResolverInterface;
use OpenEMR\Core\Routing\ZendModuleApplication;
use OpenEMR\Core\Routing\ZendModuleRouteLoader;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\CanaryControllerFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[Group('isolated')]
#[Group('core')]
class ZendModuleApplicationIsolatedTest extends TestCase
{
    private const CANARY = 'Application\\Controller\\IndexController';

    private function routeLoader(): ZendModuleRouteLoader
    {
        return new ZendModuleRouteLoader(
            dirname(__DIR__, 5) . '/interface/modules/zend_modules',
        );
    }

    private function application(): ZendModuleApplication
    {
        // matches() only consults the route collection, so a stub dispatcher is
        // sufficient here. The ServiceManager only needs ApplicationConfig: the
        // app reads its `modules` list to decide which module configs the loader
        // may require (mirroring the legacy ModulesApplication enablement).
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', [
            'modules' => ['Application', 'Acl'],
        ]);

        return new ZendModuleApplication(
            $serviceManager,
            $this->createStub(EventDispatcherInterface::class),
            $this->routeLoader(),
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

    public function testCanaryPathOnAllowlistButUnroutedDoesNotMatch(): void
    {
        // /application is on the canary allowlist, but with Application absent
        // from the enabled modules its route is never loaded, so match() throws
        // and matches() returns false via the catch. This also exercises the
        // enabledModuleNames() happy path filtering Application out.
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', ['modules' => ['Acl']]);
        $app = new ZendModuleApplication(
            $serviceManager,
            $this->createStub(EventDispatcherInterface::class),
            $this->routeLoader(),
        );

        $this->assertFalse($app->matches('/application'));
    }

    public function testMissingApplicationConfigYieldsNoCanaryRoutes(): void
    {
        // No ApplicationConfig service -> enabledModuleNames() returns [] ->
        // no module routes loaded -> the canary cannot match.
        $app = new ZendModuleApplication(
            new ServiceManager(),
            $this->createStub(EventDispatcherInterface::class),
            $this->routeLoader(),
        );

        $this->assertFalse($app->matches('/application'));
    }

    public function testNonArrayApplicationConfigYieldsNoCanaryRoutes(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', new \stdClass());
        $app = new ZendModuleApplication(
            $serviceManager,
            $this->createStub(EventDispatcherInterface::class),
            $this->routeLoader(),
        );

        $this->assertFalse($app->matches('/application'));
    }

    public function testNonArrayModulesListYieldsNoCanaryRoutes(): void
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', ['modules' => 'not-a-list']);
        $app = new ZendModuleApplication(
            $serviceManager,
            $this->createStub(EventDispatcherInterface::class),
            $this->routeLoader(),
        );

        $this->assertFalse($app->matches('/application'));
    }

    public function testHandleDispatchesCanaryEndToEnd(): void
    {
        // Full seam path: match /application, build the resolver + kernel from
        // the real ServiceManager wiring, dispatch the genuine canary controller
        // (IndexController::indexAction -> JsonModel([])) and return the Symfony
        // JsonResponse "[]" — identical to the legacy Laminas runtime output.
        $app = new ZendModuleApplication(
            $this->dispatchableServiceManager(),
            new \Symfony\Component\EventDispatcher\EventDispatcher(),
            $this->routeLoader(),
        );

        $response = $app->handle(Request::create('/application'));

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('[]', $response->getContent());
    }

    public function testHandleThrowsWhenControllerManagerWrongType(): void
    {
        // buildResolver() requires the ControllerManager service to be a real
        // ControllerManager; when it resolves to something else the seam raises a
        // RuntimeException, which the front controller treats as a recoverable
        // fallback to the legacy runtime.
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', ['modules' => ['Application']]);
        $serviceManager->setService('ControllerManager', new \stdClass());
        $app = new ZendModuleApplication(
            $serviceManager,
            new \Symfony\Component\EventDispatcher\EventDispatcher(),
            $this->routeLoader(),
        );

        $this->expectException(\RuntimeException::class);
        $app->handle(Request::create('/application'));
    }

    public function testHandleThrowsWhenViewRendererNotALaminasRenderer(): void
    {
        // viewRenderer() requires the ViewRenderer service to be a Laminas
        // RendererInterface; a non-renderer triggers the guard. ControllerManager
        // is present so the failure is isolated to the renderer branch.
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', ['modules' => ['Application']]);
        $serviceManager->setService('ControllerManager', $this->controllerManager($serviceManager));
        $serviceManager->setService('ViewRenderer', new \stdClass());
        $app = new ZendModuleApplication(
            $serviceManager,
            new \Symfony\Component\EventDispatcher\EventDispatcher(),
            $this->routeLoader(),
        );

        $this->expectException(\RuntimeException::class);
        $app->handle(Request::create('/application'));
    }

    /**
     * A ServiceManager wired with everything handle() needs: ApplicationConfig
     * (so the canary route loads), a ControllerManager that builds the canary,
     * and a ViewRenderer (unused by the JSON canary but required by
     * buildResolver()).
     */
    private function dispatchableServiceManager(): ServiceManager
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('ApplicationConfig', ['modules' => ['Application']]);
        $serviceManager->setService('ControllerManager', $this->controllerManager($serviceManager));
        $serviceManager->setService('ViewRenderer', $this->stubRenderer());

        return $serviceManager;
    }

    private function controllerManager(ServiceManager $serviceManager): ControllerManager
    {
        $serviceManager->setService('EventManager', new EventManager(new SharedEventManager()));
        $serviceManager->setFactory('ControllerPluginManager', fn($container) => new PluginManager($container));

        return new ControllerManager($serviceManager, [
            'factories' => [
                self::CANARY => CanaryControllerFactory::create(...),
            ],
        ]);
    }

    private function stubRenderer(): RendererInterface
    {
        return new class implements RendererInterface {
            public function getEngine(): self
            {
                return $this;
            }

            public function setResolver(ResolverInterface $resolver): self
            {
                return $this;
            }

            /**
             * @param mixed $values
             */
            public function render($nameOrModel, $values = null): string
            {
                return '<unused/>';
            }
        };
    }
}
