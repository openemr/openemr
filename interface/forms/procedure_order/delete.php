<?php

/**
 * This script deletes a procedure form and marks
 * associated procedure_order_id as inactive.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Waite <stephen.waite@cmsvt.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Stephen Waite <stephen.waite@cmsvt.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once($GLOBALS['srcdir'] . "/forms.inc");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Delete Encounter Form")]);
    exit;
}

// when the Cancel button is pressed, where do we go?
$returnurl = 'forms.php';

if (!empty($_POST['confirm'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    if ($_POST['id'] != "*" && $_POST['id'] != '') {
      // set the deleted flag of the indicated form
        $sql = "update forms set deleted=1 where id=?";
        sqlStatement($sql, array($_POST['id']));
      // set the procedure order to deleted
        $sql = "update procedure_order p
                left join
                       forms f
                on f.form_id = p.procedure_order_id
                set activity=0
                where f.id=?";
        sqlStatement($sql, array($_POST['id']));
      // Delete the visit's "source=visit" attributes that are not used by any other form.
        sqlStatement(
            "DELETE FROM shared_attributes WHERE " .
            "pid = ? AND encounter = ? AND field_id NOT IN (" .
            "SELECT lo.field_id FROM forms AS f, layout_options AS lo WHERE " .
            "f.pid = ? AND f.encounter = ? AND f.formdir LIKE 'LBF%' AND " .
            "f.deleted = 0 AND " .
            "lo.form_id = f.formdir AND lo.source = 'E' AND lo.uor > 0)",
            array($pid, $encounter, $pid, $encounter)
        );
    }
    // log the event
    EventAuditLogger::instance()->newEvent("delete", $_SESSION['authUser'], $_SESSION['authProvider'], 1, "Form " . $_POST['formname'] . " deleted from Encounter " . $_POST['encounter']);

    // redirect back to the encounter
    $address = "{$GLOBALS['rootdir']}/patient_file/encounter/$returnurl";
    echo "\n<script>top.restoreSession();window.location='$address';</script>\n";
    exit;
}
?>
<html>

<head>
    <?php Header::setupHeader(); ?>
    <title><?php echo xlt('Delete Encounter Form'); ?></title>
</head>

<body>
    <div class="container mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Delete Encounter Form'); ?></h2>
                <form method="post" action="<?php echo $rootdir; ?>/forms/procedure_order/delete.php"
                    name="my_form" id="my_form">
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <?php
                    // output each GET variable as a hidden form input
                    foreach ($_GET as $key => $value) {
                        echo '<input type="hidden" id="' . attr($key) . '" name="' . attr($key) . '" value="' . attr($value) . '"/>' . "\n";
                    }
                    ?>
                    <input type="hidden" id="confirm" name="confirm" value="1" />

                    <p>
                    <?php
                    $formdir = $_GET["formname"];
                    $formName = getFormNameByFormdir($formdir);
                    echo xlt('You are about to delete the following form from this encounter') . ': ' . text(xl_form_title($formName["form_name"]));
                    ?>
                    </p>
                    <div class="btn-group">
                        <button type="button" class="btn btn-danger btn-delete" id="confirmbtn" name="confirmbtn" value='<?php echo xla('Yes, Delete this form'); ?>'>
                            <?php echo xlt('Yes, Delete this form'); ?>
                        </button>
                        <button type="button" class="btn btn-secondary btn-cancel" id="cancel" name="cancel" value='<?php echo xla('Cancel'); ?>'>
                            <?php echo xlt('Cancel'); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<script>
// jQuery stuff to make the page a little easier to use

$(function () {
    $("#confirmbtn").on("click", function() { return ConfirmDelete(); });
    $("#cancel").on("click", function() { location.href=<?php echo js_escape("$rootdir/patient_file/encounter/$returnurl");?>; });
});

function ConfirmDelete() {
    if (confirm(<?php echo xlj('This action cannot be undone. Are you sure you wish to delete this form?'); ?>)) {
        top.restoreSession();
        $("#my_form").submit();
        return true;
    }
    return false;
}
</script>
</body>
</html>
