<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc\Controller;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Request;
use Laminas\Mvc\Exception\ReachedFinalHandlerException;
use Laminas\Mvc\Exception\RuntimeException;
use Laminas\Mvc\MvcEvent;
use Laminas\Psr7Bridge\Psr7ServerRequest;
use Laminas\Router\RouteMatch;
use Laminas\Stratigility\Delegate\CallableDelegateDecorator;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * @internal don't use this in your codebase, or else @ocramius will hunt you
 *     down. This is just an internal hack to make middleware trigger
 *     'dispatch' events attached to the DispatchableInterface identifier.
 *
 *     Specifically, it will receive a @see MiddlewarePipe and a
 *     @see ResponseInterface prototype, and then dispatch the pipe whilst still
 *     behaving like a normal controller. That is needed for any events
 *     attached to the @see \Laminas\Stdlib\DispatchableInterface identifier to
 *     reach their listeners on any attached
 *     @see \Laminas\EventManager\SharedEventManagerInterface
 */
final class MiddlewareController extends AbstractController
{
    /**
     * @var MiddlewarePipe
     */
    private $pipe;

    /**
     * @var ResponseInterface
     */
    private $responsePrototype;

    public function __construct(
        MiddlewarePipe $pipe,
        ResponseInterface $responsePrototype,
        EventManagerInterface $eventManager,
        MvcEvent $event
    ) {
        $this->eventIdentifier   = __CLASS__;
        $this->pipe              = $pipe;
        $this->responsePrototype = $responsePrototype;

        $this->setEventManager($eventManager);
        $this->setEvent($event);
    }

    /**
     * {@inheritDoc}
     *
     * @throws RuntimeException
     */
    public function onDispatch(MvcEvent $e)
    {
        $routeMatch  = $e->getRouteMatch();
        $psr7Request = $this->populateRequestParametersFromRoute(
            $this->loadRequest()->withAttribute(RouteMatch::class, $routeMatch),
            $routeMatch
        );

        $result = $this->pipe->process($psr7Request, new CallableDelegateDecorator(
            function () {
                throw ReachedFinalHandlerException::create();
            },
            $this->responsePrototype
        ));

        $e->setResult($result);

        return $result;
    }

    /**
     * @return \Laminas\Diactoros\ServerRequest
     *
     * @throws RuntimeException
     */
    private function loadRequest()
    {
        $request = $this->request;

        if (! $request instanceof Request) {
            throw new RuntimeException(sprintf(
                'Expected request to be a %s, %s given',
                Request::class,
                get_class($request)
            ));
        }

        return Psr7ServerRequest::fromLaminas($request);
    }

    /**
     * @param ServerRequestInterface $request
     * @param RouteMatch|null $routeMatch
     *
     * @return ServerRequestInterface
     */
    private function populateRequestParametersFromRoute(ServerRequestInterface $request, RouteMatch $routeMatch = null)
    {
        if (! $routeMatch) {
            return $request;
        }

        foreach ($routeMatch->getParams() as $key => $value) {
            $request = $request->withAttribute($key, $value);
        }

        return $request;
    }
}
