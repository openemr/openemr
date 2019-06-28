<?php
/**
 * Common script for the encounter form (new and view) scripts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("$srcdir/options.inc.php");
require_once("$srcdir/acl.inc");
require_once("$srcdir/lists.inc");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;
use OpenEMR\OeUI\OemrUI;

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
<?php Header::setupHeader(['datetime-picker', 'common', 'jquery-ui', 'jquery-ui-darkness']);?>
<title><?php echo xlt('Patient Encounter'); ?></title>


<!-- validation library -->
<?php
//Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
$use_validate_js = 1;
require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

<?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
<script language="JavaScript">


    var mypcc = <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

    // Process click on issue title.
    function newissue() {
        dlgopen('../../patient_file/summary/add_edit_issue.php', '_blank', 700, 535, '', '', {
            buttons: [
            {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
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
         $collectthis = json_sanitize($collectthis["new_encounter"]["rules"]);
    }
    ?>
    var collectvalidation = <?php echo $collectthis; ?>;
    $(function(){
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

               buttons: [{text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}],

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
        var pid = <?php echo attr($pid);?>;
        var dte = document.getElementById('form_date').value;
        var facility = document.forms[0].facility_id.value;
        ajax_bill_loc(pid, dte, facility);
        <?php if ($GLOBALS['set_pos_code_encounter']) { ?>
            $.ajax({
                url: "./../../../library/ajax/facility_ajax_code.php",
                method: "GET",
                data: {
                    mode: "get_pos",
                    facility_id: facility,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }})
                .done(function (fid) {
                    document.forms[0].pos_code.value = fid;
                })
                .fail(function (xhr) {
                    console.log('error', xhr);
                });
        <?php } ?>
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
<style>
@media only screen and (max-width: 1024px) {
    #visit-details [class*="col-"], #visit-issues [class*="col-"]{
    width: 100%;
    text-align: <?php echo ($_SESSION['language_direction'] == 'rtl') ? 'right ': 'left '?> !Important;
}
</style>
<?php
if ($viewmode) {
    $body_javascript = '';
    $heading_caption = xl('Patient Encounter Form');
} else {
    $body_javascript = 'onload="javascript:document.new_encounter.reason.focus();"';
    $heading_caption = xl('New Encounter Form');
}


if ($GLOBALS['enable_help'] == 1) {
    $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 2) {
    $help_icon = '<a class="pull-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
} elseif ($GLOBALS['enable_help'] == 0) {
     $help_icon = '';
}
?>
<?php
$arrOeUiSettings = array(
    'heading_title' => $heading_caption,
    'include_patient_name' => true,// use only in appropriate pages
    'expandable' => false,
    'expandable_files' => array(""),//all file names need suffix _xpd
    'action' => "",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "",//only for actions - reset, link or back
    'show_help_icon' => true,
    'help_file_name' => "common_help.php"
);
$oemr_ui = new OemrUI($arrOeUiSettings);
?>
</head>
<body class="body_top" <?php echo $body_javascript;?>>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?>">
        <div class="row">
            <div class="col-sm-12">
                <!-- Required for the popup date selectors -->
                <div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
                <div class="page-header clearfix">
                    <?php echo  $oemr_ui->pageHeading() . "\r\n"; ?>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <form id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'>
                <?php if ($viewmode) { ?>
                    <input type=hidden name='mode' value='update'>
                    <input type=hidden name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>'>
                <?php } else { ?>
                    <input type='hidden' name='mode' value='new'>
                <?php } ?>
                    <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />
                    <fieldset>
                        <legend><?php echo xlt('Visit Details')?></legend>
                        <div id = "visit-details">
                            <div class="form-group ">
                                    <label for="pc_catid" class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Visit Category:'); ?></label>
                                    <div class="col-sm-3">
                                        <select  name='pc_catid' id='pc_catid' class='form-control col-sm-12'>
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
                                    </div>
                                    <?php
                                        $sensitivities = acl_get_sensitivities();
                                    if ($sensitivities && count($sensitivities)) {
                                        usort($sensitivities, "sensitivity_compare");
                                        ?>
                                        <label for="pc_catid" class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Sensitivity:'); ?> &nbsp;<i id='sensitivity-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></label>
                                        <div class="col-sm-3">
                                        <select name='form_sensitivity' id='form_sensitivity' class='form-control col-sm-12' >
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
                                        <?php
                                    } else {
                                        ?>

                                        <?php
                                    }
                                    ?>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="form-group">
                                    <label for='form_date' class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Date of Service:'); ?></label>
                                    <div class="col-sm-3">
                                        <input type='text' class='form-control datepicker col-sm-12' name='form_date' id='form_date' <?php echo $disabled ?>
                                        value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : attr(oeFormatShortDate(date('Y-m-d'))); ?>'
                                        title='<?php echo xla('Date of service'); ?>'/>
                                    </div>

                                    <div
                                        <?php
                                        if ($GLOBALS['ippf_specific']) {
                                            echo " style='visibility:hidden;'";
                                        } ?>>
                                        <label for='form_onset_date' class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Onset/hosp. date:'); ?> &nbsp;<i id='onset-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></label>
                                        <div class="col-sm-3">
                                            <input type='text' class='form-control datepicker col-sm-12' name='form_onset_date' id='form_onset_date'
                                            value='<?php echo $viewmode && $result['onset_date']!='0000-00-00 00:00:00' ? attr(oeFormatShortDate(substr($result['onset_date'], 0, 10))) : ''; ?>'
                                            title='<?php echo xla('Date of onset or hospitalization'); ?>' />
                                        </div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="form-group"
                                    <?php
                                    if (!$GLOBALS['gbl_visit_referral_source']) {
                                        echo "style='display:none' " ;
                                    } ?>
                                    >
                                    <label  class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Referral Source'); ?>:</label>
                                    <div class="col-sm-3">
                                        <?php echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', '');?>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php if ($GLOBALS['enable_group_therapy']) { ?>
                                <div class="form-group"id="therapy_group_name" style="display: none">
                                    <label for="form_group" class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Group name'); ?>:</label>
                                    <div class="col-sm-3">
                                        <input type='text'name='form_group' class='form-control col-sm-12' id="form_group"  placeholder='<?php echo xla('Click to select');?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
                                        <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <?php }?>
                                <div class="form-group">
                                    <label for='facility_id' class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Facility'); ?>:</label>
                                    <div class="col-sm-8">
                                        <select name='facility_id' id='facility_id' class='form-control col-sm-9' onChange="bill_loc()">
                                            <?php
                                            if ($viewmode) {
                                                $def_facility = $result['facility_id'];
                                            } else {
                                                $dres = sqlStatement("select facility_id from users where username = ?", array($_SESSION['authUser']));
                                                $drow = sqlFetchArray($dres);
                                                $def_facility = $drow['facility_id'];
                                            }
                                            $posCode = '';
                                            $facilities = $facilityService->getAllServiceLocations();
                                            if ($facilities) {
                                                foreach ($facilities as $iter) { ?>
                                            <option value="<?php echo attr($iter['id']); ?>"
                                                    <?php
                                                    if ($def_facility == $iter['id']) {
                                                        if (!$viewmode) {
                                                            $posCode = $iter['pos_code'];
                                                        }
                                                        echo "selected";
                                                    }?>><?php echo text($iter['name']); ?>
                                            </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                                <div class="form-group">
                                    <label for='billing_facility' class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('Billing Facility'); ?>:</label>
                                    <div id="ajaxdiv" class="col-sm-8">
                                        <?php
                                            $default_bill_fac = isset($result['billing_facility']) ? $result['billing_facility'] : $def_facility;
                                            billing_facility('billing_facility', $default_bill_fac);
                                        ?>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>
                        </div>
                        <?php if ($GLOBALS['set_pos_code_encounter']) { ?>
                            <div class="form-group">
                                <label for='pos_code' class="control-label col-sm-2 oe-text-to-right"><?php echo xlt('POS Code'); ?>:</label>
                                <div class="col-sm-8">
                                    <select name="pos_code" id="pos_code" class='form-control col-sm-9'>
                                        <?php
                                        $pc = new POSRef();
                                        foreach ($pc->get_pos_ref() as $pos) {
                                            echo "<option value=\"" . attr($pos["code"]) . "\" ";
                                            if (($pos["code"] == $result['pos_code'] && $viewmode) || ($pos["code"] == $posCode && !$viewmode)) {
                                                echo "selected";
                                            }
                                            echo ">" . text($pos['code'])  . ": ". xlt($pos['title']);
                                            echo "</option>\n";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        <?php }?>
                    </fieldset>
                    <fieldset>
                        <legend><?php echo xlt('Reason for Visit')?></legend>
                            <div class="form-group">
                                <div class="col-sm-10 col-sm-offset-1">
                                    <textarea name="reason" id="reason" class="form-control" cols="80" rows="4" ><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
                                </div>
                            </div>
                    </fieldset>
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
                    <fieldset>
                        <legend><?php echo xlt('Link/Add Issues (Injuries/Medical/Allergy) to Current Visit')?></legend>
                            <div id = "visit-issues">
                                <div class="form-group clearfix">
                                    <div class="col-sm-6 col-lg-offset-3">
                                            <div class="col-sm-12">
                                                <div class="col-sm-12 pull-left" style="padding-bottom:5px">
                                                    <div class="btn-group" role="group">
                                                        <?php if (acl_check('patients', 'med', '', 'write')) { ?>
                                                       <a href="../../patient_file/summary/add_edit_issue.php" class="css_button_small link_submit enc_issue"
                                                        onclick="top.restoreSession()"><span><?php echo xlt('Add Issue'); ?></span></a>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                                <div class="col-sm-12 pull-left">
                                                    <select multiple name='issues[]' class='col-sm-12'
                                                            title='<?php echo xla('Hold down [Ctrl] for multiple selections or to unselect'); ?>' size='6'>
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
                                                                "pid = ? AND encounter = ? AND list_id = ?", array($pid, $encounter, $list_id));
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
                                                </div>
                                                <div class="col-sm-12">
                                                    <p><i><?php echo xlt('To link this encounter/consult to an existing issue, click the '
                                                    . 'desired issue above to highlight it and then click [Save]. '
                                                    . 'Hold down [Ctrl] button to select multiple issues.'); ?></i></p>
                                                </div>
                                            </div>
                                    </div>
                                </div>
                            </div>
                    </fieldset>
                        <?php
                    }
                    ?>
                    <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
                    <div class="form-group clearfix">
                        <div class="col-sm-12 text-left position-override">
                            <button type="button" class="btn btn-default btn-save" onclick="top.restoreSession(); saveClicked(undefined);"><?php echo xlt('Save');?></button>
                            <?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
                                <button type="button" class="btn btn-link btn-cancel btn-separate-left" onClick="return cancelClickedOld()"><?php echo xlt('Cancel');?></button>
                            <?php } else { // not $viewmode ?>
                            <button class="btn btn-link btn-cancel btn-separate-left link_submit" onClick="return cancelClickedNew()">
                                    <?php echo xlt('Cancel'); ?></button>
                            <?php } // end not $viewmode ?>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                </form>
            </div>
        </div>
    </div><!--End of container div-->
    <?php $oemr_ui->oeBelowContainerDiv();?>
</body>
<script language="javascript">
<?php
if (!$viewmode) { ?>
 function duplicateVisit(enc, datestr) {
    if (!confirm(<?php echo xlj("A visit already exists for this patient today. Click Cancel to open it, or OK to proceed with creating a new one.") ?>)) {
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
    "FROM form_encounter AS fe, forms AS f WHERE " .
    "fe.pid = ? " .
    " AND fe.date >= ? " .
    " AND fe.date <= ? " .
    " AND " .
    "f.formdir = 'newpatient' AND f.form_id = fe.id AND f.deleted = 0 " .
    "ORDER BY fe.encounter DESC LIMIT 1", array($pid,date('Y-m-d 00:00:00'),date('Y-m-d 23:59:59')));

    if (!empty($erow['encounter'])) {
        // If there is an encounter from today then present the duplicate visit dialog
        echo "duplicateVisit(" . js_escape($erow['encounter']) . ", " .
        js_escape(oeFormatShortDate(substr($erow['date'], 0, 10))) . ");\n";
    }
}
?>

<?php
if ($GLOBALS['enable_group_therapy']) { ?>
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
              {text: <?php echo xlj('Close'); ?>, close: true, style: 'default btn-sm'}
          ]
      });
    }
    // This is for callback by the find-group popup.
    function setgroup(gid, name) {
        var f = document.forms[0];
        f.form_group.value = name;
        f.form_gid.value = gid;
    }

    <?php
    if ($viewmode && in_array($result['pc_catid'], $therapyGroupCategories)) {?>
        $('#therapy_group_name').show();
        <?php
    } ?>
    <?php
} ?>

$(function (){
    $('#billing_facility').addClass('col-sm-9');
    //for jquery tooltip to function if jquery 1.12.1.js is called via jquery-ui in the Header::setupHeader
    // the relevant css file needs to be called i.e. jquery-ui-darkness - to get a black tooltip
    $('#sensitivity-tooltip').attr( "title", <?php echo xlj('If set as high will restrict visibility of encounter to users belonging to certain groups (AROs). By default - Physicians and Administrators'); ?> );
    $('#sensitivity-tooltip').tooltip();
    $('#onset-tooltip').attr( "title", <?php echo xlj('Hospital date needed for successful billing of hospital encounters'); ?> );
    $('#onset-tooltip').tooltip();
});
</script>
</html>
