<?php
/**
 * Common script for the encounter form (new and view) scripts.
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
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

require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear-1, $thisyear, $thisyear+1, $thisyear+2);

if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_encounter WHERE id = ?", array($id));
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

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
  "pid = ? AND enddate IS NULL " .
  "ORDER BY type, begdate", array($pid));
?>
<!DOCTYPE html>
<html>
<head>

<title><?php echo xlt('Patient Encounter'); ?></title>
    <?php Header::setupHeader(['jquery-ui', 'datetime-picker']); ?>

<!-- validation library -->
<?php
//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 // Process click on issue title.
 function newissue() {
  dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 700, 535, '', '', {
      buttons: [
          {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
      ]
  });
  return false;
 }

 // callback from add_edit_issue.php:
 function refreshIssue(issue, title) {
  var s = document.forms[0]['issues[]'];
  s.options[s.options.length] = new Option(title, issue, true, true);
 }

    <?php
 //Gets validation rules from Page Validation list.
 //Note that for technical reasons, we are bypassing the standard validateUsingPageRules() call.
    $collectthis = collectValidationPageRules("/interface/forms/newpatient/common.php");
    if (empty($collectthis)) {
         $collectthis = "undefined";
    } else {
         $collectthis = $collectthis["new_encounter"]["rules"];
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
   $(".enc_issue").on('click', function(e) {
       e.preventDefault();e.stopPropagation();
       dlgopen('', '', 700, 650, '', '', {
           buttons: [
               {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
           ],
           allowResize: true,
           allowDrag: true,
           dialogId: '',
           type: 'iframe',
           url: $(this).attr('href')
       });
   });
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
 function cancelClickedNew() {
     if (top.tab_mode) {
         window.parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
     }
     var target = window;
     while (target != top) {
         if (target.name == 'RBot') {
             target.parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
             break;
         }
         else if (target.name == 'RTop') {
             target.parent.left_nav.loadFrame('dem1', window.name, 'patient_file/summary/demographics.php');
             break;
         }
         target = target.parent;
     }
     return false;
 }

 // Handler for cancel clicked when not creating a new encounter.
 // Just reload the view mode.
 function cancelClickedOld() {
     location.href = '<?php echo "$rootdir/patient_file/encounter/forms.php"; ?>';
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

<form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'>

<div style='float:left'>
<?php if ($viewmode) { ?>
<input type=hidden name='mode' value='update'>
<input type=hidden name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>'>
<span class=title><?php echo xlt('Patient Encounter Form'); ?></span>
<?php } else { ?>
<input type='hidden' name='mode' value='new'>
<span class='title'><?php echo xlt('New Encounter Form'); ?></span>
<?php } ?>
</div>

<div>
    <div style = 'float:left; margin-left:8px;margin-top:-3px'>
      <a href="javascript:saveClicked(undefined);" class="css_button link_submit"><span><?php echo xlt('Save'); ?></span></a>
<?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
    </div>
    <div style = 'float:left; margin-top:-3px'>
      <a href="" class="css_button link_submit" onClick="return cancelClickedOld()">
      <span><?php echo xlt('Cancel'); ?></span></a>
    <?php } else { // not $viewmode ?>
      <a href="" class="css_button link_submit" onClick="return cancelClickedNew()">
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
     <td class='bold' nowrap><?php echo xlt('Visit Category:'); ?></td>
     <td class='text'>
      <select class="form-control" name='pc_catid' id='pc_catid'>
          <option value='_blank'>-- <?php echo xlt('Select One'); ?> --</option>
            <?php
            //Bring only patient ang group categories
            $visitSQL = "SELECT pc_catid, pc_catname, pc_cattype 
                       FROM openemr_postcalendar_categories
                       WHERE pc_active = 1 and pc_cattype IN (0,3) and pc_constant_id  != 'no_show' ORDER BY pc_seq";

            $visitResult = sqlStatement($visitSQL);
            $therapyGroupCategories = [];

            while ($row = sqlFetchArray($visitResult)) {
                $catId = $row['pc_catid'];
                $name = $row['pc_catname'];

                if ($row['pc_cattype'] == 3) {
                    $therapyGroupCategories[] = $catId;
                }

                if ($catId === "_blank") {
                    continue;
                }

                if ($row['pc_cattype'] == 3 && !$GLOBALS['enable_group_therapy']) {
                    continue;
                }

                // Fetch acl for category of given encounter. Only if has write auth for a category, then can create an encounter of that category.
                $postCalendarCategoryACO = fetchPostCalendarCategoryACO($catId);
                if ($postCalendarCategoryACO) {
                    $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                    $authPostCalendarCategoryWrite = acl_check($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
                } else { // if no aco is set for category
                    $authPostCalendarCategoryWrite = true;
                }

                //if no permission for category write, don't show in drop-down
                if (!$authPostCalendarCategoryWrite) {
                    continue;
                }

                $optionStr = '<option value="%pc_catid%" %selected%>%pc_catname%</option>';
                $optionStr = str_replace("%pc_catid%", attr($catId), $optionStr);
                $optionStr = str_replace("%pc_catname%", text(xl_appt_category($name)), $optionStr);
                if ($viewmode) {
                    $selected = ($result['pc_catid'] == $catId) ? " selected" : "";
                } else {
                    $selected = ($GLOBALS['default_visit_category'] == $catId) ? " selected" : "";
                }

                  $optionStr = str_replace("%selected%", $selected, $optionStr);
                  echo $optionStr;
            }
            ?>
      </select>
     </td>
    </tr>

    <tr>
     <td class='bold' nowrap><?php echo xlt('Facility:'); ?></td>
     <td class='text'>
      <select class="form-control" name='facility_id' onChange="bill_loc()">
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
        if ($iter['billing_location'] == 1) {
            $posCode = $iter['pos_code'];
        }
    ?>
       <option value="<?php echo attr($iter['id']); ?>" <?php if ($def_facility == $iter['id']) {
            echo "selected";
}?>><?php echo text($iter['name']); ?></option>
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
                <select class="form-control" name="pos_code">
                <?php

                $pc = new POSRef();

                foreach ($pc->get_pos_ref() as $pos) {
                    echo "<option value=\"" . attr($pos["code"]) . "\" ";
                    if ($pos["code"] == $result['pos_code'] || $pos["code"] == $posCode) {
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
     <select class="form-control" name='form_sensitivity'>
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

    <tr<?php if (!$GLOBALS['gbl_visit_referral_source']) {
        echo " style='visibility:hidden;'";
} ?>>
     <td class='bold' nowrap><?php echo xlt('Referral Source'); ?>:</td>
     <td class='text'>
<?php
  echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');
?>
     </td>
    </tr>

    <?php if ($GLOBALS['enable_group_therapy']) { ?>
        <!-- select group name - showing just if therapy group type is selected -->
    <tr id="therapy_group_name" style="display: none">
        <td class='bold' nowrap><?php echo xlt('Group name'); ?>:</td>
        <td>
            <input type='text' class="input-sm" size='10' name='form_group' id="form_group" style='width:100%;cursor:pointer;cursor:hand' placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
            <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
        </td>
    </tr>

    <?php }?>
    <tr>
     <td class='bold' nowrap><?php echo xlt('Date of Service:'); ?></td>
     <td class='text' nowrap>
      <input type='text' size='10' class='datepicker input-sm' name='form_date' id='form_date' <?php echo $disabled ?>
       value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : oeFormatShortDate(date('Y-m-d')); ?>'
       title='<?php echo xla('Date of service'); ?>' />
     </td>
    </tr>

    <tr<?php if ($GLOBALS['ippf_specific']) {
        echo " style='visibility:hidden;'";
} ?>>
     <td class='bold' nowrap><?php echo xlt('Onset/hosp. date:'); ?></td>
     <td class='text' nowrap><!-- default is blank so that while generating claim the date is blank. -->
      <input type='text' size='10' class='datepicker input-sm' name='form_onset_date' id='form_onset_date'
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


  <td class='bold' width='33%' nowrap>

<?php
  // To see issues stuff user needs write access to all issue types.
  $issuesauth = true;
foreach ($ISSUE_TYPES as $type => $dummy) {
    if (!acl_check_issue($type, '', 'write')) {
        $issuesauth = false;
        break;
    }
}

if ($issuesauth) {
?>
  <div style='float:left'>
<?php echo xlt('Issues (Injuries/Medical/Allergy)'); ?>
  </div>
  <div style='float:left;margin-left:8px;margin-top:-3px'>
    <?php if (acl_check('patients', 'med', '', 'write')) { ?>
       <a href="../../patient_file/summary/add_edit_issue.php" class="css_button_small link_submit enc_issue"
        onclick="top.restoreSession()"><span><?php echo xlt('Add'); ?></span></a>
        <?php } ?>
  </div>
<?php } ?>

  </td>
 </tr>

 <tr>
  <td class='text' valign='top'>
   <textarea name='reason' cols='40' rows='12' wrap='virtual' style='width:96%'
    ><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
  </td>
  <td class='text' valign='top'>

<?php if ($issuesauth) { ?>
   <select class="form-control" multiple name='issues[]' size='8' style='width:100%'
    title='<?php echo xla('Hold down [Ctrl] for multiple selections or to unselect'); ?>'>
<?php
while ($irow = sqlFetchArray($ires)) {
    $list_id = $irow['id'];
    $tcode = $irow['type'];
    if ($ISSUE_TYPES[$tcode]) {
        $tcode = $ISSUE_TYPES[$tcode][2];
    }

    echo "    <option value='" . attr($list_id) . "'";
    if ($viewmode) {
        $perow = sqlQuery("SELECT count(*) AS count FROM issue_encounter WHERE " .
        "pid = ? AND encounter = ? AND list_id = ?", array($pid,$encounter,$list_id));
        if ($perow['count']) {
            echo " selected";
        }
    } else {
        // For new encounters the invoker may pass an issue ID.
        if (!empty($_REQUEST['issue']) && $_REQUEST['issue'] == $list_id) {
            echo " selected";
        }
    }

    echo ">" . text($tcode) . ": " . text($irow['begdate']) . " " .
    text(substr($irow['title'], 0, 40)) . "</option>\n";
}
?>
   </select>
   <p><i><?php echo xlt('To link this encounter/consult to an existing issue, click the '
    . 'desired issue above to highlight it and then click [Save]. '
    . 'Hold down [Ctrl] button to select multiple issues.'); ?></i></p>
<?php } ?>

  </td>
 </tr>

</table>

</form>

</body>

<script language="javascript">
<?php
if (!$viewmode) { ?>
 function duplicateVisit(enc, datestr) {
    if (!confirm('<?php echo xls("A visit already exists for this patient today. Click Cancel to open it, or OK to proceed with creating a new one.") ?>')) {
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
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? " .
    " AND fe.date >= ? " .
    " AND fe.date <= ? " .
    " AND " .
    "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
    "ORDER BY fe.encounter DESC LIMIT 1", array($pid,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')));

if (!empty($erow['encounter'])) {
    // If there is an encounter from today then present the duplicate visit dialog
    echo "duplicateVisit('" . $erow['encounter'] . "', '" .
        attr(oeFormatShortDate(substr($erow['date'], 0, 10))) . "');\n";
}
}
?>

<?php if ($GLOBALS['enable_group_therapy']) { ?>
/* hide / show group name input */
  var groupCategories = <?php echo json_encode($therapyGroupCategories); ?>;
  $('#pc_catid').on('change', function () {
      if(groupCategories.indexOf($(this).val()) > -1){
          $('#therapy_group_name').show();
      } else {
          $('#therapy_group_name').hide();
      }
  })

  function sel_group() {
      top.restoreSession();
      var url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_group_popup.php';
      dlgopen(url, '_blank', 500, 400, '', '', {
          buttons: [
              {text: '<?php echo xla('Close'); ?>', close: true, style: 'default btn-sm'}
          ]
      });
  }
  // This is for callback by the find-group popup.
  function setgroup(gid, name) {
     var f = document.forms[0];
     f.form_group.value = name;
     f.form_gid.value = gid;
  }

    <?php if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
    $('#therapy_group_name').show();
    <?php } ?>
<?php } ?>
</script>

</html>
