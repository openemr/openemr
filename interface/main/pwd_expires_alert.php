<?php

/**
 * Display a message indicating that the user's password has/will expire.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    ViCarePlus Team, Visolve <vicareplus_engg@visolve.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 ViCarePlus Team, Visolve <vicareplus_engg@visolve.com>
 * @copyright Copyright (c) 2018-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");

use OpenEMR\Common\Auth\AuthUtils;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (AuthUtils::useActiveDirectory()) {
    // this user should never of been directed to this screen
    die(xlt('Not Applicable'));
}

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$result = privQuery("select `last_update_password` from `users_secure` where `id` = ?", [$_SESSION["authUserID"]]);
$current_date = date("Y-m-d");
$pwd_expires = date("Y-m-d", strtotime($result['last_update_password'] . "+" . $GLOBALS['password_expiration_days'] . " days"));
$grace_time = date("Y-m-d", strtotime($pwd_expires . "+" . $GLOBALS['password_grace_time'] . " days"));

// Determine the expiration message to display
//  (note that user can not even get to this screen if credentials are expired)
$msg_alert = "";
if (strtotime($current_date) > strtotime($pwd_expires)) {
    //display warning if user is in grace period to change password
    $msg_alert = xl("Change your password before it expires on") . " " . oeFormatShortDate($grace_time);
} else { // strtotime($pwd_expires) == strtotime($current_date)
    // Display warning if password expires on current day
    $msg_alert = xl("Your password expires today. Please change your password now.");
}
?>

<html>
<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Password Expiration'); ?></title>
</head>
<body class="body_top">
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-danger" role="alert"><?php echo text($msg_alert);?></div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <a href="../usergroup/user_info.php" class="btn btn-secondary btn-transmit" onclick="top.restoreSession()"><?php echo xlt("Change Password");?></a>
        </div>
    </div>
</div>
</body>
</html>
