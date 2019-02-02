<?php
/**
 * @link      http://github.com/zendframework/zend-session for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session;

class ConfigProvider
{
    /**
     * Retrieve configuration for zend-session.
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
     * Retrieve dependency config for zend-session.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'abstract_factories' => [
                Service\ContainerAbstractServiceFactory::class,
            ],
            'aliases' => [
                SessionManager::class => ManagerInterface::class,
            ],
            'factories' => [
                Config\ConfigInterface::class => Service\SessionConfigFactory::class,
                ManagerInterface::class => Service\SessionManagerFactory::class,
                Storage\StorageInterface::class => Service\StorageFactory::class,
            ],
        ];
    }
}
