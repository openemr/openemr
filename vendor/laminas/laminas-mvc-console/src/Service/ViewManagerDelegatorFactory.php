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

class ViewManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Return a ConsoleViewManager if in a Console environment.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Laminas\Mvc\Console\View\ViewManager|Laminas\Mvc\View\Http\ViewManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        if (! Console::isConsole() || ! $container->has('ConsoleViewManager')) {
            return $callback();
        }

        return $container->get('ConsoleViewManager');
    }

    /**
     * Return a ConsoleViewManager if in a Console environment. (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return \Laminas\Mvc\Console\View\ViewManager|Laminas\Mvc\View\Http\ViewManager
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
