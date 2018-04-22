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
use Zend\Mvc\Router\RouteStackInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ConsoleRouterFactory implements FactoryInterface
{
    use RouterConfigTrait;

    /**
     * Create and return the console router
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return RouteStackInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config       = $container->has('config') ? $container->get('config') : [];

        // Defaults
        $class  = 'Zend\Mvc\Router\Console\SimpleRouteStack';
        $config = isset($config['console']['router']) ? $config['console']['router'] : [];

        return $this->createRouter($class, $config, $container);
    }

    /**
     * Create and return RouteStackInterface instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return RouteStackInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, RouteStackInterface::class);
    }
}
