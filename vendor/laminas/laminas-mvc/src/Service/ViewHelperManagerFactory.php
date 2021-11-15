<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\View\Helper as ViewHelper;
use Laminas\View\HelperPluginManager;

class ViewHelperManagerFactory extends AbstractPluginManagerFactory
{
    const PLUGIN_MANAGER_CLASS = HelperPluginManager::class;

    /**
     * An array of helper configuration classes to ensure are on the helper_map stack.
     *
     * These are *not* imported; that way they can be optional dependencies.
     *
     * @todo Remove these once their components have Modules defined.
     * @var array
     */
    protected $defaultHelperMapClasses = [];

    /**
     * Create and return the view helper manager
     *
     * @param  ContainerInterface $container
     * @param  string             $requestedName
     * @param  null|array         $options
     * @return HelperPluginManager
     * @throws ServiceNotCreatedException
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $options = $options ?: [];
        $options['factories'] = isset($options['factories']) ? $options['factories'] : [];
        $plugins = parent::__invoke($container, $requestedName, $options);

        // Override plugin factories
        $plugins = $this->injectOverrideFactories($plugins, $container);

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
        $plugins->setFactory('laminasviewhelperurl', $urlFactory);

        // Configure base path helper
        $basePathFactory = $this->createBasePathHelperFactory($services);
        $plugins->setFactory(ViewHelper\BasePath::class, $basePathFactory);
        $plugins->setFactory('laminasviewhelperbasepath', $basePathFactory);

        // Configure doctype view helper
        $doctypeFactory = $this->createDoctypeHelperFactory($services);
        $plugins->setFactory(ViewHelper\Doctype::class, $doctypeFactory);
        $plugins->setFactory('laminasviewhelperdoctype', $doctypeFactory);

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
