<?php

/**
 * Add new user.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c7) 2017-2018 Brady Miller
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/calendar.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;
use OpenEMR\Events\User\UserEditRenderEvent;
use OpenEMR\Menu\MainMenuRole;
use OpenEMR\Menu\PatientMenuRole;
use OpenEMR\Services\FacilityService;
use OpenEMR\Services\UserService;

$facilityService = new FacilityService();

if (!AclMain::aclCheckCore('admin', 'users')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Add User")]);
    exit;
}

$alertmsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['google_signin_email'] ?? '';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $alertmsg = xl('Please enter a valid email address for Google Email for Login.');
    }
}

?>
<html>
<head>

<?php Header::setupHeader(['common', 'opener', 'erx']); ?>

<script src="checkpwd_validation.js"></script>

<!-- validation library -->
<?php $use_validate_js = 1;?>
<?php require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>
<?php
// Gets validation rules from Page Validation list.
$collectthis = collectValidationPageRules("/interface/usergroup/usergroup_admin_add.php");
$collectthis = empty($collectthis) ? "undefined" : json_sanitize($collectthis["new_user"]["rules"]);
?>
<script>
var collectvalidation = <?php echo $collectthis; ?>;

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validateEmailField() {
    var emailField = document.new_user.google_signin_email;
    if (!isValidEmail(emailField.value)) {
        alert(<?php echo xlj('Please enter a valid email address.'); ?>);
        emailField.focus();
        return false;
    }
    return true;
}

function submitform() {
    if (!validateEmailField()) {
        return false;
    }

    var valid = submitme(1, undefined, 'new_user', collectvalidation);
    if (!valid) return false;

    top.restoreSession();
    document.new_user.submit();
}

function authorized_clicked() {
    var f = document.forms[0];
    f.calendar.disabled = !f.authorized.checked;
    f.calendar.checked = f.authorized.checked;
}
</script>
</head>
