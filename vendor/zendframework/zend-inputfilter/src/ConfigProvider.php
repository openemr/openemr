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
            'abstract_factories' => [
                InputFilterAbstractServiceFactory::class,
            ],
            'factories' => [
                InputFilterPluginManager::class => InputFilterPluginManagerFactory::class,
            ],
        ];
    }
}
