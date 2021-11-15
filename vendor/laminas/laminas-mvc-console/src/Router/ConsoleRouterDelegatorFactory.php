<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Router;

use Interop\Container\ContainerInterface;
use Laminas\Console\Console;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Delegator factory for the Router service.
 *
 * If a console environment is detected, returns the ConsoleRouter service
 * instead of the default router.
 */
class ConsoleRouterDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * Known router names/aliases; allows auto-selection of console router.
     *
     * @var string[]
     */
    private $knownRouterNames = [
        'router',
        'laminas\\router\routestackinterface',
    ];

    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return \Laminas\Mvc\Router\RouteStackInterface
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        // Console environment?
        if ($name === 'ConsoleRouter'                                      // force console router
            || (in_array(strtolower($name), $this->knownRouterNames, true)
                && Console::isConsole())                                   // auto detect console
        ) {
            return $container->get('ConsoleRouter');
        }

        return $callback();
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
     * @return \Laminas\Mvc\Router\RouteStackInterface
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }
}
