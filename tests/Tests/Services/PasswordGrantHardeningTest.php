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
        }
        foreach ($this->originalUserLockoutByUsername as $username => $original) {
            // Restore the pre-mutation lockout state exactly rather than
            // resetting to zero — a shared user (admin) may legitimately
            // have had a non-zero counter or a recent last_login_fail from
            // real activity before the test ran.
            QueryUtils::sqlStatementThrowException(
                "UPDATE `users_secure` SET `login_fail_counter` = ?, "
                    . "`last_login_fail` = ?, `auto_block_emailed` = ? "
                    . "WHERE BINARY `username` = ?",
                [
                    $original['login_fail_counter'],
                    $original['last_login_fail'],
                    $original['auto_block_emailed'],
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
                    . "`ip_auto_block_emailed` = ? "
                    . "WHERE `ip_string` = ?",
                [
                    $original['total_ip_login_fail_counter'],
                    $original['ip_login_fail_counter'],
                    $original['ip_last_login_fail'],
                    $original['ip_auto_block_emailed'],
                    $ipString,
                ]
            );
        }

        $this->portalFixtures?->removePortalPatientFixtures();

        // Restore mutated globals: tests here set $_POST and $_SERVER keys
        // for MfaUtils / IP resolution. phpunit does not run in isolation,
        // so leaving them set leaks into unrelated tests in the same process.
        unset($_POST['mfa_token'], $_POST['mfa_type']);
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

    public function testValidateClientAllowsAuthCodeGrantForConfidentialClientWithNullSecret(): void
    {
        // Regression guard: CustomAuthCodeGrant validates a JWT client
        // assertion first and then calls validateClient() with a null
        // client_secret purely to run the grant-authorization check.
        // Rejecting that call would break every confidential client that
        // authenticates with private_key_jwt on the authorization_code flow.
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertTrue(
            $repo->validateClient($client['client_id'], null, 'authorization_code'),
            'Confidential client on authorization_code with null client_secret '
                . 'must pass validateClient() so JWT-authenticated flows keep working.'
        );
        $this->assertTrue(
            $repo->validateClient($client['client_id'], '', 'authorization_code'),
            'An empty-string client_secret on authorization_code must also pass '
                . '(same semantics as null; League passes an empty string when '
                . 'the request body has no client_secret parameter).'
        );
    }

    public function testValidateClientDeniesAuthCodeGrantWhenConfidentialClientSecretIsWrong(): void
    {
        // Complement to the null-secret allow test: when a client actually
        // presents a secret on authorization_code, a wrong value must still
        // reject. The null case is opt-in to JWT authentication upstream;
        // sending a wrong secret is not.
        $client = $this->insertConfidentialClientFixture(clientSecret: 'correct-secret');
        $repo = new ClientRepository();
        $this->assertFalse(
            $repo->validateClient($client['client_id'], 'wrong-secret', 'authorization_code'),
            'Confidential client on authorization_code must reject a wrong secret.'
        );
    }

    // ---------- UserRepository::getAccountByPassword MFA required (6xc2) ----------

    public function testPasswordGrantRejectsTotpEnrolledUserWithoutMfaToken(): void
    {
        $userId = $this->requireExistingAdminUserId();
        $this->enrollTotpForUser($userId);

        unset($_POST['mfa_token'], $_POST['mfa_type']);
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
        unset($_POST['mfa_token'], $_POST['mfa_type']);
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

    public function testPasswordGrantTotpFailureIncrementsLockoutCounters(): void
    {
        // A wrong TOTP code on password grant must engage the standard
        // user + IP lockout counters — otherwise an attacker with the
        // right password can grind the 6-digit code indefinitely.
        $userId = $this->requireExistingAdminUserId();
        $secret = $this->enrollTotpForUser($userId);
        $this->snapshotUserLockout('admin');
        $this->snapshotIpTracking($this->clientIp);

        AuthUtils::resetLoginFailedCounter('admin');
        $userBefore = $this->readUserCounter('admin');
        $ipBefore = $this->readIpCounter($this->clientIp);

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
            $this->readUserCounter('admin'),
            'Wrong TOTP on password grant must bump users_secure.login_fail_counter'
        );
        $this->assertGreaterThan(
            $ipBefore,
            $this->readIpCounter($this->clientIp),
            'Wrong TOTP on password grant must bump ip_tracking.ip_login_fail_counter'
        );
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

    public function testPortalPasswordGrantResetsIpCounterOnSuccess(): void
    {
        // Legit patient traffic (typos, several patients behind the same NAT
        // address) accumulates strikes without a reset on success. Mirror the
        // staff-side reset so the counter zeroes out after a good login.
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-reset-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->snapshotIpTracking($ipString);

        // Seed a non-zero counter so we can assert the success path zeroed it.
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO ip_tracking (ip_string, ip_login_fail_counter, ip_last_login_fail) "
                . "VALUES (?, 3, NOW()) ON DUPLICATE KEY UPDATE "
                . "ip_login_fail_counter = 3, ip_last_login_fail = NOW()",
            [$ipString]
        );
        $this->assertSame(3, $this->readIpCounter($ipString), 'seed must land');

        $auth = new AuthUtils('portal-api');
        $ok = $auth->confirmPassword(
            $fixture['portal_login_username'],
            $fixture['plain_password'],
            $fixture['email']
        );
        $this->assertTrue($ok, 'Correct portal password must authenticate');
        $this->assertSame(
            0,
            $this->readIpCounter($ipString),
            'Successful portal login must reset ip_login_fail_counter'
        );
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
            "INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2, last_challenge) "
                . "VALUES (?, 'test-u2f', 'U2F', ?, '', NULL)",
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
            "INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2, last_challenge) "
                . "VALUES (?, 'test', 'TOTP', ?, '', NULL)",
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
            "SELECT `login_fail_counter`, `last_login_fail`, `auto_block_emailed` "
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
                . "`ip_last_login_fail`, `ip_auto_block_emailed` "
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
            "SELECT user_id, name, method, var1, var2, last_challenge "
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
}
