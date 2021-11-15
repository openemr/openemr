<?php

/**
 * @see       https://github.com/laminas/laminas-mvc for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc/blob/master/LICENSE.md New BSD License
 */

namespace Laminas\Mvc;

use Interop\Container\ContainerInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface;
use Laminas\EventManager\AbstractListenerAggregate;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\Controller\MiddlewareController;
use Laminas\Mvc\Exception\InvalidMiddlewareException;
use Laminas\Psr7Bridge\Psr7Response;
use Laminas\Stratigility\MiddlewarePipe;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

use function sprintf;
use function trigger_error;

use const E_USER_DEPRECATED;

/**
 * @deprecated Since 3.2.0
 */
class MiddlewareListener extends AbstractListenerAggregate
{
    /**
     * Attach listeners to an event manager
     *
     * @param  EventManagerInterface $events
     * @param  int                   $priority
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 1);
    }

    /**
     * Listen to the "dispatch" event
     *
     * @param  MvcEvent $event
     * @return mixed
     */
    public function onDispatch(MvcEvent $event)
    {
        if (null !== $event->getResult()) {
            return;
        }

        $routeMatch = $event->getRouteMatch();
        $middleware = $routeMatch->getParam('middleware', false);
        if (false === $middleware) {
            return;
        }

        trigger_error(sprintf(
            'Dispatching middleware with %s is deprecated since 3.2.0;'
            . ' please use the laminas/laminas-mvc-middleware package instead',
            self::class
        ), E_USER_DEPRECATED);

        $request        = $event->getRequest();
        $application    = $event->getApplication();
        $response       = $application->getResponse();
        $serviceManager = $application->getServiceManager();

        $psr7ResponsePrototype = Psr7Response::fromLaminas($response);

        try {
            $pipe = $this->createPipeFromSpec(
                $serviceManager,
                $psr7ResponsePrototype,
                is_array($middleware) ? $middleware : [$middleware]
            );
        } catch (InvalidMiddlewareException $invalidMiddlewareException) {
            $return = $this->marshalInvalidMiddleware(
                $application::ERROR_MIDDLEWARE_CANNOT_DISPATCH,
                $invalidMiddlewareException->toMiddlewareName(),
                $event,
                $application,
                $invalidMiddlewareException
            );
            $event->setResult($return);
            return $return;
        }

        $caughtException = null;
        try {
            $return = (new MiddlewareController(
                $pipe,
                $psr7ResponsePrototype,
                $application->getServiceManager()->get('EventManager'),
                $event
            ))->dispatch($request, $response);
        } catch (\Throwable $ex) {
            $caughtException = $ex;
        }

        if ($caughtException !== null) {
            $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
            $event->setError($application::ERROR_EXCEPTION);
            $event->setParam('exception', $caughtException);

            $events  = $application->getEventManager();
            $results = $events->triggerEvent($event);
            $return  = $results->last();
            if (! $return) {
                $return = $event->getResult();
            }
        }

        $event->setError('');

        if (! $return instanceof PsrResponseInterface) {
            $event->setResult($return);
            return $return;
        }
        $response = Psr7Response::toLaminas($return);
        $event->setResult($response);
        return $response;
    }

    /**
     * Create a middleware pipe from the array spec given.
     *
     * @param ContainerInterface $serviceLocator
     * @param ResponseInterface $responsePrototype
     * @param array $middlewaresToBePiped
     * @return MiddlewarePipe
     * @throws InvalidMiddlewareException
     */
    private function createPipeFromSpec(
        ContainerInterface $serviceLocator,
        ResponseInterface $responsePrototype,
        array $middlewaresToBePiped
    ) {
        $pipe = new MiddlewarePipe();
        $pipe->setResponsePrototype($responsePrototype);
        foreach ($middlewaresToBePiped as $middlewareToBePiped) {
            if (null === $middlewareToBePiped) {
                throw InvalidMiddlewareException::fromNull();
            }

            $middlewareName = is_string($middlewareToBePiped) ? $middlewareToBePiped : get_class($middlewareToBePiped);

            if (is_string($middlewareToBePiped) && $serviceLocator->has($middlewareToBePiped)) {
                $middlewareToBePiped = $serviceLocator->get($middlewareToBePiped);
            }
            if (! $middlewareToBePiped instanceof MiddlewareInterface && ! is_callable($middlewareToBePiped)) {
                throw InvalidMiddlewareException::fromMiddlewareName($middlewareName);
            }

            $pipe->pipe($middlewareToBePiped);
        }
        return $pipe;
    }

    /**
     * Marshal a middleware not callable exception event
     *
     * @param  string $type
     * @param  string $middlewareName
     * @param  MvcEvent $event
     * @param  Application $application
     * @param  \Exception $exception
     * @return mixed
     */
    protected function marshalInvalidMiddleware(
        $type,
        $middlewareName,
        MvcEvent $event,
        Application $application,
        \Exception $exception = null
    ) {
        $event->setName(MvcEvent::EVENT_DISPATCH_ERROR);
        $event->setError($type);
        $event->setController($middlewareName);
        $event->setControllerClass('Middleware not callable: ' . $middlewareName);
        if ($exception !== null) {
            $event->setParam('exception', $exception);
        }

        $events  = $application->getEventManager();
        $results = $events->triggerEvent($event);
        $return  = $results->last();
        if (! $return) {
            $return = $event->getResult();
        }
        return $return;
    }
}
