<?php
/**
 * portal/get_patient_info.php
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Cassian LUP <cassi.lup@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2011 Cassian LUP <cassi.lup@gmail.com>
 * @copyright Copyright (c) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// starting the PHP session
// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

// regenerating the session id to avoid session fixation attacks
session_regenerate_id(true);
//

// landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=" . urlencode($_SESSION['site_id']);
//

// checking whether the request comes from index.php
if (! isset($_SESSION['itsme'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

// some validation
if (! isset($_POST['uname']) || empty($_POST['uname'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

if (! isset($_POST['pass']) || empty($_POST['pass'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

// set the language
if (! empty($_POST['languageChoice'])) {
    $_SESSION['language_choice'] = (int) $_POST['languageChoice'];
} else if (empty($_SESSION['language_choice'])) {
    // just in case both are empty, then use english
    $_SESSION['language_choice'] = 1;
} else {
    // keep the current session language token
}

// Settings that will override globals.php
$ignoreAuth = 1;
//

// Authentication
require_once('../interface/globals.php');
require_once(dirname(__FILE__) . "/lib/appsql.class.php");
$logit = new ApplicationTable();
require_once("$srcdir/authentication/common_operations.php");
require_once("$srcdir/user.inc");
$password_update = isset($_SESSION['password_update']);
unset($_SESSION['password_update']);
$plain_code = $_POST['pass'];

$authorizedPortal = false; // flag
DEFINE("TBL_PAT_ACC_ON", "patient_access_onsite");
DEFINE("COL_PID", "pid");
DEFINE("COL_POR_PWD", "portal_pwd");
DEFINE("COL_POR_USER", "portal_username");
DEFINE("COL_POR_SALT", "portal_salt");
DEFINE("COL_POR_PWD_STAT", "portal_pwd_status");
$sql = "SELECT " . implode(",", array(
    COL_ID,
    COL_PID,
    COL_POR_PWD,
    COL_POR_SALT,
    COL_POR_PWD_STAT
)) . " FROM " . TBL_PAT_ACC_ON . " WHERE " . COL_POR_USER . "=?";
$auth = privQuery($sql, array(
    $_POST['uname']
));
if ($auth === false) {
    $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid username'), '', '0');
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&u');
    exit();
}

if (empty($auth[COL_POR_SALT])) {
    if (SHA1($plain_code) != $auth[COL_POR_PWD]) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':pass not salted'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&p');
        exit();
    }

    $new_salt = oemr_password_salt();
    $new_hash = oemr_password_hash($plain_code, $new_salt);
    $sqlUpdatePwd = " UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_PWD . "=?, " . COL_POR_SALT . "=? " . " WHERE " . COL_ID . "=?";
    privStatement($sqlUpdatePwd, array(
        $new_hash,
        $new_salt,
        $auth[COL_ID]
    ));
} else {
    $tmp = oemr_password_hash($plain_code, $auth[COL_POR_SALT]);
    if ($tmp != $auth[COL_POR_PWD]) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid password'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&p');
        exit();
    }
}

$_SESSION['portal_username'] = $_POST['uname'];
$sql = "SELECT * FROM `patient_data` WHERE `pid` = ?";

if ($userData = sqlQuery($sql, array(
    $auth['pid']
))) { // if query gets executed
    if (empty($userData)) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':not active patient'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($userData['email'] != $_POST['passaddon'] && $GLOBALS['enforce_signin_email']) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid email'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($userData['allow_patient_portal'] != "YES") {
        // Patient has not authorized portal, so escape
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':allow portal turned off'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($auth['pid'] != $userData['pid']) {
        // Not sure if this is even possible, but should escape if this happens
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($password_update) {
        $code_new = $_POST['pass_new'];
        $code_new_confirm = $_POST['pass_new_confirm'];
        if (! (empty($_POST['pass_new'])) && ! (empty($_POST['pass_new_confirm'])) && ($code_new == $code_new_confirm)) {
            $new_salt = oemr_password_salt();
            $new_hash = oemr_password_hash($code_new, $new_salt);

            // Update the password and continue (patient is authorized)
            privStatement(
                "UPDATE " . TBL_PAT_ACC_ON . "  SET " . COL_POR_PWD . "=?," . COL_POR_SALT . "=?," . COL_POR_PWD_STAT . "=1 WHERE id=?",
                array(
                        $new_hash,
                        $new_salt,
                        $auth['id']
                )
            );
            $authorizedPortal = true;
            $logit->portalLog(
                'password update',
                $auth['pid'],
                ($_SESSION['portal_username'] . ': ' . $_SESSION['ptName'] . ':success')
            );
        }
    }

    if ($auth['portal_pwd_status'] == 0) {
        if (! $authorizedPortal) {
            // Need to enter a new password in the index.php script
            $_SESSION['password_update'] = 1;
            header('Location: ' . $landingpage);
            exit();
        }
    }

    if ($auth['portal_pwd_status'] == 1) {
        // continue (patient is authorized)
        $authorizedPortal = true;
    }

    if ($authorizedPortal) {
        // patient is authorized (prepare the session variables)
        unset($_SESSION['password_update']); // just being safe
        unset($_SESSION['itsme']); // just being safe
        $_SESSION['pid'] = $auth['pid'];
        $_SESSION['patient_portal_onsite_two'] = 1;

        $tmp = getUserIDInfo($userData['providerID']);
        $_SESSION['providerName'] = $tmp['fname'] . ' ' . $tmp['lname'];
        $_SESSION['providerUName'] = $tmp['username'];
        $_SESSION['sessionUser'] = '-patient-'; // $_POST['uname'];
        $_SESSION['providerId'] = $userData['providerID'] ? $userData['providerID'] : 'undefined';
        $_SESSION['ptName'] = $userData['fname'] . ' ' . $userData['lname'];

        $logit->portalLog('login', $_SESSION['pid'], ($_SESSION['portal_username'] . ': ' . $_SESSION['ptName'] . ':success'));
    } else {
        $logit->portalLog('login', '', ($_POST['uname'] . ':not authorized'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }
} else { // problem with query
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Location: ./home.php');
exit();
