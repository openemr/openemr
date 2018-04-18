<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\Router;

use Interop\Container\ContainerInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Specialized invokable/abstract factory for use with RoutePluginManager.
 *
 * Can be mapped directly to specific route plugin names, or used as an
 * abstract factory to map FQCN services to invokables.
 */
class RouteInvokableFactory implements
    AbstractFactoryInterface,
    FactoryInterface
{
    /**
     * Options used to create instance (used with zend-servicemanager v2)
     *
     * @var array
     */
    protected $creationOptions = [];

    /**
     * Can we create a route instance with the given name? (v3)
     *
     * Only works for FQCN $routeName values, for classes that implement RouteInterface.
     *
     * @param ContainerInterface $container
     * @param string $routeName
     * @return bool
     */
    public function canCreate(ContainerInterface $container, $routeName)
    {
        if (! class_exists($routeName)) {
            return false;
        }

        if (! is_subclass_of($routeName, RouteInterface::class)) {
            return false;
        }

        return true;
    }

    /**
     * Can we create a route instance with the given name? (v2)
     *
     * Proxies to canCreate().
     *
     * @param ServiceLocatorInterface $container
     * @param string $normalizedName
     * @param string $routeName
     * @return bool
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $container, $normalizedName, $routeName)
    {
        return $this->canCreate($container, $routeName);
    }

    /**
     * Create and return a RouteInterface instance.
     *
     * If the specified $routeName class does not exist or does not implement
     * RouteInterface, this method will raise an exception.
     *
     * Otherwise, it uses the class' `factory()` method with the provided
     * $options to produce an instance.
     *
     * @param ContainerInterface $container
     * @param string $routeName
     * @param null|array $options
     * @return RouteInterface
     */
    public function __invoke(ContainerInterface $container, $routeName, array $options = null)
    {
        $options = $options ?: [];

        if (! class_exists($routeName)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s: failed retrieving invokable class "%s"; class does not exist',
                __CLASS__,
                $routeName
            ));
        }

        if (! is_subclass_of($routeName, RouteInterface::class)) {
            throw new ServiceNotCreatedException(sprintf(
                '%s: failed retrieving invokable class "%s"; class does not implement %s',
                __CLASS__,
                $routeName,
                RouteInterface::class
            ));
        }

        return $routeName::factory($options);
    }

    /**
     * Create a route instance with the given name. (v2)
     *
     * Proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @param string $normalizedName
     * @param string $routeName
     * @return RouteInterface
     */
    public function createServiceWithName(ServiceLocatorInterface $container, $normalizedName, $routeName)
    {
        return $this($container, $routeName, $this->creationOptions);
    }

    /**
     * Create and return RouteInterface instance
     *
     * For use with zend-servicemanager v2; proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @return RouteInterface
     */
    public function createService(ServiceLocatorInterface $container, $normalizedName = null, $routeName = null)
    {
        $routeName = $routeName ?: RouteInterface::class;
        return $this($container, $routeName, $this->creationOptions);
    }

    /**
     * Set options to use when creating a service (v2)
     *
     * @param array $creationOptions
     */
    public function setCreationOptions(array $creationOptions)
    {
        $this->creationOptions = $creationOptions;
    }
}
