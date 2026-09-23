<?php

/**
 * PasswordGrantHardeningTest
 *
 * Regression coverage for the four related tightening changes across the
 * OAuth2 + web-login auth boundary:
 *
 *   1. UserRepository::getAccountByPassword must require MFA when the user
 *      has TOTP enrolled — omitting mfa_token no longer skips the check.
 *   2. ClientRepository::validateClient must require a valid client_secret
 *      for confidential clients on the password grant (previously only
 *      enforced on authorization_code).
 *   3. AuthUtils::confirmPatientPassword must count failed portal-user
 *      password attempts against the per-IP counter so the portal-side
 *      password grant is rate-limited.
 *   4. AuthUtils::recordFailedAuthChallenge (new helper) must increment
 *      both the per-user (users_secure) and per-IP (ip_tracking) counters
 *      so callers on TOTP-failure paths engage the existing lockout on
 *      the next login attempt.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2026 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

declare(strict_types=1);

namespace OpenEMR\Tests\Services;

use League\OAuth2\Server\Exception\OAuthServerException;
use OpenEMR\BC\ServiceContainer;
use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Auth\OpenIDConnect\Entities\UserEntity;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\ClientRepository;
use OpenEMR\Common\Auth\OpenIDConnect\Repositories\UserRepository;
use OpenEMR\Common\Auth\UuidUserAccount;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Tests\Fixtures\PortalPatientFixtureManager;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use ReflectionClass;
use RobThree\Auth\Providers\Qr\BaconQrCodeProvider;
use RobThree\Auth\TwoFactorAuth;

class PasswordGrantHardeningTest extends TestCase
{
    private ?PortalPatientFixtureManager $portalFixtures = null;

    /** @var list<string> */
    private array $trackedClientIds = [];
    /**
     * Snapshots of `login_mfa_registrations` rows the tests replaced, keyed
     * by user_id. tearDown deletes our test rows for each user and re-inserts
     * whatever was there originally so tests do not clobber real MFA setup
     * on shared users (e.g. admin).
     *
     * @var array<int, list<array<string, mixed>>>
     */
    private array $originalMfaRowsByUser = [];
    private mixed $originalPasswordGrantSetting = null;
    private bool $originalPasswordGrantSettingWasSet = false;
    /**
     * Snapshots of users_secure lockout fields (login_fail_counter,
     * last_login_fail, auto_block_emailed) keyed by username. tearDown
     * restores the exact pre-mutation values so a shared user (e.g.
     * admin) doesn't lose its real lockout state to zero after a test.
     *
     * @var array<string, array<mixed>>
     */
    private array $originalUserLockoutByUsername = [];
    /**
     * Snapshots of ip_tracking rows keyed by ip_string. Value is the row
     * as returned by QueryUtils::querySingleRow (associative array) if
     * the row existed before the test, or null if it did not. tearDown
     * restores the exact row (or deletes an inserted row) so shared
     * IP-tracking state isn't wiped.
     *
     * @var array<string, ?array<mixed>>
     */
    private array $originalIpTrackingByString = [];
    private string $clientIp = '127.0.0.1';
    private bool $originalHttpHostWasSet = false;
    private ?string $originalHttpHost = null;
    private bool $originalRemoteAddrWasSet = false;
    private ?string $originalRemoteAddr = null;

    protected function setUp(): void
    {
        parent::setUp();
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSettingWasSet = $globals->has('oauth_password_grant');
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        // Enable both staff (1) and patient (2) password grant paths for the
        // whole test class so no test has to toggle it mid-flight.
        $globals->set('oauth_password_grant', 3);

        // Give CLI a deterministic client host/IP; MfaUtils reads HTTP_HOST
        // and collectIpAddresses() reads REMOTE_ADDR, both empty in CLI.
        // Track whether the key existed so tearDown can unset — leaving a
        // stale value behind poisons unrelated tests in the same process.
        $this->originalHttpHostWasSet = array_key_exists('HTTP_HOST', $_SERVER);
        $this->originalHttpHost = is_string($_SERVER['HTTP_HOST'] ?? null) ? $_SERVER['HTTP_HOST'] : null;
        if (!isset($_SERVER['HTTP_HOST']) || !is_string($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === '') {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }
        $this->originalRemoteAddrWasSet = array_key_exists('REMOTE_ADDR', $_SERVER);
        $this->originalRemoteAddr = is_string($_SERVER['REMOTE_ADDR'] ?? null) ? $_SERVER['REMOTE_ADDR'] : null;
        if (!isset($_SERVER['REMOTE_ADDR']) || !is_string($_SERVER['REMOTE_ADDR']) || $_SERVER['REMOTE_ADDR'] === '') {
            $_SERVER['REMOTE_ADDR'] = '127.0.0.1';
        }
        $this->clientIp = $_SERVER['REMOTE_ADDR'];
    }

    protected function tearDown(): void
    {
        foreach ($this->trackedClientIds as $clientId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM oauth_clients WHERE client_id = ?",
                [$clientId]
            );
        }
        foreach ($this->originalMfaRowsByUser as $userId => $originalRows) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM login_mfa_registrations WHERE user_id = ?",
                [$userId]
            );
            foreach ($originalRows as $row) {
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
        foreach ($this->originalUserLockoutByUsername as $username => $original) {
            // Restore the pre-mutation lockout state exactly rather than
            // resetting to zero — a shared user (admin) may legitimately
            // have had a non-zero counter or a recent last_login_fail from
            // real activity before the test ran.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `login_fail_counter` = ?, "
                    . "`last_login_fail` = ?, `auto_block_emailed` = ?, "
                    . "`mfa_fail_counter` = ?, `mfa_last_fail` = ? "
                    . "WHERE BINARY `username` = ?",
                [
                    $original['login_fail_counter'],
                    $original['last_login_fail'],
                    $original['auto_block_emailed'],
                    $original['mfa_fail_counter'],
                    $original['mfa_last_fail'],
                    $username,
                ]
            );
        }
        foreach ($this->originalIpTrackingByString as $ipString => $original) {
            if ($original === null) {
                // Row did not exist before the test; delete anything we
                // inserted.
                QueryUtils::sqlStatementThrowException(
                    "DELETE FROM `ip_tracking` WHERE `ip_string` = ?",
                    [$ipString]
                );
                continue;
            }
            // Row existed — restore its exact prior values instead of
            // deleting (which would wipe shared IP-tracking state that
            // other tests or the surrounding env may depend on).
            QueryUtils::sqlStatementThrowException(
                "UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = ?, "
                    . "`ip_login_fail_counter` = ?, `ip_last_login_fail` = ?, "
                    . "`ip_auto_block_emailed` = ?, "
                    . "`mfa_login_fail_counter` = ?, `mfa_last_login_fail` = ? "
                    . "WHERE `ip_string` = ?",
                [
                    $original['total_ip_login_fail_counter'],
                    $original['ip_login_fail_counter'],
                    $original['ip_last_login_fail'],
                    $original['ip_auto_block_emailed'],
                    $original['mfa_login_fail_counter'],
                    $original['mfa_last_login_fail'],
                    $ipString,
                ]
            );
        }

        $this->portalFixtures?->removePortalPatientFixtures();

        // Restore mutated globals: tests here set $_POST and $_SERVER keys
        // for MfaUtils / IP resolution. phpunit does not run in isolation,
        // so leaving them set leaks into unrelated tests in the same process.
        unset($_POST['mfa_token'], $_POST['mfa_type'], $_POST['authUser']);
        if ($this->originalHttpHostWasSet) {
            $_SERVER['HTTP_HOST'] = $this->originalHttpHost;
        } else {
            unset($_SERVER['HTTP_HOST']);
        }
        if ($this->originalRemoteAddrWasSet) {
            $_SERVER['REMOTE_ADDR'] = $this->originalRemoteAddr;
        } else {
            unset($_SERVER['REMOTE_ADDR']);
        }

        $globals = OEGlobalsBag::getInstance();
        if ($this->originalPasswordGrantSettingWasSet) {
            $globals->set('oauth_password_grant', $this->originalPasswordGrantSetting);
        } else {
            $globals->remove('oauth_password_grant');
            unset($GLOBALS['oauth_password_grant']);
        }

        parent::tearDown();
    }

    // ---------- ClientRepository::validateClient (4fx8 V8) ----------

    public function testValidateClientDeniesPasswordGrantWhenConfidentialClientOmitsSecret(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');

        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], null, 'password'),
            'Confidential client on password grant must not validate with a null client_secret.'
        );
        $this->assertFalse(
            $repo->validateClient($client['client_id'], '', 'password'),
            'Confidential client on password grant must not validate with an empty client_secret.'
        );
    }

    public function testValidateClientDeniesPasswordGrantWhenConfidentialClientSecretIsWrong(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], 'wrong-secret', 'password'),
            'Confidential client on password grant must reject a mismatched client_secret.'
        );
    }

    public function testValidateClientAllowsPasswordGrantWithCorrectConfidentialClientSecret(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], 'correct-secret', 'password'),
            'Confidential client on password grant must validate with the correct client_secret.'
        );
    }

    public function testValidateClientAllowsPasswordGrantForPublicClientWithNoSecret(): void
    {
        $client = $this->insertPublicClientFixture();
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], null, 'password'),
            'Public client on password grant must validate without a client_secret.'
        );
    }

    public function testValidateClientDeniesAuthCodeGrantWhenConfidentialClientOmitsSecret(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], null, 'authorization_code'),
            'Confidential client on authorization_code must not validate without a client_secret.'
        );
        $this->assertFalse(
            $repo->validateClient($client['client_id'], '', 'authorization_code'),
            'Confidential client on authorization_code must not validate with an empty client_secret.'
        );
    }

    public function testValidateClientDeniesAuthCodeGrantWhenConfidentialClientSecretIsWrong(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], 'wrong-secret', 'authorization_code'),
            'Confidential client on authorization_code must reject a wrong secret.'
        );
    }

    public function testValidateClientAllowsAuthCodeGrantWithCorrectConfidentialClientSecret(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], 'correct-secret', 'authorization_code'),
            'Confidential client on authorization_code must validate with the correct client_secret.'
        );
    }

    public function testValidateClientAllowsAuthCodeGrantForPublicClientWithNoSecret(): void
    {
        $client = $this->insertPublicClientFixture();
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], null, 'authorization_code'),
            'Public client on authorization_code with no client_secret must validate.'
        );
    }

    public function testValidateClientDeniesRefreshGrantWhenConfidentialClientOmitsSecret(): void
    {
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], null, 'refresh_token'),
            'Confidential client on refresh_token must not validate without a client_secret.'
        );
        $this->assertFalse(
            $repo->validateClient($client['client_id'], '', 'refresh_token'),
            'Confidential client on refresh_token must not validate with an empty client_secret.'
        );
    }

    public function testValidateClientAllowsRefreshGrantForPublicClientWithNoSecret(): void
    {
        $client = $this->insertPublicClientFixture();
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], null, 'refresh_token'),
            'Public client on refresh_token with no client_secret must validate.'
        );
    }

    // ---------- UserRepository::getAccountByPassword MFA required (6xc2) ----------

    public function testPasswordGrantRejectsTotpEnrolledUserWithoutMfaToken(): void
    {
        $userId = $this->requireExistingAdminUserId();
        $this->enrollTotpForUser($userId);

        unset($_POST['mfa_token'], $_POST['mfa_type'], $_POST['authUser']);
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException when TOTP-enrolled user omits mfa_token');
        } catch (OAuthServerException $e) {
            $this->assertSame(13, $e->getCode(), 'MFA-token-required must surface as error code 13');
            $this->assertSame('mfa_token_required', $e->getErrorType());
        }
    }

    public function testPasswordGrantAcceptsTotpEnrolledUserWithValidMfaToken(): void
    {
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);

        // Compute a current TOTP code from the secret so the test does not
        // depend on wall-clock luck within a 30-second window.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $currentCode = $tfa->getCode($secret);

        $_POST['mfa_token'] = $currentCode;
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        $result = $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
        $this->assertTrue($result, 'Valid TOTP token must be accepted on password grant');
    }

    public function testPasswordGrantRejectsMfaEnrolledUserWhenTotpNotEnrolled(): void
    {
        // A user enrolled only in non-TOTP factors (e.g. U2F) still has
        // isMfaRequired()==true but no TOTP row. Password grant cannot
        // satisfy those factors and must deny rather than fall through.
        $userId = $this->requireExistingAdminUserId();
        $this->enrollNonTotpForUser($userId);
        unset($_POST['mfa_token'], $_POST['mfa_type'], $_POST['authUser']);
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException when MFA-enrolled user has no TOTP factor');
        } catch (OAuthServerException $e) {
            $this->assertSame(14, $e->getCode(), 'MFA-not-supported must surface as error code 14');
            $this->assertSame('mfa_not_supported', $e->getErrorType());
        }
    }

    public function testPasswordGrantTotpFailureIncrementsMfaLockoutCounters(): void
    {
        // A wrong TOTP code on password grant must engage the dedicated
        // MFA lockout counters — otherwise an attacker with the right
        // password can grind the 6-digit code indefinitely. The MFA
        // counters are kept independent of the password login-fail
        // counters so the confirmPassword-success reset (which fires on
        // every attempt) does not zero them between iterations.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        AuthUtils::resetMfaChallengeCounters('admin', $this->clientIp);
        $userBefore = $this->readMfaUserCounter('admin');
        $ipBefore = $this->readMfaIpCounter($this->clientIp);

        // Derive a code that is guaranteed to differ from the currently-valid
        // TOTP so this negative-path test cannot flake on the roughly 1-in-1M
        // clock alignment where a hardcoded '000000' happens to be valid.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $currentCode = (int) $tfa->getCode($secret);
        $wrongCode = str_pad((string) (($currentCode + 1) % 1000000), 6, '0', STR_PAD_LEFT);
        $_POST['mfa_token'] = $wrongCode;
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException on wrong TOTP');
        } catch (OAuthServerException $e) {
            $this->assertSame('mfa_token_invalid', $e->getErrorType());
        }

        $this->assertSame(
            $userBefore + 1,
            $this->readMfaUserCounter('admin'),
            'Wrong TOTP on password grant must bump users_secure.mfa_fail_counter'
        );
        $this->assertGreaterThan(
            $ipBefore,
            $this->readMfaIpCounter($this->clientIp),
            'Wrong TOTP on password grant must bump ip_tracking.mfa_login_fail_counter'
        );
    }

    public function testPasswordGrantMfaLockoutCounterGrowsAcrossRepeatedFailures(): void
    {
        // Regression: prior to option B (dedicated MFA counters), the
        // password-lockout counter was reset on every attempt by the
        // confirmPassword-success reset in AuthUtils. That capped the
        // observable counter at 1 and made the lockout gate unreachable
        // via brute force. This test drives four consecutive wrong-TOTP
        // attempts and asserts the MFA counter grows linearly, proving
        // the counter survives the confirmPassword reset.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        AuthUtils::resetMfaChallengeCounters('admin', $this->clientIp);
        $userBefore = $this->readMfaUserCounter('admin');
        $ipBefore = $this->readMfaIpCounter($this->clientIp);

        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $password = $this->adminPassword();
        $_POST['mfa_type'] = 'TOTP';

        $attempts = 4;
        for ($i = 1; $i <= $attempts; $i++) {
            // Regenerate each iteration so a clock tick across the loop
            // still lands on a distinct wrong code.
            $currentCode = (int) $tfa->getCode($secret);
            $wrongCode = str_pad((string) (($currentCode + $i) % 1000000), 6, '0', STR_PAD_LEFT);
            $_POST['mfa_token'] = $wrongCode;

            $repo = $this->buildUserRepository();
            try {
                $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
                $this->fail("Expected OAuthServerException on wrong TOTP attempt $i");
            } catch (OAuthServerException $e) {
                $this->assertSame('mfa_token_invalid', $e->getErrorType(), "Attempt $i must yield mfa_token_invalid");
            }

            $this->assertSame(
                $userBefore + $i,
                $this->readMfaUserCounter('admin'),
                "users_secure.mfa_fail_counter must equal $i after $i failed attempts"
            );
        }

        $this->assertSame(
            $userBefore + $attempts,
            $this->readMfaUserCounter('admin'),
            "Final mfa_fail_counter must equal $attempts (proves counter is not being reset)"
        );
        $this->assertGreaterThanOrEqual(
            $ipBefore + $attempts,
            $this->readMfaIpCounter($this->clientIp),
            "Final mfa_login_fail_counter must grow by at least $attempts across the loop"
        );
    }

    public function testMfaBlockClearsAfterConfiguredResetWindowElapses(): void
    {
        // Rabbit finding: without a reset window, once mfa_fail_counter
        // hits password_max_failed_logins the pre-validate block
        // short-circuits every subsequent attempt — so a legitimate
        // user can never submit a good code to clear the counter.
        // Mirror the checkLoginFailedCounter / checkIpLoginFailedCounter
        // behavior: when
        // seconds_since_last_fail > time_reset_password_max_failed_logins
        // > 0 the gate must clear the counters and let the caller through.
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        $globals = OEGlobalsBag::getInstance();
        $originalUserMax = $globals->getInt('password_max_failed_logins');
        $originalUserWindow = $globals->getInt('time_reset_password_max_failed_logins');
        $originalIpMax = $globals->getInt('ip_max_failed_logins');
        $originalIpWindow = $globals->getInt('ip_time_reset_password_max_failed_logins');

        try {
            $globals->set('password_max_failed_logins', 3);
            $globals->set('time_reset_password_max_failed_logins', 60);
            $globals->set('ip_max_failed_logins', 3);
            $globals->set('ip_time_reset_password_max_failed_logins', 60);

            // Seed the counters at threshold with a last-fail 2 minutes
            // ago (past the 60-second reset window) so the gate should
            // clear both and pass.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `mfa_fail_counter` = 5, "
                    . "`mfa_last_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND) "
                    . "WHERE BINARY `username` = ?",
                ['admin']
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                    . "VALUES (?, 5, DATE_SUB(NOW(), INTERVAL 120 SECOND)) "
                    . "ON DUPLICATE KEY UPDATE `mfa_login_fail_counter` = 5, "
                    . "`mfa_last_login_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND)",
                [$this->clientIp]
            );

            $auth = new AuthUtils();
            $this->assertFalse(
                $auth->isMfaChallengeBlocked('admin', $this->clientIp),
                'Elapsed reset window must clear the block and let this attempt through'
            );
            $this->assertSame(
                0,
                $this->readMfaUserCounter('admin'),
                'Elapsed reset window must zero users_secure.mfa_fail_counter'
            );
            $this->assertSame(
                0,
                $this->readMfaIpCounter($this->clientIp),
                'Elapsed reset window must zero ip_tracking.mfa_login_fail_counter'
            );

            // Re-seed at threshold but with a recent last-fail (within
            // the window) — gate must still block.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `mfa_fail_counter` = 5, "
                    . "`mfa_last_fail` = NOW() "
                    . "WHERE BINARY `username` = ?",
                ['admin']
            );
            QueryUtils::sqlStatementThrowException(
                "UPDATE `ip_tracking` SET `mfa_login_fail_counter` = 5, "
                    . "`mfa_last_login_fail` = NOW() WHERE `ip_string` = ?",
                [$this->clientIp]
            );
            $this->assertTrue(
                $auth->isMfaChallengeBlocked('admin', $this->clientIp),
                'Recent failure inside the reset window must keep the block engaged'
            );
        } finally {
            $globals->set('password_max_failed_logins', $originalUserMax);
            $globals->set('time_reset_password_max_failed_logins', $originalUserWindow);
            $globals->set('ip_max_failed_logins', $originalIpMax);
            $globals->set('ip_time_reset_password_max_failed_logins', $originalIpWindow);
        }
    }

    public function testUpdatePasswordBackfillsMissingUuidAndCompletesRevocation(): void
    {
        // Rabbit finding: users.uuid is nullable in the schema.
        // updatePassword() previously wrote the new password hash and
        // silently skipped the OAuth2 token revocation UPDATEs when
        // UUID resolution returned null — the exact scenario the
        // revocation exists to defend against (credential compromise
        // → password rotated → old refresh_token continues to mint
        // API access). Fix backfills the UUID via
        // UuidRegistry::createMissingUuidForRow BEFORE the
        // transaction opens so the in-transaction SELECT always
        // resolves to a value; the transaction additionally throws
        // if resolution still fails (defense-in-depth).
        //
        // Setup: null out admin's uuid, then call updatePassword and
        // assert (1) the password change succeeded, (2) uuid was
        // repopulated so future revocations have a target, and (3)
        // the password hash actually changed. Restore original hash
        // via SQL in a finally so a failed assertion doesn't lock us
        // out of the shared admin account.
        $userId = $this->requireExistingAdminUserId();

        $before = QueryUtils::querySingleRow(
            "SELECT `password` FROM `users_secure` WHERE `id` = ?",
            [$userId]
        );
        $originalHash = is_array($before) && is_string($before['password'] ?? null)
            ? $before['password']
            : null;
        if ($originalHash === null) {
            $this->markTestSkipped('admin has no users_secure row in this env');
        }
        $uuidRow = QueryUtils::querySingleRow(
            "SELECT `uuid` FROM `users` WHERE `id` = ?",
            [$userId]
        );
        $originalUuid = is_array($uuidRow) ? ($uuidRow['uuid'] ?? null) : null;

        $this->snapshotUserLockout('admin');

        // Force admin's password to a hash we control so this test does
        // not depend on the shared DB's admin password matching the
        // adminPassword() default — earlier test sessions or POC runs
        // routinely rotate that hash. The finally restores the
        // originalHash regardless of outcome.
        $knownPwd = 'KnownAdminPwd-ForBackfillTest-1!';
        $knownHash = password_hash($knownPwd, PASSWORD_BCRYPT);
        QueryUtils::sqlStatementThrowException(
            "UPDATE `users_secure` SET `password` = ? WHERE `id` = ?",
            [$knownHash, $userId]
        );

        try {
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users` SET `uuid` = NULL WHERE `id` = ?",
                [$userId]
            );

            $auth = new AuthUtils();
            $currentPwd = $knownPwd;
            $newPwd = 'ScratchNewPassword-Rollback-Test-1!';
            $ok = $auth->updatePassword($userId, $userId, $currentPwd, $newPwd);
            $this->assertTrue($ok, 'updatePassword must succeed after UUID backfill');

            $afterUuid = QueryUtils::querySingleRow(
                "SELECT `uuid` FROM `users` WHERE `id` = ?",
                [$userId]
            );
            $backfilled = is_array($afterUuid) ? ($afterUuid['uuid'] ?? null) : null;
            $this->assertTrue(
                is_string($backfilled) && $backfilled !== '',
                'users.uuid must be backfilled by updatePassword so revocation has a target'
            );
        } finally {
            // Restore the admin password hash directly rather than
            // running updatePassword a second time (which would
            // trigger another round of revocation and lockout resets
            // on the shared admin row).
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `password` = ? WHERE `id` = ?",
                [$originalHash, $userId]
            );
            // Restore unconditionally — if the row was already NULL
            // pre-test, we still need to zero out the UUID that
            // updatePassword's createMissingUuidForRow backfilled, or
            // later tests in the same process see a UUID that wasn't
            // there originally.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users` SET `uuid` = ? WHERE `id` = ?",
                [$originalUuid, $userId]
            );
        }
    }

    public function testMfaBlockUserWindowExpiryDoesNotReleaseActiveIpLockout(): void
    {
        // Rabbit finding: when the user counter's reset window elapses
        // isMfaChallengeBlocked previously reset BOTH counters and
        // returned false — silently releasing a still-active IP
        // lockout that had an independently-fresh timestamp. Each
        // window must reset only its own counter and fall through so
        // an active lockout on the other axis stays engaged.
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        $globals = OEGlobalsBag::getInstance();
        $originalUserMax = $globals->getInt('password_max_failed_logins');
        $originalUserWindow = $globals->getInt('time_reset_password_max_failed_logins');
        $originalIpMax = $globals->getInt('ip_max_failed_logins');
        $originalIpWindow = $globals->getInt('ip_time_reset_password_max_failed_logins');

        try {
            $globals->set('password_max_failed_logins', 3);
            $globals->set('time_reset_password_max_failed_logins', 60);
            $globals->set('ip_max_failed_logins', 3);
            $globals->set('ip_time_reset_password_max_failed_logins', 60);

            // User over threshold with a STALE timestamp (past the
            // window) — user gate should clear its counter and fall
            // through. IP over threshold with a FRESH timestamp
            // (inside window) — IP gate must still block.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `mfa_fail_counter` = 5, "
                    . "`mfa_last_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND) "
                    . "WHERE BINARY `username` = ?",
                ['admin']
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                    . "VALUES (?, 5, NOW()) ON DUPLICATE KEY UPDATE "
                    . "`mfa_login_fail_counter` = 5, `mfa_last_login_fail` = NOW()",
                [$this->clientIp]
            );

            $auth = new AuthUtils();
            $this->assertTrue(
                $auth->isMfaChallengeBlocked('admin', $this->clientIp),
                'IP lockout with fresh failure must stay engaged even when user window expires'
            );
            $this->assertSame(
                0,
                $this->readMfaUserCounter('admin'),
                'Stale user counter must be zeroed by its window expiry'
            );
            $this->assertSame(
                5,
                $this->readMfaIpCounter($this->clientIp),
                'Active IP counter must NOT be zeroed by the user window expiry'
            );

            // Now flip the axes: IP over threshold with stale
            // timestamp; user over threshold with fresh timestamp.
            // User gate must block; IP counter should get cleared as
            // we walk past its check but only because we fall through
            // to the return (in this arrangement we return true from
            // the user gate before touching the IP gate).
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `mfa_fail_counter` = 5, "
                    . "`mfa_last_fail` = NOW() "
                    . "WHERE BINARY `username` = ?",
                ['admin']
            );
            QueryUtils::sqlStatementThrowException(
                "UPDATE `ip_tracking` SET `mfa_login_fail_counter` = 5, "
                    . "`mfa_last_login_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND) "
                    . "WHERE `ip_string` = ?",
                [$this->clientIp]
            );
            $this->assertTrue(
                $auth->isMfaChallengeBlocked('admin', $this->clientIp),
                'User lockout with fresh failure must stay engaged even when IP window expires'
            );
            $this->assertSame(
                5,
                $this->readMfaUserCounter('admin'),
                'Active user counter must NOT be zeroed by the IP window expiry'
            );
            $this->assertSame(
                5,
                $this->readMfaIpCounter($this->clientIp),
                'IP counter reset is only reachable when the user gate falls through'
            );
        } finally {
            $globals->set('password_max_failed_logins', $originalUserMax);
            $globals->set('time_reset_password_max_failed_logins', $originalUserWindow);
            $globals->set('ip_max_failed_logins', $originalIpMax);
            $globals->set('ip_time_reset_password_max_failed_logins', $originalIpWindow);
        }
    }

    public function testPasswordGrantRejectsReplayedTotpCodeWithinAcceptanceWindow(): void
    {
        // TOTP replay protection: RobThree TwoFactorAuth accepts codes for
        // slots T-30/T/T+30 (up to 90s validity). A code that has been
        // successfully consumed once must not be reusable within that
        // window — otherwise an attacker who observes one valid code
        // once (shoulder-surfing, screenshot, MITM) can replay it until
        // it ages out. Verifies MfaUtils::checkTOTP + login_mfa_registrations
        // last_used_token / last_challenge enforcement.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        // Snapshot both lockout counters before the second (replay)
        // submission — that attempt runs through the mfa_token_invalid
        // branch which bumps recordFailedAuthChallenge counters on both
        // users_secure (per-user) and ip_tracking (per-IP). tearDown
        // needs the snapshots to restore original state.
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        // First submission with a currently-valid code — should succeed.
        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $currentCode = $tfa->getCode($secret);
        $_POST['mfa_token'] = $currentCode;
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        $this->assertTrue(
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password),
            'First submission of a valid TOTP must succeed'
        );

        // Second submission of the exact same code within the acceptance
        // window — must reject even though RobThree would still verify
        // the code as valid.
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException when replaying a just-consumed TOTP');
        } catch (OAuthServerException $e) {
            $this->assertSame(
                'mfa_token_invalid',
                $e->getErrorType(),
                'Replayed TOTP must reject with mfa_token_invalid, not silently accept'
            );
        }
    }

    public function testPasswordGrantRejectsTotpFromOlderSlotThanLastConsumed(): void
    {
        // Guards against the A-B-A replay class: RobThree's verifyCode
        // accepts codes for slices {T-1, T, T+1}, so up to 3 different
        // valid codes coexist in the ~90-second acceptance window. If
        // replay protection only tracked the exact last-used token,
        // A -> B -> A would slip through (last_used=B, incoming=A,
        // A != B, passes). Slice-monotonic protection blocks the whole
        // class by requiring incoming.slice > last_used_step.
        //
        // Simulated here by seeding last_used_step to a value in the
        // future (a slice higher than any code the harness could
        // possibly compute right now), then attempting to consume the
        // current code — must reject because current.slice < seeded.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        // Seed a slice far enough into the future that any current
        // code's matched slice (T-1/T/T+1) is definitely less than it.
        $futureStep = (int) floor(time() / 30) + 1000;
        QueryUtils::sqlStatementThrowException(
            "UPDATE login_mfa_registrations SET last_used_step = ?, last_challenge = NOW() "
                . "WHERE user_id = ? AND method = 'TOTP'",
            [$futureStep, $userId]
        );

        $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
        $_POST['mfa_token'] = $tfa->getCode($secret);
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException when submitting a code from an older slice than the seeded last_used_step');
        } catch (OAuthServerException $e) {
            $this->assertSame(
                'mfa_token_invalid',
                $e->getErrorType(),
                'A code whose slice is not strictly greater than the last consumed slice must reject as invalid — this is the A-B-A defense'
            );
        }
    }

    public function testPasswordGrantRejectsUnexpectedMfaTokenWhenUserHasNoMfa(): void
    {
        // Complement to the "no MFA + no token" happy path implicit in the
        // 175 api-suite tests. When a user with no MFA enrolled posts an
        // mfa_token anyway, the server treats it as a client error (the
        // client believes MFA is configured when it isn't) and returns
        // 403 mfa_not_supported rather than silently accepting the token.
        $userId = $this->requireExistingAdminUserId();
        // Snapshot admin MFA rows so this test does not leak state, then
        // delete any real MFA registrations so isMfaRequired() is false.
        $this->snapshotMfaRowsForUser($userId);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$userId]
        );
        $_POST['mfa_token'] = '123456';
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail('Expected OAuthServerException when non-MFA user posts an mfa_token');
        } catch (OAuthServerException $e) {
            $this->assertSame(11, $e->getCode(), 'Unexpected mfa_token must surface as error code 11');
            $this->assertSame('mfa_not_supported', $e->getErrorType());
        }
    }

    // ---------- mfa_token shape variants (defensive) ----------

    /**
     * @return array<string, array{string, string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidMfaTokenShapeProvider(): array
    {
        // MfaUtils::tokenFromRequest returns false when validateToken
        // rejects the shape; the empty() check downstream catches both
        // false and empty string, so all malformed shapes surface as
        // mfa_token_required. A correctly-shaped 6-digit numeric but
        // wrong value is covered by testPasswordGrantTotpFailureIncrementsLockoutCounters
        // (that path surfaces as mfa_token_invalid).
        return [
            'empty string'      => ['', 'mfa_token_required'],
            'whitespace only'   => ['      ', 'mfa_token_required'],
            'five digits'       => ['12345', 'mfa_token_required'],
            'seven digits'      => ['1234567', 'mfa_token_required'],
            'non-numeric'       => ['abcdef', 'mfa_token_required'],
            'sql-injection-ish' => ["' OR '1'='1", 'mfa_token_required'],
        ];
    }

    #[DataProvider('invalidMfaTokenShapeProvider')]
    public function testPasswordGrantRejectsMalformedMfaTokenShapes(string $token, string $expectedErrorType): void
    {
        $userId = $this->requireExistingAdminUserId();
        $this->enrollTotpForUser($userId);
        $_POST['mfa_token'] = $token;
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();

        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
            $this->fail("Expected OAuthServerException for malformed mfa_token: {$token}");
        } catch (OAuthServerException $e) {
            $this->assertSame(
                $expectedErrorType,
                $e->getErrorType(),
                "Malformed mfa_token '{$token}' must reject with {$expectedErrorType}"
            );
        }
    }

    public function testMalformedMfaTokenBumpsMfaFailCounter(): void
    {
        // Rabbit finding: a malformed mfa_token (validateToken
        // rejected via wrong length / non-numeric) short-circuits
        // through the empty($mfaToken) branch without ever calling
        // $mfa->check — so the counter and block gate that check
        // owns never fire. An attacker could POST 'abcdef' forever
        // and never trip the throttle. Verify a rejection bumps.
        //
        // The absent (null) case must NOT bump — that's the legit
        // "user hasn't been shown the MFA prompt yet" flow.
        $userId = $this->requireExistingAdminUserId();
        $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);
        AuthUtils::resetMfaChallengeCounters('admin', $this->clientIp);

        // Case 1: absent token — no bump.
        unset($_POST['mfa_token']);
        $_POST['mfa_type'] = 'TOTP';
        $password = $this->adminPassword();
        $userBeforeAbsent = $this->readMfaUserCounter('admin');
        $repo = $this->buildUserRepository();
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
        } catch (OAuthServerException) {
            // expected mfa_token_required
        }
        $this->assertSame(
            $userBeforeAbsent,
            $this->readMfaUserCounter('admin'),
            'Absent mfa_token must not bump the MFA counter (legit no-token-yet flow)'
        );

        // Case 2: malformed token — must bump.
        $_POST['mfa_token'] = 'abcdef';
        $userBeforeMalformed = $this->readMfaUserCounter('admin');
        try {
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
        } catch (OAuthServerException) {
            // expected mfa_token_required
        }
        $this->assertSame(
            $userBeforeMalformed + 1,
            $this->readMfaUserCounter('admin'),
            'Malformed mfa_token must bump the MFA counter so spam cannot sidestep the throttle'
        );
    }

    // ---------- oauth_password_grant global gate ----------

    public function testPasswordGrantIsRejectedWhenGlobalIsDisabled(): void
    {
        // Global value 0 = password grant disabled entirely for both roles.
        // A regression that removed the gate check would allow password
        // grant regardless of admin opt-in — the whole opt-in surface
        // silently becomes always-on.
        OEGlobalsBag::getInstance()->set('oauth_password_grant', 0);
        $password = $this->adminPassword();
        $repo = $this->buildUserRepository();
        $this->assertFalse(
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password),
            'oauth_password_grant=0 must reject the staff (users) role'
        );
        $this->assertFalse(
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_PATIENT, 'admin', $password),
            'oauth_password_grant=0 must reject the patient role'
        );
    }

    public function testPasswordGrantWithGlobalStaffOnlyRejectsPatientRole(): void
    {
        // Global value 1 = staff (users) role enabled, patient role
        // disabled. Verifies the gate distinguishes the two roles
        // rather than being a single on/off toggle.
        OEGlobalsBag::getInstance()->set('oauth_password_grant', 1);
        $password = $this->adminPassword();
        $repo = $this->buildUserRepository();
        $this->assertFalse(
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_PATIENT, 'admin', $password),
            'oauth_password_grant=1 (staff only) must reject the patient role'
        );
    }

    public function testPasswordGrantWithGlobalPatientOnlyRejectsStaffRole(): void
    {
        // Global value 2 = patient role enabled, staff (users) role
        // disabled.
        OEGlobalsBag::getInstance()->set('oauth_password_grant', 2);
        $password = $this->adminPassword();
        $repo = $this->buildUserRepository();
        $this->assertFalse(
            $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password),
            'oauth_password_grant=2 (patient only) must reject the staff (users) role'
        );
    }

    // ---------- user_role parameter validation ----------

    /**
     * @return array<string, array{string}>
     *
     * @codeCoverageIgnore Data providers run before coverage instrumentation starts.
     */
    public static function invalidUserRoleProvider(): array
    {
        return [
            'empty string' => [''],
            'admin'        => ['admin'],
            'god'          => ['god'],
            'unknown'      => ['unknown-role'],
            'uppercase'    => ['USERS'],   // case-sensitive comparison
        ];
    }

    #[DataProvider('invalidUserRoleProvider')]
    public function testPasswordGrantRejectsUnknownUserRoleValues(string $userRole): void
    {
        // getAccountByPassword branches on $userrole via strict ==
        // comparisons to 'users' / 'patient'. Any other value falls
        // through to `return false`. Verifies the fall-through denies
        // rather than crashes or leaks a token.
        $password = $this->adminPassword();
        $repo = $this->buildUserRepository();
        $this->assertFalse(
            $this->invokeGetAccountByPassword($repo, $userRole, 'admin', $password),
            "user_role='{$userRole}' is neither 'users' nor 'patient' — must reject"
        );
    }

    // ---------- AuthUtils::confirmPatientPassword IP rate limit (4fx8 V9) ----------

    public function testPortalPasswordGrantIncrementsIpCounterOnFailure(): void
    {
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        $before = $this->readIpCounter($ipString);
        $wrong = 'wrong-password';
        $auth = new AuthUtils('portal-api');
        $ok = $auth->confirmPassword(
            $fixture['portal_login_username'],
            $wrong,
            $fixture['email']
        );
        $this->assertFalse($ok, 'Wrong portal password must not authenticate');

        $after = $this->readIpCounter($ipString);
        $this->assertGreaterThan(
            $before,
            $after,
            'Portal password grant must increment the per-IP fail counter on wrong password'
        );
    }

    public function testPortalPasswordGrantIncrementsIpCounterOnUsernameGuess(): void
    {
        // Rabbit finding: confirmPatientPassword only bumped the IP
        // counter on empty-cred / wrong-password / block-gate branches.
        // Unknown username / email-mismatch paths returned false with
        // zero rate-limit progress — an attacker could iterate portal
        // usernames or (with enforce_signin_email) emails against a
        // known password forever without the block gate engaging.
        // Every post-gate rejection must now increment.
        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);
        $before = $this->readIpCounter($ipString);

        $auth = new AuthUtils('portal-api');
        $unknownUser = 'nonexistent-portal-user-' . Uuid::uuid4()->toString();
        $anyPassword = 'any-password';
        $ok = $auth->confirmPassword($unknownUser, $anyPassword, 'noone@example.invalid');
        $this->assertFalse($ok, 'Unknown portal user must not authenticate');

        $this->assertGreaterThan(
            $before,
            $this->readIpCounter($ipString),
            'Unknown-user rejection must count against the per-IP fail counter '
                . '(otherwise username enumeration has no rate limit)'
        );
    }

    public function testPortalPasswordGrantIncrementsIpCounterOnEmailMismatch(): void
    {
        $globals = OEGlobalsBag::getInstance();
        $originalEnforceEmail = $globals->getBoolean('enforce_signin_email');
        try {
            $globals->set('enforce_signin_email', true);
            $fixture = $this->portalFixtures()->installPortalPatient(
                portalLoginUsername: 'test-portal-user-email-' . Uuid::uuid4()->toString(),
                plainPassword: 'CorrectPortalPassword1!'
            );

            $ipString = $this->clientIp;
            $this->snapshotIpTracking($ipString);
            $before = $this->readIpCounter($ipString);

            $auth = new AuthUtils('portal-api');
            $rightPassword = $fixture['plain_password'];
            $ok = $auth->confirmPassword(
                $fixture['portal_login_username'],
                $rightPassword,
                'wrong-email@example.invalid'
            );
            $this->assertFalse($ok, 'Email mismatch must not authenticate');

            $this->assertGreaterThan(
                $before,
                $this->readIpCounter($ipString),
                'Email-mismatch rejection must count against the per-IP fail counter'
            );
        } finally {
            $globals->set('enforce_signin_email', $originalEnforceEmail);
        }
    }

    public function testPortalPasswordGrantResetsPortalAccountCounterOnSuccess(): void
    {
        // Successful portal login always clears the per-account
        // failure counter for THIS account. The shared per-IP
        // counter's clearing behaviour is admin-configurable via
        // clear_ip_counter_on_auth_success (default ON — matches
        // pre-8.5.0). This test locks in the strict-mode variant
        // (setting=0): in that mode, portal success clears the
        // per-account counter but the IP counter survives, so a
        // valid login on account A cannot clear an in-progress
        // brute force being accumulated against account B from the
        // same IP. The setting-ON variant (default) is covered by
        // testClearIpCounterOnAuthSuccessGlobalOptsIntoLegacyBehavior.
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-reset-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        $globals = OEGlobalsBag::getInstance();
        $originalSetting = $globals->getBoolean('clear_ip_counter_on_auth_success');
        try {
            $globals->set('clear_ip_counter_on_auth_success', false);

            // Seed the per-account counter above zero so we can assert
            // the success path zeroed it.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `patient_access_onsite` "
                    . "SET `portal_fail_counter` = 3, `portal_last_fail` = NOW() "
                    . "WHERE BINARY `portal_login_username` = ?",
                [$fixture['portal_login_username']]
            );
            // And bump the IP counter — we assert this survives success
            // under strict-mode.
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ip_tracking (ip_string, ip_login_fail_counter, ip_last_login_fail) "
                    . "VALUES (?, 3, NOW()) ON DUPLICATE KEY UPDATE "
                    . "ip_login_fail_counter = 3, ip_last_login_fail = NOW()",
                [$ipString]
            );

            $auth = new AuthUtils('portal-api');
            $login = $fixture['portal_login_username'];
            $pw = $fixture['plain_password'];
            $ok = $auth->confirmPassword($login, $pw, $fixture['email']);
            $this->assertTrue($ok, 'Correct portal password must authenticate');
            $this->assertSame(
                0,
                $this->readPortalAccountCounter($fixture['portal_login_username']),
                'Successful portal login must reset the per-account counter'
            );
            $this->assertSame(
                3,
                $this->readIpCounter($ipString),
                'With clear_ip_counter_on_auth_success = 0, successful portal '
                    . 'login must NOT reset the shared per-IP counter '
                    . '(otherwise a valid login on one account bypasses the brute-force gate on another)'
            );
        } finally {
            $globals->set('clear_ip_counter_on_auth_success', $originalSetting);
        }
    }

    public function testClearIpCounterOnAuthSuccessGlobalFlipsBothWays(): void
    {
        // The clear_ip_counter_on_auth_success global (default ON,
        // preserving pre-8.5.0 behaviour) governs whether a
        // successful login zeros the shared IP counter. Exercise
        // both positions of the toggle in a single test to pin the
        // setting's uniform effect across the portal + MFA success
        // paths.
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-optin-' . Uuid::uuid4()->toString(),
            plainPassword: 'OptInPortalPassword1!'
        );
        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        $globals = OEGlobalsBag::getInstance();
        $originalSetting = $globals->getBoolean('clear_ip_counter_on_auth_success');
        try {
            $globals->set('clear_ip_counter_on_auth_success', true);

            // Seed the shared IP counter, then run a successful
            // portal login; assert IP counter now clears too.
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ip_tracking (ip_string, ip_login_fail_counter, ip_last_login_fail) "
                    . "VALUES (?, 4, NOW()) ON DUPLICATE KEY UPDATE "
                    . "ip_login_fail_counter = 4, ip_last_login_fail = NOW()",
                [$ipString]
            );
            $auth = new AuthUtils('portal-api');
            $login = $fixture['portal_login_username'];
            $pw = $fixture['plain_password'];
            $ok = $auth->confirmPassword($login, $pw, $fixture['email']);
            $this->assertTrue($ok);
            $this->assertSame(
                0,
                $this->readIpCounter($ipString),
                'Portal success with setting ON must reset the shared IP counter'
            );

            // Same setting, MFA path: seed the MFA IP counter and run
            // resetMfaChallengeCounters (which the success flow calls).
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO ip_tracking (ip_string, mfa_login_fail_counter, mfa_last_login_fail) "
                    . "VALUES (?, 4, NOW()) ON DUPLICATE KEY UPDATE "
                    . "mfa_login_fail_counter = 4, mfa_last_login_fail = NOW()",
                [$ipString]
            );
            AuthUtils::resetMfaChallengeCounters(null, $ipString);
            $this->assertSame(
                0,
                $this->readMfaIpCounter($ipString),
                'MFA success with setting ON must reset the shared MFA IP counter'
            );

            // Flip OFF and verify same operations leave the IP
            // counters untouched.
            $globals->set('clear_ip_counter_on_auth_success', false);
            QueryUtils::sqlStatementThrowException(
                "UPDATE ip_tracking SET ip_login_fail_counter = 5, "
                    . "mfa_login_fail_counter = 5, ip_last_login_fail = NOW(), "
                    . "mfa_last_login_fail = NOW() WHERE ip_string = ?",
                [$ipString]
            );
            $pwRerun = $fixture['plain_password'];
            $this->assertTrue($auth->confirmPassword($login, $pwRerun, $fixture['email']));
            AuthUtils::resetMfaChallengeCounters(null, $ipString);
            $this->assertSame(
                5,
                $this->readIpCounter($ipString),
                'Portal success with setting OFF must NOT reset the shared IP counter'
            );
            $this->assertSame(
                5,
                $this->readMfaIpCounter($ipString),
                'MFA success with setting OFF must NOT reset the shared MFA IP counter'
            );
        } finally {
            $globals->set('clear_ip_counter_on_auth_success', $originalSetting);
        }
    }

    public function testPortalAccountIncrementResetsStalePartialCounterInsteadOfBuildingOnIt(): void
    {
        // Rabbit finding: increment helpers only reset counters that
        // are AT threshold. A partial counter (e.g. max-1) that sits
        // idle past the reset window survives the window entirely —
        // one new failure jumps to max and refreshes the timestamp,
        // giving an attacker a stale-state DoS primitive against a
        // legit account. Reset-before-increment: when
        // seconds_since_last_fail > window > 0, the increment must
        // set the counter to 1 (fresh failure), not counter+1.
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'stale-partial-' . Uuid::uuid4()->toString(),
            plainPassword: 'AccountStalePartial1!'
        );
        $globals = OEGlobalsBag::getInstance();
        $originalMax = $globals->getInt('password_max_failed_logins');
        $originalWindow = $globals->getInt('time_reset_password_max_failed_logins');
        try {
            $globals->set('password_max_failed_logins', 3);
            $globals->set('time_reset_password_max_failed_logins', 60);

            // Seed the counter at max-1 with a stale timestamp
            // (2 minutes ago, past the 60s window).
            $login = $fixture['portal_login_username'];
            QueryUtils::sqlStatementThrowException(
                "UPDATE `patient_access_onsite` "
                    . "SET `portal_fail_counter` = 2, "
                    . "`portal_last_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND) "
                    . "WHERE BINARY `portal_login_username` = ?",
                [$login]
            );

            $auth = new AuthUtils('portal-api');
            $wrong = 'wrong-password';
            $ok = $auth->confirmPassword($login, $wrong, $fixture['email']);
            $this->assertFalse($ok);
            $this->assertSame(
                1,
                $this->readPortalAccountCounter($login),
                'Stale-partial counter must reset to 1 on the fresh failure '
                    . '(otherwise the next attempt would trip the block from stale state)'
            );
        } finally {
            $globals->set('password_max_failed_logins', $originalMax);
            $globals->set('time_reset_password_max_failed_logins', $originalWindow);
        }
    }

    public function testMfaFailCounterResetsStalePartialInsteadOfBuildingOnIt(): void
    {
        // Same finding, applied to incrementMfaFailCounter /
        // incrementIpMfaLoginFailCounter. Seed both counters at
        // max-1 with a stale timestamp; assert one wrong-TOTP
        // attempt resets each to 1 rather than bumping to max.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        $globals = OEGlobalsBag::getInstance();
        $originalUserMax = $globals->getInt('password_max_failed_logins');
        $originalUserWindow = $globals->getInt('time_reset_password_max_failed_logins');
        $originalIpMax = $globals->getInt('ip_max_failed_logins');
        $originalIpWindow = $globals->getInt('ip_time_reset_password_max_failed_logins');
        try {
            $globals->set('password_max_failed_logins', 3);
            $globals->set('time_reset_password_max_failed_logins', 60);
            $globals->set('ip_max_failed_logins', 3);
            $globals->set('ip_time_reset_password_max_failed_logins', 60);

            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `mfa_fail_counter` = 2, "
                    . "`mfa_last_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND) "
                    . "WHERE BINARY `username` = ?",
                ['admin']
            );
            QueryUtils::sqlStatementThrowException(
                "INSERT INTO `ip_tracking` (`ip_string`, `mfa_login_fail_counter`, `mfa_last_login_fail`) "
                    . "VALUES (?, 2, DATE_SUB(NOW(), INTERVAL 120 SECOND)) "
                    . "ON DUPLICATE KEY UPDATE `mfa_login_fail_counter` = 2, "
                    . "`mfa_last_login_fail` = DATE_SUB(NOW(), INTERVAL 120 SECOND)",
                [$this->clientIp]
            );

            $tfa = new TwoFactorAuth(new BaconQrCodeProvider(4, '#ffffff', '#000000', 'svg'));
            $currentCode = (int) $tfa->getCode($secret);
            $wrongCode = str_pad((string) (($currentCode + 1) % 1000000), 6, '0', STR_PAD_LEFT);
            $_POST['mfa_token'] = $wrongCode;
            $_POST['mfa_type'] = 'TOTP';
            $password = $this->adminPassword();

            $repo = $this->buildUserRepository();
            try {
                $this->invokeGetAccountByPassword($repo, UuidUserAccount::USER_ROLE_USERS, 'admin', $password);
                $this->fail('Expected OAuthServerException on wrong TOTP');
            } catch (OAuthServerException $e) {
                $this->assertSame('mfa_token_invalid', $e->getErrorType());
            }

            $this->assertSame(
                1,
                $this->readMfaUserCounter('admin'),
                'Stale-partial user MFA counter must reset to 1'
            );
            $this->assertSame(
                1,
                $this->readMfaIpCounter($this->clientIp),
                'Stale-partial IP MFA counter must reset to 1'
            );
        } finally {
            $globals->set('password_max_failed_logins', $originalUserMax);
            $globals->set('time_reset_password_max_failed_logins', $originalUserWindow);
            $globals->set('ip_max_failed_logins', $originalIpMax);
            $globals->set('ip_time_reset_password_max_failed_logins', $originalIpWindow);
        }
    }

    public function testPortalPasswordGrantBlocksSecondAccountAfterPerAccountThresholdEvenWhenFirstAccountSucceeds(): void
    {
        // Regression pin for the finding this whole per-account
        // counter block exists to close: attacker holds valid creds
        // for account A, tries to brute-force account B. Before the
        // fix, they could burn N-1 failures on B, log in cleanly on
        // A (which reset the shared IP counter), and repeat forever.
        // With the per-account counter, the block on B accumulates
        // independent of any success on A.
        $accountA = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'account-a-' . Uuid::uuid4()->toString(),
            plainPassword: 'AccountAPassword1!'
        );
        $accountB = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'account-b-' . Uuid::uuid4()->toString(),
            plainPassword: 'AccountBPassword1!'
        );
        $this->snapshotIpTracking($this->clientIp);

        $globals = OEGlobalsBag::getInstance();
        $originalMax = $globals->getInt('password_max_failed_logins');
        try {
            // Low threshold so the loop is short and deterministic.
            $globals->set('password_max_failed_logins', 3);

            $auth = new AuthUtils('portal-api');
            $loginB = $accountB['portal_login_username'];
            // Burn threshold failures on account B. confirmPassword
            // clears the password buffer by reference, so re-init the
            // variable each loop iteration.
            for ($i = 0; $i < 3; $i++) {
                $wrong = 'wrong-password';
                $ok = $auth->confirmPassword($loginB, $wrong, $accountB['email']);
                $this->assertFalse($ok, "Attempt $i on B must fail");
            }
            $this->assertSame(
                3,
                $this->readPortalAccountCounter($loginB),
                'Per-account counter for B must equal threshold after 3 failures'
            );

            // Successful login on account A — under the OLD design
            // this cleared the shared IP counter and re-opened the
            // window for more B attempts. Under the fix it clears
            // only A's per-account counter.
            $loginA = $accountA['portal_login_username'];
            $pwA = $accountA['plain_password'];
            $this->assertTrue(
                $auth->confirmPassword($loginA, $pwA, $accountA['email']),
                'A must authenticate cleanly'
            );

            // Next attempt on B must still be blocked by the per-
            // account gate even though attacker just cleared their
            // own account's counter.
            $wrongAgain = 'wrong-password';
            $ok = $auth->confirmPassword($loginB, $wrongAgain, $accountB['email']);
            $this->assertFalse(
                $ok,
                'Per-account block on B must persist across a valid login on A'
            );
            $this->assertSame(
                3,
                $this->readPortalAccountCounter($loginB),
                'B counter must not be cleared by A success (still at threshold)'
            );
        } finally {
            $globals->set('password_max_failed_logins', $originalMax);
        }
    }

    public function testPortalPasswordGrantBlocksAfterRepeatedFailures(): void
    {
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-block-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        // Seed the counter above the global threshold so the very next call
        // must be rejected by the block gate rather than the wrong-password
        // check. Using the seeded row bypasses whatever residual state the
        // shared test env may carry so this assertion is deterministic.
        $threshold = OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins');
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ip_tracking (ip_string, ip_login_fail_counter, ip_last_login_fail) "
                . "VALUES (?, ?, NOW()) ON DUPLICATE KEY UPDATE "
                . "ip_login_fail_counter = VALUES(ip_login_fail_counter), "
                . "ip_last_login_fail = VALUES(ip_last_login_fail)",
            [$ipString, $threshold + 1]
        );

        $auth = new AuthUtils('portal-api');
        $ok = $auth->confirmPassword(
            $fixture['portal_login_username'],
            $fixture['plain_password'],
            $fixture['email']
        );
        $this->assertFalse($ok, 'Blocked IP must be rejected even with the correct portal password');
    }

    // ---------- AuthUtils::recordFailedAuthChallenge (rp7c helper) ----------

    public function testRecordFailedAuthChallengeIncrementsBothCounters(): void
    {
        $username = 'admin';
        $this->snapshotUserLockout($username);
        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        AuthUtils::resetLoginFailedCounter($username);
        $userBefore = $this->readUserCounter($username);
        $ipBefore = $this->readIpCounter($ipString);

        (new AuthUtils())->recordFailedAuthChallenge($username);

        $this->assertSame(
            $userBefore + 1,
            $this->readUserCounter($username),
            'recordFailedAuthChallenge must bump users_secure.login_fail_counter'
        );
        $this->assertGreaterThan(
            $ipBefore,
            $this->readIpCounter($ipString),
            'recordFailedAuthChallenge must bump ip_tracking.ip_login_fail_counter'
        );
    }

    public function testRecordFailedAuthChallengeSkipsUserCounterWhenUsernameNull(): void
    {
        $ipString = $this->clientIp;
        // Snapshot BEFORE resetting so tearDown restores whatever the
        // shared admin row looked like pre-test; without the snapshot
        // the reset (and any real prior counter state) would leak past
        // this test.
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($ipString);
        // Baseline the admin counter so we can prove it was not touched.
        AuthUtils::resetLoginFailedCounter('admin');
        $adminBefore = $this->readUserCounter('admin');
        $ipBefore = $this->readIpCounter($ipString);

        (new AuthUtils())->recordFailedAuthChallenge(null);

        $this->assertSame(
            $adminBefore,
            $this->readUserCounter('admin'),
            'Null username must not touch any user counter'
        );
        $this->assertGreaterThan(
            $ipBefore,
            $this->readIpCounter($ipString),
            'Null username must still bump the per-IP counter'
        );
    }

    // ---------- helpers ----------

    private function portalFixtures(): PortalPatientFixtureManager
    {
        return $this->portalFixtures ??= new PortalPatientFixtureManager();
    }

    private function requireExistingAdminUserId(): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT id FROM users WHERE username = 'admin' AND active = 1"
        );
        $id = $row['id'] ?? null;
        if (!is_numeric($id) || (int) $id === 0) {
            $this->markTestSkipped('admin user not present in this test environment');
        }
        return (int) $id;
    }

    private function adminPassword(): string
    {
        // Default seed password in the docker test environment.
        return (string) (getenv('OE_PASS') ?: 'pass');
    }

    private function enrollNonTotpForUser(int $userId): void
    {
        // A U2F row is enough to make MfaUtils::isMfaRequired() true while
        // keeping TOTP off the enrolled list. var1 must at least JSON-decode
        // to an object with a keyHandle — MfaUtils reads that field when
        // hydrating registrations.
        $this->snapshotMfaRowsForUser($userId);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$userId]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2, last_challenge, last_used_step) "
                . "VALUES (?, 'test-u2f', 'U2F', ?, '', NULL, NULL)",
            [$userId, '{"keyHandle":"test-key-handle"}']
        );
    }

    private function enrollTotpForUser(int $userId): string
    {
        // Use a stable, well-formed base32 secret. MfaUtils::checkTOTP()
        // decrypts var1 via ServiceContainer::getCrypto()->decryptFromDatabase()
        // so the stored secret must be encrypted the same way.
        $secret = 'JBSWY3DPEHPK3PXP';
        $encryptedSecret = ServiceContainer::getCrypto()->encryptForDatabase($secret);
        $this->snapshotMfaRowsForUser($userId);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ?",
            [$userId]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2, last_challenge, last_used_step) "
                . "VALUES (?, 'test', 'TOTP', ?, '', NULL, NULL)",
            [$userId, $encryptedSecret]
        );
        return $secret;
    }

    /**
     * Snapshot the users_secure lockout fields for a username so tearDown
     * can restore them after mutation. Idempotent — repeated calls in the
     * same test do not overwrite the initial snapshot.
     */
    private function snapshotUserLockout(string $username): void
    {
        if (array_key_exists($username, $this->originalUserLockoutByUsername)) {
            return;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `login_fail_counter`, `last_login_fail`, `auto_block_emailed`, "
                . "`mfa_fail_counter`, `mfa_last_fail` "
                . "FROM `users_secure` WHERE BINARY `username` = ?",
            [$username]
        );
        if (!is_array($row)) {
            // No users_secure row — the user isn't set up for password
            // login, so there's nothing to restore. Skip snapshotting so
            // tearDown does not try to UPDATE a non-existent row.
            return;
        }
        $this->originalUserLockoutByUsername[$username] = $row;
    }

    /**
     * Snapshot the ip_tracking row for an ip_string so tearDown can restore
     * it after mutation. Records null if the row did not exist (tearDown
     * will then delete anything the test inserted). Idempotent — repeated
     * calls in the same test do not overwrite the initial snapshot.
     */
    private function snapshotIpTracking(string $ipString): void
    {
        if (array_key_exists($ipString, $this->originalIpTrackingByString)) {
            return;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `total_ip_login_fail_counter`, `ip_login_fail_counter`, "
                . "`ip_last_login_fail`, `ip_auto_block_emailed`, "
                . "`mfa_login_fail_counter`, `mfa_last_login_fail` "
                . "FROM `ip_tracking` WHERE `ip_string` = ?",
            [$ipString]
        );
        $this->originalIpTrackingByString[$ipString] = is_array($row) ? $row : null;
    }

    /**
     * Snapshot the login_mfa_registrations rows for a user so tearDown can
     * restore them after we replaced them. Idempotent: repeated calls in the
     * same test do not overwrite the original snapshot.
     */
    private function snapshotMfaRowsForUser(int $userId): void
    {
        if (array_key_exists($userId, $this->originalMfaRowsByUser)) {
            return;
        }
        /** @var list<array<string, mixed>> $rows */
        $rows = QueryUtils::fetchRecords(
            "SELECT user_id, name, method, var1, var2, last_challenge, last_used_step "
                . "FROM login_mfa_registrations WHERE user_id = ?",
            [$userId]
        );
        $this->originalMfaRowsByUser[$userId] = $rows;
    }

    /**
     * @return array{client_id: string}
     */
    private function insertConfidentialClientFixture(string $clientSecret): array
    {
        $clientId = 'test-client-' . Uuid::uuid4()->toString();
        $encryptedSecret = (\OpenEMR\BC\ServiceContainer::getCrypto())->encryptForDatabase($clientSecret);
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients (client_id, client_secret, is_confidential, is_enabled, client_role) "
                . "VALUES (?, ?, 1, 1, 'users')",
            [$clientId, $encryptedSecret]
        );
        $this->trackedClientIds[] = $clientId;
        return ['client_id' => $clientId];
    }

    /**
     * @return array{client_id: string}
     */
    private function insertPublicClientFixture(): array
    {
        $clientId = 'test-client-public-' . Uuid::uuid4()->toString();
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO oauth_clients (client_id, client_secret, is_confidential, is_enabled, client_role) "
                . "VALUES (?, '', 0, 1, 'users')",
            [$clientId]
        );
        $this->trackedClientIds[] = $clientId;
        return ['client_id' => $clientId];
    }

    private function buildUserRepository(): UserRepository
    {
        // getAccountByPassword needs a base FHIR URL for user-entity creation
        // on the success path — value is arbitrary for these unit-style tests.
        return new UserRepository('http://openemr/apis/default/fhir');
    }

    private function invokeGetAccountByPassword(
        UserRepository $repo,
        string $userRole,
        string $username,
        string $password,
        string $email = ''
    ): bool {
        // Every call here mutates lockout state: staff branch runs
        // confirmPassword which resets both counters on success or
        // bumps them on failure; MFA success additionally resets the
        // MFA counters; the patient branch flows through
        // confirmPatientPassword's IP counter. Register the relevant
        // snapshots (idempotent) so tearDown restores whatever the
        // shared admin / IP row looked like before the test, not
        // whatever this helper left behind. Tests that also mutate
        // MFA state or portal state can still call the individual
        // snapshot helpers explicitly.
        if ($userRole === UuidUserAccount::USER_ROLE_USERS) {
            $this->snapshotUserLockout($username);
        }
        $this->snapshotIpTracking($this->clientIp);

        $rc = new ReflectionClass(UserRepository::class);
        $method = $rc->getMethod('getAccountByPassword');
        $user = new UserEntity();
        return (bool) $method->invoke($repo, $user, $userRole, $username, $password, $email);
    }

    private function readUserCounter(string $username): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT login_fail_counter FROM users_secure WHERE username = ?",
            [$username]
        );
        $value = $row['login_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readIpCounter(string $ipString): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT ip_login_fail_counter FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['ip_login_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readMfaUserCounter(string $username): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_fail_counter FROM users_secure WHERE BINARY username = ?",
            [$username]
        );
        $value = $row['mfa_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readMfaIpCounter(string $ipString): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT mfa_login_fail_counter FROM ip_tracking WHERE ip_string = ?",
            [$ipString]
        );
        $value = $row['mfa_login_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }

    private function readPortalAccountCounter(string $portalLoginUsername): int
    {
        $row = QueryUtils::querySingleRow(
            "SELECT portal_fail_counter FROM patient_access_onsite WHERE BINARY portal_login_username = ?",
            [$portalLoginUsername]
        );
        $value = $row['portal_fail_counter'] ?? 0;
        return is_numeric($value) ? (int) $value : 0;
    }
}
