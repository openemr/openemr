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

require_once(__DIR__ . "/../../../src/Common/Session/SessionUtil.php");
OpenEMR\Common\Session\SessionUtil::portalSessionStart();

$is_portal = (isset($_SESSION['patient_portal_onsite_two']) && $_SESSION['authUser'] == 'portal-user') ? 1 : $_GET['isPortal'];

if (empty($is_portal)) {
    OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
} else {
    //landing page definition -- where to go if something goes wrong
    $landingpage = "index.php?site=" . urlencode($_SESSION['site_id'] ?? null);
    //
    if (isset($_SESSION['pid']) && isset($_SESSION['patient_portal_onsite_two'])) {
        $pid = $_SESSION['pid'];
    } else {
        OpenEMR\Common\Session\SessionUtil::portalSessionCookieDestroy();
        header('Location: ' . $landingpage . '&w');
        exit;
    }
    $ignoreAuth_onsite_portal = true;
}

require_once(__DIR__ . '/../../../interface/globals.php');

$aud = "admin-signature";
$cuser = attr($_SESSION['authUserID'] ?? "-patient-");
$cpid = attr($_SESSION['pid'] ?? "0");
$api_id = $_SESSION['api_csrf_token'] ?? ''; // portal doesn't do remote

$msg1 = xlt('Use Current');
$msg2 = xlt('Cancel');
$msg3 = xlt('Is Authorizing Signature');
$msg4 = xlt('Sign Above');
$msg5 = xlt('Clear Canvas');
$msg6 = xlt('Sign and Save');
$msg7 = xlt('Signatory');
$msg8 = xlt('Signatory did not sign or Signer was closed.');
$msg9 = xlt('Waiting for Signature on Signer Pad');
$msg10 = xlt('Please provide a signature first');
$msg11 = xlt('Signer Pad Checked In and Available');
$msg12 = xlt('Transaction Failed');
// why tempt fate 13 a no no....
$msg14 = xlt("A Remote Signing Device is not answering.") . "<br /><h4>" . xlt("Using this device until remote becomes available.") . "</h4>";
$msg15 = xlt("Remote is Currently Busy");
// module translations
$vars = "<script>const msgSignator='" . $msg7 . "';const msgNoSign='" . $msg8 . "';const msgWaiting='" . $msg9 . "';const msgBusy='" . $msg15 . "';</script>\n";
$vars .= "<script>const msgNeedSign='" . $msg10 . "';const msgCheckIn='" . $msg11 . "';const msgFail='" . $msg12 . "';const msgAnswering='" . $msg14 . "';</script>\n";
$vars .= "<script>var apiToken=" . js_escape($api_id) . ";</script>\n";
// override templates or source to ensure these are set correctly for signer api.
// you'll always have two signatures, portal(not witnessed, that's coming) and clinic.
if ($is_portal) {
    $aud = "patient-signature";
    $vars .= "<script>var isPortal=" . js_escape($is_portal) . ";var cuser=" . js_escape($cuser) . ";var cpid=" . js_escape($cpid) . ";</script>\n";
}
// short & sweet dynamic modal
$modal = <<<MODAL
$vars
<div id='openSignModal' class='modal fade' role='dialog' tabindex='-1'>
    <div class='modal-dialog modal-xl'>
        <div class='modal-content signature-pad'>
            <div class='modal-body signature-pad-body'><span class='sigNav'><label style='display: none;'>
                <input style='display:none;' type='checkbox' id='isAdmin' name='isAdmin' />$msg3</label></span>
                <div class='row signature-pad-content'>
                    <div class='embed-responsive embed-responsive-21by7 border border-dark'>
                        <canvas class="canvas embed-responsive-item"></canvas>
                    </div>
                    <div class='signature-pad-footer text-dark'>
                        <div class='description'>$msg4</div>
                        <div class='btn-group signature-pad-actions bg-light'>
                                <button type='button' class='btn btn-secondary btn-sm clear' data-action='clear'>$msg5</button>
                                <button type='button' class='btn btn-secondary btn-sm' data-action='place' data-type='$aud' id='signatureModal'>$msg1</button>
                                <button type='button' class='btn btn-danger btn-sm' data-dismiss='modal'>$msg2</button>
                                <button type='button' class='btn btn-success btn-sm save' data-action='save_signature'>$msg6</button>
                                <span><h6 id='labelName'></h6></span>
                            <input type='hidden' id='name' /><input type='hidden' id='user' value='$cuser' /><input type='hidden' id='pid' value='$cpid' />
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
