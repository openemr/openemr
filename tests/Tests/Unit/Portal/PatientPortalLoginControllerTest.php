<?php

/**
 * PatientPortalLoginControllerTest — characterization tests for every branch of the
 * patient portal login flow, exercising the controller with an in-memory credentials
 * repository and an in-memory session.
 *
 * These tests lock in the existing behavior of portal/get_patient_info.php so future
 * changes can be verified against a baseline.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Tests\Unit\Portal;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Controllers\Portal\PatientPortalLoginController;
use OpenEMR\Controllers\Portal\PortalAuditLogger;
use OpenEMR\Controllers\Portal\PortalLoginCredentialsRepository;
use OpenEMR\Controllers\Portal\PortalSessionAccessor;
use OpenEMR\Core\OEGlobalsBag;
use PHPUnit\Framework\TestCase;

/**
 * @phpstan-import-type PatientAccessOnsiteRow from PortalLoginCredentialsRepository
 * @phpstan-import-type PatientDataRow from PortalLoginCredentialsRepository
 * @phpstan-import-type ProviderInfoRow from PortalLoginCredentialsRepository
 */

class PatientPortalLoginControllerTest extends TestCase
{
    private InMemoryPortalSessionAccessor $session;
    private InMemoryPortalLoginCredentialsRepository $repo;
    private RecordingPortalAuditLogger $log;
    private OEGlobalsBag $globalsBag;
    private PatientPortalLoginController $controller;
    private bool $originalEnforceSigninEmail;

    private const PID = 42;
    private const SITE = 'default';

    protected function setUp(): void
    {
        parent::setUp();
        $this->session = new InMemoryPortalSessionAccessor();
        $this->repo = new InMemoryPortalLoginCredentialsRepository();
        $this->log = new RecordingPortalAuditLogger();
        $this->globalsBag = OEGlobalsBag::getInstance();
        // Capture the singleton's prior value so tearDown can restore it instead of
        // overwriting whatever the surrounding suite had configured.
        $this->originalEnforceSigninEmail = $this->globalsBag->getBoolean('enforce_signin_email');
        $this->globalsBag->set('enforce_signin_email', null);
        $this->controller = new PatientPortalLoginController($this->repo, $this->log);
    }

    protected function tearDown(): void
    {
        unset($_SESSION['csrf_private_key']);
        $this->globalsBag->set('enforce_signin_email', $this->originalEnforceSigninEmail);
        parent::tearDown();
    }

    /**
     * @return PatientAccessOnsiteRow
     */
    private function seededAuth(string $passwordPlain = 'goodpass'): array
    {
        $hash = (new AuthHash())->passwordHash($passwordPlain);
        if (!is_string($hash)) {
            $this->fail('AuthHash::passwordHash unexpectedly returned non-string in test fixture'); // @codeCoverageIgnore
        }
        return [
            'id' => 7,
            'pid' => self::PID,
            'portal_pwd' => $hash,
            'portal_username' => 'alice',
            'portal_login_username' => 'alice_login',
            'portal_pwd_status' => 1,
        ];
    }

    /**
     * @return PatientDataRow
     */
    private function seededPatientData(string $email = 'alice@example.com', string $allowPortal = 'YES'): array
    {
        return [
            'pid' => self::PID,
            'fname' => 'Alice',
            'lname' => 'Smith',
            'email' => $email,
            'providerID' => 11,
            'allow_patient_portal' => $allowPortal,
        ];
    }

    // ---------------------------------------------------------------------
    // Pre-auth gates
    // ---------------------------------------------------------------------

    public function testRedirectsToErrorWhenItsmeSessionMarkerAbsent(): void
    {
        $result = $this->controller->login(self::SITE, [], [], $this->session, $this->globalsBag);

        $this->assertStringEndsWith('&w', $result->redirectUrl);
        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertNull($result->portalLogArgs);
        $this->assertSame([], $this->log->calls, 'No audit entry on anti-CSRF gate');
    }

