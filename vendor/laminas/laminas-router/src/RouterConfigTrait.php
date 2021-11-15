<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router;

use Interop\Container\ContainerInterface;

trait RouterConfigTrait
{
    /**
     * Create and return a router instance, by calling the appropriate factory.
     *
     * @param string $class
     * @param array $config
     * @param ContainerInterface $container
     */
    private function createRouter($class, array $config, ContainerInterface $container)
    {
        // Obtain the configured router class, if any
        if (isset($config['router_class']) && class_exists($config['router_class'])) {
            $class = $config['router_class'];
        }

        // Inject the route plugins
        if (! isset($config['route_plugins'])) {
            $routePluginManager = $container->get('RoutePluginManager');
            $config['route_plugins'] = $routePluginManager;
        }

        // Obtain an instance
        $factory = sprintf('%s::factory', $class);
        return call_user_func($factory, $config);
    }
}
