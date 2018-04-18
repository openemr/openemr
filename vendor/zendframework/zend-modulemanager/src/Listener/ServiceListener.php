<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\ModuleManager\Listener;

use Traversable;
use Zend\EventManager\EventManagerInterface;
use Zend\ModuleManager\ModuleEvent;
use Zend\ServiceManager\Config as ServiceConfig;
use Zend\ServiceManager\ConfigInterface as ServiceConfigInterface;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\ArrayUtils;

class ServiceListener implements ServiceListenerInterface
{

    /**
     * Service manager post-configuration.
     *
     * @var ServiceManager
     */
    protected $configuredServiceManager;

    /**
     * @var callable[]
     */
    protected $listeners = [];

    /**
     * Default service manager used to fulfill other SMs that need to be lazy loaded
     *
     * @var ServiceManager
     */
    protected $defaultServiceManager;

    /**
     * Default service configuration for the application service manager.
     *
     * @var array
     */
    protected $defaultServiceConfig;

    /**
     * @var array
     */
    protected $serviceManagers = [];

    /**
     * @param ServiceManager $serviceManager
     * @param null|array $configuration
     */
    public function __construct(ServiceManager $serviceManager, $configuration = null)
    {
        $this->defaultServiceManager = $serviceManager;

        if ($configuration !== null) {
            $this->setDefaultServiceConfig($configuration);
        }
    }

