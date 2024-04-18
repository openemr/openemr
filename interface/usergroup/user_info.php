<?php

/**
 * User password change tool
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/user.inc.php");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

if (AuthUtils::useActiveDirectory()) {
    exit();
}
$userid = $_SESSION['authUserID'];
$user_name = getUserIDInfo($userid);
$user_full_name = $user_name['fname'] . " " . $user_name['lname'];
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('Change Password'); ?></title>

<script src="checkpwd_validation.js"></script>

<script>
//Validating password and display message if password field is empty - starts
var webroot=<?php echo js_escape($webroot); ?>;
function update_password()
{
    top.restoreSession();
    // Not Empty
    // Strong if required
    // Matches

    $.post("user_info_ajax.php",
        {
            curPass:    $("input[name='curPass']").val(),
            newPass:    $("input[name='newPass']").val(),
            newPass2:   $("input[name='newPass2']").val(),
            csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
        },
        function(data)
        {
            $("input[type='password']").val("");
            $("#display_msg").html(data);
        }

    );
    return false;
}

</script>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Change Password'),
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
<body class="body_top" onload="document.forms[0].curPass.focus()">

<?php

$res = sqlStatement("select fname,lname,username from users where id=?", array($_SESSION['authUserID']));
$row = sqlFetchArray($res);
      $iter = $row;
?>
<div id="container_div" class="<?php echo $oemr_ui->oeContainer();?>">
    <div class="row">
        <div class="col-sm-12">
            <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div id="display_msg"></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <form method='post' action='user_info.php' class='form-horizontal' onsubmit='return update_password()'>
                <input type=hidden name=secure_pwd value="<?php echo attr($GLOBALS['secure_password']); ?>">
                <fieldset>
                    <legend><?php echo xlt('Change Password for') . " " . text($user_full_name); ?></legend>
                    <div class="form-group">
                        <label class='control-label col-sm-2'><?php echo xlt('Full Name') . ":"; ?></label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?php echo text($iter["fname"]) . " " . text($iter["lname"]) ?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class='control-label col-sm-2'><?php echo xlt('User Name') . ":"; ?></label>
                        <div class="col-sm-10">
                            <p class="form-control-static"><?php echo text($iter["username"]) ?></p>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for='curPass' class='control-label col-sm-2'><?php echo xlt('Current Password') . ":"; ?></label>
                        <div class='col-sm-3'>
                            <input type='password' class='form-control'  name='curPass'  id='curPass' value="" autocomplete='off'>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class='control-label col-sm-2'><?php echo xlt('New Password') . ":"; ?></label>
                        <div class='col-sm-3'>
                            <input type='password' class='form-control' name='newPass'  value="" autocomplete='off'>
                            <!-- Password Strength Meter -->
                            <div id="password_strength_meter" class="progress">
                                <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width:0%"></div>
                            </div>
                            <div id="password_strength_text"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class='control-label col-sm-2'><?php echo xlt('Repeat New Password') . ":"; ?></label>
                        <div class='col-sm-3'>
                            <input type='password' class='form-control' name=newPass2  value="" autocomplete='off'>
                        </div>
                    </div>
                </fieldset>
                <div class="form-group">
                    <div class='offset-sm-2 col-sm-10'>
                        <button type="Submit" class='btn btn-secondary btn-save'><?php echo xlt('Save Changes'); ?></button>
                    </div>
                </div>
            </form>
    </div>
    </div>
</div><!--end of container div -->
<?php $oemr_ui->oeBelowContainerDiv();?>

</body>
</html>

<?php
//  da39a3ee5e6b4b0d3255bfef95601890afd80709 == blank
?>
