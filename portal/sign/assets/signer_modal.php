<?php
/**
 * Patient Portal Signer Modal Dynamic Template
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2016-2019 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(dirname(__FILE__) . "/../../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$is_portal = isset($_SESSION['portal_init']) ? 1 : $_GET['isPortal'];
if (!$is_portal) {
    session_destroy();
    require_once(dirname(__FILE__) . '/../../../interface/globals.php');
} else {
    require_once dirname(__FILE__) . "/../../verify_session.php";
}

$cuser = attr(isset($_SESSION['authUserID']) ? $_SESSION['authUserID'] : "-patient-");
$cpid = attr(isset($_SESSION['pid']) ? $_SESSION['pid'] : "0");
$api_id = isset($_SESSION['api_csrf_token']) ? $_SESSION['api_csrf_token'] : ''; // portal doesn't do remote

$msg1 = xlt('Show Current Signature On File');
$msg2 = xlt('As appears on documents');
$msg3 = xlt('Is Authorizing Signature');
$msg4 = xlt('Sign Above');
$msg5 = xlt('Clear');
$msg6 = xlt('Acknowledge Electronic Signature');
$msg7 = xlt('Signatory');
$msg8 = xlt('Signatory did not sign or Signer was closed.');
$msg9 = xlt('Waiting for Signature on Signer Pad');
$msg10 = xlt('Please provide a signature first');
$msg11 = xlt('Signer Pad Checked In and Available');
$msg12 = xlt('Transaction Failed');
// why tempt fate 13 a no no....
$msg14 = xlt("A Remote Signing Device is not answering.") . "<br><h4>" . xlt("Using this device until remote becomes available.") . "</h4>";
$msg15 = xlt("Remote is Currently Busy");
// module translations
$vars = "<script>const msgSignator='" . $msg7 . "';const msgNoSign='" . $msg8 . "';const msgWaiting='" . $msg9 . "';const msgBusy='" . $msg15 . "';</script>\n";
$vars .= "<script>const msgNeedSign='" . $msg10 . "';const msgCheckIn='" . $msg11 . "';const msgFail='" . $msg12 . "';const msgAnswering='" . $msg14 . "';</script>\n";
$vars .= "<script>var apiToken=" . js_escape($api_id) . ";</script>\n";
// short & sweet dynamic modal
$modal = <<<MODAL
$vars
<div id='openSignModal' class='modal fade' role='dialog' tabindex='-1'>
<div class='modal-dialog modal-lg'><div class='modal-content'><div class='modal-header'>
<button type='button' class='close pull-right' data-dismiss='modal'>X</button><div class='input-group'>
<span class='input-group-addon' data-action='show'><em> $msg1 <br>$msg2.</em></span>
<img class='signature form-control' data-action='place' data-type='patient-signature' id='signatureModal' alt='Signature On File' src=''>
<h4 id='labelName'></h4></div></div><div class='modal-body signature-pad-body'><ul class='sigNav'><label style='display: none;'>
<input style='display:none;' type='checkbox' id='isAdmin' name='isAdmin'/>$msg3</label></ul>
<div class='row sigPad'><div class='panel'><div class='sign-body'><div id='signature-pad' class='signature-pad'>
<div class='signature-pad--body'><canvas width='800' height="400"></canvas></div><div class='signature-pad--footer'><div class='description'>$msg4</div>
<div class='signature-pad--actions'><div><button type='button' class='btn btn-primary btn-sm clear' data-action='clear'>$msg5</button>
</div><div><button type='button' class='btn btn-primary btn-sm save' data-action='save_signature'>$msg6</button>
<button type='button' class='btn btn-primary btn-sm send' data-action='send_signature'style='display:none'>$msg6</button>
<input type='hidden' id='name'/><input type='hidden' id='user' value='$cuser' /><input type='hidden' id='pid' value='$cpid' /></div>
</div></div></div></div></div></div></div></div></div></div>
<i id='waitend' class='fa fa-refresh fa-spin' style='display: none;'></i>
MODAL;

echo js_escape($modal);
exit();
