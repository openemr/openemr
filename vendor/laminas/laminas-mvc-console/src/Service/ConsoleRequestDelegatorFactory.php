<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Console\Console;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Delegator factory for the Request service.
 *
 * If a console environment is detected, returns a ConsoleRequest instead
 * of the default request.
 */
class ConsoleRequestDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return ConsoleRequest|\Laminas\Http\Request
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        if (! Console::isConsole()) {
            return $callback();
        }

        return new ConsoleRequest();
    }

    /**
     * laminas-servicemanager v2 compatibility.
     *
     * Proxies to __invoke().
     *
     * @param ServiceLocatorInterface $container
     * @param string $name
     * @param string $requestedName
     * @param callable $callback
     * @return ConsoleRequest|\Laminas\Http\Request
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
