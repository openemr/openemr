<?php
// +-----------------------------------------------------------------------------+ 
// Copyright (C) 2015 Z&H Consultancy Services Private Limited <sam@zhservices.com>
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
//
// +------------------------------------------------------------------------------+

//SANITIZE ALL ESCAPES
$sanitize_all_escapes = true;

//STOP FAKE REGISTER GLOBALS
$fake_register_globals = false;

include_once("../../globals.php");
include_once("$srcdir/api.inc");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");
require_once($GLOBALS['srcdir'] . '/csv_like_join.php');
require_once($GLOBALS['fileroot'] . '/custom/code_types.inc.php');

formHeader("Form:Observation Form");
$returnurl = $GLOBALS['concurrent_layout'] ? 'encounter_top.php' : 'patient_encounter.php';
$formid = 0 + (isset($_GET['id']) ? $_GET['id'] : '');

if ($formid) {
    $sql = "SELECT * FROM `form_observation` WHERE id=? AND pid = ? AND encounter = ?";
    $res = sqlStatement($sql, array($formid,$_SESSION["pid"], $_SESSION["encounter"]));

    for ($iter = 0; $row = sqlFetchArray($res); $iter++)
        $all[$iter] = $row;
    $check_res = $all;
}

$check_res = $formid ? $check_res : array();

