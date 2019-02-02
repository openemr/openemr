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
use Zend\Di\Di;
use Zend\Di\Exception\ClassNotFoundException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Create and return instances from a DI container and/or the parent container.
 *
 * This abstract factory can be mapped to arbitrary class names, and used to
 * pull them from the composed Di instance, using the following behaviors:
 *
 * - If USE_SL_BEFORE_DI is passed as the second argument to the constructor,
 *   the factory will attempt to fetch the service from the passed container
 *   first, and fall back to the composed DI container only on failure.
 * - If USE_SL_AFTER_DI is passed as the second argument to the constructor,
 *   the factory will attempt to fetch the service from the composed DI
 *   container first, and fall back to the passed container only on failure.
 * - If USE_SL_NONE is passed as the second argument to the constructor (or no
 *   argument is passed), then the factory will only fetch from the composed
 *   DI container.
 *
 * Unlike DiAbstractServiceFactory and DiServiceFactory, this abstract factory
 * requires that classes requested are in a provided whitelist; if the requested
 * service is not, an exception is raised. This is useful to provide a scoped
 * container, e.g., to limit to known controller classes, etc.
 *
 * @deprecated Since 2.7.9. The factory is now defined in zend-servicemanager-di,
 *     and removed in 3.0.0. Use Zend\ServiceManager\Di\DiStrictAbstractServiceFactory
 *     from zend-servicemanager-di if you are using zend-servicemanager v3, and/or when
 *     ready to migrate to zend-mvc 3.0.
 */
class DiStrictAbstractServiceFactory extends Di implements AbstractFactoryInterface
{
    /**@#+
     * constants
     */
    const USE_SL_BEFORE_DI = 'before';
    const USE_SL_AFTER_DI  = 'after';
    const USE_SL_NONE      = 'none';
    /**@#-*/

    /**
     * @var Di
     */
    protected $di = null;

    /**
     * @var string
     */
    protected $useContainer = self::USE_SL_AFTER_DI;

    /**
     * @var ContainerInterface
     */
    protected $container = null;

    /**
     * @var array an array of whitelisted service names (keys are the service names)
     */
    protected $allowedServiceNames = [];

    /**
     * @param Di $di
     * @param string $useContainer
     */
    public function __construct(Di $di, $useContainer = self::USE_SL_NONE)
    {
        $this->useContainer = $useContainer;

        // Since we are using this in a proxy-fashion, localize state
        $this->di              = $di;
        $this->definitions     = $this->di->definitions;
        $this->instanceManager = $this->di->instanceManager;
    }

    /**
     * @param array $allowedServiceNames
     */
    public function setAllowedServiceNames(array $allowedServiceNames)
    {
        $this->allowedServiceNames = array_flip(array_values($allowedServiceNames));
    }

    /**
     * @return array
     */
    public function getAllowedServiceNames()
    {
        return array_keys($this->allowedServiceNames);
    }

    /**
     * {@inheritDoc}
     *
     * Allows creation of services only when in a whitelist
     */
    public function __invoke(ContainerInterface $container, $name, array $options = null)
    {
        if (! isset($this->allowedServiceNames[$name])) {
            throw new Exception\InvalidServiceException(sprintf(
                'Service "%s" is not whitelisted',
                $name
            ));
        }

        $this->container = ($container instanceof AbstractPluginManager)
            ? $container->getServiceLocator()
            : $container;

        return parent::get($name);
    }

    /**
     * {@inheritDoc}
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     */
    public function createServiceWithName(ServiceLocatorInterface $container, $serviceName, $requestedName)
    {
        return $this($container, $requestedName);
    }

    /**
     * Overrides Zend\Di to allow the given container's services to be reused by Di itself
     *
     * {@inheritDoc}
     *
     * @throws Exception\InvalidServiceNameException
     */
    public function get($name, array $params = [])
    {
        if (null === $this->container) {
            throw new Exception\DomainException(
                'No ServiceLocator defined, use `createServiceWithName` instead of `get`'
            );
        }

        if (self::USE_SL_BEFORE_DI === $this->useContainer && $this->container->has($name)) {
            return $this->container->get($name);
        }

        try {
            return parent::get($name, $params);
        } catch (ClassNotFoundException $e) {
            if (self::USE_SL_AFTER_DI === $this->useContainer && $this->container->has($name)) {
                return $this->container->get($name);
            }

            throw new Exception\ServiceNotFoundException(
                sprintf('Service %s was not found in this DI instance', $name),
                null,
                $e
            );
        }
    }

    /**
     * {@inheritDoc}
     *
     * Allows creation of services only when in a whitelist.
     */
    public function canCreate(ContainerInterface $container, $requestedName)
    {
        // won't check if the service exists, we are trusting the user's whitelist
        return isset($this->allowedServiceNames[$requestedName]);
    }

    /**
     * {@inheritDoc}
     *
     * For use with zend-servicemanager v2; proxies to canCreate().
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $name, $requestedName)
    {
        return $this->canCreate($container, $requestedName);
    }
}
