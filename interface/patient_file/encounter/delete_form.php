<?php

/**
 * This script delete an Encounter form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Roberto Vasquez <robertogagliotta@gmail.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Roberto Vasquez <robertogagliotta@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once(dirname(__FILE__) . "/../../../library/forms.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Common\Logging\EventAuditLogger;
use OpenEMR\Core\Header;

// Control access
if (!AclMain::aclCheckCore('admin', 'super')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Delete Encounter Form")]);
    exit;
}

// allow a custom 'delete' form
$deleteform = $incdir . "/forms/" . $_REQUEST["formname"] . "/delete.php";

check_file_dir_name($_REQUEST["formname"]);

if (file_exists($deleteform)) {
    include_once($deleteform);
    exit;
}

// if no custom 'delete' form, then use a generic one

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
                <form method="post" action="<?php echo $rootdir;?>/patient_file/encounter/delete_form.php" name="my_form" id="my_form">
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
                    $formdir = $_GET["formname"] ?? '';
                    $form_id = $_GET["id"] ?? 0;
                    if ($formdir == 'questionnaire_assessments') {
                        $formName = sqlQuery("SELECT form_name FROM forms WHERE id = ? AND deleted = 0", array($form_id));
                    } else {
                        $formName = getFormNameByFormdir($formdir);
                    }
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
