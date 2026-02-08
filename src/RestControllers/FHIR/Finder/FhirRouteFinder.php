<?php

// TODO: need copyright header here

namespace OpenEMR\RestControllers\FHIR\Finder;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;
use OpenEMR\RestControllers\Finder\IRouteFinder;

class FhirRouteFinder implements IRouteFinder
{
    public function __construct(private readonly OEHttpKernel $kernel)
    {
    }

    public function find(HttpRestRequest $request): array
    {
        // TODO: this is where we can differentiate between different FHIR versions or profiles
        $routes = include __DIR__ . '/../../../../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php';

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent([], $routes, [], $request);
        $restApiCreateEvent = $this->kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getFHIRRouteMap();
        return $routes;
    }
}
