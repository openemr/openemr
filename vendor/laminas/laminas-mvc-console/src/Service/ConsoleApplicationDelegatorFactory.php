<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Console\Console;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Delegator factory for the Application instance.
 *
 * If in a console environment, attaches the console view manager as an event
 * listener on the Application prior to returning it.
 *
 * @deprecated since 1.1.8 Use the ViewManagerDelegatorFactory instead.
 */
class ConsoleApplicationDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Laminas\Mvc\ApplicationInterface
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $application = $callback();

        if (! Console::isConsole()) {
            return $application;
        }

        $container->get('ConsoleViewManager')->attach($application->getEventManager());
        return $application;
    }

    /**
     * laminas-servicemanager v2 compatibility.
     *
     * Proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Laminas\Mvc\ApplicationInterface
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
