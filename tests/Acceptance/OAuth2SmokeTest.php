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
use OpenEMR\Tests\Acceptance\Support\ResponseHeaders;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * OAuth2 API-disabled gate smoke test.
 *
 * Openemr's `OAuth2AuthorizationListener` gates every `/oauth2/*`
 * request behind "at least one of `rest_api`, `rest_fhir_api`, or
 * `rest_portal_api` must be enabled" — see
 * src/RestControllers/Subscriber/OAuth2AuthorizationListener.php.
 * The default openemr install ships with all three globals set to
 * `0` (defense-in-depth: API off unless admins explicitly opt in via
 * the UI), so this is the state every fresh production docker image
 * and tarball install starts in.
 *
 * Both tests below assert that gate is in place and returns the
 * expected shape (404 + JSON body carrying "API is disabled"):
 *
 *   - Regression signal: if the listener check is ever removed or
 *     gated wrongly, OAuth2 endpoints would become externally
 *     reachable on a default install — exposing dynamic client
 *     registration + full auth surface to any anonymous caller who
 *     can reach the artifact. These tests fail loudly if that
 *     regression ever ships.
 *
 *   - Contract shape: proves the gate returns 404 (not 500, not a
 *     redirect to setup.php, not a raw HTML error page) so external
 *     OAuth2 clients receive a definitive "not here" response with
 *     an interpretable error body.
 *
 *   - Coverage complement: Phase 4a's FhirSmokeTest proves the FHIR
 *     discovery paths BYPASS the gate (they're on the
 *     SkipAuthorizationStrategy allowlist and MUST be reachable
 *     without auth per FHIR/SMART spec). This one proves the OAuth2
 *     paths do NOT accidentally bypass it — the two tests together
 *     pin down both sides of the auth-gate policy.
 *
 * Once Phase 4c's wizard-UI tests can drive the admin panel to flip
 * an API toggle, additional assertions can verify OAuth2 endpoints
 * return 200 when the API is actually enabled. Full authenticated
 * Bearer-token access is still Phase 4a-3 (blocked on the
 * site_addr_oath install-time story).
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class OAuth2SmokeTest extends TestCase
{
    private const API_DISABLED_MESSAGE = 'API is disabled';

    public function testOidcDiscoveryEndpointIsGatedWhenApiDisabled(): void
    {
        // The discovery endpoint is the first thing every OIDC client
        // hits before any auth handshake. If the API-disabled gate is
        // accidentally removed here, the entire OAuth2 surface (auth,
        // token, DCR, jwks) becomes reachable by anonymous callers on
        // any freshly-installed openemr artifact.
        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/oauth2/default/.well-known/openid-configuration',
        );
        $response = $browser->getResponse();

        self::assertSame(
            404,
            $response->getStatusCode(),
            'OIDC discovery must be 404 on a default (API-disabled) install — a 200 response means the OAuth2AuthorizationListener gate has been bypassed and the OAuth2 surface is now reachable anonymously',
        );

        // Content-Type asserted BEFORE json_decode because a JSON-shaped
        // response served with the wrong media type (text/html, text/plain)
        // fails machine-readable clients even though json_decode would
        // still parse the body. Assert the media-type prefix so a
        // ; charset=utf-8 suffix doesn't break the check.
        self::assertStringStartsWith(
            'application/json',
            ResponseHeaders::first($response, 'Content-Type'),
            'API-disabled response must be served as application/json — a text/html response means an unhandled framework exception rendered the default HTML error page instead of the RestConfig-shaped JSON error body',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            'API-disabled response should be a JSON error object — a non-JSON body (raw HTML, empty response, redirect) means the response shape has regressed and machine-readable error handling is broken',
        );
        self::assertArrayHasKey('message', $body, 'API-disabled response must carry a machine-readable "message" field');
        self::assertIsString($body['message']);
        self::assertStringContainsString(
            self::API_DISABLED_MESSAGE,
            $body['message'],
            'Error body should identify the reason as API-disabled — a different message means the 404 came from a different code path than the API-disabled gate',
        );
    }

    public function testDynamicClientRegistrationIsGatedWhenApiDisabled(): void
    {
        // The DCR endpoint is the mechanism by which external clients
        // onboard themselves. If the API-disabled gate is bypassed
        // here, anonymous callers can mint arbitrary client_id +
        // client_secret pairs against a default install — one step
        // closer to unauthenticated abuse of the token endpoint. Fire
        // a well-formed POST body so this test would still fail
        // (rather than 400/415) if the gate were removed but the DCR
        // handler still validated its input.
        $browser = ArtifactBrowser::create();
        $browser->request(
            'POST',
            ArtifactBrowser::baseUrl() . '/oauth2/default/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'application_type' => 'web',
                'redirect_uris' => ['https://acceptance-harness.example/callback'],
                'client_name' => 'AcceptanceHarnessApiDisabledGateProbe',
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['acceptance-harness@openemr.example'],
                'scope' => 'openid fhirUser',
            ], JSON_THROW_ON_ERROR),
        );
        $response = $browser->getResponse();

        self::assertSame(
            404,
            $response->getStatusCode(),
            'DCR must be 404 on a default (API-disabled) install — a 200/201 response means anonymous callers can now mint client credentials against a fresh openemr',
        );

        self::assertStringStartsWith(
            'application/json',
            ResponseHeaders::first($response, 'Content-Type'),
            'API-disabled DCR response must be served as application/json for machine-readable error handling',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'API-disabled DCR response should be a JSON error object');
        self::assertArrayHasKey('message', $body);
        self::assertIsString($body['message']);
        self::assertStringContainsString(
            self::API_DISABLED_MESSAGE,
            $body['message'],
            'DCR error body should identify the reason as API-disabled — different message means 404 came from a different code path',
        );
    }
}
