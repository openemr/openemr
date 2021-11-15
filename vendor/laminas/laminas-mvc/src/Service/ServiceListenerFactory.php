<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Service;

use Interop\Container\ContainerInterface;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ModuleManager\Listener\ServiceListenerInterface;
use Laminas\Mvc\View;
use Laminas\ServiceManager\Exception\ServiceNotCreatedException;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

class ServiceListenerFactory implements FactoryInterface
{
    /**
     * @var string
     */
    const MISSING_KEY_ERROR = 'Invalid service listener options detected, %s array must contain %s key.';

    /**
     * @var string
     */
    const VALUE_TYPE_ERROR = 'Invalid service listener options detected, %s must be a string, %s given.';

    /**
     * Default mvc-related service configuration -- can be overridden by modules.
     *
     * @var array
     */
    protected $defaultServiceConfig = [
        'aliases' => [
            'application'                                => 'Application',
            'Config'                                     => 'config',
            'configuration'                              => 'config',
            'Configuration'                              => 'config',
            'HttpDefaultRenderingStrategy'               => View\Http\DefaultRenderingStrategy::class,
            'MiddlewareListener'                         => 'Laminas\Mvc\MiddlewareListener',
            'request'                                    => 'Request',
            'response'                                   => 'Response',
            'RouteListener'                              => 'Laminas\Mvc\RouteListener',
            'SendResponseListener'                       => 'Laminas\Mvc\SendResponseListener',
            'View'                                       => 'Laminas\View\View',
            'ViewFeedRenderer'                           => 'Laminas\View\Renderer\FeedRenderer',
            'ViewJsonRenderer'                           => 'Laminas\View\Renderer\JsonRenderer',
            'ViewPhpRendererStrategy'                    => 'Laminas\View\Strategy\PhpRendererStrategy',
            'ViewPhpRenderer'                            => 'Laminas\View\Renderer\PhpRenderer',
            'ViewRenderer'                               => 'Laminas\View\Renderer\PhpRenderer',
            'Laminas\Mvc\Controller\PluginManager'          => 'ControllerPluginManager',
            'Laminas\Mvc\View\Http\InjectTemplateListener'  => 'InjectTemplateListener',
            'Laminas\View\Renderer\RendererInterface'       => 'Laminas\View\Renderer\PhpRenderer',
            'Laminas\View\Resolver\TemplateMapResolver'     => 'ViewTemplateMapResolver',
            'Laminas\View\Resolver\TemplatePathStack'       => 'ViewTemplatePathStack',
            'Laminas\View\Resolver\AggregateResolver'       => 'ViewResolver',
            'Laminas\View\Resolver\ResolverInterface'       => 'ViewResolver',
        ],
        'invokables' => [],
        'factories'  => [
            'Application'                    => ApplicationFactory::class,
            'config'                         => 'Laminas\Mvc\Service\ConfigFactory',
            'ControllerManager'              => 'Laminas\Mvc\Service\ControllerManagerFactory',
            'ControllerPluginManager'        => 'Laminas\Mvc\Service\ControllerPluginManagerFactory',
            'DispatchListener'               => 'Laminas\Mvc\Service\DispatchListenerFactory',
            'HttpExceptionStrategy'          => HttpExceptionStrategyFactory::class,
            'HttpMethodListener'             => 'Laminas\Mvc\Service\HttpMethodListenerFactory',
            'HttpRouteNotFoundStrategy'      => HttpRouteNotFoundStrategyFactory::class,
            'HttpViewManager'                => 'Laminas\Mvc\Service\HttpViewManagerFactory',
            'InjectTemplateListener'         => 'Laminas\Mvc\Service\InjectTemplateListenerFactory',
            'PaginatorPluginManager'         => 'Laminas\Mvc\Service\PaginatorPluginManagerFactory',
            'Request'                        => 'Laminas\Mvc\Service\RequestFactory',
            'Response'                       => 'Laminas\Mvc\Service\ResponseFactory',
            'ViewHelperManager'              => 'Laminas\Mvc\Service\ViewHelperManagerFactory',
            View\Http\DefaultRenderingStrategy::class => HttpDefaultRenderingStrategyFactory::class,
            'ViewFeedStrategy'               => 'Laminas\Mvc\Service\ViewFeedStrategyFactory',
            'ViewJsonStrategy'               => 'Laminas\Mvc\Service\ViewJsonStrategyFactory',
            'ViewManager'                    => 'Laminas\Mvc\Service\ViewManagerFactory',
            'ViewResolver'                   => 'Laminas\Mvc\Service\ViewResolverFactory',
            'ViewTemplateMapResolver'        => 'Laminas\Mvc\Service\ViewTemplateMapResolverFactory',
            'ViewTemplatePathStack'          => 'Laminas\Mvc\Service\ViewTemplatePathStackFactory',
            'ViewPrefixPathStackResolver'    => 'Laminas\Mvc\Service\ViewPrefixPathStackResolverFactory',
            'Laminas\Mvc\MiddlewareListener'    => InvokableFactory::class,
            'Laminas\Mvc\RouteListener'         => InvokableFactory::class,
            'Laminas\Mvc\SendResponseListener'  => SendResponseListenerFactory::class,
            'Laminas\View\Renderer\FeedRenderer' => InvokableFactory::class,
            'Laminas\View\Renderer\JsonRenderer' => InvokableFactory::class,
            'Laminas\View\Renderer\PhpRenderer' => ViewPhpRendererFactory::class,
            'Laminas\View\Strategy\PhpRendererStrategy' => ViewPhpRendererStrategyFactory::class,
            'Laminas\View\View'                 => ViewFactory::class,
        ],
    ];

