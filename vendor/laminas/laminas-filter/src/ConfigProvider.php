<?php

/**
 * @see       https://github.com/laminas/laminas-filter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-filter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-filter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Filter;

class ConfigProvider
{
    /**
     * Return configuration for this component.
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
     * Return dependency mappings for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'FilterManager' => FilterPluginManager::class,

                // Legacy Zend Framework aliases
                \Zend\Filter\FilterPluginManager::class => FilterPluginManager::class,
            ],
            'factories' => [
                FilterPluginManager::class => FilterPluginManagerFactory::class,
            ],
        ];
    }
}
