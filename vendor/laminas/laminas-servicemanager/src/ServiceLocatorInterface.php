<?php

declare(strict_types=1);

/**
 * @see       https://github.com/laminas/laminas-servicemanager for the canonical source repository
 * @copyright https://github.com/laminas/laminas-servicemanager/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-servicemanager/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\ServiceManager;

use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface as PsrContainerInterface;

/**
 * Interface for service locator
 */
interface ServiceLocatorInterface extends
    PsrContainerInterface,
    InteropContainerInterface
{
    /**
     * Build a service by its name, using optional options (such services are NEVER cached).
     *
     * @param  string $name
     * @param  null|array  $options
     * @return mixed
     * @throws Exception\ServiceNotFoundException If no factory/abstract
     *     factory could be found to create the instance.
     * @throws Exception\ServiceNotCreatedException If factory/delegator fails
     *     to create the instance.
     * @throws ContainerExceptionInterface if any other error occurs
     */
    public function build($name, array $options = null);
}
