<?php

/**
 * HttpRestParsedRoute represents a parsed http rest api request.  It splits apart an OpenEMR route definition and
 * parses the provided http request against that route definition.  Validates the route definition and extracts the
 * resource name as well as any route parameters defined in the route definition.
 * @package openemr
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Http;

use OpenEMR\Common\Logging\SystemLogger;

class HttpRestParsedRoute
{

    /**
     * Whether the route definition is a valid match against the current request
     * @var bool
     */
    private $isValid;

    /**
     * The endpoint resource that the api request is for.  Only populated if the route definition
     * matches against the current route
     * @var string
     */
    private $resource;

    /**
     * The endpoint paramters (identifiers, and anything else marked with the :colon param).
     * Only populated if the route definition matches against the current route
     * @var string
     */
    private $routeParams;

    /**
     * The OpenEMR route definition that this request is being matched / parsed against
     * @var string
     */
    private $routeDefinition;

    /**
     * The current HTTP request route we are attempting to match against a route definition
     * @var string
     */
    private $requestRoute;

    public function __construct($requestMethod, $requestRoute, $routeDefinition)
    {
        $this->routeDefinition = $routeDefinition;
        $this->requestRoute = $requestRoute;
        $this->requestMethod = $requestMethod;

        $routePieces = explode(" ", $routeDefinition);
        $routeDefinitionMethod = $routePieces[0];
        $pattern = $this->getRouteMatchExpression($routePieces[1]);
        $matches = array();
        if ($requestMethod === $routeDefinitionMethod && preg_match($pattern, $requestRoute, $matches)) {
            $this->isValid = true;
            array_shift($matches); // drop request method
            $this->routeParams = $matches;
            $this->resource = $this->getResourceForRoute($routeDefinition);
            (new SystemLogger())->debug("HttpRestParsedRoute->__construct() ", ['routePath' => $routeDefinition,
                'requestPath' => $requestRoute
                ,'method' => $requestMethod, 'routeParams' => $this->routeParams, 'resource' => $this->getResource()]);
        } else {
            $this->isValid = false;
        }
    }

    /**
     * Returns true if the
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->isValid;
    }

    /**
     * @return string
     */
    public function getResource(): string
    {
        return $this->resource;
    }

    /**
     * @return array
     */
    public function getRouteParams(): array
    {
        return $this->routeParams;
    }

    /**
     * @return string
     */
    public function getRouteDefinition()
    {
        return $this->routeDefinition;
    }

    /**
     * @return string
     */
    public function getRequestRoute()
    {
        return $this->requestRoute;
    }

    /**
     * Returns the regex for a given path we use to match against a route.
     * @param $path
     * @return string
     */
    private function getRouteMatchExpression($path)
    {
        // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
        return "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_\$]+)', preg_quote($path)) . "$@D";
    }


    private function getResourceForRoute($routePath)
    {
        $parts = explode("/", $routePath);
        $finalArg = end($parts);
        if (strpos($finalArg, ':') !== false) {
            array_pop($parts);
            $finalArg = end($parts);
        }
        return $finalArg;
    }
}
