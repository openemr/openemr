<?php

/**
 * OAuth2 attack-scenario regression gate suite.
 *
 * Each test method runs ONE deny-path attack and asserts the server
 * rejects it with the expected shape (status + error field, or
 * header-absence). If any one of these ever starts succeeding, a
 * regression has silently landed. The suite's job is to fail CI
 * before that regression ships.
 *
 * Covers DCR malformation, token-endpoint client-auth bypass, password
 * grant deny-paths, refresh-token misuse, bearer-token presentation
 * errors, introspection input shapes, CORS origin enforcement, and
 * information-disclosure invariants.
 *
 * Each test is self-contained: DCR + any required state is created and
 * torn down per test. No shared mutable state between tests.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use DateTimeImmutable;
use GuzzleHttp\Client;
use Lcobucci\JWT\Encoding\ChainedFormatter;
use Lcobucci\JWT\Encoding\JoseEncoder;
use Lcobucci\JWT\Signer\Key\InMemory;
use Lcobucci\JWT\Signer\Rsa\Sha256 as RsaSha256Signer;
use Lcobucci\JWT\Token\Builder as JwtTokenBuilder;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-type AttackResponse array{status: int, body: string, headers: array<array<string>>}
 */
class OAuth2AttackScenarioTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $registeredClientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';
        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            if (getenv('CI') !== false) {
                self::fail('OAuth flow requires a real webserver (Apache/nginx) — hard failure in CI');
            }
            $this->markTestSkipped('OAuth flow requires a real webserver');
        }
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
    }

    protected function tearDown(): void
    {
        $globals = OEGlobalsBag::getInstance();
        if ($this->originalPasswordGrantSettingWasSet) {
            $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
        } else {
            $globals->remove('oauth_password_grant');
            unset($GLOBALS['oauth_password_grant']);
        }
        foreach ($this->registeredClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_token` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `api_refresh_token` WHERE `client_id` = ?',
                [$clientId]
            );
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
        }
    }

    // ----- DCR attack scenarios -----

    #[Test]
    public function testDcrRejectsNonUrlRedirectUri(): void
    {
        $resp = $this->dcr(['redirect_uris' => ['not-a-url']]);
        $this->assertRejected($resp, 'DCR must reject a redirect_uri that is not a URL');
    }

    #[Test]
    public function testDcrRejectsEmptyRedirectUrisArray(): void
    {
        $resp = $this->dcr(['redirect_uris' => []]);
        $this->assertRejected($resp, 'DCR must reject an empty redirect_uris array');
    }

    #[Test]
    public function testDcrRejectsRelativeRedirectUri(): void
    {
        $resp = $this->dcr(['redirect_uris' => ['/relative/path']]);
        $this->assertRejected($resp, 'DCR must reject a relative redirect_uri');
    }

    #[Test]
    public function testDcrRejectsUnsupportedTokenEndpointAuthMethod(): void
    {
        $resp = $this->dcr(['token_endpoint_auth_method' => 'telepathic_hum']);
        $this->assertRejected($resp, 'DCR must reject an unsupported token_endpoint_auth_method value');
    }

    // ----- Token endpoint — client-auth attack scenarios -----

    #[Test]
    public function testTokenEndpointRejectsUnregisteredClient(): void
    {
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => 'nope-this-client-does-not-exist-' . bin2hex(random_bytes(4)),
            'client_secret' => 'bogus',
        ]);
        $this->assertRejected($resp, 'Token endpoint must reject an unregistered client_id');
    }

    #[Test]
    public function testTokenEndpointRejectsWrongClientSecret(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongSecret');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec . 'tamper',
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Token endpoint must reject a wrong client_secret');
    }

    #[Test]
    public function testClientCredentialsRejectsAlgNoneAssertion(): void
    {
        [$cid, $_csec] = $this->dcrConfidentialPasswordGrantClient('AlgNoneAssertion');
        $header = $this->base64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->base64url((string) json_encode([
            'iss' => $cid,
            'sub' => $cid,
            'aud' => $this->baseUrl . '/oauth2/default/token',
            'exp' => time() + 60,
            'jti' => bin2hex(random_bytes(16)),
        ]));
        $forged = $header . '.' . $payload . '.anything';
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => $cid,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $forged,
            'scope' => 'api:oemr',
        ]);
        $this->assertRejected($resp, 'client_credentials must reject an alg=none JWT client assertion');
    }

    #[Test]
    public function testClientCredentialsRejectsAssertionSignedByForeignKey(): void
    {
        [$cid, $_csec] = $this->dcrConfidentialPasswordGrantClient('ForeignKeyAssertion');
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $foreignPrivateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $signer = new RsaSha256Signer();
        $assertion = (new JwtTokenBuilder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy($cid)
            ->relatedTo($cid)
            ->permittedFor($this->baseUrl . '/oauth2/default/token')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->modify('+60 seconds'))
            ->getToken($signer, $foreignPrivateKey)
            ->toString();
        $resp = $this->postTokenRaw([
            'grant_type' => 'client_credentials',
            'client_id' => $cid,
            'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
            'client_assertion' => $assertion,
            'scope' => 'api:oemr',
        ]);
        $this->assertRejected($resp, 'client_credentials must reject a JWT client assertion signed by a key the client did not register');
    }

    // ----- Token endpoint — password grant deny-paths -----

    #[Test]
    public function testPasswordGrantRejectsWrongPassword(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongPassword');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'admin',
            'password' => 'definitely-not-the-password',
        ]);
        $this->assertRejected($resp, 'Password grant must reject a wrong user password');
    }

    #[Test]
    public function testPasswordGrantRejectsUnknownUser(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('UnknownUser');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:oemr',
            'user_role' => 'users',
            'username' => 'this-user-does-not-exist-' . bin2hex(random_bytes(4)),
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Password grant must reject an unknown username');
    }

    #[Test]
    public function testPasswordGrantRejectsWrongUserRole(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('WrongUserRole');
        $resp = $this->postTokenRaw([
            'grant_type' => 'password',
            'client_id' => $cid,
            'client_secret' => $csec,
            'scope' => 'openid api:port',
            'user_role' => 'patient',
            'username' => 'admin',
            'password' => 'pass',
        ]);
        $this->assertRejected($resp, 'Password grant must reject when user_role (patient) does not match the registered user (staff admin)');
    }

    // ----- Refresh-token deny-paths -----

    #[Test]
    public function testRefreshWithMadeUpTokenIsRejected(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('MadeUpRefresh');
        $resp = $this->postTokenRaw([
            'grant_type' => 'refresh_token',
            'refresh_token' => 'completely-fabricated-token-' . bin2hex(random_bytes(16)),
            'client_id' => $cid,
            'client_secret' => $csec,
        ]);
        $this->assertRejected($resp, 'Refresh token endpoint must reject a totally made-up refresh_token value');
    }

    // ----- Bearer-token presentation attacks (on an authenticated FHIR endpoint) -----

    #[Test]
    public function testFhirRejectsAuthorizationBasicScheme(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Basic ' . base64_encode('a:b')]);
        $this->assertSame(401, $resp['status'], 'Authorization: Basic must not satisfy a Bearer-protected endpoint');
    }

    #[Test]
    public function testFhirRejectsBearerAlgNoneJwt(): void
    {
        $header = $this->base64url('{"alg":"none","typ":"JWT"}');
        $payload = $this->base64url((string) json_encode([
            'sub' => 'admin',
            'exp' => time() + 3600,
            'scopes' => ['api:oemr', 'user/patient.read'],
        ]));
        $forged = $header . '.' . $payload . '.anything';
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer ' . $forged]);
        $this->assertSame(401, $resp['status'], 'Bearer with alg=none JWT must be rejected');
    }

    #[Test]
    public function testFhirRejectsBearerSignedByForeignKey(): void
    {
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $foreignPrivateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $signer = new RsaSha256Signer();
        $forged = (new JwtTokenBuilder(new JoseEncoder(), ChainedFormatter::default()))
            ->issuedBy('http://attacker.example/oauth2/default')
            ->relatedTo('admin')
            ->permittedFor('attacker-client-id')
            ->identifiedBy(bin2hex(random_bytes(16)))
            ->issuedAt(new DateTimeImmutable())
            ->expiresAt((new DateTimeImmutable())->modify('+1 hour'))
            ->withClaim('scopes', ['api:oemr', 'user/patient.read'])
            ->getToken($signer, $foreignPrivateKey)
            ->toString();
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer ' . $forged]);
        $this->assertSame(401, $resp['status'], 'Bearer JWT signed by a key the server does not trust must be rejected');
    }

    #[Test]
    public function testFhirRejectsBearerMalformedJwt(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => 'Bearer not.a.jwt']);
        $this->assertSame(401, $resp['status'], 'Bearer with a malformed JWT must be rejected');
    }

    #[Test]
    public function testFhirRejectsEmptyAuthorizationHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', ['Authorization' => '']);
        $this->assertSame(401, $resp['status'], 'Bearer with an empty Authorization header value must be rejected');
    }

    #[Test]
    public function testFhirRejectsMissingAuthorizationHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/patient', []);
        $this->assertSame(401, $resp['status'], 'Request to an auth-gated endpoint with no Authorization header must be rejected');
    }

    // ----- Introspection input-shape attacks -----

    #[Test]
    public function testIntrospectionTreatsMadeUpTokenAsInactive(): void
    {
        [$cid, $csec] = $this->dcrConfidentialPasswordGrantClient('IntrospectMadeUp');
        $resp = $this->rawPostForm('/oauth2/default/introspect', [
            'token' => 'completely-fabricated-value-' . bin2hex(random_bytes(16)),
            'client_id' => $cid,
            'client_secret' => $csec,
        ]);
        $this->assertSame(200, $resp['status'], 'Introspection with a made-up token should return 200 per RFC 7662');
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertFalse($body['active'] ?? null, 'Introspection must report active:false for an unknown token');
    }

    #[Test]
    public function testIntrospectionWithoutClientIdReturnsInactive(): void
    {
        $resp = $this->rawPostForm('/oauth2/default/introspect', [
            'token' => 'anything',
        ]);
        $this->assertSame(200, $resp['status'], 'Introspection without client_id should still return 200');
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $this->assertFalse($body['active'] ?? null, 'Introspection without client_id must report active:false');
    }

    // ----- CORS enforcement -----

    #[Test]
    public function testCorsPreflightFromDisallowedOriginEmitsNoAllowOriginHeader(): void
    {
        $resp = $this->rawOptions('/apis/default/fhir/Patient', [
            'Origin' => 'https://attacker.example',
            'Access-Control-Request-Method' => 'POST',
        ]);
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Preflight from a disallowed origin must not reflect the Origin into Access-Control-Allow-Origin. Received headers: '
            . json_encode(array_keys($resp['headers']))
        );
    }

    #[Test]
    public function testCorsResponseFromDisallowedOriginEmitsNoAllowOriginHeader(): void
    {
        $resp = $this->rawGet('/apis/default/api/version', ['Origin' => 'https://attacker.example']);
        $this->assertArrayNotHasKey(
            'access-control-allow-origin',
            array_change_key_case($resp['headers']),
            'Response from a disallowed origin must not reflect the Origin into Access-Control-Allow-Origin. Received headers: '
            . json_encode(array_keys($resp['headers']))
        );
    }

    // ----- Information-disclosure invariants -----

    #[Test]
    public function testDcrResponseContainsNoPrivateKeyMaterial(): void
    {
        [$cid, $csec, $raw] = $this->dcrFull('NoPrivateKeyLeak');
        $this->assertStringNotContainsStringIgnoringCase('BEGIN PRIVATE KEY', $raw);
        $this->assertStringNotContainsStringIgnoringCase('BEGIN RSA PRIVATE KEY', $raw);
        $this->assertStringNotContainsStringIgnoringCase('BEGIN EC PRIVATE KEY', $raw);
        // Negative control: the client_secret value is in the response (so this
        // test is not meaningless) but is itself not a private key.
        $this->assertStringContainsString($csec, $raw);
    }

    #[Test]
    public function testDiscoveryEndpointAdvertisesOnlyPublicEndpoints(): void
    {
        $resp = $this->rawGet('/oauth2/default/.well-known/openid-configuration', []);
        $this->assertSame(200, $resp['status']);
        $body = json_decode($resp['body'], true);
        $this->assertIsArray($body);
        $flat = (string) json_encode($body);
        // These are server-internal paths that must not appear in discovery.
        foreach (['/interface/', '/admin/', '/library/', '/sites/default/documents/'] as $forbiddenFragment) {
            $this->assertStringNotContainsString(
                $forbiddenFragment,
                $flat,
                "Discovery must not advertise an internal path (`$forbiddenFragment`)"
            );
        }
    }

    #[Test]
    public function testJwkEndpointPublishesOnlyPublicKeys(): void
    {
        $resp = $this->rawGet('/oauth2/default/jwk', []);
        $this->assertSame(200, $resp['status']);
        $decoded = json_decode($resp['body'], true);
        $this->assertIsArray($decoded, 'JWK response must be valid JSON');
        $this->assertArrayHasKey('keys', $decoded);
        $this->assertIsArray($decoded['keys']);
        // RFC 7518 §6 defines these as the private fields for RSA and EC keys.
        // Checking the decoded structure (rather than a substring match on the
        // body) catches whitespace variants like `"d" :` and private fields
        // whose names happen to collide with substrings in other field values.
        $privateFields = ['d', 'p', 'q', 'dp', 'dq', 'qi'];
        foreach ($decoded['keys'] as $index => $key) {
            $this->assertIsArray($key, "JWK entry $index must be an object");
            foreach ($privateFields as $privateField) {
                $this->assertArrayNotHasKey(
                    $privateField,
                    $key,
                    "JWK entry $index must not expose private key field `$privateField`"
                );
            }
        }
        $this->assertStringNotContainsStringIgnoringCase('BEGIN PRIVATE KEY', $resp['body']);
    }

    #[Test]
    public function testTokenEndpointErrorForMissingGrantTypeDoesNotLeakStack(): void
    {
        $resp = $this->rawPostForm('/oauth2/default/token', [
            'client_id' => 'anything',
        ]);
        $this->assertGreaterThanOrEqual(
            400,
            $resp['status'],
            'Token endpoint must reject a request with no grant_type'
        );
        $this->assertLessThan(
            500,
            $resp['status'],
            'Rejection must be a client-error 4xx, not a server failure that happens to satisfy the "not 200" check'
        );
        $this->assertStringNotContainsString(
            '/vendor/',
            $resp['body'],
            'Token endpoint 4xx responses must not expose vendor stack paths'
        );
        $this->assertStringNotContainsString(
            '/src/RestControllers/',
            $resp['body'],
            'Token endpoint 4xx responses must not expose server source paths'
        );
    }

    // ----- Shared helpers -----

    /**
     * @param array<string, mixed> $overrides
     * @return AttackResponse
     */
    private function dcr(array $overrides = []): array
    {
        $payload = array_merge([
            'application_type' => 'private',
            'redirect_uris' => ['https://attacker-regression.example/cb'],
            'client_name' => 'OAuth2AttackScenarioTest-' . bin2hex(random_bytes(3)),
            'token_endpoint_auth_method' => 'client_secret_post',
            'contacts' => ['attack-regression@test.example'],
            'scope' => 'openid',
        ], $overrides);
        $http = $this->buildClient();
        $response = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => $payload,
        ]);
        $body = (string) $response->getBody();
        $data = json_decode($body, true);
        if (is_array($data) && isset($data['client_id']) && is_string($data['client_id'])) {
            $this->registeredClientIds[] = $data['client_id'];
        }
        return [
            'status' => $response->getStatusCode(),
            'body' => $body,
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * DCR + enable password grant + fix is_enabled=1 (admin-UI equivalent).
     *
     * @return array{0: string, 1: string}
     */
    private function dcrConfidentialPasswordGrantClient(string $tag): array
    {
        [$cid, $csec, $_body] = $this->dcrFull($tag);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $cid]
        );
        return [$cid, $csec];
    }

    /**
     * @return array{0: string, 1: string, 2: string}
     */
    private function dcrFull(string $tag): array
    {
        $resp = $this->dcr([
            'client_name' => 'OAuth2AttackScenarioTest-' . $tag . '-' . bin2hex(random_bytes(3)),
            'scope' => 'openid api:oemr offline_access',
        ]);
        $this->assertSame(200, $resp['status'], 'DCR for the attack-suite fixture must succeed. Body: ' . $resp['body']);
        $data = json_decode($resp['body'], true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id'] ?? null);
        $this->assertIsString($data['client_secret'] ?? null);
        return [$data['client_id'], $data['client_secret'], $resp['body']];
    }

    /**
     * @param array<string, string> $form
     * @return AttackResponse
     */
    private function postTokenRaw(array $form): array
    {
        return $this->rawPostForm('/oauth2/default/token', $form);
    }

    /**
     * @param array<string, string> $form
     * @return AttackResponse
     */
    private function rawPostForm(string $path, array $form): array
    {
        $response = $this->buildClient()->post($this->baseUrl . $path, [
            'form_params' => $form,
        ]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return AttackResponse
     */
    private function rawGet(string $path, array $headers): array
    {
        $response = $this->buildClient()->get($this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * @param array<string, string> $headers
     * @return AttackResponse
     */
    private function rawOptions(string $path, array $headers): array
    {
        $response = $this->buildClient()->request('OPTIONS', $this->baseUrl . $path, ['headers' => $headers]);
        return [
            'status' => $response->getStatusCode(),
            'body' => (string) $response->getBody(),
            'headers' => $response->getHeaders(),
        ];
    }

    /**
     * Asserts a response has an OAuth2 rejection shape: 4xx + JSON body
     * with an `error` field. Both halves matter: a 404 would also pass
     * `assertNotSame(200, …)` but masks a broken endpoint.
     *
     * @param AttackResponse $response
     */
    private function assertRejected(array $response, string $context): void
    {
        $this->assertGreaterThanOrEqual(400, $response['status'], $context . ' — status ' . $response['status'] . ' body: ' . $response['body']);
        $this->assertLessThan(500, $response['status'], $context . ' — status ' . $response['status'] . ' body: ' . $response['body']);
        $body = json_decode($response['body'], true);
        $this->assertIsArray($body, $context . ' — response body is not JSON: ' . $response['body']);
        $this->assertArrayHasKey('error', $body, $context . ' — response body has no `error` field: ' . $response['body']);
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }
}
