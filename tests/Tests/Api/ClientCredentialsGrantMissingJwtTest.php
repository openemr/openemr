<?php

/**
 * Client-credentials grant without a JWT client assertion is rejected.
 *
 * CustomClientCredentialsGrant requires a signed JWT client_assertion —
 * shared-secret client authentication is not accepted on this grant.
 * The rejection happens in CustomClientCredentialsGrant::getClientCredentials()
 * (throws invalid_request "assertion type is not supported" when
 * hasJWTClientAssertion() is false); this test pins that end-to-end by
 * posting a client_credentials request that omits client_assertion and
 * asserting the endpoint returns a 4xx invalid_request/invalid_client
 * error. Both codes are OAuth2-spec conformant here — the test locks
 * in that the request is rejected without accepting the value in the
 * body as a shared-secret shortcut.
 *
 * The positive path for client_credentials is exercised via
 * BulkAPITestClient (Bulk FHIR export flow); the acceptance-tier
 * ApiSmokeJwtAssertionTest exercises the JWT-assertion variant of the
 * password/auth_code grants. Neither exercises the "grant_type=client_
 * credentials but no client_assertion" rejection path.
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
use OpenEMR\Common\Database\QueryUtils;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientCredentialsGrantMissingJwtTest extends TestCase
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
    public function testClientCredentialsGrantWithoutJwtAssertionReturns401(): void
    {
        $http = $this->buildClient();

        // Register a private-application client with a valid jwks. This
        // is the shape a real Bulk FHIR client would take — jwks is
        // required at DCR time for any system scope. The test then
        // omits client_assertion at /token, which is the specific
        // rejection path CustomClientCredentialsGrant enforces.
        $jwks = json_decode(
            (string) file_get_contents(__DIR__ . '/../data/Unit/Common/Auth/Grant/jwk-public-valid.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'ClientCredentialsGrantMissingJwtTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'system/Patient.read',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertArrayHasKey('client_id', $clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // Post to /token with grant_type=client_credentials and NO
        // client_assertion. The grant must reject — no fallback to
        // shared-secret auth is accepted on this grant. League returns
        // 400 invalid_request or 401 invalid_client depending on which
        // internal check fires first; both are OAuth2 spec-conformant
        // for this shape.
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'client_credentials grant with no client_assertion must return 400 or 401. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertContains(
            $body['error'] ?? null,
            ['invalid_request', 'invalid_client'],
            'client_credentials without client_assertion must return '
                . '{"error":"invalid_request"} or {"error":"invalid_client"}'
        );
    }

    #[Test]
    public function testClientCredentialsGrantWithClientSecretButNoJwtAssertionReturns401(): void
    {
        // Complementary case: even if the caller sends a client_secret
        // (the shape shared-secret grants use), the client_credentials
        // grant must still reject. This locks in that no shared-secret
        // fallback path exists for client_credentials — the grant is
        // JWT-only.
        $http = $this->buildClient();

        $jwks = json_decode(
            (string) file_get_contents(__DIR__ . '/../data/Unit/Common/Auth/Grant/jwk-public-valid.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'ClientCredentialsGrantMissingJwtTest-secret-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'system/Patient.read',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR registration should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertArrayHasKey('client_id', $clientData);
        $this->assertArrayHasKey('client_secret', $clientData);
        $this->assertIsString($clientData['client_id']);
        $this->assertIsString($clientData['client_secret']);
        $this->clientId = $clientData['client_id'];

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $this->clientId,
                'client_secret' => $clientData['client_secret'],
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'client_credentials grant with client_secret (no client_assertion) '
                . 'must still be rejected. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertContains(
            $body['error'] ?? null,
            ['invalid_request', 'invalid_client'],
            'client_credentials must reject shared-secret auth with '
                . '{"error":"invalid_request"} or {"error":"invalid_client"}'
        );
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
