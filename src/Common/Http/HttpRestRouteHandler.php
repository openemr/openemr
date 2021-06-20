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
use Psr\Http\Message\ResponseInterface;

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
                    if ($parsedRoute->isOperation()) {
                        $dispatchRestRequest->setOperation($parsedRoute->getOperation());
                    }

                    // make sure our scopes pass the security checks
                    self::checkSecurity($dispatchRestRequest);
                    (new SystemLogger())->debug("HttpRestRouteHandler->dispatch() dispatching route", ["route" => $routePath,]);
                    $hasRoute = true;

                    // now grab our url parameters and issue the controller callback for the route
                    $routeControllerParameters = $parsedRoute->getRouteParams();
                    $routeControllerParameters[] = $dispatchRestRequest; // add in the request object to everything
                    // call the function and use array unpacking to make this faster
                    $result = $routeCallback(...$routeControllerParameters);

                    // returning responses let's us unit test this way better.
                    if ($result instanceof ResponseInterface) {
                        return $result; // we will let the caller output this value
                    }
                    if ($return_method === 'standard') {
                        header('Content-Type: application/json');
                        // if we fail to encode we WANT an error thrown
                        echo json_encode($result, JSON_THROW_ON_ERROR);
                        break;
                    }
                    if ($return_method === 'direct-json') {
                        return json_encode($result, JSON_THROW_ON_ERROR);
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
                    , 'path' => $restRequest->getRequestURI()
                ]
            );
            http_response_code(401);
            exit;
        } catch (\JsonException $exception) { // intellisense says this is never thrown but the json_encode WILL throw this
            (new SystemLogger())->error(
                "HttpRestRouteHandler::dispatch() failed to encode JSON object" . $exception->getMessage(),
                [
                    'clientId' => $restRequest->getClientId()
                    , 'userUUID' => $restRequest->getRequestUserUUIDString()
                    , 'userType' => $restRequest->getRequestUserRole()
                    , 'path' => $restRequest->getRequestURI()
                ]
            );
            http_response_code(500);
            exit;
        } catch (Exception $exception) {
            (new SystemLogger())->error(
                "HttpRestRouteHandler::dispatch() " . $exception->getMessage(),
                [
                    'section' => $exception->getRequiredSection(), 'subCategory' => $exception->getRequiredSection()
                    , 'clientId' => $restRequest->getClientId()
                    , 'userUUID' => $restRequest->getRequestUserUUIDString()
                    , 'userType' => $restRequest->getRequestUserRole()
                    , 'path' => $restRequest->getRequestURI()
                ]
            );
            http_response_code(500);
            exit;
        }
    }

    /**
     * Given a PSR7 response send the response (headers & body) to the HTTP requesting client
     * @param ResponseInterface $response The response to send
     */
    public static function emitResponse(ResponseInterface $response)
    {
        // we don't use the Rest Config response as our http status response is different here
        foreach ($response->getHeaders() as $k => $values) {
            foreach ($values as $v) {
                header(sprintf('%s: %s', $k, $v));
            }
        }
        echo $response->getBody()->getContents();
    }

    /**
     * Security check on the request route against the Access Token scopes.
     * @param HttpRestRequest $restRequest
     * @throws AccessDeniedException If the security check fails
     */
    private static function checkSecurity(HttpRestRequest $restRequest)
    {
        $scopeType = 'patient';
        switch ($restRequest->getRequestUserRole()) {
            case 'users':
                $scopeType = 'user';
                break;
            case 'patient':
                $scopeType = 'patient';
                break;
            case 'system':
                $scopeType = 'system';
                break;
        }
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

        $config = $restRequest->getRestConfig();

        if ($restRequest->isPatientRequest()) {
            (new SystemLogger())->debug("checkSecurity() - patient specific request, so only allowing access to records to that one patient");
            if (empty($restRequest->getPatientUUIDString()) || ($restRequest->getRequestUserRole() !== 'patient') || ($scopeType !== 'patient')) {
                // need to fail here since this means the downstream patient binding mechanism will be broken
                (new SystemLogger())->error("checkSecurity() - exited since patient binding mechanism broken");
                http_response_code(401);
                $config::destroySession();
                exit;
            }
        }

        if ($restRequest->isFhir()) {
            // don't do any checks on our open fhir resources
            if (
                $restRequest->getResource() == 'metadata'
                || $restRequest->getResource() == '.well-known'
            ) {
                return;
            }
            if ($restRequest->isPatientWriteRequest()) {
                // not allowing patient userrole write for fhir
                (new SystemLogger())->debug("checkSecurity() - not allowing patient role write for fhir");
                http_response_code(401);
                $config::destroySession();
                exit;
            }
        } elseif (($restRequest->getApiType() === 'oemr') || ($restRequest->getApiType() === 'port')) {
            // don't do any checks on our open non-fhir resources
            if (
                $restRequest->getResource() == 'version'
                || $restRequest->getResource() == 'product'
            ) {
                return;
            }
            // ensure correct user role type for the non-fhir routes
            if (($restRequest->getApiType() === 'oemr') && (($restRequest->getRequestUserRole() !== 'users') || ($scopeType !== 'user'))) {
                (new SystemLogger())->debug("checkSecurity() - not allowing patient role to access oemr api");
                http_response_code(401);
                $config::destroySession();
                exit;
            }
            if (($restRequest->getApiType() === 'port') && (($restRequest->getRequestUserRole() !== 'patient') || ($scopeType !== 'patient'))) {
                (new SystemLogger())->debug("checkSecurity() - not allowing users role to access port api");
                http_response_code(401);
                $config::destroySession();
                exit;
            }
        } else {
            // should never be here
            (new SystemLogger())->error("checkSecurity() - illegal api type");
            http_response_code(401);
            $config::destroySession();
            exit;
        }

        // handle our scope checks
        $config::scope_check($scopeType, $resource, $permission);
    }
}
