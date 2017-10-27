<?php
// +-----------------------------------------------------------------------------+
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
// Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
//
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
//
// A copy of the GNU General Public License is included along with this program:
// openemr/interface/login/GnuGPL.html
// For more information write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// Author:   Jacob T Paul <jacob@zhservices.com>
//           Vinish K <vinish@zhservices.com>
//           Brady Miller <brady.g.miller@gmail.com>
//
// +------------------------------------------------------------------------------+
use OpenEMR\Core\Header;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

formHeader("Form:Functional and Cognitive Status Form");
$returnurl = 'encounter_top.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');
if ($formid) {
    $sql = "SELECT * FROM `form_functional_cognitive_status` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid,$_SESSION["pid"], $_SESSION["encounter"]));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++) {
        $all[$iter] = $row;
    }
    $check_res = $all;
}

$check_res = $formid ? $check_res : array();
?>
<html>
    <head>
        <?php Header::setupHeader(['datetime-picker']);?>
        <style type="text/css" title="mystyles" media="all">
            @media only screen and (max-width: 768px) {
                [class*="col-"] {
                width: 100%;
                text-align:left!Important;
            }
        </style>
    </head>

    <body class="body_top">
        <script type="text/javascript">

            function duplicateRow(e) {
                var newRow = e.cloneNode(true);
                e.parentNode.insertBefore(newRow, e.nextSibling);
                changeIds('tb_row');
                changeIds('description');
                changeIds('activity');
                changeIds('activity1');
                changeIds('code');
                changeIds('codetext');
                changeIds('code_date');
                changeIds('displaytext');
                removeVal(newRow.id);
            }

            function removeVal(rowid)
            {
                rowid1 = rowid.split('tb_row_');
                document.getElementById("description_" + rowid1[1]).value = '';
                document.getElementById("activity_" + rowid1[1]).checked = false;
                document.getElementById("activity_" + rowid1[1]).value = 0;
                document.getElementById("activity1_" + rowid1[1]).value = 0;
                document.getElementById("code_" + rowid1[1]).value = '';
                document.getElementById("codetext_" + rowid1[1]).value = '';
                document.getElementById("code_date_" + rowid1[1]).value = '';
                document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
            }

            function changeIds(class_val) {
                var elem = document.getElementsByClassName(class_val);
                for (var i = 0; i < elem.length; i++) {
                    if (elem[i].id) {
                        index = i + 1;
                        elem[i].id = class_val + "_" + index;
                    }
                }
            }

            function deleteRow(rowId)
            {
                if (rowId != 'tb_row_1') {
                    var table = document.getElementById("functional_cognitive_status");
                    var rowIndex = document.getElementById(rowId).rowIndex;
                    table.deleteRow(rowIndex);
                }
            }

            function sel_code(id)
            {
                id = id.split('tb_row_');
                var checkId = '_' + id[1];
                document.getElementById('clickId').value = checkId;
                dlgopen('<?php echo $GLOBALS['webroot'] . "/interface/patient_file/encounter/" ?>find_code_popup.php?codetype=SNOMED-CT', '_blank', 700, 400);
            }

            function set_related(codetype, code, selector, codedesc) {
                var checkId = document.getElementById('clickId').value;
                document.getElementById("code" + checkId).value = code;
                document.getElementById("codetext" + checkId).value = codedesc;
                document.getElementById("displaytext" + checkId).innerHTML  = codedesc;
            }

            function checkVal(id)
            {
                var id1 = id.split('activity_')
                if (document.getElementById(id).checked)
                {
                    document.getElementById(id).value = 1;
                    document.getElementById('activity1_' + id1[1]).value = 1;
                }
                else
                {
                    document.getElementById(id).value = 0;
                    document.getElementById('activity1_' + id1[1]).value = 0;
                }
            }

            $(document).ready(function() {
                // special case to deal with static and dynamic datepicker items
                $(document).on('mouseover','.datepicker', function(){
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
        <div class="container">
            <div class="row">
                <div class="page-header">
                        <h2><?php echo xlt('Functional and Cognitive Status Form'); ?></h2>
                </div>
            </div>
            <div class="row">
                <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/functional_cognitive_status/save.php?id=" . attr($formid) . "'>\n"; ?>
                    <fieldset>
                         <legend><?php echo xlt('Enter Details'); ?></legend>
                        <?php
                        if (!empty($check_res)) {
                            foreach ($check_res as $key => $obj) {
                        ?>
                            <div class="tb_row" id="tb_row_<?php echo attr($key) + 1; ?>">
                                <div class="form-group">
                                    <div class=" forms col-xs-3">
                                        <label for="code_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Code'); ?>:</label>
                                        <input type="text" id="code_<?php echo attr($key) + 1; ?>"  name="code[]" class="form-control code" value="<?php echo text($obj{"code"}); ?>"  onclick='sel_code(this.parentElement.parentElement.id);'>
                                        <span id="displaytext_<?php echo attr($key) + 1; ?>"  class="displaytext help-block"></span>
                                        <input type="hidden" id="codetext_<?php echo attr($key) + 1; ?>" name="codetext[]" class="codetext" value="<?php echo text($obj{"codetext"}); ?>">
                                    </div>
                                    <div class="forms col-xs-4">
                                        <label for="description_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Description'); ?>:</label>
                                        <textarea name="description[]"  id="description_<?php echo attr($key) + 1; ?>" class="form-control description"  rows="3" ><?php echo text($obj{"description"}); ?></textarea>
                                    </div>
                                    <div class="forms col-xs-2">
                                        <label for="code_date_<?php echo attr($key) + 1; ?>" class="h5"><?php echo xlt('Date'); ?>:</label>
                                        <input type='text' id="code_date_<?php echo attr($key) + 1; ?>" name='code_date[]' class="form-control code_date datepicker" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                    </div>
                                    <div class="forms col-xs-2">
                                        <label for="activity_1" class="h5"><?php echo xlt('Active'); ?>:</label>
                                        <input type="checkbox" name="activity[]" onclick="checkVal(this.id);" id="activity_<?php echo $key + 1; ?>" value="<?php echo text($obj{"activity"}); ?>" <?php if ($obj{"activity"} == 1) {
                                            echo "checked='checked'"; } ?> class="activity">
                                        <input  type="hidden" name="activity1[]" id="activity1_<?php echo $key + 1; ?>" value="<?php echo text($obj{"activity"}); ?>" class="activity1">
                                    </div>
                                        <div class="forms col-xs-1" style="padding-top:35px">
                                            <i class="fa fa-plus-circle fa-2x" aria-hidden="true" onclick="duplicateRow(this.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'></i>
                                            <i class="fa fa-times-circle fa-2x text-danger"  aria-hidden="true" onclick="deleteRow(this.parentElement.parentElement.parentElement.id);"  title='<?php echo xla('Click here to delete the row'); ?>'></i>
                                        </div>
                                    <div class="clearfix"></div>
                                </div>
                            </div>
                        <?php
                            }
                        } else {
                        ?>
                            <div class="tb_row" id="tb_row_1">
                            <div class="form-group">
                                <div class=" forms col-xs-3">
                                    <label for="code_1" class="h5"><?php echo xlt('Code'); ?>:</label>
                                    <input type="text" id="code_<?php echo attr($key) + 1; ?>"  name="code[]" class="form-control code" value="<?php echo text($obj{"code"}); ?>"  onclick='sel_code(this.parentElement.parentElement.id);'>
                                    <span id="displaytext_1"  class="displaytext help-block"></span>
                                    <input type="hidden" id="codetext_1" name="codetext[]" class="codetext" value="<?php echo text($obj{"codetext"}); ?>">
                                </div>
                                <div class="forms col-xs-5">
                                    <label for="description_1" class="h5"><?php echo xlt('Description'); ?>:</label>
                                    <textarea name="description[]"  id="description_1" class="form-control description"  rows="3" ><?php echo text($obj{"description"}); ?></textarea>
                                </div>
                                <div class="forms col-xs-2">
                                    <label for="code_date_1" class="h5"><?php echo xlt('Date'); ?>:</label>
                                    <input type='text' id="code_date_1"  name='code_date[]' class="form-control code_date datepicker" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' />
                                </div>
                                <div class="forms col-xs-1">
                                    <label for="activity_<?php echo $key + 1; ?>" class="h5"><?php echo xlt('Active'); ?>:&nbsp;</label>
                                    <br>   
                                    <input type="checkbox" name="activity[]" onclick="checkVal(this.id);" id="activity_1" value="0" class="activity">
                                    <input type="hidden" name="activity1[]" id="activity1_1" value="0" class="activity1">
                                </div>
                                <div class="forms col-xs-1 " style="padding-top:35px">
                                    <i class="fa fa-plus-circle fa-2x" aria-hidden="true" onclick="duplicateRow(this.parentElement.parentElement.parentElement);" title='<?php echo xla('Click here to duplicate the row'); ?>'></i>
                                    <i class="fa fa-times-circle fa-2x text-danger"  aria-hidden="true" onclick="deleteRow(this.parentElement.parentElement.parentElement.id);"  title='<?php echo xla('Click here to delete the row'); ?>'></i>
                                </div>
                                <div class="clearfix"></div>
                                <input type="hidden" name="count[]" id="count_1" class="count" value="1">
                            </div>
                        </div>
                        <?php }
                        ?>
                    </fieldset>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-alig:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
                            <div class="btn-group btn-group-pinch" role="group">
                                <button type='submit'   class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                                <button type="button" class="btn btn-link btn-cancel btn-separate-left"onclick="top.restoreSession(); location.href='<?php echo "$rootdir/patient_file/encounter/$returnurl";?>'"><?php echo xlt('Cancel');?></button>
                                <input type="hidden" id="clickId" value="">
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div><!-- end of container div-->
<?php
formFooter();
?>
