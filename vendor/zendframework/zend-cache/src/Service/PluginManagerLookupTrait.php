<?php
/**
 * @link      http://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache\Service;

use Interop\Container\ContainerInterface;
use Zend\Cache\StorageFactory;
use Zend\Cache\Storage\AdapterPluginManager;
use Zend\Cache\Storage\PluginManager;

trait PluginManagerLookupTrait
{
    /**
     * Prepare the storage factory with the adapter and plugins plugin managers.
     *
     * @param ContainerInterface $container
     * @return void
     */
    private function prepareStorageFactory(ContainerInterface $container)
    {
        StorageFactory::setAdapterPluginManager($this->lookupStorageAdapterPluginManager($container));
        StorageFactory::setPluginManager($this->lookupStoragePluginManager($container));
    }

    /**
     * Lookup the storage adapter plugin manager.
     *
     * Returns the Zend\Cache\Storage\AdapterPluginManager service if present,
     * or creates a new instance otherwise.
     *
     * @param ContainerInterface $container
     * @return AdapterPluginManager
     */
    private function lookupStorageAdapterPluginManager(ContainerInterface $container)
    {
        if ($container->has(AdapterPluginManager::class)) {
            return $container->get(AdapterPluginManager::class);
        }
        return new AdapterPluginManager($container);
    }

    /**
     * Lookup the storage plugins plugin manager.
     *
     * Returns the Zend\Cache\Storage\PluginManager service if present, or
     * creates a new instance otherwise.
     *
     * @param ContainerInterface $container
     * @return PluginManager
     */
    private function lookupStoragePluginManager(ContainerInterface $container)
    {
        if ($container->has(PluginManager::class)) {
            return $container->get(PluginManager::class);
        }
        return new PluginManager($container);
    }
}
