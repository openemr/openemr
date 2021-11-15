<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use ArrayAccess;
use Laminas\EventManager\EventManagerInterface;
use Laminas\EventManager\ListenerAggregateInterface;
use Laminas\EventManager\ListenerAggregateTrait;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\DispatchableInterface;
use Laminas\View\View;
use Traversable;

/**
 * Prepares the view layer for console applications
 */
class ViewManager implements ListenerAggregateInterface
{
    use ListenerAggregateTrait;

    /**
     * @var object application configuration service
     */
    protected $config;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * @var ServiceManager
     */
    protected $services;

    /**
     * @var View
     */
    protected $view;

    /**
     * Attach bootstrap event.
     *
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_BOOTSTRAP, [$this, 'onBootstrap'], 10000);
    }

    /**
     * Prepares the view layer
     *
     * Overriding, as several operations are omitted in the console view
     * algorithms, as well as to ensure we pick up the Console variants
     * of several listeners and strategies.
     *
     * @param  MvcEvent $event
     * @return void
     */
    public function onBootstrap($event)
    {
        $application    = $event->getApplication();
        $services       = $application->getServiceManager();
        $events         = $application->getEventManager();
        $sharedEvents   = $events->getSharedManager();
        $this->config   = $this->loadConfig($services->get('config'));
        $this->services = $services;

        $routeNotFoundStrategy   = $services->get('ConsoleRouteNotFoundStrategy');
        $exceptionStrategy       = $services->get('ConsoleExceptionStrategy');
        $mvcRenderingStrategy    = $services->get('ConsoleDefaultRenderingStrategy');
        $createViewModelListener = new CreateViewModelListener();
        $injectViewModelListener = new InjectViewModelListener();
        $injectParamsListener    = new InjectNamedConsoleParamsListener();

        $this->registerMvcRenderingStrategies($events);
        $this->registerViewStrategies();

        // @codingStandardsIgnoreStart
        $routeNotFoundStrategy->attach($events);
        $exceptionStrategy->attach($events);
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$injectViewModelListener, 'injectViewModel'], -100);
        $events->attach(MvcEvent::EVENT_RENDER_ERROR,   [$injectViewModelListener, 'injectViewModel'], -100);
        $mvcRenderingStrategy->attach($events);

        $sharedEvents->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$injectParamsListener,    'injectNamedParams'],        1000);
        $sharedEvents->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromArray'],  -80);
        $sharedEvents->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromString'], -80);
        $sharedEvents->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromNull'],   -80);
        $sharedEvents->attach(DispatchableInterface::class, MvcEvent::EVENT_DISPATCH, [$injectViewModelListener, 'injectViewModel'],          -100);
        // @codingStandardsIgnoreEnd
    }

    /**
     * Retrieves the View instance
     *
     * @return View
     */
    public function getView()
    {
        if ($this->view) {
            return $this->view;
        }

        $this->view = $this->services->get(View::class);
        return $this->view;
    }

    /**
     * Extract view manager configuration from the application's configuration
     *
     * @param array|ArrayAccess $configService
     * @return array|ArrayAccess
     */
    private function loadConfig($configService)
    {
        $config = [];

        // override when console config is provided, otherwise use the standard definition
        if (isset($configService['console']['view_manager'])) {
            $config = $configService['console']['view_manager'];
        } elseif (isset($configService['view_manager'])) {
            $config = $configService['view_manager'];
        }

        return ($config instanceof ArrayAccess || is_array($config))
            ? $config
            : [];
    }

    /**
     * Register additional mvc rendering strategies
     *
     * If there is a "mvc_strategies" key of the view manager configuration, loop
     * through it. Pull each as a service from the service manager, and, if it
     * is a ListenerAggregate, attach it to the view, at priority 100. This
     * latter allows each to trigger before the default mvc rendering strategy,
     * and for them to trigger in the order they are registered.
     *
     * @param EventManagerInterface $events
     * @return void
     */
    private function registerMvcRenderingStrategies(EventManagerInterface $events)
    {
        if (! isset($this->config['mvc_strategies'])) {
            return;
        }

        $mvcStrategies = $this->config['mvc_strategies'];

        if (is_string($mvcStrategies)) {
            $mvcStrategies = [$mvcStrategies];
        }

        if (! is_array($mvcStrategies) && ! $mvcStrategies instanceof Traversable) {
            return;
        }

        foreach ($mvcStrategies as $mvcStrategy) {
            if (! is_string($mvcStrategy)) {
                continue;
            }

            $listener = $this->services->get($mvcStrategy);
            if (! $listener instanceof ListenerAggregateInterface) {
                continue;
            }

            $listener->attach($events, 100);
        }
    }

    /**
     * Register additional view strategies
     *
     * If there is a "strategies" key of the view manager configuration, loop
     * through it. Pull each as a service from the service manager, and, if it
     * is a ListenerAggregate, attach it to the view, at priority 100. This
     * latter allows each to trigger before the default strategy, and for them
     * to trigger in the order they are registered.
     *
     * @return void
     */
    private function registerViewStrategies()
    {
        if (! isset($this->config['strategies'])) {
            return;
        }

        $strategies = $this->config['strategies'];

        if (is_string($strategies)) {
            $strategies = [$strategies];
        }

        if (! is_array($strategies) && ! $strategies instanceof Traversable) {
            return;
        }

        $view   = $this->getView();
        $events = $view->getEventManager();

        foreach ($strategies as $strategy) {
            if (! is_string($strategy)) {
                continue;
            }

            $listener = $this->services->get($strategy);
            if ($listener instanceof ListenerAggregateInterface) {
                $listener->attach($events, 100);
            }
        }
    }
}
