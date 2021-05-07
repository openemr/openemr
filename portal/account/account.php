<?php

/**
 * Ajax Handler for Register
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2020 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(__DIR__ . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

if (
    ($_SESSION['register'] === true && isset($_SESSION['pid'])) ||
    ($_SESSION['credentials_update'] === 1 && isset($_SESSION['pid'])) ||
    ($_SESSION['itsme'] === 1 && isset($_SESSION['password_update']))
) {
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . "/../../interface/globals.php");
require_once("$srcdir/patient.inc");
require_once(__DIR__ . "/../lib/portal_mail.inc");
require_once("$srcdir/pnotes.inc");
require_once("./account.lib.php");
$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';
if ($action == 'set_lang') {
    $_SESSION['language_choice'] = (int)$_REQUEST['value'];
    echo 'okay';
    exit();
}

if ($action == 'userIsUnique') {
    if (
        ((int)$_SESSION['credentials_update'] === 1 && isset($_SESSION['pid'])) ||
        ((int)$_SESSION['itsme'] === 1 && isset($_SESSION['password_update']))
    ) {
        // The above comparisons will not allow querying for usernames if not authorized (ie. not including the register stuff)
        if (empty(trim($_REQUEST['account']))) {
            echo "0";
            exit;
        }
        $tmp = trim($_REQUEST['loginUname']);
        if (empty($tmp)) {
            echo "0";
            exit;
        }
        $auth = sqlQueryNoLog("Select * From patient_access_onsite Where portal_login_username = ? Or portal_username = ?", array($tmp, $tmp));
        if ($auth === false) {
            echo "1";
            exit;
        }

        if ($auth['portal_username'] === trim($_REQUEST['account'])) {
            echo "1";
            exit;
        }
    }
    echo "0";
    exit;
}

if ($action == 'get_newpid') {
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $rtn = isNew($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first'], $email);
    if ((int)$rtn != 0) {
        echo xlt("This account already exists.") . "\r\n\r\n" .
            xlt("We are sorry you are having troubles with your account.") . "\r\n" .
            xlt("Please contact your provider.") . "\r\n" .
            xlt("Reference this Account Number") . " " . $rtn;
        exit();
    }
    $rtn = getNewPid();
    echo "$rtn";
    exit();
}

if ($action == 'is_new') {
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $rtn = isNew($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first'], $email);
    echo "$rtn";
    exit();
}

if ($action == 'do_signup') {
    $rtn = doCredentials($_REQUEST['pid']);
    echo "$rtn";
    exit();
}

if ($action == 'new_insurance') {
    $pid = $_REQUEST['pid'];
    saveInsurance($pid);
    exit();
}

if ($action == 'notify_admin') {
    $pid = $_REQUEST['pid'];
    $provider = $_REQUEST['provider'];
    $rtn = notifyAdmin($pid, $provider);
    echo "$rtn";
    exit();
}

if ($action == 'cleanup') {
    unset($_SESSION['patient_portal_onsite_two']);
    unset($_SESSION['authUser']);
    unset($_SESSION['pid']);
    unset($_SESSION['site_id']);
    unset($_SESSION['register']);
    echo 'gone';
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
// I know, makes little sense.
} else {
    exit();
}
die(); //too be sure
