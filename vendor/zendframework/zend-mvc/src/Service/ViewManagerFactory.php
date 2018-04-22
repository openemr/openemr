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
use Zend\Mvc\View\Console\ViewManager as ConsoleViewManager;
use Zend\Mvc\View\Http\ViewManager as HttpViewManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ViewManagerFactory implements FactoryInterface
{
    /**
     * Create and return a view manager based on detected environment
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ConsoleViewManager|HttpViewManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (Console::isConsole()) {
            return $container->get('ConsoleViewManager');
        }

        return $container->get('HttpViewManager');
    }

    /**
     * Create and return HttpViewManager or ConsoleViewManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return HttpViewManager|ConsoleViewManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        $type = Console::isConsole() ? ConsoleViewManager::class : HttpViewManager::class;
        return $this($container, $type);
    }
}
