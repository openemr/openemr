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
use OpenEMR\Events\RestApiExtend\RestApiSecurityCheckEvent;
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
                , 'queryParams' => $restRequest->getQueryParams()
            ]
        );

        if ($dispatchRestRequest->isFhir() && self::isFhirSearchRequest($dispatchRestRequest)) {
            (new SystemLogger())->debug("HttpRestRouteHandler::dispatch() FHIR POST _search request needs normalization");
            $dispatchRestRequest = self::normalizeFhirSearchRequest($dispatchRestRequest);
            (new SystemLogger())->debug(
                "HttpRestRouteHandler::dispatch() request normalized",
                ['resource' => $dispatchRestRequest->getResource(), 'method' => $dispatchRestRequest->getRequestMethod()
                    , 'user' => $dispatchRestRequest->getRequestUserUUID(), 'role' => $dispatchRestRequest->getRequestUserRole()
                    , 'client' => $dispatchRestRequest->getClientId(), 'apiType' => $dispatchRestRequest->getApiType()
                    , 'route' => $dispatchRestRequest->getRequestPath()
                    , 'queryParams' => $dispatchRestRequest->getQueryParams()
                ]
            );
        }

        $route = $dispatchRestRequest->getRequestPath();
        $request_method = $dispatchRestRequest->getRequestMethod();

        // this is already handled somewhere else.
        // let's quickly be able to enable our CORS at the PHP level.
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: origin, authorization, accept, content-type, content-encoding, x-requested-with");
        header("Access-Control-Allow-Methods: GET, HEAD, POST, PUT, DELETE, TRACE, OPTIONS");