?>
<html>
    <head>
        <?php html_header_show(); ?>
        <!-- pop up calendar -->
        <style type="text/css">@import url(<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.css);</style>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar.js"></script>
        <?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dynarch_calendar_setup.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/textformat.js"></script>
        <script type="text/javascript" src="<?php echo $GLOBALS['webroot'] ?>/library/dialog.js"></script>
        <link rel="stylesheet" href="<?php echo $css_header; ?>" type="text/css">
    </head>

    <body class="body_top">
        <script type="text/javascript">

            function duplicateRow(e) {
                var newRow = e.cloneNode(true);
                e.parentNode.insertBefore(newRow, e.nextSibling);
                changeIds('tb_row');
                changeIds('comments');
                changeIds('code');
                changeIds('description');
                changeIds('img_code_date');
                changeIds('code_date');
                changeIds('displaytext');
                changeIds('code_type');
                changeIds('table_code');
                changeIds('ob_value');
                changeIds('ob_unit');
                changeIds('ob_value_phin');
                changeIds('ob_value_head');
                changeIds('ob_unit_head');
                removeVal(newRow.id);
            }

            function removeVal(rowid)
            {
                rowid1 = rowid.split('tb_row_');
                document.getElementById("comments" + rowid1[1]).value = '';
                document.getElementById("code_" + rowid1[1]).value = '';
                document.getElementById("description_" + rowid1[1]).value = '';
                document.getElementById("img_code_date_" + rowid1[1]).value = '';
                document.getElementById("code_date_" + rowid1[1]).value = '';
                document.getElementById("displaytext_" + rowid1[1]).innerHTML = '';
                document.getElementById("code_type_" + rowid1[1]).value = '';
                document.getElementById("table_code_" + rowid1[1]).value = '';
                document.getElementById("ob_value_" + rowid1[1]).value = '';
                document.getElementById("ob_unit_" + rowid1[1]).value = '';
                document.getElementById("ob_value_phin_" + rowid1[1]).value = '';
                document.getElementById("ob_value_head" + rowid1[1]).innerHTML = '';
                document.getElementById("ob_unit_head" + rowid1[1]).innerHTML = '';
            }

            function changeIds(class_val) {
                var elem = document.getElementsByClassName(class_val);
                for (var i = 0; i < elem.length; i++) {
                    if (elem[i].id) {
                        index = i + 1;
                        elem[i].id = class_val + "_" + index;
                    }
                    if (class_val == 'code_date')
                    {
                        Calendar.setup({inputField: class_val + "_" + index, ifFormat: "%Y-%m-%d", button: "img_code_date_" + index});
                    }
                }
            }

            function deleteRow(rowId)
            {
                if (rowId != 'tb_row_1') {
                    var table = document.getElementById("table_observation");
                    var rowIndex = document.getElementById(rowId).rowIndex;
                    table.deleteRow(rowIndex);
                }
            }

            function sel_code(id)
            {
                id = id.split('tb_row_');
                var checkId = '_' + id[1];
                document.getElementById('clickId').value = checkId;
                dlgopen('<?php echo $GLOBALS['webroot'] . "/interface/patient_file/encounter/" ?>find_code_popup.php?codetype=LOINC,PHIN Questions', '_blank', 700, 400);
            }
            
            function set_related(codetype, code, selector, codedesc) {
                var checkId = document.getElementById('clickId').value;
                document.getElementById("code" + checkId).value = code;
                document.getElementById("description" + checkId).value = codedesc;
                document.getElementById("displaytext" + checkId).innerHTML  = codedesc;
                document.getElementById("code_type" + checkId).value = codetype;
                if(codetype == 'LOINC') {
                  document.getElementById("table_code" + checkId).value = 'LN';
                  if(code == '21612-7') {
                    document.getElementById('ob_value_head' + checkId).style.display = '';
                    document.getElementById('ob_unit_head' + checkId).style.display = '';
                    document.getElementById('ob_value' + checkId).style.display = '';
                    var sel_unit_age = document.getElementById('ob_unit' + checkId);
                      if(document.getElementById('ob_unit' + checkId).value == '') {
                        var opt = document.createElement("option");
                        opt.value='d';
                        opt.text='Day';
                        sel_unit_age.appendChild(opt);
                        var opt1 = document.createElement("option");
                        opt1.value='mo';
                        opt1.text='Month';
                        sel_unit_age.appendChild(opt1);
                        var opt2 = document.createElement("option");
                        opt2.value='UNK';
                        opt2.text='Unknown';
                        sel_unit_age.appendChild(opt2);
                        var opt3 = document.createElement("option");
                        opt3.value='wk';
                        opt3.text='Week';
                        sel_unit_age.appendChild(opt3);
                        var opt4 = document.createElement("option");
                        opt4.value='a';
                        opt4.text='Year';
                        sel_unit_age.appendChild(opt4);
                    }
                    document.getElementById('ob_unit' + checkId).style.display = 'block';
                    document.getElementById('ob_value_phin' + checkId).style.display = 'none';
                  }
                  else if (code == '8661-1'){
                    document.getElementById('ob_unit_head' + checkId).style.display = 'none';
                    var select = document.getElementById('ob_unit' + checkId);
                    select.innerHTML= "";
                    document.getElementById('ob_unit' + checkId).style.display = 'none';
                    document.getElementById('ob_value_phin' + checkId).style.display = 'none';
                    document.getElementById('ob_value_head' + checkId).style.display = '';
                    document.getElementById('ob_value' + checkId).style.display = '';
                  }
                }
                else {
                  document.getElementById("table_code" + checkId).value = 'PHINQUESTION';
                  document.getElementById('ob_value_head' + checkId).style.display = '';
                  document.getElementById('ob_unit_head' + checkId).style.display = 'none';
                  var select_unit = document.getElementById('ob_unit' + checkId);
                  select_unit.innerHTML= "";
                  document.getElementById('ob_value' + checkId).value = '';
                  document.getElementById('ob_value' + checkId).style.display = 'none';
                  document.getElementById('ob_unit' + checkId).style.display = 'none';
                  document.getElementById('ob_value_phin' + checkId).style.display = '';
                }
            }
            
        </script>
        <p><span class="forms-title"><?php echo xlt('Observation Form'); ?></span></p>
        </br>
        <?php echo "<form method='post' name='my_form' " . "action='$rootdir/forms/observation/save.php?id=" . attr($formid) . "'>\n"; ?>
        <table id="table_observation" border="0" >            

            <?php
            if (!empty($check_res)) {
                foreach ($check_res as $key => $obj) {
                  $style= '';
                    ?>
                    <tr class="tb_row" id="tb_row_<?php echo $key + 1; ?>">
                        <td align="left" class="forms"><?php echo xlt('Code'); ?>:</td>
                        <td class="forms">
                            <input type="text" id="code_<?php echo $key + 1; ?>" style="width:50%;" name="code[]" class="code" value="<?php echo text($obj{"code"}); ?>"  onclick='sel_code(this.parentElement.parentElement.id);'><br>
                            <span id="displaytext_<?php echo $key + 1; ?>" style="width:210px !important;display: block;font-size:13px;color: blue;" class="displaytext"><?php echo text($obj{"description"}); ?></span>
                            <input type="hidden" id="description_<?php echo $key + 1; ?>" name="description[]" class="description" value="<?php echo text($obj{"description"}); ?>">
                            <input type="hidden" id="code_type_<?php echo $key + 1; ?>" name="code_type[]" class="code_type" value="<?php echo text($obj{"code_type"}); ?>">
                            <input type="hidden" id="table_code_<?php echo $key + 1; ?>" name="table_code[]" class="table_code" value="<?php echo text($obj{"table_code"}); ?>">                          
                        </td>
                        <td id="ob_value_head_<?php echo $key + 1; ?>" class="forms ob_value_head" align="left" <?php if( !$obj{"ob_value"}) {?>style="display: none;" <?php }?>><?php echo xlt('Value'); ?>:</td>
                        <td class="forms"> 
                           <?php 
                                if((text($obj{"code"}) == '21612-7' || text($obj{"code"}) == '8661-1')) { 
                                  $style = 'display: block;'; 
                                } 
                                elseif(text($obj{"code"}) == 'SS003' || !$obj{"ob_value"}) {
                                  $style = 'display: none;'; 
                                }
                           ?>
                          <input type="text" name="ob_value[]" id="ob_value_<?php echo $key + 1; ?>" style="width: 60%; <?php echo $style;?>" class="ob_value" value="<?php if((text($obj{"code"}) == '21612-7' || text($obj{"code"}) == '8661-1') && text($obj{"code"}) != 'SS003') echo text($obj{"ob_value"}); ?>">
                          <select name="ob_value_phin[]" id="ob_value_phin_<?php echo $key + 1; ?>" class="ob_value_phin" <?php if(text($obj{"code"}) != 'SS003') { ?> style="display: none;" <?php }?>>
                              <option value="261QE0002X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QE0002X') echo 'selected = "selected"' ;?>><?php echo xlt('Emergency Care'); ?></option>
                              <option value="261QM2500X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QM2500X') echo 'selected = "selected"' ;?>><?php echo xlt('Medical Specialty'); ?></option>
                              <option value="261QP2300X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QP2300X') echo 'selected = "selected"' ;?>><?php echo xlt('Primary Care'); ?></option>
                              <option value="261QU0200X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QU0200X') echo 'selected = "selected"' ;?>><?php echo xlt('Urgent Care'); ?></option>
                          </select>
                        </td>
                        <?php 
                            if(!$obj{"ob_unit"} || (text($obj{"code"}) == 'SS003')|| text($obj{"code"}) == '8661-1') {
                              $style = 'display: none;' ;
                            }
                            elseif(text($obj{"code"}) == '21612-7') {
                              $style = 'display: block';
                            }
                        ?>
                        <td id="ob_unit_head_<?php echo $key + 1; ?>" class="forms ob_unit_head" align="left" style="<?php echo $style;?>"><?php echo xlt('Units'); ?>:</td>
                        <td class="forms"> 
                          <select <?php if(text($obj{"code"}) != '21612-7') { ?> style="display: none;" <?php }?>  name="ob_unit[]" id="ob_unit_<?php echo $key + 1; ?>" class="ob_unit">
                              <option value="d" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'd') echo 'selected = "selected"' ;?>><?php echo xlt('Day'); ?></option>
                              <option value="mo" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'mo') echo 'selected = "selected"' ;?>><?php echo xlt('Month'); ?></option>
                              <option value="UNK" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'UNK') echo 'selected = "selected"' ;?>><?php echo xlt('Unknown'); ?></option>
                              <option value="wk" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'wk') echo 'selected = "selected"' ;?>><?php echo xlt('Week'); ?></option>
                              <option value="a" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'a') echo 'selected = "selected"' ;?>><?php echo xlt('Year'); ?></option>
                          </select>
                        </td>
                        <td align="left" class="forms"><?php echo xlt('Comments'); ?>:</td>
                        <td class="forms">
                            <textarea rows="4" id="comments_<?php echo $key + 1; ?>" cols="20" name="comments[]" class="comments"><?php echo text($obj{"observation"}); ?></textarea>
                        </td>
                        <td align="left" class="forms"><?php echo xlt('Date'); ?>:</td>
                        <td class="forms">
                            <input type='text' id="code_date_<?php echo $key + 1; ?>" size='10' name='code_date[]' class="code_date" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)' />
                            <img src='../../pic/show_calendar.gif' align='absbottom' id="img_code_date_<?php echo $key + 1; ?>" width='24' height='22' class="img_code_date" border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to choose a date'); ?>'>
                        </td>
                        <td>
                            <img src='../../pic/add.png' onclick="duplicateRow(this.parentElement.parentElement);" align='absbottom' width='27' height='24' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to duplicate the row'); ?>'>
                            <img src='../../pic/remove.png' onclick="deleteRow(this.parentElement.parentElement.id);" align='absbottom' width='24' height='22' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to delete the row'); ?>'>
                        </td>
                    <script language="javascript">
                        /* required for popup calendar */
                        Calendar.setup({inputField: "code_date_<?php echo $key + 1; ?>", ifFormat: "%Y-%m-%d", button: "img_code_date_<?php echo $key + 1; ?>"});
                    </script>
                </tr>
                <?php
            }
        } else {
            ?>
            <tr class="tb_row" id="tb_row_1">
                <td align="left" class="forms"><?php echo xlt('Code'); ?>:</td>
                <td class="forms">
                    <input type="text" id="code_1" name="code[]" style="width:50%;" class="code" value="<?php echo text($obj{"code"}); ?>" onclick='sel_code(this.parentElement.parentElement.id);'><br>
                    <span id="displaytext_1" style="width:210px !important;display: block;font-size:13px;color: blue;" class="displaytext"><?php echo text($obj{"description"}); ?></span>
                    <input type="hidden" id="description_1" name="description[]" class="description" value="<?php echo text($obj{"description"}); ?>">
                    <input type="hidden" id="code_type_1" name="code_type[]" class="code_type" value="<?php echo text($obj{"code_type"}); ?>">
                    <input type="hidden" id="table_code_1" name="table_code[]" class="table_code" value="<?php echo text($obj{"table_code"}); ?>">
                </td>
                <td id="ob_value_head_1" class="forms ob_value_head" align="left" <?php if( !$obj{"ob_value"}) {?>style="display: none;" <?php }?>><?php echo xlt('Value'); ?>:</td>
                <td class="forms"> 
                  <?php 
                      if((text($obj{"code"}) == '21612-7' || text($obj{"code"}) == '8661-1')) { 
                        $style = 'display: block;'; 
                      } 
                      elseif(text($obj{"code"}) == 'SS003' || !$obj{"ob_value"}) {
                        $style = 'display: none;'; 
                      }
                   ?>
                  <input type="text" name="ob_value[]" id="ob_value_1" style="width: 60%;<?php echo $style;?>" class="ob_value" value="<?php if((text($obj{"code"}) == '21612-7' || text($obj{"code"}) == '8661-1') && text($obj{"code"}) != 'SS003') echo text($obj{"ob_value"}); ?>">
                  <select name="ob_value_phin[]" id="ob_value_phin_1" class="ob_value_phin" <?php if(text($obj{"code"}) != 'SS003') { ?> style="display: none;" <?php }?>>
                    <option value="261QE0002X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QE0002X') echo 'selected = "selected"' ;?>><?php echo xlt('Emergency Care'); ?></option>
                    <option value="261QM2500X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QM2500X') echo 'selected = "selected"' ;?>><?php echo xlt('Medical Specialty'); ?></option>
                    <option value="261QP2300X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QP2300X') echo 'selected = "selected"' ;?>><?php echo xlt('Primary Care'); ?></option>
                    <option value="261QU0200X" <?php if(text($obj{"code"}) == 'SS003' && text($obj{"ob_value"}) == '261QU0200X') echo 'selected = "selected"' ;?>><?php echo xlt('Urgent Care'); ?></option>
                  </select>
                </td>
                <?php 
                    if(!$obj{"ob_unit"} || (text($obj{"code"}) == 'SS003')|| text($obj{"code"}) == '8661-1') {
                      $style = 'display: none;' ;
                    }
                    elseif(text($obj{"code"}) == '21612-7') {
                      $style = 'display: block';
                    }
                ?>
                <td id="ob_unit_head_1" class="forms ob_unit_head" align="left" style="<?php echo $style;?>"><?php echo xlt('Units'); ?>:</td>
                <td class="forms">  
                  <select <?php if(text($obj{"code"}) != '21612-7') { ?> style="display: none;" <?php }?> name="ob_unit[]" id="ob_unit_1" class="ob_unit">
                      <option value="d" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'd') echo 'selected = "selected"' ;?>><?php echo xlt('Day'); ?></option>
                      <option value="mo" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'mo') echo 'selected = "selected"' ;?>><?php echo xlt('Month'); ?></option>
                      <option value="UNK" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'UNK') echo 'selected = "selected"' ;?>><?php echo xlt('Unknown'); ?></option>
                      <option value="wk" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'wk') echo 'selected = "selected"' ;?>><?php echo xlt('Week'); ?></option>
                      <option value="a" <?php if(text($obj{"code"}) == '21612-7' && text($obj{"ob_unit"}) == 'a') echo 'selected = "selected"' ;?>><?php echo xlt('Year'); ?></option>
                  </select>
                </td>
                <td align="left" class="forms"><?php echo xlt('Comments'); ?>:</td>
                <td class="forms">
                    <textarea rows="4" id="comments_1" cols="20" name="comments[]" class="comments"><?php echo text($obj{"observation"}); ?></textarea>
                </td>
                <td align="left" class="forms"><?php echo xlt('Date'); ?>:</td>
                <td class="forms">
                    <input type='text' id="code_date_1" size='10' name='code_date[]' class="code_date" <?php echo attr($disabled) ?> value='<?php echo attr($obj{"date"}); ?>' title='<?php echo xla('yyyy-mm-dd Date of service'); ?>' onkeyup='datekeyup(this, mypcc)' onblur='dateblur(this, mypcc)' />
                    <img src='../../pic/show_calendar.gif' align='absbottom' id="img_code_date_<?php echo $key + 1; ?>" width='24' height='22' class="img_code_date" border='0' alt='[?]' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to choose a date'); ?>'>
                </td>
                <td>
                    <img src='../../pic/add.png' onclick="duplicateRow(this.parentElement.parentElement);" align='absbottom' width='27' height='24' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to duplicate the row'); ?>'>
                    <img src='../../pic/remove.png' onclick="deleteRow(this.parentElement.parentElement.id);" align='absbottom' width='24' height='22' border='0' style='cursor:pointer;cursor:hand' title='<?php echo xla('Click here to delete the row'); ?>'>
                </td>
            <script language="javascript">
                /* required for popup calendar */
                Calendar.setup({inputField: "code_date_1", ifFormat: "%Y-%m-%d", button: "img_code_date_1"});
            </script>
        </tr>
    <?php }
    ?>

    <tr>
        <td align="left" colspan="5" style="padding-bottom:7px;"></td>
    </tr>
    <tr>
        <td colspan="5"></td>
        <td colspan="4">
            <input type="hidden" id="clickId" value="">
            <input type='submit'  value='<?php echo xla('Save'); ?>' class="button-css">&nbsp;
        </td>
    </tr>
</table>
</form>    
<?php
formFooter();
?>



       