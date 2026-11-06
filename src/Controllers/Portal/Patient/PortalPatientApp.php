<?php

/**
 * Serves a portal/patient request: routes it, checks the route's access rule, and runs the
 * controller action. portal/patient/index.php runs the session bootstrap and CSRF check first.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Controllers\Portal\Patient;

use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRouter;
use OpenEMR\Portal\Routing\PortalRouteTable;
use OpenEMR\Portal\Routing\RouteMatch;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final readonly class PortalPatientApp
{
    private PortalRouter $router;

    /**
     * Routes with the portal route table unless a router is given (tests).
     */
    public function __construct(private PortalPageRenderer $pages, ?PortalRouter $router = null)
    {
        $this->router = $router ?? new PortalRouter(PortalRouteTable::routes());
    }

    /**
     * @param string $path the route path, from PortalRequestPath
     * @throws \OpenEMR\Portal\Routing\PortalRouteDeniedException when the caller may not use the route
     */
    public function handle(Request $request, string $path, PortalPrincipal $caller): Response
    {
        $match = $this->router->match($request->getRealMethod(), $path, $caller);
        if ($match === null) {
            return (new PortalErrorPages($this->pages))->notFound();
        }

        return $this->run($match, $request, $caller);
    }

    /**
     * Builds the controller the matched route names and calls its action with the route parameters.
     */
    private function run(RouteMatch $match, Request $request, PortalPrincipal $caller): Response
    {
        $route = $match->route;
        $controller = match ($route->controller) {
            OnsiteDocumentController::class => new OnsiteDocumentController($this->pages),
            PatientController::class => new PatientController($this->pages),
            PortalPatientController::class => new PortalPatientController(),
            PortalReviewController::class => new PortalReviewController($this->pages),
            ProviderController::class => new ProviderController($this->pages),
            default => throw new \LogicException('No portal controller ' . $route->controller),
        };
        $action = [$controller, $route->action];
        if (!is_callable($action)) {
            throw new \LogicException('No portal action ' . $route->controller . '::' . $route->action);
        }
        $response = $action($request, $match, $caller);
        if (!$response instanceof Response) {
            throw new \LogicException('Portal action ' . $route->controller . '::' . $route->action . ' returned no response');
        }

        return $response;
    }
}