    public function testRedirectsWithChangedWhenUsernameMissing(): void
    {
        $this->session->set('itsme', 1);
        $result = $this->controller->login(self::SITE, ['pass' => 'x'], [], $this->session, $this->globalsBag);
        $this->assertSame('index.php?site=default&w&c', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertSame([], $this->log->calls);
    }

    public function testRedirectsWithChangedWhenPasswordMissing(): void
    {
        $this->session->set('itsme', 1);
        $result = $this->controller->login(self::SITE, ['uname' => 'x'], [], $this->session, $this->globalsBag);
        $this->assertSame('index.php?site=default&w&c', $result->redirectUrl);
    }

    public function testRedirectsWithChangedWhenEmailEnforcedButMissing(): void
    {
        $this->session->set('itsme', 1);
        $this->globalsBag->set('enforce_signin_email', true);
        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'x', 'pass' => 'y'],
            [],
            $this->session,
            $this->globalsBag
        );
        $this->assertSame('index.php?site=default&w&c', $result->redirectUrl);
    }

    public function testIncludesRedirectQueryStringInLandingPage(): void
    {
        $this->session->set('itsme', 1);
        $result = $this->controller->login(
            self::SITE,
            [],
            ['redirect' => 'foo/bar.php'],
            $this->session,
            $this->globalsBag
        );
        $this->assertStringContainsString('&redirect=foo%2Fbar.php', $result->redirectUrl);
    }

    // ---------------------------------------------------------------------
    // Credential failures
    // ---------------------------------------------------------------------

    public function testRedirectsWithInvalidUsernameWhenLookupReturnsNothing(): void
    {
        $this->session->set('itsme', 1);
        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'nope', 'pass' => 'x'],
            [],
            $this->session,
            $this->globalsBag
        );
        $this->assertSame('index.php?site=default&w&u', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertCount(1, $this->log->calls);
        $this->assertSame('login attempt', $this->log->calls[0]['event']);
        $this->assertSame('nope:invalid username', $this->log->calls[0]['comments']);
        $this->assertSame('0', $this->log->calls[0]['success']);
    }

    public function testRedirectsWithInvalidPasswordWhenHashFailsToVerify(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth('goodpass');

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'wrongpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w&p', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertSame('alice_login:invalid password', $this->log->calls[0]['comments']);
    }

    public function testRehashesPasswordWhenHashAlgorithmNeedsUpgrade(): void
    {
        $this->session->set('itsme', 1);
        // A bcrypt cost-4 hash will be considered in need of rehash by modern AuthHash settings.
        $weakHash = password_hash('goodpass', PASSWORD_BCRYPT, ['cost' => 4]);
        $auth = $this->seededAuth();
        $auth['portal_pwd'] = $weakHash;
        $this->repo->stubByLoginUsername['alice_login'] = $auth;
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        $this->repo->stubProviderInfo[11] = ['fname' => 'Dr', 'lname' => 'Who', 'username' => 'drwho'];

        $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertCount(1, $this->repo->passwordHashUpdates, 'A rehash should be persisted');
        $this->assertSame(7, $this->repo->passwordHashUpdates[0]['id']);
        $this->assertNotSame($weakHash, $this->repo->passwordHashUpdates[0]['hash']);
    }

    // ---------------------------------------------------------------------
    // Patient-data branch
    // ---------------------------------------------------------------------

    public function testRedirectsWhenPatientDataLookupReturnsNothing(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        // No patient_data row seeded.

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertSame([], $this->log->calls, 'No audit entry on patient_data-missing path');
    }

    public function testRedirectsWhenEmailDoesNotMatchAndEnforcementOn(): void
    {
        $this->session->set('itsme', 1);
        $this->globalsBag->set('enforce_signin_email', true);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData('alice@example.com');

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass', 'passaddon' => 'wrong@example.com'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertSame('alice_login:invalid email', $this->log->calls[0]['comments']);
    }

    public function testRedirectsWhenAllowPatientPortalIsDisabled(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData('alice@example.com', 'NO');

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertSame('alice_login:allow portal turned off', $this->log->calls[0]['comments']);
    }

    // ---------------------------------------------------------------------
    // Password-change-required flow
    // ---------------------------------------------------------------------

    public function testRedirectsToPasswordChangeFormWhenPwdStatusZeroAndNoChangeAttempt(): void
    {
        $this->session->set('itsme', 1);
        $auth = $this->seededAuth();
        $auth['portal_pwd_status'] = 0;
        $this->repo->stubByLoginUsername['alice_login'] = $auth;
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default', $result->redirectUrl, 'Bare landing, no &w');
        $this->assertFalse($result->destroySessionCookie, 'Session preserved for the change-form round-trip');
        $this->assertSame(1, $this->session->get('password_update'), 'Mode 1 set for the next POST');
    }

    public function testCompletesPasswordChangeWhenNewPasswordsMatch(): void
    {
        // Patient was previously bounced to the change form; now POSTing the new password.
        $this->session->set('itsme', 1);
        $this->session->set('password_update', 1);
        $auth = $this->seededAuth();
        $auth['portal_pwd_status'] = 0;
        $this->repo->stubByUsername['alice'] = $auth;  // mode 1 looks up by portal_username
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        $this->repo->stubProviderInfo[11] = ['fname' => 'Dr', 'lname' => 'Who', 'username' => 'drwho'];

        $result = $this->controller->login(
            self::SITE,
            [
                'uname' => 'alice',
                'pass' => 'goodpass',
                'pass_new' => 'NewPass123!',
                'pass_new_confirm' => 'NewPass123!',
                'login_uname' => 'alice_login',
            ],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('./home.php', $result->redirectUrl, 'Successful login after password change');
        $this->assertCount(1, $this->repo->loginAndPasswordUpdates);
        $this->assertSame('alice_login', $this->repo->loginAndPasswordUpdates[0]['login']);

        // The mid-flow "password update" audit and the final-state "login success" audit both fire.
        $eventNames = array_column($this->log->calls, 'event');
        $this->assertContains('password update', $eventNames);
        $this->assertNotNull($result->portalLogArgs);
        $this->assertSame('login', $result->portalLogArgs[0]);
    }

    public function testPasswordChangeThrowsWhenHasherReturnsFalse(): void
    {
        // Inject a hasher that fails so the defensive throw in the password-change branch fires.
        $controller = new PatientPortalLoginController(
            $this->repo,
            $this->log,
            new class implements \OpenEMR\Controllers\Portal\PortalPasswordHasher {
                public function hash(string $plain): string|false
                {
                    return false;
                }
            },
        );

        $this->session->set('itsme', 1);
        $this->session->set('password_update', 1);
        $auth = $this->seededAuth();
        $auth['portal_pwd_status'] = 0;
        $this->repo->stubByUsername['alice'] = $auth;
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('OpenEMR is not working because unable to create a hash.');

        $controller->login(
            self::SITE,
            [
                'uname' => 'alice',
                'pass' => 'goodpass',
                'pass_new' => 'NewPass123!',
                'pass_new_confirm' => 'NewPass123!',
                'login_uname' => 'alice_login',
            ],
            [],
            $this->session,
            $this->globalsBag
        );
    }

    // ---------------------------------------------------------------------
    // Successful login
    // ---------------------------------------------------------------------

    public function testSuccessfulLoginEstablishesSessionAndReturnsHomeRedirect(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        $this->repo->stubProviderInfo[11] = ['fname' => 'Dr', 'lname' => 'Who', 'username' => 'drwho'];

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('./home.php', $result->redirectUrl);
        $this->assertFalse($result->destroySessionCookie);
        $this->assertTrue($result->sendNoCacheHeaders, 'home.php redirect should set the cache-disable headers');
        $this->assertTrue($result->establishCsrf, 'successful login result asks the caller to set up CSRF');

        // Session keys established.
        $this->assertSame(self::PID, $this->session->get('pid'));
        $this->assertSame(1, $this->session->get('patient_portal_onsite_two'));
        $this->assertSame('Dr Who', $this->session->get('providerName'));
        $this->assertSame('drwho', $this->session->get('providerUName'));
        $this->assertSame('-patient-', $this->session->get('sessionUser'));
        $this->assertSame(11, $this->session->get('providerId'));
        $this->assertSame('Alice Smith', $this->session->get('ptName'));
        $this->assertSame('portal-user', $this->session->get('authUser'));
        $this->assertSame('alice', $this->session->get('portal_username'));
        $this->assertSame('alice_login', $this->session->get('portal_login_username'));

        // itsme cleared post-establishment.
        $this->assertFalse($this->session->has('itsme'));

        // Final-state log carried in result, not yet emitted by the controller.
        $this->assertNotNull($result->portalLogArgs);
        $this->assertSame('login', $result->portalLogArgs[0]);
        $this->assertSame(self::PID, $result->portalLogArgs[1]);
        $this->assertIsString($result->portalLogArgs[2]);
        $this->assertStringContainsString(':success', $result->portalLogArgs[2]);
    }

    // ---------------------------------------------------------------------
    // Additional branch coverage
    // ---------------------------------------------------------------------

    public function testLanguageChoiceFromPostOverridesSession(): void
    {
        $this->session->set('itsme', 1);
        $this->session->set('language_choice', 7);

        // uname/pass are required to get past the presence-check bail; the
        // credentials don't have to validate — language is set before lookupAuth runs.
        $this->controller->login(
            self::SITE,
            ['uname' => 'whoever', 'pass' => 'whatever', 'languageChoice' => '3'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame(3, $this->session->get('language_choice'));
    }

    public function testRedirectsSilentlyWhenAuthPidMismatchesPatientDataPid(): void
    {
        $this->session->set('itsme', 1);
        $auth = $this->seededAuth();
        $auth['pid'] = 99; // mismatched
        $this->repo->stubByLoginUsername['alice_login'] = $auth;
        // patient_data fixture is keyed at pid=99 because that's the lookup pid the
        // controller will use (it joins via $auth['pid']); set userData with the WRONG
        // mirror pid 42 to trigger the defensive guard.
        $this->repo->stubPatientData[99] = $this->seededPatientData();
        $this->repo->stubPatientData[99]['pid'] = 42;

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertSame([], $this->log->calls, 'pid-mismatch guard is silent (no audit)');
    }

    public function testMismatchedNewPasswordsLeavesAuthorisationFalseAndLogsNotAuthorized(): void
    {
        // pwd_status=1 + passwordUpdate mode set, but new passwords don't match -> the
        // change-flow doesn't run, but pwd_status==1 makes $authorizedPortal true and the
        // patient logs in normally. To exercise the "!authorizedPortal" terminal log, use
        // pwd_status=0 with mismatched pass_new so neither branch sets $authorizedPortal=true.
        // The earlier-line pwd_status==0 bounce-back is also gated by !$authorizedPortal,
        // so we'd hit THAT — not the terminal log. The legacy behaviour for "mismatched
        // pass_new under pwd_status=0" is therefore "go back to the password-change form,"
        // and we cover the terminal-log path differently: with pwd_status set to neither
        // 0 nor 1 (a forced "in-between" state that the password-change attempt fails to
        // resolve).
        $this->session->set('itsme', 1);
        $this->session->set('password_update', 1);
        $auth = $this->seededAuth();
        $auth['portal_pwd_status'] = 2; // neither 0 nor 1 — exercises the terminal !authorizedPortal
        $this->repo->stubByUsername['alice'] = $auth;
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();

        $result = $this->controller->login(
            self::SITE,
            [
                'uname' => 'alice',
                'pass' => 'goodpass',
                'pass_new' => 'NewPass123!',
                'pass_new_confirm' => 'Different!',
                'login_uname' => 'alice_login',
            ],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w', $result->redirectUrl);
        $this->assertTrue($result->destroySessionCookie);
        $this->assertSame('login', $this->log->calls[0]['event']);
        $this->assertSame('alice:not authorized', $this->log->calls[0]['comments']);
    }

    public function testSuccessfulLoginWithMissingProviderInfoFallsBackToBlanks(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        // No provider info stub — fetchProviderInfo returns null; controller falls back.

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('./home.php', $result->redirectUrl);
        $this->assertSame(' ', $this->session->get('providerName'), 'fname + space + lname; both empty');
        $this->assertNull($this->session->get('providerUName'));
    }

    public function testOneTimePinResetHappyPath(): void
    {
        // Patient is in the one-time PIN reset flow: the session carries the PIN they
        // received out-of-band plus the `forward` token; the row is keyed by portal_onetime.
        $this->session->set('itsme', 1);
        $this->session->set('password_update', 2);
        $this->session->set('pin', '987654');
        // portal_onetime is 32 chars of opaque + 6 chars of validate-PIN appended.
        $oneTimeToken = str_repeat('a', 32) . '987654';
        $this->session->set('forward', $oneTimeToken);

        $auth = $this->seededAuth();
        $auth['portal_pwd'] = 'plaintext-pin-mode-pwd'; // mode 2 compares column directly
        $auth['portal_onetime'] = $oneTimeToken;
        $this->repo->stubByOneTimeToken[$oneTimeToken] = $auth;
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        $this->repo->stubProviderInfo[11] = ['fname' => 'Dr', 'lname' => 'Who', 'username' => 'drwho'];

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice', 'pass' => 'plaintext-pin-mode-pwd', 'token_pin' => '987654'],
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('./home.php', $result->redirectUrl);
        $this->assertSame(self::PID, $this->session->get('pid'), 'PIN-reset login establishes the session');
        $this->assertSame([$oneTimeToken], $this->repo->clearedOneTimeTokens, 'PIN-reset token consumed');
        $this->assertFalse($this->session->has('forward'), 'forward cleared post-PIN');
        $this->assertFalse($this->session->has('pin'), 'pin cleared post-PIN');
    }

    public function testOneTimePinResetInvalidPinRejected(): void
    {
        $this->session->set('itsme', 1);
        $this->session->set('password_update', 2);
        $this->session->set('pin', '987654');
        $oneTimeToken = str_repeat('a', 32) . '987654';
        $this->session->set('forward', $oneTimeToken);

        $auth = $this->seededAuth();
        $auth['portal_pwd'] = 'pwd';
        $auth['portal_onetime'] = $oneTimeToken;
        $this->repo->stubByOneTimeToken[$oneTimeToken] = $auth;

        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice', 'pass' => 'pwd', 'token_pin' => '111111'], // wrong PIN
            [],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('index.php?site=default&w&u', $result->redirectUrl);
        $this->assertSame([$oneTimeToken], $this->repo->clearedOneTimeTokens, 'token consumed regardless of PIN validity');
        $this->assertSame('alice:invalid username', $this->log->calls[0]['comments']);
    }

    public function testUnsafeRedirectFallsBackToHomeWithCacheHeaders(): void
    {
        $this->session->set('itsme', 1);
        $this->repo->stubByLoginUsername['alice_login'] = $this->seededAuth();
        $this->repo->stubPatientData[self::PID] = $this->seededPatientData();
        $this->repo->stubProviderInfo[11] = ['fname' => 'Dr', 'lname' => 'Who', 'username' => 'drwho'];

        // ModulesApplication::filterSafeLocalModuleFiles will reject this unknown path and
        // strip it; the success path then falls back to ./home.php (which DOES set the
        // legacy no-cache headers, matching the original script's behaviour). The test
        // asserts the safelist is consulted: a malicious redirect param can't escape
        // through to the user.
        $result = $this->controller->login(
            self::SITE,
            ['uname' => 'alice_login', 'pass' => 'goodpass'],
            ['redirect' => '/etc/passwd'],
            $this->session,
            $this->globalsBag
        );

        $this->assertSame('./home.php', $result->redirectUrl);
        $this->assertTrue($result->sendNoCacheHeaders);
    }
}

/**
 * In-memory PortalLoginCredentialsRepository for tests.
 */
/**
 * @phpstan-import-type PatientAccessOnsiteRow from PortalLoginCredentialsRepository
 * @phpstan-import-type PatientDataRow from PortalLoginCredentialsRepository
 * @phpstan-import-type ProviderInfoRow from PortalLoginCredentialsRepository
 */
final class InMemoryPortalLoginCredentialsRepository implements PortalLoginCredentialsRepository
{
    /** @var array<string, PatientAccessOnsiteRow> */
    public array $stubByOneTimeToken = [];
    /** @var array<string, PatientAccessOnsiteRow> */
    public array $stubByLoginUsername = [];
    /** @var array<string, PatientAccessOnsiteRow> */
    public array $stubByUsername = [];
    /** @var array<int, PatientDataRow> */
    public array $stubPatientData = [];
    /** @var array<int, ProviderInfoRow> */
    public array $stubProviderInfo = [];

    /** @var list<string> */
    public array $clearedOneTimeTokens = [];
    /** @var list<array{id: int, hash: string}> */
    public array $passwordHashUpdates = [];
    /** @var list<array{id: int, login: string, hash: string}> */
    public array $loginAndPasswordUpdates = [];

    public function fetchByOneTimeToken(string $token): ?array
    {
        return $this->stubByOneTimeToken[$token] ?? null;
    }

    public function fetchByLoginUsername(string $loginUsername): ?array
    {
        return $this->stubByLoginUsername[$loginUsername] ?? null;
    }

    public function fetchByUsername(string $username): ?array
    {
        return $this->stubByUsername[$username] ?? null;
    }

    public function clearOneTimeToken(string $token): void
    {
        $this->clearedOneTimeTokens[] = $token;
    }

    public function updatePasswordHash(int $id, string $newHash): void
    {
        $this->passwordHashUpdates[] = ['id' => $id, 'hash' => $newHash];
    }

    public function updateLoginAndPassword(int $id, string $loginUsername, string $newHash): void
    {
        $this->loginAndPasswordUpdates[] = ['id' => $id, 'login' => $loginUsername, 'hash' => $newHash];
    }

    public function fetchPatientData(int $pid): ?array
    {
        return $this->stubPatientData[$pid] ?? null;
    }

    public function fetchProviderInfo(int $providerId): ?array
    {
        return $this->stubProviderInfo[$providerId] ?? null;
    }
}

/**
 * Recording PortalAuditLogger that captures every portalLog call for assertions.
 */
final class RecordingPortalAuditLogger implements PortalAuditLogger
{
    /** @var list<array{event: string, patientId: mixed, comments: string, binds: string, success: string}> */
    public array $calls = [];

    public function portalLog(string $event, $patientId, string $comments, string $binds = '', string $success = '1'): void
    {
        $this->calls[] = [
            'event' => $event,
            'patientId' => $patientId,
            'comments' => $comments,
            'binds' => $binds,
            'success' => $success,
        ];
    }
}

/**
 * In-memory PortalSessionAccessor for tests. Stores everything in a plain array and
 * records mutations so tests can assert against the final state.
 */
final class InMemoryPortalSessionAccessor implements PortalSessionAccessor
{
    /** @var array<string, mixed> */
    private array $data = [];

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    public function setMany(array $kvs): void
    {
        foreach ($kvs as $key => $value) {
            $this->data[$key] = $value;
        }
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function removeMany(array $keys): void
    {
        foreach ($keys as $key) {
            unset($this->data[$key]);
        }
    }
}
