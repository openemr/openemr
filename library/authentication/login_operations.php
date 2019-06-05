<?php
/**
 * This is a library of commonly used functions for managing data for authentication
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <www.oemr.org>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
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
    if ($GLOBALS['use_active_directory']) {
        $valid = active_directory_validation($username, $password);
        $_SESSION['active_directory_auth'] = $valid;
    } else {
        $getUserSecureSQL= " SELECT " . implode(",", array(COL_ID,COL_PWD,COL_SALT))
                        ." FROM ".TBL_USERS_SECURE
                        ." WHERE BINARY ".COL_UNM."=?";
                        // Use binary keyword to require case sensitive username match
        $userSecure=privQuery($getUserSecureSQL, array($username));
        if (is_array($userSecure)) {
            $phash=oemr_password_hash($password, $userSecure[COL_SALT]);
            if ($phash!=$userSecure[COL_PWD]) {
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
            $_SESSION['authId'] = $userInfo{'id'};
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

/* Validation of user and password using active directory. */
function active_directory_validation($user, $pass)
{
    $valid = false;

    // Create class instance
    $ad = new Adldap\Adldap();

    // Create a configuration array.
    $config = array(
        // Your account suffix, for example: jdoe@corp.acme.org
        'account_suffix'        => $GLOBALS['account_suffix'],

        // You can use the host name or the IP address of your controllers.
        'hosts'    => [$GLOBALS['domain_controllers']],

        // Your base DN.
        'base_dn'               => $GLOBALS['base_dn'],

        // The account to use for querying / modifying users. This
        // does not need to be an actual admin account.
        'username'        => $user,
        'password'        => $pass,
    );

    // Add a connection provider to Adldap.
    $ad->addProvider($config);

    // If a successful connection is made, the provider will be returned.
    try {
        $prov = $ad->connect('', $user.$GLOBALS['account_suffix'], $pass);
        $valid = $prov->auth()->attempt($user, $pass, true);
    } catch (Exception $e) {
        error_log(errorLogEscape($e->getMessage()));
    }

    return $valid;
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
    if ($GLOBALS['password_max_failed_logins'] == 0 || $GLOBALS['use_active_directory']) {
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
