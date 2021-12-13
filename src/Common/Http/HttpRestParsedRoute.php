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
     * The endpoint operation that the api request is for.  Only populated if the route definition
     * matches against the current route
     * @var string
     */
    private $operation;

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
            $this->parseRouteParams($matches, $routeDefinition);
            (new SystemLogger())->debug("HttpRestParsedRoute->__construct() matched", ['routePath' => $routeDefinition,
                'requestPath' => $requestRoute
                ,'method' => $requestMethod, 'routeParams' => $this->routeParams
                , 'resource' => $this->getResource(), 'operation' => $this->getOperation()]);
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
    public function getResource(): ?string
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
     * Returns the operation name for the parsed route.  Operations are prefixed with a $
     * @return string
     */
    public function getOperation(): ?string
    {
        return $this->operation;
    }

    /**
     * Returns true if the parsed route is an operation, false otherwise
     * @return bool
     */
    public function isOperation(): bool
    {
        return !empty($this->operation);
    }

    /**
     * Returns the regex for a given path we use to match against a route.
     * @param $path
     * @return string
     */
    private function getRouteMatchExpression($path)
    {
        // Taken from https://stackoverflow.com/questions/11722711/url-routing-regex-php/11723153#11723153
        return "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_\$\:]+)', preg_quote($path)) . "$@D";
    }

    /**
     * Sets up the operation and resource definitions for this parsed route
     * @param $routeParams
     * @param $routeDefinition
     * @return mixed|void
     */
    private function parseRouteParams($routeParams, $routeDefinition)
    {
        $parts = explode("/", $routeDefinition);
        if (empty($parts)) {
            return; // nothing we can do here
        }
        $apiType = $parts[1] ?? null;

        $finalArg = end($parts);
        if (strpos($finalArg, '$') !== false) {
            $this->operation = $finalArg;
            array_pop($parts);
            $finalArg = end($parts);
        }

        if (strpos($finalArg, ':') !== false) {
            array_pop($parts);
            $finalArg = end($parts);
        }

        // We've implemented our FHIR api spec so the resource is the first argument
        // We have to accomodate this for our scope permissions
        // standard api allows for nesting of resources so we have to handle the other possibilities there.
        if ($apiType === 'fhir') {
            $this->resource = $parts[2] ?? null;
        } else if (!empty($finalArg) && !\in_array($finalArg, ['portal', 'api'])) {
            $this->resource = $finalArg;
        }

        return $finalArg;
    }
}
