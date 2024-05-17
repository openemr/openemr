<?php

/**
 * For various specialty forms to call from dialog using the
 * Ajax, iFrame, Alert, Confirm or HTML modes. Just follow
 * the example patient previous names history form pattern shown below.
 *
 * @package   OpenEMR
 * @link      https://www.open-emr.org
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021-2024 Jerry Padgett <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once(__DIR__ . "/../interface/globals.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!CsrfUtils::verifyCsrfToken($_GET["csrf_token_form"])) {
    CsrfUtils::csrfNotVerified();
}

$disablePreviousNameAdds = (int)$_SESSION['disablePreviousNameAdds'] ?? 0;

$form = trim($_GET['form_handler']);
echo "<script>var form=" . js_escape($form) . "</script>";
?>
<!DOCTYPE html>
<html>
<head>
    <title><?php echo xlt("Specialty Form"); ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php Header::setupHeader(['opener', 'datetime-picker']); ?>
    <script>
        $(function () {
            $("#names_form").submit(function (event) {
                event.preventDefault();
                const url = top.webroot_url + '/library/ajax/specialty_form_ajax.php';
                return fetch(
                    url,
                    {
                        method: 'POST',
                        body: new FormData(this)
                    }
                ).then(data => data.json()).then(data => {
                    let ele = opener.document.getElementById('form_name_history');
                    if (data !== false) {
                        let newOption = new Option(data.name, data.id, true, true);
                        ele.append(newOption);
                    } else {
                        let message = xl("Previous name history already exist. Try again or Cancel.");
                        dialog.alert(message);
                    }
                }).then(() => {
                    dlgclose();
                });
            });
            $("#form_cancel").click(function () {
                dlgclose();
            });
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = false; ?>
                <?php $datetimepicker_showseconds = false; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            });
        });
    </script>
</head>
<body>
    <?php if ($disablePreviousNameAdds === 1) {
        $form = '';
        $frameMassage = "<p class='text-center bg-light text-dark'><strong>" . xlt('Sorry! Add New not allowed here') . "</strong></p><p class='text-justify'>" .
            xlt("Since we are creating a new patient and an associated patient id has not been created yet, we are unable to establish a relationship between patient and the new name to save.") .
            "<br />" . xlt("You can add Previous Names after saving the new patient.") . "</p>";
        ?>
        <div class="container-fluid">
            <div class="text-center">
                <?php echo $frameMassage; ?>
                <dix class="text-center">
                    <button type='button' class='btn btn-secondary' name='form_cancel' id='form_cancel'><?php echo xlt('Cancel'); ?></button>
                </dix>
            </div>
        </div>
    <?php } elseif ($form === 'name_history') { ?>
        <div class="container-fluid">
            <form class="form" id="names_form">
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type="hidden" name="pid" value="<?php echo attr($pid); ?>" />
                <input type="hidden" name="task_name_history" value="save" />
                <div class="col">
                    <p class="small text-center"><?php echo xlt("Patient previous names history. Ensure to add the date the name was last used if known."); ?></p>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_prefix" id="previous_name_prefix" class="form-control" />
                            <label class="form-label" for="previous_name_prefix"><?php echo xlt("Title"); ?></label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_first" id="previous_name_first" class="form-control" />
                            <label class="form-label" for="previous_name_first"><?php echo xlt("First name"); ?></label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_middle" id="previous_name_middle" class="form-control" />
                            <label class="form-label" for="previous_name_middle"><?php echo xlt("Middle name"); ?></label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_last" id="previous_name_last" class="form-control" />
                            <label class="form-label" for="previous_name_last"><?php echo xlt("Last name"); ?></label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_suffix" id="previous_name_suffix" class="form-control" />
                            <label class="form-label" for="previous_name_suffix"><?php echo xlt("Suffix"); ?></label>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <input type="text" name="previous_name_enddate" id="previous_name_enddate" class="form-control datepicker" />
                            <label class="form-label" for="previous_name_enddate"><?php echo xlt("End Date"); ?></label>
                        </div>
                    </div>
                </div>
                <div class="col-12 text-center">
                    <div class="form-group">
                        <button type='submit' class='btn btn-primary' name='form_save' id='form_save' value="save"><?php echo xlt('Save'); ?></button>
                        <button type='button' class='btn btn-secondary' name='form_cancel' id='form_cancel'><?php echo xlt('Cancel'); ?></button>
                    </div>
                </div>
            </form>
        </div>
    <?php } ?>
</body>
</html>
