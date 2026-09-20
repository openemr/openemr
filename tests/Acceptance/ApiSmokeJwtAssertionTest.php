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
 * Authenticated OpenEMR REST API smoke test — private_key_jwt variant
 * of the authorization_code flow.
 *
 * Companion to ApiSmokeTest, which runs the same DCR + admin-approval
 * + authorization-code + token-exchange + Bearer-request flow against
 * /apis/default/api/facility, but with client_secret_post client
 * authentication on the token endpoint. This test does the same thing
 * with token_endpoint_auth_method=private_key_jwt: the client is
 * registered with a JWKS and authenticates at /token with a signed
 * JWT client_assertion instead of a shared secret.
 *
 * Why a separate test class rather than a data provider on
 * ApiSmokeTest: the two variants exercise different server-side code
 * paths (CustomAuthCodeGrant's traditional client_secret branch vs its
 * JWT-assertion branch, plus different ClientRepository::validateClient
 * behaviour for the null-secret case). Splitting the tests gives
 * separate failure signals so a regression in one branch does not
 * mask (or get masked by) a regression in the other.
 *
 * Failure modes caught here that ApiSmokeTest does NOT catch:
 *   - CustomAuthCodeGrant::validateClient JWT-assertion branch
 *     regression (the branch that calls JwtAuthenticationService)
 *   - ClientRepository::validateClient rejecting a null client_secret
 *     on authorization_code (the branch reached after JWT authentication
 *     succeeds — this is the specific regression this test locks in)
 *   - JWKS-based public key resolution
 *     (JwtAuthenticationService::validateJWTClientAssertion)
 *   - DCR handling of token_endpoint_auth_method=private_key_jwt +
 *     jwks payload
 *
 * Prerequisites are identical to ApiSmokeTest — api-enable.php Panther
 * bootstrap must have run.
 */
#[Group('api-enabled')]
final class ApiSmokeJwtAssertionTest extends TestCase
{
    /**
     * The /token endpoint must reject a client_assertion with a tampered
     * signature — otherwise the JWT client-authentication branch is
     * accepting anything JWT-shaped and confidential-client identity on
     * this transport is not actually being enforced. Companion test to
     * the success case below; keeps the positive and negative outcomes
     * of the same validation seam side-by-side.
     */
    public function testTokenEndpointRejectsTamperedJwtAssertion(): void
    {
        $browser = AuthCodeFlow::attemptTokenExchangeWithJwtAssertion(
            'openid api:oemr user/facility.crus',
            // Replace the signature segment (parts[2] of header.payload.sig)
            // with a valid-base64url string of the same length. 'A' in
            // base64url decodes to six zero bits, so an all-'A' segment
            // decodes to all-zero bytes — a well-formed but definitely
            // wrong RSA signature. Preserving the length keeps the JWT
            // parseable so the server reaches the signature-verification
            // step (rather than short-circuiting on a decode error) and
            // rejects there, which is the property we want to prove.
            static function (string $assertion): array {
                $parts = explode('.', $assertion);
                $parts[2] = str_repeat('A', strlen($parts[2]));
                return [
                    'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                    'client_assertion' => implode('.', $parts),
                ];
            },
        );
        $response = $browser->getResponse();

        self::assertSame(
            401,
            $response->getStatusCode(),
            'A JWT client_assertion whose signature does not verify against the'
                . ' registered JWKS must be rejected with 401. A 200 here means the'
                . ' JWT validation is not actually gating client authentication —'
                . ' any assertion-shaped string would then be accepted.',
        );
        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'Token endpoint should return a JSON error body on rejection');
        self::assertSame(
            'invalid_client',
            $body['error'] ?? null,
            'Rejection error code should be OAuth2 invalid_client, not e.g. invalid_grant',
        );
    }

    public function testAuthenticatedFacilityEndpointReturnsFacilityListWithJwtAssertion(): void
    {
        // Same scopes as ApiSmokeTest — the variance is purely on the
        // client-authentication transport at /token, so keeping the
        // scope + endpoint identical means any assertion difference
        // is attributable to the transport rather than to authorization.
        $accessToken = AuthCodeFlow::mintAccessTokenWithJwtAssertion('openid api:oemr user/facility.crus');

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
            'GET /apis/default/api/facility with a Bearer minted via private_key_jwt must return 200. '
            . 'Codes: 401 = Bearer strategy rejected the token; '
            . '403 = api:oemr missing or user role wrong; '
            . '401 with scope missing = SMART scope not granted at consent time',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'Facility endpoint must return JSON');
        self::assertArrayHasKey('data', $body, 'Facility list response should have a data key');
        self::assertIsArray($body['data']);
        self::assertNotEmpty(
            $body['data'],
            'A fresh install has at least one facility',
        );
    }
}
