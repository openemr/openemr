<?php

/**
 * PatientPortalLoginController — orchestrates the patient portal login flow.
 *
 * Extracted from portal/get_patient_info.php so the procedural login logic can be
 * unit-tested. The controller is intentionally a faithful port of the original script:
 * the same SQL is issued (via PortalLoginCredentialsRepository), the same session keys
 * are set in the same order, the same audit entries are emitted, and the same redirect
 * URLs are produced.
 *
 * Behavior the controller owns (side effects during login):
 * - DB writes for password rehash and password update (via the repository).
 * - Session mutations for language choice, portal_username/portal_login_username,
 *   pid/patient_portal_onsite_two/etc on successful authentication, password_update=1
 *   when a password change is required (via the PortalSessionAccessor).
 * - Mid-flow audit entries (password-update success, all login-attempt failures).
 *
 * Behavior the caller owns (after the controller returns):
 * - The final-state portalLog entry for successful login (carried in result.portalLogArgs).
 * - CsrfUtils::setupCsrfKey($session) when result.establishCsrf is true.
 * - Destroying the portal session cookie when the result says to.
 * - Setting cache-disable headers when the result says to.
 * - Sending the HTTP redirect.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Michael A. Smith <michael@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc.
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Controllers\Portal;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Core\ModulesApplication;
use OpenEMR\Core\OEGlobalsBag;

/**
 * @phpstan-import-type PatientAccessOnsiteRow from PortalLoginCredentialsRepository
 * @phpstan-import-type PatientDataRow from PortalLoginCredentialsRepository
 */
class PatientPortalLoginController
{
    private const PASSWORD_UPDATE_NORMAL = 0;
    private const PASSWORD_UPDATE_REQUIRED = 1;
    private const PASSWORD_UPDATE_ONE_TIME_RESET = 2;

    public function __construct(
        private readonly PortalLoginCredentialsRepository $repository,
        private readonly PortalAuditLogger $logit,
    ) {
    }

