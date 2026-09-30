<?php

/**
 * OAuth2 clients may use only the grant types they registered for.
 *
 * Before enforcement, a client registered for the authorization_code grant could call the
 * token endpoint with grant_type=password and a user's credentials, bypassing the consent
 * screen. These tests pin the registration contract and the token-endpoint rejection.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2026 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Api;

use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use Lcobucci\JWT\Signer\Key\InMemory;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tools\OAuth2\ClientCredentialsAssertionGenerator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ClientGrantTypeEnforcementTest extends TestCase
{
    private string $baseUrl;
    /** @var list<string> */
    private array $clientIds = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

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

        // Enable the password grant server-side so the request reaches the client check;
        // the point of the test is that the server has the grant enabled but this client
        // is not registered for it.
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
        $this->persistPasswordGrantEnabled();
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->clientIds as $clientId) {
                QueryUtils::sqlStatementThrowException('DELETE FROM `oauth_clients` WHERE `client_id` = ?', [$clientId]);
            }
            $this->restorePasswordGrantGlobal();
        } finally {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantSettingWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        }
    }

    #[Test]
    public function testAuthorizationCodeClientCannotUsePasswordGrant(): void
    {
        $http = $this->buildClient();
        $registration = $this->register($http, []);
        $this->assertSame(200, $registration['status'], 'DCR should succeed');
        $this->assertSame(['authorization_code'], $registration['body']['grant_types'] ?? null, 'omitted grant_types defaults to authorization_code (RFC 7591)');

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $registration['client_id'],
                'client_secret' => $registration['client_secret'],
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(400, $response->getStatusCode(), 'Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('unauthorized_client', $body['error'] ?? null);
    }

    #[Test]
    public function testUnauthenticatedClientStillGetsInvalidClient(): void
    {
        // Client authentication runs first, so a wrong secret cannot be used to probe which
        // grants a client is registered for (RFC 6749 5.2).
        $http = $this->buildClient();
        $registration = $this->register($http, []);
        $this->assertSame(200, $registration['status'], 'DCR should succeed');

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $registration['client_id'],
                'client_secret' => 'wrong-secret',
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_client', $body['error'] ?? null, 'Body: ' . (string) $response->getBody());
    }

    #[Test]
    public function testRegistrationCannotGrantItselfThePasswordGrant(): void
    {
        // Registration is open, so the password grant is allowed only by an administrator.
        // RFC 7591 3.2.1: the response reports the grant types actually stored.
        $http = $this->buildClient();
        $registration = $this->register($http, ['grant_types' => ['authorization_code', 'password', 'refresh_token']]);
        $this->assertSame(200, $registration['status']);
        $this->assertSame(['authorization_code', 'refresh_token'], $registration['body']['grant_types'] ?? null);

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $registration['client_id'],
                'client_secret' => $registration['client_secret'],
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('unauthorized_client', $body['error'] ?? null, 'Body: ' . (string) $response->getBody());
    }

    #[Test]
    public function testRegistrationRejectsClientCredentialsWithoutJwks(): void
    {
        $registration = $this->register($this->buildClient(), ['grant_types' => ['client_credentials'], 'scope' => 'system/Patient.read']);
        $this->assertSame(400, $registration['status']);
        $this->assertSame('invalid_client_metadata', $registration['body']['error'] ?? null);
    }

    #[Test]
    public function testRegistrationRejectsClientCredentialsWithoutSystemScopes(): void
    {
        $registration = $this->register($this->buildClient(), [
            'grant_types' => ['client_credentials'],
            'token_endpoint_auth_method' => 'private_key_jwt',
            'scope' => 'openid user/Patient.read',
            'jwks' => $this->testJwks(),
        ]);
        $this->assertSame(400, $registration['status']);
        $this->assertSame('invalid_client_metadata', $registration['body']['error'] ?? null);
    }

    #[Test]
    public function testClientCredentialsTokenCarriesOnlySystemScopes(): void
    {
        // client_credentials acts for no user, so user/ and patient/ scopes are dropped even
        // when the client registered them for another grant.
        $http = $this->buildClient();
        $registration = $this->register($http, [
            'grant_types' => ['authorization_code', 'client_credentials'],
            'token_endpoint_auth_method' => 'private_key_jwt',
            'scope' => 'system/Patient.read user/Patient.read patient/Patient.read',
            'jwks' => $this->testJwks(),
        ]);
        $this->assertSame(200, $registration['status'], 'Body: ' . json_encode($registration['body']));
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `is_enabled` = 1 WHERE `client_id` = ?',
            [$registration['client_id']]
        );

        $keyLocation = __DIR__ . '/../data/Unit/Common/Auth/Grant/';
        $assertion = ClientCredentialsAssertionGenerator::generateAssertion(
            InMemory::file($keyLocation . 'openemr-rsa384-private.key'),
            InMemory::file($keyLocation . 'openemr-rsa384-public.pem'),
            $this->baseUrl . '/oauth2/default/token',
            $registration['client_id'],
        );
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $registration['client_id'],
                'client_assertion_type' => 'urn:ietf:params:oauth:client-assertion-type:jwt-bearer',
                'client_assertion' => $assertion,
                'scope' => 'system/Patient.read user/Patient.read patient/Patient.read',
            ],
        ]);
        $this->assertSame(200, $response->getStatusCode(), 'Body: ' . (string) $response->getBody());
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $accessToken = $body['access_token'] ?? null;
        $this->assertIsString($accessToken);
        $this->assertSame(['system/Patient.read'], $this->accessTokenScopes($accessToken));
    }

    #[Test]
    public function testRegistrationRejectsUnsupportedGrantType(): void
    {
        $registration = $this->register($this->buildClient(), ['grant_types' => ['implicit']]);
        $this->assertSame(400, $registration['status']);
        $this->assertSame('invalid_client_metadata', $registration['body']['error'] ?? null);
    }

    /**
     * @param array<string, mixed> $extra
     * @return array{status: int, body: array<array-key, mixed>, client_id: string, client_secret: string}
     */
    private function register(Client $http, array $extra): array
    {
        $response = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => array_merge([
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'ClientGrantTypeEnforcementTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr',
            ], $extra),
        ]);
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body, 'Body: ' . (string) $response->getBody());
        $clientId = $body['client_id'] ?? '';
        $clientSecret = $body['client_secret'] ?? '';
        $this->assertIsString($clientId);
        $this->assertIsString($clientSecret);
        if ($clientId !== '') {
            $this->clientIds[] = $clientId;
        }
        return ['status' => $response->getStatusCode(), 'body' => $body, 'client_id' => $clientId, 'client_secret' => $clientSecret];
    }

    private function testJwks(): mixed
    {
        $jwks = file_get_contents(__DIR__ . '/../data/Unit/Common/Auth/Grant/jwk-public-valid.json');
        $this->assertIsString($jwks);
        return json_decode($jwks);
    }

    /**
     * @return list<string>
     */
    private function accessTokenScopes(string $accessToken): array
    {
        $parts = explode('.', $accessToken);
        $this->assertCount(3, $parts, 'access token should be a JWT');
        $payload = base64_decode(strtr($parts[1], '-_', '+/'), true);
        $this->assertIsString($payload);
        $claims = json_decode($payload, true);
        $this->assertIsArray($claims);
        $scopes = $claims['scopes'] ?? null;
        $this->assertIsArray($scopes);
        $list = array_values(array_filter($scopes, is_string(...)));
        sort($list);
        return $list;
    }

    private function persistPasswordGrantEnabled(): void
    {
        $current = QueryUtils::querySingleRow('SELECT gl_value FROM `globals` WHERE gl_name = ?', ['oauth_password_grant']);
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalPasswordGrantGlobalRow = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException('UPDATE `globals` SET gl_value = ? WHERE gl_name = ?', ['3', 'oauth_password_grant']);
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                ['oauth_password_grant', '3']
            );
            $this->passwordGrantGlobalRowWasInserted = true;
        }
    }

    private function restorePasswordGrantGlobal(): void
    {
        if ($this->passwordGrantGlobalRowWasInserted) {
            QueryUtils::sqlStatementThrowException('DELETE FROM `globals` WHERE gl_name = ?', ['oauth_password_grant']);
        } elseif ($this->originalPasswordGrantGlobalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$this->originalPasswordGrantGlobalRow, 'oauth_password_grant']
            );
        }
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
