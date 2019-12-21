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


require_once(dirname(__FILE__) . "/privDB.php");
require_once(dirname(__FILE__) . "/password_hashing.php");

define("TBL_USERS_SECURE", "users_secure");
define("TBL_USERS", "users");

define("COL_PWD", "password");
define("COL_UNM", "username");
define("COL_ID", "id");
define("COL_SALT", "salt");
define("COL_LU", "last_update");
define("COL_PWD_H1", "password_history1");
define("COL_SALT_H1", "salt_history1");
define("COL_ACTIVE", "active");

define("COL_PWD_H2", "password_history2");
define("COL_SALT_H2", "salt_history2");


/**
 * create a new password entry in the users_secure table
 *
 * @param type $username
 * @param type $password  Passing by reference so additional copy is not created in memory
 */
function initializePassword($username, $userid, &$password)
{

    $salt=oemr_password_salt();
    $hash=oemr_password_hash($password, $salt);
    $passwordSQL= "INSERT INTO ".TBL_USERS_SECURE.
                  " (".implode(",", array(COL_ID,COL_UNM,COL_PWD,COL_SALT,COL_LU)).")".
                  " VALUES (?,?,?,?,NOW()) ";

    $params=array(
                    $userid,
                    $username,
                    $hash,
                    $salt
    );
    privStatement($passwordSQL, $params);
    return $hash;
}


/**
 * After a user's password has been updated to use the new hashing strategy wipe out the old hash value.
 *
 *
 * @param type $username
 * @param type $userid
 */
function purgeCompatabilityPassword($username, $userid)
{
    $purgeSQL = " UPDATE " . TBL_USERS
                ." SET ". COL_PWD . "='NoLongerUsed' "
                ." WHERE ".COL_UNM. "=? "
                ." AND ".COL_ID. "=?";
    privStatement($purgeSQL, array($username,$userid));
}


/**
 *
 * @param type $username
 * @param type $password
 * @return boolean  returns true if the password for the given user is correct, false otherwise.
 */
function confirm_user_password($username, &$password)
{
    $getUserSecureSQL= " SELECT " . implode(",", array(COL_ID,COL_PWD,COL_SALT))
                       ." FROM ".TBL_USERS_SECURE
                       ." WHERE BINARY ".COL_UNM."=?";
                       // Use binary keyword to require case sensitive username match
    $userSecure=privQuery($getUserSecureSQL, array($username));
    if (is_array($userSecure)) {
        $phash=oemr_password_hash($password, $userSecure[COL_SALT]);
        if ($phash==$userSecure[COL_PWD]) {
            return true;
        }
    }

    return false;
}
