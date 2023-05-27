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
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Ken Chapple <ken@mi-squared.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <www.oemr.org>
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
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Utils\RandomGenUtils;
use OpenEMR\Services\UserService;

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

        // Set up AuthHash instance (note it uses auth mode)
        $this->authHashAuth = new AuthHash('auth');

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
    }

    /**
     *
     * @param type $username
     * @param type $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param type $email    - used in case of portal auth when a email address is required
     * @return boolean  returns true if the password for the given user is correct, false otherwise.
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
     * @param type $username
     * @param type $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param type $email    - used when a email address is required
     * @return boolean  returns true if the password for the given user is correct, false otherwise.
     */
    private function confirmPatientPassword($username, &$password, $email = '')
    {
        // Set variables for log
        $event = 'portalapi';
        $beginLog = 'Portal API failure';

        // Collect ip address for log
        $ip = collectIpAddresses();

        // Check to ensure username and password are not empty
        if (empty($username) || empty($password)) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". empty username or password");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Perform checks from patient_access_onsite
        $getPatientSQL = "select `id`, `pid`, `portal_username`, `portal_login_username`, `portal_pwd`, `portal_pwd_status`, `portal_onetime`  from `patient_access_onsite` where BINARY `portal_login_username` = ?";
        $patientInfo = privQuery($getPatientSQL, [$username]);
        if (empty($patientInfo) || empty($patientInfo['id']) || empty($patientInfo['pid'])) {
            // Patient portal information not found
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient portal information not found", $patientInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif (empty($patientInfo['portal_username']) || empty($patientInfo['portal_login_username']) || empty($patientInfo['portal_pwd'])) {
            // Patient missing username, login username, or password
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient missing username, login username, or password", $patientInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif (!empty($patientInfo['portal_onetime'])) {
            // Patient onetime is set, so still in process of verifying account
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient account not yet verified (portal_onetime set)", $patientInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif ($patientInfo['portal_pwd_status'] != 1) {
            // Patient portal_pwd_status is not 1, so still in process of verifying account
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient account not yet verified (portal_pwd_status is not 1)", $patientInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Perform checks from patient_data
        $getPatientDataSQL = "select `pid`, `email`, `allow_patient_portal` FROM `patient_data` WHERE `pid` = ?";
        $patientDataInfo = privQuery($getPatientDataSQL, [$patientInfo['pid']]);
        if (empty($patientDataInfo) || empty($patientDataInfo['pid'])) {
            // Patient not found
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif ($patientDataInfo['allow_patient_portal'] != "YES") {
            // Patient does not permit portal access
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient does not permit portal access", $patientDataInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif ($GLOBALS['enforce_signin_email']) {
            // Need to enforce email in credentials
            if (empty($email)) {
                // Patient email was not included in credentials
                EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient email was not included in credentials", $patientDataInfo['pid']);
                $this->clearFromMemory($password);
                $this->preventTimingAttack();
                return false;
            } elseif (empty($patientDataInfo['email'])) {
                // Patient email missing from demographics
                EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient does not have an email in demographics", $patientDataInfo['pid']);
                $this->clearFromMemory($password);
                $this->preventTimingAttack();
                return false;
            } elseif ($patientDataInfo['email'] != $email) {
                // Email not correct
                EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient email not correct", $patientDataInfo['pid']);
                $this->clearFromMemory($password);
                $this->preventTimingAttack();
                return false;
            }
        }

        // This error should never happen, but still gotta check for it
        if ($patientInfo['pid'] != $patientDataInfo['pid']) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient pid comparison with very unusual error");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Authentication
        // First, ensure the user hash is a valid hash
        if (!AuthHash::hashValid($patientInfo['portal_pwd'])) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient stored password hash is invalid", $patientDataInfo['pid']);
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }
        // Second, authentication
        if (!AuthHash::passwordVerify($password, $patientInfo['portal_pwd'])) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". patient password incorrect", $patientDataInfo['pid']);
            $this->clearFromMemory($password);
            return false;
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
        //  Set up class variable that the api will need to collect (log for API is done outside)
        $this->patientId = $patientDataInfo['pid'];
        return true;
    }

    /**
     *
     * @param type $username
     * @param type $password - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @return boolean  returns true if the password for the given user is correct, false otherwise.
     */
    private function confirmUserPassword($username, &$password)
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
                    EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address has been manually blocked");
                } else {
                    EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". IP address exceeded maximum number of failed logins");
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
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". empty username or password");
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
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } elseif ($userInfo['active'] != 1) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not active");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user is in a group (and collect the group name)
        $authGroup = UserService::getAuthGroupForUser($username);
        if (empty($authGroup)) {
            if ($this->loginAuth || $this->apiAuth) {
                // Utilize this during logins (and not during standard password checks within openemr such as esign)
                $this->incrementIpLoginFailedCounter($ip['ip_string']);
            }
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found in a group");
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
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user not in any phpGACL groups");
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
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user credentials not found");
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
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user exceeded maximum number of failed logins");
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
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user failed ldap authentication");
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
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user stored password hash is invalid");
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
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user password incorrect");
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
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user password is expired");
            $this->clearFromMemory($password);
            return false;
        }

        // PASSED
        $this->clearFromMemory($password);
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            self::resetLoginFailedCounter($username);
            $this->resetIpLoginFailedCounter($ip['ip_string']);
        }
        if ($this->loginAuth) {
            // Specialized code for login auth (not api auth)
            if (!empty($newHash)) {
                $hash = $newHash;
            } else {
                $hash = $userSecure['password'];
            }

            // If $hash is empty, then something is very wrong
            if (empty($hash)) {
                error_log('OpenEMR Error : OpenEMR is not working because broken function.');
                die("OpenEMR Error : OpenEMR is not working because broken function.");
            }
            self::setUserSessionVariables($username, $hash, $userInfo, $authGroup);
            EventAuditLogger::instance()->newEvent('login', $username, $authGroup, 1, "success: " . $ip['ip_string']);
        } elseif ($this->apiAuth) {
            // Set up class variables that the api will need to collect (log for API is done outside)
            $this->userId = $userInfo['id'];
            $this->userGroup = $authGroup;
        } else {
            // Log for authentication that are done, which are not api auth or login auth
            EventAuditLogger::instance()->newEvent('auth', $username, $authGroup, 1, "Auth success: " . $ip['ip_string']);
        }
        return true;
    }

    /**
     * Setup or change a user's password
     *
     * @param type $activeUser      ID of who is trying to make the change (either the user himself, or an administrator) - CAN NOT BE EMPTY
     * @param type $targetUser      ID of what account's password is to be updated (for a new user this doesn't exist yet).
     * @param type $currentPwd      the active user's current password - CAN NOT BE EMPTY
     *                              - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param type $newPwd          the new password for the target user
     *                              - password is passed by reference so that it can be "cleared out" as soon as we are done with it.
     * @param type $create          Are we creating a new user or
     * @param type $insert_sql      SQL to run to create the row in "users" (and generate a new id) when needed.
     * @param type $new_username    The username for a new user
     * @return boolean              Was the password successfully updated/created? If false, then $this->errorMessage will tell you why it failed.
     */
    public function updatePassword($activeUser, $targetUser, &$currentPwd, &$newPwd, $create = false, $insert_sql = "", $new_username = null)
    {
        if (empty($activeUser) || empty($currentPwd)) {
            $this->errorMessage = xl("Password update error!");
            $this->clearFromMemory($currentPwd);
            $this->clearFromMemory($newPwd);
            return false;
        }

        $userSQL = "SELECT `password`, `password_history1`, `password_history2`, `password_history3`, `password_history4`" .
            " FROM `users_secure`" .
            " WHERE `id` = ?";
        $userInfo = privQuery($userSQL, [$targetUser]);

        // Verify the active user's password
        $changingOwnPassword = $activeUser == $targetUser;
        // True if this is the current user changing their own password
        if ($changingOwnPassword) {
            if ($create) {
                $this->errorMessage = xl("Trying to create user with existing username!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                return false;
            }
            if (empty($userInfo['password'])) {
                $this->errorMessage = xl("Password update error!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                return false;
            }
            // If this user is changing his own password, then confirm that they have the current password correct
            if (!AuthHash::passwordVerify($currentPwd, $userInfo['password'])) {
                $this->errorMessage = xl("Incorrect password!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                return false;
            }
        } else {
            // If this is an administrator changing someone else's password, then check that they have this privilege
            if (!AclMain::aclCheckCore('admin', 'users')) {
                $this->errorMessage = xl("Not authorized to manage users!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                return false;
            }

            // If this is an administrator changing someone else's password, then authenticate the administrator
            if (self::useActiveDirectory()) {
                if (empty($_SESSION['authUser'])) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    return false;
                }
                if (!$this->activeDirectoryValidation($_SESSION['authUser'], $currentPwd)) {
                    $this->errorMessage = xl("Incorrect password!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
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
                    return false;
                }
                if (!AuthHash::passwordVerify($currentPwd, $adminInfo['password'])) {
                    $this->errorMessage = xl("Incorrect password!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
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
            if (empty($newPwd)) {
                // Something is seriously wrong with the random generator
                $this->clearFromMemory($newPwd);
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a random unique string.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a random unique string.");
            }
        }

        // Ensure new password is not blank
        if (empty($newPwd)) {
            $this->errorMessage = xl("Empty Password Not Allowed");
            $this->clearFromMemory($newPwd);
            return false;
        }

        // Ensure password is long enough, if this option is on (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testMinimumPasswordLength($newPwd))) {
            $this->clearFromMemory($newPwd);
            return false;
        }

        // Ensure password is not too long (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testMaximumPasswordLength($newPwd))) {
            $this->clearFromMemory($newPwd);
            return false;
        }

        // Ensure new password is strong enough, if this option is on (note LDAP skips this)
        if ((!$ldapDummyPassword) && (!$this->testPasswordStrength($newPwd))) {
            $this->clearFromMemory($newPwd);
            return false;
        }

        if ($userInfo === false) {
            // No userInfo means a new user
            // In these cases don't worry about password history
            if ($create) {
                if (empty($new_username)) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($newPwd);
                    return false;
                }
                // Collect the new user id from the users table
                privStatement($insert_sql, array());
                $getUserID = "SELECT `id`" .
                    " FROM `users`" .
                    " WHERE BINARY `username` = ?";
                $user_id = privQuery($getUserID, [$new_username]);
                if (empty($user_id) || empty($user_id['id'])) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($newPwd);
                    return false;
                }
                // Create the new user password hash
                $hash = $this->authHashAuth->passwordHash($newPwd);
                if (empty($hash)) {
                    // Something is seriously wrong
                    error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                    die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
                }
                // Store the new user credentials
                $passwordSQL = "INSERT INTO `users_secure`" .
                    " (`id`,`username`,`password`,`last_update_password`)" .
                    " VALUES (?,?,?,NOW()) ";
                privStatement($passwordSQL, [$user_id['id'], $new_username, $hash]);
            } else {
                $this->errorMessage = xl("Missing user credentials") . ":" . $targetUser;
                $this->clearFromMemory($newPwd);
                return false;
            }
        } else { // We are trying to update the password of an existing user
            if ($create) {
                $this->errorMessage = xl("Trying to create user with existing username!");
                $this->clearFromMemory($newPwd);
                return false;
            }

            if (empty($targetUser)) {
                $this->errorMessage = xl("Password update error!");
                $this->clearFromMemory($newPwd);
                return false;
            }

            if (($GLOBALS['password_history'] != 0) && (check_integer($GLOBALS['password_history']))) {
                // password reuse disallowed
                $pass_reuse_fail = false;
                if (($GLOBALS['password_history'] > 0) && (AuthHash::passwordVerify($newPwd, $userInfo['password']))) {
                    $pass_reuse_fail = true;
                }
                if (($GLOBALS['password_history'] > 1) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history1']))) {
                    $pass_reuse_fail = true;
                }
                if (($GLOBALS['password_history'] > 2) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history2']))) {
                    $pass_reuse_fail = true;
                }
                if (($GLOBALS['password_history'] > 3) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history3']))) {
                    $pass_reuse_fail = true;
                }
                if (($GLOBALS['password_history'] > 4) && (AuthHash::passwordVerify($newPwd, $userInfo['password_history4']))) {
                    $pass_reuse_fail = true;
                }
                if ($pass_reuse_fail) {
                    $this->errorMessage = xl("Reuse of previous passwords not allowed!");
                    $this->clearFromMemory($newPwd);
                    return false;
                }
            }

            // Everything checks out at this point, so update the password record
            $newHash = $this->authHashAuth->passwordHash($newPwd);
            if (empty($newHash)) {
                // Something is seriously wrong
                $this->clearFromMemory($newPwd);
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }

            $updateParams = array();
            $updateSQL = "UPDATE `users_secure`";
            $updateSQL .= " SET `last_update_password` = NOW()";
            $updateSQL .= ", `login_fail_counter` = 0";
            $updateSQL .= ", `last_login_fail` = null";
            $updateSQL .= ", `auto_block_emailed` = 0";
            $updateSQL .= ", `password` = ?";
            array_push($updateParams, $newHash);
            if ($GLOBALS['password_history'] != 0) {
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
            privStatement($updateSQL, $updateParams);

            // If the user is changing their own password, we need to update the session
            if ($changingOwnPassword) {
                $_SESSION['authPass'] = $newHash;
            }
        }

        // Done with $newPwd, so can clear it now
        $this->clearFromMemory($newPwd);

        return true;
    }

    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    public function getUserId()
    {
        return $this->userId;
    }

    public function getUserGroup()
    {
        return $this->userGroup;
    }

    public function getPatientId()
    {
        return $this->patientId;
    }

    // Ensure user hash remains valid (for example, if user is deactivated or password is changed, then
    //  this will not allow the same user in another session continue to use OpenEMR)
    // This function is static since requires no class specific defines
    public static function authCheckSession()
    {
        if ((!empty($_SESSION['authUserID'])) && (!empty($_SESSION['authUser'])) && (!empty($_SESSION['authPass']))) {
            $authDB = privQuery("SELECT `users`.`username`, `users_secure`.`password`" .
                " FROM `users`, `users_secure`" .
                " WHERE `users`.`id` = ? " .
                " AND `users`.`id` = `users_secure`.`id` " .
                " AND BINARY `users`.`username` = `users_secure`.`username`" .
                " AND `users`.`active` = 1", [$_SESSION['authUserID']]);
            if (
                (!empty($authDB)) &&
                (!empty($authDB['username'])) &&
                (!empty($authDB['password'])) &&
                ($_SESSION['authUser'] == $authDB['username']) &&
                (hash_equals($_SESSION['authPass'], $authDB['password']))
            ) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    // Check if the current or a specified user logs in with LDAP.
    // This function is static since requires no class specific defines
    public static function useActiveDirectory($user = '')
    {
        if (empty($GLOBALS['gbl_ldap_enabled'])) {
            return false;
        }
        if ($user == '') {
            $user = $_SESSION['authUser'];
        }
        $exarr = explode(',', $GLOBALS['gbl_ldap_exclusions']);
        foreach ($exarr as $ex) {
            if ($user == trim($ex)) {
                return false;
            }
        }
        return true;
    }

    // Validation of user and password using LDAP.
    // - $pass passed by reference to prevent storage of pass in memory
    private function activeDirectoryValidation($user, &$pass)
    {
        // Make sure the connection is not anonymous.
        if ($pass === '' || preg_match('/^\0/', $pass) || !preg_match('/^[\w.-]+$/', $user)) {
            error_log("Empty user or password for activeDirectoryValidation()");
            return false;
        }

        // below can be uncommented for detailed debugging
        // ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);

        $ldapconn = ldap_connect($GLOBALS['gbl_ldap_host']);
        if ($ldapconn) {
            // block of code to support encryption
            $isTls = false;
            if (
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-ca") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-cert") &&
                file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-key")
            ) {
                // set ca cert and client key/cert
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-ca")) {
                    error_log("Setting ldap-ca certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CERTFILE, $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-cert")) {
                    error_log("Setting ldap-cert client certificate failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_KEYFILE, $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-key")) {
                    error_log("Setting ldap-cert client key failed");
                }
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_REQUIRE_CERT, LDAP_OPT_X_TLS_DEMAND)) {
                    error_log("Setting require_cert to demand failed");
                }
                $isTls = true;
            } elseif (file_exists($GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-ca")) {
                // set ca cert
                if (!ldap_set_option(null, LDAP_OPT_X_TLS_CACERTFILE, $GLOBALS['OE_SITE_DIR'] . "/documents/certificates/ldap-ca")) {
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
                str_replace('{login}', $user, $GLOBALS['gbl_ldap_dn']),
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

    // Function to centralize the rehash process
    // It will return the new hash
    // - $password passed by reference to prevent storage of pass in memory
    private function rehashPassword($username, &$password)
    {
        if (self::useActiveDirectory($username)) {
            // rehash for LDAP
            $newRandomDummyPassword = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
            if (empty($newRandomDummyPassword)) {
                // Something is seriously wrong with the random generator
                $this->clearFromMemory($password);
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a random unique string.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a random unique string.");
            }
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
     * @param type $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return boolean      is the password long enough?
     */
    private function testMinimumPasswordLength(&$pwd)
    {
        if (($GLOBALS['gbl_minimum_password_length'] != 0) && (check_integer($GLOBALS['gbl_minimum_password_length']))) {
            if (strlen($pwd) < $GLOBALS['gbl_minimum_password_length']) {
                $this->errorMessage = xl("Password too short. Minimum characters required") . ": " . $GLOBALS['gbl_minimum_password_length'];
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
     * @param type $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return boolean      is the password short enough?
     */
    private function testMaximumPasswordLength(&$pwd)
    {
        if ((!empty($GLOBALS['gbl_maximum_password_length'])) && (check_integer($GLOBALS['gbl_maximum_password_length']))) {
            if (strlen($pwd) > $GLOBALS['gbl_maximum_password_length']) {
                $this->errorMessage = xl("Password too long. Maximum characters allowed") . ": " . $GLOBALS['gbl_maximum_password_length'];
                return false;
            }
        }

        return true;
    }

    /**
     * Does the new password meet the strength requirements?
     *
     * @param type $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return boolean      is the password strong enough?
     */
    private function testPasswordStrength(&$pwd)
    {
        if ($GLOBALS['secure_password']) {
            $features = 0;
            $reg_security = array("/[a-z]+/","/[A-Z]+/","/\d+/","/[\W_]+/");
            foreach ($reg_security as $expr) {
                if (preg_match($expr, $pwd)) {
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

    private function checkPasswordNotExpired($user)
    {
        if (($GLOBALS['password_expiration_days'] == 0) || self::useActiveDirectory($user)) {
            // skip the check if turned off or using active directory for login
            return true;
        }
        $query = privQuery("SELECT `last_update_password` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
        if ((!empty($query)) && (!empty($query['last_update_password'])) && (check_integer($GLOBALS['password_expiration_days'])) && (check_integer($GLOBALS['password_grace_time']))) {
            $current_date = date("Y-m-d");
            $expiredPlusGraceTime = date("Y-m-d", strtotime($query['last_update_password'] . "+" . ((int)$GLOBALS['password_expiration_days'] + (int)$GLOBALS['password_grace_time']) . " days"));
            if (strtotime($current_date) > strtotime($expiredPlusGraceTime)) {
                return false;
            }
        } else {
            error_log("OpenEMR ERROR: there is a problem when trying to check if user's password is expired");
        }
        return true;
    }

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
            if ((int)$GLOBALS['ip_max_failed_logins'] != 0) {
                if (!empty((int)$GLOBALS['ip_time_reset_password_max_failed_logins']) && (int)$GLOBALS['ip_time_reset_password_max_failed_logins'] > 0) {
                    $where[] = ' (ip_login_fail_counter > ? AND TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) < ?) ';
                    array_push($sqlBind, (int)$GLOBALS['ip_max_failed_logins'], (int)$GLOBALS['ip_time_reset_password_max_failed_logins']);
                } else {
                    $where[] = ' (ip_login_fail_counter > ?) ';
                    array_push($sqlBind, (int)$GLOBALS['ip_max_failed_logins']);
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

    private function checkLoginFailedCounter(string $user): array
    {
        if ((int)$GLOBALS['password_max_failed_logins'] == 0) {
            // skip the check if turned off
            return ['pass' => true, 'email_notification' => null];
        }

        $query = privQuery("SELECT `auto_block_emailed`, `login_fail_counter`, TIMESTAMPDIFF(SECOND, `last_login_fail`, NOW()) as `seconds_last_login_fail` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
        if ($query['login_fail_counter'] >= (int)$GLOBALS['password_max_failed_logins']) {
            if (
                !empty((int)$GLOBALS['time_reset_password_max_failed_logins']) &&
                (int)$GLOBALS['time_reset_password_max_failed_logins'] > 0 &&
                !empty($query['seconds_last_login_fail']) &&
                $query['seconds_last_login_fail'] > (int)$GLOBALS['time_reset_password_max_failed_logins']
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will pass
                //  (also need to reset the counter)
                self::resetLoginFailedCounter($user);
                return ['pass' => true, 'email_notification' => null];
            }
            if (empty($query['auto_block_emailed'])) {
                $emailNotification = true;
            } else {
                $emailNotification = false;
            }
            return ['pass' => false, 'email_notification' => $emailNotification];
        } else {
            return ['pass' => true, 'email_notification' => null];
        }
    }

    private function checkIpLoginFailedCounter(string $ipString): array
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        if ((int)$GLOBALS['ip_max_failed_logins'] == 0) {
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
        if ($query['ip_login_fail_counter'] >= (int)$GLOBALS['ip_max_failed_logins']) {
            if (
                !empty((int)$GLOBALS['ip_time_reset_password_max_failed_logins']) &&
                (int)$GLOBALS['ip_time_reset_password_max_failed_logins'] > 0 &&
                !empty($query['seconds_last_ip_login_fail']) &&
                $query['seconds_last_ip_login_fail'] > (int)$GLOBALS['ip_time_reset_password_max_failed_logins']
            ) {
                // the last ip login fail was longer than the timeout required to reset the failed logins, so will pass
                //  (also need to reset the counter)
                $this->resetIpLoginFailedCounter($ipString);
                return ['pass' => true, 'force_block' => null, 'skip_timing_attack' => null, 'email_notification' => null];
            }
            if (empty($query['ip_auto_block_emailed'])) {
                $emailNotification = true;
            } else {
                $emailNotification = false;
            }
            return ['pass' => false, 'force_block' => false, 'skip_timing_attack' => false, 'email_notification' => $emailNotification];
        } else {
            return ['pass' => true, 'force_block' => null, 'skip_timing_attack' => null, 'email_notification' => null];
        }
    }

    public static function resetLoginFailedCounter($user)
    {
        privStatement("UPDATE `users_secure` SET `login_fail_counter` = 0, `last_login_fail` = null, `auto_block_emailed` = 0 WHERE BINARY `username` = ?", [$user]);
    }

    private function resetIpLoginFailedCounter(string $ipString): void
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        sqlStatement("UPDATE `ip_tracking` SET `ip_login_fail_counter` = 0, `ip_last_login_fail` = null, `ip_auto_block_emailed` = 0 WHERE `ip_string` = ?", [$ipString]);
    }

    private function incrementLoginFailedCounter($user): void
    {
        // If there is a timeout set for the autoblock, then need to check it when incrementing the counter
        if (
            !empty((int)$GLOBALS['time_reset_password_max_failed_logins']) &&
            (int)$GLOBALS['time_reset_password_max_failed_logins'] > 0
        ) {
            $query = privQuery("SELECT TIMESTAMPDIFF(SECOND, `last_login_fail`, NOW()) as `seconds_last_login_fail` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
            if (
                !empty($query['seconds_last_login_fail']) &&
                $query['seconds_last_login_fail'] > (int)$GLOBALS['time_reset_password_max_failed_logins']
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will set the login_fail_counter to 1 (ie. reset the counter to 0 and add the 1 for the most recent fail)
                privStatement("UPDATE `users_secure` SET `total_login_fail_counter` = total_login_fail_counter+1, `login_fail_counter` = 1, `last_login_fail` = NOW(), `auto_block_emailed` = 0 WHERE BINARY `username` = ?", [$user]);
                return;
            }
        }

        privStatement("UPDATE `users_secure` SET `total_login_fail_counter` = total_login_fail_counter+1, `login_fail_counter` = login_fail_counter+1, `last_login_fail` = NOW() WHERE BINARY `username` = ?", [$user]);
    }

    private function incrementIpLoginFailedCounter(string $ipString): void
    {
        if (empty($ipString)) {
            // this should not happen, but will do this to ensure things do not break if it does happen
            $ipString = 'blank';
        }

        // If there is a timeout set for the autoblock, then need to check it when incrementing the counter
        if (
            !empty((int)$GLOBALS['ip_time_reset_password_max_failed_logins']) &&
            (int)$GLOBALS['ip_time_reset_password_max_failed_logins'] > 0
        ) {
            $query = sqlQuery("SELECT TIMESTAMPDIFF(SECOND, `ip_last_login_fail`, NOW()) as `seconds_last_ip_login_fail` FROM `ip_tracking` WHERE `ip_string` = ?", [$ipString]);
            if (
                !empty($query['seconds_last_ip_login_fail']) &&
                $query['seconds_last_ip_login_fail'] > (int)$GLOBALS['ip_time_reset_password_max_failed_logins']
            ) {
                // the last login fail was longer than the timeout required to reset the failed logins, so will set the login_fail_counter to 1 (ie. reset the counter to 0 and add the 1 for the most recent fail)
                sqlStatement("UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = total_ip_login_fail_counter+1, `ip_login_fail_counter` = 1, `ip_last_login_fail` = NOW(), `ip_auto_block_emailed` = 0 WHERE `ip_string` = ?", [$ipString]);
                return;
            }
        }

        sqlStatement("UPDATE `ip_tracking` SET `total_ip_login_fail_counter` = total_ip_login_fail_counter+1, `ip_login_fail_counter` = ip_login_fail_counter+1, `ip_last_login_fail` = NOW() WHERE `ip_string` = ?", [$ipString]);
    }

    public static function resetIpCounter(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_login_fail_counter` = 0, `ip_last_login_fail` = null, `ip_auto_block_emailed` = 0 WHERE `id` = ?", [$ipId]);
    }

    public static function disableIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_force_block` = 1 WHERE `id` = ?", [$ipId]);
    }

    public static function enableIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_force_block` = 0 WHERE `id` = ?", [$ipId]);
    }

    public static function skipTimingIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_no_prevent_timing_attack` = 1 WHERE `id` = ?", [$ipId]);
    }

    public static function noSkipTimingIp(int $ipId): void
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_no_prevent_timing_attack` = 0 WHERE `id` = ?", [$ipId]);
    }

    private function notifyIpBlock(string $ip_string): bool
    {
        sqlStatement("UPDATE `ip_tracking` SET `ip_auto_block_emailed` = 1 WHERE `ip_string` = ?", [$ip_string]);

        if (!empty($GLOBALS['patient_reminder_sender_email']) && !empty($GLOBALS['practice_return_email_path'])) {
            if (empty((int)$GLOBALS['ip_time_reset_password_max_failed_logins'])) {
                $message = "IP address '" . text($ip_string) . "' has been blocked.";
            } else {
                $message = "IP address '" . text($ip_string) . "' has been temporarily blocked.";
            }
            return MyMailer::emailServiceQueue($GLOBALS['patient_reminder_sender_email'], $GLOBALS['practice_return_email_path'], xl('IP Address Block Notification For OpenEMR Admin'), $message);
        } else {
            error_log("Unable to send OpenEMR admin email notification since either patient_reminder_sender_email or practice_return_email_path global was not set");
            return false;
        }
    }

    private function notifyUserBlock(string $username): bool
    {
        privStatement("UPDATE `users_secure` SET `auto_block_emailed` = 1 WHERE BINARY `username` = ?", [$username]);

        if (!empty($GLOBALS['patient_reminder_sender_email']) && !empty($GLOBALS['practice_return_email_path'])) {
            if (empty((int)$GLOBALS['time_reset_password_max_failed_logins'])) {
                $message = "Username '" . text($username) . "' has been blocked.";
            } else {
                $message = "Username '" . text($username) . "' has been temporarily blocked.";
            }
            return MyMailer::emailServiceQueue($GLOBALS['patient_reminder_sender_email'], $GLOBALS['practice_return_email_path'], xl('Username Block Notification For OpenEMR Admin'), $message);
        } else {
            error_log("Unable to send OpenEMR admin email notification since either patient_reminder_sender_email or practice_return_email_path global was not set");
            return false;
        }
    }

    // Function to prevent timing attacks
    //  For standard authentication, simulating a call to passwordVerify() run using the same hashing algorithm.
    //  For ldap authentication, simulating a call to ldap server.
    private function preventTimingAttack()
    {
        $dummyPassword = "heyheyhey";
        if ($GLOBALS['gbl_ldap_enabled']) {
            // ldap authentication simulation
            $this->activeDirectoryValidation("dummyCheck", $dummyPassword);
        } else {
            // standard authentication simulation
            AuthHash::passwordVerify($dummyPassword, $this->dummyHash);
        }
    }

    // Function to support clearing password from memory
    // - $password passed by reference to prevent storage of pass in memory
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
    public static function verifyGoogleSignIn($token)
    {
        $event = 'login';
        $beginLog = 'Google Failure';
        $ip = collectIpAddresses();

        if (empty($token)) {
            EventAuditLogger::instance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin attempt failed because of empty token");
            return false;
        }

        if (empty($GLOBALS['google_signin_client_id'])) {
            EventAuditLogger::instance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin attempt failed because of empty app client id");
            return false;
        }

        // Specify the CLIENT_ID of the app that accesses the backend
        $client = new Google_Client(['client_id' => $GLOBALS['google_signin_client_id']]);
        $payload = $client->verifyIdToken($token);

        // ensure verify id token was successful
        if (empty($payload)) {
            EventAuditLogger::instance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin verify id attempt failed");
            return false;
        }

        // ensure verify id token returned an email
        if (empty($payload['email'])) {
            EventAuditLogger::instance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " google signin verify id attempt failed (empty email)");
            return false;
        }

        // collect user info
        $user = privQuery("select `id`, `username`, `authorized`, `see_auth`, `active` from `users` where `google_signin_email` = ?", [$payload['email']]);

        // ensure user exists
        if (empty($user['id']) || empty($user['username'])) {
            EventAuditLogger::instance()->newEvent($event, '', '', 0, $beginLog . ": " . $ip['ip_string'] . " Google mail '" . $payload['email'] . "' not in user table");
            return false;
        }

        // ensure user is active
        if (empty($user['active'])) {
            EventAuditLogger::instance()->newEvent($event, $user['username'], '', 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' is not active");
            return false;
        }

        // Ensure that the user is in an auth group
        $authGroup = UserService::getAuthGroupForUser($user['username']);
        if (empty($authGroup)) {
            EventAuditLogger::instance()->newEvent($event, $user['username'], '', 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' does not belong to a group ");
            return false;
        }

        // Check to ensure user is in a acl group
        if (AclExtended::aclGetGroupTitles($user['username']) == 0) {
            EventAuditLogger::instance()->newEvent($event, $user['username'], $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . ". user with Google mail '" . $payload['email'] . "' is not in any phpGACL groups");
            return false;
        }

        // collect secure user info
        $userSecure = privQuery("SELECT `password` FROM `users_secure` WHERE BINARY `username` = ?", [$user['username']]);

        // ensure user is configured for login
        if (empty($userSecure['password'])) {
            EventAuditLogger::instance()->newEvent($event, $user['username'], $authGroup, 0, $beginLog . ": " . $ip['ip_string'] . " user with Google mail '" . $payload['email'] . "' is not configured for login");
            return false;
        }

        // drumroll... the user is authenticated by google
        EventAuditLogger::instance()->newEvent($event, $user['username'], $authGroup, 1, "Auth success via Google LogIn by user with Google mail '" . $payload['email'] . "' : " . $ip['ip_string']);
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
        // Set up session environment
        $_SESSION['authUser'] = $username; // username
        $_SESSION['authPass'] = $hash; // user hash used to confirm session in authCheckSession()
        $_SESSION['authUserID'] = $userInfo['id']; // user id
        $_SESSION['authProvider'] = $authGroup; // user group
        $_SESSION['userauthorized'] = $userInfo['authorized']; // user authorized setting
        // Some users may be able to authorize without being providers:
        if ($userInfo['see_auth'] > '2') {
            $_SESSION['userauthorized'] = '1';
        }
    }
}