    /**
     * Create the service listener service
     *
     * Tries to get a service named ServiceListenerInterface from the service
     * locator, otherwise creates a ServiceListener instance, passing it the
     * container instance and the default service configuration, which can be
     * overridden by modules.
     *
     * It looks for the 'service_listener_options' key in the application
     * config and tries to add service/plugin managers as configured. The value
     * of 'service_listener_options' must be a list (array) which contains the
     * following keys:
     *
     * - service_manager: the name of the service manage to create as string
     * - config_key: the name of the configuration key to search for as string
     * - interface: the name of the interface that modules can implement as string
     * - method: the name of the method that modules have to implement as string
     *
     * @param  ContainerInterface  $container
     * @param  string              $requestedName
     * @param  null|array          $options
     * @return ServiceListenerInterface
     * @throws ServiceNotCreatedException for invalid ServiceListener service
     * @throws ServiceNotCreatedException For invalid configurations.
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $configuration   = $container->get('ApplicationConfig');

        $serviceListener = $container->has('ServiceListenerInterface')
            ? $container->get('ServiceListenerInterface')
            : new ServiceListener($container);

        if (! $serviceListener instanceof ServiceListenerInterface) {
            throw new ServiceNotCreatedException(
                'The service named ServiceListenerInterface must implement '
                .  ServiceListenerInterface::class
            );
        }

        $serviceListener->setDefaultServiceConfig($this->defaultServiceConfig);

        if (isset($configuration['service_listener_options'])) {
            $this->injectServiceListenerOptions($configuration['service_listener_options'], $serviceListener);
        }

        return $serviceListener;
    }

    /**
     * Validate and inject plugin manager options into the service listener.
     *
     * @param array $options
     * @param ServiceListenerInterface $serviceListener
     * @throws ServiceListenerInterface for invalid $options types
     */
    private function injectServiceListenerOptions($options, ServiceListenerInterface $serviceListener)
    {
        if (! is_array($options)) {
            throw new ServiceNotCreatedException(sprintf(
                'The value of service_listener_options must be an array, %s given.',
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        foreach ($options as $key => $newServiceManager) {
            $this->validatePluginManagerOptions($newServiceManager, $key);

            $serviceListener->addServiceManager(
                $newServiceManager['service_manager'],
                $newServiceManager['config_key'],
                $newServiceManager['interface'],
                $newServiceManager['method']
            );
        }
    }

    /**
     * Validate the structure and types for plugin manager configuration options.
     *
     * Ensures all required keys are present in the expected types.
     *
     * @param array $options
     * @param string $name Plugin manager service name; used for exception messages
     * @throws ServiceNotCreatedException for any missing configuration options.
     * @throws ServiceNotCreatedException for configuration options of invalid types.
     */
    private function validatePluginManagerOptions($options, $name)
    {
        if (! is_array($options)) {
            throw new ServiceNotCreatedException(sprintf(
                'Plugin manager configuration for "%s" is invalid; must be an array, received "%s"',
                $name,
                (is_object($options) ? get_class($options) : gettype($options))
            ));
        }

        if (! isset($options['service_manager'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'service_manager'));
        }

        if (! is_string($options['service_manager'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'service_manager',
                gettype($options['service_manager'])
            ));
        }

        if (! isset($options['config_key'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'config_key'));
        }

        if (! is_string($options['config_key'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'config_key',
                gettype($options['config_key'])
            ));
        }

        if (! isset($options['interface'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'interface'));
        }

        if (! is_string($options['interface'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'interface',
                gettype($options['interface'])
            ));
        }

        if (! isset($options['method'])) {
            throw new ServiceNotCreatedException(sprintf(self::MISSING_KEY_ERROR, $name, 'method'));
        }

        if (! is_string($options['method'])) {
            throw new ServiceNotCreatedException(sprintf(
                self::VALUE_TYPE_ERROR,
                'method',
                gettype($options['method'])
            ));
        }
    }
}
