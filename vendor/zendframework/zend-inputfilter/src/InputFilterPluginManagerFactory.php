<?php
/**
 * @link      http://github.com/zendframework/zend-inputfilter for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\InputFilter;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class InputFilterPluginManagerFactory implements FactoryInterface
{
    /**
     * zend-servicemanager v2 support for invocation options.
     *
     * @param array
     */
    protected $creationOptions;

    /**
     * {@inheritDoc}
     *
     * @return InputFilterPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $pluginManager = new InputFilterPluginManager($container, $options ?: []);

        // If this is in a zend-mvc application, the ServiceListener will inject
        // merged configuration during bootstrap.
        if ($container->has('ServiceListener')) {
            return $pluginManager;
        }

        // If we do not have a config service, nothing more to do
        if (! $container->has('config')) {
            return $pluginManager;
        }

        $config = $container->get('config');

        // If we do not have input_filters configuration, nothing more to do
        if (! isset($config['input_filters']) || ! is_array($config['input_filters'])) {
            return $pluginManager;
        }

        // Wire service configuration for input_filters
        (new Config($config['input_filters']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }

    /**
     * {@inheritDoc}
     *
     * @return InputFilterPluginManager
     */
    public function createService(ServiceLocatorInterface $container, $name = null, $requestedName = null)
    {
        return $this($container, $requestedName ?: InputFilterPluginManager::class, $this->creationOptions);
    }

    /**
     * zend-servicemanager v2 support for invocation options.
     *
     * @param array $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
