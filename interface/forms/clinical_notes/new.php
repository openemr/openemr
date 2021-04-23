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
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2021 <sjpadgett@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$formid = 0 + ($_GET['id'] ?? 0);
if ($formid) {
    $sql = "SELECT * FROM `form_clinical_notes` WHERE `id`=? AND `pid` = ? AND `encounter` = ?";
    $res = sqlStatement($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    $check_res = $all;
}
$check_res = $formid ? $check_res : array();
$sql1 = "SELECT `option_id` AS `value`, notes AS code, `title` FROM `list_options` WHERE list_id = ? ORDER BY `seq`";
$result = sqlStatement($sql1, array('Clinical_Note_Type'));
foreach ($result as $value) {
    $clinical_notes_type[] = $value;
}
?>
<html>
<head>
    <title><?php echo xlt("Clinical Notes Form"); ?></title>

    <?php Header::setupHeader(['datetime-picker']); ?>
    <script>
        <?php echo "const codeArray=" . json_encode($clinical_notes_type, true) . ";\n"; ?>
        function duplicateRow(e) {
            var newRow = e.cloneNode(true);
            e.parentNode.insertBefore(newRow, e.nextSibling);
            changeIds('tb_row');
            changeIds('description');
            changeIds('code');
            changeIds('codetext');
            changeIds('code_date');
            changeIds('clinical_notes_type');
            changeIds('user');
            changeIds('count');
            removeVal(newRow.id);
        }

        function removeVal(rowid) {
            rowid1 = rowid.split('tb_row_');
            document.getElementById("description_" + rowid1[1]).value = '';
            document.getElementById("code_" + rowid1[1]).value = '';
            document.getElementById("codetext_" + rowid1[1]).value = '';
            document.getElementById("code_date_" + rowid1[1]).value = '';
            document.getElementById("clinical_notes_type_" + rowid1[1]).value = '';
            document.getElementById("user_" + rowid1[1]).value = '';
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

        function deleteRow(rowId) {
            if (rowId != 'tb_row_1') {
                var elem = document.getElementById(rowId);
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
                codeEl.value = jsText(codeArray[i].code);
                codeTextEl.value = jsText(codeArray[i].title);
                codeContext.dataset.textcontext = jsText(codeArray[i].title);
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
                        <?php if (!empty($check_res)) {
                            foreach ($check_res as $key => $obj) {
                                $context = "";
                                ?>
                        <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                            <fieldset>
                                <div class="form-row">
                                    <input type="hidden" id="user_<?php echo attr($key) + 1; ?>" name="user[]" class="user" value="<?php echo attr($obj["user"]); ?>" />
                                    <input type="hidden" id="code_<?php echo attr($key) + 1; ?>" name="code[]" class="code" value="<?php echo attr($obj["code"]); ?>" />
                                    <input type="hidden" id="codetext_<?php echo attr($key) + 1; ?>" name="codetext[]" class="codetext" value="<?php echo attr($obj["codetext"]); ?>" />
                                    <div class="forms col-lg-1">
                                        <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                                        <input type='text' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"]); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                    </div>
                                    <div class="forms col-lg-2">
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
                                    <div class="forms col-lg-9">
                                        <label for="description_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Narrative'); ?>:</label>
                                        <textarea name="description[]" id="description_<?php echo attr($key) + 1; ?>" data-textcontext="<?php echo text($context); ?>" class="form-control description" rows="14"><?php echo text($obj["description"]); ?></textarea>
                                    </div>
                                    <div class="form-row w-100 mt-2 text-center">
                                        <div class="col-lg-12">
                                            <button type="button" class="btn btn-primary btn-add btn-sm" onclick="duplicateRow(this.parentElement.parentElement.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'>
                                                <?php echo xlt('Add'); ?>
                                            </button>
                                            <button class="btn btn-danger btn-sm" onclick="deleteRow(this.parentElement.parentElement.parentElement.parentElement.parentElement.id);" title='<?php echo xla('Click here to delete the row'); ?>'>
                                                <?php echo xlt('Delete'); ?>
                                            </button>
                                        </div>
                                        <input type="hidden" name="count[]" id="count_<?php echo attr($key) + 1; ?>" class="count" value="<?php echo attr($key) + 1; ?>" />
                                    </div>
                        </div>
                            </fieldset>
                    </div>
                    <hr />
                        <?php }
                        } else { ?>
                        <div class="tb_row" id="tb_row_1">
                            <fieldset>
                                <div class="form-row">
                                    <input type="hidden" id="user_1" name="user[]" class="user" value="<?php echo attr($obj["code"] ?? $_SESSION["authUser"]); ?>" />
                                    <input type="hidden" id="code_1" name="code[]" class="code" value="<?php echo attr($obj["code"] ?? ''); ?>" />
                                    <input type="hidden" id="codetext_1" name="codetext[]" class="codetext" value="<?php echo attr($obj["codetext"]); ?>" />
                                    <div class="forms col-lg-1">
                                        <label for="code_date_1" class="h5"><?php echo xlt('Date'); ?>:</label>
                                        <input type='text' id="code_date_1" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                    </div>
                                    <div class="forms col-lg-2">
                                        <label for="clinical_notes_type_1" class="h5"><?php echo xlt('Type'); ?>:</label>
                                        <select name="clinical_notes_type[]" id="clinical_notes_type_1" class="form-control clinical_notes_type" onchange="typeChange(this)">
                                            <option value=""><?php echo xlt('Select Note Type'); ?></option>
                                            <?php foreach ($clinical_notes_type as $value) :
                                                $selected = ($value['value'] == ($obj["clinical_notes_type"] ?? '')) ? 'selected="selected"' : '';
                                                ?>
                                                <option value="<?php echo attr($value['value']); ?>" <?php echo $selected; ?>><?php echo text($value['title']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="forms col-lg-9">
                                        <label for="description_1" class="h5"><?php echo xlt('Narrative'); ?>:</label>
                                        <textarea name="description[]" id="description_1" data-textcontext="" class="form-control description" rows="14"><?php echo text($obj["description"] ?? ''); ?></textarea>
                                    </div>
                                    <div class="form-row w-100 mt-2 text-center">
                                        <div class="forms col-lg-12">
                                            <button type="button" class="btn btn-primary btn-add btn-sm" onclick="duplicateRow(this.parentElement.parentElement.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'><?php echo xlt('Add'); ?>
                                            </button>
                                            <button type="button" class="btn btn-danger btn-delete btn-sm" onclick="deleteRow(this.parentElement.parentElement.parentElement.parentElement.parentElement.id);" title='<?php echo xla('Click here to delete the row'); ?>'><?php echo xlt('Delete'); ?>
                                            </button>
                                        </div>
                                        <input type="hidden" name="count[]" id="count_1" class="count" value="1" />
                                    </div>
                                </div>
                            </fieldset>
                            <hr />
                        </div>
                        <?php } ?>
            </div>
            <div class="form-group">
                <div class="col-sm-12 position-override">
                    <div class="btn-group" role="group">
                        <button type="submit" onclick="top.restoreSession()" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                        <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel'); ?></button>
                    </div>
                    <input type="hidden" id="clickId" value="" />
                </div>
            </div>
            </form>
        </div>
    </div>
    </div>
</body>
</html>
