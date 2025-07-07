<?php

namespace OpenEMR\RestControllers\Subscriber;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Http\HttpRestParsedRoute;
use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Http\HttpRestRouteHandler;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class RoutesExtensionListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 40]]
        ];
    }
    public function onKernelRequest(RequestEvent $event)
    {
        if ($event->hasResponse()) {
            // If the event already has a response, we do not need to process it further.
            // This can happen if a previous listener has already handled the request.
            return;
        }

        $request = $event->getRequest();
        $kernel = $event->getKernel();
        if (!$kernel instanceof OEHttpKernel) {
            // If the kernel is not an instance of OEHttpKernel, we cannot proceed with route extension.
            return;
        }
        if (!$request instanceof HttpRestRequest) {
            return; // If the request is not an instance of HttpRestRequest, we cannot proceed with route extension.
        }
        // CORS request is handled by a separate listener, so we do not need to handle it here.

        // handle each type of request separately
        if ($request->isFhirRequest()) {
            $this->processFhirRequest($request, $kernel);
        } else if ($request->isPatientRequest()) {
            $this->processPatientPortalRequest($request, $kernel);
        } else {
            $this->processStandardRequest($request, $kernel);
        }
//
//        $standardRoutes = include __DIR__ . '/../../../apis/routes/_rest_routes_standard.inc.php';
//        $fhirRoutes = include __DIR__ . '/../../../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php';
//        $portalRoutes = include __DIR__ . '/../../../apis/routes/_rest_routes_portal.inc.php';
//
//        // This method is intended to handle the request and extend routes.
//        // Implementation details would depend on the specific requirements of the application.
//        // For example, you might want to add custom routes or modify existing ones.
//        $restApiCreateEvent = new RestApiCreateEvent($standardRoutes, $fhirRoutes, $portalRoutes, $request);
//        $restApiCreateEvent = $kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
//        $standardRoutes = $restApiCreateEvent->getRouteMap();
//        $fhirRoutes = $restApiCreateEvent->getFHIRRouteMap();
//        $portalRoutes = $restApiCreateEvent->getPortalRouteMap();
        // TODO: do we need to handle the $request override here?
    }

    private function processFhirRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        if ($request->isFhirSearchRequest()) {
            $request = $this->normalizeFhirSearchRequest($request);
        }
        // TODO: this is where we can differentiate between different FHIR versions or profiles
        $routes = include __DIR__ . '/../../../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php';

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent([], $routes, [], $request);
        $restApiCreateEvent = $kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getFHIRRouteMap();
        return $this->dispatch($kernel, $routes, $request, $kernel->getSystemLogger());
    }

    private function processStandardRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        // TODO: this is where we can differentiate between different FHIR versions or profiles
        $routes = include __DIR__ . '/../../../apis/routes/_rest_routes_standard.inc.php';

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent($routes, [], [], $request);
        $restApiCreateEvent = $kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getRouteMap();
        return $this->dispatch($kernel, $routes, $request, $kernel->getSystemLogger());
    }

    private function processPatientPortalRequest(HttpRestRequest $request, OEHttpKernel $kernel)
    {
        // TODO: this is where we can differentiate between different FHIR versions or profiles
        $routes = include __DIR__ . '/../../../apis/routes/_rest_routes_portal.inc.php';

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent([], [], $routes, $request);
        $restApiCreateEvent = $kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getPortalRouteMap();
        return $this->dispatch($kernel, $routes, $request, $kernel->getSystemLogger());
    }

    private function dispatch(OEHttpKernel $kernel, $routes, HttpRestRequest $dispatchRestRequest, SystemLogger $logger)
    {
        $logger->error(
            "HttpRestRouteHandler::dispatch() start request",
            ['resource' => $dispatchRestRequest->getResource(), 'method' => $dispatchRestRequest->getRequestMethod()
                , 'user' => $dispatchRestRequest->getRequestUserUUID(), 'role' => $dispatchRestRequest->getRequestUserRole()
                , 'client' => $dispatchRestRequest->getClientId(), 'apiType' => $dispatchRestRequest->getApiType()
                , 'route' => $dispatchRestRequest->getRequestPathWithoutSite()
                , 'queryParams' => $dispatchRestRequest->getQueryParams()
            ]
        );

        $dispatchRestRequestPath = $dispatchRestRequest->getRequestPathWithoutSite();
        $dispatchRestRequestMethod = $dispatchRestRequest->getRequestMethod();

        try {
            // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
            foreach ($routes as $routePath => $routeCallback) {
                $parsedRoute = new HttpRestParsedRoute($dispatchRestRequestMethod, $dispatchRestRequestPath, $routePath);
                if ($parsedRoute->isValid()) {
                    $dispatchRestRequest->setResource($parsedRoute->getResource());
                    if ($parsedRoute->isOperation()) {
                        $dispatchRestRequest->setOperation($parsedRoute->getOperation());
                    }


                    // if our requested resource is a patient context ie patient/<resource>.<permission> then
                    // we want to mark the request as a patient request and make sure we restrict requests
                    if ($dispatchRestRequest->getScopeContextForResource($parsedRoute->getResource()) == 'patient') {
                        $dispatchRestRequest->setPatientRequest(true);
                    }

                    // make sure our scopes pass the security checks
                    $response = $this->checkSecurity($kernel, $dispatchRestRequest);
                    if ($response instanceof ResponseInterface) {
                        // if the response is a ResponseInterface then we need to set it on the event
                        $logger->debug(self::class . "::dispatch() security check failed", ["route" => $routePath]);
                        return $response;
                    }
                    $logger->debug("HttpRestRouteHandler->dispatch() dispatching route", ["route" => $routePath]);
                    $hasRoute = true;

                    // now grab our url parameters and issue the controller callback for the route

                    // call the function and use array unpacking to make this faster

                    // the controller result can be a Response object but if its not then it gets handled in the kernel view event
                    $routeControllerParameters = $parsedRoute->getRouteParams();
                    $routeControllerParameters[] = $dispatchRestRequest; // add in the request object to everything

                    // set the _controller attribute for the kernel to handle, gives other listeners a chance to modify things as needed
                    $dispatchRestRequest->attributes->set("_controller", function () use ($routeCallback, $routeControllerParameters) {
                        return $routeCallback(...$routeControllerParameters);
                    });
                    return;
                }
            }
            throw new HttpException(Response::HTTP_NOT_FOUND, "Route not found");
        } catch (AccessDeniedException $exception) {
            // TODO: @adunsulag do we want to just let this exception bubble up and let the kernel handle it?
            $logger->errorLogCaller(
                $exception->getMessage(),
                [
                    'section' => $exception->getRequiredSection(), 'subCategory' => $exception->getSubCategory()
                    , 'clientId' => $dispatchRestRequest->getClientId()
                    , 'userUUID' => $dispatchRestRequest->getRequestUserUUIDString()
                    , 'userType' => $dispatchRestRequest->getRequestUserRole()
                    , 'path' => $dispatchRestRequest->getRequestURI()
                ]
            );
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Unauthorized", $exception);
        } catch (\Throwable $exception) {
            $logger->errorLogCaller(
                $exception->getMessage(),
                [
                    'clientId' => $dispatchRestRequest->getClientId()
                    , 'userUUID' => $dispatchRestRequest->getRequestUserUUIDString()
                    , 'userType' => $dispatchRestRequest->getRequestUserRole()
                    , 'path' => $dispatchRestRequest->getRequestURI()
                    ,'trace' => $exception->getTraceAsString()
                ]
            );
            throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "System error occurred", $exception);
        }
    }

    private function normalizeFhirSearchRequest(HttpRestRequest $dispatchRestRequest): HttpRestRequest
    {

        // in FHIR a POST request to a resource/_search is identical to the equivalent GET request with parameters.
        // POST requests are application/x-www-form-urlencoded and parameters may appear both in the URL and the request
        // body. The spec says that putting requests into both the body and query string is the same as repeating the
        // parameter.  In our case we treat the parameter as a union search if it appears in both the query string and
        // the post body.
        $normalizedRequest = clone $dispatchRestRequest;
        // chop off the back
        $pos = strripos($dispatchRestRequest->getRequestPath(), "/_search");
        if ($pos === false) {
            throw new \BadMethodCallException("Attempted to normalize search request on a path that does not contain search");
        }

        $requestPath = substr($dispatchRestRequest->getRequestPath(), 0, $pos);
        $normalizedRequest->setRequestPath($requestPath);
        $queryVars = $normalizedRequest->getQueryParams();
        $normalizedRequest->setRequestMethod("GET");

        // grab any post vars and stuff them into our query vars
        // @see https://www.hl7.org/fhir/http.html#search
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (isset($queryVars[$key])) {
                    $queryVars[$key] = is_array($queryVars[$key]) ? $queryVars[$key] : [$queryVars[$key]];
                    $queryVars[$key][] = $value;
                } else {
                    $queryVars[$key] = $value;
                }
            }
        }
        $normalizedRequest->setQueryParams($queryVars);
        return $normalizedRequest;
    }

    private function checkSecurity(OEHttpKernel $kernel, HttpRestRequest $restRequest)
    {
        $scopeType = $restRequest->getRequestUserRole() === 'users' ? 'user' : $restRequest->getRequestUserRole();

        $resource = $restRequest->getResource();
        if (!empty($restRequest->getOperation())) {
            $permission = $restRequest->getOperation();
            // this only applies to root level permissions, which I don't believe we are granting in the system right
            // now.
            if (empty($resource)) {
                $resource = "*"; // for our permission check
            }
        } else {
            $permission = $restRequest->getRequestMethod() === "GET" ? "read" : "write";
        }
        $restApiSecurityCheckEvent = new RestApiSecurityCheckEvent($restRequest);
        $restApiSecurityCheckEvent->setRestRequest($restRequest);
        $restApiSecurityCheckEvent->setScopeType($scopeType);
        $restApiSecurityCheckEvent->setResource($resource);
        $restApiSecurityCheckEvent->setPermission($permission);
        // preferred approach is to throw an AccessDeniedException if the security check fails
        // however, we also allow for a response to be set on the event for custom security message rendering
        $checkedRestApiSecurityCheckEvent = $kernel->getEventDispatcher()->dispatch($restApiSecurityCheckEvent, RestApiSecurityCheckEvent::EVENT_HANDLE);
        if (!$checkedRestApiSecurityCheckEvent instanceof RestApiSecurityCheckEvent) {
            throw new \RuntimeException("Invalid event object returned as part of dispatch");
        }
        if ($checkedRestApiSecurityCheckEvent->hasSecurityCheckFailedResponse()) {
            return $checkedRestApiSecurityCheckEvent->getSecurityCheckFailedResponse();
        }
        return null; // No response means the security check passed
    }
}
