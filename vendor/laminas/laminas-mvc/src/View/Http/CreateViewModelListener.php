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
use Laminas\Stdlib\ArrayUtils;
use Laminas\View\Model\ViewModel;

class CreateViewModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'createViewModelFromArray'], -80);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'createViewModelFromNull'], -80);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if an assoc array is detected
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function createViewModelFromArray(MvcEvent $e)
    {
        $result = $e->getResult();
        if (! ArrayUtils::hasStringKeys($result, true)) {
            return;
        }

        $model = new ViewModel($result);
        $e->setResult($model);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if null is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function createViewModelFromNull(MvcEvent $e)
    {
        $result = $e->getResult();
        if (null !== $result) {
            return;
        }

        $model = new ViewModel;
        $e->setResult($model);
    }
}