    /**
     * Run the login attempt.
     *
     * @param string                $siteIdHint  Caller's resolution of `$_GET['site']`-or-default.
     * @param array<string, mixed>  $post        The raw `$_POST` superglobal contents.
     * @param array<string, mixed>  $request     The raw `$_REQUEST` superglobal contents.
     * @param PortalSessionAccessor $session     Read/write accessor for the portal session.
     * @param OEGlobalsBag          $globalsBag  Access to OpenEMR globals (e.g. enforce_signin_email).
     */
    public function login(
        string $siteIdHint,
        array $post,
        array $request,
        PortalSessionAccessor $session,
        OEGlobalsBag $globalsBag
    ): PatientPortalLoginResult {
        $landingpage = 'index.php?site=' . urlencode($siteIdHint);
        $redirectParam = $this->stringOrNull($request['redirect'] ?? null);
        if ($redirectParam !== null) {
            $landingpage .= '&redirect=' . urlencode($redirectParam);
        }

        // Anti-CSRF: must have come from portal/index.php which set 'itsme' before POSTing.
        if (!$session->get('itsme', false)) {
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        // Input presence.
        $uname = $this->stringOrNull($post['uname'] ?? null);
        $pass = $this->stringOrNull($post['pass'] ?? null);
        if ($uname === null || $pass === null) {
            return new PatientPortalLoginResult($landingpage . '&w&c', true);
        }

        // Optional email-as-second-factor (the legacy "passaddon" feature, not MFA).
        $passaddon = $this->stringOrNull($post['passaddon'] ?? null);
        if ($globalsBag->getBoolean('enforce_signin_email') && $passaddon === null) {
            return new PatientPortalLoginResult($landingpage . '&w&c', true);
        }

        // Language selection (mutates session whatever the auth outcome). Matches the
        // legacy three-way logic exactly:
        //   if (!empty($_POST['languageChoice'])) { set as int }
        //   elseif (empty($session->get('language_choice'))) { default to 1 }
        //   else { leave session value alone }
        // `empty('0')` is true in PHP, so '0' here counts as unset for both branches.
        $languageChoice = $this->stringOrNull($post['languageChoice'] ?? null);
        if ($languageChoice !== null) {
            $session->set('language_choice', (int) $languageChoice);
        } else {
            $existing = $session->get('language_choice');
            // Mirror `empty()` truthiness across the types this slot can hold.
            $isLegacyEmpty = in_array($existing, [null, false, 0, '', '0'], true);
            if ($isLegacyEmpty) {
                // just in case both are empty, then use english (preserved from original)
                $session->set('language_choice', 1);
            }
        }

        // Mode discriminator: 2 = one-time PIN reset, 1 = normal-reset (must-change), 0 = normal.
        // Pulled and immediately cleared from the session because subsequent flows expect a fresh state.
        $passwordUpdateRaw = $session->get('password_update', 0);
        $passwordUpdate = is_int($passwordUpdateRaw) ? $passwordUpdateRaw : 0;
        $session->remove('password_update');

        // Authenticate: locate the patient_access_onsite row.
        $auth = $this->lookupAuth($passwordUpdate, $uname, $post, $session);

        if ($auth === null) {
            $this->logit->portalLog('login attempt', '', ($uname . ':invalid username'), '', '0');
            return new PatientPortalLoginResult($landingpage . '&w&u', true);
        }

        // Verify password.
        if (!$this->verifyPassword($passwordUpdate, $pass, $auth)) {
            $this->logit->portalLog('login attempt', '', ($uname . ':invalid password'), '', '0');
            return new PatientPortalLoginResult($landingpage . '&w&p', true);
        }

        // Mirror the portal_username / portal_login_username into the session early
        // (preserves original ordering — these are set before the patient_data lookup).
        $session->set('portal_username', $auth['portal_username']);
        $session->set('portal_login_username', $auth['portal_login_username']);

        $userData = $this->repository->fetchPatientData($auth['pid']);
        if ($userData === null) {
            // Original "problem with query" path: silent destroy + &w redirect, no log.
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        if ($userData['email'] !== ($passaddon ?? '') && $globalsBag->getBoolean('enforce_signin_email')) {
            $this->logit->portalLog('login attempt', '', ($uname . ':invalid email'), '', '0');
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        if ($userData['allow_patient_portal'] !== 'YES') {
            $this->logit->portalLog('login attempt', '', ($uname . ':allow portal turned off'), '', '0');
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        if ($auth['pid'] !== $userData['pid']) {
            // Defensive: should be impossible given the join, but the original code guards it.
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        // Handle in-flight password change (the patient was redirected here from the
        // "you must change your password" form and is now POSTing pass_new + confirmation).
        $authorizedPortal = false;
        if ($passwordUpdate !== self::PASSWORD_UPDATE_NORMAL) {
            $codeNew = $this->stringOrNull($post['pass_new'] ?? null);
            $codeNewConfirm = $this->stringOrNull($post['pass_new_confirm'] ?? null);
            if ($codeNew !== null && $codeNewConfirm !== null && hash_equals($codeNewConfirm, $codeNew)) {
                $newHash = (new AuthHash())->passwordHash($codeNew);
                if (!is_string($newHash) || $newHash === '') {
                    // @codeCoverageIgnoreStart — AuthHash::passwordHash returns mixed but in
                    // practice produces a bcrypt string; this defensive branch only fires on
                    // a configuration breakage that would also break the rest of the system.
                    throw new \RuntimeException('OpenEMR is not working because unable to create a hash.');
                    // @codeCoverageIgnoreEnd
                }
                // The legacy script reads $_POST['login_uname'] without an empty() check,
                // so '0' is a valid login username here. Narrow with is_string only — no
                // empty()-style filter — and fall back to '' only when truly absent or
                // non-string, matching the original `[$_POST['login_uname'], ...]` bind.
                $loginUnameRaw = $post['login_uname'] ?? null;
                $newLoginUname = is_string($loginUnameRaw) ? $loginUnameRaw : '';
                $this->repository->updateLoginAndPassword($auth['id'], $newLoginUname, $newHash);
                $authorizedPortal = true;
                $ptName = $this->stringOrNull($session->get('ptName')) ?? '';
                $this->logit->portalLog(
                    'password update',
                    $auth['pid'],
                    ($newLoginUname . ': ' . $ptName . ':success')
                );
            }
        }

        // If portal_pwd_status is 0 and we haven't just successfully changed the password,
        // bounce back to the change form (mode=1).
        if ($auth['portal_pwd_status'] === 0 && !$authorizedPortal) {
            $session->set('password_update', self::PASSWORD_UPDATE_REQUIRED);
            return new PatientPortalLoginResult($landingpage, false);
        }

        // portal_pwd_status==1 means password is current and the patient is authorized.
        if ($auth['portal_pwd_status'] === 1) {
            $authorizedPortal = true;
        }

        if (!$authorizedPortal) {
            $this->logit->portalLog('login', '', ($uname . ':not authorized'), '', '0');
            return new PatientPortalLoginResult($landingpage . '&w', true);
        }

        // Establish the portal session in one bulk write (mirrors upstream's
        // SessionUtil::setUnsetSession pattern used in the original script).
        $providerInfo = $this->repository->fetchProviderInfo($userData['providerID']) ?? [
            'fname' => '',
            'lname' => '',
            'username' => null,
        ];
        $session->setMany([
            'pid' => $auth['pid'],
            'patient_portal_onsite_two' => 1,
            'providerName' => $providerInfo['fname'] . ' ' . $providerInfo['lname'],
            'providerUName' => $providerInfo['username'],
            'sessionUser' => '-patient-',
            'providerId' => $userData['providerID'] !== 0 ? $userData['providerID'] : 'undefined',
            'ptName' => $userData['fname'] . ' ' . $userData['lname'],
            // authUser is consumed by ACL; authUserID is intentionally not set for portal sessions.
            'authUser' => 'portal-user',
        ]);
        $session->removeMany(['password_update', 'itsme']);

        $successPid = $session->get('pid');
        $successPortalUsername = $this->stringOrNull($session->get('portal_username')) ?? '';
        $successPtName = $this->stringOrNull($session->get('ptName')) ?? '';
        $successLogArgs = [
            'login',
            $successPid,
            ($successPortalUsername . ': ' . $successPtName . ':success'),
        ];

        // Honour a post-login redirect target if the caller supplied one AND the target
        // is on the allowed-modules safelist; otherwise default to ./home.php with
        // cache-disable headers (the original behaviour).
        if ($redirectParam !== null) {
            $safeRedirect = ModulesApplication::filterSafeLocalModuleFiles([$redirectParam]);
            $safeUrl = $safeRedirect[0] ?? null;
            // @codeCoverageIgnoreStart — filterSafeLocalModuleFiles uses realpath() against
            // the live modules directory, so an accepted redirect requires a real on-disk
            // file under interface/modules/. Exercised by the E2E suite, not unit tests.
            if (is_string($safeUrl) && $safeUrl !== '') {
                return new PatientPortalLoginResult($safeUrl, false, $successLogArgs, false, true);
            }
            // @codeCoverageIgnoreEnd
        }

        return new PatientPortalLoginResult('./home.php', false, $successLogArgs, true, true);
    }

    /**
     * Locate the patient_access_onsite row for the current login attempt.
     *
     * @param array<string, mixed> $post
     * @return PatientAccessOnsiteRow|null
     */
    private function lookupAuth(int $passwordUpdate, string $uname, array $post, PortalSessionAccessor $session): ?array
    {
        $pin = $this->stringOrNull($session->get('pin'));
        if ($passwordUpdate === self::PASSWORD_UPDATE_ONE_TIME_RESET && $pin !== null) {
            // One-time PIN reset: the row is keyed by the one-time token, not by username.
            $token = $this->stringOrNull($session->get('forward')) ?? '';
            $auth = $this->repository->fetchByOneTimeToken($token);

            if ($auth === null) {
                return null;
            }

            // The token is single-use regardless of whether the PIN validates.
            $oneTimeToken = $auth['portal_onetime'] ?? '';
            $this->repository->clearOneTimeToken($oneTimeToken);

            $validate = substr($oneTimeToken, 32, 6);
            $tokenPin = $this->stringOrNull($post['token_pin'] ?? null);
            $pinValid = $validate !== ''
                && $tokenPin !== null
                && hash_equals($pin, $tokenPin)
                && hash_equals($validate, $tokenPin);

            $session->removeMany(['forward', 'pin']);

            return $pinValid ? $auth : null;
        }

        return $passwordUpdate === self::PASSWORD_UPDATE_REQUIRED
            ? $this->repository->fetchByUsername($uname)
            : $this->repository->fetchByLoginUsername($uname);
    }

    /**
     * Verify the supplied password against the stored hash, rehashing on success if the
     * stored hash uses an older algorithm. Returns true on success, false on failure.
     *
     * @param PatientAccessOnsiteRow $auth
     */
    private function verifyPassword(int $passwordUpdate, string $pass, array $auth): bool
    {
        if ($passwordUpdate === self::PASSWORD_UPDATE_ONE_TIME_RESET) {
            // Mode 2 (one-time PIN) compares the password column directly (no hashing).
            // Use hash_equals to avoid the timing side-channel of a plain != comparison.
            return hash_equals($auth['portal_pwd'], $pass);
        }

        if (!AuthHash::passwordVerify($pass, $auth['portal_pwd'])) {
            return false;
        }

        $authHashPortal = new AuthHash();
        if ($authHashPortal->passwordNeedsRehash($auth['portal_pwd'])) {
            $reHash = $authHashPortal->passwordHash($pass);
            if (!is_string($reHash) || $reHash === '') {
                // @codeCoverageIgnoreStart — see the matching defensive branch in login().
                throw new \RuntimeException('OpenEMR is not working because unable to create a hash.');
                // @codeCoverageIgnoreEnd
            }
            $this->repository->updatePasswordHash($auth['id'], $reHash);
        }

        return true;
    }

    /**
     * Narrow `mixed` to a non-empty string or null, matching PHP's `empty()` semantics
     * so this refactor preserves the legacy script's behavior bit-for-bit. `empty('0')`
     * is true in PHP — the legacy script's `if (empty($_POST['x']))` checks therefore
     * treated the literal "0" the same as "" and null, and so does this helper.
     *
     * Symfony's typed Request accessors (e.g. `InputBag::getString`) would replace
     * this if the controller were restructured to take a `ServerRequestInterface`
     * instead of raw `$_POST`/`$_REQUEST` arrays. That restructure is out of scope
     * for this behavior-preserving extraction.
     */
    private function stringOrNull(mixed $value): ?string
    {
        if (is_string($value) && $value !== '' && $value !== '0') {
            return $value;
        }
        return null;
    }
}
