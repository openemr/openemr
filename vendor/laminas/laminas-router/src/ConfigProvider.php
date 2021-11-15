<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router;

/**
 * Provide base configuration for using the component.
 *
 * Provides base configuration expected in order to:
 *
 * - seed and configure the default routers and route plugin manager.
 * - provide routes to the given routers.
 */
class ConfigProvider
{
    /**
     * Provide default configuration.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
            'route_manager' => $this->getRouteManagerConfig(),
        ];
    }

    /**
     * Provide default container dependency configuration.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'HttpRouter' => Http\TreeRouteStack::class,
                'router' => RouteStackInterface::class,
                'Router' => RouteStackInterface::class,
                'RoutePluginManager' => RoutePluginManager::class,

                // Legacy Zend Framework aliases
                \Zend\Router\Http\TreeRouteStack::class => Http\TreeRouteStack::class,
                \Zend\Router\RoutePluginManager::class => RoutePluginManager::class,
                \Zend\Router\RouteStackInterface::class => RouteStackInterface::class,
            ],
            'factories' => [
                Http\TreeRouteStack::class => Http\HttpRouterFactory::class,
                RoutePluginManager::class => RoutePluginManagerFactory::class,
                RouteStackInterface::class => RouterFactory::class,
            ],
        ];
    }

    /**
     * Provide default route plugin manager configuration.
     *
     * @return array
     */
    public function getRouteManagerConfig()
    {
        return [];
    }
}
