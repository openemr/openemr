<?php

/**
 * Multi-Factor Authentication Management
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

require_once("../globals.php");
require_once(\OpenEMR\Core\OEGlobalsBag::getInstance()->getSrcDir() . "/options.inc.php");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Http\CurrentRequest;
use OpenEMR\Common\Session\SessionWrapperFactory;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$session = SessionWrapperFactory::getInstance()->getActiveSession();
function writeRow($method, $name, $allowEdit = false): void
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

$userid = $session->get('authUserID');
$user_name = getUserIDInfo($userid);
$user_full_name = $user_name['fname'] . " " . $user_name['lname'];
$message = '';
if (!empty($_POST['form_delete_method'])) {
    CsrfUtils::checkCsrfInput(INPUT_POST, dieOnFail: true);
    // Require current-password confirmation before removing an MFA factor so a
    // hijacked session cannot silently disable the user's second factor.
    // Mirrors the password reprompt mfa_totp.php `action=reg2` performs for
    // the register step.
    $clearPass = CurrentRequest::get()->request->getString('clearPass', '');
    if (!(new AuthUtils())->confirmPassword($session->get('authUser'), $clearPass)) {
        $message = xl('Invalid password.');
    } else {
        sqlStatement(
            "DELETE FROM login_mfa_registrations WHERE user_id = ? AND method = ? AND name = ?",
            [$userid, $_POST['form_delete_method'], $_POST['form_delete_name']]
        );
        $message = xl('Delete successful.');
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <?php Header::setupHeader(); ?>

<title><?php echo xlt('Manage Multi Factor Authentication'); ?></title>
<script>

function delclick(mfamethod, mfaname) {
    document.getElementById('delete_target_method').value = mfamethod;
    document.getElementById('delete_target_name').value = mfaname;
    document.getElementById('delete_target_display').textContent = mfaname;
    document.getElementById('delete_pass').value = '';
    $('#deleteConfirmModal').modal('show');
}

function confirmDelete() {
    var pass = document.getElementById('delete_pass').value;
    if (!pass) {
        return;
    }
    var f = document.forms[0];
    f.form_delete_method.value = document.getElementById('delete_target_method').value;
    f.form_delete_name.value = document.getElementById('delete_target_name').value;
    f.clearPass.value = pass;
    $('#deleteConfirmModal').modal('hide');
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
$arrOeUiSettings = [
    'heading_title' => xl('Manage Multi Factor Authentication'),
    'include_patient_name' => false,
    'expandable' => false,
    'expandable_files' => [],//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "mfa_help.php"
];
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
                    <input type="hidden" name="csrf_token_form" value="<?php echo CsrfUtils::collectCsrfToken(session: $session); ?>" />
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
                                "user_id = ? ORDER BY method, name", [$userid]);
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
                            <input type='hidden' name='clearPass' value='' />
                        </fieldset>
                    </div>
                </form>
            </div>
        </div>

    </div><!--end of container div -->

    <div class="modal fade" id="deleteConfirmModal" tabindex="-1" role="dialog" aria-labelledby="deleteConfirmModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteConfirmModalLabel"><?php echo xlt('Confirm Delete'); ?></h5>
                </div>
                <div class="modal-body">
                    <p><?php echo xlt('Removing authentication factor'); ?>: <strong id="delete_target_display"></strong></p>
                    <input type="hidden" id="delete_target_method" value="" />
                    <input type="hidden" id="delete_target_name" value="" />
                    <label for="delete_pass"><?php echo xlt('Re-enter your OpenEMR login password to confirm'); ?></label>
                    <input type="password" class="form-control" id="delete_pass" autocomplete="current-password" />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo xlt('Cancel'); ?></button>
                    <button type="button" class="btn btn-danger" onclick="confirmDelete()"><?php echo xlt('Delete'); ?></button>
                </div>
            </div>
        </div>
    </div>

    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
</html>
