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
use Laminas\Stdlib\DispatchableInterface;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;
use OpenEMR\Core\Routing\ServiceManagerControllerLocator;
use OpenEMR\Tests\Isolated\Core\Routing\Fixture\CanaryControllerFactory;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

#[Group('isolated')]
#[Group('core')]
class ServiceManagerControllerLocatorIsolatedTest extends TestCase
{
    private const CANARY = \Application\Controller\IndexController::class;

    /**
     * A ControllerManager with the minimal collaborators it injects on get():
     * a shared-aware EventManager and a ControllerPluginManager. The canary
     * controller is registered through its real factory so the locator is
     * exercised against genuine Laminas DI wiring.
     */
    private function controllerManager(): ControllerManager
    {
        $serviceManager = new ServiceManager();
        $serviceManager->setService('EventManager', new EventManager(new SharedEventManager()));
        $serviceManager->setFactory('ControllerPluginManager', fn($container) => new PluginManager($container));

        return new ControllerManager($serviceManager, [
            'factories' => [
                self::CANARY => CanaryControllerFactory::create(...),
            ],
        ]);
    }

    public function testHasReflectsControllerManager(): void
    {
        $locator = new ServiceManagerControllerLocator($this->controllerManager());

        $this->assertTrue($locator->has(self::CANARY));
        $this->assertFalse($locator->has('Nonexistent\\Controller'));
    }

    public function testGetReturnsAbstractActionController(): void
    {
        $locator = new ServiceManagerControllerLocator($this->controllerManager());

        $controller = $locator->get(self::CANARY);

        $this->assertInstanceOf(\Application\Controller\IndexController::class, $controller);
    }

    public function testGetUnknownControllerThrows(): void
    {
        $locator = new ServiceManagerControllerLocator($this->controllerManager());

        $this->expectException(\RuntimeException::class);
        $locator->get('Nonexistent\\Controller');
    }

    public function testGetNonActionControllerThrows(): void
    {
        // The ControllerManager only requires a DispatchableInterface, so a plain
        // dispatchable resolves successfully but is not an AbstractActionController
        // — the locator must reject it rather than hand it to the resolver.
        $dispatchable = new class implements DispatchableInterface {
            public function dispatch(RequestInterface $request, ?ResponseInterface $response = null): mixed
            {
                return null;
            }
        };
        $controllerManager = $this->controllerManager();
        $controllerManager->setService('Plain\\Dispatchable', $dispatchable);

        $locator = new ServiceManagerControllerLocator($controllerManager);

        $this->expectException(\RuntimeException::class);
        $locator->get('Plain\\Dispatchable');
    }
}
