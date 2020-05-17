<?php

namespace OpenEMR\Events\RestApiExtend;

use Symfony\Component\EventDispatcher\Event;

class RestApiCreateEvent extends Event
{
    const EVENT_HANDLE = 'restConfig.route_map.create';
    private $route_map;
    private $fhir_route_map;
    private $portal_route_map;
    private $portal_fhir_route_map;

    /**
     * RestApiCreateEvent constructor.
     * @param $route_map
     * @param $fhir_route_map
     */
    public function __construct($route_map, $fhir_route_map, $portal_route_map, $portal_fhir_route_map)
    {
        $this->route_map = $route_map;
        $this->fhir_route_map = $fhir_route_map;
        $this->portal_route_map = $portal_route_map;
        $this->portal_fhir_route_map = $portal_fhir_route_map;
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
     * @return mixed
     */
    public function getPortalFHIRRouteMap()
    {
        return $this->portal_fhir_route_map;
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
     * @param $route
     * @param $action
     */
    public function addToPortalFHIRRouteMap($route, $action)
    {
        $this->portal_fhir_route_map[$route] = $action;
    }
}
