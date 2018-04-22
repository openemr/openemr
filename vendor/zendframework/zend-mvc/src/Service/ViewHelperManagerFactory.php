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
use Zend\Console\Console;
use Zend\Mvc\Router\RouteMatch;
use Zend\ServiceManager\ConfigInterface;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\View\Helper as ViewHelper;
use Zend\View\HelperPluginManager;

class ViewHelperManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = HelperPluginManager::class;

    /**
     * An array of helper configuration classes to ensure are on the helper_map stack.
     *
     * These are *not* imported; that way they can be optional dependencies.
     *
     * @todo Re-enable these once their components have been updated to zend-servicemanager v3
     * @var array
     */
    protected $defaultHelperMapClasses = [
        'Zend\Form\View\HelperConfig',
        'Zend\I18n\View\HelperConfig',
        'Zend\Navigation\View\HelperConfig',
    ];

    /**
     * Create and return the view helper manager
     *
     * @param  ContainerInterface $container
     * @return HelperPluginManager
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $options ?: [];
        $options['factories'] = isset($options['factories']) ? $options['factories'] : [];
        $plugins = parent::__invoke($container, $requestedName, $options);

        // Configure default helpers from other components
        $plugins = $this->configureHelpers($plugins);

        // Override plugin factories
        $plugins = $this->injectOverrideFactories($plugins, $container);

        return $plugins;
    }

    /**
     * Configure helpers from other components.
     *
     * Loops through the list of default helper configuration classes, and uses
     * each to configure the helper plugin manager.
     *
     * @param HelperPluginManager $plugins
     * @return HelperPluginManager
     */
    private function configureHelpers(HelperPluginManager $plugins)
    {
        foreach ($this->defaultHelperMapClasses as $configClass) {
            if (! is_string($configClass) || ! class_exists($configClass)) {
                continue;
            }

            $config = new $configClass();

            if (! $config instanceof ConfigInterface) {
                throw new ServiceNotCreatedException(sprintf(
                    'Invalid service manager configuration class provided; received "%s", expected class implementing %s',
                    $configClass,
                    ConfigInterface::class
                ));
            }

            $config->configureServiceManager($plugins);
        }

        return $plugins;
    }

    /**
     * Inject override factories into the plugin manager.
     *
     * @param HelperPluginManager $plugins
     * @param ContainerInterface $services
     * @return HelperPluginManager
     */
    private function injectOverrideFactories(HelperPluginManager $plugins, ContainerInterface $services)
    {
        // Configure URL view helper
        $urlFactory = $this->createUrlHelperFactory($services);
        $plugins->setFactory(ViewHelper\Url::class, $urlFactory);
        $plugins->setFactory('zendviewhelperurl', $urlFactory);

        // Configure base path helper
        $basePathFactory = $this->createBasePathHelperFactory($services);
        $plugins->setFactory(ViewHelper\BasePath::class, $basePathFactory);
        $plugins->setFactory('zendviewhelperbasepath', $basePathFactory);

        // Configure doctype view helper
        $doctypeFactory = $this->createDoctypeHelperFactory($services);
        $plugins->setFactory(ViewHelper\Doctype::class, $doctypeFactory);
        $plugins->setFactory('zendviewhelperdoctype', $doctypeFactory);

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
            $router = Console::isConsole() ? 'HttpRouter' : 'Router';
            $helper->setRouter($services->get($router));

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

            if (Console::isConsole()
                && isset($config['view_manager']['base_path_console'])
            ) {
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

    /**
     * Create and return a Doctype helper factory.
     *
     * Other view helpers depend on this to decide which spec to generate their tags
     * based on. This is why it must be set early instead of later in the layout phtml.
     *
     * @param ContainerInterface $services
     * @return callable
     */
    private function createDoctypeHelperFactory(ContainerInterface $services)
    {
        return function () use ($services) {
            $config = $services->has('config') ? $services->get('config') : [];
            $config = isset($config['view_manager']) ? $config['view_manager'] : [];
            $helper = new ViewHelper\Doctype;
            if (isset($config['doctype']) && $config['doctype']) {
                $helper->setDoctype($config['doctype']);
            }
            return $helper;
        };
    }
}
