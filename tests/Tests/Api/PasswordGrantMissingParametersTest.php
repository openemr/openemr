<?php

/**
 * Password grant required-parameter rejection tests.
 *
 * CustomPasswordGrant::validateUser (src/.../Grant/CustomPasswordGrant.php)
 * throws OAuthServerException::invalidRequest on three missing-parameter
 * pre-conditions before it ever consults the user repository:
 *
 *   - :56  username must be present
 *   - :62  password must be present
 *   - :74  email must be present when user_role=patient
 *
 * These are shallow checks but no test locks them in — a regression that
 * deleted one of the guards would surface as a token being issued (or a
 * different error) instead of the explicit `invalid_request` League
 * shape callers rely on.
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
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class PasswordGrantMissingParametersTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;
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
            $message = 'OAuth flow requires a real webserver (Apache/nginx)';
            if (getenv('CI') !== false) {
                self::fail($message . ' — hard failure in CI');
            }
            $this->markTestSkipped($message);
        }

        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
    }

    protected function tearDown(): void
    {
        try {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
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
    public function testPasswordGrantWithoutUsernameReturnsInvalidRequest(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'password' => 'pass',
            ],
        ]);
        $this->assertPasswordGrantParameterError($response, 'username');
    }

    #[Test]
    public function testPasswordGrantWithoutPasswordReturnsInvalidRequest(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => 'admin',
            ],
        ]);
        $this->assertPasswordGrantParameterError($response, 'password');
    }

    #[Test]
    public function testPasswordGrantPatientRoleWithoutEmailReturnsInvalidRequest(): void
    {
        // CustomPasswordGrant:74 — email required only when user_role=patient.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:port',
                'user_role' => 'patient',
                'username' => 'somepatient',
                'password' => 'somepass',
            ],
        ]);
        $this->assertPasswordGrantParameterError($response, 'email');
    }

    private function assertPasswordGrantParameterError(
        ResponseInterface $response,
        string $expectedHintFragment,
    ): void {
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'Password grant missing required parameter must return 4xx. '
                . 'Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_request',
            $body['error'] ?? null,
            'Missing required parameter must return {"error":"invalid_request"}'
        );
        $hint = $body['hint'] ?? '';
        $this->assertIsString($hint);
        $this->assertStringContainsString(
            $expectedHintFragment,
            $hint,
            'Rejection hint should name the missing parameter'
        );
    }

    /**
     * @return array{string, string} [client_id, client_secret]
     */
    private function registerConfidentialClient(Client $http): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'PasswordGrantMissingParametersTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr api:port',
            ],
        ]);
        $this->assertSame(200, $reg->getStatusCode(), 'DCR should succeed');
        $data = json_decode((string) $reg->getBody(), true);
        $this->assertIsArray($data);
        $this->assertIsString($data['client_id']);
        $this->assertIsString($data['client_secret']);
        return [$data['client_id'], $data['client_secret']];
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
