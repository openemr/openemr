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

$msg1 = xlt('Show Current');
$msg2 = xlt('Cancel');
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
<div class='modal-dialog modal-fluid modal-lg'>
<div class='modal-content'>
<div class='modal-body signature-pad-body'><span class='sigNav'><label style='display: none;'>
<input style='display:none;' type='checkbox' id='isAdmin' name='isAdmin' />$msg3</label></span>
<div class='row sigPad'>
<div class='sign-body'>
<div id='signature-pad' class='signature-pad'>
<div class='embed-responsive embed-responsive-4by3 border border-dark'>
<canvas class="embed-responsive-item"></canvas>
</div>
<div class='signature-pad--footer'>
<div class='description'>$msg4</div>
<div class='button-group'>
<div class='clearfix'>
<button type='button' class='btn btn-primary btn-sm clear' data-action='clear'>$msg5</button>
<button type='button' class='btn btn-link btn-sm' data-dismiss='modal'><span>$msg2</span></button>
<button type='button' class='btn btn-primary btn-sm save' data-action='save_signature'>$msg6</button>
<button class='btn btn-primary btn-sm' data-action='place' data-type='patient-signature' id='signatureModal'>$msg1</button>
<button type='button' class='btn btn-primary btn-sm send' data-action='send_signature' style='display:none'>$msg6</button>
<span><h6 id='labelName'></h6></span></div>
<input type='hidden' id='name' /><input type='hidden' id='user' value='$cuser' /><input type='hidden' id='pid' value='$cpid' />
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
</div>
MODAL;

echo js_escape($modal);
exit();
