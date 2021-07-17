<?php

/**
 * Common script for the encounter form (new and view) scripts.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Ranganath Pathak <pathak@scrs1.org>
 * @author    Jerry Padgett <sjpadgett@gmail.com>
 * @copyright Copyright (c) 2019 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2019 Ranganath Pathak <pathak@scrs1.org>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("$srcdir/options.inc.php");
require_once("$srcdir/lists.inc");

use OpenEMR\Common\Acl\AclExtended;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\UserService;
use OpenEMR\Services\FacilityService;
use OpenEMR\OeUI\OemrUI;

$facilityService = new FacilityService();

if ($GLOBALS['enable_group_therapy']) {
    require_once("$srcdir/group.inc");
}

$months = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
$days = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12", "13", "14",
    "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");
$thisyear = date("Y");
$years = array($thisyear - 1, $thisyear, $thisyear + 1, $thisyear + 2);

$mode = (!empty($_GET['mode'])) ? $_GET['mode'] : null;

// "followup" mode is relevant when enable follow up encounters global is enabled
// it allows the user to duplicate past encounter and connect between the two
// under this mode the facility and the visit category will be same as the origin and in readonly
if ($mode === "followup") {
    $encounter = (!empty($_GET['enc'])) ? (int)$_GET['enc'] : null;
    if (!is_null($encounter)) {
        $viewmode = true;
        $_REQUEST['id'] = $encounter;
    }
}

if ($viewmode) {
    $id = (isset($_REQUEST['id'])) ? $_REQUEST['id'] : '';
    $result = sqlQuery("SELECT * FROM form_encounter WHERE id = ?", array($id));
    $encounter = $result['encounter'];
    $encounter_followup_id = $result['parent_encounter_id'] ?? null;
    if ($encounter_followup_id) {
        $q = "SELECT fe.date as date, fe.encounter as encounter FROM form_encounter AS fe " .
            "JOIN forms AS f ON f.form_id = fe.id AND f.encounter = fe.encounter " .
            "WHERE fe.id = ? AND f.deleted = 0 ";
        $followup_enc = sqlQuery($q, array($encounter_followup_id));
        $followup_date = date("m/d/Y", strtotime($followup_enc['date']));
        $encounter_followup = $followup_enc['encounter'];
    }
    // @todo why is this here?
    if ($mode === "followup") {
        $followup_date = date("m/d/Y", strtotime($result['date']));
        $encounter_followup = $result['encounter'];
        $result['reason'] = '';
        $result['date'] = date('Y-m-d H:i:s');
        $encounterId = $result['id'];
    }

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

// get issues
$ires = sqlStatement("SELECT id, type, title, begdate FROM lists WHERE " .
    "pid = ? AND enddate IS NULL " .
    "ORDER BY type, begdate", array($pid));
?>
<!DOCTYPE html>
<head>
    <?php Header::setupHeader(['datetime-picker', 'common']); ?>
    <title><?php echo xlt('Patient Encounter'); ?></title>


    <!-- validation library -->
    <?php
    //Not lbf forms use the new validation, please make sure you have the corresponding values in the list Page validation
    $use_validate_js = 1;
    require_once($GLOBALS['srcdir'] . "/validation/validation_script.js.php"); ?>

    <?php include_once("{$GLOBALS['srcdir']}/ajax/facility_ajax_jav.inc.php"); ?>
    <script>
        const mypcc = '' + <?php echo js_escape($GLOBALS['phone_country_code']); ?>;

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
        let collectvalidation = <?php echo $collectthis; ?>;
        $(function () {
            window.saveClicked = function (event) {
                const submit = submitme(1, event, 'new-encounter-form', collectvalidation);
                if (submit) {
                    top.restoreSession();
                    $('#new-encounter-form').submit();
                }
            }

            $(".enc_issue").on('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                dlgopen('', '', 700, 650, '', '', {
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

        const isPosEnabled = "" + <?php echo js_escape($GLOBALS['set_pos_code_encounter']); ?>;

        function getPOS() {
            if (!isPosEnabled) {
                return false;
            }
            let facility = document.forms[0].facility_id.value;
            $.ajax({
                url: "./../../../library/ajax/facility_ajax_code.php",
                method: "GET",
                data: {
                    mode: "get_pos",
                    facility_id: facility,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }
            }).done(function (fid) {
                document.forms[0].pos_code.value = JSON.parse(fid);
            }).fail(function (xhr) {
                console.log('error', xhr);
            });
        }

        function newUserSelected() {
            let provider = document.getElementById('provider_id').value;
            $.ajax({
                url: "./../../../library/ajax/facility_ajax_code.php",
                method: "GET",
                data: {
                    mode: "get_user_data",
                    provider_id: provider,
                    csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
                }
            }).done(function (data) {
                let rtn = JSON.parse(data);
                document.forms[0].facility_id.value = rtn[0];
                if (isPosEnabled) {
                    document.forms[0].pos_code.value = rtn[1];
                }
                if (Number(rtn[2]) === 1) {
                    document.forms[0]['billing_facility'].value = rtn[0];
                }
            }).fail(function (xhr) {
                console.log('error', xhr);
            });
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
            #visit-details [class*="col-"],
            #visit-issues [class*="col-"] {
                width: 100%;
                text-align: <?php echo ($_SESSION['language_direction'] == 'rtl') ? 'right ' : 'left '?> !important;
            }
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
        $help_icon = '<a class="float-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#676666" title="' . xla("Click to view Help") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
    } elseif ($GLOBALS['enable_help'] == 2) {
        $help_icon = '<a class="float-right oe-help-redirect" data-target="#myModal" data-toggle="modal" href="#" id="help-href" name="help-href" style="color:#DCD6D0 !Important" title="' . xla("To enable help - Go to  Administration > Globals > Features > Enable Help Modal") . '"><i class="fa fa-question-circle" aria-hidden="true"></i></a>';
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

    $provider_id = $userauthorized ? $_SESSION['authUserID'] : 0;
    if (!$viewmode) {
        $now = date('Y-m-d');
        $encnow = date('Y-m-d 00:00:00');
        $time = date("H:i:00");
        $q = "SELECT pc_aid, pc_facility, pc_billing_location, pc_catid, pc_startTime" .
            " FROM openemr_postcalendar_events WHERE pc_pid=? AND pc_eventDate=?" .
            " ORDER BY pc_startTime ASC";
        $q_events = sqlStatement($q, array($pid, $now));
        while ($override = sqlFetchArray($q_events)) {
            $q = "SELECT fe.encounter as encounter FROM form_encounter AS fe " .
                "JOIN forms AS f ON f.form_id = fe.id AND f.encounter = fe.encounter " .
                "WHERE fe.pid=? AND fe.date=? AND fe.provider_id=? AND f.deleted=0";
            $q_enc = sqlQuery($q, array($pid, $encnow, $override['pc_aid']));
            if (!empty($override) && is_array($override) && empty($q_enc['encounter'])) {
                $provider_id = $override['pc_aid'];
                $default_bill_fac_override = $override['pc_billing_location'];
                $default_fac_override = $override['pc_facility'];
                $default_catid_override = $override['pc_catid'];
            }
        }
    }
    ?>
</head>
<body <?php echo $body_javascript; ?>>
    <div id="container_div" class="<?php echo attr($oemr_ui->oeContainer()); ?> mt-3">
        <div class="row">
            <div class="col-sm-12">
                <!-- Required for the popup date selectors -->
                <div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>
                <?php echo $oemr_ui->pageHeading() . "\r\n"; ?>
            </div>
        </div>
        <form class="mt-3" id="new-encounter-form" method='post' action="<?php echo $rootdir ?>/forms/newpatient/save.php" name='new_encounter'>
            <?php if ($viewmode && $mode !== "followup") { ?>
                <input type='hidden' name='mode' value='update' />
                <input type='hidden' name='id' value='<?php echo (isset($_GET["id"])) ? attr($_GET["id"]) : '' ?>' />
            <?php } else { ?>
                <input type='hidden' name='mode' value='new' />
            <?php } ?>
            <input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

            <?php if ($mode === "followup") { ?>
                <input type='hidden' name='parent_enc_id' value='<?php echo attr($encounterId); ?>' />
            <?php } ?>

            <fieldset>
                <legend><?php echo xlt('Visit Details') ?>
                    <small>
                        <?php echo (!empty($encounter_followup)) ? (xlt("Follow up for") . ": " . text($encounter_followup) . " " . xlt("Dated") . ": " . text($followup_date)) : ''; ?>
                    </small>
                </legend>
                <div id="visit-details" class="px-5">
                    <div class="form-row align-items-center">
                        <div class="col-sm-2">
                            <label for="pc_catid" class="text-right"><?php echo xlt('Visit Category:'); ?></label>
                        </div>
                        <div class="col-sm">
                            <select name='pc_catid' id='pc_catid' class='form-control' <?php echo ($mode === "followup") ? 'disabled' : ''; ?>>
                                <option value='_blank'>-- <?php echo xlt('Select One'); ?> --</option>
                                <?php
                                //Bring only patient and group categories
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
                                    $postCalendarCategoryACO = AclMain::fetchPostCalendarCategoryACO($catId);
                                    if ($postCalendarCategoryACO) {
                                        $postCalendarCategoryACO = explode('|', $postCalendarCategoryACO);
                                        $authPostCalendarCategoryWrite = AclMain::aclCheckCore($postCalendarCategoryACO[0], $postCalendarCategoryACO[1], '', 'write');
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
                            <?php if ($mode === "followup") { ?>
                                <input name="pc_catid" value="<?php echo attr($result['pc_catid']); ?>" hidden />
                            <?php } ?>
                        </div>
                        <?php
                        $sensitivities = AclExtended::aclGetSensitivities();
                        if ($sensitivities && count($sensitivities)) {
                            usort($sensitivities, "sensitivity_compare");
                            ?>
                        <div class="col-sm-2">
                            <label for="pc_catid" class="text-right"><?php echo xlt('Sensitivity:'); ?> <i id='sensitivity-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></label>
                        </div>
                        <div class="col-sm">
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
                            <?php } ?>
                        </div>
                    </div>
                    <div class="form-row align-items-center mt-2">
                        <div class="col-sm-2">
                            <label for='form_date' class="text-right"><?php echo xlt('Date of Service:'); ?></label>
                        </div>
                        <div class="col-sm">
                            <input type='text' class='form-control datepicker' name='form_date' id='form_date' <?php echo ($disabled ?? '') ?> value='<?php echo $viewmode ? attr(oeFormatShortDate(substr($result['date'], 0, 10))) : attr(oeFormatShortDate(date('Y-m-d'))); ?>' title='<?php echo xla('Date of service'); ?>' />
                        </div>
                        <div class="col-sm-2" <?php echo empty($GLOBALS['gbl_visit_onset_date']) ? "style='visibility:hidden;'" : ""; ?>>
                            <label for='form_onset_date' class="text-right"><?php echo xlt('Onset/hosp. date:'); ?> &nbsp;<i id='onset-tooltip' class="fa fa-info-circle text-primary" aria-hidden="true"></i></label>
                        </div>
                        <div class="col-sm" <?php echo empty($GLOBALS['gbl_visit_onset_date']) ? "style='visibility:hidden;'" : ""; ?>>
                            <input type='text' class='form-control datepicker' name='form_onset_date' id='form_onset_date' value='<?php echo $viewmode && $result['onset_date'] !== '0000-00-00 00:00:00' ? attr(oeFormatShortDate(substr($result['onset_date'], 0, 10))) : ''; ?>' title='<?php echo xla('Date of onset or hospitalization'); ?>' />
                        </div>
                    </div>
                    <div class="form-row align-items-center mt-2"
                        <?php
                        if (!$GLOBALS['gbl_visit_referral_source']) {
                            echo "style='display:none' ";
                        } ?>>
                        <div class="col-sm-2">
                            <label for="form_referral_source" class="text-right"><?php echo xlt('Referral Source'); ?>:</label>
                        </div>
                        <div class="col-sm">
                            <?php echo generate_select_list('form_referral_source', 'refsource', $viewmode ? $result['referral_source'] : '', ''); ?>
                        </div>
                    </div>
                    <?php if ($GLOBALS['enable_group_therapy']) { ?>
                    <div class="form-group mx-auto mt-2" id="therapy_group_name" style="display: none">
                        <div class="col-sm-2">
                            <label for="form_group" class="text-right"><?php echo xlt('Group name'); ?>:</label>
                        </div>
                        <div class="col-sm">
                            <input type='text' name='form_group' class='form-control' id="form_group" placeholder='<?php echo xla('Click to select'); ?>' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr(getGroup($result['external_id'])['group_name']) : ''; ?>' onclick='sel_group()' title='<?php echo xla('Click to select group'); ?>' readonly />
                            <input type='hidden' name='form_gid' value='<?php echo $viewmode && in_array($result['pc_catid'], $therapyGroupCategories) ? attr($result['external_id']) : '' ?>' />
                        </div>
                    </div>
                    <?php } ?>
                    <div class="form-row align-items-center mt-2">
                        <div class="col-sm-2">
                            <label for='provider_id' class="text-right"><?php echo xlt('Encounter Provider'); ?>:</label>
                        </div>
                        <div class="col-sm">
                            <select name='provider_id' id='provider_id' class='form-control' onChange="newUserSelected()">
                                <?php
                                if ($viewmode) {
                                    $provider_id = $result['provider_id'];
                                }
                                $userService = new UserService();
                                $users = $userService->getActiveUsers();
                                foreach ($users as $activeUser) {
                                    $p_id = (int)$activeUser['id'];
                                    // Check for the case where an encounter is created by non-auth user
                                    // but has permissions to create/edit encounter.
                                    $flag_it = "";
                                    if ($activeUser['authorized'] != 1) {
                                        if ($p_id === (int)$result['provider_id']) {
                                            $flag_it = " (" . xlt("Non Provider") . ")";
                                        } else {
                                            continue;
                                        }
                                    }
                                    echo "<option value='" . attr($p_id) . "'";
                                    if ((int)$provider_id === $p_id) {
                                        echo "selected";
                                    }
                                    echo ">" . text($activeUser['lname']) . ' ' .
                                        text($activeUser['fname']) . ' ' . text($activeUser['mname']) . $flag_it . "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-sm-2">
                            <label for='class' class="text-right"><?php echo xlt('Class'); ?>:</label>
                        </div>
                        <div class="col-sm">
                            <?php echo generate_select_list('class_code', '_ActEncounterCode', $viewmode ? $result['class_code'] : '', '', ''); ?>
                        </div>
                    </div>
                    <div class="form-row align-items-center mt-2">
                        <div class="col-sm-2">
                            <label for='facility_id' class="text-right"><?php echo xlt('Facility'); ?>:</label>
                        </div>
                        <div class="col-sm">
                            <select name='facility_id' id='facility_id' class='form-control' onChange="getPOS()" <?php echo ($mode === "followup") ? 'disabled' : ''; ?> >
                                <?php
                                if ($viewmode) {
                                    $def_facility = $result['facility_id'];
                                } elseif (!empty($default_fac_override)) {
                                    $def_facility = $default_fac_override;
                                } else {
                                    $def_facility = $facilityService->getFacilityForUser($_SESSION['authUserID'])['id'];
                                }
                                $posCode = '';
                                $facilities = $facilityService->getAllServiceLocations();
                                foreach ($facilities as $iter) {
                                    $selected_fac = '';
                                    if ($def_facility === $iter['id']) {
                                        $selected_fac = " selected";
                                        if (!$viewmode) {
                                            $posCode = $iter['pos_code'];
                                        }
                                    } ?>
                                <option value="<?php echo attr($iter['id']); ?>"<?php echo $selected_fac; ?>><?php echo text($iter['name']); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <?php if ($mode === "followup") { ?>
                            <input type="hidden" name="facility_id" value="<?php echo attr($result['facility_id']); ?>" />
                        <?php } ?>
                        <div class="col-sm-2">
                            <label for='billing_facility' class="text-right"><?php echo xlt('Billing Facility'); ?>:</label>
                        </div>
                        <div id="ajaxdiv" class="col-sm">
                            <?php
                            if (!empty($default_bill_fac_override)) {
                                $default_bill_fac = $default_bill_fac_override;
                            } elseif (!$viewmode && $mode !== "followup") {
                                $tmp_be = $facilityService->getPrimaryBusinessEntity();
                                $tmp_bl = $facilityService->getPrimaryBillingLocation();
                                $tmp = !empty($tmp_be['id']) ? $tmp_be['id'] : (!empty($tmp_bl['id']) ? $tmp_bl['id'] : null);
                                $default_bill_fac = !empty($tmp) ? $tmp : $def_facility;
                            } else {
                                $default_bill_fac = isset($result['billing_facility']) ? $result['billing_facility'] : $def_facility;
                            }
                            billing_facility('billing_facility', $default_bill_fac);
                            ?>
                        </div>
                    </div>
                    <?php if ($GLOBALS['set_pos_code_encounter']) { ?>
                    <div class="form-row mt-2">
                        <div class="col-sm-2">
                            <label for='pos_code' class="text-right"><?php echo xlt('POS Code'); ?>:</label>
                        </div>
                        <div class="col-sm-8">
                            <select name="pos_code" id="pos_code" class='form-control'>
                                <?php
                                $pc = new POSRef();
                                foreach ($pc->get_pos_ref() as $pos) {
                                    echo "<option value=\"" . attr($pos["code"]) . "\"";
                                    if (($pos["code"] == $result['pos_code'] && $viewmode) || ($pos["code"] == $posCode && !$viewmode)) {
                                        echo " selected";
                                    }
                                    echo ">" . text($pos['code']) . ": " . xlt($pos['title']);
                                    echo "</option>\n";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </fieldset>
            <fieldset>
                <legend><?php echo xlt('Reason for Visit') ?></legend>
                <div class="form-row mx-5">
                    <textarea name="reason" id="reason" class="form-control" cols="80" rows="4"><?php echo $viewmode ? text($result['reason']) : text($GLOBALS['default_chief_complaint']); ?></textarea>
                </div>
            </fieldset>
            <?php
            // To see issues stuff user needs write access to all issue types.
            $issuesauth = true;
            foreach ($ISSUE_TYPES as $type => $dummy) {
                if (!AclMain::aclCheckIssue($type, '', 'write')) {
                    $issuesauth = false;
                    break;
                }
            }
            if ($issuesauth) {
                ?>
                <fieldset>
                    <legend><?php echo xlt('Link/Add Issues (Injuries/Medical/Allergy) to Current Visit') ?></legend>
                    <div id="visit-issues">
                        <div class="form-row px-5">
                            <div class="pb-1">
                                <div class="btn-group" role="group">
                                    <?php if (AclMain::aclCheckCore('patients', 'med', '', 'write')) { ?>
                                        <a href="../../patient_file/summary/add_edit_issue.php" class="btn btn-primary btn-add btn-sm enc_issue" onclick="top.restoreSession()"><?php echo xlt('Add Issue'); ?></a>
                                    <?php } ?>
                                </div>
                            </div>
                            <select multiple name='issues[]' class='form-control' title='<?php echo xla('Hold down [Ctrl] for multiple selections or to unselect'); ?>' size='6'>
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
                            <p><i><?php echo xlt('To link this encounter/consult to an existing issue, click the '
                                        . 'desired issue above to highlight it and then click [Save]. '
                                        . 'Hold down [Ctrl] button to select multiple issues.'); ?></i></p>
                        </div>
                    </div>
                </fieldset>
                <?php
            }
            ?>
            <?php //can change position of buttons by creating a class 'position-override' and adding rule text-align:center or right as the case may be in individual stylesheets ?>
            <div class="form-row">
                <div class="col-sm-12 text-left position-override">
                    <div class="btn-group" role="group">
                        <button type="button" class="btn btn-primary btn-save" onclick="top.restoreSession(); saveClicked(undefined);"><?php echo xlt('Save'); ?></button>
                        <?php if ($viewmode || empty($_GET["autoloaded"])) { // not creating new encounter ?>
                            <button type="button" class="btn btn-secondary btn-cancel" onClick="return cancelClickedOld()"><?php echo xlt('Cancel'); ?></button>
                        <?php } else { // not $viewmode ?>
                            <button class="btn btn-secondary btn-cancel link_submit" onClick="return cancelClickedNew()"><?php echo xlt('Cancel'); ?></button>
                        <?php } // end not $viewmode ?>
                    </div>
                </div>
            </div>
        </form>
    </div><!--End of container div-->
    <?php $oemr_ui->oeBelowContainerDiv(); ?>
<script>
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
        "ORDER BY fe.encounter DESC LIMIT 1", array($pid, date('Y-m-d 00:00:00'), date('Y-m-d 23:59:59')));

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
    let groupCategories = <?php echo json_encode($therapyGroupCategories); ?>;
    $('#pc_catid').on('change', function () {
        if (groupCategories.indexOf($(this).val()) > -1) {
            $('#therapy_group_name').show();
        } else {
            $('#therapy_group_name').hide();
        }
    });

    function sel_group() {
        top.restoreSession();
        const url = '<?php echo $GLOBALS['webroot']?>/interface/main/calendar/find_group_popup.php';
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

    $(function () {
        $('#sensitivity-tooltip').attr({"title": <?php echo xlj('If set as high will restrict visibility of encounter to users belonging to certain groups (AROs). By default - Physicians and Administrators'); ?>, "data-toggle": "tooltip", "data-placement": "bottom"}).tooltip();
        $('#onset-tooltip').attr({"title": <?php echo xlj('Hospital date needed for successful billing of hospital encounters'); ?>, "data-toggle": "tooltip", "data-placement": "bottom"}).tooltip();
    });

</script>

<?php if (!empty($GLOBALS['text_templates_enabled'])) { ?>
    <script src="<?php echo $GLOBALS['web_root'] ?>/library/js/CustomTemplateLoader.js"></script>
<?php } ?>
</body>
</html>
