<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\Controller;

use Laminas\Console\Adapter\AdapterInterface as ConsoleAdapter;
use Laminas\Console\Request as ConsoleRequest;
use Laminas\Mvc\Console\Exception\InvalidArgumentException;
use Laminas\Mvc\Console\View\ViewModel;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Stdlib\RequestInterface;
use Laminas\Stdlib\ResponseInterface;

/**
  * @method \Laminas\Mvc\Console\View\ViewModel createConsoleNotFoundModel()
 */
abstract class AbstractConsoleController extends AbstractActionController
{
    /**
     * @var ConsoleAdapter
     */
    protected $console;

    /**
     * @param ConsoleAdapter $console
     */
    public function setConsole(ConsoleAdapter $console)
    {
        $this->console = $console;
        return $this;
    }

    /**
     * @return ConsoleAdapter
     */
    public function getConsole()
    {
        return $this->console;
    }

    /**
     * {@inheritdoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        if (! $request instanceof ConsoleRequest) {
            throw new InvalidArgumentException(sprintf(
                '%s can only dispatch requests in a console environment',
                get_called_class()
            ));
        }
        return parent::dispatch($request, $response);
    }

    /**
     * Action called if matched action does not exist.
     *
     * @return ViewModel
     */
    public function notFoundAction()
    {
        $event = $this->getEvent();
        $routeMatch = $event->getRouteMatch();
        $routeMatch->setParam('action', 'not-found');

        $helper = $this->plugin('createConsoleNotFoundModel');
        return $helper();
    }
}
