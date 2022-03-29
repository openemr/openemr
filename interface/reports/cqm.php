<?php

/**
 * CDR reports.  Handles the generation and display of CQM/AMC/patient_alerts/standard reports
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @author    Stephen Nielson <snielson@discoverandchange.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2022 Discover and Change, Inc. <snielson@discoverandchange.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: This needs a complete makeover


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/report_database.inc";

use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\OeUI\OemrUI;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}


/**
 * Formats the report data into a format that can be consumed by the twig rendering output.
 * @param $report_id The report that we are formatting
 * @param $data string json encoded report data retrieved from the database
 * @param $is_amc boolean True if this is an AMC report
 * @param $is_cqm boolean True if this is an CQM report
 * @param $type_report The specific report type (could be a subset of AMC or CQM)
 * @param $amc_report_types If an AMC report, the specific AMC report type
 * @return array A formatted array of record rows to be used for displaying a CQM/AMC/Standard report.
 */
function formatReportData($report_id, &$data, $is_amc, $is_cqm, $type_report, $amc_report_types = array())
{
    $dataSheet = json_decode($data, true) ?? [];
    $formatted = [];
    $main_pass_filter = 0;
    foreach ($dataSheet as $row) {
        $row['type'] = $type_report;
        $row['total_patients'] = $row['total_patients'] ?? 0;
        $failed_items = null;
        $displayFieldSubHeader = "";

        if ($is_cqm) {
            $row['type'] = 'cqm';
            $row['total_patients'] = $row['initial_population'] ?? 0;
            if (isset($row['cqm_pqri_code'])) {
                $displayFieldSubHeader .= " " . xl('PQRI') . ":" . $row['cqm_pqri_code'] . " ";
            }
            if (isset($row['cqm_nqf_code'])) {
                $displayFieldSubHeader .= " " . xl('NQF') . ":" . $row['cqm_nqf_code'] . " ";
            }
        } else if ($is_amc) {
            $row['type'] = 'amc';
            if (!empty($amc_report_types[$type_report]['code_col'])) {
                $code_col = $amc_report_types[$type_report]['code_col'];
                $displayFieldSubHeader .= " " . text($amc_report_types[$type_report]['abbr']) . ":"
                    . text($row[$code_col]) . " ";
            }
        }

        if (isset($row['is_main'])) {
            // note that the is_main record must always come before is_sub in the report or the data will not work.
            $main_pass_filter = $row['pass_filter'] ?? 0;
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $row['id']);
            if ($type_report == "standard") {
                // Excluded is not part of denominator in standard rules so do not use in calculation
                $failed_items = $row['pass_filter'] - $row['pass_target'];
            } else {
                $failed_items = $row['pass_filter'] - $row['pass_target'] - $row['excluded'];
            }
            $row['display_field_sub'] = ($displayFieldSubHeader != "") ? "($displayFieldSubHeader)" : null;
        } else if (isset($row['is_sub'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $row['action_category'])
                . ': ' . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $row['action_item']);
            // Excluded is not part of denominator in standard rules so do not use in calculation
            $failed_items = $main_pass_filter - $row['pass_target'];
        } else if (isset($row['is_plan'])) {
            $row['display_field'] = generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $row['id']);
        }

        if (isset($row['itemized_test_id'])) {
            $csrf_token = CsrfUtils::collectCsrfToken();

            $base_link = sprintf(
                "../main/finder/patient_select.php?from_page=cdr_report&report_id=%d"
                . "&itemized_test_id=%d&numerator_label=%s&csrf_token_form=%s",
                urlencode($report_id),
                urlencode($row['itemized_test_id']),
                urlencode($row['numerator_label'] ?? ''),
                urlencode($csrf_token)
            );

            // we need the provider & group id here...

            // denominator
            if (isset($row['pass_filter']) && $row['pass_filter'] > 0) {
                $row['display_pass_link'] = $base_link . "&pass_id=all";
            }

            // excluded denominator
            if (isset($row['excluded']) && ($row['excluded'] > 0)) {
                $row['display_excluded_link'] = $base_link . "&pass_id=exclude";
            }

            // passed numerator
            if (isset($row['pass_target']) && ($row['pass_target'] > 0)) {
                $row['display_target_link'] = $base_link . "&pass_id=pass";
            }
            // failed numerator
            if (isset($failed_items) && $failed_items > 0) {
                $row['display_failed_link'] = $base_link . "&pass_id=fail";
            }
            $row['failed_items'] = $failed_items;
        }

        $formatted[] = $row;
    }
    return $formatted;
}

