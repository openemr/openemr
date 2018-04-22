<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Session\Service;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\Session\Config\ConfigInterface;
use Zend\Session\Container;
use Zend\Session\SaveHandler\SaveHandlerInterface;
use Zend\Session\SessionManager;
use Zend\Session\Storage\StorageInterface;
use Zend\Session\ManagerInterface;

class SessionManagerFactory implements FactoryInterface
{
    /**
     * Default configuration for manager behavior
     *
     * @var array
     */
    protected $defaultManagerConfig = [
        'enable_default_container_manager' => true,
    ];

    /**
     * Create session manager object (v3 usage).
     *
     * Will consume any combination (or zero) of the following services, when
     * present, to construct the SessionManager instance:
     *
     * - Zend\Session\Config\ConfigInterface
     * - Zend\Session\Storage\StorageInterface
     * - Zend\Session\SaveHandler\SaveHandlerInterface
     *
     * The first two have corresponding factories inside this namespace. The
     * last, however, does not, due to the differences in implementations, and
     * the fact that save handlers will often be written in userland. As such
     * if you wish to attach a save handler to the manager, you will need to
     * write your own factory, and assign it to the service name
     * "Zend\Session\SaveHandler\SaveHandlerInterface", (or alias that name
     * to your own service).
     *
     * You can configure limited behaviors via the "session_manager" key of the
     * Config service. Currently, these include:
     *
     * - enable_default_container_manager: whether to inject the created instance
     *   as the default manager for Container instances. The default value for
     *   this is true; set it to false to disable.
     * - validators: ...
     *
     * @param ContainerInterface $container
     * @param string $requestedName
     * @param array $options
     * @return SessionManager
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $config        = null;
        $storage       = null;
        $saveHandler   = null;
        $validators    = [];
        $managerConfig = $this->defaultManagerConfig;
        $options       = [];

        if ($container->has(ConfigInterface::class)) {
            $config = $container->get(ConfigInterface::class);
            if (! $config instanceof ConfigInterface) {
                throw new ServiceNotCreatedException(sprintf(
                    'SessionManager requires that the %s service implement %s; received "%s"',
                    ConfigInterface::class,
                    ConfigInterface::class,
                    (is_object($config) ? get_class($config) : gettype($config))
                ));
            }
        }

        if ($container->has(StorageInterface::class)) {
            $storage = $container->get(StorageInterface::class);
            if (! $storage instanceof StorageInterface) {
                throw new ServiceNotCreatedException(sprintf(
                    'SessionManager requires that the %s service implement %s; received "%s"',
                    StorageInterface::class,
                    StorageInterface::class,
                    (is_object($storage) ? get_class($storage) : gettype($storage))
                ));
            }
        }

        if ($container->has(SaveHandlerInterface::class)) {
            $saveHandler = $container->get(SaveHandlerInterface::class);
            if (! $saveHandler instanceof SaveHandlerInterface) {
                throw new ServiceNotCreatedException(sprintf(
                    'SessionManager requires that the %s service implement %s; received "%s"',
                    SaveHandlerInterface::class,
                    SaveHandlerInterface::class,
                    (is_object($saveHandler) ? get_class($saveHandler) : gettype($saveHandler))
                ));
            }
        }

        // Get session manager configuration, if any, and merge with default configuration
        if ($container->has('config')) {
            $configService = $container->get('config');
            if (isset($configService['session_manager'])
                && is_array($configService['session_manager'])
            ) {
                $managerConfig = array_merge($managerConfig, $configService['session_manager']);
            }

            if (isset($managerConfig['validators'])) {
                $validators = $managerConfig['validators'];
            }

            if (isset($managerConfig['options'])) {
                $options = $managerConfig['options'];
            }
        }

        $managerClass = class_exists($requestedName) ? $requestedName : SessionManager::class;
        if (! is_subclass_of($managerClass, ManagerInterface::class)) {
            throw new ServiceNotCreatedException(sprintf(
                'SessionManager requires that the %s service implement %s',
                $managerClass,
                ManagerInterface::class
            ));
        }

        $manager = new $managerClass($config, $storage, $saveHandler, $validators, $options);

        // If configuration enables the session manager as the default manager for container
        // instances, do so.
        if (isset($managerConfig['enable_default_container_manager'])
            && $managerConfig['enable_default_container_manager']
        ) {
            Container::setDefaultManager($manager);
        }

        return $manager;
    }

    /**
     * Create a SessionManager instance (v2 usage)
     *
     * @param ServiceLocatorInterface $services
     * @param null|string $canonicalName
     * @param string $requestedName
     * @return SessionManager
     */
    public function createService(
        ServiceLocatorInterface $services,
        $canonicalName = null,
        $requestedName = SessionManager::class
    ) {
        return $this($services, $requestedName);
    }
}
