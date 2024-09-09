<?php

/**
 * Credential Changes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019-2021 Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

$ignoreAuth_onsite_portal = $ignoreAuth = false;
// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$landingpage = "./../index.php?site=" . urlencode($_SESSION['site_id'] ?? '');
// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $ignoreAuth_onsite_portal = true;
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit;
}
require_once(dirname(__FILE__) . '/../../interface/globals.php');
require_once(dirname(__FILE__) . "/../lib/appsql.class.php");

use OpenEMR\Common\Auth\AuthHash;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;


$logit = new ApplicationTable();
//exit if portal is turned off
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}
if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"], "portal_index_reset")) {
        CsrfUtils::csrfNotVerified();
    }
}
$_SESSION['credentials_update'] = 1;

DEFINE("TBL_PAT_ACC_ON", "patient_access_onsite");
DEFINE("COL_ID", "id");
DEFINE("COL_PID", "pid");
DEFINE("COL_POR_PWD", "portal_pwd");
DEFINE("COL_POR_USER", "portal_username");
DEFINE("COL_POR_LOGINUSER", "portal_login_username");
DEFINE("COL_POR_PWD_STAT", "portal_pwd_status");

$sql = "SELECT " . implode(",", array(COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_PWD_STAT)) .
    " FROM " . TBL_PAT_ACC_ON . " WHERE pid = ?";
$auth = privQuery($sql, array($_SESSION['pid']));
$password = trim($_POST['pass_current'] ?? '');
unset($_POST['pass_current']);

$password_new = trim($_POST['pass_new'] ?? '');
$errmsg = "";
unset($_POST['pass_new']);
$isSaved = false;
$valid = ((!empty(trim($_POST['uname'] ?? ''))) &&
    (!empty(trim($_POST['login_uname'] ?? ''))) &&
    (!empty($password)) &&
    (trim($_POST['uname']) == $auth[COL_POR_USER]) &&
    (AuthHash::passwordVerify($password, $auth[COL_POR_PWD])));
if (isset($_POST['submit'])) {
    if (!$valid) {
        $errmsg = xl("The credentials you entered were invalid.");
        $logit->portalLog('Credential update attempt', '', ($_POST['uname'] . ':unknown'), '', '0');
    } else {
        $sql = " UPDATE " . TBL_PAT_ACC_ON . " SET ";
        $bind = [];
        $updateFields = [];
        if (!empty($password_new)) {
            $new_hash = (new AuthHash('auth'))->passwordHash($password_new);
            unset($password_new);
            if (empty($new_hash)) {
                // Something is seriously wrong
                error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
                die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
            }
            $updateFields[] = COL_POR_PWD . "=? ";
            $bind[] = $new_hash;
        }
        if (!empty($_POST['login_uname'])) {
            $updateFields[] = COL_POR_LOGINUSER . "=? ";
            $bind[] = $_POST['login_uname'];
        }
        // update username or password or both fields.
        if (!empty($updateFields)) {
            $sqlUpdatePwd = $sql . implode(",", $updateFields) . " WHERE " . COL_ID . "=?";
            $bind[] = $auth[COL_ID];
            privStatement($sqlUpdatePwd, $bind);
        }
        $isSaved = true;
    }
}

$vars = [
    'isSubmit' => !empty($_POST['submit'])
    ,'auth' => $auth
    ,'pid' => $_SESSION['pid']
    ,'errMsg' => $errmsg
    ,'isSaved' => $isSaved
];
try {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render("portal/portal-credentials-settings.html.twig", $vars);
} catch (\Exception $exception) {
    (new \OpenEMR\Common\Logging\SystemLogger())->errorLogCaller($exception->getMessage(), ['trace' => $exception->getTraceAsString()]);
    die(xlt("Failed to render twig file"));
}
