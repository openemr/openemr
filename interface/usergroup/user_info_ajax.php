<?php
/**
 * Controller to handle user password change requests.
 *
 * <pre>
 * Expected REQUEST parameters
 * $_REQUEST['pk'] - The primary key being used for encryption. The browser would have requested this previously
 * $_REQUEST['curPass'] - ciphertext of the user's current password
 * $_REQUEST['newPass'] - ciphertext of the new password to use
 * $_REQUEST['newPass2']) - second copy of ciphertext of the new password to confirm proper user entry.
 * </pre>
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Kevin Yeh <kevin.y@integralemr.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2013 Kevin Yeh <kevin.y@integralemr.com>
 * @copyright Copyright (c) 2013 OEMR <www.oemr.org>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/authentication/password_change.php");

use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$curPass=$_REQUEST['curPass'];
$newPass=$_REQUEST['newPass'];
$newPass2=$_REQUEST['newPass2'];

if ($newPass!=$newPass2) {
    echo "<div class='alert alert-danger'>" . xlt("Passwords Don't match!") . "</div>";
    exit;
}

$errMsg='';
$success=update_password($_SESSION['authId'], $_SESSION['authId'], $curPass, $newPass, $errMsg);
if ($success) {
    echo "<div class='alert alert-success'>" . xlt("Password change successful") . "</div>";
} else {
    // If update_password fails the error message is returned
    echo "<div class='alert alert-danger'>" . text($errMsg) . "</div>";
}
