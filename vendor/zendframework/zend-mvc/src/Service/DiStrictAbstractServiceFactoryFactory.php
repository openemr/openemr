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
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @deprecated Since 2.7.9. The factory is now defined in zend-servicemanager-di,
 *     and removed in 3.0.0. Use Zend\ServiceManager\Di\DiStrictAbstractServiceFactoryFactory
 *     from zend-servicemanager-di if you are using zend-servicemanager v3, and/or when
 *     ready to migrate to zend-mvc 3.0.
 */
class DiStrictAbstractServiceFactoryFactory implements FactoryInterface
{
    /**
     * Class responsible for instantiating a DiStrictAbstractServiceFactory
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return DiStrictAbstractServiceFactory
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        $diAbstractFactory = new DiStrictAbstractServiceFactory(
            $container->get('Di'),
            DiStrictAbstractServiceFactory::USE_SL_BEFORE_DI
        );
        $config = $container->get('config');

        if (isset($config['di']['allowed_controllers'])) {
            $diAbstractFactory->setAllowedServiceNames($config['di']['allowed_controllers']);
        }

        return $diAbstractFactory;
    }

    /**
     * Create and return DiStrictAbstractServiceFactory instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return DiStrictAbstractServiceFactory
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, DiStrictAbstractServiceFactory::class);
    }
}
