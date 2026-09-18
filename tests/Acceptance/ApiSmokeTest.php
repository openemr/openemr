<?php

/**
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Acceptance;

use OpenEMR\Tests\Acceptance\Support\ArtifactBrowser;
use OpenEMR\Tests\Acceptance\Support\OAuth2\AuthCodeFlow;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * Authenticated OpenEMR REST API smoke test.
 *
 * Runs the full DCR + admin-client-approval + authorization-code +
 * token-exchange flow via `Support/OAuth2/AuthCodeFlow`, then makes a
 * Bearer-authenticated `GET /apis/default/api/facility` and validates
 * the response. This is the second and final slice of Phase 4a-3 —
 * the first slice (#13201) landed the api-enable.php bootstrap plus
 * `OAuth2ApiEnabledTest` (OIDC discovery + DCR smoke). This slice adds
 * the "can external clients actually get an access token and use it
 * against a real Bearer-protected endpoint" coverage.
 *
 * Why /api/facility (not /api/version): the version endpoint is on the
 * SkipAuthorizationStrategy skip-list (see
 * src/RestControllers/Subscriber/AuthorizationListener.php), so a
 * Bearer request to it never exercises token validation. /api/facility
 * requires api:oemr + user/facility.* scope + admin/users ACL + Bearer
 * strategy pass — hitting it end-to-end proves every layer of the
 * dispatch pipeline is intact. The facility list is populated on every
 * install (at least "Your Clinic Name Here" from the install wizard)
 * so no seeding is needed.
 *
 * Prerequisite: the api-enable.php Panther bootstrap must have run
 * (rest_api / rest_fhir_api globals ON + site_addr_oath set to match
 * ACCEPTANCE_ARTIFACT_URL). Tagged #[Group('api-enabled')] so the
 * workflow runs it AFTER the bootstrap step, alongside
 * `OAuth2ApiEnabledTest`.
 *
 * Failure modes caught here that no earlier acceptance test covers:
 *   - `/authorize` regression (endpoint rejects valid parameters, or
 *     fails to redirect to login)
 *   - OAuth login form regression on `/oauth2/default/login` (distinct
 *     from `interface/login/login.php` which InstallTest covers)
 *   - Consent form regression (proceed button, scope grants, CSRF)
 *   - Admin client-approval regression (admin-client.php enable action,
 *     ClientRepository::updateClient IsEnabled path)
 *   - `/token` regression (code exchange, client_secret validation,
 *     iss/aud claim generation, JWT signing)
 *   - Bearer-token gate (`BearerTokenAuthorizationStrategy::verifyAccessToken`,
 *     signature check against the OAuth server's public key, iss claim
 *     validation, scope check via `isValidRequestForUserRole`)
 *   - SMART-scope check on the specific route
 *     (`AuthorizationListener::onRestApiSecurityCheck` for
 *     `user/facility.s`)
 *   - Route dispatch to `FacilityRestController::getAll` and JSON
 *     response serialization
 */
#[Group('api-enabled')]
final class ApiSmokeTest extends TestCase
{
    public function testAuthenticatedFacilityEndpointReturnsFacilityList(): void
    {
        // Scopes needed for GET /api/facility:
        //   - openid: OIDC branch (produces id_token, exercises signing)
        //   - api:oemr: Bearer strategy's per-namespace check
        //   - user/facility.crus: SMART search-scope check on the
        //     route (CRUS = create/read/update/search — the alias that
        //     ScopeRepository resolves user/facility.s against)
        $accessToken = AuthCodeFlow::mintAccessToken('openid api:oemr user/facility.crus');

        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/apis/default/api/facility',
            [],
            [],
            ['HTTP_AUTHORIZATION' => 'Bearer ' . $accessToken],
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'GET /apis/default/api/facility with valid Bearer + user/facility scope must return 200. '
            . 'Codes: 401 = Bearer strategy rejected the token (iss mismatch, signature invalid, expired); '
            . '403 (\"insufficient permissions for the requested resource\") = api:oemr missing from token scopes or user role wrong; '
            . '401 with \"scope user/facility.s not in access token\" = SMART scope not granted at consent time',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'Facility endpoint must return JSON');
        self::assertArrayHasKey('data', $body, 'Facility list response should have a data key');
        self::assertIsArray($body['data']);
        self::assertNotEmpty(
            $body['data'],
            'A fresh install has at least one facility (the one created by the install wizard) — empty list means either DB is unpopulated or the FacilityService is filtering everything out',
        );
    }
}
