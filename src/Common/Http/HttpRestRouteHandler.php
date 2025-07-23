<?php

/**
 * HttpRestRouteHandler
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use Psr\Http\Message\ResponseInterface;
use Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpRestRouteHandler
{
    private SystemLogger $logger;

    public function __construct(private readonly OEHttpKernel $kernel)
    {
        $this->logger = $kernel->getSystemLogger();
    }

    public function dispatch(array $routes, HttpRestRequest $dispatchRestRequest): HttpRestRequest
    {
        $logger = $this->logger;

        $logger->debug(
            "HttpRestRouteHandler::dispatch() start request",
            ['resource' => $dispatchRestRequest->getResource(), 'method' => $dispatchRestRequest->getMethod()
                , 'user' => $dispatchRestRequest->getRequestUserUUID(), 'role' => $dispatchRestRequest->getRequestUserRole()
                , 'client' => $dispatchRestRequest->getClientId(), 'apiType' => $dispatchRestRequest->getApiType()
                , 'route' => $dispatchRestRequest->getRequestPathWithoutSite()
                , 'queryParams' => $dispatchRestRequest->getQueryParams()
            ]
        );

        $dispatchRestRequestPath = $dispatchRestRequest->getRequestPathWithoutSite();
        $dispatchRestRequestMethod = $dispatchRestRequest->getMethod();

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
                    $response = $this->checkSecurity($this->kernel, $dispatchRestRequest);
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
                    return $dispatchRestRequest;
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

    public function checkSecurity(OEHttpKernel $kernel, HttpRestRequest $restRequest)
    {
        if (empty($restRequest->getRequestUserRole())) {
            $this->logger->error("HttpRestRouteHandler::checkSecurity() - no user role set for request", [
                'resource' => $restRequest->getResource(),
                'method' => $restRequest->getMethod(),
                'user' => $restRequest->getRequestUserUUID(),
                'client' => $restRequest->getClientId(),
                'apiType' => $restRequest->getApiType(),
                'route' => $restRequest->getRequestPath()
            ]);
            throw new AccessDeniedException($restRequest->getResource());
        }
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
            $permission = $restRequest->getMethod() === "GET" ? "read" : "write";
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
