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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * OAuth2/OIDC discovery + dynamic-client-registration smoke test.
 *
 * Together these two endpoints are what every external OAuth2/OIDC
 * integration hits FIRST — before any token can be minted, before any
 * login form is rendered, before any authenticated call is made.
 * Neither requires authentication (both are RFC-standard public
 * discovery/onboarding endpoints).
 *
 * If either regresses in a shipped artifact:
 *   - Every SMART-on-FHIR / OIDC client onboarding fails at step 1
 *   - Every dynamically-registered integration (Inferno bulk-data
 *     clients, external app-launch clients, etc.) can't get past
 *     initial handshake
 *
 * The FhirSmokeTest in Phase 4a covers the FHIR-flavored analog
 * (/apis/default/fhir/metadata + .well-known/smart-configuration).
 * This test covers the OAuth2/OIDC side of the discovery surface,
 * plus proves the DCR endpoint mints client credentials on demand.
 *
 * Authenticated API access (token issuance + Bearer requests against
 * /apis/default/api/*) is Phase 4a-3, blocked on the site_addr_oath
 * install-time configuration story (issuer claim in minted tokens
 * doesn't match the acceptance URL by default because the artifact
 * detects its own base URL from HTTP_HOST at install time, not from
 * the acceptance-runner's perspective).
 */
#[Group('fresh-install')]
#[Group('post-upgrade')]
final class OAuth2SmokeTest extends TestCase
{
    public function testOidcDiscoveryEndpointReturnsProviderMetadata(): void
    {
        // Per OIDC spec (https://openid.net/specs/openid-connect-discovery-1_0.html),
        // /.well-known/openid-configuration returns a JSON provider-
        // metadata document with mandatory fields including issuer,
        // authorization_endpoint, token_endpoint, jwks_uri. Must be
        // publicly accessible so clients can bootstrap the auth flow.
        $browser = ArtifactBrowser::create();
        $browser->request(
            'GET',
            ArtifactBrowser::baseUrl() . '/oauth2/default/.well-known/openid-configuration',
        );
        $response = $browser->getResponse();

        self::assertSame(
            200,
            $response->getStatusCode(),
            'OIDC /.well-known/openid-configuration must return 200 without auth per OIDC Discovery spec',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            'OIDC discovery response should be JSON — non-JSON body means the response is an HTML error page',
        );

        // The four mandatory OIDC provider-metadata fields per
        // https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata.
        // registration_endpoint is optional per OIDC spec, but openemr
        // ships DCR support and clients doing dynamic registration
        // (the whole point of the DCR test below) MUST have it advertised.
        foreach (
            [
                'issuer',
                'authorization_endpoint',
                'token_endpoint',
                'jwks_uri',
                'registration_endpoint',
            ] as $key
        ) {
            self::assertArrayHasKey(
                $key,
                $body,
                "OIDC discovery missing required key: {$key}",
            );
            self::assertIsString($body[$key]);
            self::assertNotFalse(
                filter_var($body[$key], FILTER_VALIDATE_URL),
                "OIDC discovery {$key} must be a valid absolute URL — empty or malformed value blocks clients from discovering the auth surface",
            );
        }
    }

    public function testDynamicClientRegistrationMintsClientCredentials(): void
    {
        // Per RFC 7591 (OAuth 2.0 Dynamic Client Registration), a POST
        // to the registration endpoint with a valid client metadata
        // document returns 200 with `client_id` + `client_secret` (the
        // latter for confidential clients using client_secret_* auth
        // methods). Openemr's DCR endpoint is open (no initial access
        // token required), which is standard for public registration.
        //
        // Purpose: proves external integrations can register a client
        // and obtain credentials — the mandatory first step of every
        // SMART app launch, Inferno certification run, etc.
        //
        // No DB cleanup: acceptance tests run against a per-CI-run
        // fresh install, so the registered client just gets thrown
        // away with the whole DB when the workflow finishes. The
        // client_name is randomized to avoid accidental clash with
        // any parallel run against the same artifact.
        $clientName = 'AcceptanceHarnessDcrSmoke-' . bin2hex(random_bytes(4));
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
            'DCR POST /oauth2/default/registration must return 200 for a well-formed registration request per RFC 7591',
        );

        $body = json_decode($response->getContent(), true);
        self::assertIsArray(
            $body,
            'DCR response should be JSON — non-JSON body means the auth-server routing is broken',
        );
        self::assertArrayHasKey(
            'client_id',
            $body,
            'DCR response must include client_id per RFC 7591 — without it the caller has no way to identify the newly-registered client',
        );
        self::assertArrayHasKey(
            'client_secret',
            $body,
            'DCR response must include client_secret when token_endpoint_auth_method is client_secret_post — without it confidential-client flows cannot authenticate',
        );
        self::assertIsString($body['client_id']);
        self::assertIsString($body['client_secret']);
        self::assertNotSame('', $body['client_id'], 'client_id must not be empty');
        self::assertNotSame('', $body['client_secret'], 'client_secret must not be empty');
        // Echo-back check: server should preserve client_name as
        // supplied. If a template munges it, clients won't be able to
        // find their own registration record.
        self::assertSame(
            $clientName,
            $body['client_name'] ?? null,
            'DCR response should echo the submitted client_name unchanged',
        );
    }
}
