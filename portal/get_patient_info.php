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

use OpenEMR\Common\Session\SessionUtil;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\OEGlobalsBag;

// Will start the (patient) portal OpenEMR session/cookie.
// Need access to classes, so run autoloader now instead of in globals.php.
require_once(__DIR__ . "/../vendor/autoload.php");
$globalsBag = OEGlobalsBag::getInstance();
if (empty(SessionUtil::getAppCookie())) {
    // Prevent error 500 in case of cleaning cookies and site data once when the login page is already loaded
    $_COOKIE[SessionUtil::APP_COOKIE_NAME] = SessionUtil::PORTAL_SESSION_ID;
}
$session = SessionWrapperFactory::instance()->getWrapper();

// regenerating the session id to avoid session fixation attacks
$session->migrate(true);
//

// landing page definition -- where to go if something goes wrong
$landingpage = "index.php?site=" . urlencode((string) ($session->get('site_id', false) ?? $_GET['site'] ?? 'default'));
//

if (!empty($_REQUEST['redirect'])) {
    // let's add the redirect back in case there are any errors or other problems.
    $landingpage .= "&redirect=" . urlencode((string) $_REQUEST['redirect']);
}

// checking whether the request comes from index.php
if (!$session->get('itsme', false)) {
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

// some validation
if (!isset($_POST['uname']) || empty($_POST['uname'])) {
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

if (!isset($_POST['pass']) || empty($_POST['pass'])) {
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

// set the language
if (!empty($_POST['languageChoice'])) {
    $session->set('language_choice', (int)$_POST['languageChoice']);
} elseif (empty($session->get('language_choice', null))) {
    // just in case both are empty, then use english
    $session->set('language_choice', 1);
} else {
    // keep the current session language token
}

// Settings that will override globals.php
$ignoreAuth_onsite_portal = true;
//

// Authentication
require_once('../interface/globals.php');

if (
    $globalsBag->get('enforce_signin_email')
    && (!isset($_POST['passaddon']) || empty($_POST['passaddon']))
) {
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&c');
    exit();
}

require_once(__DIR__ . "/lib/appsql.class.php");
require_once("$srcdir/user.inc.php");

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Csrf\CsrfUtils;

$logit = new ApplicationTable();
$password_update = $session->get('password_update', 0);
$session->remove('password_update');

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
if ($password_update === 2 && !empty($session->get('pin', null))) {
    $sql = "SELECT " . implode(",", [
            COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT, COL_POR_ONETIME]) . " FROM " . TBL_PAT_ACC_ON .
        " WHERE BINARY " . COL_POR_ONETIME . "= ?";
    $auth = privQuery($sql, [$session->get('forward')]);
    if ($auth !== false) {
        // remove the token from database
        privStatement("UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_ONETIME . "=NULL WHERE BINARY " . COL_POR_ONETIME . " = ?", [$auth['portal_onetime']]);
        // validation
        $validate = substr((string) $auth[COL_POR_ONETIME], 32, 6);
        if (!empty($validate) && !empty($_POST['token_pin'])) {
            if ($session->get('pin') !== $_POST['token_pin']) {
                $auth = false;
            } elseif ($validate !== $_POST['token_pin']) {
                $auth = false;
            }
        } else {
            $auth = false;
        }
        $session->remove('forward');
        $session->remove('pin');
        unset($_POST['token_pin']);
    }
} else {
    // normal login
    $sql = "SELECT " . implode(",", [
            COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT]) . " FROM " . TBL_PAT_ACC_ON .
        " WHERE " . COL_POR_LOGINUSER . "= ?";
    if ($password_update === 1) {
        $sql = "SELECT " . implode(",", [
                COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT]) . " FROM " . TBL_PAT_ACC_ON .
            " WHERE " . COL_POR_USER . "= ?";
    }

    $auth = privQuery($sql, [$_POST['uname']]);
}
if ($auth === false) {
    $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid username'), '', '0');
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w&u');
    exit();
}

if ($password_update === 2) {
    if ($_POST['pass'] != $auth[COL_POR_PWD]) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid password'), '', '0');
        SessionUtil::portalSessionCookieDestroy();
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
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w&p');
        exit();
    }
}



$session->set('portal_username', $auth[COL_POR_USER]);
$session->set('portal_login_username', $auth[COL_POR_LOGINUSER]);

$sql = "SELECT * FROM `patient_data` WHERE `pid` = ?";

if ($userData = sqlQuery($sql, [$auth['pid']])) { // if query gets executed
    if (empty($userData)) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':not active patient'), '', '0');
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($userData['email'] != ($_POST['passaddon'] ?? '') && $globalsBag->get('enforce_signin_email')) {
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':invalid email'), '', '0');
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($userData['allow_patient_portal'] != "YES") {
        // Patient has not authorized portal, so escape
        $logit->portalLog('login attempt', '', ($_POST['uname'] . ':allow portal turned off'), '', '0');
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }

    if ($auth['pid'] != $userData['pid']) {
        // Not sure if this is even possible, but should escape if this happens
        SessionUtil::portalSessionCookieDestroy();
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
                [
                    $_POST['login_uname'],
                    $new_hash,
                    $auth['id']
                ]
            );
            $authorizedPortal = true;
            $logit->portalLog('password update', $auth['pid'], ($_POST['login_uname'] . ': ' . $session->get('ptName') . ':success'));
        }
    }

    if ($auth['portal_pwd_status'] == 0) {
        if (!$authorizedPortal) {
            // Need to enter a new password in the index.php script
            $session->set('password_update', 1);
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
        $session->remove('password_update'); // just being safe
        $session->remove('itsme'); // just being safe
        $session->set('pid', $auth['pid']);
        $session->set('patient_portal_onsite_two', 1);

        $tmp = getUserIDInfo($userData['providerID']);
        $session->set('providerName', ($tmp['fname'] ?? '') . ' ' . ($tmp['lname'] ?? ''));
        $session->set('providerUName', $tmp['username'] ?? null);
        $session->set('sessionUser', '-patient-'); // $_POST['uname'];
        $session->set('providerId', $userData['providerID'] ?: 'undefined');
        $session->set('ptName', $userData['fname'] . ' ' . $userData['lname']);
        // never set authUserID though authUser is used for ACL!
        $session->set('authUser', 'portal-user');
        // Set up the csrf private_key (for the paient portal)
        //  Note this key always remains private and never leaves server session. It is used to create
        //  the csrf tokens.
        CsrfUtils::setupCsrfKey($session->getSymfonySession());

        $logit->portalLog('login', $session->get('pid'), ($session->get('portal_username') . ': ' . $session->get('ptName') . ':success'));
    } else {
        $logit->portalLog('login', '', ($_POST['uname'] . ':not authorized'), '', '0');
        SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit();
    }
} else { // problem with query
    SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit();
}

// now that we are authorized, we need to check for the redirect, sanitize it (or eliminate it if we can't), and then redirect

if (!empty($_REQUEST['redirect'])) {
    // for now we are only going to allow redirects to locations in the module directories, we can open this up more
    // in future requests once we consider the threat vectors
    $safeRedirect = \OpenEMR\Core\ModulesApplication::filterSafeLocalModuleFiles([$_REQUEST['redirect']]);
    if (!empty($safeRedirect)) {
        header('Location: ' . $safeRedirect[0]);
        exit();
    }
}
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
header("Cache-Control: no-cache");
header("Pragma: no-cache");
header('Location: ./home.php');
exit();
