<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console;

use Laminas\Mvc\SendResponseListener;
use Laminas\Router\RouteStackInterface;
use Laminas\ServiceManager\Factory\InvokableFactory;

class ConfigProvider
{
    /**
     * Provide configuration for this component.
     *
     * @return array
     */
    public function __invoke()
    {
        return [
            'controller_plugins' => $this->getPluginConfig(),
            'dependencies'       => $this->getDependencyConfig(),
        ];
    }

    /**
     * Provide dependency configuration for this component.
     *
     * @return array
     */
    public function getDependencyConfig()
    {
        return [
            'aliases' => [
                'console'                         => 'ConsoleAdapter',
                'Console'                         => 'ConsoleAdapter',
                'ConsoleDefaultRenderingStrategy' => View\DefaultRenderingStrategy::class,
                'ConsoleRenderer'                 => View\Renderer::class,

                // Legacy Zend Framework aliases
                \Zend\Mvc\Console\View\DefaultRenderingStrategy::class => View\DefaultRenderingStrategy::class,
                \Zend\Mvc\Console\View\Renderer::class => View\Renderer::class,
            ],
            'delegators' => [
                'ControllerManager'         => [ Service\ControllerManagerDelegatorFactory::class ],
                'Request'                   => [ Service\ConsoleRequestDelegatorFactory::class ],
                'Response'                  => [ Service\ConsoleResponseDelegatorFactory::class ],
                RouteStackInterface::class  => [ Router\ConsoleRouterDelegatorFactory::class ],
                SendResponseListener::class => [ Service\ConsoleResponseSenderDelegatorFactory::class ],
                'ViewHelperManager'         => [ Service\ConsoleViewHelperManagerDelegatorFactory::class ],
                'ViewManager'               => [ Service\ViewManagerDelegatorFactory::class ],
            ],
            'factories' => [
                'ConsoleAdapter'               => Service\ConsoleAdapterFactory::class,
                'ConsoleExceptionStrategy'     => Service\ConsoleExceptionStrategyFactory::class,
                'ConsoleRouteNotFoundStrategy' => Service\ConsoleRouteNotFoundStrategyFactory::class,
                'ConsoleRouter'                => Router\ConsoleRouterFactory::class,
                'ConsoleViewManager'           => Service\ConsoleViewManagerFactory::class,
                View\DefaultRenderingStrategy::class => Service\DefaultRenderingStrategyFactory::class,
                View\Renderer::class           => InvokableFactory::class,
            ],
        ];
    }

    /**
     * Provide controller plugin configuration for this component.
     *
     * @return array
     */
    public function getPluginConfig()
    {
        // @codingStandardsIgnoreStart
        return [
            'aliases' => [
                'CreateConsoleNotFoundModel' => Controller\Plugin\CreateConsoleNotFoundModel::class,
                'createConsoleNotFoundModel' => Controller\Plugin\CreateConsoleNotFoundModel::class,
                'createconsolenotfoundmodel' => Controller\Plugin\CreateConsoleNotFoundModel::class,
                'Laminas\Mvc\Controller\Plugin\CreateConsoleNotFoundModel::class' => Controller\Plugin\CreateConsoleNotFoundModel::class,

                // Legacy Zend Framework aliases
                'Zend\Mvc\Controller\Plugin\CreateConsoleNotFoundModel::class' => 'Laminas\Mvc\Controller\Plugin\CreateConsoleNotFoundModel::class',
                \Zend\Mvc\Console\Controller\Plugin\CreateConsoleNotFoundModel::class => Controller\Plugin\CreateConsoleNotFoundModel::class,
            ],
            'factories' => [
                Controller\Plugin\CreateConsoleNotFoundModel::class => InvokableFactory::class,
            ],
        ];
        // @codingStandardsIgnoreEnd
    }
}
