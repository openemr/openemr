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

    // Each triple of (captured, wasSet, setting) tracks a bag override:
    //   - captured is false until setUp successfully reads the pre-test
    //     bag state; cleanup must only touch the bag when captured.
    //     Required because a mid-setUp SqlQueryException can skip the
    //     capture for later globals, leaving wasSet=false (default) —
    //     without the gate, cleanup would then remove() a legitimate
    //     pre-existing bag value.
    //   - wasSet records whether the pre-test bag held the key.
    //   - setting is the pre-test value when wasSet is true.
    private bool $passwordGrantBagCaptured = false;
    private mixed $originalPasswordGrantBagSetting = null;
    private bool $originalPasswordGrantBagWasSet = false;
    private ?string $originalPasswordGrantGlobalRow = null;
    private bool $passwordGrantGlobalRowWasInserted = false;

    private bool $maxFailedBagCaptured = false;
    private mixed $originalMaxFailedBagSetting = null;
    private bool $originalMaxFailedBagWasSet = false;
    private ?string $originalMaxFailedGlobalRow = null;
    private bool $maxFailedGlobalRowWasInserted = false;

    private bool $ipMaxFailedBagCaptured = false;
    private mixed $originalIpMaxFailedBagSetting = null;
    private bool $originalIpMaxFailedBagWasSet = false;
    private ?string $originalIpMaxFailedGlobalRow = null;
    private bool $ipMaxFailedGlobalRowWasInserted = false;

    /** @var array<string, mixed>|null */
    private ?array $originalUserLockoutRow = null;

    private string $clientIp = '127.0.0.1';
    /** @var array<string, mixed>|null null when no ip_tracking row existed pre-test */
    private ?array $originalIpTrackingRow = null;
    private bool $ipTrackingRowSnapshotted = false;

    /** @var list<string> accumulator for teardown failure messages */
    private array $tearDownFailures = [];

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

        // PHPUnit 11.5 skips tearDown() when setUp() throws. Any state
        // mutation that lands before a later setup step fails would
        // leak into subsequent tests. Wrap the state-mutating section
        // so a mid-setUp failure triggers rollback via tearDown().
        try {
            $globals = OEGlobalsBag::getInstance();
            $this->originalPasswordGrantBagWasSet = $globals->has('oauth_password_grant');
            $this->originalPasswordGrantBagSetting = $globals->get('oauth_password_grant');
            $this->passwordGrantBagCaptured = true;
            $globals->set('oauth_password_grant', 3);
            $this->persistGlobal(
                'oauth_password_grant',
                '3',
                $this->originalPasswordGrantGlobalRow,
                $this->passwordGrantGlobalRowWasInserted
            );

            $this->originalMaxFailedBagWasSet = $globals->has('password_max_failed_logins');
            $this->originalMaxFailedBagSetting = $globals->getInt('password_max_failed_logins');
            $this->maxFailedBagCaptured = true;
            $globals->set('password_max_failed_logins', 2);
            $this->persistGlobal(
                'password_max_failed_logins',
                '2',
                $this->originalMaxFailedGlobalRow,
                $this->maxFailedGlobalRowWasInserted
            );

            // Disable the per-IP gate for this test. AuthUtils runs the
            // IP check BEFORE the password check; a deployment with a
            // low ip_max_failed_logins could reject the final
            // correct-password request through the IP path, letting
            // the test pass even if the per-account lockout we're
            // actually verifying regressed.
            $this->originalIpMaxFailedBagWasSet = $globals->has('ip_max_failed_logins');
            $this->originalIpMaxFailedBagSetting = $globals->getInt('ip_max_failed_logins');
            $this->ipMaxFailedBagCaptured = true;
            $globals->set('ip_max_failed_logins', 0);
            $this->persistGlobal(
                'ip_max_failed_logins',
                '0',
                $this->originalIpMaxFailedGlobalRow,
                $this->ipMaxFailedGlobalRowWasInserted
            );

            $row = QueryUtils::querySingleRow(
                "SELECT `login_fail_counter`, `last_login_fail`, `auto_block_emailed` "
                    . "FROM `users_secure` WHERE BINARY `username` = ?",
                [self::TARGET_USERNAME]
            );
            if (is_array($row)) {
                /** @var array<string, mixed> $row */
                $this->originalUserLockoutRow = $row;
                // Start from a known-clean counter so the test's
                // lockout threshold is reached deterministically
                // regardless of ambient state.
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `users_secure` SET `login_fail_counter` = 0, "
                        . "`last_login_fail` = NULL, `auto_block_emailed` = 0 "
                        . "WHERE BINARY `username` = ?",
                    [self::TARGET_USERNAME]
                );
            }

            // The two wrong-password attempts below also bump
            // ip_tracking.ip_login_fail_counter for the request IP.
            // Snapshot that row and reset the counter so (a) the
            // per-IP threshold cannot preempt the per-account lockout
            // we want to test, and (b) the counter doesn't bleed into
            // unrelated tests sharing 127.0.0.1 (the default
            // REMOTE_ADDR in CLI runs).
            $ipRow = QueryUtils::querySingleRow(
                "SELECT `total_ip_login_fail_counter`, `ip_login_fail_counter`, "
                    . "`ip_last_login_fail`, `ip_auto_block_emailed`, "
                    . "`mfa_login_fail_counter`, `mfa_last_login_fail` "
                    . "FROM `ip_tracking` WHERE `ip_string` = ?",
                [$this->clientIp]
            );
            // Only flip the snapshotted flag AFTER the SELECT returns
            // — otherwise a failed SELECT would make tearDown think it
            // owns a cleanup it never performed.
            $this->ipTrackingRowSnapshotted = true;
            if (is_array($ipRow)) {
                /** @var array<string, mixed> $ipRow */
                $this->originalIpTrackingRow = $ipRow;
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `ip_tracking` SET `ip_login_fail_counter` = 0, "
                        . "`ip_last_login_fail` = NULL, `ip_auto_block_emailed` = 0 "
                        . "WHERE `ip_string` = ?",
                    [$this->clientIp]
                );
            }
        } catch (SqlQueryException $setUpFailure) {
            try {
                $this->tearDown();
            } catch (\Throwable $rollbackFailure) {
                throw new \RuntimeException(
                    'setUp failed; rollback via tearDown also failed: '
                        . $rollbackFailure->getMessage(),
                    0,
                    $setUpFailure
                );
            }
            throw $setUpFailure;
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
            if (!$this->ipTrackingRowSnapshotted) {
                return;
            }
            if ($this->originalIpTrackingRow === null) {
                // No row before the test; delete anything AuthUtils
                // inserted during the wrong-password loop.
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM `ip_tracking` WHERE `ip_string` = ?",
                    [$this->clientIp]
                );
                return;
            }
            QueryUtils::sqlStatementThrowException(
                "UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = ?, "
                    . "`ip_login_fail_counter` = ?, `ip_last_login_fail` = ?, "
                    . "`ip_auto_block_emailed` = ?, "
                    . "`mfa_login_fail_counter` = ?, `mfa_last_login_fail` = ? "
                    . "WHERE `ip_string` = ?",
                [
                    $this->originalIpTrackingRow['total_ip_login_fail_counter'],
                    $this->originalIpTrackingRow['ip_login_fail_counter'],
                    $this->originalIpTrackingRow['ip_last_login_fail'],
                    $this->originalIpTrackingRow['ip_auto_block_emailed'],
                    $this->originalIpTrackingRow['mfa_login_fail_counter'],
                    $this->originalIpTrackingRow['mfa_last_login_fail'],
                    $this->clientIp,
                ]
            );
        });
        $this->safeRun(function (): void {
            $this->restoreGlobal(
                'ip_max_failed_logins',
                $this->originalIpMaxFailedGlobalRow,
                $this->ipMaxFailedGlobalRowWasInserted
            );
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
            if (!$this->ipMaxFailedBagCaptured) {
                // setUp threw before capturing the pre-test bag state;
                // we never mutated the bag either, so no cleanup needed.
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalIpMaxFailedBagWasSet) {
                $globals->set('ip_max_failed_logins', $this->originalIpMaxFailedBagSetting);
            } else {
                $globals->remove('ip_max_failed_logins');
                unset($GLOBALS['ip_max_failed_logins']);
            }
        });
        $this->safeRun(function (): void {
            if (!$this->maxFailedBagCaptured) {
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalMaxFailedBagWasSet) {
                $globals->set('password_max_failed_logins', $this->originalMaxFailedBagSetting);
            } else {
                $globals->remove('password_max_failed_logins');
                unset($GLOBALS['password_max_failed_logins']);
            }
        });
        $this->safeRun(function (): void {
            if (!$this->passwordGrantBagCaptured) {
                return;
            }
            $globals = OEGlobalsBag::getInstance();
            if ($this->originalPasswordGrantBagWasSet) {
                $globals->set('oauth_password_grant', $this->originalPasswordGrantBagSetting);
            } else {
                $globals->remove('oauth_password_grant');
                unset($GLOBALS['oauth_password_grant']);
            }
        });

        // Report any accumulated cleanup failures by failing tearDown.
        // Running every cleanup first (above) means stale shared state
        // is minimised regardless; failing here surfaces the regression
        // instead of silently passing a test that leaked DB state.
        if ($this->tearDownFailures !== []) {
            $failures = $this->tearDownFailures;
            $this->tearDownFailures = [];
            self::fail(
                "tearDown cleanup had " . count($failures) . " failure(s); "
                . "shared state may be stale for later tests:\n  - "
                . implode("\n  - ", $failures)
            );
        }
    }

    private function safeRun(callable $cleanup): void
    {
        try {
            $cleanup();
        } catch (SqlQueryException $e) {
            // Record and continue: subsequent restorations are more
            // important than aborting here, and phpunit will see the
            // aggregated failure at the end of tearDown. Narrowed to
            // SqlQueryException so unrelated errors (TypeError,
            // ErrorException, parse errors) still propagate.
            $this->tearDownFailures[] = $e->getMessage();
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
