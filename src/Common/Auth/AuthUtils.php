<?php

/**
 * AuthUtils class.
 *
 *   Authentication:
 *     1. This class can be run in 1 of 4 modes:
 *       -login:      Authentication of users during standard login.
 *       -api:        Authentication of users when requesting api token.
 *       -portal-api: Authentication of patients when requesting api token.
 *       -other:      Default setting. Other Authentication when already logged into OpenEMR such as when
 *                     doing Esign or changing mfa setting.
 *     2. LDAP (Active Directory) is also supported. In these cases, the login counter and
 *         expired password mechanisms are ignored.
 *     3. Google sign in is also supported (via Google Workspace with Google Open ID) via the static function
 *         verifyGoogleSignIn($token). In this case, the login counter and expired password mechanisms
 *         are ignored.
 *     4. Timing attack prevention. The time will be the same for a user that does not exist versus a user
 *         that does exist. This is done in standard authentication and ldap authentication by simulating
 *         the password verification in each via the preventTimingAttack() function.
 *        (There is one issue in this mechanism when using ldap with a user that is excluded from it. In
 *         that case unable to avoid timing differences. That feature is really only meant for configuration and
 *         debugging and recommend inactivating that excluded user when not needed, which will then mitigate
 *         this issue.)
 *        (note this mechanism is not used in the Google sign)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @link      https://www.open-emr.org/wiki/index.php/OEMR_wiki_page OEMR
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR
 * @copyright Copyright (c) 2018-2021 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 Ken Chapple <ken@mi-squared.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

namespace OpenEMR\Common\Auth;

use Google_Client;
use MyMailer;
use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Database\QueryUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\OEGlobalsBag;
use OpenEMR\Services\UserService;
use SodiumException;

class AuthUtils
{
    private $loginAuth = false; // standard login authentication
    private $apiAuth = false;   // api login authentication
    private $portalApiAuth = false;   // patient portal api login authentication
    private $otherAuth = false; // other use

    private $authHashAuth; // Store the private AuthHash instance.

    private $errorMessage; // Error messages (in updatePassword() function)

    private $userId;       // Stores user id for api to retrieve (in confirmPassword() function)
    private $userGroup;    // Stores user group for api to retrieve (in confirmPassword() function)
    private $patientId;    // Stores patient pid for api to retrieve (in confirmPassword() function)

    private $dummyHash;     // Used to prevent timing attacks

    public function __construct($mode = '')
    {
        // Set mode
        if ($mode == 'login') {
            $this->loginAuth = true;
        } elseif ($mode == 'api') {
            $this->apiAuth = true;
        } elseif ($mode == 'portal-api') {
            $this->portalApiAuth = true;
        } else {
            $this->otherAuth = true;
        }

        // Set up AuthHash instance for password hashing
        $this->authHashAuth = new AuthHash();

        // Ensure timing attack stuff is in place. This will be to prevent a bad actor from guessing
        //  usernames and knowing they got a hit since the hash verification will then take time
        //  whereas essentially no time is taken when the user does not exist. This will place
        //  a dummy hash at $this->dummyHash, which is used by preventTimingAttack() function to
        //  simulate a passwordVerify() run using the same hashing algorithm.
        $dummyPassword = "dummy";
        $timing = privQuery("SELECT * FROM `globals` WHERE `gl_name` = 'hidden_auth_dummy_hash'");
        if (empty($timing)) {
            // Create and store a new dummy hash globals entry
            $this->dummyHash = $this->authHashAuth->passwordHash($dummyPassword);
            privStatement("INSERT INTO `globals` (`gl_name`, `gl_value`) VALUES ('hidden_auth_dummy_hash', ?)", [$this->dummyHash]);
        } elseif (empty($timing['gl_value'])) {
            // Create and store a dummy rehash in existing globals entry
            $this->dummyHash = $this->authHashAuth->passwordHash($dummyPassword);
            privStatement("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'hidden_auth_dummy_hash'", [$this->dummyHash]);
        } else {
            // The below line is usually all that will happen in this big block of code
            $this->dummyHash = $timing['gl_value'];
            // Ensure the current dummy hash does not need to be rehashed
            if ($this->authHashAuth->passwordNeedsRehash($timing['gl_value'])) {
                // Create and store a dummy rehash in existing globals entry
                $this->dummyHash = $this->authHashAuth->passwordHash($dummyPassword);
                privStatement("UPDATE `globals` SET `gl_value` = ? WHERE `gl_name` = 'hidden_auth_dummy_hash'", [$this->dummyHash]);
            }
        }

        $password_expiration_days = (privQuery("SELECT * FROM `globals` WHERE `gl_name` = 'password_expiration_days' AND `gl_index` = 0")['gl_value'] ?? null);
        if ($password_expiration_days === '') {
            OEGlobalsBag::getInstance()->set('password_expiration_days', 0);
            privStatement("UPDATE `globals` SET `gl_value` = ? WHERE `globals`.`gl_name` = 'password_expiration_days' AND `globals`.`gl_index` = '0'", ['0']);
            error_log("Blank global password_expiration_days updated to 0");
        }
    }

    /**
     *
     * @param $username
     * @param $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param $email    - used in case of portal auth when a email address is required
     * @return bool returns true if the password for the given user is correct, false otherwise.
     */
    public function confirmPassword($username, &$password, $email = '')
    {
        if ($this->portalApiAuth) {
            return $this->confirmPatientPassword($username, $password, $email);
        } else { // $this->loginAuth || $this->apiAuth || $this->otherAuth
            return $this->confirmUserPassword($username, $password);
        }
    }

    /**
     *
     * @param $username
     * @param $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param $email    - used when a email address is required
     * @return bool returns true if the password for the given user is correct, false otherwise.
     */
    private function confirmPatientPassword($username, &$password, $email = ''): bool
    {
        // Set variables for log
        $event = 'portalapi';
        $beginLog = 'Portal API failure';

        // Collect ip address for log
        $ip = collectIpAddresses();

        // Check to ensure ip address has not been blocked.
        $this->setupIpLoginFailedCounter($ip['ip_string']);
        $returnArray = $this->checkIpLoginFailedCounter($ip['ip_string']);
        if (!$returnArray['pass']) {
            $this->incrementIpLoginFailedCounter($ip['ip_string']);
            if ($returnArray['force_block']) {
                EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address has been manually blocked");
            } else {
                EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address exceeded maximum number of failed logins");
            }
            $this->clearFromMemory($password);
            if ($returnArray['email_notification']) {
                $this->notifyIpBlock($ip['ip_string']);
            }
            if (!$returnArray['skip_timing_attack']) {
                $this->preventTimingAttack();
            }
            return false;
        }

        // Check to ensure username and password are not empty
        if (empty($username) || empty($password)) {
            $this->incrementIpLoginFailedCounter($ip['ip_string']);
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". empty username or password");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Per-portal-account block gate. Without a per-account counter
        // the only rate limit is the shared per-IP counter — which an
        // attacker holding valid credentials for one portal account
        // could reset on every successful login, then continue brute-
        // forcing another account indefinitely. Check the per-account
        // counter here so a lockout on account B persists across the
        // attacker's own successful logins on account A.
        if ($this->isPortalAccountBlocked($username)) {
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". portal account exceeded maximum number of failed logins");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Every post-gate rejection below must bump BOTH the per-IP
        // counter AND the per-account counter (when username maps to
        // a real portal_login_username; unknown-user attempts only
        // bump the IP counter since there is no row to UPDATE) so
        // username / email guessing paths (unknown user, disabled
        // account, email mismatch, invalid hash, ...) engage the
        // rate limit that the "wrong password" branch already does.
        $rejectPortalAttempt = function (string $reason, mixed $patientPid = null) use ($ip, $event, $username, $beginLog, &$password): bool {
            $this->incrementIpLoginFailedCounter($ip['ip_string']);
            $this->incrementPortalAccountFailedCounter($username);
            $normalizedPid = is_numeric($patientPid) ? (int) $patientPid : null;
            EventAuditLogger::getInstance()->newEvent(
                $event,
                $username,
                '',
                0,
                $beginLog . ": " . $ip['ip_string'] . ". " . $reason,
                $normalizedPid
            );
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        };

        // Perform checks from patient_access_onsite
        $getPatientSQL = "select `id`, `pid`, `portal_username`, `portal_login_username`, `portal_pwd`, `portal_pwd_status`, `portal_onetime`  from `patient_access_onsite` where BINARY `portal_login_username` = ?";
        $patientInfo = privQuery($getPatientSQL, [$username]);
        if (empty($patientInfo) || empty($patientInfo['id']) || empty($patientInfo['pid'])) {
            return $rejectPortalAttempt('patient portal information not found');
        } elseif (empty($patientInfo['portal_username']) || empty($patientInfo['portal_login_username']) || empty($patientInfo['portal_pwd'])) {
            return $rejectPortalAttempt('patient missing username, login username, or password', $patientInfo['pid']);
        } elseif (!empty($patientInfo['portal_onetime'])) {
            return $rejectPortalAttempt('patient account not yet verified (portal_onetime set)', $patientInfo['pid']);
        } elseif ($patientInfo['portal_pwd_status'] != 1) {
            return $rejectPortalAttempt('patient account not yet verified (portal_pwd_status is not 1)', $patientInfo['pid']);
        }

        // Perform checks from patient_data
        $getPatientDataSQL = "select `pid`, `email`, `allow_patient_portal` FROM `patient_data` WHERE `pid` = ?";
        $patientDataInfo = privQuery($getPatientDataSQL, [$patientInfo['pid']]);
        if (empty($patientDataInfo) || empty($patientDataInfo['pid'])) {
            return $rejectPortalAttempt('patient not found');
        } elseif ($patientDataInfo['allow_patient_portal'] != "YES") {
            return $rejectPortalAttempt('patient does not permit portal access', $patientDataInfo['pid']);
        } elseif (OEGlobalsBag::getInstance()->getBoolean('enforce_signin_email')) {
            if (empty($email)) {
                return $rejectPortalAttempt('patient email was not included in credentials', $patientDataInfo['pid']);
            } elseif (empty($patientDataInfo['email'])) {
                return $rejectPortalAttempt('patient does not have an email in demographics', $patientDataInfo['pid']);
            } elseif ($patientDataInfo['email'] != $email) {
                return $rejectPortalAttempt('patient email not correct', $patientDataInfo['pid']);
            }
        }

        // This error should never happen, but still gotta check for it
        if ($patientInfo['pid'] != $patientDataInfo['pid']) {
            return $rejectPortalAttempt('patient pid comparison with very unusual error');
        }

        // Authentication
        // First, ensure the user hash is a valid hash
        if (!AuthHash::hashValid($patientInfo['portal_pwd'])) {
            return $rejectPortalAttempt('patient stored password hash is invalid', $patientDataInfo['pid']);
        }
        // Second, authentication
        if (!AuthHash::passwordVerify($password, $patientInfo['portal_pwd'])) {
            return $rejectPortalAttempt('patient password incorrect', $patientDataInfo['pid']);
        }

        // Check for rehash
        if ($this->authHashAuth->passwordNeedsRehash($patientInfo['portal_pwd'])) {
            // Hash needs updating, so create a new hash, and replace the old one
            $newHash = $this->rehashPassword($username, $password);
            // store the rehash
            privStatement("UPDATE `patient_access_onsite` SET `portal_pwd` = ? WHERE `id` = ?", [$newHash, $patientInfo['id']]);
        }

        // PASSED auth for the portal api
        $this->clearFromMemory($password);
        // Always clear this account's own per-portal-account counter
        // on success. Clear the shared per-IP counter only when the
        // global opt-in is set; default off so a valid login on
        // account A cannot clear the IP counter that has been
        // accumulating against account B from the same IP.
        $this->resetPortalAccountFailedCounter($username);
        if (self::shouldClearIpCounterOnAuthSuccess()) {
            $this->resetIpLoginFailedCounter($ip['ip_string']);
        }
        //  Set up class variable that the api will need to collect (log for API is done outside)
        $this->patientId = $patientDataInfo['pid'];
        return true;
    }

    /**
     *
     * @param $username
     * @param $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @return bool returns true if the password for the given user is correct, false otherwise.
     * @throws SodiumException
     */
    private function confirmUserPassword($username, &$password): bool
    {
        // Set variables for log
        if ($this->loginAuth) {
            $event = 'login';
            $beginLog = 'failure';
        } elseif ($this->apiAuth) {
            $event = 'api';
            $beginLog = 'API failure';
        } else { // $this->otherAuth
            $event = 'auth';
            $beginLog = 'Auth failure';
        }

        // Collect ip address for log
        $ip = collectIpAddresses();

        // Check to ensure ip address has not been blocked
        // check IP login counter if this option is set
        if ($this->loginAuth || $this->apiAuth) {
            $this->setupIpLoginFailedCounter($ip['ip_string']);
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            $returnArray = $this->checkIpLoginFailedCounter($ip['ip_string']);
            if (!$returnArray['pass']) {
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
                if ($returnArray['force_block']) {
                    EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address has been manually blocked");
                } else {
                    EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address exceeded maximum number of failed logins");
                }
                $this->clearFromMemory($password);
                if ($returnArray['email_notification']) {
                    $this->notifyIpBlock($ip['ip_string']);
                }
                if (!$returnArray['skip_timing_attack']) {
                    $this->preventTimingAttack();
                }
                return false;
            }
        }

        // Check to ensure username and password are not empty
        if (empty($username) || empty($password)) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". empty username or password");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user exists and is active
        $getUserSQL = "select `id`, `authorized`, `see_auth`, `active` from `users` where BINARY `username` = ?";
        $userInfo = privQuery($getUserSQL, [$username]);
        if (empty($userInfo) || empty($userInfo['id'])) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif ($userInfo['active'] != 1) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not active");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user is in a group (and collect the group name)
        $userService = new UserService();
        $authGroup = $userService->getAuthGroupForUser($username);
        if (empty($authGroup)) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found in a group");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user is in a acl group
        if (AclExtended::aclGetGroupTitles($username) == 0) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user not in any phpGACL groups");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Collect user credentials from database
        $getUserSecureSQL = " SELECT `id`, `password`" .
            " FROM `users_secure`" .
            " WHERE BINARY `username` = ?";
        $userSecure = privQuery($getUserSecureSQL, [$username]);
        if (empty($userSecure) || empty($userSecure['id']) || empty($userSecure['password'])) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user credentials not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // check login counter if this option is set
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            $checkArray = $this->checkLoginFailedCounter($username);
            if (!$checkArray['pass']) {
                $this->incrementLoginFailedCounter($username);
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
                EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user exceeded maximum number of failed logins");
                $this->clearFromMemory($password);
                if ($checkArray['email_notification']) {
                    $this->notifyUserBlock($username);
                }
                $this->preventTimingAttack();
                return false;
            }
        }

        // Check password
        if (self::useActiveDirectory($username)) {
            // ldap authentication
            if (!$this->activeDirectoryValidation($username, $password)) {
                if ($this->loginAuth || $this->apiAuth) {
                    // Utilize this during logins (and not during standard password checks within openemr such as esign)
                    $this->incrementLoginFailedCounter($username);
                    $this->incrementIpLoginFailedCounter($ip['ip_string']);
                }
                EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user failed ldap authentication");
                $this->clearFromMemory($password);
                return false;
            }
        } else {
            // standard authentication
            // First, ensure the user hash is a valid hash
            if (!AuthHash::hashValid($userSecure['password'])) {
                if ($this->loginAuth || $this->apiAuth) {
                    // Utilize this during logins (and not during standard password checks within openemr such as esign)
                    $this->incrementIpLoginFailedCounter($ip['ip_string']);
                }
                EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user stored password hash is invalid");
                $this->clearFromMemory($password);
                $this->preventTimingAttack();
                return false;
            }
            // Second, authentication
            if (!AuthHash::passwordVerify($password, $userSecure['password'])) {
                if ($this->loginAuth || $this->apiAuth) {
                    // Utilize this during logins (and not during standard password checks within openemr such as esign)
                    $this->incrementLoginFailedCounter($username);
                    $this->incrementIpLoginFailedCounter($ip['ip_string']);
                }
                EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user password incorrect");
                $this->clearFromMemory($password);
                return false;
            }
        }

        // check for rehash
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            if ($this->authHashAuth->passwordNeedsRehash($userSecure['password'])) {
                // Hash needs updating, so create a new hash, and replace the old one
                $newHash = $this->rehashPassword($username, $password);
                // store the rehash
                privStatement("UPDATE `users_secure` SET `password` = ? WHERE `id` = ?", [$newHash, $userSecure['id']]);
            }
        }

        // Check to ensure password not expired if this option is set (note ldap skips this)
        if (!$this->checkPasswordNotExpired($username)) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::getInstance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user password is expired");
            error_log($username . ": " . $ip['ip_string'] . ". user password is expired");
            $this->clearFromMemory($password);
            return false;
        }

        // PASSED
        $this->clearFromMemory($password);
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            self::resetLoginFailedCounter($username);
            // Shared per-IP counter clears only when the global opt-in
            // is set. Default off so a valid login on account A cannot
            // clear the IP counter that has been accumulating against
            // account B from the same IP.
            if (self::shouldClearIpCounterOnAuthSuccess()) {
                $this->resetIpLoginFailedCounter($ip['ip_string']);
            }
        }
        if ($this->loginAuth) {
            // Specialized code for login auth (not api auth)
            $hash = !empty($newHash) ? $newHash : $userSecure['password'];

            // If $hash is empty, then something is very wrong
            if (empty($hash)) {
                error_log('OpenEMR Error : OpenEMR is not working because broken function.');
                die("OpenEMR Error : OpenEMR is not working because broken function.");
            }
            self::setUserSessionVariables($username, $hash, $userInfo, $authGroup);
            EventAuditLogger::getInstance()->newEvent('login', $username, $authGroup, 1, "success: " . $ip['ip_string']);
        } elseif ($this->apiAuth) {
            // Set up class variables that the api will need to collect (log for API is done outside)
            $this->userId = $userInfo['id'];
            $this->userGroup = $authGroup;
        } else {
            // Log for authentication that are done, which are not api auth or login auth
            EventAuditLogger::getInstance()->newEvent('auth', $username, $authGroup, 1, "Auth success: " . $ip['ip_string']);
        }
        return true;
    }

    /**
     * Setup or change a user's password
     *
     * @param $activeUser      ID of who is trying to make the change (either the user himself, or an administrator) - CAN NOT BE EMPTY
     * @param $targetUser      ID of what account's password is to be updated (for a new user this doesn't exist yet).
     * @param $currentPwd      the active user's current password - CAN NOT BE EMPTY
     *                              - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param $newPwd          the new password for the target user
     *                              - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param $create          Are we creating a new user or
     * @param array<string, mixed> $userData  Associative array of column => value pairs for the new users row.
     * @param $new_username    The username for a new user
     * @return bool Was the password successfully updated/created? If false, then $this->errorMessage will tell you why it failed.
     */
    public function updatePassword($activeUser, $targetUser, &$currentPwd, &$newPwd, $create = false, array $userData = [], $new_username = null): bool
    {
        // Collect ip address for log
        $ip = collectIpAddresses();
        $session = SessionWrapperFactory::getInstance()->getActiveSession();

        if (empty($activeUser) || empty($currentPwd)) {
            $this->errorMessage = xl("Password update error! Empty username or password.");
            $this->clearFromMemory($currentPwd);
            $this->clearFromMemory($newPwd);
            EventAuditLogger::getInstance()->newEvent('password', $session->get('authUser'), $session->get('authProvider'), 0, "Password change Failure: " . $ip['ip_string'] . ' empty username or password');
            return false;
        }

        $userSQL = "SELECT `password`, `password_history1`, `password_history2`, `password_history3`, `password_history4`" .
            " FROM `users_secure`" .
            " WHERE `id` = ?";
        $userInfo = privQuery($userSQL, [$targetUser]);

        // Verify the active user's password
        $changingOwnPassword = $activeUser == $targetUser;

        // Set variables for log
        if ($create) {
            $event = 'password-create';
            $beginLogFail = 'Password create Failure for new user "' . $new_username . '": ' . $ip['ip_string'];
            $beginLogSuccess = 'Password create Success for new user "' . $new_username . '": ' . $ip['ip_string'];
        } else {
            if ($changingOwnPassword) {
                $event = 'password-change';
                $beginLogFail = 'Password change Failure for self: ' . $ip['ip_string'];
                $beginLogSuccess = 'Password change Success for self: ' . $ip['ip_string'];
            } else {
                $event = 'password-change';
                $beginLogFail = 'Password change Failure for user id "' . $targetUser . '": ' . $ip['ip_string'];
                $beginLogSuccess = 'Password change Success for user id "' . $targetUser . '": ' . $ip['ip_string'];
            }
        }

        // True if this is the current user changing their own password
        if ($changingOwnPassword) {
            if ($create) {
                $this->errorMessage = xl("Trying to create user with existing username!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Trying to create new user with existing username");
                return false;
            }
            if (empty($userInfo['password'])) {
                $this->errorMessage = xl("Password update error!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Current user password not found");
                return false;
            }
            // If this user is changing his own password, then confirm that they have the current password correct
            if (!AuthHash::passwordVerify($currentPwd, $userInfo['password'])) {
                $this->errorMessage = xl("Incorrect password!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Incorrect password");
                return false;
            }
        } else {
            // If this is an administrator changing someone else's password, then check that they have this privilege
            if (!AclMain::aclCheckCore('admin', 'users')) {
                $this->errorMessage = xl("Not authorized to manage users!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " User not authorized to manage other user's passwords");
                return false;
            }

            // If this is an administrator changing someone else's password, then authenticate the administrator
            if (self::useActiveDirectory()) {
                if (empty($session->get('authUser'))) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " authUser session is empty");
                    return false;
                }
                if (!$this->activeDirectoryValidation($session->get('authUser'), $currentPwd)) {
                    $this->errorMessage = xl("Incorrect password!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Incorrect password");
                    return false;
                }
            } else {
                $adminSQL = "SELECT `password`" .
                    " FROM `users_secure`" .
                    " WHERE `id` = ?";
                $adminInfo = privQuery($adminSQL, [$activeUser]);
                if (empty($adminInfo) || empty($adminInfo['password'])) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Unable to find user credentials");
                    return false;
                }
                if (!AuthHash::passwordVerify($currentPwd, $adminInfo['password'])) {
                    $this->errorMessage = xl("Incorrect password!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Incorrect password");
                    return false;
                }
            }
        }

        // End active user check (can now clear $currentPwd since no longer used)
        $this->clearFromMemory($currentPwd);

        // Use case here is for when an administrator is adding a new user that will be using LDAP for authentication
        // (note that in this case, a random password is prepared for the new user below that is stored in OpenEMR
        //  and used only for session confirmations; the primary authentication for the new user will be done via
        //  LDAP)
        $ldapDummyPassword = false;
        if ($create && ($userInfo === false) && (!empty($new_username)) && (self::useActiveDirectory($new_username))) {
            $ldapDummyPassword = true;
            $newPwd = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
        }

        // Ensure new password is not blank
        if (empty($newPwd)) {
            $this->errorMessage = xl("Empty Password Not Allowed");
            $this->clearFromMemory($newPwd);
            EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Empty password");
            return false;
        }

        // Ensure password is long enough, if this option is on (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testMinimumPasswordLength($newPwd))) {
            $this->errorMessage = xl("Password not long enough");
            $this->clearFromMemory($newPwd);
            EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Password not long enough");
            return false;
        }

        // Ensure password is not too long (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testMaximumPasswordLength($newPwd))) {
            $this->errorMessage = xl("Password too long");
            $this->clearFromMemory($newPwd);
            EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Password too long");
            return false;
        }

        // Ensure new password is strong enough, if this option is on (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testPasswordStrength($newPwd))) {
            $this->errorMessage = xl("Password not strong enough");
            $this->clearFromMemory($newPwd);
            EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Password not strong enough");
            return false;
        }

        if ($userInfo === false) {
            // No userInfo means a new user
            // In these cases don't worry about password history
            if ($create) {
                if (empty($new_username)) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " New user username is empty");
                    return false;
                }
                // Insert the new user row from structured data
                if ($userData === []) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " No user data provided for new user");
                    return false;
                }
                $columns = array_map(fn($col): string => '`' . $col . '`', array_keys($userData));
                $placeholders = array_fill(0, count($userData), '?');
                $insertSql = 'INSERT INTO `users` (' . implode(', ', $columns) . ') VALUES (' . implode(', ', $placeholders) . ')';
                $newUserId = QueryUtils::sqlInsert($insertSql, array_values($userData));
                // Create the new user password hash
                $hash = $this->authHashAuth->passwordHash($newPwd);
                if (empty($hash)) {
                    // Something is seriously wrong
                    error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " OpenEMR Error : OpenEMR is not working because unable to create a hash.");
                    die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
                }
                // Store the new user credentials
                $passwordSQL = "INSERT INTO `users_secure`" .
                    " (`id`,`username`,`password`,`last_update_password`)" .
                    " VALUES (?,?,?,NOW()) ";
                QueryUtils::sqlInsert($passwordSQL, [$newUserId, $new_username, $hash]);
            } else {
                $this->errorMessage = xl("Missing user credentials") . ":" . $targetUser;
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Missing user credentials");
                return false;
            }
        } else { // We are trying to update the password of an existing user
            if ($create) {
                $this->errorMessage = xl("Trying to create user with existing username!");
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Trying to create new user with existing username");
                return false;
            }

            if (empty($targetUser)) {
                $this->errorMessage = xl("Password update error!");
                $this->clearFromMemory($newPwd);
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " targetuser is empty");
                return false;
            }

            if ((OEGlobalsBag::getInstance()->get('password_history') != 0) && (check_integer(OEGlobalsBag::getInstance()->get('password_history')))) {
                // password reuse disallowed
                $pass_reuse_fail = false;
                if ((OEGlobalsBag::getInstance()->get('password_history') > 0) && (AuthHash::passwordVerify($newPwd, $userInfo['password']))) {
                    $pass_reuse_fail = true;
                }
                if ((OEGlobalsBag::getInstance()->get('password_history') > 1) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history1']))) {
                    $pass_reuse_fail = true;
                }
                if ((OEGlobalsBag::getInstance()->get('password_history') > 2) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history2']))) {
                    $pass_reuse_fail = true;
                }
                if ((OEGlobalsBag::getInstance()->get('password_history') > 3) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history3']))) {
                    $pass_reuse_fail = true;
                }
                if ((OEGlobalsBag::getInstance()->get('password_history') > 4) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history4']))) {
                    $pass_reuse_fail = true;
                }
                if ($pass_reuse_fail) {
                    $this->errorMessage = xl("Reuse of previous passwords not allowed!");
                    $this->clearFromMemory($newPwd);
                    EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " Reuse of previous passwords not allowed");
                    return false;
                }
            }

            // Everything checks out at this point, so update the password record
            $newHash = $this->authHashAuth->passwordHash($newPwd);
            if (empty($newHash)) {
                // Something is seriously wrong
                $this->clearFromMemory($newPwd);
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 0, $beginLogFail . " OpenEMR Error : OpenEMR is not working because unable to create a hash.");
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }

            $updateParams = [];
            $updateSQL = "UPDATE `users_secure`";
            $updateSQL .= " SET `last_update_password` = NOW()";
            $updateSQL .= ", `login_fail_counter` = 0";
            $updateSQL .= ", `last_login_fail` = null";
            $updateSQL .= ", `auto_block_emailed` = 0";
            $updateSQL .= ", `password` = ?";
            array_push($updateParams, $newHash);
            if (OEGlobalsBag::getInstance()->get('password_history') != 0) {
                $updateSQL .= ", `password_history1` = ?";
                array_push($updateParams, $userInfo['password']);
                $updateSQL .= ", `password_history2` = ?";
                array_push($updateParams, $userInfo['password_history1']);
                $updateSQL .= ", `password_history3` = ?";
                array_push($updateParams, $userInfo['password_history2']);
                $updateSQL .= ", `password_history4` = ?";
                array_push($updateParams, $userInfo['password_history3']);
            }

            $updateSQL .= " WHERE `id` = ?";
            array_push($updateParams, $targetUser);

            // Password write + OAuth2 token revocations must succeed or
            // fail together. Without the transaction, a UUID lookup or
            // revocation UPDATE that throws would leave users_secure
            // updated (and the session's authPass already flipped) while
            // still-valid refresh tokens continued to mint API access
            // with the OLD credentials for weeks — the exact scenario
            // the revocation exists to prevent. Also defer the session
            // authPass write until after the transaction commits so a
            // rollback leaves the session consistent with the persisted
            // hash.
            //
            // api_refresh_token.user_id and api_token.user_id store the
            // user's UUID *string*, not the numeric users.id, so
            // backfill any missing users.uuid before the transaction
            // opens (createMissingUuidForRow is idempotent).
            //
            // On the `create` path a brand-new user has no tokens to
            // revoke — the block still runs so that a missing/broken
            // UUID would surface immediately, but the two UPDATEs are
            // no-ops.
            if (!is_int($targetUser) && !is_string($targetUser)) {
                // Every caller passes an int users.id (or a numeric
                // string). A non-scalar id would be an outright
                // programming bug — refuse rather than casting it to
                // a truthy string and issuing a WHERE clause that
                // matches nothing.
                throw new \InvalidArgumentException(
                    'updatePassword: $targetUser must be int|string'
                );
            }
            $targetUserId = $targetUser;
            UuidRegistry::createMissingUuidForRow('users', 'id', $targetUserId);
            QueryUtils::inTransaction(function () use ($updateSQL, $updateParams, $targetUserId): void {
                // Use the throwing helper so a SQL failure engages
                // the transaction's rollback path — privStatement()
                // calls exit(1) on failure and never returns, which
                // would leave the transaction dangling.
                QueryUtils::sqlStatementThrowException($updateSQL, $updateParams);
                $userUuidRow = QueryUtils::querySingleRow(
                    "SELECT `uuid` FROM `users` WHERE `id` = ?",
                    [$targetUserId]
                );
                $userUuidBytes = is_array($userUuidRow) ? ($userUuidRow['uuid'] ?? null) : null;
                if (!is_string($userUuidBytes) || $userUuidBytes === '') {
                    // users.uuid is nullable in the schema so an
                    // existing user really can have no UUID. Silently
                    // skipping the revocation UPDATEs here would let
                    // stale refresh_tokens survive a password change
                    // that was probably triggered by credential
                    // compromise — the whole reason the revocation
                    // block exists. Throw so the transaction rolls
                    // back the password write; the caller sees
                    // failure and can retry after the UUID is
                    // backfilled.
                    throw new \RuntimeException(
                        'Cannot revoke API tokens for user id=' . $targetUserId
                            . ' — users.uuid resolution failed. Password update rolled back.'
                    );
                }
                $userUuidStr = UuidRegistry::uuidToString($userUuidBytes);
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `api_refresh_token` SET `revoked` = 1 "
                        . "WHERE `user_id` = ? AND `revoked` = 0",
                    [$userUuidStr]
                );
                QueryUtils::sqlStatementThrowException(
                    "UPDATE `api_token` SET `revoked` = 1 "
                        . "WHERE `user_id` = ? AND `revoked` = 0",
                    [$userUuidStr]
                );
            });

            // If the user is changing their own password, update the
            // session — only after the transaction committed so a
            // rollback leaves authPass matching the still-persisted
            // old hash.
            if ($changingOwnPassword) {
                $session->set('authPass', $newHash);
            }
        }

        // Done with $newPwd, so can clear it now
        $this->clearFromMemory($newPwd);
        EventAuditLogger::getInstance()->newEvent($event, $session->get('authUser'), $session->get('authProvider'), 1, $beginLogSuccess);
        return true;
    }

    /**
     * @return mixed
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return mixed
     */
    public function getUserGroup()
    {
        return $this->userGroup;
    }

    /**
     * @return mixed
     */
    public function getPatientId()
    {
        return $this->patientId;
    }

    /**
     * Ensure user hash remains valid (for example, if user is deactivated or password is changed, then
     * this will not allow the same user in another session continue to use OpenEMR)
     *
     * This function is static since requires no class specific defines
     *
     * @return bool
     */
    public static function authCheckSession(): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if ((!empty($session->get('authUserID'))) && (!empty($session->get('authUser'))) && (!empty($session->get('authPass')))) {
            $authDB = privQuery("SELECT `users`.`username`, `users_secure`.`password`" .
                " FROM `users`, `users_secure`" .
                " WHERE `users`.`id` = ? " .
                " AND `users`.`id` = `users_secure`.`id` " .
                " AND BINARY `users`.`username` = `users_secure`.`username`" .
                " AND `users`.`active` = 1", [$session->get('authUserID')]);
            if (
                (!empty($authDB)) &&
                (!empty($authDB['username'])) &&
                (!empty($authDB['password'])) &&
                ($session->get('authUser') == $authDB['username']) &&
                (hash_equals($session->get('authPass'), $authDB['password']))
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Check if the current or a specified user logs in with LDAP.
     *
     * This function is static since requires no class specific defines
     *
     * @param $user
     * @return bool
     */
    public static function useActiveDirectory($user = ''): bool
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        if (!OEGlobalsBag::getInstance()->getBoolean('gbl_ldap_enabled')) {
            return false;
        }
        if ($user == '') {
            $user = $session->get('authUser');
        }
        $exarr = explode(',', OEGlobalsBag::getInstance()->getString('gbl_ldap_exclusions'));
        foreach ($exarr as $ex) {
            if ($user == trim($ex)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Validation of user and password using LDAP.
     *
     * $pass passed by reference to prevent storage of pass in memory
     *
     * @param $user
     * @param $pass
     * @return bool
     */
    private function activeDirectoryValidation($user, &$pass): bool
    {
        // Make sure the connection is not anonymous.
        if ($pass === '' || preg_match('/^\0/', (string) $pass) || !preg_match('/^[\w.-]+$/', (string) $user)) {
            error_log("Empty user or password for activeDirectoryValidation()");
            return false;
        }

        // below can be uncommented for detailed debugging
        // ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

        $ldapconn = ldap_connect(OEGlobalsBag::getInstance()->getString('gbl_ldap_host'));
        if ($ldapconn) {
            // block of code to support encryption
            $isTls = false;
            if (
                file_exists(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-ca") &&
                file_exists(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-cert") &&
                file_exists(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-key")
            ) {
                // set ca cert and client key/cert
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-ca")) {
                    error_log("Setting ldap-ca certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CERTFILE, OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-cert")) {
                    error_log("Setting ldap-cert client certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_KEYFILE, OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-key")) {
                    error_log("Setting ldap-cert client key failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_DEMAND)) {
                    error_log("Setting require_cert to demand failed");
                }
                $isTls = true;
            } elseif (file_exists(OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-ca")) {
                // set ca cert
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, OEGlobalsBag::getInstance()->get('OE_SITE_DIR') . "/documents/certificates/ldap-ca")) {
                    error_log("Setting ldap-ca certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CERTFILE, '')) {
                    error_log("Clearing ldap-cert client certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_KEYFILE, '')) {
                    error_log("Clearing ldap-cert client key failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_TRY)) {
                    error_log("Setting require_cert to try failed");
                }
                $isTls = true;
            }

            if (!ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                error_log("Setting LDAP v3 protocol failed");
            }
            if (!ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0)) {
                error_log("Disabling LDAP referrals failed");
            }

            if ($isTls) {
                if (!ldap_start_tls($ldapconn)) {
                    error_log("ldap TLS (ldap_start_tls()) failed");
                    return false;
                }
            }

            $ldapbind = ldap_bind(
                $ldapconn,
                str_replace('{login}', $user, OEGlobalsBag::getInstance()->getString('gbl_ldap_dn')),
                $pass
            );
            if ($ldapbind) {
                ldap_unbind($ldapconn);
                return true;
            }
        } else {
            error_log("ldap_connect() failed");
        }
        return false;
    }

    /**
     * Function to centralize the rehash process
     * It will return the new hash
     *
     * $password passed by reference to prevent storage of pass in memory
     *
     * @param $username
     * @param $password
     * @return \s|string|void
     */
    private function rehashPassword($username, &$password)
    {
        if (self::useActiveDirectory($username)) {
            // rehash for LDAP
            $newRandomDummyPassword = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            $phash = $this->authHashAuth->passwordHash($newRandomDummyPassword);
            $this->clearFromMemory($newRandomDummyPassword);
        } else {
            // rehash for standard
            $phash = $this->authHashAuth->passwordHash($password);
        }

        if (empty($phash)) {
            // Something is seriously wrong
            $this->clearFromMemory($password);
            error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
            die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
        }

        // return the rehash
        return $phash;
    }

    /**
     * Does the new password meet the minimum length requirements?
     *
     * @param $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return bool is the password long enough?
     */
    private function testMinimumPasswordLength(&$pwd): bool
    {
        if ((OEGlobalsBag::getInstance()->get('gbl_minimum_password_length') != 0) && (check_integer(OEGlobalsBag::getInstance()->get('gbl_minimum_password_length')))) {
            if (strlen((string) $pwd) < OEGlobalsBag::getInstance()->get('gbl_minimum_password_length')) {
                $this->errorMessage = xl("Password too short. Minimum characters required") . ": " . OEGlobalsBag::getInstance()->get('gbl_minimum_password_length');
                return false;
            }
        }

        return true;
    }

    /**
     * Does the new password meet the maximum length requirement?
     *
     * The maximum characters used in BCRYPT hash algorithm is 72 (the additional characters
     *  are simply truncated, so does not break things, but it does give the erroneous
     *  impression that they are used to create the hash; for example, if I created a
     *  password with 100 characters, then only the first 72 characters would be needed
     *  when authenticate), which is why the 'Maximum Password Length' global setting is
     *  set to this number in default installations. Recommend only changing the
     *  'Maximum Password Length' global setting if know what you are doing (for example, if using
     *  argon hashing and wish to allow larger passwords).
     *
     * @param $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return bool is the password short enough?
     */
    private function testMaximumPasswordLength(&$pwd): bool
    {
        if ((!empty(OEGlobalsBag::getInstance()->get('gbl_maximum_password_length'))) && (check_integer(OEGlobalsBag::getInstance()->get('gbl_maximum_password_length')))) {
            if (strlen((string) $pwd) > OEGlobalsBag::getInstance()->get('gbl_maximum_password_length')) {
                $this->errorMessage = xl("Password too long. Maximum characters allowed") . ": " . OEGlobalsBag::getInstance()->get('gbl_maximum_password_length');
                return false;
            }
        }

        return true;
    }

    /**
     * Does the new password meet the strength requirements?
     *
     * @param $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return bool is the password strong enough?
     */
    private function testPasswordStrength(&$pwd): bool
    {
        if (OEGlobalsBag::getInstance()->getBoolean('secure_password')) {
            $features = 0;
            $reg_security = ["/[a-z]+/","/[A-Z]+/","/\d+/","/[\W_]+/"];
            foreach ($reg_security as $expr) {
                if (preg_match($expr, (string) $pwd)) {
                    $features++;
                }
            }

            if ($features < 4) {
                $this->errorMessage = xl("Password does not meet minimum requirements and should contain at least each of the following items: A number, a lowercase letter, an uppercase letter, a special character (not a letter or number).");
                return false;
            }
        }

        return true;
    }

    /**
     * @param $user
     * @return bool
     */
    private function checkPasswordNotExpired($user): bool
    {
        if ((OEGlobalsBag::getInstance()->getInt('password_expiration_days') === 0) || self::useActiveDirectory($user)) {
            // skip the check if turned off or using active directory for login
            return true;
        }
        $query = privQuery("SELECT `last_update_password` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
        if ((!empty($query)) && (!empty($query['last_update_password'])) && (check_integer(OEGlobalsBag::getInstance()->getInt('password_expiration_days'))) && (check_integer(OEGlobalsBag::getInstance()->getInt('password_grace_time')))) {
            $current_date = date("Y-m-d");
            $expiredPlusGraceTime = date("Y-m-d", strtotime($query['last_update_password'] . "+" . (OEGlobalsBag::getInstance()->getInt('password_expiration_days') + OEGlobalsBag::getInstance()->getInt('password_grace_time')) . " days"));
            if (strtotime($current_date) > strtotime($expiredPlusGraceTime)) {
                error_log("OpenEMR Notice: Password is expired and outside of grace period. User: " . $user);
                return false;
            }
        } else {
            error_log("OpenEMR ERROR: there is a problem when trying to check if user's password is expired");
        }
        return true;
    }

    /**
     * @param bool $showOnlyWithCount
     * @param bool $showOnlyManuallyBlocked
     * @param bool $showOnlyAutoBlocked
     * @return false|\ADORecordSet
     */
    public static function collectIpLoginFailsSql(bool $showOnlyWithCount, bool $showOnlyManuallyBlocked, bool $showOnlyAutoBlocked)
    {
        $sqlBind = [];
        $where = [];
        if ($showOnlyWithCount) {
            $where[] = ' (`ip_login_fail_counter` > 0) ';
        }
        if ($showOnlyManuallyBlocked) {
            $where[] = ' (`ip_force_block` = 1) ';
        }
        if ($showOnlyAutoBlocked) {
            if (OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins') != 0) {
                if (!empty(OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins')) && OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins') > 0) {
                    $where[] = ' (ip_login_fail_counter > ? AND TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) < ?) ';
                    array_push($sqlBind, OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins'), OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins'));
                } else {
                    $where[] = ' (ip_login_fail_counter > ?) ';
                    array_push($sqlBind, OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins'));
                }
            }
        }
        if (!empty($where)) {
            $where = implode('AND', $where);
            $where = 'WHERE ' . $where;
        } else {
            $where = '';
        }

        return sqlStatement("SELECT `id`, `ip_string`, `ip_force_block`, `ip_no_prevent_timing_attack`, `total_ip_login_fail_counter`, `ip_login_fail_counter`, `ip_last_login_fail`, TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) as `seconds_last_ip_login_fail` FROM `ip_tracking` $where ORDER BY `ip_last_login_fail` DESC, `total_ip_login_fail_counter` DESC", $sqlBind);
    }

    /**
     * @param string $ipString
     * @return void
     */
    private function setupIpLoginFailedCounter(string $ipString): void
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }
        $sql = sqlQuery("SELECT `ip_string` FROM `ip_tracking` WHERE `ip_string` = ?", [$ipString]);
        if (empty($sql['ip_string'])) {
            sqlStatement("INSERT INTO `ip_tracking` (`ip_string`) VALUES (?)", [$ipString]);
        }
    }

    /**
     * @param string $user
     * @return array
     */
    private function checkLoginFailedCounter(string $user): array
    {
        if (OEGlobalsBag::getInstance()->getInt('password_max_failed_logins') === 0) {
            // skip the check if turned off
            return ['pass' => true, 'email_notification' => null];
        }

        $query = privQuery("SELECT `auto_block_emailed`, `login_fail_counter`, TIMESTAMPDIFF(SECOND, `last_login_fail`, NOW()) as `seconds_last_login_fail` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
        if ($query['login_fail_counter'] >= OEGlobalsBag::getInstance()->getInt('password_max_failed_logins')) {
            if (
                !empty(OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins')) &&
                OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins') > 0 &&
                !empty($query['seconds_last_login_fail']) &&
                $query['seconds_last_login_fail'] > OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins')
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will pass
                //  (also need to reset the counter)
                self::resetLoginFailedCounter($user);
                return ['pass' => true, 'email_notification' => null];
            }
            $emailNotification = empty($query['auto_block_emailed']);
            return ['pass' => false, 'email_notification' => $emailNotification];
        } else {
            return ['pass' => true, 'email_notification' => null];
        }
    }

    /**
     * @param string $ipString
     * @return array
     */
    private function checkIpLoginFailedCounter(string $ipString): array
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        if (OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins') === 0) {
            // skip the check if turned off
            return ['pass' => true, 'force_block' => null, 'skip_timing_attack' => null, 'email_notification' => null];
        }

        $query = sqlQuery("SELECT `ip_auto_block_emailed`, `ip_force_block`, `ip_no_prevent_timing_attack`, `ip_login_fail_counter`, TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) as `seconds_last_ip_login_fail` FROM `ip_tracking` WHERE `ip_string` = ?", [$ipString]);
        if ($query['ip_force_block'] == 1) {
            if ($query['ip_no_prevent_timing_attack'] == 1) {
                return ['pass' => false, 'force_block' => true, 'skip_timing_attack' => true, 'email_notification' => false];
            } else {
                return ['pass' => false, 'force_block' => true, 'skip_timing_attack' => false, 'email_notification' => false];
            }
        }
        if ($query['ip_login_fail_counter'] >= OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins')) {
            if (
                !empty(OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins')) &&
                OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins') > 0 &&
                !empty($query['seconds_last_ip_login_fail']) &&
                $query['seconds_last_ip_login_fail'] > OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins')
            ) {
                // the last ip login fail was longer than the timeout required to reset the failed logins, so will pass
                //  (also need to reset the counter)
                $this->resetIpLoginFailedCounter($ipString);
                return ['pass' => true, 'force_block' => null, 'skip_timing_attack' => null, 'email_notification' => null];
            }
            $emailNotification = empty($query['ip_auto_block_emailed']);
            return ['pass' => false, 'force_block' => false, 'skip_timing_attack' => false, 'email_notification' => $emailNotification];
        } else {
            return ['pass' => true, 'force_block' => null, 'skip_timing_attack' => null, 'email_notification' => null];
        }
    }

    /**
     * @param $user
     * @return void
     */
    public static function resetLoginFailedCounter($user)
    {
        privStatement("UPDATE `users_secure` SET `login_fail_counter` = 0, `last_login_fail` = null, `auto_block_emailed` = 0 WHERE BINARY `username` = ?", [$user]);
    }

    /**
     * @param string $ipString
     * @return void
     */
    private function resetIpLoginFailedCounter(string $ipString): void
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        sqlStatement("UPDATE `ip_tracking` SET `ip_login_fail_counter` = 0, `ip_last_login_fail` = null, `ip_auto_block_emailed` = 0 WHERE `ip_string` = ?", [$ipString]);
    }

    /**
     * Per-portal-account block gate. Returns true if this
     * portal_login_username has exceeded password_max_failed_logins
     * without an elapsed reset window. Independent of the per-IP
     * counter — a valid login on a different portal account does
     * NOT clear this counter, so an attacker cannot bypass by
     * cycling between accounts they own.
     *
     * Unknown usernames return false (no row → no block). The
     * per-IP counter still throttles unknown-user brute force.
     */
    private function isPortalAccountBlocked(mixed $username): bool
    {
        $max = OEGlobalsBag::getInstance()->getInt('password_max_failed_logins');
        if ($max === 0 || !is_string($username) || $username === '') {
            return false;
        }
        $row = QueryUtils::querySingleRow(
            "SELECT `portal_fail_counter`, `portal_last_fail`, "
                . "TIMESTAMPDIFF(SECOND, `portal_last_fail`, NOW()) AS `seconds_last_fail` "
                . "FROM `patient_access_onsite` WHERE BINARY `portal_login_username` = ?",
            [$username]
        );
        $counter = is_array($row) && is_numeric($row['portal_fail_counter'] ?? null)
            ? (int) $row['portal_fail_counter']
            : 0;
        if ($counter < $max) {
            return false;
        }
        $window = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');
        $seconds = is_numeric($row['seconds_last_fail'] ?? null)
            ? (int) $row['seconds_last_fail']
            : 0;
        if ($window > 0 && $seconds > $window) {
            // Reset only if the row still matches the counter +
            // timestamp we observed. If a concurrent failure or
            // reset raced in between, affectedRows will be 0 and we
            // stay in the "still blocked" state — the next attempt
            // reads fresh values.
            $lastFail = is_array($row) ? ($row['portal_last_fail'] ?? null) : null;
            QueryUtils::sqlStatementThrowException(
                "UPDATE `patient_access_onsite` "
                    . "SET `portal_fail_counter` = 0, `portal_last_fail` = NULL "
                    . "WHERE BINARY `portal_login_username` = ? "
                    . "AND `portal_fail_counter` = ? "
                    . "AND `portal_last_fail` <=> ?",
                [$username, $counter, $lastFail],
                noLog: true
            );
            if (QueryUtils::affectedRows() === 1) {
                return false;
            }
        }
        return true;
    }

    /**
     * Bump the per-account portal failure counter. Silent no-op if
     * the row does not exist (unknown-user attempts still bump the
     * per-IP counter via the reject helper).
     */
    private function incrementPortalAccountFailedCounter(mixed $username): void
    {
        if (!is_string($username) || $username === '') {
            return;
        }
        // Single-statement atomic reset-or-increment: if the last
        // failure is outside the configured window, reset the
        // counter to 1 for this fresh failure; otherwise increment.
        // Doing this in one UPDATE (rather than SELECT-then-UPDATE)
        // closes a race where a concurrent failure between the
        // read and write could be silently overwritten by the
        // reset. window=0 disables the reset — the IF() short-
        // circuits on the leading `? > 0` guard so the increment
        // always wins in that case.
        $window = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');
        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_access_onsite` "
                . "SET `portal_fail_counter` = IF(? > 0 AND TIMESTAMPDIFF(SECOND, `portal_last_fail`, NOW()) > ?, 1, `portal_fail_counter` + 1), "
                . "`portal_last_fail` = NOW() "
                . "WHERE BINARY `portal_login_username` = ?",
            [$window, $window, $username],
            noLog: true
        );
    }

    /**
     * Zero the per-account portal failure counter on successful
     * authentication for that specific account only. Deliberately
     * does NOT touch the per-IP counter.
     */
    private function resetPortalAccountFailedCounter(mixed $username): void
    {
        if (!is_string($username) || $username === '') {
            return;
        }
        QueryUtils::sqlStatementThrowException(
            "UPDATE `patient_access_onsite` "
                . "SET `portal_fail_counter` = 0, `portal_last_fail` = NULL "
                . "WHERE BINARY `portal_login_username` = ?",
            [$username],
            noLog: true
        );
    }

    /**
     * Public entry point for counting a failed post-password login challenge
     * (TOTP, U2F, or any other second-factor check that runs after the
     * password step). Bumps both the per-user counter on `users_secure` and
     * the per-IP counter on `ip_tracking`, so the next confirmPassword()
     * gate sees the failure and can enforce the standard user/IP lockout.
     *
     * @param string|null $username user whose second-factor attempt failed
     */
    public function recordFailedAuthChallenge(?string $username): void
    {
        if ($username !== null && $username !== '') {
            $this->incrementLoginFailedCounter($username);
        }
        $ip = collectIpAddresses();
        if ($ip['ip_string'] !== '') {
            $this->setupIpLoginFailedCounter($ip['ip_string']);
            $this->incrementIpLoginFailedCounter($ip['ip_string']);
        }
    }

    /**
     * Bump the MFA-specific per-user + per-IP failure counters. Kept
     * separate from recordFailedAuthChallenge / login_fail_counter so
     * an in-progress MFA brute force is not zeroed out by the
     * password-verify-success reset that confirmPassword performs on
     * every attempt (a MFA-enrolled login runs confirmPassword →
     * password succeeds → counters reset → checkTOTP fails → counters
     * would only ever grow to 1 if we shared the counter with the
     * password path).
     *
     * @param string|null $username the user whose MFA attempt failed
     */
    public function recordFailedMfaChallenge(?string $username): void
    {
        if ($username !== null && $username !== '') {
            $this->incrementMfaFailCounter($username);
        }
        $ip = collectIpAddresses();
        if ($ip['ip_string'] !== '') {
            $this->incrementIpMfaLoginFailCounter($ip['ip_string']);
        }
    }

    /**
     * Zero the MFA failure counters on full-auth success (password +
     * MFA both passed). Callers must invoke this only after MFA has
     * been verified — a bare confirmPassword success is not enough.
     *
     * The per-user counter always resets. The shared per-IP counter
     * only resets when the clear_ip_counter_on_auth_success global is
     * enabled; default off so a valid MFA on account A cannot clear
     * an IP counter that has been accumulating against account B
     * from the same IP.
     *
     * @param string|null $username user whose MFA challenge succeeded
     * @param string      $ipString caller's IP (from collectIpAddresses)
     */
    public static function resetMfaChallengeCounters(?string $username, string $ipString): void
    {
        if ($username !== null && $username !== '') {
            self::resetMfaUserFailCounter($username);
        }
        if ($ipString !== '' && self::shouldClearIpCounterOnAuthSuccess()) {
            self::resetMfaIpFailCounter($ipString);
        }
    }

    /**
     * Zero the per-user MFA fail counter for the given user
     * unconditionally. Used by success-path callers via
     * resetMfaChallengeCounters() and by the time-based reset-window
     * branch inside isMfaChallengeBlocked() (which must run even when
     * the shared-IP-clear-on-success global is off — a legit user
     * whose lockout window has elapsed still needs recovery).
     */
    private static function resetMfaUserFailCounter(string $username): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE `users_secure` SET `mfa_fail_counter` = 0, `mfa_last_fail` = NULL "
                . "WHERE BINARY `username` = ?",
            [$username],
            noLog: true
        );
    }

    /**
     * Zero the per-IP MFA fail counter for the given IP
     * unconditionally. See resetMfaUserFailCounter() docblock — same
     * reasoning for the IP axis.
     */
    private static function resetMfaIpFailCounter(string $ipString): void
    {
        QueryUtils::sqlStatementThrowException(
            "UPDATE `ip_tracking` SET `mfa_login_fail_counter` = 0, `mfa_last_login_fail` = NULL "
                . "WHERE `ip_string` = ?",
            [$ipString],
            noLog: true
        );
    }

    /**
     * Whether a successful authentication should clear the shared
     * per-IP failed-login counters (both the standard
     * ip_tracking.ip_login_fail_counter and the MFA-specific
     * ip_tracking.mfa_login_fail_counter). Off by default so a
     * valid login on one account cannot bypass the IP throttle that
     * is being accumulated against another account from the same IP.
     * Deployments behind shared NAT that prefer the convenience of
     * a clean-on-success can enable
     * `clear_ip_counter_on_auth_success` in globals.
     */
    private static function shouldClearIpCounterOnAuthSuccess(): bool
    {
        return OEGlobalsBag::getInstance()->getBoolean('clear_ip_counter_on_auth_success');
    }

    /**
     * Check whether MFA challenges from this user / IP are currently
     * over the standard lockout thresholds. Called by
     * MfaUtils::checkTOTP before validating so a locked-out attacker
     * cannot grind further codes even against an already-blocked
     * counter — same shape as checkLoginFailedCounter uses for
     * password attempts.
     *
     * Reuses the existing password_max_failed_logins /
     * ip_max_failed_logins thresholds AND the matching
     * time_reset_password_max_failed_logins /
     * ip_time_reset_password_max_failed_logins reset windows so admin
     * tuning applies uniformly across password and MFA gates.
     * Without the reset windows a legit user tripping the MFA
     * threshold would be soft-locked indefinitely — validation is
     * skipped by this gate before a good code could clear the
     * counter.
     *
     * @param string|null $username may be null for the OAuth2 password
     *                              grant path when identity is not yet
     *                              resolved
     * @param string      $ipString caller's IP (from collectIpAddresses)
     */
    public function isMfaChallengeBlocked(?string $username, string $ipString): bool
    {
        $userMax = OEGlobalsBag::getInstance()->getInt('password_max_failed_logins');
        if ($userMax > 0 && $username !== null && $username !== '') {
            $row = QueryUtils::querySingleRow(
                "SELECT `mfa_fail_counter`, `mfa_last_fail`, "
                    . "TIMESTAMPDIFF(SECOND, `mfa_last_fail`, NOW()) AS `seconds_last_fail` "
                    . "FROM `users_secure` WHERE BINARY `username` = ?",
                [$username]
            );
            $counter = is_array($row) && is_numeric($row['mfa_fail_counter'] ?? null)
                ? (int) $row['mfa_fail_counter']
                : 0;
            if ($counter >= $userMax) {
                $userWindow = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');
                $seconds = is_numeric($row['seconds_last_fail'] ?? null)
                    ? (int) $row['seconds_last_fail']
                    : 0;
                if ($userWindow > 0 && $seconds > $userWindow) {
                    // Conditional reset: only clears the user counter
                    // if the row still matches what we observed. If a
                    // concurrent failure raced in, affectedRows === 0
                    // and we treat the state as still blocked. Also
                    // isolate the reset to the user axis only — the IP
                    // counter may be independently over its threshold
                    // with a fresh timestamp, and blindly clearing it
                    // would silently release an active IP lockout.
                    $lastFail = $row['mfa_last_fail'] ?? null;
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE `users_secure` "
                            . "SET `mfa_fail_counter` = 0, `mfa_last_fail` = NULL "
                            . "WHERE BINARY `username` = ? "
                            . "AND `mfa_fail_counter` = ? "
                            . "AND `mfa_last_fail` <=> ?",
                        [$username, $counter, $lastFail],
                        noLog: true
                    );
                    if (QueryUtils::affectedRows() !== 1) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        $ipMax = OEGlobalsBag::getInstance()->getInt('ip_max_failed_logins');
        if ($ipMax > 0 && $ipString !== '') {
            $row = QueryUtils::querySingleRow(
                "SELECT `mfa_login_fail_counter`, `mfa_last_login_fail`, "
                    . "TIMESTAMPDIFF(SECOND, `mfa_last_login_fail`, NOW()) AS `seconds_last_fail` "
                    . "FROM `ip_tracking` WHERE `ip_string` = ?",
                [$ipString]
            );
            $counter = is_array($row) && is_numeric($row['mfa_login_fail_counter'] ?? null)
                ? (int) $row['mfa_login_fail_counter']
                : 0;
            if ($counter >= $ipMax) {
                $ipWindow = OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins');
                $seconds = is_numeric($row['seconds_last_fail'] ?? null)
                    ? (int) $row['seconds_last_fail']
                    : 0;
                if ($ipWindow > 0 && $seconds > $ipWindow) {
                    // Same isolation as above — only reset the IP
                    // counter, and only if the row still matches.
                    $lastFail = $row['mfa_last_login_fail'] ?? null;
                    QueryUtils::sqlStatementThrowException(
                        "UPDATE `ip_tracking` "
                            . "SET `mfa_login_fail_counter` = 0, `mfa_last_login_fail` = NULL "
                            . "WHERE `ip_string` = ? "
                            . "AND `mfa_login_fail_counter` = ? "
                            . "AND `mfa_last_login_fail` <=> ?",
                        [$ipString, $counter, $lastFail],
                        noLog: true
                    );
                    if (QueryUtils::affectedRows() !== 1) {
                        return true;
                    }
                } else {
                    return true;
                }
            }
        }
        return false;
    }

    private function incrementMfaFailCounter(string $username): void
    {
        // Single-statement atomic reset-or-increment — see the
        // matching comment on incrementPortalAccountFailedCounter()
        // for rationale.
        $window = OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins');
        QueryUtils::sqlStatementThrowException(
            "UPDATE `users_secure` "
                . "SET `mfa_fail_counter` = IF(? > 0 AND TIMESTAMPDIFF(SECOND, `mfa_last_fail`, NOW()) > ?, 1, `mfa_fail_counter` + 1), "
                . "`mfa_last_fail` = NOW() "
                . "WHERE BINARY `username` = ?",
            [$window, $window, $username],
            noLog: true
        );
    }

    private function incrementIpMfaLoginFailCounter(string $ipString): void
    {
        // Ensure a row exists — setupIpLoginFailedCounter mirrors this
        // pattern for the password path.
        $this->setupIpLoginFailedCounter($ipString);
        // Single-statement atomic reset-or-increment — see the
        // matching comment on incrementPortalAccountFailedCounter()
        // for rationale.
        $window = OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins');
        QueryUtils::sqlStatementThrowException(
            "UPDATE `ip_tracking` "
                . "SET `mfa_login_fail_counter` = IF(? > 0 AND TIMESTAMPDIFF(SECOND, `mfa_last_login_fail`, NOW()) > ?, 1, `mfa_login_fail_counter` + 1), "
                . "`mfa_last_login_fail` = NOW() "
                . "WHERE `ip_string` = ?",
            [$window, $window, $ipString],
            noLog: true
        );
    }

    /**
     * @param $user
     * @return void
     */
    private function incrementLoginFailedCounter($user): void
    {
        // If there is a timeout set for the autoblock, then need to check it when incrementing the counter
        if (
            !empty(OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins')) &&
            OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins') > 0
        ) {
            $query = privQuery("SELECT TIMESTAMPDIFF(SECOND, `last_login_fail`, NOW()) as `seconds_last_login_fail` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
            if (
                !empty($query['seconds_last_login_fail']) &&
                $query['seconds_last_login_fail'] > OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins')
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will set the login_fail_counter to 1 (ie. reset the counter to 0 and add the 1 for the most recent fail)
                privStatement("UPDATE `users_secure` SET `total_login_fail_counter` = total_login_fail_counter+1, `login_fail_counter` = 1, `last_login_fail` = NOW(), `auto_block_emailed` = 0 WHERE BINARY `username` = ?", [$user]);
                return;
            }
        }

        privStatement("UPDATE `users_secure` SET `total_login_fail_counter` = total_login_fail_counter+1, `login_fail_counter` = login_fail_counter+1, `last_login_fail` = NOW() WHERE BINARY `username` = ?", [$user]);
    }

    /**
     * @param string $ipString
     * @return void
     */
    private function incrementIpLoginFailedCounter(string $ipString): void
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        // If there is a timeout set for the autoblock, then need to check it when incrementing the counter
        if (
            !empty(OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins')) &&
            OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins') > 0
        ) {
            $query = sqlQuery("SELECT TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) as `seconds_last_ip_login_fail` FROM `ip_tracking` WHERE `ip_string` = ?", [$ipString]);
            if (
                !empty($query['seconds_last_ip_login_fail']) &&
                $query['seconds_last_ip_login_fail'] > OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins')
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will set the login_fail_counter to 1 (ie. reset the counter to 0 and add the 1 for the most recent fail)
                sqlStatement("UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = total_ip_login_fail_counter+1, `ip_login_fail_counter` = 1, `ip_last_login_fail` = NOW(), `ip_auto_block_emailed` = 0 WHERE `ip_string` = ?", [$ipString]);
                return;
            }
        }

        sqlStatement("UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = total_ip_login_fail_counter+1, `ip_login_fail_counter` = ip_login_fail_counter+1, `ip_last_login_fail` = NOW() WHERE `ip_string` = ?", [$ipString]);
    }

    /**
     * @param int $ipId
     * @return void
     */
    public static function resetIpCounter(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_login_fail_counter` = 0, `ip_last_login_fail` = null, `ip_auto_block_emailed` = 0 WHERE `id` = ?", [$ipId]);
    }

    /**
     * @param int $ipId
     * @return void
     */
    public static function disableIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_force_block` = 1 WHERE `id` = ?", [$ipId]);
    }

    /**
     * @param int $ipId
     * @return void
     */
    public static function enableIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_force_block` = 0 WHERE `id` = ?", [$ipId]);
    }

    /**
     * @param int $ipId
     * @return void
     */
    public static function skipTimingIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_no_prevent_timing_attack` = 1 WHERE `id` = ?", [$ipId]);
    }

    /**
     * @param int $ipId
     * @return void
     */
    public static function noSkipTimingIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_no_prevent_timing_attack` = 0 WHERE `id` = ?", [$ipId]);
    }

    /**
     * @param string $ip_string
     * @return bool
     */
    private function notifyIpBlock(string $ip_string): bool
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_auto_block_emailed` = 1 WHERE `ip_string` = ?", [$ip_string]);

        if (!empty(OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email')) && !empty(OEGlobalsBag::getInstance()->getString('practice_return_email_path'))) {
            if (empty(OEGlobalsBag::getInstance()->getInt('ip_time_reset_password_max_failed_logins'))) {
                $message = "IP address '" . text($ip_string) . "' has been blocked.";
            } else {
                $message = "IP address '" . text($ip_string) . "' has been temporarily blocked.";
            }
            return MyMailer::emailServiceQueue(OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email'), OEGlobalsBag::getInstance()->getString('practice_return_email_path'), xl('IP Address Block Notification For OpenEMR Admin'), $message);
        } else {
            error_log("Unable to send OpenEMR admin email notification since either patient_reminder_sender_email or practice_return_email_path global was not set");
            return false;
        }
    }

    /**
     * @param string $username
     * @return bool
     */
    private function notifyUserBlock(string $username): bool
    {
        privStatement("UPDATE `users_secure` SET `auto_block_emailed` = 1 WHERE BINARY `username` = ?", [$username]);

        if (!empty(OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email')) && !empty(OEGlobalsBag::getInstance()->getString('practice_return_email_path'))) {
            if (empty(OEGlobalsBag::getInstance()->getInt('time_reset_password_max_failed_logins'))) {
                $message = "Username '" . text($username) . "' has been blocked.";
            } else {
                $message = "Username '" . text($username) . "' has been temporarily blocked.";
            }
            return MyMailer::emailServiceQueue(OEGlobalsBag::getInstance()->getString('patient_reminder_sender_email'), OEGlobalsBag::getInstance()->getString('practice_return_email_path'), xl('Username Block Notification For OpenEMR Admin'), $message);
        } else {
            error_log("Unable to send OpenEMR admin email notification since either patient_reminder_sender_email or practice_return_email_path global was not set");
            return false;
        }
    }

    /**
     * Function to prevent timing attacks
     *
     * For standard authentication, simulating a call to passwordVerify() run using the same hashing algorithm.
     * For ldap authentication, simulating a call to ldap server.
     *
     * @return void
     */
    private function preventTimingAttack()
    {
        $dummyPassword = "heyheyhey";
        if (OEGlobalsBag::getInstance()->getBoolean('gbl_ldap_enabled')) {
            // ldap authentication simulation
            $this->activeDirectoryValidation("dummyCheck", $dummyPassword);
        } else {
            // standard authentication simulation
            AuthHash::passwordVerify($dummyPassword, $this->dummyHash);
        }
    }

    /**
     * Function to support clearing password from memory
     *
     * $password passed by reference to prevent storage of pass in memory
     *
     * @param $password
     * @return void
     * @throws SodiumException
     */
    private function clearFromMemory(&$password)
    {
        if (function_exists('sodium_memzero')) {
            sodium_memzero($password);
        } else {
            $password = '';
        }
    }

    /**
     * Validates a google ID token and returns true on success. If validation
     * fails, return false.
     *
     * @param $token
     * @return bool
     */
    public static function verifyGoogleSignIn($token): bool
    {
        $event = 'login';
        $beginLog = 'Google Failure';
        $ip = collectIpAddresses();

        if (empty($token)) {
            EventAuditLogger::getInstance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin attempt failed because of empty token");
            return false;
        }

        if (empty(OEGlobalsBag::getInstance()->getString('google_signin_client_id'))) {
            EventAuditLogger::getInstance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin attempt failed because of empty app client id");
            return false;
        }

        // Specify the CLIENT_ID of the app that accesses the backend
        $client = new Google_Client(['client_id' => OEGlobalsBag::getInstance()->getString('google_signin_client_id')]);
        $payload = $client->verifyIdToken($token);

        // ensure verify id token was successful
        if (empty($payload)) {
            EventAuditLogger::getInstance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin verify id attempt failed");
            return false;
        }

        // ensure verify id token returned an email
        if (empty($payload['email'])) {
            EventAuditLogger::getInstance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin verify id attempt failed (empty email)");
            return false;
        }

        // collect user info
        $user = privQuery("select `id`, `username`, `authorized`, `see_auth`, `active` from `users` where `google_signin_email` = ?", [$payload['email']]);

        // ensure user exists
        if (empty($user['id']) || empty($user['username'])) {
            EventAuditLogger::getInstance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " Google mail '" . $payload['email'] . "' not in user table");
            return false;
        }

        // ensure user is active
        if (empty($user['active'])) {
            EventAuditLogger::getInstance()->newEvent($event, $user['username'], '', 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' is not active");
            return false;
        }

        // Ensure that the user is in an auth group
        $userService = new UserService();
        $authGroup = $userService->getAuthGroupForUser($user['username']);
        if (empty($authGroup)) {
            EventAuditLogger::getInstance()->newEvent($event, $user['username'], '', 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' does not belong to a group ");
            return false;
        }

        // Check to ensure user is in a acl group
        if (AclExtended::aclGetGroupTitles($user['username']) == 0) {
            EventAuditLogger::getInstance()->newEvent($event, $user['username'], $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user with Google mail '" . $payload['email'] . "' is not in any phpGACL groups");
            return false;
        }

        // collect secure user info
        $userSecure = privQuery("SELECT `password` FROM `users_secure` WHERE BINARY `username` = ?", [$user['username']]);

        // ensure user is configured for login
        if (empty($userSecure['password'])) {
            EventAuditLogger::getInstance()->newEvent($event, $user['username'], $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' is not configured for login");
            return false;
        }

        // drumroll... the user is authenticated by google
        EventAuditLogger::getInstance()->newEvent($event, $user['username'], $authGroup, 1, "Auth success via Google LogIn by user with Google mail '" . $payload['email'] . "' : " . $ip['ip_string']);
        AuthUtils::setUserSessionVariables($user['username'], $userSecure['password'], $user, $authGroup);
        return true;
    }

    /**
     * Given an associative array representing the user, set the session variables
     * @param $username
     * @param $hash
     * @param array $userInfo
     * @param $authGroup
     */
    public static function setUserSessionVariables($username, $hash, $userInfo, $authGroup)
    {
        $session = SessionWrapperFactory::getInstance()->getActiveSession();
        // Set up session environment
        $session->set('authUser', $username); // username
        $session->set('authPass', $hash); // user hash used to confirm session in authCheckSession()
        $session->set('authUserID', $userInfo['id']); // user id
        $session->set('authProvider', $authGroup); // user group
        $session->set('userauthorized', $userInfo['authorized']); // user authorized setting
        // Some users may be able to authorize without being providers:
        if ($userInfo['see_auth'] > '2') {
            $session->set('userauthorized', '1');
        }
    }
}
