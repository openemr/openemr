<?php

/**
 * Functional cognitive status form.
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

require_once(__DIR__ . "/../../globals.php");
require_once("$srcdir/api.inc.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Forms\ReasonStatusCodes;
use OpenEMR\Core\Header;

$returnurl = 'encounter_top.php';
$formid = (int)(isset($_GET['id']) ? $_GET['id'] : 0);

if ($formid) {
    $sql = "SELECT * FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid, $_SESSION["pid"], $_SESSION["encounter"]));

    $all = [];
    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    $check_res = $all;
}

$check_res = $formid ? $check_res : array();

$reasonCodeStatii = ReasonStatusCodes::getCodesWithDescriptions();
$reasonCodeStatii[ReasonStatusCodes::NONE]['description'] = xl("Select a status code");

$ob_types = [];
$res = sqlStatement("SELECT `option_id`, `title`  FROM `list_options` WHERE `list_id` = 'Observation_Types' ORDER BY `seq`");
while ($type = sqlFetchArray($res)) {
    $ob_types[] = $type;
}
?>
<html>
<head>
    <title><?php echo xlt("Observation"); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'reason-code-widget']); ?>

    <script src="<?php echo attr($GLOBALS['webroot']); ?>/interface/forms/observation/observation.js?v=<?php echo attr($GLOBALS['v_js_includes']); ?>" type="text/javascript"></script>
    <script>
        window.addEventListener('DOMContentLoaded', function () {
            window.observationForm.init(<?php echo js_url($GLOBALS['webroot']); ?>, <?php echo js_url(collect_codetypes("problem", "csv")) ?>);
        });
        $(function () {
            // special case to deal with static and dynamic datepicker items
            $(document).on('mouseover', '.datepicker', function () {
                $(this).datetimepicker({
                    <?php $datetimepicker_timepicker = true; ?>
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
    <div class="container-xl mt-3">
        <div class="row">
            <div class="col-12">
                <h2><?php echo xlt('Observation'); ?></h2>
                <form method='post' name='my_form' action='<?php echo $rootdir; ?>/forms/observation/save.php?id=<?php echo attr_url($formid); ?>'>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('Enter Details'); ?></legend>
                        <div class="container-xl">
                            <?php
                            if (!empty($check_res)) {
                                foreach ($check_res as $key => $obj) { ?>
                                    <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                                        <div class="form-row">
                                            <div class="forms col-md-2">
                                                <label for="code_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Code'); ?>:</label>
                                                <input type="text" id="code_<?php echo attr($key) + 1; ?>" name="code[]" class="form-control code" value="<?php echo attr($obj["code"]); ?>" onclick='sel_code(<?php echo attr_js($GLOBALS['webroot']); ?>, this.parentElement.parentElement.parentElement.id);' />
                                                <span id="displaytext_<?php echo attr($key) + 1; ?>" class="displaytext help-block"></span>
                                                <input type="hidden" id="description_<?php echo attr($key) + 1; ?>" name="description[]" class="description" value="<?php echo attr($obj["description"]); ?>" />
                                                <input type="hidden" id="code_type_<?php echo attr($key) + 1; ?>" name="code_type[]" class="code_type" value="<?php echo attr($obj["code_type"]); ?>" />
                                                <input type="hidden" id="table_code_<?php echo attr($key) + 1; ?>" name="table_code[]" class="table_code" value="<?php echo attr($obj["table_code"]); ?>" />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="ob_type_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Type'); ?>:</label>
                                                <select name="ob_type[]" id="ob_type_<?php echo attr($key) + 1; ?>" class="ob_type form-control">
                                                    <option value=""><?php echo xlt('Select Type'); ?></option>
                                                <?php foreach ($ob_types as $type) {
                                                    $selected = '';
                                                    if ($obj["ob_type"] == $type['option_id']) {
                                                        $selected = 'selected';
                                                    }
                                                    ?>
                                                    <option <?php echo attr($selected); ?> value="<?php echo attr($type['option_id']); ?>"><?php echo text($type['title']); ?></option>
                                                <?php } ?>
                                                </select>
                                            </div>
                                            <div class="forms col-md-1">
                                                <?php
                                                $style = 'display: block;';
                                                if (($obj["code"] == '21612-7' || $obj["code"] == '8661-1')) {
                                                    $style = 'display: block;';
                                                } elseif (!empty($obj["code"]) && $obj["code"] == 'SS003') {
                                                    $style = 'display: none;';
                                                }
                                                ?>
                                                <label id="ob_value_head_<?php echo attr($key) + 1; ?>" class="ob_value_head h5"><?php echo xlt('Value'); ?>:</label>
                                                <input type="text" name="ob_value[]" id="ob_value_<?php echo attr($key) + 1; ?>" style="<?php echo $style; ?>" class="ob_value form-control" value="<?php echo attr($obj["ob_value"]); ?>" />
                                                <select name="ob_value_phin[]" id="ob_value_phin_<?php echo attr($key) + 1; ?>" class="ob_value_phin" <?php echo ($obj["code"] != 'SS003') ? "style='display: none;'" : ""; ?>>
                                                    <option value="261QE0002X" <?php echo ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QE0002X') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Emergency Care'); ?></option>
                                                    <option value="261QM2500X" <?php echo ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QM2500X') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Medical Specialty'); ?></option>
                                                    <option value="261QP2300X" <?php echo ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QP2300X') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Primary Care'); ?></option>
                                                    <option value="261QU0200X" <?php echo ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QU0200X') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Urgent Care'); ?></option>
                                                </select>
                                            </div>
                                            <div class="forms col-md-1">
                                                <?php
                                                $style = 'display: block';
                                                if (($obj["code"] == 'SS003') || $obj["code"] == '8661-1') {
                                                    $style = 'display: none;';
                                                } elseif ($obj["code"] == '21612-7') {
                                                    $style = 'display: block';
                                                }
                                                ?>
                                                <label id="ob_unit_head_<?php echo attr($key) + 1; ?>" class="ob_unit_head h5" <?php echo (!$obj["ob_value"]) ? 'style="display: block;"' : ''; ?>><?php echo xlt('Units'); ?>:</label>
                                                <?php if ($obj["code"] == '21612-7') { ?>
                                                    <select name="ob_unit[]" id="ob_unit_<?php echo attr($key) + 1; ?>" class="ob_unit">
                                                        <option value="d" <?php echo ($obj["ob_unit"] == 'd') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Day'); ?></option>
                                                        <option value="mo" <?php echo ($obj["ob_unit"] == 'mo') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Month'); ?></option>
                                                        <option value="UNK" <?php echo ($obj["ob_unit"] == 'UNK') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Unknown'); ?></option>
                                                        <option value="wk" <?php echo ($obj["ob_unit"] == 'wk') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Week'); ?></option>
                                                        <option value="a" <?php echo ($obj["ob_unit"] == 'a') ? 'selected = "selected"' : ''; ?>><?php echo xlt('Year'); ?></option>
                                                    </select>
                                                <?php } else { ?>
                                                    <input type="text" name="ob_unit[]" id="ob_unit_<?php echo attr($key) + 1; ?>" class="ob_unit form-control" value="<?php echo attr($obj["ob_unit"]); ?>" />
                                                <?php } ?>
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                                                <input type='text' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"]); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM Date of service'); ?>' />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="code_date_end_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('End Date'); ?>:</label>
                                                <input type='text' id="code_date_end_<?php echo attr($key) + 1; ?>" name='code_date_end[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date_end"]); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM End Date of service'); ?>' />
                                            </div>
                                            <div class="forms col-md-2">
                                                <label for="comments_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Comments'); ?>:</label>
                                                <textarea name="comments[]" id="comments_<?php echo attr($key) + 1; ?>" class="form-control comments" rows="3"><?php echo text($obj["observation"]); ?></textarea>
                                            </div>
                                            <div class="forms col-md-2">
                                                <?php include "templates/observation_actions.php"; ?>
                                            </div>
                                        </div>
                                        <?php include "templates/observation_reason_row.php"; ?>
                                    </div>
                                    <?php
                                }
                            } else {
                                $key = 1;
                                ?>
                                <div class="tb_row" id="tb_row_1">
                                    <div class="form-row">
                                        <div class="forms col-md-2">
                                            <label for="code_1" class="h5"><?php echo xlt('Code'); ?>:</label>
                                            <input type="text" id="code_1" name="code[]" class="form-control code" value="<?php echo attr($obj["code"] ?? ''); ?>" onclick='sel_code(<?php echo attr_js($GLOBALS['webroot']); ?>, this.parentElement.parentElement.parentElement.id);' />
                                            <span id="displaytext_1" class="displaytext help-block"></span>
                                            <input type="hidden" id="description_1" name="description[]" class="description" value="<?php echo attr($obj["description"] ?? ''); ?>" />
                                            <input type="hidden" id="code_type_1" name="code_type[]" class="code_type" value="<?php echo attr($obj["code_type"] ?? ''); ?>" />
                                            <input type="hidden" id="table_code_1" name="table_code[]" class="table_code" value="<?php echo attr($obj["table_code"] ?? ''); ?>" />
                                        </div>
                                        <div class="forms col-md-2">
                                            <label for="ob_type_1" class="h5"><?php echo xlt('Type'); ?>:</label>
                                            <select name="ob_type[]" id="ob_type_1" class="ob_type form-control">
                                                <option value=""><?php echo xlt('Select Type'); ?></option>
                                                <?php foreach ($ob_types as $type) {
                                                    $selected = '';
                                                    if ($obj["ob_type"] ?? null == $type['option_id']) {
                                                        $selected = 'selected';
                                                    }
                                                    ?>
                                                    <option <?php echo attr($selected); ?> value="<?php echo attr($type['option_id']); ?>"><?php echo text($type['title']); ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="forms col-md-1">
                                            <?php
                                            $style = 'display: block';
                                            if (!empty($obj)) {
                                                if ($obj["code"]  == '21612-7' || $obj["code"] == '8661-1') {
                                                    $style = 'display: block;';
                                                } elseif ($obj["code"] == 'SS003') {
                                                    $style = 'display: none;';
                                                }
                                            }
                                            ?>
                                            <label id="ob_value_head_1" class="ob_value_head h5"><?php echo xlt('Value'); ?>:</label>
                                            <input type="text" name="ob_value[]" id="ob_value_1" style="<?php echo $style; ?>" class="ob_value form-control" value="<?php echo attr($obj["ob_value"] ?? ''); ?>" />
                                            <select name="ob_value_phin[]" id="ob_value_phin_1" class="ob_value_phin" <?php echo (empty($obj["code"]) || ($obj["code"] != 'SS003')) ? 'style="display: none;"' : ''; ?>>
                                                <option value="261QE0002X" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_value"])) && ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QE0002X')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Emergency Care'); ?></option>
                                                <option value="261QM2500X" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_value"])) && ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QM2500X')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Medical Specialty'); ?></option>
                                                <option value="261QP2300X" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_value"])) && ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QP2300X')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Primary Care'); ?></option>
                                                <option value="261QU0200X" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_value"])) && ($obj["code"] == 'SS003' && $obj["ob_value"] == '261QU0200X')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Urgent Care'); ?></option>
                                            </select>
                                        </div>
                                        <div class="forms col-md-1">
                                            <?php
                                            $style = 'display: block';
                                            if (!empty($obj)) {
                                                if ((!empty($obj["code"]) && ($obj["code"] == 'SS003') || $obj["code"] == '8661-1')) {
                                                    $style = 'display: none;';
                                                } elseif (($obj["code"] == '21612-7')) {
                                                    $style = 'display: block';
                                                }
                                            }
                                            ?>
                                            <label id="ob_unit_head_1" class="ob_unit_head h5" style="<?php echo $style; ?>"><?php echo xlt('Units'); ?>:</label>
                                            <input type="text" name="ob_unit[]" id="ob_unit_1" class="ob_unit form-control" style="<?php echo $style; ?>" value="" />
                                            <select <?php echo (empty($obj["code"]) || ($obj["code"] != '21612-7')) ? 'style="display: none;"' : ''; ?> name="ob_unit[]" id="ob_unit_1" class="ob_unit">
                                                <option value="d" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_unit"])) && ($obj["code"] == '21612-7' && $obj["ob_unit"] == 'd')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Day'); ?></option>
                                                <option value="mo" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_unit"])) && ($obj["code"] == '21612-7' && $obj["ob_unit"] == 'mo')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Month'); ?></option>
                                                <option value="UNK" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_unit"])) && ($obj["code"] == '21612-7' && $obj["ob_unit"] == 'UNK')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Unknown'); ?></option>
                                                <option value="wk" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_unit"])) && ($obj["code"] == '21612-7' && $obj["ob_unit"] == 'wk')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Week'); ?></option>
                                                <option value="a" <?php echo ((!empty($obj["code"]) && !empty($obj["ob_unit"])) && ($obj["code"] == '21612-7' && $obj["ob_unit"] == 'a')) ? 'selected = "selected"' : ''; ?>><?php echo xlt('Year'); ?></option>
                                            </select>
                                        </div>
                                        <div class="forms col-md-2">
                                            <label for="code_date_1" class="h5"><?php echo xlt('Date'); ?>:</label>
                                            <input type='text' id="code_date_1" name='code_date[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                        </div>
                                        <div class="forms col-md-2">
                                                <label for="code_date_end_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('End Date'); ?>:</label>
                                                <input type='text' id="code_date_end_<?php echo attr($key) + 1; ?>" name='code_date_end[]' class="form-control code_date datepicker" value='<?php echo attr($obj["date_end"] ?? ''); ?>' title='<?php echo xla('yyyy-mm-dd HH:MM End Date of service'); ?>' />
                                        </div>
                                        <div class="forms col-md-2">
                                            <label for="comments_1" class="h5"><?php echo xlt('Comments'); ?>:</label>
                                            <textarea name="comments[]" id="comments_1" class="form-control comments" rows="3"><?php echo text($obj["observation"] ?? ''); ?></textarea>
                                        </div>
                                        <div class="forms col-md-2">
                                            <?php include "templates/observation_actions.php"; ?>
                                        </div>
                                    </div>
                                    <?php include "templates/observation_reason_row.php"; ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </fieldset>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 position-override">
                            <div class="btn-group" role="group">
                                <button type="submit" onclick='top.restoreSession()' class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
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
