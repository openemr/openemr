<?php
/**
 * @link      http://github.com/zendframework/zend-i18n for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\I18n\Translator;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Config;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class LoaderPluginManagerFactory implements FactoryInterface
{
    /**
     * zend-servicemanager v2 options passed to factory.
     *
     * @param array
     */
    protected $creationOptions = [];

    /**
     * Create and return a LoaderPluginManager.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return LoaderPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $options = $options ?: [];
        $pluginManager = new LoaderPluginManager($container, $options);

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

        // If we do not have translator_plugins configuration, nothing more to do
        if (! isset($config['translator_plugins']) || ! is_array($config['translator_plugins'])) {
            return $pluginManager;
        }

        // Wire service configuration for translator_plugins
        (new Config($config['translator_plugins']))->configureServiceManager($pluginManager);

        return $pluginManager;
    }

    /**
     * zend-servicemanager v2 factory to return LoaderPluginManager
     *
     * @param ServiceLocatorInterface $container
     * @return LoaderPluginManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, 'TranslatorPluginManager', $this->creationOptions);
    }

    /**
     * v2 support for instance creation options.
     *
     * @param array $options
     * @return void
     */
    public function setCreationOptions(array $options)
    {
        $this->creationOptions = $options;
    }
}
