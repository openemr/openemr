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
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$srcdir/options.inc.php");
require_once("$srcdir/api.inc");
require_once("$srcdir/group.inc");
require_once("$srcdir/classes/POSRef.class.php");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

$months = array("01","02","03","04","05","06","07","08","09","10","11","12");
$days = array("01","02","03","04","05","06","07","08","09","10","11","12","13","14",
  "15","16","17","18","19","20","21","22","23","24","25","26","27","28","29","30","31");
$thisyear = date("Y");
$years = array($thisyear - 1, $thisyear, $thisyear + 1, $thisyear + 2);

if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_groups_encounter WHERE id = ?", array($id));
    $encounter = $result['encounter'];
    if ($result['sensitivity'] && !AclMain::aclCheckCore('sensitivities', $result['sensitivity'])) {
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
<!DOCTYPE html>
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
<script>

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
         $collectthis = json_sanitize($collectthis["new-encounter-form"]["rules"]);
    }
    ?>
 var collectvalidation = <?php echo $collectthis; ?>;
 $(function () {
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
var pid=<?php echo js_escape($pid);?>;
var dte=document.getElementById('form_date').value;
var facility=document.forms[0].facility_id.value;
ajax_bill_loc(pid,dte,facility);
}

// Handler for Cancel clicked when creating a new encounter.
// Show demographics or encounters list depending on what frame we're in.
function cancelClickedNew() {
    window.parent.left_nav.loadFrame('ens1', window.name, 'patient_file/history/encounters.php');
    return false;
}

// Handler for cancel clicked when not creating a new encounter.
// Just reload the view mode.
function cancelClickedOld() {
    location.href = '<?php echo "$rootdir/patient_file/encounter/forms.php"; ?>';
    return false;
}

</script>
<style>
@media only screen and (max-width: 1024px) {
    #visit-details [class*="col-"], #visit-issues [class*="col-"] {
      width: 100%;
      text-align: <?php echo ($_SESSION['language_direction'] == 'rtl') ? 'right ' : 'left '?> !important;
    }
}
</style>
<?php
if ($viewmode) {
    $body_javascript = '';
    $heading_caption = xl('Group Encounter Form');
} else {
    $body_javascript = 'onload="javascript:document.new_encounter.reason.focus();"';
    $heading_caption = xl('New Group Encounter Form');
}

$help_icon = '';

?>
</head>

