<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Router;

use Interop\Container\ContainerInterface;
use Laminas\Router\RouterConfigTrait;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class ConsoleRouterFactory implements FactoryInterface
{
    use RouterConfigTrait;

    /**
     * Create and return the console SimpleRouteStack.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return SimpleRouteStack
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $config = $container->has('config') ? $container->get('config') : [];
        $config = isset($config['console']['router']) ? $config['console']['router'] : [];
        return $this->createRouter(SimpleRouteStack::class, $config, $container);
    }

    /**
     * Create and return SimpleRouteStack instance
     *
     * For use with laminas-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return SimpleRouteStack
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, SimpleRouteStack::class);
    }
}
