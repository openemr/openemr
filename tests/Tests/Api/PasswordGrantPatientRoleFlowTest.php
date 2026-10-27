<?php

/**
 * Password grant with user_role=patient end-to-end flow.
 *
 * The existing password-grant api tests all use user_role=users with
 * the admin fixture. The patient-role path through
 * CustomPasswordGrant::validateUser is covered only at the service
 * layer (PasswordGrantHardeningTest exercises AuthUtils directly) and
 * for a missing-parameter case (PasswordGrantMissingParametersTest).
 * No api-level test currently proves that a correctly-presented
 * patient-portal credential at /oauth2/{site}/token with
 * user_role=patient issues a token.
 *
 * This file locks that path: install a portal-enabled patient fixture,
 * present it to the token endpoint, assert a token is minted; and
 * assert the symmetric deny-paths (wrong email, staff creds posing as
 * a patient).
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
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class PasswordGrantPatientRoleFlowTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;
    /** @var array{pid: int, pubpid: non-falsy-string, portal_username: string, portal_login_username: string, plain_password: string, email: string}|null */
    private ?array $patientFixture = null;
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    private PortalPatientFixtureManager $portalFixtures;

    protected function setUp(): void
    {
        $this->baseUrl = getenv('OPENEMR_BASE_URL_API', true) ?: 'https://localhost';

        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');

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
        $globals->set('oauth_password_grant', 3);

        $this->portalFixtures = new PortalPatientFixtureManager();
    }

    protected function tearDown(): void
    {
        try {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$this->clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `api_token` WHERE `client_id` = ?',
                    [$this->clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
            if ($this->patientFixture !== null) {
                $this->portalFixtures->removePortalPatientFixtures();
            }
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
    public function testPasswordGrantIssuesTokenForValidPortalPatientCredentials(): void
    {
        // Happy path: a portal-enabled patient fixture presents its
        // username, password, email, and user_role=patient. The token
        // endpoint must issue an access token.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialPatientClient($http);
        $fixture = $this->installPortalFixture();

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:port patient/Patient.read',
                'user_role' => 'patient',
                'username' => $fixture['portal_login_username'],
                'password' => $fixture['plain_password'],
                'email' => $fixture['email'],
            ],
        ]);

        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Password grant with user_role=patient + valid portal creds must issue a token. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertIsString(
            $body['access_token'] ?? null,
            'Response must carry a non-empty access_token'
        );
        $this->assertNotSame('', $body['access_token'], 'access_token must not be an empty string');
        $this->assertSame('Bearer', $body['token_type'] ?? null);
    }

    #[Test]
    public function testPasswordGrantRejectsPatientRoleWithWrongEmail(): void
    {
        // Deny path: correct portal username + password, but a wrong
        // email. The patient-role authentication path requires all
        // three (username, password, email) to match the fixture.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialPatientClient($http);
        $fixture = $this->installPortalFixture();

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:port patient/Patient.read',
                'user_role' => 'patient',
                'username' => $fixture['portal_login_username'],
                'password' => $fixture['plain_password'],
                'email' => 'not-the-registered-email@example.invalid',
            ],
        ]);

        $this->assertGreaterThanOrEqual(
            400,
            $response->getStatusCode(),
            'Patient-role password grant with a wrong email must be rejected. Body: ' . (string) $response->getBody()
        );
        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            'Rejection must be a client-error 4xx, not a server failure. Body: ' . (string) $response->getBody()
        );
    }

    #[Test]
    public function testPasswordGrantRejectsPatientRoleWithStaffCredentials(): void
    {
        // Deny path: present staff credentials (admin/pass) with
        // user_role=patient. The admin account is in users table, not
        // the portal credentials table, so the patient-role auth path
        // must not resolve them.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialPatientClient($http);

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:port patient/Patient.read',
                'user_role' => 'patient',
                'username' => 'admin',
                'password' => 'pass',
                'email' => 'admin@example.invalid',
            ],
        ]);

        $this->assertGreaterThanOrEqual(
            400,
            $response->getStatusCode(),
            'Patient-role password grant with staff creds must be rejected. Body: ' . (string) $response->getBody()
        );
        $this->assertLessThan(
            500,
            $response->getStatusCode(),
            'Rejection must be a client-error 4xx. Body: ' . (string) $response->getBody()
        );
    }

    /**
     * @return array{pid: int, pubpid: non-falsy-string, portal_username: string, portal_login_username: string, plain_password: string, email: string}
     */
    private function installPortalFixture(): array
    {
        $this->patientFixture = $this->portalFixtures->installPortalPatient(
            portalLoginUsername: 'password-grant-patient-' . Uuid::uuid4()->toString(),
            plainPassword: 'ValidPortalPassword1!',
            email: 'password-grant-patient-' . bin2hex(random_bytes(4)) . '@test.example'
        );
        return $this->patientFixture;
    }

    /**
     * Register a confidential client with grant_types = password and
     * patient-scope permissions. Flipped to is_enabled=1 immediately
     * to bypass the admin-approval workflow for the test.
     *
     * @return array{0: string, 1: string}
     */
    private function registerConfidentialPatientClient(Client $http): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'PasswordGrantPatientRoleFlowTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:port patient/Patient.read offline_access',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR must succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        $this->clientId = $data['client_id'];
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ?, `is_enabled` = 1 WHERE `client_id` = ?',
            ['password', $this->clientId]
        );
        return [$this->clientId, $data['client_secret']];
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
