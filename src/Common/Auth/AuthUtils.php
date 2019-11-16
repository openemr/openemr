<?php
/**
 * AuthUtils class.
 *
 *   Authentication:
 *     1. This class can be run in 1 of 3 modes:
 *       -login: Authentication of users during standard login.
 *       -api:   Authentication of users when requesting api token.
 *       -other: Default setting. Other Authentication when already logged into OpenEMR such as when
 *                doing Esign or changing mfa setting.
 *     2. LDAP (Active Directory) is also supported. In these cases, the login counter and
 *         expired password mechanisms are ignored.
 *     3. Timing attack prevention. The time will be the same for a user that does not exist versus a user
 *         that does exist. This is done in standard authentication and ldap authentication by simulating
 *         the password verification in each via the preventTimingAttack() function.
 *        (There is one issue in this mechanism when using ldap with a user that is excluded from it. In
 *         that case unable to avoid timing differences. That feature is really only meant for configuration and
 *         debugging and recommend inactivating that excluded user when not needed, which will then mitigate
 *         this issue.)
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <www.oemr.org>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


namespace OpenEMR\Common\Auth;

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Utils\RandomGenUtils;

class AuthUtils
{
    private $loginAuth = false; // standard login authentication
    private $apiAuth = false;   // api login authentication
    private $otherAuth = false; // other use

    private $authHashAuth; // Store the private AuthHash instance.

    private $errorMessage; // Error messages (in updatePassword() function)

    private $userId;       // Stores user id for api to retrieve (in confirmUserPassword() function)
    private $userGroup;    // Stores user group for api to retrieve (in confirmUserPassword() function)

    private $dummyHash;     // Used to prevent timing attacks

    public function __construct($mode = '')
    {
        // Set mode
        if ($mode == 'login') {
            $this->loginAuth = true;
        } else if ($mode == 'api') {
            $this->apiAuth = true;
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
        } else if (empty($timing['gl_value'])) {
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
     * @return boolean  returns true if the password for the given user is correct, false otherwise.
     */
    public function confirmUserPassword($username, &$password)
    {
        // Set variables for log
        if ($this->loginAuth) {
            $event = 'login';
            $beginLog = 'failure';
        } else if ($this->apiAuth) {
            $event = 'api';
            $beginLog = 'API failure';
        } else { // $this->otherAuth
            $event = 'auth';
            $beginLog = 'Auth failure';
        }

        // Collect ip address for log
        $ip = collectIpAddresses();

        // Check to ensure username and password are not empty
        if (empty($username) || empty($password)) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". empty username or password");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user exists and is active
        $getUserSQL = "select `id`, `authorized`, `see_auth`, `active` from `users` where BINARY `username` = ?";
        $userInfo = privQuery($getUserSQL, [$username]);
        if (empty($userInfo) || empty($userInfo['id'])) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        } else if ($userInfo['active'] != 1) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not active");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user is in a group (and collect the group name)
        $authGroup = privQuery("select `name` from `groups` where BINARY `user` = ?", [$username]);
        if (empty($authGroup) || empty($authGroup['name'])) {
            EventAuditLogger::instance()->newEvent($event, $username, '', 0, $beginLog . ": " . $ip['ip_string'] . ". user not found in a group");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check to ensure user is in a acl group
        if (function_exists('acl_get_group_titles')) {
            if (acl_get_group_titles($username) == 0) {
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user not in any phpGACL groups");
                $this->clearFromMemory($password);
                $this->preventTimingAttack();
                return false;
            }
        } else {
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". phpGACL is not properly set up");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Collect user credentials from database
        $getUserSecureSQL = " SELECT `id`, `password`" .
            " FROM `users_secure`" .
            " WHERE BINARY `username` = ?";
        $userSecure=privQuery($getUserSecureSQL, [$username]);
        if (empty($userSecure) || empty($userSecure['id']) || empty($userSecure['password'])) {
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user credentials not found");
            $this->clearFromMemory($password);
            $this->preventTimingAttack();
            return false;
        }

        // Check password
        if (self::useActiveDirectory($username)) {
            // ldap authentication
            if (!$this->activeDirectoryValidation($username, $password)) {
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user failed ldap authentication");
                $this->clearFromMemory($password);
                return false;
            }
        } else {
            // standard authentication
            if (!AuthHash::passwordVerify($password, $userSecure['password'])) {
                if ($this->loginAuth || $this->apiAuth) {
                    // Utilize this during logins (and not during standard password checks within openemr such as esign)
                    $this->incrementLoginFailedCounter($username);
                }
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user password incorrect");
                $this->clearFromMemory($password);
                return false;
            }
        }

        // check for rehash
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            if ($this->authHashAuth->passwordNeedsRehash($userSecure['password'])) {
                // Hash needs updating, so create a new hash, and replace the old one (this will ensure always using most modern hashing)
                $newHash = $this->rehashPassword($username, $password);
                // store the rehash
                privStatement("UPDATE `users_secure` SET `password` = ? WHERE `id` = ?", [$newHash, $userSecure['id']]);
            }
        }

        // check login counter if this option is set (note ldap skips this)
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            if (!$this->checkLoginFailedCounter($username)) {
                $this->incrementLoginFailedCounter($username);
                EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user exceeded maximum number of failed logins");
                $this->clearFromMemory($password);
                return false;
            }
        }

        // Check to ensure password not expired if this option is set (note ldap skips this)
        if (!$this->checkPasswordNotExpired($username)) {
            EventAuditLogger::instance()->newEvent($event, $username, $authGroup['name'], 0, $beginLog . ": " . $ip['ip_string'] . ". user password is expired");
            $this->clearFromMemory($password);
            return false;
        }

        // PASSED
        $this->clearFromMemory($password);
        if ($this->loginAuth || $this->apiAuth) {
            // Utilize this during logins (and not during standard password checks within openemr such as esign)
            $this->resetLoginFailedCounter($username);
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

            // Set up session environment
            $_SESSION['authUser'] = $username;                     // username
            $_SESSION['authPass'] = $hash;                         // user hash used to confirm session in authCheckSession()
            $_SESSION['authUserID'] = $userInfo['id'];             // user id
            $_SESSION['authProvider'] = $authGroup['name'];        // user group
            $_SESSION['userauthorized'] = $userInfo['authorized']; // user authorized setting
            // Some users may be able to authorize without being providers:
            if ($userInfo['see_auth'] > '2') {
                $_SESSION['userauthorized'] = '1';
            }
            EventAuditLogger::instance()->newEvent('login', $username, $authGroup['name'], 1, "success: " . $ip['ip_string']);
        } else if ($this->apiAuth) {
            // Set up class variables that the api will need to collect (log for API is done outside)
            $this->userId = $userInfo['id'];
            $this->userGroup = $authGroup['name'];
        } else {
            // Log for authentication that are done, which are not api auth or login auth
            EventAuditLogger::instance()->newEvent('auth', $username, $authGroup['name'], 1, "Auth success: " . $ip['ip_string']);
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

        $userSQL = "SELECT `password`, `password_history1`, `password_history2`" .
            " FROM `users_secure`" .
            " WHERE `id` = ?";
        $userInfo = privQuery($userSQL, [$targetUser]);

        // Verify the active user's password
        $changingOwnPassword = $activeUser==$targetUser;
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
            if (!acl_check('admin', 'users')) {
                $this->errorMessage = xl("Not authorized to manage users!");
                $this->clearFromMemory($currentPwd);
                $this->clearFromMemory($newPwd);
                return false;
            }

            // If this is an administrator changing someone else's password, then check that they have the password right
            if (self::useActiveDirectory()) {
                // Use case here is for when an administrator is adding a new user that will be using LDAP for authentication
                // (note that in this case, a random password is prepared for the new user below that is stored in OpenEMR
                //  and used only for session confirmations; the primary authentication for the new user will be done via
                //  LDAP)
                if (empty($_SESSION['authUser'])) {
                    $this->errorMessage = xl("Password update error!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    return false;
                }
                $valid = $this->activeDirectoryValidation($_SESSION['authUser'], $currentPwd);
                if (!$valid) {
                    $this->errorMessage = xl("Incorrect password!");
                    $this->clearFromMemory($currentPwd);
                    $this->clearFromMemory($newPwd);
                    return false;
                } else {
                    $newPwd = RandomGenUtils::produceRandomString(32, "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789");
                    if (empty($newPwd)) {
                        // Something is seriously wrong with the random generator
                        $this->clearFromMemory($currentPwd);
                        $this->clearFromMemory($newPwd);
                        error_log('OpenEMR Error : OpenEMR is not working because unable to create a random unique string.');
                        die("OpenEMR Error : OpenEMR is not working because unable to create a random unique string.");
                    }
                }
            } else {
                $adminSQL = "SELECT `password`" .
                    " FROM `users_secure`" .
                    " WHERE `id` = ?";
                $adminInfo=privQuery($adminSQL, [$activeUser]);
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

        // Ensure new password is not blank (note that even for ldap a random password is created above)
        if (empty($newPwd)) {
            $this->errorMessage = xl("Empty Password Not Allowed");
            $this->clearFromMemory($newPwd);
            return false;
        }

        // Ensure new password is strong enough, if this option is on (note LDAP skips this)
        if (!$this->testPasswordStrength($newPwd)) {
            $this->clearFromMemory($newPwd);
            return false;
        }

        if ($userInfo===false) {
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
                $this->errorMessage = xl("Missing user credentials:".$targetUser);
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

            if ($GLOBALS['password_history']) {
                // password reuse disallowed
                if ((AuthHash::passwordVerify($newPwd, $userInfo['password'])) ||
                    (AuthHash::passwordVerify($newPwd, $userInfo['password_history1'])) ||
                    (AuthHash::passwordVerify($newPwd, $userInfo['password_history2']))) {
                    $this->errorMessage = xl("Reuse of three previous passwords not allowed!");
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

            $updateParams=array();
            $updateSQL = "UPDATE `users_secure`";
            $updateSQL .= " SET `last_update_password` = NOW()";
            $updateSQL .= ", `password` = ?";
            array_push($updateParams, $newHash);
            if ($GLOBALS['password_history']) {
                $updateSQL.=", `password_history1` = ?";
                array_push($updateParams, $userInfo['password']);
                $updateSQL.=", `password_history2` = ?";
                array_push($updateParams, $userInfo['password_history1']);
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

    // Ensure user hash remains valid (for example, if user is deactivated or password is changed, then
    //  this will not allow the same user in another session continue to use OpenEMR)
    // This function is static since requires no class specific defines
    public static function authCheckSession()
    {
        if ((!empty($_SESSION['authUserID'])) && (!empty($_SESSION['authUser'])) && (!empty($_SESSION['authPass']))) {
            $authDB = privQuery("SELECT `users`.`username`, `users_secure`.`password`" .
                " FROM `users`, `users_secure`" .
                " WHERE `users`.`id` = ? ".
                " AND `users`.`id` = `users_secure`.`id` ".
                " AND BINARY `users`.`username` = `users_secure`.`username`" .
                " AND `users`.`active` = 1", [$_SESSION['authUserID']]);
            if ((!empty($authDB)) &&
                (!empty($authDB['username'])) &&
                (!empty($authDB['password'])) &&
                ($_SESSION['authUser'] == $authDB['username']) &&
                (hash_equals($_SESSION['authPass'], $authDB['password']))) {
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
        $ldapconn = ldap_connect($GLOBALS['gbl_ldap_host']);
        if ($ldapconn) {
            if (!ldap_set_option($ldapconn, LDAP_OPT_PROTOCOL_VERSION, 3)) {
                error_log("Setting LDAP v3 protocol failed");
            }
            if (!ldap_set_option($ldapconn, LDAP_OPT_REFERRALS, 0)) {
                error_log("Disabling LDAP referrals failed");
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
     * Does the new password meet the security requirements?
     *
     * @param type $pwd     the password to test - passed by reference to prevent storage of pass in memory
     * @return boolean      is the password good enough?
     */
    private function testPasswordStrength(&$pwd)
    {
        if ($GLOBALS['secure_password']) {
            if (strlen($pwd)<8) {
                $this->errorMessage = xl("Password too short. Minimum 8 characters required.");
                return false;
            }

            $features=0;
            $reg_security=array("/[a-z]+/","/[A-Z]+/","/\d+/","/[\W_]+/");
            foreach ($reg_security as $expr) {
                if (preg_match($expr, $pwd)) {
                    $features++;
                }
            }

            if ($features<3) {
                $this->errorMessage = xl("Password does not meet minimum requirements and should contain at least three of the four following items: A number, a lowercase letter, an uppercase letter, a special character (Not a letter or number).");
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
        if ((!empty($query)) && (!empty($query['last_update_password']))) {
            $current_date = date("Y-m-d");
            $expiredPlusGraceTime = date("Y-m-d", strtotime($query['last_update_password'] . "+" . ($GLOBALS['password_expiration_days'] + $GLOBALS['password_grace_time']) . " days"));
            if (strtotime($current_date) > strtotime($expiredPlusGraceTime)) {
                return false;
            }
        } else {
            error_log("OpenEMR ERROR: there is a problem with recording of last_update_password entry in users_secure table");
        }
        return true;
    }

    private function checkLoginFailedCounter($user)
    {
        if ($GLOBALS['password_max_failed_logins'] == 0 || self::useActiveDirectory($user)) {
            // skip the check if turned off or using active directory for login
            return true;
        }

        $query = privQuery("SELECT `login_fail_counter` FROM `users_secure` WHERE BINARY `username` = ?", [$user]);
        if ($query['login_fail_counter'] >= $GLOBALS['password_max_failed_logins']) {
            return false;
        } else {
            return true;
        }
    }

    private function resetLoginFailedCounter($user)
    {
        if (!self::useActiveDirectory($user)) {
            // skip if using active directory for login
            privStatement("UPDATE `users_secure` SET `login_fail_counter` = 0 WHERE BINARY `username` = ?", [$user]);
        }
    }

    private function incrementLoginFailedCounter($user)
    {
        if (!self::useActiveDirectory($user)) {
            // skip if using active directory for login
            privStatement("UPDATE `users_secure` SET `login_fail_counter` = login_fail_counter+1 WHERE BINARY `username` = ?", [$user]);
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
}
