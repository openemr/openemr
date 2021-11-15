<?php

/**
 * @see       https://github.com/laminas/laminas-inputfilter for the canonical source repository
 * @copyright https://github.com/laminas/laminas-inputfilter/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-inputfilter/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\InputFilter;

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
            'input_filters' => $this->getInputFilterConfig(),
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
                'InputFilterManager' => InputFilterPluginManager::class,

                // Legacy Zend Framework aliases
                \Zend\InputFilter\InputFilterPluginManager::class => InputFilterPluginManager::class,
            ],
            'factories' => [
                InputFilterPluginManager::class => InputFilterPluginManagerFactory::class,
            ],
        ];
    }

    /**
     * Get input filter configuration
     *
     * @return array
     */
    public function getInputFilterConfig()
    {
        return [
            'abstract_factories' => [
                InputFilterAbstractServiceFactory::class,
            ],
        ];
    }
}
