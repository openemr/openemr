<?php

/**
 * JWKS-related rejection paths in JWTClientAuthenticationService.
 *
 * Covers three defense-in-depth rejections that fire at JWT-assertion
 * validation time (not at DCR):
 *
 *   - line 289: client has neither jwks nor jwks_uri configured
 *   - line 326: persisted jwks_uri fails outbound-URL SSRF validation
 *   - line 430: fetching jwks_uri throws JWKValidator / connection errors
 *
 * The SSRF-at-DCR path is enforced separately by DCR validation on
 * registration; this test covers the runtime re-check that fires when
 * a stored jwks_uri has been mutated out-of-band or predates the DCR
 * SSRF check.
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
use GuzzleHttp\Cookie\CookieJar;
use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class JwksBoundaryNegativesTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        if (getenv('OPENEMR_ALLOW_OAUTH_HTTPS_SKIP') === '1') {
            $this->markTestSkipped('Skipping per OPENEMR_ALLOW_OAUTH_HTTPS_SKIP=1');
        }
        $probe = $this->buildClient()->get($this->baseUrl . '/');
        if ($probe->getHeaderLine('Server') === '') {
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }
    }

    protected function tearDown(): void
    {
        if ($this->clientId !== null) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$this->clientId]
            );
        }
    }

    #[Test]
    public function testJwtAssertionAgainstClientWithNoJwksIsRejected(): void
    {
        // Register a private_key_jwt client without a jwks or jwks_uri.
        // OpenEMR DCR allows this for non-system scopes; the rejection
        // happens at runtime in validateJWTClientAssertion (line 287-290).
        $http = $this->buildClient();
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'JwksBoundaryNegativesTest-nojwks-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // Also strip any client-secret so the JWT branch is taken at
        // /token (otherwise the traditional path would be viable).
        QueryUtils::sqlStatementThrowException(
            "UPDATE `oauth_clients` SET `jwks` = '', `jwks_uri` = '' WHERE `client_id` = ?",
            [$this->clientId]
        );

        // Use the test RSA keypair. The signed assertion is well-formed
        // but the server has no JWKS to verify it against.
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            $this->clientId,
        );

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => 'openid',
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'JWT auth against a client with no JWKS must return 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    #[Test]
    public function testJwtAssertionAgainstClientWithSsrfJwksUriIsRejected(): void
    {
        // Register a client with a valid jwks (DCR would reject an SSRF
        // jwks_uri outright), then mutate oauth_clients to insert an
        // SSRF-target jwks_uri. Locks in the defense-in-depth read-path
        // check at JWTClientAuthenticationService:304-327.
        $http = $this->buildClient();
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'JwksBoundaryNegativesTest-ssrf-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // Simulate an out-of-band mutation: clear jwks and set jwks_uri
        // to a loopback URL. The validateAndPin() check should reject
        // any private/loopback/link-local target.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `oauth_clients` SET `jwks` = '', `jwks_uri` = ? WHERE `client_id` = ?",
            ['http://127.0.0.1/jwks.json', $this->clientId]
        );

        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            $this->clientId,
        );

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => 'openid',
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'JWT auth against a client whose stored jwks_uri points at a '
                . 'loopback address must return 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    #[Test]
    public function testJwtAssertionAgainstUnreachableJwksUriIsRejected(): void
    {
        // Register a client, then set jwks_uri to a public-looking URL
        // that passes SSRF checks but doesn't resolve. The fetch step
        // at validateJWTClientAssertion:424-431 throws
        // JWKValidatorException / InvalidArgumentException which the
        // catch block re-throws as invalid_client.
        $http = $this->buildClient();
        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'JwksBoundaryNegativesTest-unreachable-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // .invalid TLD is reserved (RFC 2606) and guaranteed
        // non-resolvable, so the fetch fails at DNS lookup rather than
        // via a live-but-broken server.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `oauth_clients` SET `jwks` = '', `jwks_uri` = ? WHERE `client_id` = ?",
            ['https://jwks-does-not-resolve.invalid/jwks.json', $this->clientId]
        );

        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            $privateKey,
            $publicKey,
            $this->baseUrl . '/oauth2/default/token',
            $this->clientId,
        );

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => 'openid',
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'JWT auth against an unreachable jwks_uri must return 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null);
    }

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'cookies' => new CookieJar(),
            'timeout' => 15,
        ]);
    }
}
