<?php

/**
 * FhirRouteAclEnforcementIsolatedTest
 *
 * Coverage for the FHIR route-table ACL sweep. A previous pass swept the
 * standard route table but missed several FHIR routes; the routes covered
 * here now carry ACL gates that match each route's data class.
 *
 * The test asserts textual invariants on
 * `apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php` — this is a
 * source-file inspection (like AllergyConditionRouteParameterTest) so it
 * can run isolated without bringing up the REST dispatcher, dispatching a
 * request, or standing up a database.
 *
 * Design rationale for source-inspection rather than dispatch-invocation
 * tests: the FHIR route callbacks reach `RestConfig::request_authorization_check`
 * which reads the process-wide `$_SESSION` / `authUser`. Building a full
 * dispatch harness that authenticates a real user is out of scope for an
 * isolated (no-DB, no-session) test tier — the DB-backed API tests under
 * `tests/Tests/Api/FHIR/` cover live enforcement. What we want to lock in
 * here is that the route file has not silently drifted: someone reverting
 * one of the ACL gates would fail this test even without shipping.
 *
 * Covers:
 * - Media, QuestionnaireResponse, Person route branches
 * - DocumentReference `$docref` non-patient branch
 * - Route-level ACL enforcement runs regardless of the auth mechanism that
 *   got the caller past the AuthorizationListener
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Isolated\RestRoutes;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FhirRouteAclEnforcementIsolatedTest extends TestCase
{
    private string $routeContent = '';

    protected function setUp(): void
    {
        $resolved = realpath(__DIR__ . '/../../../../apis/routes/_rest_routes_fhir_r4_us_core_3_1_0.inc.php');
        if (!is_string($resolved)) {
            $this->markTestSkipped('FHIR route file not found');
        }
        $content = file_get_contents($resolved);
        if ($content === false) {
            $this->markTestSkipped('Failed to read FHIR route file');
        }
        $this->routeContent = $content;
    }

    /**
     * Extract the closure body for a single route entry, keyed by its
     * "METHOD /fhir/…" literal. Returns the substring between the opening
     * `function (…) {` and the matching closing brace of the closure. If
     * the route does not exist or the closure boundaries cannot be
     * determined the test that called us will fail its assertion — do not
     * silently return an empty string.
     */
    private function extractRouteBody(string $routeKey): string
    {
        // Match the route key as a quoted or single-quoted array key up
        // through the `function (…) {` header, then capture until the
        // matching close brace at the same indentation.
        $escapedKey = preg_quote($routeKey, '/');
        // The closure signature and opening brace, plus body up to first
        // `    },` at outer indentation (route entries all sit at that
        // level in this file).
        $pattern = '/["\']' . $escapedKey . '["\']\s*=>\s*function\s*\([^)]*\)\s*\{(.*?)\n    \},/s';
        if (preg_match($pattern, $this->routeContent, $matches) === 1) {
            return $matches[1];
        }
        return '';
    }

    // -------------------------------------------------------------------------
    // Media/:uuid must branch and gate the non-patient side
    // -------------------------------------------------------------------------

    public function testMediaSingleRouteBranchesOnIsPatientRequest(): void
    {
        $body = $this->extractRouteBody('GET /fhir/Media/:uuid');
        $this->assertNotSame('', $body, 'Media/:uuid route must exist');
        $this->assertStringContainsString(
            '$request->isPatientRequest()',
            $body,
            'Media/:uuid must branch on isPatientRequest so patient tokens are compartment-bound and non-patient tokens hit the ACL gate'
        );
    }

    public function testMediaSingleRouteRequiresAclOnNonPatientBranch(): void
    {
        $body = $this->extractRouteBody('GET /fhir/Media/:uuid');
        $this->assertNotSame('', $body, 'Media/:uuid route must exist');
        $this->assertMatchesRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']patients["\'],\s*["\']demo["\']/',
            $body,
            'Media/:uuid non-patient branch must call RestConfig::request_authorization_check(..., "patients", "demo") — mirrors the sibling list route :413-424'
        );
    }

    public function testMediaSingleRoutePatientBranchBindsPuuid(): void
    {
        $body = $this->extractRouteBody('GET /fhir/Media/:uuid');
        $this->assertNotSame('', $body, 'Media/:uuid route must exist');
        $this->assertStringContainsString(
            'getOne($uuid, $request->getPatientUUIDString())',
            $body,
            'Media/:uuid patient branch must bind the caller puuid so the compartment filter engages'
        );
    }

    // -------------------------------------------------------------------------
    // QuestionnaireResponse routes must gate the non-patient side
    // -------------------------------------------------------------------------

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function questionnaireResponseRouteProvider(): array
    {
        return [
            'list route' => ['GET /fhir/QuestionnaireResponse'],
            'single route' => ['GET /fhir/QuestionnaireResponse/:uuid'],
        ];
    }

    #[DataProvider('questionnaireResponseRouteProvider')]
    public function testQuestionnaireResponseRouteRequiresAclOnNonPatientBranch(string $routeKey): void
    {
        $body = $this->extractRouteBody($routeKey);
        $this->assertNotSame('', $body, sprintf('%s route must exist', $routeKey));
        $this->assertStringContainsString(
            '!$request->isPatientRequest()',
            $body,
            sprintf('%s must guard the non-patient branch — plain `if (!$request->isPatientRequest())`', $routeKey)
        );
        $this->assertMatchesRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']patients["\'],\s*["\']med["\']/',
            $body,
            sprintf('%s non-patient branch must call RestConfig::request_authorization_check(..., "patients", "med")', $routeKey)
        );
    }

    /**
     * The controller had a commented-out authorization_check call; the check
     * now lives on the route so the comment must be gone (leaving it invites
     * a future developer to uncomment it and double-check, or worse to
     * interpret its presence as "no check needed here").
     */
    public function testQuestionnaireResponseControllerHasNoStaleCommentedAuthCheck(): void
    {
        $controllerPath = realpath(__DIR__ . '/../../../../src/RestControllers/FHIR/FhirQuestionnaireResponseRestController.php');
        $this->assertIsString($controllerPath, 'FhirQuestionnaireResponseRestController.php must exist');
        $content = file_get_contents($controllerPath);
        $this->assertIsString($content, 'Controller file must be readable');
        $this->assertStringNotContainsString(
            '// RestConfig::authorization_check("patients", "med")',
            $content,
            'Stale commented ACL check must be removed — enforcement now lives at the FHIR route'
        );
    }

    // -------------------------------------------------------------------------
    // DocumentReference $docref non-patient branch must use admin/super
    // -------------------------------------------------------------------------

    public function testDocRefOperationNonPatientBranchRequiresAdminSuper(): void
    {
        $body = $this->extractRouteBody('POST /fhir/DocumentReference/$docref');
        $this->assertNotSame('', $body, 'POST /fhir/DocumentReference/$docref route must exist');
        $this->assertMatchesRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']admin["\'],\s*["\']super["\']/',
            $body,
            'DocumentReference/$docref non-patient branch must require admin/super — mirrors GET /fhir/DocumentReference :247-258'
        );
        $this->assertDoesNotMatchRegularExpression(
            '/RestConfig::request_authorization_check\(\s*\$request,\s*["\']patients["\'],\s*["\']demo["\']/',
            $body,
            'DocumentReference/$docref must NOT use the weaker patients/demo gate — sibling admin operations use admin/super'
        );
    }

    // -------------------------------------------------------------------------
    // Person/:uuid patient-scope else branch — must NOT bind puuid while the
    // underlying FhirPersonService is INonPatientCompartmentResourceService.
    //
    // Binding puuid on that shape has no effect (the base check drops the
    // bind for non-patient-compartment services) and misleadingly implies
    // the endpoint enforces the patient compartment. Patient tokens
    // legitimately need to read provider Person records via this endpoint.
    // -------------------------------------------------------------------------

    public function testPersonSingleRoutePatientElseBranchDoesNotBindPuuid(): void
    {
        $body = $this->extractRouteBody('GET /fhir/Person/:uuid');
        $this->assertNotSame('', $body, 'GET /fhir/Person/:uuid route must exist');
        $this->assertDoesNotMatchRegularExpression(
            '/getOne\(\s*\$uuid,\s*\$request->getPatientUUIDString\(\)\s*\)/',
            $body,
            'Person/:uuid patient-scope else branch must NOT pass puuid: FhirPersonService is declared INonPatientCompartmentResourceService and the bind is a no-op that misleadingly implies compartment enforcement.'
        );
    }

    // -------------------------------------------------------------------------
    // Route-level ACL runs regardless of auth mechanism. The following meta-
    // assertion catches accidental future changes that push enforcement into
    // an event listener that respects skipAuthorization.
    // -------------------------------------------------------------------------

    /**
     * `RestConfig::request_authorization_check` is called INLINE from each
     * FHIR route closure — it does not depend on any auth-strategy state
     * being set in the request attributes. That's the invariant that makes
     * per-route ACL gates fire for both OAuth-bearer and APICSRFTOKEN
     * (LocalApiAuthorizationController) sessions: the check reads only the
     * authUser from the session, which both auth paths populate.
     *
     * If someone refactors these checks into an event listener that respects
     * `skipAuthorization`, callers on the local-session path bypass the
     * check. This test locks the inline-call pattern for these routes.
     */
    public function testGatedRoutesUseInlineAclCheck(): void
    {
        $gatedRoutes = [
            'GET /fhir/Media/:uuid',
            'POST /fhir/DocumentReference/$docref',
            'GET /fhir/QuestionnaireResponse',
            'GET /fhir/QuestionnaireResponse/:uuid',
        ];
        foreach ($gatedRoutes as $route) {
            $body = $this->extractRouteBody($route);
            $this->assertNotSame('', $body, sprintf('%s route must exist', $route));
            $this->assertStringContainsString(
                'RestConfig::request_authorization_check(',
                $body,
                sprintf('%s must call RestConfig::request_authorization_check inline — do not move to an event listener that respects skipAuthorization', $route)
            );
        }
    }
}
