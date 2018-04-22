<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Form\View;

use Zend\Form\ConfigProvider;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * Service manager configuration for form view helpers
 *
 * @deprecated since 2.8.0, and scheduled for removal with v3.0.0.
 */
class HelperConfig implements ConfigInterface
{
    /**
     * Configure the provided service manager instance with the configuration
     * in this class.
     *
     * Adds the invokables defined in this class to the SM managing helpers.
     *
     * @param ServiceManager $serviceManager
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $serviceManager)
    {
        $config = $this->toArray();

        if (method_exists($serviceManager, 'configure')) {
            $serviceManager->configure($config);
            return $serviceManager;
        }

        foreach ($config['factories'] as $service => $factory) {
            $serviceManager->setFactory($service, $factory);
        }
        foreach ($config['aliases'] as $alias => $target) {
            $serviceManager->setAlias($alias, $target);
        }

        return $serviceManager;
    }

    /**
     * Provide all configuration as an array.
     *
     * Required by zend-servicemanager v3.
     *
     * @return array
     */
    public function toArray()
    {
        return (new ConfigProvider())->getViewHelperConfig();
    }
}