<body class="body_top" <?php echo $body_javascript;?>>
<div class="container">
            <!-- Required for the popup date selectors -->
            <div id="overDiv" class="position-absolute" style="visibility: hidden; z-index: 1000;"></div>
            <div>
                <h2><?php echo text($heading_caption); ?><?php echo $help_icon; ?></h2>
            </div>
            <form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newGroupEncounter/save.php" name='new_encounter'>
                <?php if ($viewmode) { ?>
                    <input type="hidden" name='mode' value='update' />
                    <input type="hidden" name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>' />
                <?php } else { ?>
                    <input type='hidden' name='mode' value='new' />
                <?php } ?>
                <fieldset>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <legend><?php echo xlt('Visit Details')?></legend>
                    <div id="visit-details">
                      <div class="row p-3">
                        <div class="col-md-6 form-group row">
                            <label for="pc_catid" class="col-form-label col-sm-2"><?php echo xlt('Visit Category'); ?>:</label>
                            <div class="col-sm-3">
                                <select name='pc_catid' id='pc_catid' class='form-control'>
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
                            </div>
                            <?php
                            $sensitivities = AclExtended::aclGetSensitivities();
                            if ($sensitivities && count($sensitivities)) {
                                usort($sensitivities, "sensitivity_compare");
                                ?>
                            <label for="pc_catid" class="col-form-label col-sm-2"><?php echo xlt('Sensitivity'); ?>:</label>
                            <div class="col-sm-3">
                                <select name='form_sensitivity' id='form_sensitivity' class='form-control'>
                                    <?php
                                    foreach ($sensitivities as $value) {
                                        // Omit sensitivities to which this user does not have access.
                                        if (AclMain::aclCheckCore('sensitivities', $value[1])) {
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

                                    echo ">" . xlt('None{{Sensitivity}}') . "</option>\n";
                                    ?>
                                </select>
                                <?php
                            } else {
                                ?>

                                    <?php
                            }
                            ?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-6 form-group row">
                            <label for='form_date' class="col-form-label col-sm-2"><?php echo xlt('Date of Service'); ?>:</label>
                            <div class="col-sm-3">
                                <input type='text' class='form-control datepicker' name='form_date' id='form_date' <?php echo $disabled ?>
                                       value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : attr(oeFormatShortDate(date('Y-m-d'))); ?>'
                                       title='<?php echo xla('Date of service'); ?>'/>
                            </div>

                            <?php if ($GLOBALS['ippf_specific']) {
                                echo "<div class='invisible'>"; } ?>
                                <label for='form_onset_date' class="col-form-label col-sm-2"><?php echo xlt('Onset/hosp. date'); ?>:</label>
                                <div class="col-sm-3">
                                    <input type='text' class='form-control datepicker' name='form_onset_date' id='form_onset_date'
                                           value='<?php echo $viewmode && $result['onset_date'] != '0000-00-00 00:00:00' ? attr(oeFormatShortDate(substr($result['onset_date'], 0, 10))) : ''; ?>'
                                           title='<?php echo xla('Date of onset or hospitalization'); ?>' />
                                </div>
                            <?php if ($GLOBALS['ippf_specific']) {
                                echo "</div>"; } ?>
                            <div class="clearfix"></div>
                        </div>
                        <div class="col-md-6 form-group row"
                            <?php
                            if (!$GLOBALS['gbl_visit_referral_source']) {
                                echo "style='display:none'";
                            } ?>>">
                            <label  class="col-form-label col-sm-2"><?php echo xlt('Referral Source'); ?>:</label>
                            <div class="col-sm-3">
                                <?php echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');?>
                            </div>
                            <div class="clearfix"></div>
                        </div>
                        <?php if ($GLOBALS['enable_group_therapy']) { ?>
                            <div class="col-md-6 form-group row" id="therapy_group_name" style="display: none">
                                <label for="form_group" class="col-form-label col-sm-2"><?php echo xlt('Group name'); ?>:</label>
                                <div class="col-sm-3">
                                    <input type='text'name='form_group' class='form-control' id="form_group" placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
                                    <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }?>
                        <?php if ($GLOBALS['set_pos_code_encounter']) { ?>
                            <div class="col-md-6 form-group row">
                                <label for='facility_id' class="col-form-label col-sm-2"><?php echo xlt('POS Code'); ?>:</label>
                                <div class="col-sm-8">
                                    <select name="pos_code" id="pos_code" class='form-control'>
                                        <?php
                                        $pc = new POSRef();
                                        foreach ($pc->get_pos_ref() as $pos) {
                                            echo "<option value=\"" . attr($pos["code"]) . "\" ";
                                            if ($pos["code"] == $result['pos_code'] || $pos["code"] == $posCode) {
                                                echo "selected";
                                            }
                                            echo ">" . text($pos['code'])  . ": " . xlt($pos['title']);
                                            echo "</option>\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }?>
                        <div class="col-md-6 form-group row">
                            <label for='facility_id' class="col-form-label col-sm-2"><?php echo xlt('Facility'); ?>:</label>
                            <div class="col-sm-8">
                                <select name='facility_id' id='facility_id' class='form-control' onChange="bill_loc()">
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
                                            <option value="<?php echo attr($iter['id']); ?>"
                                                <?php
                                                if ($def_facility == $iter['id']) {
                                                    echo "selected";
                                                }?>><?php echo text($iter['name']); ?>
                                            </option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                      </div>
                    </div>
                </fieldset>
                <fieldset>
                    <div class="col-md-12 form-group">
                      <legend><?php echo xlt('Reason for Visit')?></legend>
                      <textarea name="reason" id="reason" class="form-control" cols="80" rows="4"><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
                    </div>
                </fieldset>
                <div class="col-md-12 form-group clearfix">
                      <button type="button" class="btn btn-secondary btn-save" onclick="top.restoreSession(); saveClicked(undefined);"><?php echo xlt('Save');?></button>
                      <?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
                          <button type="button" class="btn btn-link btn-cancel" onClick="return cancelClickedOld()"><?php echo xlt('Cancel');?></button>
                      <?php } else { // not $viewmode ?>
                          <button class="btn btn-link btn-cancel link_submit" onClick="return cancelClickedNew()">
                              <?php echo xlt('Cancel'); ?></button>
                      <?php } // end not $viewmode ?>
                </div>
                <div class="clearfix"></div>
            </form>
</div><!--end of co

</form>

</body>

<script>
<?php
if (!$viewmode) { ?>
 function duplicateVisit(enc, datestr) {
    if (!confirm(<?php echo xlj("A visit already exists for this group today. Click Cancel to open it, or OK to proceed with creating a new one.") ?>)) {
            // User pressed the cancel button, so re-direct to today's encounter
            top.restoreSession();
            parent.left_nav.setEncounter(datestr, enc, window.name);
            parent.left_nav.loadFrame('enc2', window.name, 'patient_file/encounter/encounter_top.php?set_encounter=' + encodeURIComponent(enc));
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
        echo "duplicateVisit(" . js_escape($erow['encounter']) . ", " .
        js_escape(oeFormatShortDate(substr($erow['date'], 0, 10))) . ");\n";
    }
}
?>
</script>

</html>
