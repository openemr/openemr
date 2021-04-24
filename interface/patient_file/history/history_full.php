<?php

/**
 * Patient history form.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../../globals.php");
require_once("$srcdir/patient.inc");
require_once("history.inc.php");
require_once("$srcdir/options.inc.php");
require_once("$srcdir/options.js.php");
require_once("$srcdir/validation/LBF_Validation.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

$CPR = 4; // cells per row

// Check authorization.
if (AclMain::aclCheckCore('patients', 'med')) {
    $tmp = getPatientData($pid, "squad");
    if ($tmp['squad'] && ! AclMain::aclCheckCore('squads', $tmp['squad'])) {
        die(xlt("Not authorized for this squad."));
    }
}

if (!AclMain::aclCheckCore('patients', 'med', '', array('write','addonly'))) {
    die(xlt("Not authorized"));
}
?>
<html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'common', 'select2']); ?>
<title><?php echo xlt("History & Lifestyle");?></title>
<?php include_once("{$GLOBALS['srcdir']}/options.js.php"); ?>

<script>
 //Added on 5-jun-2k14 (regarding 'Smoking Status - display SNOMED code description')
 var code_options_js = Array();

    <?php
    $smoke_codes = getSmokeCodes();

    foreach ($smoke_codes as $val => $code) {
            echo "code_options_js" . "[" . js_escape($val) . "]=" . js_escape($code) . ";\n";
    }
    ?>

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
 dlgopen('../encounter/find_code_popup.php<?php echo ($GLOBALS['ippf_specific']) ? '?codetype=REF' : ''?>', '_blank', 500, 400);
}

</script>

<script>
/// todo, move this to a common library
$(function () {
    if($("#form_tobacco").val()!=""){
        if(code_options_js[$("#form_tobacco").val()]!=""){
            $("#smoke_code").html(" ( "+code_options_js[$("#form_tobacco").val()]+" )");
        }
    }
    tabbify();

    $(".select-dropdown").select2({
        theme: "bootstrap4",
        <?php require($GLOBALS['srcdir'] . '/js/xl/select2.js.php'); ?>
    });
    if (typeof error !== 'undefined') {
        if (error) {
            alertMsg(error);
        }
    }

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

<style>
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
<?php
$arrOeUiSettings = array(
    'heading_title' => xl('Edit History and Lifestyle'),
    'include_patient_name' => true,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "back",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "history.php",//only for actions - reset, link or back
    'show_help_icon' => false,
    'help_file_name' => ""
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body>

<div id="container_div" class="<?php echo $oemr_ui->oeContainer();?> mt-3">
    <div class="row">
        <div class="col-12">
            <?php require_once("$include_root/patient_file/summary/dashboard_header.php"); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
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
                <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                <input type='hidden' name='mode' value='save' />

                <div class="btn-group">
                    <button type="submit" class="btn btn-primary btn-save"><?php echo xlt('Save'); ?></button>
                    <a href="history.php" class="btn btn-secondary btn-cancel" onclick="top.restoreSession()">
                        <?php echo xlt('Cancel'); ?>
                    </a>
                </div>

                <br/>

                <!-- history tabs -->
                <div id="HIS" class="float-none mt-3">
                    <ul class="tabNav" >
                        <?php display_layout_tabs('HIS', $result, ($result2 ?? '')); ?>
                    </ul>

                    <div class="tabContainer">
                        <?php display_layout_tabs_data_editable('HIS', $result, ($result2 ?? '')); ?>
                    </div>
                </div>
            </form>

            <!-- include support for the list-add selectbox feature -->
            <?php include $GLOBALS['fileroot'] . "/library/options_listadd.inc"; ?>
        </div>
    </div>
</div><!--end of container div-->
<?php $oemr_ui->oeBelowContainerDiv();?>
</body>

<script>

// Array of skip conditions for the checkSkipConditions() function.
var skipArray = [
<?php echo $condition_str; ?>
];

<?php echo $date_init; // setup for popup calendars ?>

</script>

<script>
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
$form_id = "HIS";
//LBF forms use the new validation depending on the global value
$use_validate_js = $GLOBALS['new_validate'];

?><?php include_once("$srcdir/validation/validation_script.js.php");?>

</html>
