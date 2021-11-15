<?php

/**
 * @see       https://github.com/laminas/laminas-form for the canonical source repository
 * @copyright https://github.com/laminas/laminas-form/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-form/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Form\View;

use Laminas\Form\ConfigProvider;
use Laminas\ServiceManager\ConfigInterface;
use Laminas\ServiceManager\ServiceManager;

use function method_exists;

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
     * Required by laminas-servicemanager v3.
     *
     * @return array
     */
    public function toArray()
    {
        return (new ConfigProvider())->getViewHelperConfig();
    }
}
