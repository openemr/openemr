<?php

/**
 * @see       https://github.com/laminas/laminas-router for the canonical source repository
 * @copyright https://github.com/laminas/laminas-router/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-router/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Router\Http;

use Interop\Container\ContainerInterface;
use Laminas\Router\RouterConfigTrait;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class HttpRouterFactory implements FactoryInterface
{
    use RouterConfigTrait;

    /**
     * Create and return the HTTP router
     *
     * Retrieves the "router" key of the Config service, and uses it
     * to instantiate the router. Uses the TreeRouteStack implementation by
     * default.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return RouteStackInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->has('config') ? $container->get('config') : [];

        // Defaults
        $class  = TreeRouteStack::class;
        $config = isset($config['router']) ? $config['router'] : [];

        return $this->createRouter($class, $config, $container);
    }

    /**
     * Create and return RouteStackInterface instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return RouteStackInterface
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, RouteStackInterface::class);
    }
}
