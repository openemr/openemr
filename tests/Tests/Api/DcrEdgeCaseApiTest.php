<?php

/**
 * RFC 7591 Dynamic Client Registration edge-case regression gates.
 *
 * Rounds out the existing DCR test surface
 * (AuthorizationClientRegistrationNegativeTest, DisabledClientRejectionTest,
 * ClientRegistrationReadTest) with payload-size edges, duplicate client_name
 * policy, IDN redirect_uri handling, grant_types validation shape, and
 * RFC 7591 Section 3.2.1 response field completeness.
 *
 * Behaviours asserted are either RFC-mandated (e.g. invalid_client_metadata
 * for an unsupported grant_types value) or probe-current-policy (e.g. huge
 * array handling): the probes assert the server does not 500 and gives a
 * consistent accept-or-reject shape, so future refactors preserve the
 * boundary even if they tune the specific policy.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class DcrEdgeCaseApiTest extends TestCase
{
    private string $baseUrl;
    private Client $http;

    /** @var list<string> client_ids captured for tearDown cleanup */
    private array $clientIds = [];

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        $this->http = new Client([
            'base_uri' => $this->baseUrl,
            'verify' => false,
            'allow_redirects' => false,
            'http_errors' => false,
            'timeout' => 20,
        ]);
    }

    protected function tearDown(): void
    {
        foreach ($this->clientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                [$clientId]
            );
        }
        $this->clientIds = [];
    }

    /**
     * RFC 7591 Section 3.2.1: a successful DCR response must include at
     * minimum `client_id`, plus every requested metadata value that the
     * server stored. For a confidential client the server MUST also
     * include `client_secret` and `client_secret_expires_at`. OpenEMR
     * additionally issues a `registration_access_token` and
     * `registration_client_uri` so the client can later read its own
     * metadata (RFC 7592).
     */
    #[Test]
    public function testConfidentialRegistrationResponseIncludesRfc7591RequiredFields(): void
    {
        $body = $this->registerConfidentialClient([
            'redirect_uris' => ['https://client.example/cb'],
            'client_name' => 'DcrEdgeCase-Shape-' . bin2hex(random_bytes(3)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['e2e@test.example'],
            'scope' => 'openid',
        ]);

        // RFC 7591 core identifier
        $this->assertArrayHasKey('client_id', $body);
        $this->assertIsString($body['client_id']);
        $this->assertNotSame('', $body['client_id']);

        // RFC 7591 Section 3.2.1 for confidential clients
        $this->assertArrayHasKey('client_secret', $body);
        $this->assertIsString($body['client_secret']);
        $this->assertNotSame('', $body['client_secret']);
        $this->assertArrayHasKey('client_secret_expires_at', $body);
        $this->assertSame(0, $body['client_secret_expires_at']);

        // RFC 7591 Section 3.2.1: when issued, client_id_issued_at MUST
        // carry the issuance time as a seconds-since-epoch integer.
        $this->assertArrayHasKey('client_id_issued_at', $body);
        $this->assertIsInt($body['client_id_issued_at']);
        $this->assertGreaterThan(0, $body['client_id_issued_at']);

        // Fields the server stored for this client (echoed per Section 3.2.1)
        $this->assertArrayHasKey('redirect_uris', $body);
        $this->assertSame(['https://client.example/cb'], $body['redirect_uris']);
        $this->assertArrayHasKey('grant_types', $body);
        $this->assertIsArray($body['grant_types']);
        $this->assertNotEmpty($body['grant_types']);
        $this->assertArrayHasKey('token_endpoint_auth_method', $body);
        $this->assertSame('client_secret_post', $body['token_endpoint_auth_method']);
        $this->assertArrayHasKey('contacts', $body);
        $this->assertSame(['e2e@test.example'], $body['contacts']);
        $this->assertArrayHasKey('scope', $body);
        $this->assertSame('openid', $body['scope']);

        // OpenEMR DCR-management extensions (RFC 7592)
        $this->assertArrayHasKey('registration_access_token', $body);
        $this->assertIsString($body['registration_access_token']);
        $this->assertNotSame('', $body['registration_access_token']);
        $this->assertArrayHasKey('registration_client_uri', $body);
        $this->assertIsString($body['registration_client_uri']);
        $this->assertStringContainsString('/client/', $body['registration_client_uri']);
    }

    /**
     * A public (native/web) client registration must NOT carry a usable
     * client_secret. The server authenticates the public client via PKCE
     * at the token endpoint; emitting a secret would mislead the client
     * into treating it as a credential.
     *
     * token_endpoint_auth_method is omitted on this call because the
     * OpenEMR DCR validator currently only lists the three confidential
     * auth methods (client_secret_basic, client_secret_post,
     * private_key_jwt) in its enum, so sending "none" is rejected; the
     * net result for a public client is the same (PKCE-only).
     */
    #[Test]
    public function testPublicRegistrationResponseOmitsClientSecret(): void
    {
        $http = $this->http;
        $response = $http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'public',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'DcrEdgeCase-Public-' . bin2hex(random_bytes(3)),
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode(), 'Public DCR must succeed. Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('client_id', $body);
        $this->assertIsString($body['client_id']);
        $this->clientIds[] = $body['client_id'];

        // Public clients hold no usable secret; emitting an empty string
        // is acceptable, but a non-empty secret would be a leak.
        if (array_key_exists('client_secret', $body)) {
            $this->assertSame(
                '',
                $body['client_secret'],
                'Public client registration must not emit a usable client_secret'
            );
        }

        // RFC 7591 Section 3.2.1 integer timestamps
        $this->assertArrayHasKey('client_id_issued_at', $body);
        $this->assertIsInt($body['client_id_issued_at']);
    }

    /**
     * RFC 7591 does not require client_name uniqueness; multiple clients
     * may register with the same name. Register twice with identical
     * client_name and assert both calls succeed with distinct client_ids.
     */
    #[Test]
    public function testDuplicateClientNameIsAllowed(): void
    {
        $sharedName = 'DcrEdgeCase-DuplicateName-' . bin2hex(random_bytes(3));
        $firstBody = $this->registerConfidentialClient([
            'redirect_uris' => ['https://client.example/cb'],
            'client_name' => $sharedName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['e2e@test.example'],
            'scope' => 'openid',
        ]);
        $secondBody = $this->registerConfidentialClient([
            'redirect_uris' => ['https://client.example/cb'],
            'client_name' => $sharedName,
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['e2e@test.example'],
            'scope' => 'openid',
        ]);

        $this->assertIsString($firstBody['client_id']);
        $this->assertIsString($secondBody['client_id']);
        $this->assertNotSame(
            $firstBody['client_id'],
            $secondBody['client_id'],
            'Two registrations under the same client_name must produce distinct client_ids'
        );
        $this->assertSame($sharedName, $firstBody['client_name']);
        $this->assertSame($sharedName, $secondBody['client_name']);
    }

    /**
     * Punycode-encoded redirect_uri (xn--...) is a valid RFC 3986 URL
     * and must be accepted. Round-trips as-is through the response.
     */
    #[Test]
    public function testPunycodeRedirectUriIsAccepted(): void
    {
        $punycodeUri = 'https://xn--r8jz45g.jp/cb';
        $body = $this->registerConfidentialClient([
            'redirect_uris' => [$punycodeUri],
            'client_name' => 'DcrEdgeCase-Punycode-' . bin2hex(random_bytes(3)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['e2e@test.example'],
            'scope' => 'openid',
        ]);
        $this->assertIsArray($body['redirect_uris']);
        $this->assertSame(
            [$punycodeUri],
            $body['redirect_uris'],
            'Punycode redirect_uri must round-trip through DCR unmodified'
        );
    }

    /**
     * Raw IDN (unicode) redirect_uri is a policy choice. The validator
     * uses parse_url(), which accepts an IRI host. Whichever way the
     * server decides, the server must give a deterministic status code
     * and never 500. Probe for 2xx-or-4xx and assert the response shape
     * holds either way.
     */
    #[Test]
    public function testUnicodeIdnRedirectUriHasConsistentPolicy(): void
    {
        $idnUri = 'https://例え.jp/cb';
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [$idnUri],
                'client_name' => 'DcrEdgeCase-IDN-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(
            500,
            $status,
            'IDN redirect_uri must not 500 the server. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        if ($status === 200) {
            $this->assertArrayHasKey('client_id', $body);
            $this->assertIsString($body['client_id']);
            $this->clientIds[] = $body['client_id'];
            // RFC 7591 §3.2.1 — server MUST echo the stored metadata.
            // If the server accepts the IDN form, it must round-trip
            // the exact unicode value (no silent punycode conversion,
            // no silent trailing-slash normalization). A policy change
            // that silently rewrites the host would surface as a diff.
            $this->assertArrayHasKey('redirect_uris', $body);
            $this->assertSame(
                [$idnUri],
                $body['redirect_uris'],
                'Accepted IDN redirect_uri must round-trip without alteration'
            );
        } else {
            $this->assertGreaterThanOrEqual(400, $status, 'Non-success must be 4xx, not 2xx-non-200');
            $this->assertArrayHasKey('error', $body);
            $this->assertSame(
                'invalid_redirect_uri',
                $body['error'],
                'IDN rejection must use the RFC-6749 invalid_redirect_uri error code'
            );
        }
    }

    /**
     * A single enormous redirect_uri (10 KB) must be handled
     * deterministically: either accepted and the stored value
     * round-tripped exactly, or rejected with a 4xx. Either shape is
     * fine; a 500 or silent truncation is not.
     */
    #[Test]
    public function testHugeSingleRedirectUriHasConsistentPolicy(): void
    {
        // 10KB of path. A valid-looking URL shell so parse_url accepts
        // the authority — rejection (if any) must come from a size or
        // validator rule, not from a parse_url failure.
        $hugeUri = 'https://client.example/' . str_repeat('a', 10 * 1024);
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => [$hugeUri],
                'client_name' => 'DcrEdgeCase-HugeUri-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(
            500,
            $status,
            'A 10 KB redirect_uri must not 500 the server. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        if ($status === 200) {
            $this->assertArrayHasKey('client_id', $body);
            $this->assertIsString($body['client_id']);
            $this->clientIds[] = $body['client_id'];
            $this->assertArrayHasKey('redirect_uris', $body);
            $this->assertIsArray($body['redirect_uris']);
            // No silent truncation: if accepted, the stored value must
            // round-trip verbatim.
            $this->assertSame(
                [$hugeUri],
                $body['redirect_uris'],
                'Accepted large redirect_uri must round-trip without truncation'
            );
        } else {
            $this->assertGreaterThanOrEqual(400, $status);
            $this->assertArrayHasKey('error', $body);
            $this->assertSame(
                'invalid_redirect_uri',
                $body['error'],
                'Large-redirect_uri rejection must use the RFC-6749 invalid_redirect_uri code'
            );
        }
    }

    /**
     * Many well-formed redirect_uris in a single registration (100
     * entries). The server either accepts them all (and the response
     * echoes them back) or rejects cleanly; it must not 500 or
     * silently drop entries without an error.
     */
    #[Test]
    public function testManyRedirectUrisHaveConsistentPolicy(): void
    {
        $uris = [];
        for ($i = 0; $i < 100; $i++) {
            $uris[] = 'https://client.example/cb-' . $i;
        }
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => $uris,
                'client_name' => 'DcrEdgeCase-ManyUris-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(
            500,
            $status,
            '100 redirect_uris must not 500 the server. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        if ($status === 200) {
            $this->assertArrayHasKey('client_id', $body);
            $this->assertIsString($body['client_id']);
            $this->clientIds[] = $body['client_id'];
            $this->assertArrayHasKey('redirect_uris', $body);
            $this->assertIsArray($body['redirect_uris']);
            // If accepted, all URIs must round-trip — a silent truncation
            // would be the real bug (admin UI relies on the stored list).
            $this->assertSame(
                $uris,
                $body['redirect_uris'],
                'When many redirect_uris are accepted, every entry must round-trip'
            );
        } else {
            $this->assertGreaterThanOrEqual(400, $status);
            $this->assertArrayHasKey('error', $body);
        }
    }

    /**
     * Unknown scope values must be rejected with the RFC 6749 invalid_scope
     * error code — not silently accepted or dropped (the resource-server
     * downstream will then expect those scopes to apply).
     */
    #[Test]
    public function testMassiveUnknownScopeStringIsRejectedCleanly(): void
    {
        $scopes = ['openid'];
        for ($i = 0; $i < 200; $i++) {
            $scopes[] = 'bogus/Scope-' . $i . '.read';
        }
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'DcrEdgeCase-HugeScope-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => implode(' ', $scopes),
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(500, $status, 'Huge scope string must not 500. Body: ' . (string) $response->getBody());
        $this->assertGreaterThanOrEqual(400, $status, 'Unknown scopes must be rejected, not silently accepted');
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('error', $body);
        $this->assertSame(
            'invalid_scope',
            $body['error'],
            'An unrecognized scope must be reported via the invalid_scope error code'
        );
    }

    /**
     * An unsupported grant_types value (e.g. the obsolete urn:openid: grant
     * or a made-up string) must be rejected with invalid_client_metadata.
     * This is the RFC 7591 Section 2 contract for metadata validation.
     */
    #[Test]
    public function testUnsupportedGrantTypeIsRejected(): void
    {
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'DcrEdgeCase-BadGrant-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
                'grant_types' => ['bogus_grant'],
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(500, $status, 'Bad grant_types must not 500. Body: ' . (string) $response->getBody());
        $this->assertGreaterThanOrEqual(400, $status);
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_client_metadata',
            $body['error'] ?? null,
            'An unsupported grant_types value must be rejected with invalid_client_metadata'
        );
    }

    /**
     * A refresh_token-only grant_types list must be rejected — the client
     * also needs a grant that actually issues refresh tokens. This gates
     * ClientGrantTypePolicy::resolveRegistrationGrantTypes against a shape
     * that would otherwise pass each per-value check in isolation.
     */
    #[Test]
    public function testRefreshTokenOnlyGrantTypesIsRejected(): void
    {
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'DcrEdgeCase-RefreshOnly-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
                'grant_types' => ['refresh_token'],
            ],
        ]);
        $status = $response->getStatusCode();
        $this->assertLessThan(500, $status, 'refresh_token-only must not 500. Body: ' . (string) $response->getBody());
        $this->assertGreaterThanOrEqual(400, $status);
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_client_metadata',
            $body['error'] ?? null,
            'refresh_token alone must be rejected as invalid_client_metadata'
        );
    }

    /**
     * Shared helper — POST the body, assert 200, capture the client_id for
     * tearDown, and return the parsed response body.
     *
     * @param array<string, mixed> $overrides fields merged into the registration body
     * @return array<string, mixed>
     */
    private function registerConfidentialClient(array $overrides): array
    {
        $payload = array_merge([
            'application_type' => 'private',
        ], $overrides);
        $response = $this->http->post('/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'DCR should succeed. Body: ' . (string) $response->getBody()
        );
        $decoded = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($decoded);
        // Keep only string keys so the returned shape satisfies the
        // declared array<string, mixed> contract — a json_decode(true)
        // result is array<array-key, mixed> which PHPStan treats as a
        // superset.
        $body = [];
        foreach ($decoded as $key => $value) {
            if (is_string($key)) {
                $body[$key] = $value;
            }
        }
        $this->assertArrayHasKey('client_id', $body);
        $this->assertIsString($body['client_id']);
        $this->clientIds[] = $body['client_id'];
        return $body;
    }
}
