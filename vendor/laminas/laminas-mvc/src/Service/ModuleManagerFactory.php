<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\Listener\DefaultListenerAggregate;
use Laminas\ModuleManager\Listener\ListenerOptions;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\ServiceManager\Factory\FactoryInterface;

class ModuleManagerFactory implements FactoryInterface
{
    /**
     * Creates and returns the module manager
     *
     * Instantiates the default module listeners, providing them configuration
     * from the "module_listener_options" key of the ApplicationConfig
     * service. Also sets the default config glob path.
     *
     * Module manager is instantiated and provided with an EventManager, to which
     * the default listener aggregate is attached. The ModuleEvent is also created
     * and attached to the module manager.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return ModuleManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $configuration    = $container->get('ApplicationConfig');
        $listenerOptions  = new ListenerOptions($configuration['module_listener_options']);
        $defaultListeners = new DefaultListenerAggregate($listenerOptions);
        $serviceListener  = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            $container,
            'service_manager',
            'Laminas\ModuleManager\Feature\ServiceProviderInterface',
            'getServiceConfig'
        );

        $serviceListener->addServiceManager(
            'ControllerManager',
            'controllers',
            'Laminas\ModuleManager\Feature\ControllerProviderInterface',
            'getControllerConfig'
        );
        $serviceListener->addServiceManager(
            'ControllerPluginManager',
            'controller_plugins',
            'Laminas\ModuleManager\Feature\ControllerPluginProviderInterface',
            'getControllerPluginConfig'
        );
        $serviceListener->addServiceManager(
            'ViewHelperManager',
            'view_helpers',
            'Laminas\ModuleManager\Feature\ViewHelperProviderInterface',
            'getViewHelperConfig'
        );
        $serviceListener->addServiceManager(
            'RoutePluginManager',
            'route_manager',
            'Laminas\ModuleManager\Feature\RouteProviderInterface',
            'getRouteConfig'
        );

        $events = $container->get('EventManager');
        $defaultListeners->attach($events);
        $serviceListener->attach($events);

        $moduleEvent = new ModuleEvent;
        $moduleEvent->setParam('ServiceManager', $container);

        $moduleManager = new ModuleManager($configuration['modules'], $events);
        $moduleManager->setEvent($moduleEvent);

        return $moduleManager;
    }
}
