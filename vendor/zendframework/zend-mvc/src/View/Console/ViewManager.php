<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Mvc\View\Console;

use ArrayAccess;
use Zend\Mvc\MvcEvent;
use Zend\Mvc\View\Http\ViewManager as BaseViewManager;

/**
 * Prepares the view layer for console applications
 */
class ViewManager extends BaseViewManager
{
    /**
     * Prepares the view layer
     *
     * Overriding, as several operations are omitted in the console view
     * algorithms, as well as to ensure we pick up the Console variants
     * of several listeners and strategies.
     *
     * @param  \Zend\Mvc\MvcEvent $event
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
        $this->event    = $event;

        $routeNotFoundStrategy   = $services->get('ConsoleRouteNotFoundStrategy');
        $exceptionStrategy       = $services->get('ConsoleExceptionStrategy');
        $mvcRenderingStrategy    = $services->get('ConsoleDefaultRenderingStrategy');
        $createViewModelListener = new CreateViewModelListener();
        $injectViewModelListener = new InjectViewModelListener();
        $injectParamsListener    = new InjectNamedConsoleParamsListener();

        $this->registerMvcRenderingStrategies($events);
        $this->registerViewStrategies();

        $routeNotFoundStrategy->attach($events);
        $exceptionStrategy->attach($events);
        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$injectViewModelListener, 'injectViewModel'], -100);
        $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$injectViewModelListener, 'injectViewModel'], -100);
        $mvcRenderingStrategy->attach($events);

        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$injectParamsListener,  'injectNamedParams'], 1000);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromArray'], -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromString'], -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$createViewModelListener, 'createViewModelFromNull'], -80);
        $sharedEvents->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, [$injectViewModelListener, 'injectViewModel'], -100);
    }

    /**
     * Extract view manager configuration from the application's configuration
     *
     * @param array|ArrayAccess $configService
     *
     * @return array
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
}
