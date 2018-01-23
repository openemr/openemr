<?php
/**
 *
 * Patient history form.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */


use OpenEMR\Core\Header;

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/options.js.php");
require_once("$srcdir/validation/LBF_Validation.php");

$CPR = 4; // cells per row

// Check authorization.
if (acl_check('patients', 'med')) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! acl_check('squads', $tmp['squad'])) {
        die(htmlspecialchars(xl("Not authorized for this squad."), ENT_NOQUOTES));
    }
}

if (!acl_check('patients', 'med', '', array('write','addonly'))) {
    die(htmlspecialchars(xl("Not authorized"), ENT_NOQUOTES));
}
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'common']); ?>
<title><?php xl("History & Lifestyle", 'e');?></title>
<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script LANGUAGE="JavaScript">
 //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
 var code_options_js = Array();

    <?php
    $smoke_codes = getSmokeCodes();

    foreach ($smoke_codes as $val => $code) {
            echo "code_options_js"."['" . attr($val) . "']='" . attr($code) . "';\n";
    }
    ?>

var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

function divclick(cb, divid) {
 var divstyle = document.getElementById(divid).style;
 if (cb.checked) {
  divstyle.display = 'block';
 } else {
  divstyle.display = 'none';
 }
 return true;
}

// Compute the length of a string without leading and trailing spaces.
function trimlen(s) {
 var i = 0;
 var j = s.length - 1;
 for (; i <= j && s.charAt(i) == ' '; ++i);
 for (; i <= j && s.charAt(j) == ' '; --j);
 if (i > j) return 0;
 return j + 1 - i;
}

function validate(f) {
<?php generate_layout_validation('HIS'); ?>
 return true;
}



function submit_history() {
    top.restoreSession();
    document.forms[0].submit();
}

//function for selecting the smoking status in radio button based on the selection of drop down list.
function radioChange(rbutton)
{
    if (rbutton == 1 || rbutton == 2 || rbutton == 15 || rbutton == 16)
     {
     document.getElementById('radio_tobacco[current]').checked = true;
     }
     else if (rbutton == 3)
     {
     document.getElementById('radio_tobacco[quit]').checked = true;
     }
     else if (rbutton == 4)
     {
     document.getElementById('radio_tobacco[never]').checked = true;
     }
     else if (rbutton == 5 || rbutton == 9)
     {
     document.getElementById('radio_tobacco[not_applicable]').checked = true;
     }
     else if (rbutton == '')
     {
     var radList = document.getElementsByName('radio_tobacco');
     for (var i = 0; i < radList.length; i++) {
     if(radList[i].checked) radList[i].checked = false;
     }
     }
     //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
     if(rbutton!=""){
         if(code_options_js[rbutton]!="")
            $("#smoke_code").html(" ( "+code_options_js[rbutton]+" )");
         else
             $("#smoke_code").html("");
     }
     else
        $("#smoke_code").html("");
}

//function for selecting the smoking status in drop down list based on the selection in radio button.
function smoking_statusClicked(cb)
{
     if (cb.value == 'currenttobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 1;
     }
     else if (cb.value == 'nevertobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 4;
     }
     else if (cb.value == 'quittobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 3;
     }
     else if (cb.value == 'not_applicabletobacco')
     {
     document.getElementById('form_tobacco').selectedIndex = 6;
     }
     radioChange(document.getElementById('form_tobacco').value);
}

// The ID of the input element to receive a found code.
var current_sel_name = '';

// This is for callback by the find-code popup.
// Appends to or erases the current list of related codes.
function set_related(codetype, code, selector, codedesc) {
 var frc = document.forms[0][current_sel_name];
 var s = frc.value;
 if (code) {
  if (s.length > 0) s += ';';
  s += codetype + ':' + code;
 } else {
  s = '';
 }
 frc.value = s;
}

// This invokes the find-code popup.
function sel_related(e) {
 current_sel_name = e.name;
 dlgopen('../encounter/find_code_popup.php<?php if ($GLOBALS['ippf_specific']) {
        echo '?codetype=REF';
} ?>', '_blank', 500, 400);
}

</script>

<script type="text/javascript">
/// todo, move this to a common library
$(document).ready(function(){
    if($("#form_tobacco").val()!=""){
        if(code_options_js[$("#form_tobacco").val()]!=""){
            $("#smoke_code").html(" ( "+code_options_js[$("#form_tobacco").val()]+" )");
        }
    }
    tabbify();

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });
    $('.datetimepicker').datetimepicker({
        <?php $datetimepicker_timepicker = true; ?>
        <?php $datetimepicker_showseconds = false; ?>
        <?php $datetimepicker_formatInput = true; ?>
        <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
        <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
    });

    if (window.checkSkipConditions) {
        checkSkipConditions();
    }
});
</script>

<style type="text/css">
.form-control {
    width: auto;
    display: inline;
    height: auto;
}
div.tab {
    height: auto;
    width: auto;
}
</style>

</head>
<body class="body_top">

<div class="container">
    <div class="row">
        <div class="col-xs-12">
            <?php
            $result = getHistoryData($pid);
            if (!is_array($result)) {
                newHistoryData($pid);
                $result = getHistoryData($pid);
            }
            $condition_str = '';

            /*Get the constraint from the DB-> LBF forms accordinf the form_id*/
            $constraints = LBF_Validation::generate_validate_constraints("HIS");
            ?>
            <script> var constraints = <?php echo $constraints;?>; </script>

            <form action="history_save.php" id="HIS" name='history_form' method='post' onsubmit="submitme(<?php echo $GLOBALS['new_validate'] ? 1 : 0;?>,event,'HIS',constraints)">
                <input type='hidden' name='mode' value='save'>

                <div class="page-header">
                    <h2><?php echo htmlspecialchars(getPatientName($pid), ENT_NOQUOTES);?>&nbsp;<small><?php echo htmlspecialchars(xl('History & Lifestyle'), ENT_NOQUOTES); ?></h2>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-default btn-save"><?php echo xlt('Save'); ?></button>
                    <a href="history.php" class="btn btn-link btn-cancel" onclick="top.restoreSession()">
                        <?php echo xlt('Cancel'); ?>
                    </a>
                </div>

                <br/>

                <!-- history tabs -->
                <div id="HIS" style='float:none; margin-top: 10px; margin-right:20px'>
                    <ul class="tabNav" >
                        <?php display_layout_tabs('HIS', $result, $result2); ?>
                    </ul>

                    <div class="tabContainer">
                        <?php display_layout_tabs_data_editable('HIS', $result, $result2); ?>
                    </div>
                </div>
            </form>

            <!-- include support for the list-add selectbox feature -->
            <?php include $GLOBALS['fileroot']."/library/options_listadd.inc"; ?>
        </div>
    </div>
</div>
</body>

<script language="JavaScript">

// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];

<?php echo $date_init; // setup for popup calendars ?>

</script>

<script language='JavaScript'>
    // Array of skip conditions for the checkSkipConditions() function.
    var skipArray = [
        <?php echo $condition_str; ?>
    ];
    checkSkipConditions();
    $("input").change(function() {
        checkSkipConditions();
    });
    $("select").change(function() {
        checkSkipConditions();
    });
</script>

<?php /*Include the validation script and rules for this form*/
$form_id="HIS";
//LBF forms use the new validation depending on the global value
$use_validate_js=$GLOBALS['new_validate'];

?><?php include_once("$srcdir/validation/validation_script.js.php");?>

</html>
