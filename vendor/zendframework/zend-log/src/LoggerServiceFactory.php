<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zend-log for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory for logger instances.
 */
class LoggerServiceFactory implements FactoryInterface
{
    /**
     * Factory for zend-servicemanager v3.
     *
     * @param ContainerInterface $container
     * @param string $name
     * @param null|array $options
     * @return Logger
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        // Configure the logger
        $config = $container->get('config');
        $logConfig = isset($config['log']) ? $config['log'] : [];
        return new Logger($logConfig);
    }

    /**
     * Factory for zend-servicemanager v2.
     *
     * Proxies to `__invoke()`.
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return Logger
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        return $this($serviceLocator, Logger::class);
    }
}
