<?php
/**
 * Credential Changes
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// Will start the (patient) portal OpenEMR session/cookie.
require_once(dirname(__FILE__) . "/../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$landingpage = "./../index.php?site=" . urlencode($_SESSION['site_id']);
// kick out if patient not authenticated
if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
    $ignoreAuth = 1;
} else {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
    header('Location: ' . $landingpage . '&w');
    exit;
}
require_once(dirname(__FILE__) . '/../../interface/globals.php');
require_once("$srcdir/authentication/common_operations.php");

use OpenEMR\Core\Header;

//exit if portal is turned off
if (!(isset($GLOBALS['portal_onsite_two_enable'])) || !($GLOBALS['portal_onsite_two_enable'])) {
    echo xlt('Patient Portal is turned off');
    exit;
}

$_SESSION['credentials_update'] = 1;
DEFINE("TBL_PAT_ACC_ON", "patient_access_onsite");
DEFINE("COL_PID", "pid");
DEFINE("COL_POR_PWD", "portal_pwd");
DEFINE("COL_POR_USER", "portal_username");
DEFINE("COL_POR_LOGINUSER", "portal_login_username");
DEFINE("COL_POR_SALT", "portal_salt");
DEFINE("COL_POR_PWD_STAT", "portal_pwd_status");

$sql = "SELECT " . implode(",", array(COL_ID, COL_PID, COL_POR_PWD, COL_POR_USER, COL_POR_LOGINUSER, COL_POR_SALT, COL_POR_PWD_STAT)) .
    " FROM " . TBL_PAT_ACC_ON . " WHERE pid = ?";
$auth = privQuery($sql, array($_SESSION['pid']));
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt('Change Portal Credentials'); ?></title>
    <?php
    Header::setupHeader(['opener']);
    ?>
    <script type="text/javascript">
        function process_new_pass() {
            if (document.getElementById('login_uname').value != document.getElementById('confirm_uname').value) {
                alert(<?php echo xlj('The Username fields are not the same.'); ?>);
                return false;
            }
            if (document.getElementById('pass_new').value != document.getElementById('pass_new_confirm').value) {
                alert(<?php echo xlj('The new password fields are not the same.'); ?>);
                return false;
            }
            if (document.getElementById('pass_new').value == document.getElementById('pass_new_confirm').value) {
                if (!confirm(<?php echo xlj('The new password is the same as the current password. Click Okay to accept anyway.'); ?>)) {
                    return false;
                }
            }
        }
    </script>
    <style>
        .table > tbody > tr > td {
            border-top: 0px;
        }
    </style>
</head>
<body>
    <br><br>
    <div class="container">
        <?php if (empty($_POST['submit'])) { ?>
            <form action="" method="POST" onsubmit="return process_new_pass()">
                <input style="display:none" type="text" name="dummyuname"/>
                <input style="display:none" type="password" name="dummypassword"/>
                <table class="table table-condensed" style="border-bottom:0px;width:100%">
                    <tr>
                        <td width="35%"><strong><?php echo xlt('Account Name'); ?><strong></td>
                        <td><input class="form-control" name="uname" id="uname" type="text" readonly
                                value="<?php echo attr($auth['portal_username']); ?>" /></td>
                    </tr>
                    <tr>
                        <td><strong><?php echo xlt('New or Current Username'); ?><strong></td>
                        <td><input class="form-control" name="login_uname" id="login_uname" type="text" required
                                title="<?php echo xla('Change or keep current. Enter 8 to 20 characters'); ?>" pattern=".{8,20}"
                                value="<?php echo attr($auth['portal_login_username']); ?>" />
                        </td>
                    </tr>
                    <tr>
                    <tr>
                        <td><strong><?php echo xlt('Confirm Username'); ?><strong></td>
                        <td><input class="form-control" name="confirm_uname" id="confirm_uname" type="text" required
                                title="<?php echo xla('You must confirm this Username.'); ?>"
                                autocomplete="none" pattern=".{8,20}" value="" />
                        </td>
                    </tr>
                    </tr>
                    <tr>
                        <td><strong><?php echo xlt('New or Current Password'); ?><strong></td>
                        <td>
                            <input class="form-control" name="pass_new" id="pass_new" type="text" required
                                placeholder="<?php echo xla('Min length is 8 with upper,lowercase,numbers mix'); ?>"
                                title="<?php echo xla('You must enter a new or reenter current password to keep it.'); ?>"
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
                        <td colspan="2"><br><input class="btn btn-primary pull-right" type="submit" name="submit" value="<?php echo xla('Save'); ?>" /></td>
                    </tr>
                </table>
                <div><?php echo '* ' . xlt("All credential fields are case sensitive!") ?></div>
            </form>
        <?php } elseif (isset($_POST['submit'])) {
            if ($auth === false) {
                unset($_POST['submit']);
                header("Location: " . $_SERVER['PHP_SELF']);
            }
            $plain_code = trim($_POST['pass_new']);
            $new_salt = oemr_password_salt();
            $new_hash = oemr_password_hash($plain_code, $new_salt);
            $sqlUpdatePwd = " UPDATE " . TBL_PAT_ACC_ON . " SET " . COL_POR_PWD . "=?, " . COL_POR_SALT . "=?, " . COL_POR_LOGINUSER . "=?" . " WHERE " . COL_ID . "=?";
            privStatement($sqlUpdatePwd, array(
                $new_hash,
                $new_salt,
                $_POST['login_uname'],
                $auth[COL_ID]
            ));
            echo "<script>dlgclose();</script>\n";
        } ?>
    </div><!-- container -->
</body>
</html>
