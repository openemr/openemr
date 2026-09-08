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
 * OAuth2/OIDC successful-flow smoke test — the counterpart to
 * OAuth2SmokeTest's API-disabled 404 gate assertions.
 *
 * Prerequisite: the acceptance harness must have run
 * `tests/Acceptance/bin/api-enable.php` first to enable the API
 * globals + set site_addr_oath. Without that, /oauth2/default/*
 * endpoints return 404 with "API is disabled" (which is what
 * OAuth2SmokeTest asserts). This test asserts the ENABLED behavior:
 * discovery returns provider metadata, DCR mints client credentials.
 *
 * Together with OAuth2SmokeTest, both sides of the API-enable gate
 * policy are pinned down:
 *   - default install → gate rejects (OAuth2SmokeTest)
 *   - admin-panel-enabled install → endpoints respond normally
 *     (this test)
 *
 * Full authenticated Bearer-token access (POST /oauth2/default/token
 * exchange + GET /apis/default/api/version) is the follow-up
 * Phase 4a-3 slice — needs the auth-code flow via login-form scrape
 * + consent-form scrape, which is a chunky additional surface. This
 * PR intentionally covers only the "endpoints reachable + minting
 * credentials" half of the successful flow.
 */
#[Group('api-enabled')]
final class OAuth2ApiEnabledTest extends TestCase
{
    public function testOidcDiscoveryReturnsProviderMetadata(): void
    {
        // Per OIDC Discovery spec, .well-known/openid-configuration
        // is a JSON document listing provider endpoints. Once the
        // API is enabled, this endpoint returns 200 with the
        // discovery payload (rather than the 404 API-disabled gate
        // OAuth2SmokeTest asserts on a default install).
        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/oauth2/default/.well-known/openid-configuration',
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'OIDC discovery must return 200 when API is enabled — a 404 with "API is disabled" means the api-enable.php bootstrap did not run or did not persist',
        );
        self::assertStringStartsWith(
            'application/json',
            ResponseHeaders::first($response, 'Content-Type'),
            'OIDC discovery response must be application/json',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'OIDC discovery response must be a JSON object');

        // The four mandatory OIDC provider-metadata fields per
        // https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata,
        // plus registration_endpoint (optional per spec, but openemr
        // ships DCR and downstream tests rely on it being advertised).
        foreach (
            ['issuer', 'authorization_endpoint', 'token_endpoint', 'jwks_uri', 'registration_endpoint'] as $key
        ) {
            self::assertArrayHasKey($key, $body, "OIDC discovery missing required key: {$key}");
            self::assertIsString($body[$key]);
            self::assertNotFalse(
                filter_var($body[$key], FILTER_VALIDATE_URL),
                "OIDC discovery {$key} must be a valid absolute URL",
            );
        }

        // The issuer claim on minted tokens is derived from the
        // provider's advertised issuer, which itself comes from
        // globals.site_addr_oath. The api-enable.php bootstrap sets
        // that to ACCEPTANCE_ARTIFACT_URL so tokens' iss matches
        // what tests hit. Assert the discovery issuer reflects that.
        // (Absolute-URL check above already validates shape.)
        self::assertStringStartsWith(
            ArtifactBrowser::baseUrl(),
            $body['issuer'],
            'OIDC issuer should start with the acceptance base URL — mismatch means api-enable.php did not set site_addr_oath correctly, and downstream authenticated tests will fail on iss-claim validation',
        );
    }

    public function testDynamicClientRegistrationMintsClientCredentials(): void
    {
        // Per RFC 7591, POST /oauth2/default/registration with a
        // well-formed client metadata document returns a response
        // containing client_id + client_secret (for confidential
        // clients using client_secret_* auth methods). Client name
        // is randomized so parallel test runs don't clash.
        $clientName = 'AcceptanceApiEnabled-' . bin2hex(random_bytes(4));
        // application_type: 'private' is openemr's non-spec extension
        // that signals a confidential-client registration (returns
        // client_secret). OIDC Dynamic Registration spec only defines
        // 'web' / 'native' — those get public clients (PKCE-only, no
        // secret). See AuthorizationController::clientRegistration
        // (grep "application_type"): only 'private' takes the
        // client_secret branch. This test asserts the confidential-
        // client shape, so 'private' is the right value.
        $registration = [
            'application_type' => 'private',
            'redirect_uris' => ['https://acceptance-harness.example/callback'],
            'client_name' => $clientName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['acceptance-harness@openemr.example'],
            'scope' => 'openid fhirUser',
        ];

        $browser = ArtifactBrowser::create();
        $browser->request(
            'POST',
            ArtifactBrowser::baseUrl() . '/oauth2/default/registration',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($registration, JSON_THROW_ON_ERROR),
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'DCR must return 200 when API is enabled per RFC 7591 — a 404 with "API is disabled" means the api-enable.php bootstrap did not run',
        );
        self::assertStringStartsWith(
            'application/json',
            ResponseHeaders::first($response, 'Content-Type'),
            'DCR response must be application/json',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray($body, 'DCR response must be a JSON object');
        self::assertArrayHasKey('client_id', $body, 'DCR response must include client_id per RFC 7591');
        self::assertArrayHasKey('client_secret', $body, 'DCR response must include client_secret when token_endpoint_auth_method is client_secret_post');
        self::assertIsString($body['client_id']);
        self::assertIsString($body['client_secret']);
        self::assertNotSame('', $body['client_id'], 'client_id must not be empty');
        self::assertNotSame('', $body['client_secret'], 'client_secret must not be empty');
        self::assertSame(
            $clientName,
            $body['client_name'] ?? null,
            'DCR response should echo the submitted client_name unchanged',
        );
    }
}
