<?php

/**
 * FIDO U2F Support Module
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

require_once('../globals.php');
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

// https is required, and with a proxy the server might not see it.
$scheme = "https://"; // isset($_SERVER['HTTPS']) ? "https://" : "http://";
$appId = $scheme . $_SERVER['HTTP_HOST'];
$u2f = new u2flib_server\U2F($appId);

$userid = $_SESSION['authUserID'];
$action = $_REQUEST['action'];
$user_name = getUserIDInfo($userid);
$user_full_name = $user_name['fname'] . " " . $user_name['lname'];
?>
<html>
<head>
<?php Header::setupHeader(); ?>
<title><?php echo xlt('U2F Registration'); ?></title>
<script src="<?php echo $GLOBALS['webroot'] ?>/library/js/u2f-api.js"></script>
<script>

function doregister() {
  var f = document.forms[0];
  if (f.form_name.value.trim() == '') {
    alert(<?php echo xlj("Please enter a name for this key."); ?>);
    return;
  }
  var request = JSON.parse(f.form_request.value);
  u2f.register(
    <?php echo js_escape($appId); ?>,
    [request],
    [],
    function(data) {
      if(data.errorCode && data.errorCode != 0) {
        alert(<?php echo xlj("Registration failed with error"); ?> + ' ' + data.errorCode);
        return;
      }
      f.form_registration.value = JSON.stringify(data);
      f.action.value = 'reg2';
      top.restoreSession();
      f.submit();
    },
    60
  );
}

function docancel() {
  window.location.href = 'mfa_registrations.php';
}

</script>
<?php
    $arrOeUiSettings = array(
        'heading_title' => xl('Register Universal 2nd Factor Key') . " - " . xl('U2F'),
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
        <div class="row">
            <div class="col-sm-12">
                <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <form method='post' action='mfa_u2f.php' onsubmit='return top.restoreSession()'>
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <?php

        ///////////////////////////////////////////////////////////////////////

        if ($action == 'reg1') {
            list ($request, $signs) = $u2f->getRegisterData();
            ?>
        <div class="row">
            <div class="col-sm-12">
                <fieldset>
                    <legend><?php echo xlt('Register U2F Key for') . " " . text($user_full_name); ?></legend>
                    <div class='col-sm-12'>
                        <p><?php echo xlt("Instructions");?>:
                            <ul>
                                <li><?php echo xlt('This will register a new U2F USB key'); ?></li>
                                <li><?php echo xlt('Type a name for your key, insert it into a USB port and click the Register button below'); ?></li>
                                <li><?php echo xlt('Then press the flashing button on your key within 1 minute to complete registration'); ?></li>
                            </ul>
                    </div>

                    <div class="form-group">
                        <label for="form_name" class="col-sm-2 col-form-label"><?php echo xlt('Please give this key a name'); ?></label>
                        <div class="col-sm-4">
                            <input type='text' class='form-control' name='form_name' id='form_name'>
                            <input type='hidden' name='form_request' value='<?php echo attr(json_encode($request)); ?>'>
                            <input type='hidden' name='form_signs'   value='<?php echo attr(json_encode($signs)); ?>'>
                            <input type='hidden' name='form_registration' value=''>
                        </div>
                    </div>

                    <div class='col-sm-12'>
                        <ul>
                            <li><?php echo xlt('A secure (HTTPS) web connection is required for U2F'); ?></li>
                            <li><?php echo xlt('Chrome browser version 41 and above, Mozilla Firefox browser version 64 and above, Microsoft Edge browser version 19 and above, Safari browser version 13 and above, Opera browser version 40 and Opera browser version 42 and above support FIDO U2F API'); ?></li>
                            <li><?php echo xlt('Internet Explorer browser version 6 to Internet Explorer browser version 11 does not support FIDO U2F API'); ?></li>

                            <li><?php echo xlt('For U2F support on Linux click'); ?>: <a href='https://www.key-id.com/enable-fido-u2f-linux/' rel="noopener" target='_blank'><?php echo text('Enable FIDO U2F Linux'); ?></a></li>
                            <li><?php echo xlt('For Firefox click'); ?>: <a href='https://www.trishtech.com/2018/07/enable-fido-u2f-security-key-yubikey-in-mozilla-firefox/' rel="noopener" target='_blank'><?php echo text('Enable FIDO U2F Key in Firefox'); ?></a></li>
                        </ul>
                    </div>
                </fieldset>
                <div class="form-group clearfix">
                <div class="col-sm-12 text-left position-override">
                        <button type="button" class="btn btn-secondary btn-save" value='<?php echo xla('Register'); ?>' onclick='doregister()'><?php echo xlt('Register'); ?></button>
                        <button type="button" class="btn btn-link btn-cancel" value="<?php echo xla('Cancel'); ?>" onclick="docancel()" ><?php echo xlt('Cancel'); ?></button>
                    </div>
                </div>
            </div>
        </div>
            <?php
        } elseif ($action == 'reg2') {
            if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
                CsrfUtils::csrfNotVerified();
            }
            try {
                $data = $u2f->doRegister(json_decode($_POST['form_request']), json_decode($_POST['form_registration']));
            } catch (u2flib_server\Error $e) {
                die(xlt('Registration error') . ': ' . text($e->getMessage()));
            }
            echo "<script>\n";
            $row = sqlQuery(
                "SELECT COUNT(*) AS count FROM login_mfa_registrations WHERE " .
                "`user_id` = ? AND `name` = ?",
                array($userid, $_POST['form_name'])
            );
            if (empty($row['count'])) {
                sqlStatement(
                    "INSERT INTO login_mfa_registrations " .
                    "(`user_id`, `method`, `name`, `var1`, `var2`) VALUES " .
                    "(?, 'U2F', ?, ?, ?)",
                    array($userid, $_POST['form_name'], json_encode($data), '')
                );
            } else {
                echo " alert(" . xlj('This key name is already in use by you. Try again.') . ");\n";
            }
            echo " window.location.href = 'mfa_registrations.php';\n";
            echo "</script>\n";
        }

        ///////////////////////////////////////////////////////////////////////

        ?>

        <input type='hidden' name='action' value='' />
        </form>
    </div><!--end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
</html>
