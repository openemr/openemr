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

$landingpage = "./../index.php?site=" . urlencode($_SESSION['site_id']);
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
$password_new = trim($_POST['pass_new']);
$valid = ((!empty(trim($_POST['uname']))) &&
    (!empty(trim($_POST['login_uname']))) &&
    (!empty($password)) &&
    (!empty($password_new)) &&
    (trim($_POST['uname']) == $auth[COL_POR_USER]) &&
    (AuthHash::passwordVerify($password, $auth[COL_POR_PWD])));
if (isset($_POST['submit'])) {
    if (!$valid) {
        $errmsg = xlt("Invalid Current Credentials Error.") . xlt("Unknown.");
        $logit->portalLog('Credential update attempt', '', ($_POST['uname'] . ':unknown'), '', '0');
        die($errmsg);
    }
    $new_hash = (new AuthHash('auth'))->passwordHash($password_new);
    if (empty($new_hash)) {
        // Something is seriously wrong
        error_log('OpenEMR Error : OpenEMR is not working because unable to create a hash.');
        die("OpenEMR Error : OpenEMR is not working because unable to create a hash.");
    }
    $sqlUpdatePwd = " UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_PWD . "=?, " . COL_POR_LOGINUSER . "=?" . " WHERE " . COL_ID . "=?";
    privStatement($sqlUpdatePwd, array(
        $new_hash,
        $_POST['login_uname'],
        $auth[COL_ID]
    ));
}

?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Change Portal Credentials'); ?></title>
    <?php
    Header::setupHeader(['no_main-theme',  'portal-theme', 'opener']);
    if (!empty($_POST['submit'])) {
        unset($_POST['submit']);
        echo "<script>dlgclose();</script>\n";
    }
    ?>
    <script>
        function checkUserName() {
            let vacct = document.getElementById('uname').value;
            let vsuname = document.getElementById('login_uname').value;
            let data = {
                'action': 'userIsUnique',
                'account': vacct,
                'loginUname': vsuname
            };
            $.ajax({
                type: 'GET',
                url: './account.php',
                data: data
            }).done(function (rtn) {
                if (rtn === '1') {
                    return true;
                }
                alert(<?php echo xlj('Log In Name is unavailable. Try again!'); ?>);
                return false;
            });
        }

        function process_new_pass() {
            if (document.getElementById('login_uname').value != document.getElementById('confirm_uname').value) {
                alert(<?php echo xlj('The Username fields are not the same.'); ?>);
                return false;
            }
            if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
                alert(<?php echo xlj('The new password fields are not the same.'); ?>);
                return false;
            }
            if (document.getElementById('pass_current').value == document.getElementById('pass_new_confirm').value) {
                if (!confirm(<?php echo xlj('The new password is the same as the current password. Click Okay to accept anyway.'); ?>)) {
                    return false;
                }
            }
            return true;
        }
    </script>
    <style>
        .table > tbody > tr > td {
            border-top: 0px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <form action="" method="POST" onsubmit="return process_new_pass()">
            <div class="alert alert-info">
                <p><?php echo xlt('Use this form to change your login Password, Username or Both.'); ?></p>
                <p><?php echo xlt('For example, to change your current Password, enter and use your current Username and enter new Password. You must still confirm Password and Username regardless.'); ?></p>
            </div>
            <input style="display:none" type="text" name="dummyuname" />
            <input style="display:none" type="password" name="dummypassword" />
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken("portal_index_reset")); ?>" />
            <table class="table table-sm" style="border-bottom:0px;width:100%">
                <tr>
                    <td width="35%"><strong><?php echo xlt('Portal Account ID for reference'); ?><strong></td>
                    <td><input class="form-control" name="uname" id="uname" type="text" readonly
                            value="<?php echo attr($auth['portal_username']); ?>" /></td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Change or Keep Existing Username'); ?><strong></td>
                    <td><input class="form-control" name="login_uname" id="login_uname" type="text" required onblur="checkUserName()"
                            title="<?php echo xla('Change or keep current. Enter a minimum of 8 characters. Recommended to include symbols and numbers but not required.'); ?>" pattern=".{8,}"
                            value="<?php echo attr($auth['portal_login_username']); ?>" />
                    </td>
                </tr>
                <tr>
                <tr>
                    <td><strong><?php echo xlt('Confirm Above Username'); ?><strong></td>
                    <td><input class="form-control" name="confirm_uname" id="confirm_uname" type="text" required
                            title="<?php echo xla('You must confirm this Username.'); ?>"
                            autocomplete="none" pattern=".{8,80}" value="" />
                    </td>
                </tr>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Current Password to Authorize Changes'); ?><strong></td>
                    <td>
                        <input class="form-control" name="pass_current" id="pass_current" type="password" required
                            placeholder="<?php echo xla('Enter your current password used to login.'); ?>"
                            title="<?php echo xla('Enter your existing current password used to login.'); ?>"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Change or Keep Existing Password'); ?><strong></td>
                    <td>
                        <input class="form-control" name="pass_new" id="pass_new" type="password" required
                            placeholder="<?php echo xla('Minimum length is 8 with upper,lowercase,numbers mix'); ?>"
                            title="<?php echo xla('You must enter a new or reenter current password to keep it. Even for Username change.'); ?>"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" />
                    </td>
                </tr>
                <tr>
                    <td><strong><?php echo xlt('Confirm Password'); ?><strong></td>
                    <td>
                        <input class="form-control" name="pass_new_confirm" id="pass_new_confirm" type="password"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}" autocomplete="none" />
                    </td>
                </tr>
                <tr>
                    <td colspan="2"><br /><input class="btn btn-primary float-right" type="submit" name="submit" value="<?php echo xla('Save'); ?>" /></td>
                </tr>
            </table>
            <div><strong><?php echo '* ' . xlt("All credential fields are case sensitive!") ?></strong></div>
        </form>
    </div><!-- container -->
</body>
</html>
