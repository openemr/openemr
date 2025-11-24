<?php

namespace OpenEMR\Services\FHIR\Utils;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Logging\SystemLoggerAwareTrait;

class SearchRequestNormalizer
{
    use SystemLoggerAwareTrait;

    public function __construct(SystemLogger $logger)
    {
        $this->setSystemLogger($logger);
    }

    public function normalizeSearchRequest(HttpRestRequest $dispatchRestRequest): HttpRestRequest
    {
        // in FHIR a POST request to a resource/_search is identical to the equivalent GET request with parameters.
        // POST requests are application/x-www-form-urlencoded and parameters may appear both in the URL and the request
        // body. The spec says that putting requests into both the body and query string is the same as repeating the
        // parameter.  In our case we treat the parameter as a union search if it appears in both the query string and
        // the post body.
        // chop off the back
        $pos = strripos((string) $dispatchRestRequest->getRequestPath(), "/_search");
        if ($pos === false) {
            throw new \BadMethodCallException("Attempted to normalize search request on a path that does not contain search");
        }
        $requestPath = substr((string) $dispatchRestRequest->getRequestPath(), 0, $pos);
        $dispatchRestRequest->setRequestPath($requestPath);

        // grab any post vars and stuff them into our query vars
        // @see https://www.hl7.org/fhir/http.html#search
        $queryVars = $dispatchRestRequest->getQueryParams();
        $postParams = $dispatchRestRequest->getMethod() !== 'GET' ? $dispatchRestRequest->request->all() : [];

        if (!empty($postParams)) {
            foreach ($postParams as $key => $value) {
                if (isset($queryVars[$key])) {
                    $queryVars[$key] = is_array($queryVars[$key]) ? $queryVars[$key] : [$queryVars[$key]];
                    $queryVars[$key][] = $value;
                } else {
                    $queryVars[$key] = $value;
                }
            }
        }

        $normalizedRequest = $dispatchRestRequest;
        $normalizedRequest->setRequestMethod("GET");
        $normalizedRequest->query->replace($queryVars);
        $normalizedRequest->request->replace([]);
        $normalizedRequest->server->set('QUERY_STRING', http_build_query($queryVars));
        $normalizedRequest->server->set('REQUEST_METHOD', 'GET');
        $normalizedRequest->server->set('PATH_INFO', $requestPath);
        $this->getSystemLogger()->debug(
            "SearchRequestNormalizer::normalizeSearchRequest() normalized request",
            ['resource' => $normalizedRequest->getResource(), 'method' => $normalizedRequest->getMethod()
                , 'user' => $normalizedRequest->getRequestUserUUID(), 'role' => $normalizedRequest->getRequestUserRole()
                , 'client' => $normalizedRequest->getClientId(), 'apiType' => $normalizedRequest->getApiType()
                , 'route' => $normalizedRequest->getRequestPathWithoutSite()
                , 'queryParams' => $normalizedRequest->getQueryParams()
                , 'pathInfo' => $normalizedRequest->getPathInfo()
                , 'originalPath' => $dispatchRestRequest->getRequestPathWithoutSite()
            ]
        );
        return $normalizedRequest;
    }
}
