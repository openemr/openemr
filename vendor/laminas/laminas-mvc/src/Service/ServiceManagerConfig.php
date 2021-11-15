<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventManagerAwareInterface;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Config;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;

class ServiceManagerConfig extends Config
{

    /**
     * Default service configuration.
     *
     * In addition to these, the constructor registers several factories and
     * initializers; see that method for details.
     *
     * @var array
     */
    protected $config = [
        'abstract_factories' => [],
        'aliases'            => [
            'EventManagerInterface'            => EventManager::class,
            EventManagerInterface::class       => 'EventManager',
            ModuleManager::class               => 'ModuleManager',
            ServiceListener::class             => 'ServiceListener',
            SharedEventManager::class          => 'SharedEventManager',
            'SharedEventManagerInterface'      => 'SharedEventManager',
            SharedEventManagerInterface::class => 'SharedEventManager',
        ],
        'delegators' => [],
        'factories'  => [
            'EventManager'            => EventManagerFactory::class,
            'ModuleManager'           => ModuleManagerFactory::class,
            'ServiceListener'         => ServiceListenerFactory::class,
        ],
        'lazy_services' => [],
        'initializers'  => [],
        'invokables'    => [],
        'services'      => [],
        'shared'        => [
            'EventManager' => false,
        ],
    ];

    /**
     * Constructor
     *
     * Merges internal arrays with those passed via configuration, and also
     * defines:
     *
     * - factory for the service 'SharedEventManager'.
     * - initializer for EventManagerAwareInterface implementations
     *
     * @param  array $config
     */
    public function __construct(array $config = [])
    {
        $this->config['factories']['ServiceManager'] = function ($container) {
            return $container;
        };

        $this->config['factories']['SharedEventManager'] = function () {
            return new SharedEventManager();
        };

        $this->config['initializers'] = ArrayUtils::merge($this->config['initializers'], [
            'EventManagerAwareInitializer' => function ($first, $second) {
                if ($first instanceof ContainerInterface) {
                    $container = $first;
                    $instance = $second;
                } else {
                    $container = $second;
                    $instance = $first;
                }

                if (! $instance instanceof EventManagerAwareInterface) {
                    return;
                }

                $eventManager = $instance->getEventManager();

                // If the instance has an EM WITH an SEM composed, do nothing.
                if ($eventManager instanceof EventManagerInterface
                    && $eventManager->getSharedManager() instanceof SharedEventManagerInterface
                ) {
                    return;
                }

                $instance->setEventManager($container->get('EventManager'));
            },
        ]);

        parent::__construct($config);
    }

    /**
     * Configure service container.
     *
     * Uses the configuration present in the instance to configure the provided
     * service container.
     *
     * Before doing so, it adds a "service" entry for the ServiceManager class,
     * pointing to the provided service container.
     *
     * @param ServiceManager $services
     * @return ServiceManager
     */
    public function configureServiceManager(ServiceManager $services)
    {
        $this->config['services'][ServiceManager::class] = $services;

        // This is invoked as part of the bootstrapping process, and requires
        // the ability to override services.
        $services->setAllowOverride(true);
        parent::configureServiceManager($services);
        $services->setAllowOverride(false);

        return $services;
    }

    /**
     * Return all service configuration
     *
     * @return array
     */
    public function toArray()
    {
        return $this->config;
    }
}
