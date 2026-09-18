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
    /** @var list<int> */
    private array $trackedMfaUserIds = [];
    private mixed $originalPasswordGrantSetting = null;
    /** @var list<string> */
    private array $countersToReset = [];
    /** @var list<string> */
    private array $ipRowsToReset = [];
    private string $clientIp = '127.0.0.1';

    protected function setUp(): void
    {
        parent::setUp();
        $globals = OEGlobalsBag::getInstance();
        $this->originalPasswordGrantSetting = $globals->get('oauth_password_grant');
        // Enable both staff (1) and patient (2) password grant paths for the
        // whole test class so no test has to toggle it mid-flight.
        $globals->set('oauth_password_grant', 3);

        // Give CLI a deterministic client host/IP; MfaUtils reads HTTP_HOST
        // and collectIpAddresses() reads REMOTE_ADDR, both empty in CLI.
        if (!isset($_SERVER['HTTP_HOST']) || !is_string($_SERVER['HTTP_HOST']) || $_SERVER['HTTP_HOST'] === '') {
            $_SERVER['HTTP_HOST'] = 'localhost';
        }
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
        foreach ($this->trackedMfaUserIds as $userId) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM login_mfa_registrations WHERE user_id = ?",
                [$userId]
            );
        }
        foreach ($this->countersToReset as $username) {
            AuthUtils::resetLoginFailedCounter($username);
        }
        foreach ($this->ipRowsToReset as $ipString) {
            QueryUtils::sqlStatementThrowException(
                "DELETE FROM ip_tracking WHERE ip_string = ?",
                [$ipString]
            );
        }

        $this->portalFixtures?->removePortalPatientFixtures();

        if ($this->originalPasswordGrantSetting !== null) {
            OEGlobalsBag::getInstance()->set('oauth_password_grant', $this->originalPasswordGrantSetting);
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

    // ---------- AuthUtils::confirmPatientPassword IP rate limit (4fx8 V9) ----------

    public function testPortalPasswordGrantIncrementsIpCounterOnFailure(): void
    {
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->ipRowsToReset[] = $ipString;

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

    public function testPortalPasswordGrantBlocksAfterRepeatedFailures(): void
    {
        $fixture = $this->portalFixtures()->installPortalPatient(
            portalLoginUsername: 'test-portal-user-block-' . Uuid::uuid4()->toString(),
            plainPassword: 'CorrectPortalPassword1!'
        );

        $ipString = $this->clientIp;
        $this->ipRowsToReset[] = $ipString;

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
        $this->countersToReset[] = $username;
        $ipString = $this->clientIp;
        $this->ipRowsToReset[] = $ipString;

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
        $this->ipRowsToReset[] = $ipString;
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

    private function enrollTotpForUser(int $userId): string
    {
        // Use a stable, well-formed base32 secret. MfaUtils::checkTOTP()
        // decrypts var1 via ServiceContainer::getCrypto()->decryptFromDatabase()
        // so the stored secret must be encrypted the same way.
        $secret = 'JBSWY3DPEHPK3PXP';
        $encryptedSecret = ServiceContainer::getCrypto()->encryptForDatabase($secret);
        QueryUtils::sqlStatementThrowException(
            "DELETE FROM login_mfa_registrations WHERE user_id = ? AND method = 'TOTP'",
            [$userId]
        );
        QueryUtils::sqlStatementThrowException(
            "INSERT INTO login_mfa_registrations (user_id, name, method, var1, var2, last_challenge) "
                . "VALUES (?, 'test', 'TOTP', ?, '', NULL)",
            [$userId, $encryptedSecret]
        );
        $this->trackedMfaUserIds[] = $userId;
        return $secret;
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
