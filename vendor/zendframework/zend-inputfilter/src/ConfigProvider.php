<?php
/**
 * @link      http://github.com/zendframework/zend-inputfilter for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

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
