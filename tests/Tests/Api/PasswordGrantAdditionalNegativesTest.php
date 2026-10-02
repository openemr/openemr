<?php

/**
 * Password grant additional negative-path tests.
 *
 * Covers the two rejection branches in CustomPasswordGrant not
 * previously pinned:
 *
 *   - missing user_role at line 68 (invalidRequest)
 *   - wrong credentials at line 104 (invalidGrant Failed Authentication)
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

class PasswordGrantAdditionalNegativesTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    /** @var list<array<string, mixed>> */
    private array $originalAdminMfaRows = [];
    private int $adminUserId = 0;
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

        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);

        // OEGlobalsBag only mutates the current PHPUnit process; the HTTP
        // server reads oauth_password_grant from the globals table on
        // each request. Persist to DB so CustomPasswordGrant is enabled
        // server-side for the token calls below.
        $this->persistPasswordGrantEnabled();

        // Wrong-credentials test needs admin to NOT be TOTP-enrolled so
        // we exercise the user-lookup rejection (line 104) rather than
        // the MFA branch. Snapshot + delete any existing MFA rows so
        // tearDown can restore them.
        $adminRow = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1"
        );
        $adminId = $adminRow['id'] ?? null;
        if (is_numeric($adminId) && (int) $adminId !== 0) {
            $this->adminUserId = (int) $adminId;
            /** @var list<array<string, mixed>> $rows */
            $rows = QueryUtils::fetchRecords(
                "SELECT user_id, name, method, var1, var2, last_challenge, last_used_step "
                    . "FROM login_mfa_registrations WHERE user_id = ?",
                [$this->adminUserId]
            );
            $this->originalAdminMfaRows = $rows;
            if ($rows !== []) {
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM login_mfa_registrations WHERE user_id = ?",
                    [$this->adminUserId]
                );
            }
        }
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
            if ($this->adminUserId > 0 && $this->originalAdminMfaRows !== []) {
                foreach ($this->originalAdminMfaRows as $row) {
                    QueryUtils::sqlStatementThrowException(
                        "INSERT INTO login_mfa_registrations "
                            . "(user_id, name, method, var1, var2, last_challenge, last_used_step) "
                            . "VALUES (?, ?, ?, ?, ?, ?, ?)",
                        [
                            $row['user_id'],
                            $row['name'],
                            $row['method'],
                            $row['var1'],
                            $row['var2'],
                            $row['last_challenge'],
                            $row['last_used_step'] ?? null,
                        ]
                    );
                }
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

    private function persistPasswordGrantEnabled(): void
    {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            ['oauth_password_grant']
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $this->originalPasswordGrantGlobalRow = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                ['3', 'oauth_password_grant']
            );
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
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                ['oauth_password_grant']
            );
        } elseif ($this->originalPasswordGrantGlobalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$this->originalPasswordGrantGlobalRow, 'oauth_password_grant']
            );
        }
    }

    #[Test]
    public function testPasswordGrantWithoutUserRoleReturnsInvalidRequest(): void
    {
        // CustomPasswordGrant:68 throws invalidRequest when user_role is
        // absent. Sibling to the username/password/email parameter tests.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr',
                'username' => 'admin',
                'password' => 'pass',
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'Missing user_role must return 4xx. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame('invalid_request', $body['error'] ?? null);
        $hint = $body['hint'] ?? '';
        $this->assertIsString($hint);
        $this->assertStringContainsString('user_role', $hint);
    }

    #[Test]
    public function testPasswordGrantWithWrongPasswordReturnsInvalidGrant(): void
    {
        // CustomPasswordGrant:104 throws invalidGrant when the user
        // repository returns non-UserEntity for the credentials.
        // Sends valid client + valid username + wrong password so the
        // grant reaches the user lookup and gets back a null result.
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
                'password' => 'definitely-not-the-real-password-' . bin2hex(random_bytes(4)),
            ],
        ]);
        $this->assertContains(
            $response->getStatusCode(),
            [400, 401],
            'Wrong password must return 4xx. Body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertSame(
            'invalid_grant',
            $body['error'] ?? null,
            'Wrong credentials must return {"error":"invalid_grant"}'
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
                'client_name' => 'PasswordGrantAdditionalNegativesTest-' . bin2hex(random_bytes(3)),
                'token_endpoint_auth_method' => 'client_secret_post',
                'contacts' => ['e2e@test.example'],
                'scope' => 'openid api:oemr',
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
