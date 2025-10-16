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

use OpenEMR\Common\Logging\SystemLoggerAwareTrait;

class HttpRestParsedRoute
{
    use SystemLoggerAwareTrait;

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
     * The identifier of a resource for an instance level operation ie fhir/Patient/{id} -> id or
     * /api/patient/{id} -> id or /api/patient/{id}/encounter/{encounter_id} -> encounter_id
     * @var string|null
     */
    private ?string $instanceIdentifier;

    /**
     * The endpoint paramters (identifiers, and anything else marked with the :colon param).
     * Only populated if the route definition matches against the current route
     * @var string
     */
    private $routeParams;

    /**
     * @param mixed $requestMethod
     * @param string $requestRoute The current HTTP request route we are attempting to match against a route definition
     * @param string $routeDefinition The OpenEMR route definition that this request is being matched / parsed against
     */
    public function __construct(
        private $requestMethod,
        private $requestRoute,
        private $routeDefinition
    ) {
        $this->instanceIdentifier = null;

        $routePieces = explode(" ", $this->routeDefinition);
        $routeDefinitionMethod = $routePieces[0];
        $pattern = $this->getRouteMatchExpression($routePieces[1]);
        $matches = [];
        if ($this->requestMethod === $routeDefinitionMethod && preg_match($pattern, $this->requestRoute, $matches)) {
            $this->isValid = true;
            array_shift($matches); // drop request method
            $this->routeParams = $matches;
            $this->parseRouteParams($matches, $this->routeDefinition);
            $this->getSystemLogger()->debug("HttpRestParsedRoute->__construct() matched", ['routePath' => $this->routeDefinition,
                'requestPath' => $this->requestRoute
                ,'method' => $this->requestMethod, 'routeParams' => $this->routeParams
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
        return "@^" . preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_\$\:]+)', preg_quote((string) $path)) . "$@D";
    }

    /**
     * Returns the resource instance identifier if it exists.  This is the last part of the route that is not a resource or operation.
     * @return string|null
     */
    public function getInstanceIdentifier(): ?string
    {
        return $this->instanceIdentifier;
    }

    /**
     * Sets up the operation and resource definitions for this parsed route
     * @param $routeParams
     * @param $routeDefinition
     * @return mixed|void
     */
    private function parseRouteParams($routeParams, $routeDefinition)
    {
        $parts = explode("/", (string) $routeDefinition);
        if (empty($parts)) {
            return; // nothing we can do here
        }
        $apiType = $parts[1] ?? null;

        $finalArg = end($parts);
        if (str_contains($finalArg, '$')) {
            $this->operation = $finalArg;
            array_pop($parts);
            $finalArg = end($parts);
        }

        if (str_starts_with($finalArg, ':') !== false) {
            // valid match so every part should be accounted for, so the ending part is the instance identifier
            $this->instanceIdentifier = end($routeParams);
            array_pop($parts);
            $finalArg = end($parts);
        }

        // We've implemented our FHIR api spec so the resource is the first argument
        // We have to accomodate this for our scope permissions
        // standard api allows for nesting of resources so we have to handle the other possibilities there.
        if ($apiType === 'fhir') {
            $this->resource = $parts[2] ?? null;
        } elseif (!empty($finalArg) && !\in_array($finalArg, ['portal', 'api'])) {
            $this->resource = $finalArg;
        }

        return $finalArg;
    }
}
