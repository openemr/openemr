<?php

/**
 * @see       https://github.com/laminas/laminas-hydrator for the canonical source repository
 * @copyright https://github.com/laminas/laminas-hydrator/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-hydrator/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Laminas\Hydrator;

use Laminas\ModuleManager\ModuleManager;

class Module
{
    /**
     * Return default laminas-hydrator configuration for laminas-mvc applications.
     *
     * @return mixed[]
     */
    public function getConfig() : array
    {
        $provider = new ConfigProvider();

        return [
            'service_manager' => $provider->getDependencyConfig(),
        ];
    }

    /**
     * Register a specification for the HydratorManager with the ServiceListener.
     */
    public function init(ModuleManager $moduleManager) : void
    {
        $event           = $moduleManager->getEvent();
        $container       = $event->getParam('ServiceManager');
        $serviceListener = $container->get('ServiceListener');

        $serviceListener->addServiceManager(
            'HydratorManager',
            'hydrators',
            HydratorProviderInterface::class,
            'getHydratorConfig'
        );
    }
}
