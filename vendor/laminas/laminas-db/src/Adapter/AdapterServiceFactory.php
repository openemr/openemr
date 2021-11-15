<?php

/**
 * @see       https://github.com/laminas/laminas-db for the canonical source repository
 * @copyright https://github.com/laminas/laminas-db/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-db/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Db\Adapter;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

class AdapterServiceFactory implements FactoryInterface
{
    /**
     * Create db adapter service
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return Adapter
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $container->get('config');
        return new Adapter($config['db']);
    }

    /**
     * Create db adapter service (v2)
     *
     * @param ServiceLocatorInterface $container
     * @return Adapter
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Adapter::class);
    }
}
