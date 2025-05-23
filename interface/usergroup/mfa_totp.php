<?php

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
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;
require_once('../globals.php');
require_once("$srcdir/classes/Totp.class.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Crypto\CryptoGen;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$userid = $_SESSION['authUserID'];
$action = $_REQUEST['action'];
$user_name = getUserIDInfo($userid);
$user_full_name = $user_name['fname'] . " " . $user_name['lname'];

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

        $(function () {
            $('#clearPass').focus();
        });
    </script>
    <style>
        p {
            text-align: center
        }
            .alert-msg {
            font-size:100%;
            font-weight:700;
        }
    </style>
    <?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Register Time Based One Time Password Key') . " - " . xl('TOTP'),
        'include_patient_name' => false,
        'expandable' => false,
        'expandable_files' => array(),//all file names need suffix _xpd
        'action' => "",//conceal, reveal, search, reset, link or back
        'action_title' => "",
        'action_href' => "",//only for actions - reset, link or back
        'show_help_icon' => false,
        'help_file_name' => ""
    );
    $oemr_ui = new OemrUI($arrOeUiSettings);
    ?>
</head>
<body class="body_top">
    <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <?php
    // If current step is reg1 or reg2, display the header
    if ($action == 'reg1' || $action == 'reg2') { ?>
        <div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
            <div class="row">
                <div class="col-sm-12">
                    <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        <?php
    } ?>    <div class="row">
                <div class="col-sm-12">
                    <form method='post' class="form-horizontal" action='mfa_totp.php' onsubmit="doregister('reg2')">
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />




                        <?php
                        // step 1 is to verify the password
                        if ($action == 'reg1') {
                            $error = (isset($_GET["error"])) ? $_GET["error"] : false;
                            ?>
                            <div>
                                <fieldset>
                                    <legend><?php echo xlt('Provide Password for') . " " . text($user_full_name); ?></legend>
                                    <div class="row">
                                        <div class="col-sm-12">
                                            <?php if ($error == "auth") { ?>
                                                <div class="alert alert-danger alert-msg login-failure m-1">
                                                    <?php echo xlt('Invalid password'); ?>
                                                </div>
                                            <?php } ?>
                                            <p><?php echo xlt('In order to register your device, please provide your OpenEMR login password'); ?></p>
                                            <div class="col-sm-4 offset-sm-4">
                                                <input type="password" class="form-control" id="clearPass" name="clearPass" placeholder="<?php echo xla('Password'); ?>:" >
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="form-group clearfix">
                                <div class="col-sm-12 text-left position-override">
                                        <button type="submit" class="btn btn-secondary btn-save" value="<?php echo xla('Submit'); ?>"><?php echo xlt('Submit'); ?></button>
                                        <button type="button" class="btn btn-link btn-cancel" value="<?php echo xla('Cancel'); ?>" onclick="docancel()" ><?php echo xlt('Cancel'); ?></button>
                                    </div>
                                </div>
                            </div>
                            <?php
                        // step 2 is to validate password and display qr code
                        } elseif ($action == 'reg2') {
                            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                                CsrfUtils::csrfNotVerified();
                            }

                            // Redirect back to step 1 if user password is incorrect
                            if (!(new AuthUtils())->confirmPassword($_SESSION['authUser'], $_POST['clearPass'])) {
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
                                $cryptoGen = new CryptoGen();
                                $secret = $cryptoGen->decryptStandard($existingSecret['var1']);
                                $doesExist = true;
                            }

                            // Generate a new QR code or existing QR code
                            $mfaAuth = new Totp($secret, $_SESSION['authUser']);
                            $qr = $mfaAuth->generateQrCode();


                            // if secret did not exist previously, stores secret in session variable for saving
                            if (!$doesExist) {
                                $_SESSION['totpSecret'] = $mfaAuth->getSecret();
                            }
                            ?>
                            <fieldset>
                                <legend><?php echo xlt('Register TOTP Key for') . " " . text($user_full_name); ?></legend>
                                <div class="row">
                                    <div class="col-sm-12">
                                        <?php if (!$doesExist) { ?>
                                            <p>
                                                <?php echo xlt('Scan the following QR code with your preferred authenticator app to register a new TOTP key.'); ?>
                                            </p>
                                        <?php } else { // $doesExist ?>
                                            <p>
                                                <?php echo xlt('Your current TOTP key QR code is displayed below.'); ?>
                                            </p>
                                        <?php } ?>
                                            <br />
                                            <img src="<?php echo attr($qr); ?>" class="img-responsive center-block" style="height:200px !Important"/>
                                            <br />
                                            <p><?php echo xlt("Or paste in the following code into your authenticator app"); ?></p>
                                            <p><?php echo $mfaAuth->getSecret(); ?></p>
                                            <p><?php echo xlt('Example authenticator apps include'); ?>:</p>
                                            <div class="col-sm-4 offset-sm-4">
                                                <ul>
                                                    <li><?php echo xlt('Google Auth'); ?>
                                                        (<a href="https://itunes.apple.com/us/app/google-authenticator/id388497605?mt=8" target="_blank" rel="noopener">
                                                            <?php echo xlt('ios'); ?>
                                                        </a>,
                                                        <a href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en" target="_blank" rel="noopener">
                                                            <?php echo xlt('android'); ?>
                                                        </a>)</li>
                                                    <li><?php echo xlt('Authy'); ?>
                                                        (<a href="https://itunes.apple.com/us/app/authy/id494168017?mt=8" target="_blank" rel="noopener"><?php echo xlt('ios'); ?></a>, <a href="https://play.google.com/store/apps/details?id=com.authy.authy&hl=en" target="_blank" rel="noopener"><?php echo xlt('android'); ?></a>)</li>
                                                </ul>
                                            </div>
                                    </div>
                                </div>
                            </fieldset>
                            <div class="form-group clearfix">
                                <div class="col-sm-12 text-left position-override">
                                    <?php if (!$doesExist) { ?>
                                        <button type="button" class="btn btn-secondary btn-save" value="<?php echo xla('Register'); ?>" onclick="doregister('reg3')"><?php echo xlt('Register'); ?></button>
                                        <button type="button" class="btn btn-link btn-cancel" value="<?php echo xla('Cancel'); ?>" onclick="docancel()" ><?php echo xlt('Cancel'); ?></button>
                                    <?php } else { // $doesExist ?>
                                        <button type="button" class="btn btn-link btn-back" value="<?php echo xla('Back'); ?>" onclick="docancel()" ><?php echo xlt('Back'); ?></button>
                                    <?php } ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        // step 3 is to save the qr code
                        } elseif ($action == 'reg3') {
                            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                                CsrfUtils::csrfNotVerified();
                            }

                            echo "<script>\n";

                            // Verify that no TOTP method exists already
                            $row = privQuery(
                                "SELECT COUNT(*) AS count FROM login_mfa_registrations WHERE " .
                                "`user_id` = ? AND `method` = 'TOTP'",
                                array($userid)
                            );


                            if (empty($row['count']) && isset($_SESSION['totpSecret'])) {
                                $cryptoGen = new CryptoGen();
                                privStatement(
                                    "INSERT INTO login_mfa_registrations " .
                                    "(`user_id`, `method`, `name`, `var1`, `var2`) VALUES " .
                                    "(?, 'TOTP', 'App Based 2FA', ?, '')",
                                    array($userid, $cryptoGen->encryptStandard($_SESSION['totpSecret']))
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
                </div>
            </div>
    </div><!--end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
</html>
