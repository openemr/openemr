<?php
/**
 * Common script for the encounter form (new and view) scripts for therapy groups.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Amiel Elboim <amielel@matrix.co.il>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2016 Shachar Zilbershlag <shaharzi@matrix.co.il>
 * @copyright Copyright (c) 2016 Amiel Elboim <amielel@matrix.co.il>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/classes/POSRef.class.php");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_groups_encounter WHERE id = ?", array($id));
    $encounter = $result['encounter'];
    if ($result['sensitivity'] && !acl_check('sensitivities', $result['sensitivity'])) {
        echo "<body>\n<html>\n";
        echo "<p>" . xlt('You are not authorized to see this encounter.') . "</p>\n";
        echo "</body>\n</html>\n";
        exit();
    }
}

// Sort comparison for sensitivities by their order attribute.
function sensitivity_compare($a, $b)
{
    return ($a[2] < $b[2]) ? -1 : 1;
}

/*// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = ? AND enddate IS NULL " .
  "ORDER BY type, begdate", array($pid));
*/?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>

<title><?php echo xlt('Therapy Group Encounter'); ?></title>

<?php Header::setupHeader(['common', 'datetime-picker']); ?>

<!-- validation library -->
<?php
//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">

/*
 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 800, 600);
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0]['issues[]'];
  s.options[s.options.length] = new Option(title, issue, true, true);
 }
*/

    <?php
 //Gets validation rules from Page Validation list.
 //Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
    $collectthis = collectValidationPageRules("/interface/forms/newGroupEncounter/common.php");
    if (empty($collectthis)) {
         $collectthis = "undefined";
    } else {
         $collectthis = $collectthis["new-encounter-form"]["rules"];
    }
    ?>
 var collectvalidation = <?php echo($collectthis); ?>;
 $(document).ready(function(){
   window.saveClicked = function(event) {
     var submit = submitme(1, event, 'new-encounter-form', collectvalidation);
     if (submit) {
       top.restoreSession();
       $('#new-encounter-form').submit();
     }
   }

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = false; ?>
    <?php $datetimepicker_showseconds = false; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });

 });

function bill_loc(){
var pid=<?php echo attr($pid);?>;
var dte=document.getElementById('form_date').value;
var facility=document.forms[0].facility_id.value;
ajax_bill_loc(pid,dte,facility);
}

// Handler for Cancel clicked when creating a new encounter.
// Show demographics or encounters list depending on what frame we're in.
function cancelClicked() {
 if (window.name == 'RBot') {
  parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
 }
 else {
  parent.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php');
 }
 return false;
}

</script>
</head>

<?php if ($viewmode) { ?>
<body class="body_top">
<?php } else { ?>
<body class="body_top" onload="javascript:document.new_encounter.reason.focus();">
<?php } ?>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newGroupEncounter/save.php" name='new_encounter'>

<div style='float:left'>
<?php if ($viewmode) { ?>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>'>
<span class=title><?php echo xlt('Group Encounter Form'); ?></span>
<?php } else { ?>
<input type='hidden' name='mode' value='new'>
<span class='title'><?php echo xlt('New Group Encounter Form'); ?></span>
<?php } ?>
</div>

<div>
    <div style = 'float:left; margin-left:8px;margin-top:-3px'>
      <a href="javascript:saveClicked(undefined);" class="css_button link_submit"><span><?php echo xlt('Save'); ?></span></a>
        <?php
        if ($viewmode || !isset($_GET["autoloaded"]) || $_GET["autoloaded"] != "1") { ?>
    </div>
    <div style = 'float:left; margin-top:-3px'>
      <a href="<?php echo $GLOBALS['form_exit_url']; ?>"
        class="css_button link_submit" onClick="top.restoreSession()"><span><?php echo xlt('Cancel'); ?></span></a>
        <?php } else { // not $viewmode ?>
      <a href="" class="css_button link_submit" onClick="return cancelClicked()">
      <span><?php echo xlt('Cancel'); ?></span></a>
        <?php } // end not $viewmode ?>
    </div>
 </div>

<br> <br>

<table width='96%'>

 <tr>
  <td width='33%' nowrap class='bold'><?php echo xlt('Consultation Brief Description'); ?>:</td>
  <td width='34%' rowspan='2' align='center' valign='center' class='text'>
   <table>

    <tr>
     <td class='bold' nowrap><?php echo xlt('Visit Category'); ?>:</td>
     <td class='text'>
      <select name='pc_catid' id='pc_catid'>
    <option value='_blank'>-- <?php echo xlt('Select One'); ?> --</option>
    <?php
    $cres = sqlStatement("SELECT pc_catid, pc_catname, pc_cattype " .
    "FROM openemr_postcalendar_categories where pc_active = 1 ORDER BY pc_seq ");
    while ($crow = sqlFetchArray($cres)) {
        $catid = $crow['pc_catid'];
        if ($crow['pc_cattype'] != 3) {
            continue;
        }

        echo "       <option value='" . attr($catid) . "'";
    // mark therapy group's category as selected
        if (!$viewmode && $crow['pc_cattype'] == 3) {
            echo " selected";
        }

        if ($viewmode && $crow['pc_catid'] == $result['pc_catid']) {
            echo " selected";
        }

        echo ">" . text(xl_appt_category($crow['pc_catname'])) . "</option>\n";
    }
    ?>
      </select>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php echo xlt('Facility'); ?>:</td>
     <td class='text'>
      <select name='facility_id' onChange="bill_loc()">
<?php

