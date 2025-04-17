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
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE CNU General Public License 3
 */

// Set $sessionAllowWrite to true to prevent session concurrency issues during authorization related code
$sessionAllowWrite = true;
require_once("../globals.php");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$curPass = $_REQUEST['curPass'];
$newPass = $_REQUEST['newPass'];
$newPass2 = $_REQUEST['newPass2'];

if ($newPass != $newPass2) {
    echo "<div class='alert alert-danger'>" . xlt("Passwords Don't match!") . "</div>";
    exit;
}

$authUtilsUpdatePassword = new AuthUtils();
$success = $authUtilsUpdatePassword->updatePassword($_SESSION['authUserID'], $_SESSION['authUserID'], $curPass, $newPass);
if ($success) {
    echo "<div class='alert alert-success'>" . xlt("Password change successful") . "</div>";
} else {
    // If updatePassword fails the error message is returned
    echo "<div class='alert alert-danger'>" . text($authUtilsUpdatePassword->getErrorMessage()) . "</div>";
}
