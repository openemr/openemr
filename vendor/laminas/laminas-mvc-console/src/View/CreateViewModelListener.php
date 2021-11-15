<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface as Events;
use Laminas\Mvc\Console\View\ViewModel as ConsoleModel;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ArrayUtils;

class CreateViewModelListener extends AbstractListenerAggregate
{
    /**
     * {@inheritDoc}
     */
    public function attach(Events $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'createViewModelFromString'], -80);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'createViewModelFromArray'], -80);
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'createViewModelFromNull'], -80);
    }

    /**
     * Inspect the result, and cast it to a ViewModel if a string is detected
     *
     * @param MvcEvent $e
     * @return void
    */
    public function createViewModelFromString(MvcEvent $e)
    {
        $result = $e->getResult();
        if (! is_string($result)) {
            return;
        }

        // create Console model
        $model = new ConsoleModel;

        // store the result in a model variable
        $model->setVariable(ConsoleModel::RESULT, $result);
        $e->setResult($model);
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

        $model = new ConsoleModel($result);
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

        $model = new ConsoleModel;
        $e->setResult($model);
    }
}
