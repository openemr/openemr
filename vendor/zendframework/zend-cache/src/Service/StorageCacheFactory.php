<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Service;

use Interop\Container\ContainerInterface;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Storage cache factory.
 */
class StorageCacheFactory implements FactoryInterface
{
    use PluginManagerLookupTrait;

    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->prepareStorageFactory($container);

        $config = $container->get('config');
        $cacheConfig = isset($config['cache']) ? $config['cache'] : [];
        return StorageFactory::factory($cacheConfig);
    }

    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, StorageInterface::class);
    }
}
