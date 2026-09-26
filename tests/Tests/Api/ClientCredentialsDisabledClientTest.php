<?php

/**
 * Client-credentials grant + disabled client rejection.
 *
 * DisabledClientRejectionTest covers auth_code / password / refresh.
 * client_credentials rejects through a different code path — the
 * disabled check lives in JWTClientAuthenticationService (line 281)
 * rather than the grant class itself. This test locks in that path
 * end-to-end.
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

class ClientCredentialsDisabledClientTest extends TestCase
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
    public function testClientCredentialsGrantRejectsDisabledClient(): void
    {
        $http = $this->buildClient();

        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $jwks = json_decode((string) file_get_contents($keyLocation . 'jwk-public-valid.json'));
        $privateKey = InMemory::file($keyLocation . 'openemr-rsa384-private.key');
        $publicKey = InMemory::file($keyLocation . 'openemr-rsa384-public.pem');

        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'ClientCredentialsDisabledClientTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'private_key_jwt',
                'contacts' => ['e2e@test.example'],
                'scope' => 'system/Patient.read',
                'jwks' => $jwks,
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $clientData = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($clientData);
        $this->assertIsString($clientData['client_id']);
        $this->clientId = $clientData['client_id'];

        // Flip is_enabled directly in oauth_clients so the JWT auth
        // service disabled check (line 281) fires when the assertion is
        // presented.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 0 WHERE `client_id` = ?',
            [$this->clientId]
        );

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
                'scope' => 'system/Patient.read',
            ],
        ]);
        $this->assertSame(
            401,
            $response->getStatusCode(),
            'Disabled client on client_credentials must return 401. '
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
