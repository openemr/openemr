<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\View\Http;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface as Events;
use Laminas\Mvc\MvcEvent;
use Laminas\View\Model\ClearableModelInterface;
use Laminas\View\Model\ModelInterface as ViewModel;

class InjectViewModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'injectViewModel'], -100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'injectViewModel'], -100);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER_ERROR, [$this, 'injectViewModel'], -100);
    }

    /**
     * Insert the view model into the event
     *
     * Inspects the MVC result; if it's a view model, it then either (a) adds
     * it as a child to the default, composed view model, or (b) replaces it
     * if the result is marked as terminable.
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectViewModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if (! $result instanceof ViewModel) {
            return;
        }

        $model = $e->getViewModel();

        if ($result->terminate()) {
            $e->setViewModel($result);
            return;
        }

        if ($e->getError() && $model instanceof ClearableModelInterface) {
            $model->clearChildren();
        }

        $model->addChild($result);
    }
}