//        header("Access-Control-Allow-Origin: *");
        // we have already validated the token which authenticates our client_id
        // we will go ahead and allow the origin
        $origins = $dispatchRestRequest->getHeader('Origin');
        if (!empty($origins)) {
            header("Access-Control-Allow-Origin: " . $origins[0]);
        }

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


                    // if our requested resource is a patient context ie patient/<resource>.<permission> then
                    // we want to mark the request as a patient request and make sure we restrict requests
                    if ($dispatchRestRequest->getScopeContextForResource($parsedRoute->getResource()) == 'patient') {
                        $dispatchRestRequest->setPatientRequest(true);
                    }

                    // make sure our scopes pass the security checks
                    $securityCheck = self::checkSecurity($dispatchRestRequest);
                    if ($securityCheck instanceof ResponseInterface) {
                        (new SystemLogger())->debug("HttpRestRouteHandler->dispatch() security check failed", ["route" => $routePath]);
                        return $securityCheck;
                    }
                    (new SystemLogger())->debug("HttpRestRouteHandler->dispatch() dispatching route", ["route" => $routePath]);
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
                        // PHP default json_encode will escape forward slash characters '/' so you can embed the JSON
                        // inside of a <script> tag.  However, since forward slash escaping is optional as part of the
                        // JSON spec some servers (looking at you ONC FHIR Inferno and your missing data tests) don't
                        // know how to handle the unescaped slashes so we remove the forward slash escaping.
                        echo json_encode($result, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES);
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

    private static function isFhirSearchRequest(HttpRestRequest $dispatchRestRequest): bool
    {
        return $dispatchRestRequest->isFhirSearchRequest();
    }

    private static function normalizeFhirSearchRequest(HttpRestRequest $dispatchRestRequest): HttpRestRequest
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

    /**
     * Security check on the request route against the Access Token scopes.
     * @param HttpRestRequest $restRequest
     * @throws AccessDeniedException If the security check fails
     * @returns ResponseInterface|bool
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
            if (empty($restRequest->getPatientUUIDString())) { // we MUST have a patient uuid string if its a patient request
                // need to fail here since this means the downstream patient binding mechanism will be broken
                (new SystemLogger())->error("checkSecurity() - exited since patient binding mechanism broken");
                $psrFactory = new Psr17Factory();
                $config::destroySession();
                return $psrFactory->createResponse(401);
            }
            // if we are a patient only request and we have a patient uuid populated (from session) then we set our scope type to be patient.
            $scopeType = 'patient';
        }
        // let module writers handle there own security checking or bypass the security as needed
        // this allows experiments to be done on a module basis such as opening up patient write requests if a module
        // allows that to occur, or more comprehensive in depth permission checks occurring.
        $restApiSecurityCheckEvent = new RestApiSecurityCheckEvent($restRequest);
        $restApiSecurityCheckEvent->setRestRequest($restRequest);
        $restApiSecurityCheckEvent->setScopeType($scopeType);
        $restApiSecurityCheckEvent->setResource($resource);
        $restApiSecurityCheckEvent->setPermission($permission);
        $checkedRestApiSecurityCheckEvent = $GLOBALS['kernel']->getEventDispatcher()->dispatch($restApiSecurityCheckEvent, RestApiSecurityCheckEvent::EVENT_HANDLE);
        if (!$checkedRestApiSecurityCheckEvent instanceof RestApiSecurityCheckEvent) {
            throw new \RuntimeException("Invalid event object returned as part of dispatch");
        }
        if ($checkedRestApiSecurityCheckEvent->hasSecurityCheckFailedResponse()) {
            return $checkedRestApiSecurityCheckEvent->getSecurityCheckFailedResponse();
        } else if ($checkedRestApiSecurityCheckEvent->shouldSkipSecurityCheck()) {
            return true;
        }

        if ($restRequest->isFhir()) {
            // don't do any checks on our open fhir resources
            if (self::fhirRestRequestSkipSecurityCheck($restRequest)) {
                return true;
            }
            // we do NOT want logged in patients writing data at this point so we fail
            // TODO: when we have better auditing and provider merge/verification mechanisms look at opening up patient write access to data.
            if ($restRequest->isPatientWriteRequest() && $restRequest->getRequestUserRole() == 'patient') {
                // not allowing patient userrole write for fhir
                (new SystemLogger())->debug("checkSecurity() - not allowing patient role write for fhir");
                $psrFactory = new Psr17Factory();
                $config::destroySession();
                return $psrFactory->createResponse(401);
            }
        } elseif (($restRequest->getApiType() === 'oemr') || ($restRequest->getApiType() === 'port')) {
            // don't do any checks on our open non-fhir resources
            if (
                $restRequest->getResource() == 'version'
                || $restRequest->getResource() == 'product'
                || $restRequest->isLocalApi() // skip security check if its a local api
            ) {
                return true;
            }
            // ensure correct user role type for the non-fhir routes
            if (($restRequest->getApiType() === 'oemr') && (($restRequest->getRequestUserRole() !== 'users') || ($scopeType !== 'user'))) {
                (new SystemLogger())->debug("checkSecurity() - not allowing patient role to access oemr api");
                $psrFactory = new Psr17Factory();
                $config::destroySession();
                return $psrFactory->createResponse(401);
            }
            if (($restRequest->getApiType() === 'port') && (($restRequest->getRequestUserRole() !== 'patient') || ($scopeType !== 'patient'))) {
                (new SystemLogger())->debug("checkSecurity() - not allowing users role to access port api");
                $psrFactory = new Psr17Factory();
                $config::destroySession();
                return $psrFactory->createResponse(401);
            }
        } else {
            // should never be here
            (new SystemLogger())->error("checkSecurity() - illegal api type");
            $psrFactory = new Psr17Factory();
            $config::destroySession();
            return $psrFactory->createResponse(401);
        }

        // handle our scope checks
        $config::scope_check($scopeType, $resource, $permission);
    }

    public static function fhirRestRequestSkipSecurityCheck(HttpRestRequest $restRequest): bool
    {
        // if someone is hitting the local api and have a valid CSRF token we skip the security check.
        // TODO: @adunsulag need to verify this assumption is correct
        if ($restRequest->isLocalApi()) {
            return true;
        }

        $resource = $restRequest->getResource();
        // capability statement, smart well knowns, and operation definitions are skipped.
        $skippedChecks = ['metadata', '.well-known', 'OperationDefinition'];
        return array_search($resource, $skippedChecks) !== false;
    }
}
