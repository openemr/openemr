<?php

/**
 * Multi-Factor Authentication Management
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

function writeRow($method, $name, $allowEdit = false)
{
    echo "        <tr><td>&nbsp;";
    if ($name == '') {
        echo '<i class="fa fa-exclamation-circle oe-text-orange" aria-hidden="true"></i>' . ' ' . text($method);
    } else {
        echo text($method);
    }
    echo "&nbsp;</td><td>&nbsp;";
    echo text($name);
    echo "&nbsp;</td><td>";
    if ($allowEdit) {
        echo "<button type='button' class='btn btn-secondary btn-search' onclick='editclick(" . attr_js($method) . ")'>" . xlt('View') . "</button> &nbsp";
    }
    if ($name) {
        echo "<button type='button' class='btn btn-secondary btn-delete' onclick='delclick(" . attr_js($method) . ", " .
        attr_js($name) . ")'>" . xlt('Delete') . "</button>";
    }
    echo "</td></tr>\n";
}

$userid = $_SESSION['authUserID'];
$user_name = getUserIDInfo($userid);
$user_full_name = $user_name['fname'] . " " . $user_name['lname'];
$message = '';
if (!empty($_POST['form_delete_method'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
    // Delete the indicated MFA instance.
    sqlStatement(
        "DELETE FROM login_mfa_registrations WHERE user_id = ? AND method = ? AND name = ?",
        array($userid, $_POST['form_delete_method'], $_POST['form_delete_name'])
    );
    $message = xl('Delete successful.');
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>

<title><?php echo xlt('Manage Multi Factor Authentication'); ?></title>
<script>

function delclick(mfamethod, mfaname) {
    var f = document.forms[0];
    f.form_delete_method.value = mfamethod;
    f.form_delete_name.value = mfaname;
    top.restoreSession();
    f.submit();
}

function editclick(method) {
    top.restoreSession();
    if (method == 'TOTP') {
        window.location.href = 'mfa_totp.php?action=reg1';
    }
    else {
        alert(<?php echo xlj('Not yet implemented.'); ?>);
    }
}

function addclick(sel) {
    top.restoreSession();
    if (sel.value) {
        if (sel.value == 'U2F') {
            window.location.href = 'mfa_u2f.php?action=reg1';
        } else if (sel.value == 'TOTP') {
            window.location.href = 'mfa_totp.php?action=reg1';
        }
        else {
            alert(<?php echo xlj('Not yet implemented.'); ?>);
        }
    }
    sel.selectedIndex = 0;
}

</script>
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Manage Multi Factor Authentication'),
    'include_patient_name' => false,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "mfa_help.php"
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
        <div class="row">
            <div class="col-sm-12">
            <?php
            if ($message) {?>
              <div id="display_msg" class="alert alert-danger" style="font-size:100%; font-weight:700"><?php echo text($message); ?></div>
                <?php
            }
            ?>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form method='post' action='mfa_registrations.php' onsubmit='return top.restoreSession()'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <div>
                        <fieldset>
                            <legend><?php echo xlt('Current Authentication Method for') . " " . text($user_full_name); ?></legend>
                            <table class='table'>
                                <tr>
                                  <th align='left'>&nbsp;<?php echo xlt('Method'); ?>&nbsp;</th>
                                  <th align='left'>&nbsp;<?php echo xlt('Key Name'); ?>&nbsp;</th>
                                  <th align='left'>&nbsp;<?php echo xlt('Action'); ?>&nbsp;</th>
                                </tr>
                                <?php
                                $res = sqlStatement("SELECT name, method FROM login_mfa_registrations WHERE " .
                                "user_id = ? ORDER BY method, name", array($userid));
                                $disableNewTotp = false;
                                if (sqlNumRows($res)) {
                                    while ($row = sqlFetchArray($res)) {
                                        if ($row['method'] == "TOTP") {
                                            $disableNewTotp = true;
                                            writeRow($row['method'], $row['name'], true);
                                        } else {
                                            writeRow($row['method'], $row['name']);
                                        }
                                    }
                                } else {
                                    writeRow(xl("No method enabled"), '');
                                }
                                ?>
                            </table>
                        </fieldset>
                    </div>
                    <div>
                        <fieldset>
                            <legend><?php echo xlt('Select/Add New Authentication Method for') . " " . text($user_full_name); ?></legend>
                            <div class='col-sm-4 offset-sm-4'>
                                <select name='form_add' onchange='addclick(this)'class='col-sm-12'>
                                    <option value=''><?php echo xlt('Add New...'); ?></option>
                                    <option value='U2F'><?php echo xlt('U2F USB Device'); ?></option>
                                    <option value='TOTP'
                                        <?php echo ($disableNewTotp) ? 'title="' . xla('Only one TOTP Key can be set up per user') . '"' : ''; ?>
                                        <?php echo ($disableNewTotp) ? 'disabled' : ''; ?>>
                                        <?php echo xlt('TOTP Key'); ?>
                                    </option>
                                </select>
                            </div>
                            <input type='hidden' name='form_delete_method' value='' />
                            <input type='hidden' name='form_delete_name' value='' />
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>

    </div><!--end of container div -->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
</html>
