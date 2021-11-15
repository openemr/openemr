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

class RouterFactory implements FactoryInterface
{
    /**
     * Create and return the router
     *
     * Delegates to the HttpRouter service.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return RouteStackInterface
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return $container->get('HttpRouter');
    }

    /**
     * Create and return RouteStackInterface instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @param null|string $normalizedName
     * @param null|string $requestedName
     * @return RouteStackInterface
     */
    public function createService(ServiceLocatorInterface $container, $normalizedName = null, $requestedName = null)
    {
        $requestedName = $requestedName ?: 'Router';
        return $this($container, $requestedName);
    }
}
