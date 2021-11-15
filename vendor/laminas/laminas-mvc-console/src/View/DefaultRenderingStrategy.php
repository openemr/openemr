<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-console for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-console/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-console/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Console\View;

use Laminas\Console\Response as ConsoleResponse;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Console\View\ViewModel as ConsoleViewModel;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface as Response;

class DefaultRenderingStrategy extends AbstractListenerAggregate
{
    /**
     * @var Renderer
     */
    private $renderer;

    /**
     * @param Renderer $renderer
     */
    public function __construct(Renderer $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_RENDER, [$this, 'render'], -10000);
    }

    /**
     * Render the view
     *
     * @param  MvcEvent $e
     * @return Response
     */
    public function render(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof Response) {
            return $result; // the result is already rendered ...
        }

        // Marshal arguments
        $response  = $e->getResponse();

        // Render the result
        $responseText = $this->renderer->render($result);

        // Fetch service manager
        $sm = $e->getApplication()->getServiceManager();

        // Fetch console
        $console = $sm->get('console');

        // Append console response to response object
        $content = $response->getContent() . $responseText;
        if (is_callable([$console, 'encodeText'])) {
            $content = $console->encodeText($content);
        }
        $response->setContent($content);

        // Pass on console-specific options
        if ($response instanceof ConsoleResponse
            && $result instanceof ConsoleViewModel
        ) {
            $errorLevel = $result->getErrorLevel();
            $response->setErrorLevel($errorLevel);
        }

        return $response;
    }
}
