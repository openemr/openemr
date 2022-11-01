<?php

/**
 * Clinical Notes form new.php Borrowed from Care Plan
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @author    Stephen Nielson <stephen@nielson.org>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2021 Stephen Nielson <stephen@nielson.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Uuid\UuidRegistry;
use OpenEMR\Core\Header;
use OpenEMR\Services\ClinicalNotesService;

$returnurl = 'encounter_top.php';
$formid = (int) ($_GET['id'] ?? 0);

$clinicalNotesService = new ClinicalNotesService();

if (empty($formid)) {
    $sql = "SELECT form_id, encounter FROM `forms` WHERE formdir = 'clinical_notes' AND pid = ? AND encounter = ? AND deleted = 0 LIMIT 1";
    $formid = sqlQuery($sql, array($_SESSION["pid"], $_SESSION["encounter"]))['form_id'] ?? 0;
    if (!empty($formid)) {
        echo "<script>var message=" .
            js_escape(xl("Already a Clinical Notes form for this encounter. Using existing Clinical Notes form.")) .
        "</script>";
    }
}
if ($formid) {
    $records = $clinicalNotesService->getClinicalNotesForPatientForm($formid, $_SESSION['pid'], $_SESSION['encounter']) ?? [];
    $check_res = [];
    foreach ($records as $record) {
        // we are only going to include active clinical notes, but we leave them as historical records in the system
        // FHIR and other resources still refer to them, they will just be marked as inactive...
        if ($record['activity'] == ClinicalNotesService::ACTIVITY_ACTIVE) {
            $record['uuid'] = UuidRegistry::uuidToString($record['uuid']);
            $check_res[] = $record;
        }
    }
} else {
    $check_res = [
        [
            'id' => 0
            ,'code' => ''
            ,'codetext' => ''
            ,'clinical_notes_type' => ''
            ,'description' => ''
        ]
    ];
}

$clinical_notes_type = $clinicalNotesService->getClinicalNoteTypes();
$clinical_notes_category = $clinicalNotesService->getClinicalNoteCategories();

?>
<html>
<head>
    <title><?php echo xlt("Clinical Notes Form"); ?></title>

    <?php Header::setupHeader(['datetime-picker']); ?>
    <script>
        <?php echo "const codeArray=" . json_encode($clinical_notes_type, true) . ";\n"; ?>
        function duplicateRow(oldId) {
            event.preventDefault();
            let dupRow = document.getElementById(oldId);
            let newRow = dupRow.cloneNode(true);
            dupRow.parentNode.insertBefore(newRow, dupRow.nextSibling);
            changeIds('tb_row');
            changeIds('description');
            changeIds('code');
            changeIds('codetext');
            changeIds('code_date');
            changeIds('clinical_notes_type');
            changeIds('count');
            changeIds('id');
            removeVal(newRow.id);
        }

        function removeVal(rowid) {
            rowid1 = rowid.split('tb_row_');
            document.getElementById("description_" + rowid1[1]).value = '';
            document.getElementById("code_" + rowid1[1]).value = '';
            document.getElementById("codetext_" + rowid1[1]).value = '';
            document.getElementById("code_date_" + rowid1[1]).value = '';
            document.getElementById("clinical_notes_type_" + rowid1[1]).value = '';
            document.getElementById("id_" + rowid1[1]).value = '';
            if (typeof doTemplateEditor !== 'undefined') {
                document.getElementById("description_" + rowid1[1]).addEventListener('dblclick', event => {
                    doTemplateEditor(this, event, event.target.dataset.textcontext);
                })
            }
        }

        function changeIds(class_val) {
            var elem = document.getElementsByClassName(class_val);
            for (let i = 0; i < elem.length; i++) {
                if (elem[i].id) {
                    index = i + 1;
                    elem[i].id = class_val + "_" + index;
                }
                if (class_val == 'count') {
                    elem[i].value = index;
                }
            }
        }

        function deleteRow(othis) {
            rowId = $(othis).parents('.tb_row').attr("id");
            if (rowId != 'tb_row_1' && rowId) {
                let elem = document.getElementById(rowId);
                elem.parentNode.removeChild(elem);
            }
        }

        function typeChange(othis) {
            try {
                let rowid = othis.id.split('clinical_notes_type_');
                let oId = rowid[1];
                let codeEl = document.getElementById("code_" + oId);
                let codeTextEl = document.getElementById("codetext_" + oId);
                let codeContext = document.getElementById("description_" + oId);
                let type = othis.options[othis.selectedIndex].value;
                let i = codeArray.findIndex((v, idx) => codeArray[idx].value === type);
                if (i >= 0)
                {
                    codeEl.value = jsText(codeArray[i].code);
                    codeTextEl.value = jsText(codeArray[i].title);
                    codeContext.dataset.textcontext = jsText(codeArray[i].title);
                } else {
                    console.error("Code not found in array for selected element ", codeEl);
                    // they are clearing out the value so we are going to empty everything out.
                    codeEl.value = "";
                    codeTextEl.value = "";
                    codeContext.vlaue = "";
                }

            } catch (e) {
                alert(jsText(e));
            }
        }

        $(function () {
// special case to deal with static and dynamic datepicker items
            $(document).on('mouseover', '.datepicker', function () {
                $(this).datetimepicker({
                    <?php $datetimepicker_timepicker = false; ?>
                    <?php $datetimepicker_showseconds = false; ?>
                    <?php $datetimepicker_formatInput = false; ?>
                    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                });
            });

            if (typeof message !== 'undefined') {
                alert(message);
            }
        });
    </script>
</head>
<body>
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Clinical Notes Form'); ?></h2>
                <form method='post' name='my_form' action='<?php echo $rootdir ?>/forms/clinical_notes/save.php?id=<?php echo attr_url($formid) ?>'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <legend><?php echo xlt('Note Details'); ?></legend>
                    <div class="container-fluid">
                        <?php
                        foreach ($check_res as $key => $obj) {
                            $context = "";
                            ?>
                        <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                            <fieldset>
                                <div class="form-row">
                                    <input type="hidden" id="id_<?php echo attr($key) + 1; ?>" name="id[]" class="id" value="<?php echo attr($obj["id"]); ?>" />
                                    <input type="hidden" id="code_<?php echo attr($key) + 1; ?>" name="code[]" class="code" value="<?php echo attr($obj["code"]); ?>" />
                                    <input type="hidden" id="codetext_<?php echo attr($key) + 1; ?>" name="codetext[]" class="codetext" value="<?php echo attr($obj["codetext"]); ?>" />
                                    <div class="forms col-lg-4">
                                        <div class="row pl-2">
                                            <div class="col-12">
                                                <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                                                <input type='text' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                            </div>
                                            <div class="col-12">
                                                <label for="clinical_notes_type_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Type'); ?>:</label>
                                                <select name="clinical_notes_type[]" id="clinical_notes_type_<?php echo attr($key) + 1; ?>" class="form-control clinical_notes_type" onchange="typeChange(this)">
                                                    <option value=""><?php echo xlt('Select Note Type'); ?></option>
                                                    <?php foreach ($clinical_notes_type as $value) :
                                                        $selected = ($value['value'] == $obj["clinical_notes_type"]) ? 'selected="selected"' : '';
                                                        if (!empty($selected)) {
                                                            $context = $value['title'];
                                                        }
                                                        ?>
                                                        <option value="<?php echo attr($value['value']); ?>" <?php echo $selected; ?>><?php echo text($value['title']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label for="clinical_notes_category_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Category'); ?>:</label>
                                                <select name="clinical_notes_category[]" id="clinical_notes_category_<?php echo attr($key) + 1; ?>" class="form-control clinical_notes_category">
                                                    <option value=""><?php echo xlt('Select Note Category'); ?></option>
                                                    <?php foreach ($clinical_notes_category as $value) :
                                                        $selected = ($value['value'] == ($obj["clinical_notes_category"] ?? '')) ? 'selected="selected"' : '';
                                                        if (!empty($selected)) {
                                                            $context = $value['title'];
                                                        }
                                                        ?>
                                                        <option value="<?php echo attr($value['value']); ?>" <?php echo $selected; ?>><?php echo text($value['title']); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="forms col-lg-8">
                                        <div class="row pl-2 pr-2">
                                            <div class="col-12">
                                                <label for="description_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Narrative'); ?>:</label>
                                                <textarea name="description[]" id="description_<?php echo attr($key) + 1; ?>" data-textcontext="<?php echo text($context); ?>" class="form-control description" rows="14"><?php echo text($obj["description"]); ?></textarea>
                                            </div>
                                            <div class="col-12 text-sm-center text-md-left mt-1">
                                                <button type="button" class="btn btn-primary btn-add btn-sm" onclick="duplicateRow('tb_row_<?php echo attr($key) + 1; ?>');" title='<?php echo xla('Click here to duplicate the row'); ?>'>
                                                    <?php echo xlt('Add'); ?>
                                                </button>
                                                <button class="btn btn-danger btn-sm" onclick="deleteRow(this);" title='<?php echo xla('Click here to delete the row'); ?>'>
                                                    <?php echo xlt('Delete'); ?>
                                                </button>
                                                <input type="hidden" name="count[]" id="count_<?php echo attr($key) + 1; ?>" class="count" value="<?php echo attr($key) + 1; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </fieldset>
                            <hr />
                        </div>
                        <?php } ?>
                        <div class="form-group">
                            <div class="col-sm-12">
                                <div class="btn-group" role="group">
                                    <button type="submit" onclick="top.restoreSession()" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel'); ?></button>
                                </div>
                                <input type="hidden" id="clickId" value="" />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
