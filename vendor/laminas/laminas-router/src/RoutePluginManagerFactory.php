<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class RoutePluginManagerFactory implements FactoryInterface
{
    /**
     * Create and return a route plugin manager.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return RoutePluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $options = $options ?: [];
        return new RoutePluginManager($container, $options);
    }

    /**
     * Create and return RoutePluginManager instance.
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return RoutePluginManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, RoutePluginManager::class);
    }
}