    /**
     * @param  array $configuration
     * @return ServiceListener
     */
    public function setDefaultServiceConfig($configuration)
    {
        $this->defaultServiceConfig = $configuration;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function addServiceManager($serviceManager, $key, $moduleInterface, $method)
    {
        if (is_string($serviceManager)) {
            $smKey = $serviceManager;
        } elseif ($serviceManager instanceof ServiceManager) {
            $smKey = spl_object_hash($serviceManager);
        } else {
            throw new Exception\RuntimeException(sprintf(
                'Invalid service manager provided, expected ServiceManager or string, %s provided',
                (is_object($serviceManager) ? get_class($serviceManager) : gettype($serviceManager))
            ));
        }

        $this->serviceManagers[$smKey] = [
            'service_manager'        => $serviceManager,
            'config_key'             => $key,
            'module_class_interface' => $moduleInterface,
            'module_class_method'    => $method,
            'configuration'          => [],
        ];

        if ($key === 'service_manager' && $this->defaultServiceConfig) {
            $this->serviceManagers[$smKey]['configuration']['default_config'] = $this->defaultServiceConfig;
        }

        return $this;
    }

    /**
     * @param  EventManagerInterface $events
     * @param  int $priority
     * @return ServiceListener
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULE, [$this, 'onLoadModule']);
        $this->listeners[] = $events->attach(ModuleEvent::EVENT_LOAD_MODULES_POST, [$this, 'onLoadModulesPost']);
        return $this;
    }

    /**
     * @param  EventManagerInterface $events
     * @return void
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $key => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$key]);
            }
        }
    }

    /**
     * Retrieve service manager configuration from module, and
     * configure the service manager.
     *
     * If the module does not implement a specific interface and does not
     * implement a specific method, does nothing. Also, if the return value
     * of that method is not a ServiceConfig object, or not an array or
     * Traversable that can seed one, does nothing.
     *
     * The interface and method name can be set by adding a new service manager
     * via the addServiceManager() method.
     *
     * @param  ModuleEvent $e
     * @return void
     */
    public function onLoadModule(ModuleEvent $e)
    {
        $module = $e->getModule();

        foreach ($this->serviceManagers as $key => $sm) {
            if (! $module instanceof $sm['module_class_interface']
                && ! method_exists($module, $sm['module_class_method'])
            ) {
                continue;
            }

            $config = $module->{$sm['module_class_method']}();

            if ($config instanceof ServiceConfigInterface) {
                $config = $this->serviceConfigToArray($config);
            }

            if ($config instanceof Traversable) {
                $config = ArrayUtils::iteratorToArray($config);
            }

            if (! is_array($config)) {
                // If we do not have an array by this point, nothing left to do.
                continue;
            }

            // We are keeping track of which modules provided which configuration to which service managers.
            // The actual merging takes place later. Doing it this way will enable us to provide more powerful
            // debugging tools for showing which modules overrode what.
            $fullname = $e->getModuleName() . '::' . $sm['module_class_method'] . '()';
            $this->serviceManagers[$key]['configuration'][$fullname] = $config;
        }
    }

    /**
     * Use merged configuration to configure service manager
     *
     * If the merged configuration has a non-empty, array 'service_manager'
     * key, it will be passed to a ServiceManager Config object, and
     * used to configure the service manager.
     *
     * @param  ModuleEvent $e
     * @throws Exception\RuntimeException
     * @return void
     */
    public function onLoadModulesPost(ModuleEvent $e)
    {
        $configListener = $e->getConfigListener();
        $config         = $configListener->getMergedConfig(false);

        foreach ($this->serviceManagers as $key => $sm) {
            $smConfig = $this->mergeServiceConfiguration($key, $sm, $config);

            if (! $sm['service_manager'] instanceof ServiceManager) {
                if (! $this->defaultServiceManager->has($sm['service_manager'])) {
                    // No plugin manager registered by that name; nothing to configure.
                    continue;
                }

                $instance = $this->defaultServiceManager->get($sm['service_manager']);
                if (! $instance instanceof ServiceManager) {
                    throw new Exception\RuntimeException(sprintf(
                        'Could not find a valid ServiceManager for %s',
                        $sm['service_manager']
                    ));
                }

                $sm['service_manager'] = $instance;
            }

            $serviceConfig = new ServiceConfig($smConfig);

            // The service listener is meant to operate during bootstrap, and, as such,
            // needs to be able to override existing configuration.
            $allowOverride = $sm['service_manager']->getAllowOverride();
            $sm['service_manager']->setAllowOverride(true);

            $serviceConfig->configureServiceManager($sm['service_manager']);

            $sm['service_manager']->setAllowOverride($allowOverride);
        }
    }

    /**
     * Merge a service configuration container
     *
     * Extracts the various service configuration arrays.
     *
     * @param ServiceConfigInterface|string $config ServiceConfigInterface or
     *     class name resolving to one.
     * @return array
     * @throws Exception\RuntimeException if resolved class name is not a
     *     ServiceConfigInterface implementation.
     * @throws Exception\RuntimeException under zend-servicemanager v2 if the
     *     configuration instance is not specifically a ServiceConfig, as there
     *     is no way to extract service configuration in that case.
     */
    protected function serviceConfigToArray($config)
    {
        if (is_string($config) && class_exists($config)) {
            $class  = $config;
            $config = new $class;
        }

        if (! $config instanceof ServiceConfigInterface) {
            throw new Exception\RuntimeException(sprintf(
                'Invalid service manager configuration class provided; received "%s", expected an instance of %s',
                (is_object($config) ? get_class($config) : (is_scalar($config) ? $config : gettype($config))),
                ServiceConfigInterface::class
            ));
        }

        if (method_exists($config, 'toArray')) {
            // zend-servicemanager v3 interface
            return $config->toArray();
        }

        // For zend-servicemanager v2, we need a Zend\ServiceManager\Config
        // instance specifically.
        if (! $config instanceof ServiceConfig) {
            throw new Exception\RuntimeException(sprintf(
                'Invalid service manager configuration class provided; received "%s", expected an instance of %s',
                (is_object($config) ? get_class($config) : (is_scalar($config) ? $config : gettype($config))),
                ServiceConfig::class
            ));
        }

        // Pull service configuration from discrete methods.
        return [
            'abstract_factories' => $config->getAbstractFactories(),
            'aliases'            => $config->getAliases(),
            'delegators'         => $config->getDelegators(),
            'factories'          => $config->getFactories(),
            'initializers'       => $config->getInitializers(),
            'invokables'         => $config->getInvokables(),
            'services'           => $config->getServices(),
            'shared'             => $config->getShared(),
        ];
    }

    /**
     * Merge all configuration for a given service manager to a single array.
     *
     * @param string $key Named service manager
     * @param array $metadata Service manager metadata
     * @param array $config Merged configuration
     * @return array Service manager-specific configuration
     */
    private function mergeServiceConfiguration($key, array $metadata, array $config)
    {
        if (isset($config[$metadata['config_key']])
            && is_array($config[$metadata['config_key']])
            && ! empty($config[$metadata['config_key']])
        ) {
            $this->serviceManagers[$key]['configuration']['merged_config'] = $config[$metadata['config_key']];
        }

        // Merge all of the things!
        $serviceConfig = [];
        foreach ($this->serviceManagers[$key]['configuration'] as $name => $configs) {
            if (isset($configs['configuration_classes'])) {
                foreach ($configs['configuration_classes'] as $class) {
                    $configs = ArrayUtils::merge($configs, $this->serviceConfigToArray($class));
                }
            }
            $serviceConfig = ArrayUtils::merge($serviceConfig, $configs);
        }

        return $serviceConfig;
    }
}
