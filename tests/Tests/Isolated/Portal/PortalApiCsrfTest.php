<?php

/**
 * The portal/patient entry point checks the CSRF token before it hands the request to the
 * app, and routing takes the request method only from the HTTP request, so a GET can't be
 * routed as a DELETE.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\PortalPageRenderer;
use OpenEMR\Controllers\Portal\Patient\PortalPatientApp;
use OpenEMR\Controllers\Portal\Patient\ProviderController;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRoute;
use OpenEMR\Portal\Routing\PortalRouter;
use OpenEMR\Portal\Routing\RouteAccess;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * The entry point's own checks (the token is required before dispatch, by the real method) are
 * tested in PortalFrontControllerTest.
 */
final class PortalApiCsrfTest extends TestCase
{
    public function testMethodOverridesAreIgnored(): void
    {
        // Symfony applies an override only to a POST, so only a POST can tell the two apart.
        $request = Request::create('/portal/patient/api/thing/5', 'POST');
        $request->headers->set('X-HTTP-Method-Override', 'DELETE');
        $app = new PortalPatientApp(
            new class implements PortalPageRenderer {
                public function render(string $page, array $vars = []): string
                {
                    return 'page:' . $page;
                }
            },
            new PortalRouter([
                new PortalRoute('DELETE', 'api/thing/(:num)', ProviderController::class, 'ErrorApi404', RouteAccess::All, params: ['id' => 2]),
            ]),
        );

        $response = $app->handle($request, 'api/thing/5', PortalPrincipal::staff());

        $this->assertSame('page:DefaultError404', $response->getContent(), 'the POST finds no route; the DELETE route never runs');
    }
}
