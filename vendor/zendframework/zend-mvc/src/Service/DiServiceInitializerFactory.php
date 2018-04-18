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
use Zend\Mvc\Exception;
use Zend\ServiceManager\Di\DiServiceInitializer;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManager;

/**
 * @deprecated Since 2.7.9. The factory is now defined in zend-servicemanager-di,
 *     and removed in 3.0.0. Use Zend\ServiceManager\Di\DiServiceInitializerFactory
 *     from zend-servicemanager-di if you are using zend-servicemanager v3, and/or when
 *     ready to migrate to zend-mvc 3.0.
 */
class DiServiceInitializerFactory implements FactoryInterface
{
    /**
     * Class responsible for instantiating a DiServiceInitializer
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return DiServiceInitializer
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        if (! class_exists(DiServiceInitializer::class)) {
            throw new Exception\RuntimeException(sprintf(
                "%s is not compatible with zend-servicemanager v3, which you are currently using. \n"
                . "Please run 'composer require zendframework/zend-servicemanager-di', and then update\n"
                . "your configuration to use Zend\ServiceManager\Di\DiServiceInitializerFactory instead.",
                __CLASS__
            ));
        }

        return new DiServiceInitializer($container->get('Di'), $container);
    }

    /**
     * Create and return DiServiceInitializer instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return DiServiceInitializer
     */
    public function createService(ServiceLocatorInterface $container)
    {
        return $this($container, DiServiceInitializer::class);
    }
}
