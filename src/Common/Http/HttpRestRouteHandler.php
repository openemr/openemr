<?php

/**
 * HttpResponseHelper
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Matthew Vita <matthewvita48@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Matthew Vita <matthewvita48@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Acl\AccessDeniedException;
use OpenEMR\Common\Logging\SystemLogger;

class HttpRestRouteHandler
{

    public static function dispatch(&$routes, HttpRestRequest $restRequest, $return_method = 'standard')
    {
        $dispatchRestRequest = clone $restRequest; // don't want to mess with the original request properties.

        (new SystemLogger())->debug(
            "HttpRestRouteHandler::dispatch() start request",
            ['resource' => $restRequest->getResource(), 'method' => $restRequest->getRequestMethod()
                , 'user' => $restRequest->getRequestUserUUID(), 'role' => $restRequest->getRequestUserRole()
                , 'client' => $restRequest->getClientId(), 'apiType' => $restRequest->getApiType()
                , 'route' => $restRequest->getRequestPath()
            ]
        );

        $route = $dispatchRestRequest->getRequestPath();
        $request_method = $dispatchRestRequest->getRequestMethod();

        // this is already handled somewhere else.
        // let's quickly be able to enable our CORS at the PHP level.
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: origin, authorization, accept, content-type, x-requested-with");
        header("Access-Control-Allow-Methods: GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS");
        header("Access-Control-Allow-Origin: *");
        if ($request_method === 'OPTIONS') {
            return true; // for now we just return true if we have the route.
        }

        try {
            // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
            $hasRoute = false;
            foreach ($routes as $routePath => $routeCallback) {
                $parsedRoute = new HttpRestParsedRoute($dispatchRestRequest->getRequestMethod(), $dispatchRestRequest->getRequestPath(), $routePath);
                if ($parsedRoute->isValid()) {
                    $dispatchRestRequest->setResource($parsedRoute->getResource());

                    // make sure our scopes pass the security checks
                    self::checkSecurity($dispatchRestRequest);
                    (new SystemLogger())->debug("HttpRestRouteHandler->dispatch() dispatching route", ["route" => $routePath,]);
                    $hasRoute = true;

                    // now grab our url parameters and issue the controller callback for the route
                    $routeControllerParameters = $parsedRoute->getRouteParams();
                    $routeControllerParameters[] = $dispatchRestRequest; // add in the request object to everything
                    // call the function and use array unpacking to make this faster
                    $result = $routeCallback(...$routeControllerParameters);

                    if ($return_method === 'standard') {
                        header('Content-Type: application/json');
                        echo json_encode($result);
                        break;
                    }
                    if ($return_method === 'direct-json') {
                        return json_encode($result);
                    }
                    // $return_method == 'direct'
                    return $result;
                }
            }
            return $hasRoute;
        } catch (AccessDeniedException $exception) {
            (new SystemLogger())->error(
                "HttpRestRouteHandler::dispatch() " . $exception->getMessage(),
                [
                    'section' => $exception->getRequiredSection(), 'subCategory' => $exception->getRequiredSection()
                    , 'clientId' => $restRequest->getClientId()
                    , 'userUUID' => $restRequest->getRequestUserUUIDString()
                    , 'userType' => $restRequest->getRequestUserRole()
                ]
            );
            http_response_code(401);
            exit;
        }
    }

    /**
     * Security check on the request route against the Access Token scopes.
     * @param HttpRestRequest $restRequest
     * @throws AccessDeniedException If the security check fails
     */
    private static function checkSecurity(HttpRestRequest $restRequest)
    {
        $scopeType = $restRequest->isPatientRequest() ? "patient" : "user";
        $permission = $restRequest->getRequestMethod() === "GET" ? "read" : "write";
        $resource = $restRequest->getResource();

        if ($restRequest->isFhir()) {
            // don't do any checks on our open resources
            if (
                $restRequest->getResource() == 'metadata'
                || $restRequest->getResource() == 'smart-configuration'
            ) {
                return;
            }
            if ($restRequest->isPatientWriteRequest()) {
                http_response_code(401);
            }
        }

        // handle our scope checks
        $config = $restRequest->getRestConfig();
        // TODO: adunsulag if this is working nicely we can open it up for the standard API
        if ($restRequest->isFhir()) {
            $config::scope_check($scopeType, $resource, $permission);
        }
    }
}
