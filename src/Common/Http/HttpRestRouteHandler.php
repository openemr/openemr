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
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
use Psr\Http\Message\ResponseInterface;
use Throwable;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class HttpRestRouteHandler
{
    private readonly SystemLogger $logger;

    private readonly OEGlobalsBag $globalsBag;

    public function __construct(private readonly OEHttpKernel $kernel)
    {
        $this->logger = $kernel->getSystemLogger();
        $this->globalsBag = $this->kernel->getGlobalsBag();
    }

    public function dispatch(array $routes, HttpRestRequest $dispatchRestRequest): ?ResponseInterface
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
                    // if our requested resource is a patient context ie patient/<resource>.<permission> then
                    // we want to mark the request as a patient request and make sure we restrict requests
                    // TODO: @adunsulag this will have problems if there are multiple scope contexts for a resource,
                    if ($dispatchRestRequest->getScopeContextForResource($parsedRoute->getResource()) == 'patient') {
                        $dispatchRestRequest->setPatientRequest(true);
                    }
                    // TODO: Would it be better to throw a security exception here instead of returning a custom response?
                    // this will allow us to handle the response in the kernel view event
                    $response = $this->checkSecurity($this->kernel, $dispatchRestRequest, $parsedRoute);
                    if ($response instanceof ResponseInterface) {
                        // if the response is a ResponseInterface then we need to set it on the event
                        $logger->debug(self::class . "::dispatch() security check failed", ["route" => $routePath]);
                        return $response;
                    }
                    $dispatchRestRequest->attributes->set("_route", $parsedRoute);
                    $dispatchRestRequest->setResource($parsedRoute->getResource());
                    if ($parsedRoute->isOperation()) {
                        $dispatchRestRequest->setOperation($parsedRoute->getOperation());
                    }
                    $logger->debug("HttpRestRouteHandler->dispatch() dispatching route", ["route" => $routePath]);

                    // now grab our url parameters and issue the controller callback for the route
                    // call the function and use array unpacking to make this faster

                    // the controller result can be a Response object but if its not then it gets handled in the kernel view event
                    // TODO: @adunsulag if we can figure out how to change this out to use the ArgumentResolver then we can
                    // remove this code and just use the controller callback directly.
                    $routeControllerParameters = $parsedRoute->getRouteParams();
                    $routeControllerParameters[] = $dispatchRestRequest; // add in the request object to everything
                    $routeControllerParameters[] = $this->globalsBag; // add in the globals bag to everything if they need it

                    // set the _controller attribute for the kernel to handle, gives other listeners a chance to modify things as needed
                    $dispatchRestRequest->attributes->set("_controller", fn() => $routeCallback(...$routeControllerParameters));
                    return null; // return null to let the kernel handle the response
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
                    , 'innerExceptionTrace' => $exception->getTraceAsString()
                ]
            );
            throw new HttpException(Response::HTTP_UNAUTHORIZED, "Unauthorized", $exception);
        } catch (Throwable $exception) {
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
            if ($exception instanceof HttpException) {
                // rethrow http exceptions as is
                throw $exception;
            } else {
                throw new HttpException(Response::HTTP_INTERNAL_SERVER_ERROR, "System error occurred", $exception);
            }
        }
    }

    /**
     * Check the security for the request and return a response if the security check fails.
     * @param OEHttpKernel $kernel
     * @param HttpRestRequest $restRequest
     * @param HttpRestParsedRoute $parsedRoute
     * @return ResponseInterface|null
     * @throws AccessDeniedException
     */
    public function checkSecurity(OEHttpKernel $kernel, HttpRestRequest $restRequest, HttpRestParsedRoute $parsedRoute): ?ResponseInterface
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

        $resource = $parsedRoute->getResource();
        if (!empty($parsedRoute->getOperation())) {
            $permission = $parsedRoute->getOperation();
            // this only applies to root level permissions, which I don't believe we are granting in the system right
            // now.
            if (empty($resource)) {
                $resource = "*"; // for our permission check
            }
        } else {
            $permission = match ($restRequest->getMethod()) {
                // we don't support HEAD requests as a route in the api so will not address that here
                // TODO: @adunsulag spec says we should return a 405 Method Not Allowed or a 501 ("not implemented") if we don't support head.
                "GET" =>  $this->getGetRequestPermission($parsedRoute)
                , "POST" => 'c'
                , "PUT" => 'u'
                , "DELETE" => 'd'
                , default => throw new HttpException(Response::HTTP_NOT_IMPLEMENTED, "Not implemented")
            };
        }
        $restApiSecurityCheckEvent = new RestApiSecurityCheckEvent($restRequest);
        $restApiSecurityCheckEvent->setRestRequest($restRequest);
        $restApiSecurityCheckEvent->setScopeType($scopeType);
        $restApiSecurityCheckEvent->setResource($resource);
        $restApiSecurityCheckEvent->setPermission($permission);
        // preferred approach is to throw an AccessDeniedException if the security check fails
        // however, we also allow for a response to be set on the event for custom security message rendering
        $checkedRestApiSecurityCheckEvent = $kernel->getEventDispatcher()->dispatch($restApiSecurityCheckEvent, RestApiSecurityCheckEvent::EVENT_HANDLE);
        if ($checkedRestApiSecurityCheckEvent->hasSecurityCheckFailedResponse()) {
            return $checkedRestApiSecurityCheckEvent->getSecurityCheckFailedResponse();
        }
        return null; // No response means the security check passed
    }

    protected function getGetRequestPermission(HttpRestParsedRoute $parsedRoute): string
    {

        // this should handle things like /fhir/Patient/{id} or /api/patient/{id}
        // as well as more complex params
        if (!empty($parsedRoute->getInstanceIdentifier())) {
            return 'r'; // read permission for instance level operations
        } else {
            return 's'; // anything that is not an instance level operation is a search request
        }
    }
}
