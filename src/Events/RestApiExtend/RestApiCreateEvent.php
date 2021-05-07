<?php

namespace OpenEMR\Events\RestApiExtend;

use OpenEMR\Common\Http\HttpRestRequest;
use Symfony\Component\EventDispatcher\Event;

class RestApiCreateEvent extends Event
{
    const EVENT_HANDLE = 'restConfig.route_map.create';
    private $route_map;
    private $fhir_route_map;
    private $portal_route_map;

    /**
     * @var HttpRestRequest
     */
    private $restRequest;

    /**
     * RestApiCreateEvent constructor.
     * @param $route_map
     * @param $fhir_route_map
     */
    public function __construct($route_map, $fhir_route_map, $portal_route_map, $restRequest = null)
    {
        $this->route_map = $route_map;
        $this->fhir_route_map = $fhir_route_map;
        $this->portal_route_map = $portal_route_map;
        $this->restRequest = $restRequest;
    }

    /**
     * @return mixed
     */
    public function getRouteMap()
    {
        return $this->route_map;
    }

    /**
     * @return mixed
     */
    public function getFHIRRouteMap()
    {
        return $this->fhir_route_map;
    }

    /**
     * @return mixed
     */
    public function getPortalRouteMap()
    {
        return $this->portal_route_map;
    }

    /**
     * @param $route
     * @param $action
     */
    public function addToRouteMap($route, $action)
    {
        $this->route_map[$route] = $action;
    }

    /**
     * @param $route
     * @param $action
     */
    public function addToFHIRRouteMap($route, $action)
    {
        $this->fhir_route_map[$route] = $action;
    }

    /**
     * @param $route
     * @param $action
     */
    public function addToPortalRouteMap($route, $action)
    {
        $this->portal_route_map[$route] = $action;
    }

    /**
     * @return HttpRestRequest
     */
    public function getRestRequest(): HttpRestRequest
    {
        return $this->restRequest;
    }

    /**
     * @param HttpRestRequest $restRequest
     */
    public function setRestRequest(HttpRestRequest $restRequest): void
    {
        $this->restRequest = $restRequest;
    }
}
