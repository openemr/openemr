<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Service;

use Interop\Container\ContainerInterface;
use Laminas\Console\Console;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\DelegatorFactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Helper as ViewHelper;
use Laminas\View\HelperPluginManager;

/**
 * Delegator factory for the laminas-view helper manager.
 *
 * Injects the alternate Url and BasePath helper factories if the current
 * environment is a console environment.
 */
class ConsoleViewHelperManagerDelegatorFactory implements DelegatorFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param string $name
     * @param callable $callback
     * @param null|array $options
     * @return HelperPluginManager
     */
    public function __invoke(ContainerInterface $container, $name, callable $callback, array $options = null)
    {
        $plugins = $callback();

        if (! Console::isConsole()) {
            return $plugins;
        }

        return $this->injectOverrideFactories($plugins, $container);
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
     * @return HelperPluginManager
     */
    public function createDelegatorWithName(ServiceLocatorInterface $container, $name, $requestedName, $callback)
    {
        return $this($container, $requestedName, $callback);
    }

    /**
     * @param HelperPluginManager $plugins
     * @param ContainerInterface $container
     * @return HelperPluginManager
     */
    private function injectOverrideFactories(HelperPluginManager $plugins, ContainerInterface $container)
    {
        $urlFactory = $this->createUrlHelperFactory($container);
        $plugins->setFactory(ViewHelper\Url::class, $urlFactory);
        $plugins->setFactory('laminasviewhelperurl', $urlFactory);

        $basePathFactory = $this->createBasePathHelperFactory($container);
        $plugins->setFactory(ViewHelper\BasePath::class, $basePathFactory);
        $plugins->setFactory('laminasviewhelperbasepath', $basePathFactory);

        return $plugins;
    }

    /**
     * Create and return a factory for creating a URL helper.
     *
     * Retrieves the application and router from the servicemanager,
     * and the route match from the MvcEvent composed by the application,
     * using them to configure the helper.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createUrlHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $helper = new ViewHelper\Url;
            $helper->setRouter($services->get('HttpRouter'));

            $match = $services->get('Application')
                ->getMvcEvent()
                ->getRouteMatch()
            ;

            if ($match instanceof RouteMatch) {
                $helper->setRouteMatch($match);
            }

            return $helper;
        };
    }

    /**
     * Create and return a factory for creating a BasePath helper.
     *
     * Uses configuration and request services to configure the helper.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createBasePathHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $config = $services->has('config') ? $services->get('config') : [];
            $helper = new ViewHelper\BasePath;

            if (isset($config['view_manager']['base_path_console'])) {
                $helper->setBasePath($config['view_manager']['base_path_console']);
                return $helper;
            }

            if (isset($config['view_manager']) && isset($config['view_manager']['base_path'])) {
                $helper->setBasePath($config['view_manager']['base_path']);
                return $helper;
            }

            $request = $services->get('Request');

            if (is_callable([$request, 'getBasePath'])) {
                $helper->setBasePath($request->getBasePath());
            }

            return $helper;
        };
    }
}
