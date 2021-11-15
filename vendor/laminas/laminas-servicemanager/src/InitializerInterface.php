<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager;

/**
 * Backwards-compatibility shim for InitializerInterface.
 *
 * Implementations should update to implement only Laminas\ServiceManager\Initializer\InitializerInterface.
 *
 * If upgrading from v2, take the following steps:
 *
 * - rename the method `initialize()` to `__invoke()`, and:
 *   - rename the `$serviceLocator` argument to `$container`, and change the
 *     typehint to `Interop\Container\ContainerInterface`
 *   - swap the order of the arguments (so that `$instance` comes second)
 * - create an `initialize()` method as defined in this interface, and have it
 *   proxy to `__invoke()`, passing the arguments in the new order.
 *
 * Once you have tested your code, you can then update your class to only implement
 * Laminas\ServiceManager\Initializer\InitializerInterface, and remove the `initialize()`
 * method.
 *
 * @deprecated Use Laminas\ServiceManager\Initializer\InitializerInterface instead.
 */
interface InitializerInterface extends Initializer\InitializerInterface
{
    /**
     * Initialize
     *
     * @param $instance
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function initialize($instance, ServiceLocatorInterface $serviceLocator);
}
