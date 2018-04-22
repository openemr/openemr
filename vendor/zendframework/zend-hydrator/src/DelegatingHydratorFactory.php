<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Hydrator;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class DelegatingHydratorFactory implements FactoryInterface
{
    /**
     * Creates DelegatingHydrator (v2)
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return DelegatingHydrator
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, '');
    }

    /**
     * Creates DelegatingHydrator (v3)
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array|null $options
     * @return DelegatingHydrator
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $container = $this->marshalHydratorPluginManager($container);
        return new DelegatingHydrator($container);
    }

    /**
     * Locate and return a HydratorPluginManager instance.
     *
     * @param ContainerInterface $container
     * @return HydratorPluginManager
     */
    private function marshalHydratorPluginManager(ContainerInterface $container)
    {
        // Already one? Return it.
        if ($container instanceof HydratorPluginManager) {
            return $container;
        }

        // As typically registered with v3 (FQCN)
        if ($container->has(HydratorPluginManager::class)) {
            return $container->get(HydratorPluginManager::class);
        }

        // As registered by zend-mvc
        if ($container->has('HydratorManager')) {
            return $container->get('HydratorManager');
        }

        // Fallback: create one
        return new HydratorPluginManager($container);
    }
}
