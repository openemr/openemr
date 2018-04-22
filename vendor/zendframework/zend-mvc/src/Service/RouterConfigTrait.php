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
