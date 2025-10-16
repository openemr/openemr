<?php

namespace OpenEMR\RestControllers;

use OpenEMR\Common\Logging\SystemLoggerAwareTrait;
use OpenEMR\RestControllers\Subscriber\ApiResponseLoggerListener;
use OpenEMR\RestControllers\Subscriber\CORSListener;
use OpenEMR\RestControllers\Subscriber\OAuth2AuthorizationListener;
use OpenEMR\RestControllers\Subscriber\TelemetryListener;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use OpenEMR\RestControllers\Subscriber\SiteSetupListener;
use OpenEMR\RestControllers\Subscriber\AuthorizationListener;
use OpenEMR\RestControllers\Subscriber\ExceptionHandlerListener;
use OpenEMR\RestControllers\Subscriber\ViewRendererListener;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\RestControllers\Subscriber\RoutesExtensionListener;
use OpenEMR\RestControllers\Subscriber\SessionCleanupListener;
use Symfony\Component\HttpFoundation\RequestStack;

class ApiApplication
{
    use SystemLoggerAwareTrait;

    /**
     * Send the response directly to the client.
     */
    const RESPONSE_MODE_SEND = 0;

    /**
     * Return the response object to the caller.
     */
    const RESPONSE_MODE_RETURN = 1;

    private int $responseMode = self::RESPONSE_MODE_SEND;

    private EventDispatcher $dispatcher;

    public function __construct(private string $webroot = "")
    {
    }

    public function setDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    public function getDispatcher()
    {
        if (!isset($this->dispatcher)) {
            $this->dispatcher = new EventDispatcher();
        }
        return $this->dispatcher;
    }
    public function setResponseMode(int $responseMode)
    {
        // This method is for setting the response mode if needed.
        // It can be used to handle specific response modes like JSON, XML, etc.
        // Currently, it does not do anything as the response mode is handled in the ViewRendererListener.
        $this->responseMode = $responseMode;
    }
    /**
     * @param HttpRestRequest $request
     * @return void
     * @throws \Exception
     */
    public function run(HttpRestRequest $request): ?Response
    {
        $eventDispatcher = $this->getDispatcher();

        // need to handle request finish and session cleanup listeners first before any other listeners
        $eventDispatcher->addSubscriber(new ExceptionHandlerListener());

        // this listener will handle the telemetry data collection at the end of the request
        $eventDispatcher->addSubscriber(new TelemetryListener());

        // log all api responses (unless its a local api request)
        $eventDispatcher->addSubscriber(new ApiResponseLoggerListener());

        // this listener will handle the session cleanup at the end of the request unless its a local api request
        $eventDispatcher->addSubscriber(new SessionCleanupListener());

        // site setup will handle the site id, db connection, and globals setup
        $eventDispatcher->addSubscriber(new SiteSetupListener());

        // CORS listener will handle the CORS headers and preflight requests, needs to be added early on
        $eventDispatcher->addSubscriber(new CORSListener());

        $oauth2AuthorizationStrategy = new OAuth2AuthorizationListener();
        $eventDispatcher->addSubscriber($oauth2AuthorizationStrategy);

        // TODO: @adunsulag if we can use the security component here eventually, or rename this to be AuthenticationListener
        $eventDispatcher->addSubscriber(new AuthorizationListener());

        // handle all the routes and controllers
        $eventDispatcher->addSubscriber(new RoutesExtensionListener());

        // handle conversion of controller response objects to request responses (json, text, etc).
        $eventDispatcher->addSubscriber(new ViewRendererListener());

        $controllerResolver = new ControllerResolver($this->getSystemLogger());
        // TODO: @adunsulag we aren't really leveraging the ArgumentResolver yet, but we can use it to resolve controller arguments
        // if we want to use the ArgumentResolver to resolve controller arguments, we can do so here, we'd want to setup our
        // service container to handle the arguments and inject them into the controller
        $argumentResolver = new ArgumentResolver();
        $handleAllThrowables = true; // set to true to handle all exceptions in the ExceptionHandlerListener
        $kernel = new OEHttpKernel($eventDispatcher, $controllerResolver, new RequestStack(), $argumentResolver, $handleAllThrowables);

        // actually execute the kernel, which turns the request into a response
// by dispatching events, calling a controller, and returning the response
// events dispatched are:
//   kernel.request -> RequestEvent
//   kernel.controller -> ControllerEvent
//   kernel.controller_arguments -> ControllerArgumentsEvent
//   kernel.view -> ViewEvent
//   kernel.response -> ResponseEvent
//   kernel.finish_request -> FinishRequestEvent
//   kernel.exception -> ExceptionEvent
        $response = $kernel->handle($request);

        // send the headers and echo the content
        if ($this->responseMode === self::RESPONSE_MODE_RETURN) {
            // trigger the kernel.terminate event
            $kernel->terminate($request, $response);
            return $response; // Return the response object to the caller
        } else {
            $response->send();
            // trigger the kernel.terminate event
            $kernel->terminate($request, $response);
            return null; // No response object to return, as the response has been sent directly
        }
    }
}
