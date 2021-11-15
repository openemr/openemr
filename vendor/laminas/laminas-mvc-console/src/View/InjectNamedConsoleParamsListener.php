<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use Laminas\Console\Request as ConsoleRequest;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface as Events;
use Laminas\Mvc\MvcEvent;

class InjectNamedConsoleParamsListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectNamedParams'], -80);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if a string is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function injectNamedParams(MvcEvent $e)
    {
        if (! $routeMatch = $e->getRouteMatch()) {
            return; // cannot work without route match
        }

        $request = $e->getRequest();
        if (! $request instanceof ConsoleRequest) {
            return; // will not inject non-console requests
        }

        // Inject route match params into request
        $params = array_merge(
            $request->getParams()->toArray(),
            $routeMatch->getParams()
        );
        $request->getParams()->fromArray($params);
    }
}
