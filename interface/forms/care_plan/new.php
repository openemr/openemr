<?php

/**
 * Care plan form new.php
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Jacob T Paul <jacob@zhservices.com>
 * @author    Vinish K <vinish@zhservices.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$formid = (int)($_GET['id'] ?? 0);
if (empty($formid)) {
    $sql = "SELECT form_id, encounter FROM `forms` WHERE formdir = 'care_plan' AND pid = ? AND encounter = ? AND deleted = 0 LIMIT 1";
    $formid = sqlQuery($sql, array($_SESSION["pid"], $_SESSION["encounter"]))['form_id'] ?? 0;
    if (!empty($formid)) {
        echo "<script>var message=" .
            js_escape(xl("Already a Care Plan form for this encounter. Using existing Care Plan form.")) .
            "</script>";
    }
}
if (!empty($formid)) {
    $sql = "SELECT * FROM `form_care_plan` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid,$_SESSION["pid"], $_SESSION["encounter"]));
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    $check_res = $all;
}
$check_res = $formid ? $check_res : array();
$sql1 = "SELECT option_id AS `value`, title FROM `list_options` WHERE list_id = ?";
$result = sqlStatement($sql1, array('Plan_of_Care_Type'));
foreach ($result as $value) :
    $care_plan_type[] = $value;
endforeach;
$reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
$reasonCodeStatii[ReasonStatusCodes::NONE]['description'] = xl("Select a status code");
?>
<html>
    <head>
        <title><?php echo xlt("Care Plan Form"); ?></title>

        <?php Header::setupHeader(['datetime-picker', 'reason-code-widget']);?>
        <script src="<?php echo attr($GLOBALS['webroot']); ?>/interface/forms/care_plan/careplan.js?v=<?php echo attr($GLOBALS['v_js_includes']); ?>" type="text/javascript"></script>
        <script>
            window.addEventListener('DOMContentLoaded', function() {
                window.careplanForm.init(<?php echo js_url($GLOBALS['webroot']); ?>);
            });

            $(function () {
                // special case to deal with static and dynamic datepicker items
                $(document).on('mouseover','.datepicker', function(){
                    $(this).datetimepicker({
                        <?php $datetimepicker_timepicker = true; ?>
                        <?php $datetimepicker_showseconds = false; ?>
                        <?php $datetimepicker_formatInput = false; ?>
                        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
                    });
                });
                if (typeof message !== 'undefined') {
                    alert(message);
                }

                $('[data-toggle="tooltip"]').tooltip();
            });
        </script>
    </head>
    <body>
        <div class="container mt-3">
            <div class="row">
                <div class="col-12">
                    <h2><?php echo xlt('Care Plan Form'); ?></h2>
                    <form method='post' name='my_form' action='<?php echo $rootdir?>/forms/care_plan/save.php?id=<?php echo attr_url($formid) ?>'>
                        <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                        <fieldset>
                            <legend><?php echo xlt('Enter Details'); ?></legend>
                            <div class="container">
                                <?php
                                if (!empty($check_res)) {
                                    foreach ($check_res as $key => $obj) {
                                        $context = "";
                                        ?>
                                    <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                                        <div class="form-row">
                                            <div class="forms col-md-2">
                                                <label for="code_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Code'); ?>:</label>
                                                <input type="text" id="code_<?php echo attr($key) + 1; ?>" name="code[]" class="form-control code" 
                                                  value="<?php echo attr($obj["code"]); ?>"  onclick='sel_code(<?php echo attr_js($GLOBALS['webroot']) ?>,
                                                  this.parentElement.parentElement.parentElement.id);' data-toggle='tooltip' data-placement='bottom' title='<?php echo attr($obj['code']) . "'"; ?> />
                                                <span id="displaytext_<?php echo attr($key) + 1; ?>"  class="displaytext help-block"><?php echo text($obj["codetext"] ?? ''); ?></span>
                                                <input type="hidden" id="codetext_<?php echo attr($key) + 1; ?>" name="codetext[]" class="codetext" value="<?php echo attr($obj["codetext"]); ?>" />
                                                <input type="hidden" id="user_<?php echo attr($key) + 1; ?>" name="user[]" class="user" value="<?php echo attr($obj["user"]); ?>" />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                                                <input type='text' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"]); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM Date of service'); ?>' />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="care_plan_type_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Type'); ?>:</label>
                                                <select name="care_plan_type[]" id="care_plan_type_<?php echo attr($key) + 1; ?>" class="form-control care_plan_type">
                                                    <option value=""></option>
                                                    <?php foreach ($care_plan_type as $value) :
                                                        $selected = ($value['value'] == $obj["care_plan_type"]) ? 'selected="selected"' : '';
                                                        if (!empty($selected)) {
                                                            $context = $value['title'];
                                                        }
                                                        ?>
                                                        <option value="<?php echo attr($value['value']);?>" <?php echo $selected;?>><?php echo text($value['title']);?></option>
                                                    <?php endforeach;?>
                                                    </select>
                                            </div>
                                            <div class="forms col-md-6">
                                                <label for="description_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Description'); ?>:</label>
                                                <textarea name="description[]"  id="description_<?php echo attr($key) + 1; ?>" data-textcontext="<?php echo attr($context); ?>" class="form-control description" rows="6" ><?php echo text($obj["description"]); ?></textarea>
                                            </div>
                                            <div class="form-row w-100 mt-2 text-center">
                                                <div class="forms col-md-12">
                                                    <?php include("templates/careplan_actions.php"); ?>
                                                </div>
                                                <input type="hidden" name="count[]" id="count_<?php echo attr($key) + 1; ?>" class="count" value="<?php echo attr($key) + 1;?>" />
                                            </div>
                                            <?php include "templates/careplan_reason_row.php"; ?>
                                        </div>
                                        <hr />
                                    </div>
                                <?php }
                                } else {  ?>
                                    <div class="tb_row" id="tb_row_1">
                                        <div class="form-row">
                                            <div class="forms col-md-2">
                                                <label for="code_1" class="h5"><?php echo xlt('Code'); ?>:</label>
                                                <input type="text" id="code_1"  name="code[]" class="form-control code" value="<?php echo attr($obj["code"] ?? ''); ?>"  onclick='sel_code(<?php echo attr_js($GLOBALS['webroot']) ?>, this.parentElement.parentElement.parentElement.id || "");'>
                                                <input type="hidden" id="user_1" name="user[]" class="user" value="<?php echo attr($obj["user"] ?? $_SESSION["authUser"]); ?>" />
                                                <span id="displaytext_1"  class="displaytext help-block"></span>
                                                <input type="hidden" id="codetext_1" name="codetext[]" class="codetext" value="<?php echo attr($obj["codetext"] ?? ''); ?>">
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="code_date_1" class="h5"><?php echo xlt('Date'); ?>:</label>
                                                <input type='text' id="code_date_1"  name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="care_plan_type_1" class="h5"><?php echo xlt('Type'); ?>:</label>
                                                <select name="care_plan_type[]" id="care_plan_type_1" class="form-control care_plan_type">
                                                    <option value=""></option>
                                                    <?php foreach ($care_plan_type as $value) :
                                                        $selected = ($value['value'] == ($obj["care_plan_type"] ?? '')) ? 'selected="selected"' : '';
                                                        ?>
                                                        <option value="<?php echo attr($value['value']);?>" <?php echo $selected;?>><?php echo text($value['title']);?></option>
                                                    <?php endforeach;?>
                                                </select>
                                            </div>
                                            <div class="forms col-md-6">
                                                <label for="description_1" class="h5"><?php echo xlt('Description'); ?>:</label>
                                                <textarea name="description[]"  id="description_1" data-textcontext="" class="form-control description" rows="6" ><?php echo text($obj["description"] ?? ''); ?></textarea>
                                            </div>
                                            <div class="form-row w-100 mt-2 text-center">
                                                <div class="forms col-md-12">
                                                    <?php include("templates/careplan_actions.php"); ?>
                                                </div>
                                                <input type="hidden" name="count[]" id="count_1" class="count" value="1" />
                                            </div>
                                            <hr />
                                        </div>
                                        <?php include "templates/careplan_reason_row.php"; ?>
                                    </div>
                                <?php } ?>
                            </div>
                        </fieldset>
                        <div class="form-group">
                            <div class="col-sm-12 position-override">
                                <div class="btn-group" role="group">
                                    <button type="submit" onclick="top.restoreSession()" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                                    <button type="button" class="btn btn-secondary btn-cancel" onclick="top.restoreSession(); parent.closeTab(window.name, false);"><?php echo xlt('Cancel');?></button>
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
