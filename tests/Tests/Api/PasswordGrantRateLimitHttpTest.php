<?php

/**
 * Password grant rate-limit HTTP response-shape test.
 *
 * The service-layer PasswordGrantHardeningTest covers the AuthUtils
 * counter semantics directly; this api-layer analogue proves the same
 * invariant is observable at the OAuth2 /token endpoint — specifically
 * that once the brute-force threshold is reached, correct credentials
 * are also rejected (the lockout is active, not just the mismatch).
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
use OpenEMR\Common\Database\SqlQueryException;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PasswordGrantRateLimitHttpTest extends TestCase
{
    private const TARGET_USERNAME = 'admin';
    private const CORRECT_PASSWORD = 'pass';

    private string $baseUrl;
    private ?string $clientId = null;

    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private mixed $originalMaxFailedBagSetting = null;
    private bool $originalMaxFailedBagWasSet = false;
    private ?string $originalMaxFailedGlobalRow = null;
    private bool $maxFailedGlobalRowWasInserted = false;

    /** @var array<string, mixed>|null */
    private ?array $originalUserLockoutRow = null;

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
        $this->originalPasswordGrantBagWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantBagSetting = $globals->get('oauth_password_grant');
        $globals->set('oauth_password_grant', 3);
        $this->persistGlobal(
            'oauth_password_grant',
            '3',
            $this->originalPasswordGrantGlobalRow,
            $this->passwordGrantGlobalRowWasInserted
        );

        $this->originalMaxFailedBagWasSet = $globals->has('password_max_failed_logins');
        $this->originalMaxFailedBagSetting = $globals->getInt('password_max_failed_logins');
        $globals->set('password_max_failed_logins', 2);
        $this->persistGlobal(
            'password_max_failed_logins',
            '2',
            $this->originalMaxFailedGlobalRow,
            $this->maxFailedGlobalRowWasInserted
        );

        $row = QueryUtils::querySingleRow(
            "SELECT `login_fail_counter`, `last_login_fail`, `auto_block_emailed` "
                . "FROM `users_secure` WHERE BINARY `username` = ?",
            [self::TARGET_USERNAME]
        );
        if (is_array($row)) {
            /** @var array<string, mixed> $row */
            $this->originalUserLockoutRow = $row;
            // Start from a known-clean counter so the test's lockout
            // threshold is reached deterministically regardless of
            // ambient state.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `login_fail_counter` = 0, "
                    . "`last_login_fail` = NULL, `auto_block_emailed` = 0 "
                    . "WHERE BINARY `username` = ?",
                [self::TARGET_USERNAME]
            );
        }
    }

    protected function tearDown(): void
    {
        // Each cleanup action is isolated: a failure in one must not
        // skip the others, since any skipped restoration can leave the
        // admin account locked or the DB threshold at 2 for later tests.
        $this->safeRun(function (): void {
            if ($this->clientId !== null) {
                QueryUtils::sqlStatementThrowException(
                    'DELETE FROM `oauth_clients` WHERE `client_id` = ?',
                    [$this->clientId]
                );
            }
        });
        $this->safeRun(function (): void {
            if ($this->originalUserLockoutRow !== null) {
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `users_secure` SET `login_fail_counter` = ?, "
                        . "`last_login_fail` = ?, `auto_block_emailed` = ? "
                        . "WHERE BINARY `username` = ?",
                    [
                        $this->originalUserLockoutRow['login_fail_counter'],
                        $this->originalUserLockoutRow['last_login_fail'],
                        $this->originalUserLockoutRow['auto_block_emailed'],
                        self::TARGET_USERNAME,
                    ]
                );
            }
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'password_max_failed_logins',
                $this->originalMaxFailedGlobalRow,
                $this->maxFailedGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'oauth_password_grant',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );
        });
        $this->safeRun(function (): void {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalMaxFailedBagWasSet) {
                $globals->set('password_max_failed_logins', $this->originalMaxFailedBagSetting);
            } else {
                $globals->remove('password_max_failed_logins');
                unset($GLOBALS['password_max_failed_logins']);
            }
        });
        $this->safeRun(function (): void {
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantBagWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantBagSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        });
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            // Swallow and continue: subsequent restorations are more
            // important than surfacing a single DB failure here, and
            // phpunit still reports the test outcome. Narrowed to
            // SqlQueryException so unrelated errors (TypeError,
            // ErrorException, parse errors) still propagate.
            fwrite(STDERR, '[' . static::class . "::tearDown] cleanup step failed: " . $e->getMessage() . "\n");
        }
    }

    #[Test]
    public function testTokenEndpointRejectsCorrectPasswordAfterBruteForceThresholdReached(): void
    {
        // password_max_failed_logins is set to 2 in setUp. AuthUtils
        // locks the account once users_secure.login_fail_counter >= 2.
        // Two wrong attempts raise the counter to 2; the third attempt
        // — even with the CORRECT password — must be rejected because
        // the lockout check runs before the password check. This locks
        // the regression invariant that the lockout is observable at
        // the HTTP /token endpoint, not only at the AuthUtils layer.
        $http = $this->buildClient();
        [$clientId, $clientSecret] = $this->registerConfidentialPasswordGrantClient($http);
        $this->clientId = $clientId;

        // Sentinel: prove the fixture credentials actually work before
        // running the lockout sequence. Without this, a missing admin
        // row or renamed password would make every /token call return
        // invalid_grant and the test would pass trivially while the
        // intended regression stayed undetected.
        $sentinel = $this->postToken($http, $clientId, $clientSecret, self::CORRECT_PASSWORD);
        $this->assertSame(
            200,
            $sentinel['status'],
            'Sentinel mint with correct credentials must succeed; otherwise '
                . 'the lockout sequence below is meaningless. Body: ' . $sentinel['body']
        );
        $sentinelBody = json_decode($sentinel['body'], true);
        $this->assertIsArray($sentinelBody);
        $this->assertIsString(
            $sentinelBody['access_token'] ?? null,
            'Sentinel response must contain an access_token.'
        );
        // Successful auth does not bump login_fail_counter, so the
        // counter stays at the zero we set in setUp. No reset needed.

        $wrongPassword = 'definitely-not-the-real-password-' . bin2hex(random_bytes(4));

        for ($attempt = 1; $attempt <= 2; $attempt++) {
            $resp = $this->postToken($http, $clientId, $clientSecret, $wrongPassword);
            $this->assertContains(
                $resp['status'],
                [400, 401],
                "Wrong-password attempt $attempt must be a 4xx. Body: " . $resp['body']
            );
            $body = json_decode($resp['body'], true);
            $this->assertIsArray($body);
            $this->assertSame(
                'invalid_grant',
                $body['error'] ?? null,
                "Wrong-password attempt $attempt must return invalid_grant"
            );
        }

        // Counter is now 2; threshold is 2. Correct credentials must
        // still be rejected while the lockout window is active.
        $lockedResp = $this->postToken($http, $clientId, $clientSecret, self::CORRECT_PASSWORD);
        $this->assertContains(
            $lockedResp['status'],
            [400, 401],
            'After threshold, correct password must also be rejected. Body: ' . $lockedResp['body']
        );
        $lockedBody = json_decode($lockedResp['body'], true);
        $this->assertIsArray($lockedBody);
        $this->assertSame(
            'invalid_grant',
            $lockedBody['error'] ?? null,
            'Lockout response must present as invalid_grant at /token (not a 5xx or a success)'
        );
        $this->assertArrayNotHasKey(
            'access_token',
            $lockedBody,
            'Locked account must never receive an access_token even with correct credentials'
        );
    }

    /**
     * @return array{status: int, body: string}
     */
    private function postToken(Client $http, string $clientId, string $clientSecret, string $password): array
    {
        $resp = $http->post($this->baseUrl . '/oauth2/default/token', [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'scope' => 'openid api:oemr',
                'user_role' => 'users',
                'username' => self::TARGET_USERNAME,
                'password' => $password,
            ],
        ]);
        return [
            'status' => $resp->getStatusCode(),
            'body' => (string) $resp->getBody(),
        ];
    }

    /**
     * @return array{string, string} [client_id, client_secret]
     */
    private function registerConfidentialPasswordGrantClient(Client $http): array
    {
        $reg = $http->post($this->baseUrl . '/oauth2/default/registration', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'application_type' => 'private',
                'redirect_uris' => ['https://client.example/cb'],
                'client_name' => 'PasswordGrantRateLimitHttpTest-' . bin2hex(random_bytes(3)),
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
        QueryUtils::sqlStatementThrowException(
            'UPDATE `oauth_clients` SET `grant_types` = ? WHERE `client_id` = ?',
            ['password', $data['client_id']]
        );
        return [$data['client_id'], $data['client_secret']];
    }

    private function persistGlobal(
        string $name,
        string $value,
        ?string &$originalCapture,
        bool &$insertedCapture
    ): void {
        $current = QueryUtils::querySingleRow(
            'SELECT gl_value FROM `globals` WHERE gl_name = ?',
            [$name]
        );
        if (is_array($current)) {
            $glValue = $current['gl_value'] ?? null;
            $originalCapture = is_string($glValue) ? $glValue : null;
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$value, $name]
            );
        } else {
            QueryUtils::sqlStatementThrowException(
                'INSERT INTO `globals` (`gl_name`, `gl_index`, `gl_value`) VALUES (?, 0, ?)',
                [$name, $value]
            );
            $insertedCapture = true;
        }
    }

    private function restoreGlobal(string $name, ?string $originalRow, bool $wasInserted): void
    {
        if ($wasInserted) {
            QueryUtils::sqlStatementThrowException(
                'DELETE FROM `globals` WHERE gl_name = ?',
                [$name]
            );
        } elseif ($originalRow !== null) {
            QueryUtils::sqlStatementThrowException(
                'UPDATE `globals` SET gl_value = ? WHERE gl_name = ?',
                [$originalRow, $name]
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
