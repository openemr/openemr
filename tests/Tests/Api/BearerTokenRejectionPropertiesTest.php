<?php

/**
 * Regression gates for canonical bearer-token rejection properties.
 *
 * These three properties are each a one-line WHERE clause away from
 * silently flipping on. The audit identified them as having zero
 * api-level coverage; this file is the gate.
 *
 * 1. Expired tokens — once past expiry, bearer validation rejects.
 * 2. Revoked tokens — once revoked (admin, password-change, logout),
 *    bearer validation rejects even if not yet expired.
 * 3. Scope insufficiency — a token issued with only
 *    `user/Observation.read` must not succeed on a FHIR endpoint that
 *    requires a write scope.
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

class BearerTokenRejectionPropertiesTest extends TestCase
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
                'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                [$clientId]
            );
        }
    }

    #[Test]
    public function testExpiredAccessTokenIsRejected(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'ExpiredBearer');
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'openid api:oemr user/patient.read');
        $this->assertIsString($tokens['access_token']);
        $this->assertNotSame('', $tokens['access_token']);

        // Expire the token via DB UPDATE. The DB column is `expiry` and the
        // JWT also carries an exp claim — the DB check is enforced regardless
        // of what the JWT claims, so flipping it to a past timestamp exercises
        // the full bearer validation path including the revocation lookup.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `api_token` SET `expiry` = ? WHERE `client_id` = ?",
            [date('Y-m-d H:i:s', time() - 3600), $clientId]
        );

        $resp = $http->get($this->baseUrl . '/apis/default/api/patient', [
            'headers' => ['Authorization' => 'Bearer ' . $tokens['access_token']],
        ]);
        $this->assertSame(
            401,
            $resp->getStatusCode(),
            'Expired access token must be rejected with 401. Body: ' . (string) $resp->getBody()
        );
    }

    #[Test]
    public function testRevokedAccessTokenIsRejected(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'RevokedBearer');
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'openid api:oemr user/patient.read');
        $this->assertIsString($tokens['access_token']);

        // Flip revoked=1 the same way password-change / admin-revoke do.
        QueryUtils::sqlStatementThrowException(
            "UPDATE `api_token` SET `revoked` = 1 WHERE `client_id` = ?",
            [$clientId]
        );

        $resp = $http->get($this->baseUrl . '/apis/default/api/patient', [
            'headers' => ['Authorization' => 'Bearer ' . $tokens['access_token']],
        ]);
        $this->assertSame(
            401,
            $resp->getStatusCode(),
            'Revoked access token must be rejected with 401 even if its exp is still in the future. Body: '
                . (string) $resp->getBody()
        );
    }

    #[Test]
    public function testTokenWithReadOnlyScopeCannotWriteViaFhirEndpoint(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerPasswordGrantClient($http, 'ReadOnlyFhir');
        // Request only user/Patient.read — explicitly NOT user/Patient.write.
        $tokens = $this->passwordGrant($http, $clientId, $clientSecret, 'user/Patient.read');
        $this->assertIsString($tokens['access_token']);

        // Attempt a FHIR Patient POST (create), which requires user/Patient.write.
        $resp = $http->post($this->baseUrl . '/apis/default/fhir/Patient', [
            'headers' => [
                'Authorization' => 'Bearer ' . $tokens['access_token'],
                'Content-Type' => 'application/fhir+json',
            ],
            'body' => (string) json_encode([
                'resourceType' => 'Patient',
                'name' => [['family' => 'ScopeTestDoNotPersist']],
            ]),
        ]);
        $this->assertGreaterThanOrEqual(
            400,
            $resp->getStatusCode(),
            'POST to /fhir/Patient with a read-only scope must not succeed. Status: '
                . $resp->getStatusCode() . ' body: ' . (string) $resp->getBody()
        );
        $this->assertNotSame(
            201,
            $resp->getStatusCode(),
            'POST to /fhir/Patient with a read-only scope must not create a Patient. Status: '
                . $resp->getStatusCode()
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
                'client_name' => 'BearerTokenRejectionPropertiesTest-' . $nameTag . '-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr api:fhir offline_access user/Patient.read user/Patient.write user/patient.read',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        // The requested scope set includes user/* which triggers manual
        // approval under the default oauth_app_manual_approval policy and
        // disables the client on insert. Flip grant_types and is_enabled
        // together the way an administrator would via the admin UI.
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $data['client_id']]
        );
        $this->registeredClientIds[] = $data['client_id'];
        return [$data['client_id'], $data['client_secret']];
    }

    /**
     * @return array<string, mixed>
     */
    private function passwordGrant(Client $http, string $clientId, string $clientSecret, string $scope): array
    {
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => $scope,
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

    private function buildClient(): Client
    {
        return new Client([
            'verify' => false,
            'http_errors' => false,
            'timeout' => 15,
        ]);
    }
}
