<?php

/**
 * Encounter form for entering clinical data as a scanned document.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Tyler Wrenn <tyler@tylerwrenn.com>
 * @copyright Copyright (c) 2006-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2020 Tyler Wrenn <tyler@tylerwrenn.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/forms.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$row = array();

if (!$encounter) { // comes from globals.php
    die("Internal error: we do not seem to be in an encounter!");
}

$formid = $_GET['id'] ?? '0';
$imagedir = $GLOBALS['OE_SITE_DIR'] . "/documents/" . check_file_dir_name($pid) . "/encounters";

if (($_POST['delete'] ?? null) == 'delete' || ($_POST['back'] ?? null) == 'back') {
    formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;
}
// If Save was clicked, save the info.
if ($_POST['bn_save'] ?? null) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    // If updating an existing form...
    //
    if ($formid) {
        $query = "UPDATE form_scanned_notes SET notes = ? WHERE id = ?";
        sqlStatement($query, array($_POST['form_notes'], $formid));
    } else { // If adding a new form...
        $query = "INSERT INTO form_scanned_notes (notes) VALUES (?)";
        $formid = sqlInsert($query, array($_POST['form_notes']));
        addForm($encounter, "Scanned Notes", $formid, "scanned_notes", $pid, $userauthorized);
    }

    $imagepath = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . ".jpg";

    // Upload new or replacement document.
    // Always convert it to jpeg.
    if ($_FILES['form_image']['size']) {
        // If the patient's encounter image directory does not yet exist, create it.
        if (!is_dir($imagedir)) {
            $tmp0 = exec("mkdir -p " . escapeshellarg($imagedir), $tmp1, $tmp2);
            if ($tmp2) {
                die("mkdir returned " . text($tmp2) . ": " . text($tmp0));
            }

            exec("touch " . escapeshellarg($imagedir . '/index.html'));
        }

        // Remove any previous image files for this encounter and form ID.
        for ($i = -1; true; ++$i) {
            $suffix = ($i < 0) ? "" : "-$i";
            $path = $imagedir . "/" . check_file_dir_name($encounter) . "_" . check_file_dir_name($formid) . check_file_dir_name($suffix) . ".jpg";
            if (is_file($path)) {
                unlink($path);
            } else {
                if ($i >= 0) {
                    break;
                }
            }
        }

        $tmp_name = $_FILES['form_image']['tmp_name'];
        // default density is 72 dpi, we change to 96.  And -append was removed
        // to create a separate image file for each page.
        $cmd = "magick -density 96 " . escapeshellarg($tmp_name) . " " . escapeshellarg($imagepath);
        $tmp0 = exec($cmd, $tmp1, $tmp2);

        // Handle errors
        if ($tmp2 !== 0) {
            error_log("Command executed: $cmd");
            error_log("Command output: " . implode("\n", $tmp1));
            echo("\"" . text($cmd) . "\" returned $tmp2: " . text(implode("\n", $tmp1)));
        }
    }

    /*formHeader("Redirecting....");
    formJump();
    formFooter();
    exit;*/
}

$imagepath = $imagedir . "/" . check_file_dir_name($encounter . "_" . $formid . ".jpg");
$imageurl = "$web_root/sites/" . $_SESSION['site_id'] .
    "/documents/" . check_file_dir_name($pid) . "/encounters/" . check_file_dir_name($encounter . "_" . $formid . ".jpg");

if ($formid) {
    $row = sqlQuery(
        "SELECT * FROM form_scanned_notes WHERE id = ? AND activity = '1'",
        array($formid)
    );
    $formrow = sqlQuery(
        "SELECT id FROM forms WHERE form_id = ? AND formdir = 'scanned_notes'",
        array($formid)
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo xlt('Scanned Notes'); ?></title>
    <?php Header::setupHeader(); ?>
    <style>
      .dehead {
        font-family: sans-serif;
        font-size: 0.875rem;
        font-weight: bold;
      }

      .detail {
        font-family: sans-serif;
        font-size: 0.875rem;
        font-weight: normal;
      }
    </style>
    <script>
        function newEvt() {
            dlgopen('../../main/calendar/add_edit_event.php?patientid=' + <?php echo js_url($pid); ?>, '_blank', 775, 500);
            return false;
        }

        function deleteme(event) {
            event.stopPropagation();
            dlgopen('../../patient_file/deleter.php?formid=' + <?php echo js_url($formrow['id']); ?> +'&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>, '_blank', 500, 450, '', '', {
                resolvePromiseOn: 'close'
            }).then(function (data) {
                // Restore the session and proceed with form submission
                top.restoreSession();
            }).catch(function (error) {
                // Handle errors if dialog promise is rejected
                console.error("Dialog operation failed:", error);
                alert("Operation was canceled or failed.");
            });
            return false;
        }

        function imdeleted() {
            top.restoreSession();
            $("#delete").val("delete"); // Set the delete flag
            $("#scanned-form").submit(); // Submit the form
        }

        function goBack() {
            top.restoreSession();
            $("#back").val("back");
            $("#scanned-form").submit(); // Submit the form
        }
    </script>
</head>
<body class="body_top">
    <form method="post" enctype="multipart/form-data" id="scanned-form" class="container mt-4"
        action="<?php echo $rootdir ?>/forms/scanned_notes/new.php?id=<?php echo attr_url($formid); ?>">
        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

        <div class="card">
            <div class="card-header text-center bg-light">
                <h5 class="dehead m-0"><?php echo xlt('Scanned Encounter Notes'); ?></h5>
            </div>
            <div class="card-body">
                <div class="form-group row">
                    <label for="form_notes" class="col-sm-2 col-form-label dehead"><?php echo xlt('Comments'); ?></label>
                    <div class="col-sm-10">
                        <textarea id="form_notes" name="form_notes" rows="4" class="form-control"><?php echo text($row['notes']); ?></textarea>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="form_image" class="col-sm-2 col-form-label dehead"><?php echo xlt('Document'); ?></label>
                    <div class="col-sm-10">
                        <?php if (is_file($imagepath)) { ?>
                            <label class="m-0"><?php echo xlt('Replace this Scanned Note:'); ?></label>
                        <?php } else { ?>
                            <label class="m-0"><?php echo xlt('Upload a Scanned Note:'); ?></label>
                        <?php } ?>
                        <input id="form_image" name="form_image" type="file" class="form-control-file" />
                        <hr />
                        <input type="hidden" name="MAX_FILE_SIZE" value="12000000" />
                        <?php if ($formid && is_file($imagepath)) { ?>
                            <div class="text-center">
                                <img src="<?php echo attr($imageurl); ?>" class="img-fluid" alt="<?php echo xla("Unable to load image") . ": " . attr($imageurl); ?>">
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt-3 text-center">
            <div class="btn-group">
                <button type="submit" class="btn btn-primary" name='bn_save' value="save"><?php echo xlt('Save'); ?></button>
                <button type="button" class="btn btn-primary" onclick="newEvt()"><?php echo xlt('Add Appointment'); ?></button>
                <input type="hidden" id="back" name="back" value="">
                <button type="button" class="btn btn-secondary" onclick="return goBack()"><?php echo xlt('Back'); ?></button>
                <?php if ($formrow['id'] && AclMain::aclCheckCore('admin', 'super')) { ?>
                    <input type="hidden" id="delete" name="delete" value="">
                    <button type="button" class="btn btn-danger" onclick="return deleteme(event);"><?php echo xlt('Delete'); ?></button>
                <?php } ?>
            </div>
        </div>
    </form>
</body>
</html>
