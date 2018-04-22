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
use Zend\Mvc\Application;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

class ApplicationFactory implements FactoryInterface
{
    /**
     * Create the Application service (v3)
     *
     * Creates a Zend\Mvc\Application service, passing it the configuration
     * service and the service manager instance.
     *
     * @param  ContainerInterface $container
     * @param  string $name
     * @param  null|array $options
     * @return Application
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        return new Application(
            $container->get('config'),
            $container,
            $container->get('EventManager'),
            $container->get('Request'),
            $container->get('Response')
        );
    }

    /**
     * Create the Application service (v2)
     *
     * Proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return Application
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Application::class);
    }
}
