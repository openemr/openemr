<?php
/**
 * Ajax Handler for Register
 *
 * @package OpenEMR
 * @link    http://www.open-emr.org
 * @author  Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2017 Jerry Padgett <sjpadgett@gmail.com>
 * @license https://www.gnu.org/licenses/agpl-3.0.en.html GNU Affero General Public License 3
 */

session_start();
if ($_SESSION['patient_portal_onsite_two'] && $_SESSION['pid']) {
    $ignoreAuth_onsite_portal_two = true;
}

require_once("../../interface/globals.php");
require_once("$srcdir/patient.inc");
require_once(dirname(__FILE__) . "/../lib/portal_mail.inc");
require_once("$srcdir/pnotes.inc");
require_once("./account.lib.php");

$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

if ($action == 'set_lang') {
    $_SESSION['language_choice'] = (int) $_REQUEST['value'];
    echo 'okay';
    exit();
} elseif ($action == 'get_newpid') {
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $rtn = isNew($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first'], $email);
    if ((int) $rtn != 0) {
        echo xlt("This account already exists.") . "\r\n\r\n" . xlt("If you are having troubles logging into your account.") . "\r\n" . xlt("Please contact your provider.") . "\r\n" . xlt("Reference this Account Id: ") . $rtn;
        exit();
    }
    $rtn = getNewPid();
    echo "$rtn";

    exit();
} elseif ($action == 'is_new') {
    $email = isset($_REQUEST['email']) ? $_REQUEST['email'] : '';
    $rtn = isNew($_REQUEST['dob'], $_REQUEST['last'], $_REQUEST['first'], $email);
    echo "$rtn";

    exit();
} elseif ($action == 'do_signup') {
    $rtn = doCredentials($_REQUEST['pid']);
    echo "$rtn";

    exit();
} elseif ($action == 'new_insurance') {
    $pid = $_REQUEST['pid'];
    saveInsurance($pid);

    exit();
} elseif ($action == 'notify_admin') {
    $pid = $_REQUEST['pid'];
    $provider = $_REQUEST['provider'];
    $rtn = notifyAdmin($pid, $provider);
    echo "$rtn";

    exit();
} elseif ($action == 'cleanup') {
    unset($_SESSION['patient_portal_onsite_two']);
    unset($_SESSION['authUser']);
    unset($_SESSION['pid']);
    unset($_SESSION['site_id']);
    unset($_SESSION['register']);
    echo 'gone';
    session_destroy(); // I know, makes little sense.
} else {
    exit();
}
die(); //too be sure
