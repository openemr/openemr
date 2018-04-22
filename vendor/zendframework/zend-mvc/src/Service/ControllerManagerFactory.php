<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Service;

use Interop\Container\ContainerInterface;
use Zend\Mvc\Controller\ControllerManager;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ControllerManagerFactory implements FactoryInterface
{
    /**
     * Create the controller manager service
     *
     * Creates and returns an instance of ControllerManager. The
     * only controllers this manager will allow are those defined in the
     * application configuration's "controllers" array. If a controller is
     * matched, the scoped manager will attempt to load the controller.
     * Finally, it will attempt to inject the controller plugin manager
     * if the controller implements a setPluginManager() method.
     *
     * @param  ContainerInterface $container
     * @param  string $Name
     * @param  null|array $options
     * @return ControllerManager
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if ($options) {
            return new ControllerManager($container, $options);
        }
        return new ControllerManager($container);
    }

    /**
     * Create and return ControllerManager instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return ControllerManager
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, ControllerManager::class);
    }
}