$amc_report_types = CertificationReportTypes::getReportTypeRecords();

// See if showing an old report or creating a new report
$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";

// Collect the back variable, if pertinent
$back_link = (isset($_GET['back'])) ? trim($_GET['back']) : "";

// If showing an old report, then collect information
$heading_title = "";
$help_file_name = "";
if (!empty($report_id)) {
    $report_view = collectReportDatabase($report_id);
    $date_report = $report_view['date_report'];
    $type_report = $report_view['type'];

    $is_amc_report = CertificationReportTypes::isAMCReportType($type_report);
    $is_cqm_report = ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014");
    $type_report = ($is_amc_report || $is_cqm_report) ? $type_report : "standard";
    $rule_filter = $report_view['type'];

    if ($is_amc_report) {
        $begin_date = $report_view['date_begin'];
        $labs_manual = $report_view['labs_manual'];
    }

    $target_date = $report_view['date_target'];
    $plan_filter = $report_view['plan'];
    $organize_method = $report_view['organize_mode'];
    $provider  = $report_view['provider'];
    $pat_prov_rel = $report_view['pat_prov_rel'];


    $amc_report_data = $amc_report_types[$type_report] ?? array();
    $dataSheet = formatReportData($report_id, $report_view['data'], $is_amc_report, $is_cqm_report, $type_report, $amc_report_data);
} else {
  // Collect report type parameter (standard, amc, cqm)
  // Note that need to convert amc_2011 and amc_2014 to amc and cqm_2011 and cqm_2014 to cqm
  // to simplify for when submitting for a new report.
    $type_report = (isset($_GET['type'])) ? trim($_GET['type']) : "standard";

    $is_amc_report = CertificationReportTypes::isAMCReportType($type_report);
    $is_cqm_report = ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014");

    if (($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
        $type_report = "cqm";
    }

  // Collect form parameters (set defaults if empty)
    if ($is_amc_report) {
        $begin_date = (isset($_POST['form_begin_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_begin_date'])) : "";
        $labs_manual = (isset($_POST['labs_manual_entry'])) ? trim($_POST['labs_manual_entry']) : "0";
    }

    $target_date = (isset($_POST['form_target_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_target_date'])) : date('Y-m-d H:i:s');
    $rule_filter = (isset($_POST['form_rule_filter'])) ? trim($_POST['form_rule_filter']) : CertificationReportTypes::DEFAULT;
    $plan_filter = (isset($_POST['form_plan_filter'])) ? trim($_POST['form_plan_filter']) : "";
    $organize_method = (empty($plan_filter)) ? "default" : "plans";
    $provider  = trim($_POST['form_provider'] ?? '');
    $pat_prov_rel = (empty($_POST['form_pat_prov_rel'])) ? "primary" : trim($_POST['form_pat_prov_rel']);
    $dataSheet = [];
}

$show_help = false;
if ($type_report == "standard") {
    $heading_title = xl('Standard Measures');
} else if ($type_report == "cqm") {
    $heading_title = xl('Clinical Quality Measures (CQM)');
} else if ($type_report == 'cqm_2011') {
    $heading_title = 'Clinical Quality Measures (CQM) - 2011';
} else if ($is_amc_report) {
    $heading_title = $amc_report_types[$type_report]['title'];
    $show_help = true;
    $help_file_name = "cqm_amc_help.php";
}


$arrOeUiSettings = array(
    'heading_title' => xl('Add/Edit Patient Transaction'),
    'include_patient_name' => false,
    'expandable' => false,
    'expandable_files' => array(),//all file names need suffix _xpd
    'action' => "conceal",//conceal, reveal, search, reset, link or back
    'action_title' => "",
    'action_href' => "cqm.php",//only for actions - reset, link and back
    'show_help_icon' => $show_help,
    'help_file_name' => $help_file_name
);
$oemr_ui = new OemrUI($arrOeUiSettings);

require_once("$srcdir/display_help_icon_inc.php");

?>

<html>

<head>
    <title><?php echo text($heading_title); ?></title>
<?php Header::setupHeader('datetime-picker'); ?>

<script>

    <?php require $GLOBALS['srcdir'] . "/formatting_DateToYYYYMMDD_js.js.php" ?>

 $(function () {
  var win = top.printLogSetup ? top : opener.top;
  win.printLogSetup(document.getElementById('printbutton'));

  $('.datepicker').datetimepicker({
    <?php $datetimepicker_timepicker = true; ?>
    <?php $datetimepicker_showseconds = true; ?>
    <?php $datetimepicker_formatInput = true; ?>
    <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
    <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
  });
 });

 function runReport() {

   // Validate first
   if (!(validateForm())) {
     alert(<?php echo xlj("Rule Set and Plan Set selections are not consistent. Please fix and Submit again."); ?>);
     return false;
   }

   // Showing processing wheel
   $("#processing").show();

   // hide Submit buttons
   $("#submit_button").hide();
   $("#xmla_button").hide();
   $("#xmlb_button").hide();
   $("#xmlc_button").hide();
   $("#print_button").hide();
   $("#genQRDA").hide();

   // hide instructions
   $("#instructions_text").hide();

   // Collect an id string via an ajax request
   top.restoreSession();
   $.get("../../library/ajax/collect_new_report_id.php",
     { csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?> },
     function(data){
       // Set the report id in page form
       $("#form_new_report_id").attr("value",data);

       // Start collection status checks
       collectStatus($("#form_new_report_id").val());

       // Run the report
       top.restoreSession();
       $.post("../../library/ajax/execute_cdr_report.php",
         {provider: $("#form_provider").val(),
          type: $("#form_rule_filter").val(),
          date_target: DateToYYYYMMDDHHMMSS_js($("#form_target_date").val()),
          date_begin: DateToYYYYMMDDHHMMSS_js($("#form_begin_date").val()),
          plan: $("#form_plan_filter").val(),
          labs: $("#labs_manual_entry").val(),
          pat_prov_rel: $("#form_pat_prov_rel").val(),
          execute_report_id: $("#form_new_report_id").val(),
          csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
         });
   });
 }

 function collectStatus(report_id) {
   // Collect the status string via an ajax request and place in DOM at timed intervals
   top.restoreSession();
   // Do not send the skip_timeout_reset parameter, so don't close window before report is done.
   $.post("../../library/ajax/status_report.php",
     {
       status_report_id: report_id,
       csrf_token_form: <?php echo js_escape(CsrfUtils::collectCsrfToken()); ?>
     },
     function(data){
       if (data == "PENDING") {
         // Place the pending string in the DOM
         $('#status_span').replaceWith("<span id='status_span'><?php echo xla("Preparing To Run Report"); ?></span>");
       }
       else if (data == "COMPLETE") {
         // Go into the results page
         top.restoreSession();
         link_report = "cqm.php?report_id=" + encodeURIComponent(report_id);
         window.open(link_report,'_self',false);
       }
       else {
         // Place the string in the DOM
         $('#status_span').replaceWith("<span id='status_span'>"+data+"</span>");
       }
   });
   // run status check every 10 seconds
   var repeater = setTimeout("collectStatus("+report_id+")", 10000);
 }

 function GenXml(sNested) {
      top.restoreSession();
      //QRDA Category III Export
      if(sNested == "QRDA"){
        var form_rule_filter = theform.form_rule_filter.value
        var sLoc = '../../custom/export_qrda_xml.php?target_date=' + encodeURIComponent(DateToYYYYMMDDHHMMSS_js(theform.form_target_date.value)) + '&qrda_version=3&rule_filter=cqm_2014&form_provider=' + encodeURIComponent(theform.form_provider.value) + '&report_id=' + <?php echo js_url($report_id); ?> + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
      }else{
        var sLoc = '../../custom/export_registry_xml.php?&target_date=' + encodeURIComponent(DateToYYYYMMDDHHMMSS_js(theform.form_target_date.value)) + '&nested=' + encodeURIComponent(sNested) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
      }
      dlgopen(sLoc, '_blank', 600, 500);
      return false;
 }

 //QRDA I - 2014 Download
 function downloadQRDA() {
    top.restoreSession();
    var reportID = <?php echo js_escape($report_id); ?>;
    var provider = $("#form_provider").val();
    sLoc = '../../custom/download_qrda.php?&report_id=' + encodeURIComponent(reportID) + '&provider_id=' + encodeURIComponent(provider) + '&csrf_token_form=' + <?php echo js_url(CsrfUtils::collectCsrfToken()); ?>;
    dlgopen(sLoc, '_blank', 600, 500);
 }

 function validateForm() {
    <?php if ((empty($report_id)) && ($type_report == "cqm")) { ?>
     // If this is a cqm and plan set not set to ignore, then need to ensure consistent with the rules set
     if ($("#form_plan_filter").val() != '') {
       if ($("#form_rule_filter").val() == $("#form_plan_filter").val()) {
         return true;
       } else {
         return false;
       }
     }
     else {
       return true;
     }
    <?php } else { ?>
     return true;
    <?php } ?>
 }

 function Form_Validate() {
        <?php if ((empty($report_id)) && $is_amc_report) { ?>
         var d = document.forms[0];
         FromDate = DateToYYYYMMDDHHMMSS_js(d.form_begin_date.value);
         ToDate = DateToYYYYMMDDHHMMSS_js(d.form_target_date.value);
          if ( (FromDate.length > 0) && (ToDate.length > 0) ) {
             if (FromDate > ToDate){
                  alert(<?php echo xlj('End date must be later than Begin date!'); ?>);
                  return false;
             }
         }
        <?php } ?>

    //For Results are in Gray Background & disabling anchor links
    <?php if ($report_id != "") {?>
    $("#report_results").css("opacity", '0.4');
    $("#report_results").css("filter", 'alpha(opacity=40)');
    $("a").removeAttr("href");
    <?php }?>

    $("#form_refresh").attr("value","true");
    runReport();
    return true;
}

</script>

<style>

/* specifically include & exclude from printing */
@media print {
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
<div id="container_div" class="container-fluid mt-3">

<span class='title'><?php echo xlt('Report'); ?> -

<?php if ($type_report == "standard") { ?>
    <?php echo xlt('Standard Measures'); ?>
<?php } ?>

<?php if ($type_report == "cqm") { ?>
    <?php echo xlt('Clinical Quality Measures (CQM)'); ?>
<?php } ?>
<?php if ($type_report == "cqm_2011") { ?>
    <?php echo xlt('Clinical Quality Measures (CQM) - 2011'); ?>
<?php } ?>
<?php if ($type_report == "cqm_2014") { ?>
    <?php echo xlt('Clinical Quality Measures (CQM) - 2014'); ?>
<?php } ?>

    <?php if (isset($amc_report_types[$type_report])) : ?>
        <?php echo text($amc_report_types[$type_report]['title']); ?>
    <?php endif; ?>

<?php $dis_text = ''; ?>
<?php if (!empty($report_id)) { ?>
    <?php echo " - " . xlt('Date of Report') . ": " . text(oeFormatDateTime($date_report, "global", true));
        //prepare to disable form elements
        $dis_text = " disabled='disabled' ";
    ?>
<?php }
if ($show_help) {
    echo $help_icon;
}
?>
</span>

<form method='post' name='theform' id='theform' action='cqm.php?type=<?php echo attr($type_report) ;?>' onsubmit='return validateForm()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<?php
    $widthDyn = "610px";
if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
    $widthDyn = "410px";
}
?>
<table>
 <tr>
  <td scope="row" width='<?php echo attr($widthDyn); ?>'>
    <div style='float:left'>

    <table class='text'>

        <?php if ($is_amc_report) { ?>
                   <tr>
                      <td class='col-form-label'>
                            <?php echo xlt('Begin Date'); ?>:
                      </td>
                      <td>
                         <input <?php echo $dis_text; ?> type='text' name='form_begin_date' id="form_begin_date" size='20' value='<?php echo attr(oeFormatDateTime($begin_date, 0, true)); ?>' class='datepicker form-control'>
                            <?php if (empty($report_id)) { ?>
                            <?php } ?>
                      </td>
                   </tr>
        <?php } ?>

                <tr>
                        <td class='col-form-label'>
                            <?php if ($is_amc_report) { ?>
                                <?php echo xlt('End Date'); ?>:
                            <?php } else { ?>
                                <?php echo xlt('Target Date'); ?>:
                            <?php } ?>
                        </td>
                        <td>
                           <input <?php echo $dis_text; ?> type='text' name='form_target_date' id="form_target_date" size='20' value='<?php echo attr(oeFormatDateTime($target_date, 0, true)); ?>'
                                class='datepicker form-control'>
                            <?php if (empty($report_id)) { ?>
                            <?php } ?>
                        </td>
                </tr>

                <?php if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) { ?>
                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Rule Set'); ?>:
                        </td>
                        <td>
                            <select <?php echo $dis_text; ?> id='form_rule_filter' name='form_rule_filter' class='form-control'>
                            <option value='cqm' <?php echo ($rule_filter == "cqm") ? "selected" : ""; ?>>
                            <?php echo xlt('All Clinical Quality Measures (CQM)'); ?></option>
                            <option value='cqm_2011' <?php echo ($rule_filter == "cqm_2011") ? "selected" : ""; ?>>
                            <?php echo xlt('2011 Clinical Quality Measures (CQM)'); ?></option>
                            <option value='cqm_2014' <?php echo ($rule_filter == "cqm_2014") ? "selected" : ""; ?>>
                            <?php echo xlt('2014 Clinical Quality Measures (CQM)'); ?></option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($is_amc_report) { ?>
                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Rule Set'); ?>:
                        </td>
                        <td>
                            <select <?php echo $dis_text; ?> id='form_rule_filter' name='form_rule_filter' class='form-control'>

                            <?php if ($rule_filter == "amc") { //only show this when displaying old reports. Not available option for new reports ?>
                              <option value='amc' selected>
                                <?php echo xlt('All Automated Measure Calculations (AMC)'); ?></option>
                            <?php } ?>
                            <?php foreach ($amc_report_types as $key => $report_type) : ?>
                                <option value="<?php echo attr($key); ?>" <?php echo ($rule_filter == $key) ? "selected" : ""; ?>><?php echo text($report_type['ruleset_title']); ?></option>
                            <?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($type_report == "standard") { ?>
                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Rule Set'); ?>:
                        </td>
                        <td>
                            <select <?php echo $dis_text; ?> id='form_rule_filter' name='form_rule_filter' class='form-control'>
                            <option value='passive_alert' <?php echo ($rule_filter == "passive_alert") ? "selected" : ""; ?>>
                            <?php echo xlt('Passive Alert Rules'); ?></option>
                            <option value='active_alert' <?php echo ($rule_filter == "active_alert") ? "selected" : ""; ?>>
                            <?php echo xlt('Active Alert Rules'); ?></option>
                            <option value='patient_reminder' <?php echo ($rule_filter == "patient_reminder") ? "selected" : ""; ?>>
                            <?php echo xlt('Patient Reminder Rules'); ?></option>
                            </select>
                        </td>
                    </tr>
                <?php } ?>

                <?php if ($is_amc_report) { ?>
                    <input type='hidden' id='form_plan_filter' name='form_plan_filter' value=''>
                <?php } else { ?>
                    <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Plan Set'); ?>:
                        </td>
                        <td>
                                 <select <?php echo $dis_text; ?> id='form_plan_filter' name='form_plan_filter' class='form-control'>
                                 <option value=''>-- <?php echo xlt('Ignore'); ?> --</option>
                                    <?php if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) { ?>
                                   <option value='cqm' <?php echo ($plan_filter == "cqm") ? "selected" : ""; ?>>
                                        <?php echo xlt('All Official Clinical Quality Measures (CQM) Measure Groups'); ?></option>
                                   <option value='cqm_2011' <?php echo ($plan_filter == "cqm_2011") ? "selected" : ""; ?>>
                                        <?php echo xlt('2011 Official Clinical Quality Measures (CQM) Measure Groups'); ?></option>
                                   <option value='cqm_2014' <?php echo ($plan_filter == "cqm_2014") ? "selected" : ""; ?>>
                                        <?php echo xlt('2014 Official Clinical Quality Measures (CQM) Measure Groups'); ?></option>
                                    <?php } ?>
                                    <?php if ($type_report == "standard") { ?>
                                   <option value='normal' <?php echo ($plan_filter == "normal") ? "selected" : ""; ?>>
                                        <?php echo xlt('Active Plans'); ?></option>
                                    <?php } ?>
                        </td>
                    </tr>
                <?php } ?>

                <tr>
            <td class='col-form-label'>
                <?php echo xlt('Provider'); ?>:
            </td>
            <td>
                <select <?php echo $dis_text; ?> id='form_provider' name='form_provider' class='form-control'>
                    <option value=''>-- <?php echo xlt('All (Cumulative)') ?>--</option>
                    <option value='collate_outer' <?php echo ($provider == 'collate_outer') ? 'selected' : '';
                    ?>>-- <?php echo xlt('All (Collated Format A)') ?>--</option>
                    <option value='collate_inner' <?php echo ($provider == 'collate_inner') ? 'selected' : '';
                    ?>>-- <?php echo xlt('All (Collated Format B)') ?>--</option>
                    <option value='group_calculation' <?php echo ($provider == 'group_calculation') ? 'selected' : '';
                    ?>>-- <?php echo xlt('All EP/EC Group Calculation') ?>--</option>
                <?php

                 // Build a drop-down list of providers.
                 //

                 $query = "SELECT id, lname, fname FROM users WHERE " .
                  "authorized = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

                 $ures = sqlStatement($query);

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == $provider) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname'] . ", " . $urow['fname']) . "\n";
                }
                ?>
                </select>
                        </td>
        </tr>

                <tr>
                        <td class='col-form-label'>
                            <?php echo xlt('Provider Relationship'); ?>:
                        </td>
                        <td>
                                <?php

                                 // Build a drop-down list of of patient provider relationships.
                                 //
                                 echo "   <select " . $dis_text . " id='form_pat_prov_rel' name='form_pat_prov_rel' class='form-control' title='" . xla('Only applicable if a provider or collated list was chosen above. PRIMARY only selects patients that the provider is the primary provider. ENCOUNTER selects all patients that the provider has seen.') . "'>\n";
                                 echo "    <option value='primary'";
                                if ($pat_prov_rel == 'primary') {
                                    echo " selected";
                                }

                                 echo ">" . xlt('Primary') . "\n";
                                 echo "    <option value='encounter'";
                                if ($pat_prov_rel == 'encounter') {
                                    echo " selected";
                                }

                                 echo ">" . xlt('Encounter') . "\n";
                                 echo "   </select>\n";
                                ?>
                        </td>
                </tr>

                <?php if ($is_amc_report) { ?>
                  <tr>
                        <td>
                                <?php echo xlt('Number labs'); ?>:<br />
                               (<?php echo xlt('Non-electronic'); ?>)
                        </td>
                        <td>
                               <input <?php echo $dis_text; ?> type="text" id="labs_manual_entry" name="labs_manual_entry" class='form-control' value="<?php echo attr($labs_manual); ?>">
                        </td>
                  </tr>
                <?php } ?>

    </table>

    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td scope="row">
                <div class="text-center">
          <div class="btn-group" role="group">
            <?php if (empty($report_id)) { ?>
            <a href='#' id='submit_button' class='btn btn-secondary btn-save' onclick='runReport();'><?php echo xlt('Submit'); ?></a>
            <span id='status_span'></span>
            <div id='processing' style='margin:10px; display:none;'><img src='../pic/ajax-loader.gif'/></div>
                <?php if ($type_report == "cqm") { ?>
                          <a href='#' id='xmla_button' class='btn btn-secondary btn-transmit' onclick='return GenXml("false")'><?php echo xlt('Generate PQRI report (Method A) - 2011'); ?></a>
              <a href='#' id='xmlb_button' class='btn btn-secondary btn-transmit' onclick='return GenXml("true")'><?php echo xlt('Generate PQRI report (Method E) - 2011'); ?></a>
            <?php } ?>
            <?php } ?>
            <?php if (!empty($report_id)) { ?>
            <a href='#' class='btn btn-secondary btn-print' id='printbutton'><?php echo xlt('Print'); ?></a>
                <?php if ($type_report == "cqm_2014") { ?>
              <a href='#' id="genQRDA" class='btn btn-secondary btn-transmit' onclick='return downloadQRDA()'><?php echo xlt('Generate QRDA I – 2014'); ?></a>
              <a href='#' id="xmlc_button" class='btn btn-secondary btn-transmit' onclick='return GenXml("QRDA")'><?php echo xlt('Generate QRDA III - 2014'); ?></a>
            <?php } ?>
                <?php if ($back_link == "list") { ?>
              <a href='report_results.php' class='btn btn-secondary btn-transmit' onclick='top.restoreSession()'><?php echo xlt("Return To Report Results"); ?></a>
            <?php } else { ?>
              <a href='#' class='btn btn-secondary btn-transmit' onclick='top.restoreSession(); $("#theform").submit();'><?php echo xlt("Start Another Report"); ?></a>
            <?php } ?>
            <?php } ?>
          </div>
                </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>

</div>  <!-- end of search parameters -->

<br />

<?php
if (!empty($report_id)) {
    $twigContainer = new \OpenEMR\Common\Twig\TwigContainer(null, $GLOBALS['kernel']);
    $twig = $twigContainer->getTwig();
    $form_provider = $_POST['form_provider'] ?? '';

    $data = [
        'report_id' => $report_id
        , 'collate_outer' => $form_provider == 'collate_outer'
        , 'datasheet' => $dataSheet
    ];

    if ($is_cqm_report) {
        echo $twig->render('reports/cqm/cqm-results-table.html.twig', $data);
    } else if ($is_amc_report) {
        echo $twig->render('reports/cqm/amc-results-table.html.twig', $data);
    } else {
        echo $twig->render('reports/cqm/results-table.html.twig', $data);
    }
} else { ?>
<div id="instructions_text" class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to start report.'); ?>
</div>
<?php } ?>

<input type='hidden' name='form_new_report_id' id='form_new_report_id' value=''/>

</form>
</div>
<?php
$oemr_ui->oeBelowContainerDiv();
?>
</body>

</html>
