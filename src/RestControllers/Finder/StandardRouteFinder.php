<?php

/**
 * Standard Route Finder - locates and returns standard API routes.
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

class StandardRouteFinder implements IRouteFinder
{
    public function __construct(private OEHttpKernel $kernel)
    {
    }

    public function find(HttpRestRequest $request): array
    {
        /**
         * @see apis/routes/_rest_routes_standard.inc.php
         * @see apis/routes/_rest_routes_standard_user.inc.php
         */
        $routes = array_merge(
            include __DIR__ . '/../../../apis/routes/_rest_routes_standard.inc.php',
            include __DIR__ . '/../../../apis/routes/_rest_routes_standard_user.inc.php',
        );

        // This method is intended to handle the request and extend routes.
        // Implementation details would depend on the specific requirements of the application.
        // For example, you might want to add custom routes or modify existing ones.
        $restApiCreateEvent = new RestApiCreateEvent($routes, [], [], $request);
        $restApiCreateEvent = $this->kernel->getEventDispatcher()->dispatch($restApiCreateEvent, RestApiCreateEvent::EVENT_HANDLE, 10);
        $routes = $restApiCreateEvent->getRouteMap();
        return $routes;
    }
}
