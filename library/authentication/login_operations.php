<?php
/**
 * This is a library of commonly used functions for managing data for authentication
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


require_once(dirname(__FILE__) . "/common_operations.php");

use OpenEMR\Common\Logging\EventAuditLogger;

/**
 *
 * @param type $username
 * @param type $password    password is passed by reference so that it can be "cleared out"
 *                          as soon as we are done with it.
 * @param type $provider
 */
function validate_user_password($username, &$password, $provider)
{
    $ip = collectIpAddresses();

    $valid=false;

    //Active Directory Authentication added by shachar zilbershlag <shaharzi@matrix.co.il>
    if (useActiveDirectory($username)) {
        $valid = active_directory_validation($username, $password);
        $_SESSION['active_directory_auth'] = $valid;
        if (!$valid) {
            EventAuditLogger::instance()->newEvent('login', $username, $provider, 0, "failure: " . $ip['ip_string'] . ". user failed ldap authentication");
            $password='';
            return false;
        }
    } else {
        $getUserSecureSQL= " SELECT " . implode(",", array(COL_ID,COL_PWD,COL_SALT))
                        ." FROM ".TBL_USERS_SECURE
                        ." WHERE BINARY ".COL_UNM."=?";
                        // Use binary keyword to require case sensitive username match
        $userSecure=privQuery($getUserSecureSQL, array($username));
        if (is_array($userSecure)) {
            $phash=oemr_password_hash($password, $userSecure[COL_SALT]);
            if (!hash_equals($phash, $userSecure[COL_PWD])) {
                EventAuditLogger::instance()->newEvent('login', $username, $provider, 0, "failure: " . $ip['ip_string'] . ". user password incorrect");
                incrementLoginFailedCounter($username);
                return false;
            }

            $valid=true;
        } else {
            if ((!isset($GLOBALS['password_compatibility'])||$GLOBALS['password_compatibility'])) {           // use old password scheme if allowed.
                $getUserSQL="select username,id, password from users where BINARY username = ?";
                $userInfo = privQuery($getUserSQL, array($username));
                if ($userInfo===false) {
                    return false;
                }

                $username=$userInfo['username'];
                $dbPasswordLen=strlen($userInfo['password']);
                if ($dbPasswordLen==32) {
                    $phash=md5($password);
                    $valid=$phash==$userInfo['password'];
                } else if ($dbPasswordLen==40) {
                    $phash=sha1($password);
                    $valid=$phash==$userInfo['password'];
                }

                if ($valid) {
                    $phash=initializePassword($username, $userInfo['id'], $password);
                    purgeCompatabilityPassword($username, $userInfo['id']);
                    $_SESSION['relogin'] = 1;
                } else {
                    return false;
                }
            }
        }
    }

    $getUserSQL="select id, authorized, see_auth".
                        ", active ".
                        " from users where BINARY username = ?";
    $userInfo = privQuery($getUserSQL, array($username));

    if ($userInfo['active'] != 1) {
        EventAuditLogger::instance()->newEvent('login', $username, $provider, 0, "failure: " . $ip['ip_string'] . ". user not active or not found in users table");
        $password='';
        return false;
    }

    // Done with the cleartext password at this point!
    $password='';
    if ($valid) {
        if ($authGroup = privQuery("select * from `groups` where user=? and name=?", array($username,$provider))) {
            $_SESSION['authUser'] = $username;
            $_SESSION['authPass'] = $phash;
            $_SESSION['authGroup'] = $authGroup['name'];
            $_SESSION['authUserID'] = $userInfo['id'];
            $_SESSION['authProvider'] = $provider;
            $_SESSION['authId'] = $userInfo['id'];
            $_SESSION['userauthorized'] = $userInfo['authorized'];
            // Some users may be able to authorize without being providers:
            if ($userInfo['see_auth'] > '2') {
                $_SESSION['userauthorized'] = '1';
            }
            $valid=true;
        } else {
            EventAuditLogger::instance()->newEvent('login', $username, $provider, 0, "failure: " . $ip['ip_string'] . ". user not in group: $provider");
            $valid=false;
        }
    }

    // Check if user exceeds max number of failed logins
    if ($valid) {
        if (checkLoginFailedCounter($username)) {
            EventAuditLogger::instance()->newEvent('login', $username, $provider, 1, "success: " . $ip['ip_string']);
            resetLoginFailedCounter($username);
            $valid=true;
        } else {
            EventAuditLogger::instance()->newEvent('login', $username, $provider, 0, "failure: " . $ip['ip_string'] . ". user exceeded maximum number of failed logins");
            $valid=false;
        }
    }

    return $valid;
}

function verify_user_gacl_group($user, $provider)
{
    global $phpgacl_location;

    $ip = collectIpAddresses();

    if (isset($phpgacl_location)) {
        if (acl_get_group_titles($user) == 0) {
            EventAuditLogger::instance()->newEvent('login', $user, $provider, 0, "failure: " . $ip['ip_string'] . ". user not in any phpGACL groups. (bad username?)");
            return false;
        }
    }

    return true;
}

/* Validation of user and password using LDAP. */

function active_directory_validation($user, $pass)
{
    // Make sure the connection is not anonymous.
    if ($pass === '' || preg_match('/^\0/', $pass) || !preg_match('/^[\w.-]+$/', $user)) {
        error_log("Empty user or password for active_directory_validation()");
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

function resetLoginFailedCounter($user)
{
    privStatement("UPDATE `users_secure` SET `login_fail_counter` = 0 WHERE BINARY `username` = ?", [$user]);
}

function incrementLoginFailedCounter($user)
{
    privStatement("UPDATE `users_secure` SET `login_fail_counter` = login_fail_counter+1 WHERE BINARY `username` = ?", [$user]);
}

function checkLoginFailedCounter($user)
{
    if ($GLOBALS['password_max_failed_logins'] == 0 || useActiveDirectory($user)) {
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
