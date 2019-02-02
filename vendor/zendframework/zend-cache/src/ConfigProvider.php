<?php
/**
 * @link      http://github.com/zendframework/zend-cache for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Cache;

class ConfigProvider
{
    /**
     * Return default configuration for zend-cache.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    /**
     * Return default service mappings for zend-cache.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'abstract_factories' => [
                Service\StorageCacheAbstractServiceFactory::class,
            ],
            'factories' => [
                PatternPluginManager::class => Service\PatternPluginManagerFactory::class,
                Storage\AdapterPluginManager::class => Service\StorageAdapterPluginManagerFactory::class,
                Storage\PluginManager::class => Service\StoragePluginManagerFactory::class,
            ],
        ];
    }
}