if ($viewmode) {
    $def_facility = $result['facility_id'];
} else {
    $dres = sqlStatement("select facility_id from users where username = ?", array($_SESSION['authUser']));
    $drow = sqlFetchArray($dres);
    $def_facility = $drow['facility_id'];
}

$facilities = $facilityService->getAllServiceLocations();
if ($facilities) {
    foreach ($facilities as $iter) {
    ?>
       <option value="<?php echo attr($iter['id']); ?>" <?php echo ($def_facility == $iter['id']) ? "selected" : ""; ?>><?php echo text($iter['name']); ?></option>
<?php
    }
}
?>
      </select>
     </td>
    </tr>
    <tr>
        <td class='bold' nowrap><?php echo xlt('Billing Facility'); ?>:</td>
        <td class='text'>
            <div id="ajaxdiv">
            <?php
            billing_facility('billing_facility', $result['billing_facility']);
            ?>
            </div>
        </td>
     </tr>
        <?php if ($GLOBALS['set_pos_code_encounter']) { ?>
        <tr>
            <td><span class='bold' nowrap><?php echo xlt('POS Code'); ?>: </span></td>
            <td colspan="6">
                <select name="pos_code">
                <?php

                $pc = new POSRef();

                foreach ($pc->get_pos_ref() as $pos) {
                    echo "<option value=\"" . attr($pos["code"]) . "\" ";
                    if ($pos["code"] == $result['pos_code']) {
                        echo "selected";
                    }

                    echo ">" . text($pos['code'])  . ": ". xlt($pos['title']);
                    echo "</option>\n";
                }

                ?>
                </select>
            </td>
       </tr>
        <?php } ?>
    <tr>
<?php
 $sensitivities = acl_get_sensitivities();
if ($sensitivities && count($sensitivities)) {
    usort($sensitivities, "sensitivity_compare");
?>
   <td class='bold' nowrap><?php echo xlt('Sensitivity:'); ?></td>
    <td class='text'>
     <select name='form_sensitivity'>
<?php
foreach ($sensitivities as $value) {
   // Omit sensitivities to which this user does not have access.
    if (acl_check('sensitivities', $value[1])) {
        echo "       <option value='" . attr($value[1]) . "'";
        if ($viewmode && $result['sensitivity'] == $value[1]) {
            echo " selected";
        }

        echo ">" . xlt($value[3]) . "</option>\n";
    }
}

echo "       <option value=''";
if ($viewmode && !$result['sensitivity']) {
    echo " selected";
}

echo ">" . xlt('None'). "</option>\n";
?>
     </select>
    </td>
<?php
} else {
?>
    <td colspan='2'><!-- sensitivities not used --></td>
<?php
}
?>
    </tr>

     <tr<?php echo (!$GLOBALS['gbl_visit_referral_source']) ? " style='visibility:hidden;'" : ""; ?>>
     <td class='bold' nowrap><?php echo xlt('Referral Source'); ?>:</td>
     <td class='text'>
<?php
  echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');
?>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php echo xlt('Date of Service'); ?>:</td>
     <td class='text' nowrap>
      <input type='text' size='10' class='datepicker' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : oeFormatShortDate(date('Y-m-d')); ?>'
       title='<?php echo xla('Date of service'); ?>' />
     </td>
    </tr>

    <tr<?php echo ($GLOBALS['ippf_specific']) ? " style='visibility:hidden;'" : ""; ?>>
     <td class='bold' nowrap><?php echo xlt('Additional Date:'); ?></td>
     <td class='text' nowrap><!-- default is blank so that while generating claim the date is blank. -->
      <input type='text' size='10' class='datepicker' name='form_onset_date' id='form_onset_date'
       value='<?php echo $viewmode && $result['onset_date']!='0000-00-00 00:00:00' ? attr(oeFormatShortDate(substr($result['onset_date'], 0, 10))) : ''; ?>'
       title='<?php echo xla('Date of onset or hospitalization'); ?>' />
     </td>
    </tr>
    <tr>
     <td class='text' colspan='2' style='padding-top:1em'>
     </td>
    </tr>
   </table>

  </td>
 </tr>

 <tr>
  <td class='text' valign='top'>
   <textarea name='reason' cols='40' rows='12' wrap='virtual' style='width:96%'
    ><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
  </td>
 </tr>

</table>

</form>

</body>

<script language="javascript">
<?php
if (!$viewmode) { ?>
 function duplicateVisit(enc, datestr) {
    if (!confirm('<?php echo xls("A visit already exists for this group today. Click Cancel to open it, or OK to proceed with creating a new one.") ?>')) {
            // User pressed the cancel button, so re-direct to today's encounter
            top.restoreSession();
            parent.left_nav.setEncounter(datestr, enc, window.name);
            parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + enc);
            return;
        }
        // otherwise just continue normally
    }
<?php

  // Search for an encounter from today
  $erow = sqlQuery("SELECT fe.encounter, fe.date " .
    "FROM form_groups_encounter AS fe, forms AS f WHERE " .
    "fe.group_id = ? " .
    " AND fe.date >= ? " .
    " AND fe.date <= ? " .
    " AND " .
    "f.formdir = 'newGroupEncounter' AND f.form_id = fe.id AND f.deleted = 0 " .
    "ORDER BY fe.encounter DESC LIMIT 1", array($therapy_group,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')));

if (!empty($erow['encounter'])) {
    // If there is an encounter from today then present the duplicate visit dialog
    echo "duplicateVisit('" . $erow['encounter'] . "', '" .
        text(oeFormatShortDate(substr($erow['date'], 0, 10))) . "');\n";
}
}
?>
</script>

</html>
