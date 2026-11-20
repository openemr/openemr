<?php

/**
 * The portal/patient router keeps the old route map's access rules for every kind of caller,
 * and the route table keeps its URLs and flags. Replaces GenericRouterStaffAclTest, with all of
 * its cases.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\Portal;

use OpenEMR\Controllers\Portal\Patient\OnsiteDocumentController;
use OpenEMR\Controllers\Portal\Patient\PortalPatientController;
use OpenEMR\Controllers\Portal\Patient\ProviderController;
use OpenEMR\Portal\Routing\PortalPrincipal;
use OpenEMR\Portal\Routing\PortalRoute;
use OpenEMR\Portal\Routing\PortalRouteDeniedException;
use OpenEMR\Portal\Routing\PortalRouter;
use OpenEMR\Portal\Routing\PortalRouteTable;
use OpenEMR\Portal\Routing\RouteAccess;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PortalRouterTest extends TestCase
{
    public function testStaffCanReachLiteralAndWildcardStaffRoutes(): void
    {
        $router = $this->router(
            $this->route('GET', 'staff', RouteAccess::Staff),
            $this->route('GET', 'staff/(:num)', RouteAccess::Staff, params: ['id' => 1]),
        );

        $this->assertSame('staff', $router->match('GET', 'staff', PortalPrincipal::staff())?->route->pattern);
        $match = $router->match('GET', 'staff/9', PortalPrincipal::staff());
        $this->assertSame('staff/(:num)', $match?->route->pattern);
        $this->assertSame('9', $match->param('id'));
    }

    /**
     * @return array<string, array{string, string, RouteAccess}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedForPatientsProvider(): array
    {
        return [
            'literal staff route' => ['staff', 'staff', RouteAccess::Staff],
            'wildcard staff route' => ['staff/(:num)', 'staff/9', RouteAccess::Staff],
            'literal closed route' => ['closed', 'closed', RouteAccess::None],
            'wildcard closed route' => ['closed/(:num)', 'closed/9', RouteAccess::None],
        ];
    }

    #[DataProvider('refusedForPatientsProvider')]
    public function testPortalPatientsAreRefused(string $pattern, string $path, RouteAccess $access): void
    {
        $router = $this->router($this->route('GET', $pattern, $access));

        $this->expectException(PortalRouteDeniedException::class);
        $router->match('GET', $path, $this->patient());
    }

    /**
     * A portal patient reaches routes open to all, with their parameters.
     */
    public function testPortalPatientsReachOpenRoutes(): void
    {
        $router = $this->router(
            $this->route('GET', 'open', RouteAccess::All),
            $this->route('GET', 'open/(:num)', RouteAccess::All, params: ['id' => 1]),
        );

        $this->assertNotNull($router->match('GET', 'open', $this->patient()));
        $this->assertSame('9', $router->match('GET', 'open/9', $this->patient())?->param('id'));
    }

    public function testOnlyAPortalPatientHasAPatientPid(): void
    {
        $this->assertSame(5, $this->patient()->patientPid());
        $this->assertNull(PortalPrincipal::staff()->patientPid());
        $this->assertNull(PortalPrincipal::registering()->patientPid());
    }

    /**
     * @return array<string, array{string, string, RouteAccess}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function refusedForStaffProvider(): array
    {
        return [
            'a closed route' => ['closed', 'closed', RouteAccess::None],
        ];
    }

    #[DataProvider('refusedForStaffProvider')]
    public function testStaffAreRefusedPatientAndClosedRoutes(string $pattern, string $path, RouteAccess $access): void
    {
        $router = $this->router($this->route('GET', $pattern, $access));

        $this->expectException(PortalRouteDeniedException::class);
        $router->match('GET', $path, PortalPrincipal::staff());
    }

    public function testRegistrationCanReachARegistrationRouteThatIsClosedToEveryoneElse(): void
    {
        $router = $this->router($this->route('POST', 'api/patient', RouteAccess::None, registration: true));

        $this->assertNotNull($router->match('POST', 'api/patient', PortalPrincipal::registering()));

        $this->expectException(PortalRouteDeniedException::class);
        $router->match('POST', 'api/patient', PortalPrincipal::staff());
    }

    /**
     * @return array<string, array{string, string, RouteAccess}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function closedToRegistrationProvider(): array
    {
        return [
            'literal open route' => ['onsitedocuments', 'onsitedocuments', RouteAccess::All],
            'literal staff route' => ['staff', 'staff', RouteAccess::Staff],
            'wildcard open route' => ['api/patient/(:num)', 'api/patient/9', RouteAccess::All],
            'wildcard staff route' => ['staff/(:num)', 'staff/9', RouteAccess::Staff],
        ];
    }

    #[DataProvider('closedToRegistrationProvider')]
    public function testRegistrationCannotReachRoutesWithoutRegistrationAccess(string $pattern, string $path, RouteAccess $access): void
    {
        $router = $this->router($this->route('GET', $pattern, $access));

        $this->expectException(PortalRouteDeniedException::class);
        $router->match('GET', $path, PortalPrincipal::registering());
    }

    public function testALiteralRouteWinsOverAnEarlierWildcard(): void
    {
        $router = $this->router(
            $this->route('GET', 'api/(:any)', RouteAccess::None),
            $this->route('GET', 'api/onsitedocuments', RouteAccess::All),
        );

        $this->assertSame('api/onsitedocuments', $router->match('GET', 'api/onsitedocuments', PortalPrincipal::staff())?->route->pattern);
    }

    public function testAnUnknownPathOrMethodIsNotFound(): void
    {
        $router = $this->router($this->route('GET', 'open/(:num)', RouteAccess::All));

        $this->assertNull($router->match('GET', 'other', PortalPrincipal::staff()));
        $this->assertNull($router->match('GET', 'open/abc', PortalPrincipal::staff()), '(:num) matches digits only');
        $this->assertNull($router->match('DELETE', 'open/9', PortalPrincipal::staff()));
    }

    /**
     * Every route in the table names a controller action that exists.
     */
    public function testEveryRouteNamesARealControllerAction(): void
    {
        foreach (PortalRouteTable::routes() as $route) {
            $this->assertTrue(method_exists($route->controller, $route->action), $route->key() . ' → ' . $route->controller . '::' . $route->action);
        }
    }

    /**
     * A registering caller reaches only the registration form routes.
     */
    public function testOnlyTheRegistrationFormRoutesAreOpenToRegistration(): void
    {
        $open = array_values(array_map(
            static fn(PortalRoute $route): string => $route->key(),
            // The unknown-endpoint reply is open to every caller; it serves no data.
            array_filter(
                PortalRouteTable::routes(),
                static fn(PortalRoute $route): bool => $route->registration && $route->action !== 'ErrorApi404',
            ),
        ));
        sort($open);

        $this->assertSame(['GET:api/patientdata', 'GET:patientdata', 'POST:api/patient'], $open);
    }

    public function testActivityReviewRoutesAreStaffOnly(): void
    {
        $staff = array_values(array_map(
            static fn(PortalRoute $route): string => $route->key(),
            array_filter(PortalRouteTable::routes(), static fn(PortalRoute $route): bool => $route->access === RouteAccess::Staff),
        ));

        $this->assertSame(['PUT:api/patient/(:num)', 'GET:onsiteactivityviews', 'GET:api/onsiteactivityviews', 'GET:api/onsiteactivityview/(:any)'], $staff);
    }

    public function testOnlyStaffSaveAProfileStraightToTheChart(): void
    {
        $router = new PortalRouter(PortalRouteTable::routes());

        $this->assertSame('Update', $router->match('PUT', 'api/patient/9', PortalPrincipal::staff())?->route->action);
        // A portal patient sends their changes for review instead.
        $this->assertSame(PortalPatientController::class, $router->match('PUT', 'api/portalpatient/9', $this->patient())?->route->controller);

        $this->expectException(PortalRouteDeniedException::class);
        $router->match('PUT', 'api/patient/9', $this->patient());
    }

    /**
     * The document API routes go to OnsiteDocumentController, open to all callers.
     */
    public function testTheDocumentRoutesAreOpenAndCheckedByTheController(): void
    {
        $router = new PortalRouter(PortalRouteTable::routes());

        foreach (['GET' => 'api/onsitedocuments', 'POST' => 'api/onsitedocument', 'PUT' => 'api/onsitedocument/5', 'DELETE' => 'api/onsitedocument/5'] as $method => $path) {
            $match = $router->match($method, $path, PortalPrincipal::staff());
            $this->assertSame(OnsiteDocumentController::class, $match?->route->controller, "{$method} {$path}");
            $this->assertSame(RouteAccess::All, $match->route->access);
        }
    }

    /**
     * An unknown API path gets the unknown-endpoint reply, whoever asks and whatever the method.
     */
    public function testAnyOtherApiPathGetsTheUnknownEndpointReplyForEveryone(): void
    {
        $router = new PortalRouter(PortalRouteTable::routes());

        foreach ([PortalPrincipal::staff(), $this->patient(), PortalPrincipal::registering()] as $caller) {
            foreach (['GET', 'PUT', 'POST', 'DELETE'] as $method) {
                $match = $router->match($method, 'api/no-such-endpoint', $caller);
                $this->assertSame('ErrorApi404', $match?->route->action, "{$method} for {$caller->kind->name}");
            }
        }
    }

    private function patient(): PortalPrincipal
    {
        return PortalPrincipal::portalPatient(5);
    }

    /**
     * @param array<string, int> $params
     */
    private function route(string $method, string $pattern, RouteAccess $access, bool $registration = false, array $params = []): PortalRoute
    {
        return new PortalRoute($method, $pattern, ProviderController::class, 'Home', $access, $registration, $params);
    }

    private function router(PortalRoute ...$routes): PortalRouter
    {
        return new PortalRouter(array_values($routes));
    }
}
