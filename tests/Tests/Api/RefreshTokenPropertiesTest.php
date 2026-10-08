<?php

/**
 * Regression gates for canonical refresh_token properties on the token
 * endpoint. Covers two distinct properties previously not pinned by any
 * api-level test:
 *
 * - Single-use enforcement (RFC 6749 §10.4): after a refresh_token has
 *   been successfully exchanged, presenting the same refresh_token a
 *   second time must be rejected. Without this, a leaked refresh_token
 *   stays usable for the natural refresh lifetime (weeks/months).
 *
 * - Client binding (RFC 6749 §6): a refresh_token issued to client A
 *   must be rejected when presented authenticating as client B. Without
 *   this, a leaked refresh_token could be redeemed by any other
 *   registered client.
 *
 * Both properties are tested via the password grant since it is the
 * simplest grant to drive end-to-end from a test (no browser or
 * consent-page flow required).
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
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class RefreshTokenPropertiesTest extends TestCase
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
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
        }
    }

    #[Test]
    public function testRefreshTokenCannotBeReusedAfterSuccessfulExchange(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'RefreshSingleUseA');

        $tokens = $this->passwordGrant($http, $clientId, $clientSecret);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertIsString($tokens['refresh_token']);
        $originalRefresh = $tokens['refresh_token'];

        // First refresh — must succeed and should return a different
        // access_token value (otherwise refresh is a no-op).
        $firstRefresh = $this->http($http, 'POST', '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $originalRefresh,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertSame(200, $firstRefresh['status'], 'First refresh should succeed');

        // Second refresh with the SAME original token — must be rejected.
        $secondRefresh = $this->http($http, 'POST', '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $originalRefresh,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertNotSame(
            200,
            $secondRefresh['status'],
            'Reusing a refresh_token that has already been exchanged must be rejected (RFC 6749 §10.4). '
            . 'Response body: ' . $secondRefresh['body']
        );
    }

    #[Test]
    public function testRefreshTokenIssuedToClientARejectedWhenPresentedAsClientB(): void
    {
        $http = $this->buildClient();
        [$clientIdA, $clientSecretA] = $this->registerPasswordGrantClient($http, 'RefreshBoundA');
        [$clientIdB, $clientSecretB] = $this->registerPasswordGrantClient($http, 'RefreshBoundB');

        $tokens = $this->passwordGrant($http, $clientIdA, $clientSecretA);
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertIsString($tokens['refresh_token']);
        $refreshFromA = $tokens['refresh_token'];

        // Present client A's refresh token while authenticating as client B.
        // Per RFC 6749 §6 the server must reject this: refresh tokens are
        // bound to the client to which they were issued.
        $response = $this->http($http, 'POST', '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshFromA,
                'client_id' => $clientIdB,
                'client_secret' => $clientSecretB,
            ],
        ]);
        $this->assertNotSame(
            200,
            $response['status'],
            'A refresh_token issued to client A must not be redeemable by client B. '
            . 'Response body: ' . $response['body']
        );
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function registerPasswordGrantClient(Client $http, string $nameTag): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'RefreshTokenPropertiesTest-' . $nameTag . '-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr offline_access',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ? WHERE `client_id` = ?',
            ['password', $data['client_id']]
        );
        $this->registeredClientIds[] = $data['client_id'];
        return [$data['client_id'], $data['client_secret']];
    }

    /**
     * @return array<string, mixed>
     */
    private function passwordGrant(Client $http, string $clientId, string $clientSecret): array
    {
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr offline_access',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Password grant should succeed — body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        /** @var array<string, mixed> $body */
        return $body;
    }

    /**
     * @param array<string, mixed> $options
     * @return array{status: int, body: string}
     */
    private function http(Client $http, string $method, string $path, array $options = []): array
    {
        $response = $http->request($method, $this->baseUrl . $path, $options);
        return ['status' => $response->getStatusCode(), 'body' => (string) $response->getBody()];
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
