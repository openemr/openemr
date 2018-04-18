<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Logger abstract service factory.
 *
 * Allow to configure multiple loggers for application.
 */
class LoggerAbstractServiceFactory implements AbstractFactoryInterface
{
    /**
     * @var array
     */
    protected $config;

    /**
     * Configuration key holding logger configuration
     *
     * @var string
     */
    protected $configKey = 'log';

    /**
     * {@inheritDoc}
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        $config = $this->getConfig($container);
        if (empty($config)) {
            return false;
        }

        return isset($config[$requestedName]);
    }

    /**
     * {@inheritdoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }

    /**
     * {@inheritdoc}
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config = $this->getConfig($container);
        $config = $config[$requestedName];

        $this->processConfig($config, $container);

        return new Logger($config);
    }

    /**
     * {@inheritDoc}
     */
    public function createServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this($container, $requestedName);
    }

    /**
     * Retrieve configuration for loggers, if any
     *
     * @param ContainerInterface $services
     *
     * @return array
     */
    protected function getConfig(ContainerInterface $services)
    {
        if ($this->config !== null) {
            return $this->config;
        }

        if (! $services->has('config')) {
            $this->config = [];

            return $this->config;
        }

        $config = $services->get('config');
        if (! isset($config[$this->configKey])) {
            $this->config = [];

            return $this->config;
        }

        $this->config = $config[$this->configKey];

        return $this->config;
    }

    /**
     * Process and return the configuration from the container.
     *
     * @param array $config Passed by reference
     * @param ContainerInterface $services
     */
    protected function processConfig(&$config, ContainerInterface $services)
    {
        if (isset($config['writer_plugin_manager'])
            && is_string($config['writer_plugin_manager'])
            && $services->has($config['writer_plugin_manager'])
        ) {
            $config['writer_plugin_manager'] = $services->get($config['writer_plugin_manager']);
        }

        if ((! isset($config['writer_plugin_manager'])
                || ! $config['writer_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogWriterManager')
        ) {
            $config['writer_plugin_manager'] = $services->get('LogWriterManager');
        }

        if (isset($config['processor_plugin_manager'])
            && is_string($config['processor_plugin_manager'])
            && $services->has($config['processor_plugin_manager'])
        ) {
            $config['processor_plugin_manager'] = $services->get($config['processor_plugin_manager']);
        }

        if ((! isset($config['processor_plugin_manager'])
                || ! $config['processor_plugin_manager'] instanceof AbstractPluginManager)
            && $services->has('LogProcessorManager')
        ) {
            $config['processor_plugin_manager'] = $services->get('LogProcessorManager');
        }

        if (! isset($config['writers'])) {
            return;
        }

        foreach ($config['writers'] as $index => $writerConfig) {
            if (isset($writerConfig['name'])
                && ('db' === $writerConfig['name']
                    || Writer\Db::class === $writerConfig['name']
                    || 'zendlogwriterdb' === $writerConfig['name']
                )
                && isset($writerConfig['options']['db'])
                && is_string($writerConfig['options']['db'])
                && $services->has($writerConfig['options']['db'])
            ) {
                // Retrieve the DB service from the service locator, and
                // inject it into the configuration.
                $db = $services->get($writerConfig['options']['db']);
                $config['writers'][$index]['options']['db'] = $db;
                continue;
            }

            if (isset($writerConfig['name'])
                && ('mongo' === $writerConfig['name']
                    || Writer\Mongo::class === $writerConfig['name']
                    || 'zendlogwritermongo' === $writerConfig['name']
                )
                && isset($writerConfig['options']['mongo'])
                && is_string($writerConfig['options']['mongo'])
                && $services->has($writerConfig['options']['mongo'])
            ) {
                // Retrieve the Mongo service from the service locator, and
                // inject it into the configuration.
                $mongoClient = $services->get($writerConfig['options']['mongo']);
                $config['writers'][$index]['options']['mongo'] = $mongoClient;
                continue;
            }

            if (isset($writerConfig['name'])
                && ('mongodb' === $writerConfig['name']
                    || Writer\MongoDB::class === $writerConfig['name']
                    || 'zendlogwritermongodb' === $writerConfig['name']
                )
                && isset($writerConfig['options']['manager'])
                && is_string($writerConfig['options']['manager'])
                && $services->has($writerConfig['options']['manager'])
            ) {
                // Retrieve the MongoDB Manager service from the service locator, and
                // inject it into the configuration.
                $manager = $services->get($writerConfig['options']['manager']);
                $config['writers'][$index]['options']['manager'] = $manager;
                continue;
            }
        }
    }
}
