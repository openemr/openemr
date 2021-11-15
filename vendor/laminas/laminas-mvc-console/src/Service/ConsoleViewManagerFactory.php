<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Console\Console;
use Laminas\Mvc\Console\View\ViewManager as ConsoleViewManager;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConsoleViewManagerFactory implements FactoryInterface
{
    /**
     * Create and return the view manager for the console environment
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ConsoleViewManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (! Console::isConsole()) {
            throw new ServiceNotCreatedException(
                'ConsoleViewManager requires a Console environment; console environment not detected'
            );
        }

        return new ConsoleViewManager();
    }

    /**
     * Create and return ConsoleViewManager instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ConsoleViewManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ConsoleViewManager::class);
    }
}
