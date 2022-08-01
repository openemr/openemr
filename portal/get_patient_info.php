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
$landingpage = "index.php?site=" . urlencode($_SESSION['site_id'] ?? ($_GET['site'] ?? 'default'));
//

// checking whether the request comes from index.php
if (!isset($_SESSION['itsme'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

// some validation
if (!isset($_POST['uname']) || empty($_POST['uname'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

if (!isset($_POST['pass']) || empty($_POST['pass'])) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

// set the language
if (!empty($_POST['languageChoice'])) {
    $_SESSION['language_choice'] = (int)$_POST['languageChoice'];
} elseif (empty($_SESSION['language_choice'])) {
    // just in case both are empty, then use english
    $_SESSION['language_choice'] = 1;
} else {
    // keep the current session language token
}

// Settings that will override globals.php
$ignoreAuth_onsite_portal = true;
//

// Authentication
require_once('../interface/globals.php');
require_once(dirname(__FILE__) . "/lib/appsql.class.php");
require_once("$srcdir/user.inc");

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Csrf\CsrfUtils;

$logit = new ApplicationTable();
$password_update = isset($_SESSION['password_update']) ? $_SESSION['password_update'] : 0;
unset($_SESSION['password_update']);

$authorizedPortal = false; // flag
DEFINE("TBL_PAT_ACC_ON", "patient_access_onsite");
DEFINE("COL_ID", "id");
DEFINE("COL_PID", "pid");
DEFINE("COL_POR_PWD", "portal_pwd");
DEFINE("COL_POR_USER", "portal_username");
DEFINE("COL_POR_LOGINUSER", "portal_login_username");
DEFINE("COL_POR_PWD_STAT", "portal_pwd_status");
DEFINE("COL_POR_ONETIME", "portal_onetime");

// 2 is flag for one time credential reset else 1 = normal reset.
// one time reset requires a PIN where normal uses a new temp pass sent to user.
if ($password_update === 2 && !empty($_SESSION['pin'])) {
    $sql = "SELECT " . implode(",", array(
            COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT, COL_POR_ONETIME)) . " FROM " . TBL_PAT_ACC_ON .
        " WHERE BINARY " . COL_POR_ONETIME . "= ?";
    $auth = privQuery($sql, array($_SESSION['forward']));
    if ($auth !== false) {
        // remove the token from database
        privStatement("UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_ONETIME . "=NULL WHERE BINARY " . COL_POR_ONETIME . " = ?", [$auth['portal_onetime']]);
        // validation
        $validate = substr($auth[COL_POR_ONETIME], 32, 6);
        if (!empty($validate) && !empty($_POST['token_pin'])) {
            if ($_SESSION['pin'] !== $_POST['token_pin']) {
                $auth = false;
            } elseif ($validate !== $_POST['token_pin']) {
                $auth = false;
            }
        } else {
            $auth = false;
        }
        unset($_SESSION['forward']);
        unset($_SESSION['pin']);
        unset($_POST['token_pin']);
    }
} else {
    // normal login
    $sql = "SELECT " . implode(",", array(
            COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT)) . " FROM " . TBL_PAT_ACC_ON .
        " WHERE " . COL_POR_LOGINUSER . "= ?";
    if ($password_update === 1) {
        $sql = "SELECT " . implode(",", array(
                COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT)) . " FROM " . TBL_PAT_ACC_ON .
            " WHERE " . COL_POR_USER . "= ?";
    }

    $auth = privQuery($sql, array($_POST['uname']));
}
if ($auth === false) {
    $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid username'), '', '0');
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&u');
    exit();
}

if ($password_update === 2) {
    if ($_POST['pass'] != $auth[COL_POR_PWD]) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid password'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&p');
        exit();
    }
} else {
    if (AuthHash::passwordVerify($_POST['pass'], $auth[COL_POR_PWD])) {
        $authHashPortal = new AuthHash('auth');
        if ($authHashPortal->passwordNeedsRehash($auth[COL_POR_PWD])) {
            // If so, create a new hash, and replace the old one (this will ensure always using most modern hashing)
            $reHash = $authHashPortal->passwordHash($_POST['pass']);
            if (empty($reHash)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }
            privStatement(
                "UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_PWD . " = ? WHERE " . COL_ID . " = ?",
                [
                    $reHash,
                    $auth[COL_ID]
                ]
            );
        }
    } else {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid password'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&p');
        exit();
    }
}



$_SESSION['portal_username'] = $auth[COL_POR_USER];
$_SESSION['portal_login_username'] = $auth[COL_POR_LOGINUSER];

$sql = "SELECT * FROM `patient_data` WHERE `pid` = ?";

if ($userData = sqlQuery($sql, array($auth['pid']))) { // if query gets executed
    if (empty($userData)) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':not active patient'), '', '0');
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($userData['email'] != ($_POST['passaddon'] ?? '') && $GLOBALS['enforce_signin_email']) {
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
        if (!(empty($_POST['pass_new'])) && !(empty($_POST['pass_new_confirm'])) && ($code_new == $code_new_confirm)) {
            $new_hash = (new AuthHash('auth'))->passwordHash($code_new);
            if (empty($new_hash)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }
            // Update the password and continue (patient is authorized)
            privStatement(
                "UPDATE " . TBL_PAT_ACC_ON . "  SET " . COL_POR_LOGINUSER . "=?," . COL_POR_PWD . "=?," . COL_POR_PWD_STAT . "=1 WHERE id=?",
                array(
                    $_POST['login_uname'],
                    $new_hash,
                    $auth['id']
                )
            );
            $authorizedPortal = true;
            $logit->portalLog('password update', $auth['pid'], ($_POST['login_uname'] . ': ' . $_SESSION['ptName'] . ':success'));
        }
    }

    if ($auth['portal_pwd_status'] == 0) {
        if (!$authorizedPortal) {
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
        // never set authUserID though authUser is used for ACL!
        $_SESSION['authUser'] = 'portal-user';
        // Set up the csrf private_key (for the paient portal)
        //  Note this key always remains private and never leaves server session. It is used to create
        //  the csrf tokens.
        CsrfUtils::setupCsrfKey();

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
