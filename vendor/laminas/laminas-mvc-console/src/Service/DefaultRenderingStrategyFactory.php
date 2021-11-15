<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Mvc\Console\View\DefaultRenderingStrategy;
use Laminas\Mvc\Console\View\Renderer;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class DefaultRenderingStrategyFactory implements FactoryInterface
{
    /**
     * Create and return DefaultRenderingStrategy (v3)
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return DefaultRenderingStrategy
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new DefaultRenderingStrategy($container->get(Renderer::class));
    }

    /**
     * Create and return DefaultRenderingStrategy (v2)
     *
     * @param ServiceLocatorInterface $container
     * @param null|string $name
     * @param null|string $requestedName
     * @return DefaultRenderingStrategy
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        $requestedName = $requestedName ?: Renderer::class;
        return $this($container, $requestedName);
    }
}
