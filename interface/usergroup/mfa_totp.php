<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
/**
 * App Based TOTP Support
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Anthony Zullo <anthonykzullo@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Anthony Zullo <anthonykzullo@gmail.com>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */
require_once('../globals.php');
require_once("$srcdir/classes/Totp.class.php");

use OpenEMR\Core\Header;

$userid = $_SESSION['authId'];
$action = $_REQUEST['action'];

?>
<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('TOTP Registration'); ?></title>
    <script>

        function doregister(step, error) {
            var f = document.forms[0];
            f.action.value = step;
            if (error) {
                f.error.value = error;
            }
            f.action.value = step;
            top.restoreSession();
            f.submit();
        }

        function docancel() {
            var redirectUrl = 'mfa_registrations.php';
            window.location.href = 'mfa_registrations.php';
        }

        function dodelete() {
            var f = document.forms[0];
            f.action = 'mfa_registrations.php';
            doregister('delete');
        }
    </script>
</head>
<body class="body_top">
<form method='post' class="form-horizontal" action='mfa_totp.php' onsubmit='return top.restoreSession()'>
    <input type="hidden" name="csrf_token_form" value="<?php echo attr(collectCsrfToken()); ?>" />


    <?php
    // If current step is reg1 or reg2, display the header
    if ($action == 'reg1' || $action == 'reg2') { ?>
        <div class="container">
            <div class="row">
                <div class="col-xs-12">
                    <div class="page-header">
                        <h3><?php echo xlt('Register App Based TOTP'); ?></h3>
                    </div>
                </div>
            </div>
    <?php } ?>

    <?php
    // step 1 is to verify the password
    if ($action == 'reg1') {
        $error = (isset($_GET["error"])) ? $_GET["error"] : false;
        ?>
            <div class="row">
                <div class="col-xs-12">
                    <?php if ($error == "auth") { ?>
                        <div class="alert alert-danger login-failure m-1">
                            Invalid password
                        </div>
                    <?php } ?>
                    <p>In order to register your device, please provide your password</p>
                    <table cellspacing="5">
                        <tr>
                            <td>
                                <label for="clearPass"><?php echo xlt('Password:'); ?>
                            </td>
                            <td>
                                <input type="password" class="form-control" id="clearPass" name="clearPass" placeholder="<?php echo xla('Password:'); ?>" >
                            </td>
                        </tr>
                        <tr>
                            <td></td>
                            <td>
                                <input type="button" value="Submit" onclick="doregister('reg2')" />
                                <input type="button" value="Cancel" onclick="docancel()" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
<?php
    // step 2 is to validate password and display qr code
    } elseif ($action == 'reg2') {

        // Validate user password
        $postedPassword = (isset($_POST['clearPass'])) ? $_POST['clearPass'] : "";
        $userSQL = "SELECT ".COL_PWD.", ".COL_SALT." FROM ".TBL_USERS_SECURE." WHERE username = ? ";
        $userInfo = privQuery($userSQL, array($_SESSION["pc_username"]));
        $hash_current = oemr_password_hash($postedPassword, $userInfo[COL_SALT]);
        $password = $userInfo[COL_PWD];

        // Redirect back to step 1 if user password is incorrect
        if (($hash_current != $password)) {
            header("Location: mfa_totp.php?action=reg1&error=auth");
            exit();
        }

        // Determines whether existing TOTP method exists already
        $existingSecret = privQuery(
            "SELECT var1 FROM login_mfa_registrations WHERE " .
            "`user_id` = ? AND `method` = 'TOTP'",
            array($userid)
        );
        if (empty($existingSecret['var1'])) {
            $secret = false;
            $doesExist = false;
        } else {
            $secret = $existingSecret['var1'];
            $doesExist = true;
        }

        // Generate a new QR code or existing QR code
        $googleAuth = new Totp($password, $secret, $_SESSION["pc_username"]);
        $qr = $googleAuth->generateQrCode();


        // if secret did not exist previously, stores secret in session variable for saving
        if (!$doesExist) {
            $_SESSION['totpSecret'] = $googleAuth->getSecret();
        }
        ?>
            <div class="row">
                <div class="col-xs-12">
                    <p>
                        <?php echo xlt('This will register a new App Based TOTP key.'); ?>
                        <?php echo xlt('Scan the following QR code with your preferred authenticator app.'); ?>
                    </p>
                    <img src="<?php echo $qr; ?>" height="150" />
                    <p>
                        <?php echo xlt('Example authenticator apps include:'); ?>
                        <ul>
                            <li>Google Auth
                                (<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8">ios</a>, <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en">android</a>)</li>
                            <li>Authy
                                (<a href="https://itunes.apple.com/us/app/authy/id494168017?mt=8">ios</a>, <a href="https://play.google.com/store/apps/details?id=com.authy.authy&hl=en">android</a>)</li>
                        </ul>
                    </p>
                    <p>
                        <?php if ($doesExist) { ?>
                            <input type='hidden' name='form_delete_method' value='TOTP' />
                            <input type='hidden' name='form_delete_name' value='App Based 2FA' />
                            <input type='button' value='<?php echo xla('Disable'); ?>' onclick='dodelete();' />
                        <?php } else { ?>
                            <input type='button' value='<?php echo xla('Register'); ?>' onclick='doregister("reg3")'   />
                        <?php } ?>

                        <input type='button' value='<?php echo xla('Cancel'); ?>' onclick='docancel()'   />
                    </p>
                </div>
            </div>
        </div>
<?php
    // step 3 is to save the qr code
    } else if ($action == 'reg3') {

        if (!verifyCsrfToken($_POST["csrf_token_form"])) {
            csrfNotVerified();
        }

        echo "<script>\n";

        // Verify that no TOTP method exists already
        $row = privQuery(
            "SELECT COUNT(*) AS count FROM login_mfa_registrations WHERE " .
            "`user_id` = ? AND `method` = 'TOTP'",
            array($userid)
        );


        if (empty($row['count']) && isset($_SESSION['totpSecret'])) {

            privStatement(
                "INSERT INTO login_mfa_registrations " .
                "(`user_id`, `method`, `name`, `var1`, `var2`) VALUES " .
                "(?, 'TOTP', 'App Based 2FA', ?, '')",
                array($userid, $_SESSION['totpSecret'])
            );
            unset($_SESSION['totpSecret']);

        } else {
            echo " alert(" . xlj('TOTP Method already exists and is enabled. Try again.') . ");\n";
        }

        echo "window.location.href = 'mfa_registrations.php';\n";
        echo "</script>\n";
    }
    ?>

    <input type='hidden' name='action' value='' />
    <input type='hidden' name='error' value='' />
</form>
</body>
</html>