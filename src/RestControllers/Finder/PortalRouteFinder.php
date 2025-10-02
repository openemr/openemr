<?php

/**
 * Portal Route Finder - locates and returns the patient portal API routes.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2025 Discover and Change, Inc <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\RestControllers\Finder;

use OpenEMR\Common\Http\HttpRestRequest;
use OpenEMR\Core\OEHttpKernel;
use OpenEMR\Events\RestApiExtend\RestApiCreateEvent;

class PortalRouteFinder implements IRouteFinder
{
    public function __construct(private readonly OEHttpKernel $kernel)
    {
    }

    public function find(HttpRestRequest $request): array
    {
        $routes = include __DIR__ . '/../../../apis/routes/_rest_routes_portal.inc.php';

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent([], [], $routes, $request);
        $restApiCreateEvent = $this->kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getPortalRouteMap();
        return $routes;
    }
}
