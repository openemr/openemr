<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\Controller\AbstractConsoleController;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ControllerManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Add a ControllerManager initializer to inject the console into AbstractConsoleController instances.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Laminas\Mvc\Controller\ControllerManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $controllers = $callback();
        $controllers->addInitializer([$this, 'injectConsole']);
        return $controllers;
    }

    /**
     * Add a ControllerManager initializer to inject the console into AbstractConsoleController instances. (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Laminas\Mvc\Controller\ControllerManager
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }

    /**
     * Initializer: inject a Console instance into AbstractConsoleController instances.
     *
     * @param ContainerInterface|mixed $first ContainerInterface under
     *     laminas-servicemanager v3, instance to inspect under v2.
     * @param mixed|ServiceLocatorInterface $second Instance to inspect
     *     under laminas-servicemanager v3, plugin manager under v3.
     * @return void
     */
    public function injectConsole($first, $second)
    {
        if ($first instanceof ContainerInterface) {
            // v3
            $container = $first;
            $controller = $second;
        } else {
            // For v2, we need to pull the parent service locator
            $container = $second->getServiceLocator() ?: $second;
            $controller = $first;
        }

        if (! $controller instanceof AbstractConsoleController) {
            return;
        }

        $controller->setConsole($container->get('Console'));
    }
}
