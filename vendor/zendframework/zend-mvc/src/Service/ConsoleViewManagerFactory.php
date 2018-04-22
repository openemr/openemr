<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\Console\Console;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;

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
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ConsoleViewManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ConsoleViewManager::class);
    }
}
