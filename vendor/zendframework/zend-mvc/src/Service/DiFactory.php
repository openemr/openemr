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
use Zend\Di\Config;
use Zend\Di\Di;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @deprecated Since 2.7.9. The factory is now defined in zend-servicemanager-di,
 *     and removed in 3.0.0. Use Zend\ServiceManager\Di\DiFactory from
 *     from zend-servicemanager-di if you are using zend-servicemanager v3, and/or when
 *     ready to migrate to zend-mvc 3.0.
 */
class DiFactory implements FactoryInterface
{
    /**
     * Create and return abstract factory seeded by dependency injector
     *
     * Creates and returns an abstract factory seeded by the dependency
     * injector. If the "di" key of the configuration service is set, that
     * sub-array is passed to a DiConfig object and used to configure
     * the DI instance. The DI instance is then used to seed the
     * DiAbstractServiceFactory, which is then registered with the service
     * manager.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return Di
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $di     = new Di();
        $config = $container->has('config') ? $container->get('config') : [];

        if (isset($config['di'])) {
            (new Config($config['di']))->configure($di);
        }

        return $di;
    }

    /**
     * Create and return Di instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return Di
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, Di::class);
    }
}
