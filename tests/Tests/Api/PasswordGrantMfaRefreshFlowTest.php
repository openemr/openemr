<?php

/**
 * Password grant + MFA-satisfied refresh flow.
 *
 * Verifies that once a password-grant access_token has been issued for
 * an MFA-enrolled user (staff role) with a valid TOTP, the returned
 * refresh_token can be exchanged for a new access_token WITHOUT the
 * caller re-presenting a TOTP code. That is a documented OAuth2
 * property (refresh is a long-lived credential that survives beyond
 * the initial authentication event), but it is load-bearing — a
 * regression that started requiring MFA on every refresh would break
 * every password-grant client that relies on refresh; a regression
 * that INVALIDATED the refresh token on MFA-required paths would
 * also break the same clients. Locking the behaviour in explicitly.
 *
 * Separately confirms that the initial password-grant token WAS
 * actually MFA-gated (a wrong TOTP is rejected), so the "MFA held on
 * the way in" premise this refresh property depends on is real.
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
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

class PasswordGrantMfaRefreshFlowTest extends TestCase
{
    private string $baseUrl;
    private ?string $clientId = null;
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    /** @var list<array<string, mixed>> */
    private array $originalAdminMfaRows = [];
    private int $adminUserId = 0;
    private ?string $originalAdminPasswordHash = null;

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

        // Enable password grant for both roles for the duration of this
        // test (snapshot + restore in tearDown so we do not leak state
        // into other test files running in the same process).
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);

        // Snapshot admin MFA rows so tearDown restores exactly whatever
        // was there before this test, then enroll a TOTP with a known
        // secret so we can compute valid codes deterministically.
        $adminRow = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1"
        );
        $adminId = $adminRow['id'] ?? null;
        if (!is_numeric($adminId) || (int) $adminId === 0) {
            $this->markTestSkipped('admin user not present in this test environment');
        }
        $this->adminUserId = (int) $adminId;
        // Snapshot the current password hash so tearDown can restore
        // it directly via SQL (avoiding updatePassword's session +
        // history + revocation side effects on the test tear-down path).
        $pwRow = QueryUtils::querySingleRow(
            "SELECT password FROM users_secure WHERE id = ?",
            [$this->adminUserId]
        );
        $this->originalAdminPasswordHash = is_array($pwRow) && is_string($pwRow['password'] ?? null)
            ? $pwRow['password']
            : null;
        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords(
            "SELECT user_id, name, method, var1, var2, last_challenge "
                . "FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        $this->originalAdminMfaRows = $rows;
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$this->adminUserId]
        );
        $secret = 'JBSWY3DPEHPK3PXP';
        $encryptedSecret = ServiceContainer::getCrypto()->encryptForDatabase($secret);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO login_mfa_registrations "
                . "(user_id, name, method, var1, var2, last_challenge) "
                . "VALUES (?, 'test-refresh-flow', 'TOTP', ?, '', NULL)",
            [$this->adminUserId, $encryptedSecret]
        );
    }

    protected function tearDown(): void
    {
        try {
            // Restore admin's password hash first — do this via direct SQL
            // rather than updatePassword() so a failed restore doesn't
            // cascade into a broken admin account for subsequent tests
            // in this process. Also reset lockout counters that
            // updatePassword clears.
            if ($this->originalAdminPasswordHash !== null && $this->adminUserId > 0) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE users_secure SET password = ?, login_fail_counter = 0, "
                        . "last_login_fail = NULL, auto_block_emailed = 0 "
                        . "WHERE id = ?",
                    [$this->originalAdminPasswordHash, $this->adminUserId]
                );
            }
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_trusted_user` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
            // Restore admin MFA rows exactly as they were pre-test.
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM login_mfa_registrations WHERE user_id = ?",
                [$this->adminUserId]
            );
            foreach ($this->originalAdminMfaRows as $row) {
                QueryUtils::sqlStatementThrowException(
                    "INSERT INTO login_mfa_registrations "
                        . "(user_id, name, method, var1, var2, last_challenge) "
                        . "VALUES (?, ?, ?, ?, ?, ?)",
                    [
                        $row['user_id'],
                        $row['name'],
                        $row['method'],
                        $row['var1'],
                        $row['var2'],
                        $row['last_challenge'],
                    ]
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
    public function testRefreshTokenAfterMfaSatisfiedPasswordGrantSucceedsWithoutFreshMfa(): void
    {
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        // Initial password-grant request with a valid TOTP — expected 200.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $tokens = $this->postToken(
            $http,
            [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr offline_access',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
                'mfa_token' => $tfa->getCode('JBSWY3DPEHPK3PXP'),
                'mfa_type' => 'TOTP',
            ]
        );
        $this->assertArrayHasKey(
            'refresh_token',
            $tokens,
            'Password grant with offline_access + MFA-satisfied auth must return a refresh_token'
        );
        $this->assertIsString($tokens['refresh_token']);
        $this->assertNotSame('', $tokens['refresh_token']);
        $firstAccess = $tokens['access_token'];

        // Refresh WITHOUT presenting a new TOTP — the property this test
        // locks in. Refresh_token is a long-lived credential that
        // survives beyond the initial MFA challenge; any regression that
        // required a fresh TOTP on refresh would break every
        // password-grant client relying on offline_access.
        $refreshed = $this->postToken(
            $http,
            [
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokens['refresh_token'],
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ]
        );
        $this->assertArrayHasKey('access_token', $refreshed, 'Refresh must return a new access_token');
        $this->assertIsString($refreshed['access_token']);
        $this->assertNotSame(
            $firstAccess,
            $refreshed['access_token'],
            'Refresh must return a NEW access_token (not the same value re-issued)'
        );
    }

    #[Test]
    public function testRefreshTokenIsRevokedAfterPasswordChange(): void
    {
        // Real security property (previously untested): once a user
        // changes their password, any refresh_token that was issued
        // with the OLD credentials must stop working. Without this,
        // an attacker who stole a refresh_token before the change
        // keeps API access for the natural refresh_token lifetime
        // (weeks/months) despite the user closing the compromise
        // window. See AuthUtils::updatePassword — after the
        // users_secure UPDATE we now UPDATE api_refresh_token /
        // api_token SET revoked=1 for that user_id.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        // Mint a token pair with a valid TOTP so we have a real
        // refresh_token to attempt to reuse after the password change.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $tokens = $this->postToken(
            $http,
            [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr offline_access',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
                'mfa_token' => $tfa->getCode('JBSWY3DPEHPK3PXP'),
                'mfa_type' => 'TOTP',
            ]
        );
        $this->assertArrayHasKey('refresh_token', $tokens);
        $this->assertIsString($tokens['refresh_token']);
        $refreshToken = $tokens['refresh_token'];

        // Simulate a password change via AuthUtils::updatePassword. We
        // call it inline (rather than through a UI form) so the test
        // exercises the code path where token revocation must fire.
        // Change admin's password to a scratch value, then change it
        // back — the important thing is that updatePassword() runs.
        $auth = new AuthUtils();
        // updatePassword takes both passwords by reference and clears
        // the buffers via clearFromMemory on return. Use fresh string
        // variables and don't reuse them. tearDown() restores the
        // original hash directly via SQL so we don't have to call
        // updatePassword a second time here to put things back.
        $currentPwd = 'pass';
        $scratchPwd = 'ScratchPassword1!';
        $ok = $auth->updatePassword($this->adminUserId, $this->adminUserId, $currentPwd, $scratchPwd);
        $errMsg = $auth->getErrorMessage();
        $this->assertTrue(
            $ok,
            'updatePassword should succeed: ' . (is_string($errMsg) ? $errMsg : '(no error msg)')
        );

        // Attempt to use the pre-change refresh_token — must be rejected.
        $refreshResp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
            ],
        ]);
        $this->assertNotSame(
            200,
            $refreshResp->getStatusCode(),
            'Refresh token minted before a password change must be rejected. '
                . '200 here means the token survived the password change and an '
                . 'attacker who stole it keeps access until natural expiry.'
        );
    }

    #[Test]
    public function testPasswordGrantRejectsWrongMfaTokenAndDoesNotIssueRefresh(): void
    {
        // Companion assertion — the "MFA was actually held on the way
        // in" premise the refresh test depends on. If wrong TOTP quietly
        // issued a token, the property under test would be moot.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialClient($http);
        $this->clientId = $clientId;

        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr offline_access',
                'user_role' => 'users',
                'username' => 'admin',
                'password' => 'pass',
                'mfa_token' => '000001', // guaranteed not the current code
                'mfa_type' => 'TOTP',
            ],
        ]);
        $this->assertNotSame(
            200,
            $response->getStatusCode(),
            'Wrong TOTP on password grant must not issue a token — '
                . 'a 200 here means MFA is not actually being enforced.'
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
                'client_name' => 'PasswordGrantMfaRefreshFlowTest',
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
        return [$data['client_id'], $data['client_secret']];
    }

    /**
     * @param array<string, string> $form
     *
     * @return array<string, mixed>
     */
    private function postToken(Client $http, array $form): array
    {
        $response = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => $form,
        ]);
        $this->assertSame(
            200,
            $response->getStatusCode(),
            'Token request should return 200 — body: ' . (string) $response->getBody()
        );
        $body = json_decode((string) $response->getBody(), true);
        $this->assertIsArray($body);
        $this->assertArrayHasKey('access_token', $body, 'Token response must include access_token');
        /** @var array<string, mixed> $body */
        return $body;
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
