<?php

/**
 * @see       https://github.com/laminas/laminas-modulemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-modulemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-modulemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ModuleManager\Listener;

use Laminas\EventManager\ListenerAggregateInterface;

interface ServiceListenerInterface extends ListenerAggregateInterface
{
    /**
     * Provide metadata describing how to aggregate service/plugin manager configuration.
     *
     * - $serviceManager is the service name for the service/plugin manager.
     * - $key is the configuration key containing configuration for it.
     * - $moduleInterface is the interface indicating a configuration provider for it.
     * - $method is used for duck-typing configuration providers.
     *
     * @param  string $serviceManager  Service name for service/plugin manager
     * @param  string $key             Configuration key
     * @param  string $moduleInterface FQCN as string
     * @param  string $method          Method name
     */
    public function addServiceManager($serviceManager, $key, $moduleInterface, $method);

    /**
     * @param  array $configuration
     * @return ServiceListenerInterface
     */
    public function setDefaultServiceConfig($configuration);
}
