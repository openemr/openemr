<?php
/**
 * @link      http://github.com/zendframework/zend-paginator for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Paginator;

class ConfigProvider
{
    /**
     * Retrieve default zend-paginator configuration.
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
     * Retrieve dependency configuration for zend-paginator.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'factories' => [
                AdapterPluginManager::class => AdapterPluginManagerFactory::class,
                ScrollingStylePluginManager::class => ScrollingStylePluginManagerFactory::class,
            ],
        ];
    }
}
