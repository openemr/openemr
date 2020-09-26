<?php

/**
 * CDR reports.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

// TODO: This needs a complete makeover


require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/report_database.inc";

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

// See if showing an old report or creating a new report
$report_id = (isset($_GET['report_id'])) ? trim($_GET['report_id']) : "";

// Collect the back variable, if pertinent
$back_link = (isset($_GET['back'])) ? trim($_GET['back']) : "";

// If showing an old report, then collect information
if (!empty($report_id)) {
    $report_view = collectReportDatabase($report_id);
    $date_report = $report_view['date_report'];
    $type_report = $report_view['type'];

    $type_report = (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014")  || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2") ||
                  ($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) ? $type_report : "standard";
    $rule_filter = $report_view['type'];

    if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014")  || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) {
        $begin_date = $report_view['date_begin'];
        $labs_manual = $report_view['labs_manual'];
    }

    $target_date = $report_view['date_target'];
    $plan_filter = $report_view['plan'];
    $organize_method = $report_view['organize_mode'];
    $provider  = $report_view['provider'];
    $pat_prov_rel = $report_view['pat_prov_rel'];
    $dataSheet = json_decode($report_view['data'], true);
} else {
  // Collect report type parameter (standard, amc, cqm)
  // Note that need to convert amc_2011 and amc_2014 to amc and cqm_2011 and cqm_2014 to cqm
  // to simplify for when submitting for a new report.
    $type_report = (isset($_GET['type'])) ? trim($_GET['type']) : "standard";

    if (($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
        $type_report = "cqm";
    }

    if (($type_report == "amc_2011") || ($type_report == "amc_2014")  || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) {
        $type_report = "amc";
    }

  // Collect form parameters (set defaults if empty)
    if ($type_report == "amc") {
        $begin_date = (isset($_POST['form_begin_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_begin_date'])) : "";
        $labs_manual = (isset($_POST['labs_manual_entry'])) ? trim($_POST['labs_manual_entry']) : "0";
    }

    $target_date = (isset($_POST['form_target_date'])) ? DateTimeToYYYYMMDDHHMMSS(trim($_POST['form_target_date'])) : date('Y-m-d H:i:s');
    $rule_filter = (isset($_POST['form_rule_filter'])) ? trim($_POST['form_rule_filter']) : "";
    $plan_filter = (isset($_POST['form_plan_filter'])) ? trim($_POST['form_plan_filter']) : "";
    $organize_method = (empty($plan_filter)) ? "default" : "plans";
    $provider  = trim($_POST['form_provider'] ?? '');
    $pat_prov_rel = (empty($_POST['form_pat_prov_rel'])) ? "primary" : trim($_POST['form_pat_prov_rel']);
}
?>

<html>

<head>

<?php if ($type_report == "standard") { ?>
  <title><?php echo xlt('Standard Measures'); ?></title>
<?php } ?>

<?php if ($type_report == "cqm") { ?>
  <title><?php echo xlt('Clinical Quality Measures (CQM)'); ?></title>
<?php } ?>
<?php if ($type_report == "cqm_2011") { ?>
  <title><?php echo xlt('Clinical Quality Measures (CQM) - 2011'); ?></title>
<?php } ?>
<?php if ($type_report == "cqm_2014") { ?>
  <title><?php echo xlt('Clinical Quality Measures (CQM) - 2014'); ?></title>
<?php } ?>

<?php if ($type_report == "amc") { ?>
  <title><?php echo xlt('Automated Measure Calculations (AMC)'); ?></title>
<?php } ?>
<?php if ($type_report == "amc_2011") { ?>
  <title><?php echo xlt('Automated Measure Calculations (AMC) - 2011'); ?></title>
<?php } ?>
<?php if ($type_report == "amc_2014_stage1") { ?>
  <title><?php echo xlt('Automated Measure Calculations (AMC) - 2014 Stage I'); ?></title>
<?php } ?>
<?php if ($type_report == "amc_2014_stage2") { ?>
  <title><?php echo xlt('Automated Measure Calculations (AMC) - 2014 Stage II'); ?></title>
<?php } ?>

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
        <?php if ((empty($report_id)) && (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2"))) { ?>
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

<?php if ($type_report == "amc") { ?>
    <?php echo xlt('Automated Measure Calculations (AMC)'); ?>
<?php } ?>
<?php if ($type_report == "amc_2011") { ?>
    <?php echo xlt('Automated Measure Calculations (AMC) - 2011'); ?>
<?php } ?>
<?php if ($type_report == "amc_2014_stage1") { ?>
    <?php echo xlt('Automated Measure Calculations (AMC) - 2014 Stage I'); ?>
<?php } ?>
<?php if ($type_report == "amc_2014_stage2") { ?>
    <?php echo xlt('Automated Measure Calculations (AMC) - 2014 Stage II'); ?>
<?php } ?>

<?php $dis_text = ''; ?>
<?php if (!empty($report_id)) { ?>
    <?php echo " - " . xlt('Date of Report') . ": " . text(oeFormatDateTime($date_report, "global", true));
        //prepare to disable form elements
        $dis_text = " disabled='disabled' ";
    ?>
<?php } ?>
</span>

<form method='post' name='theform' id='theform' action='cqm.php?type=<?php echo attr($type_report) ;?>' onsubmit='return validateForm()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<?php
    $widthDyn = "470px";
if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
    $widthDyn = "410px";
}
?>
<table>
 <tr>
  <td scope="row" width='<?php echo attr($widthDyn); ?>'>
    <div style='float:left'>

    <table class='text'>

        <?php if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) { ?>
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
                            <?php if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) { ?>
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

                <?php if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) { ?>
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

                            <option value='amc_2011' <?php echo ($rule_filter == "amc_2011") ? "selected" : ""; ?>>
                            <?php  echo xlt('2011 Automated Measure Calculations (AMC)'); ?></option>
                            <option value='amc_2014_stage1' <?php echo ($rule_filter == "amc_2014_stage1") ? "selected" : ""; ?>>
                            <?php echo xlt('2014 Automated Measure Calculations (AMC) - Stage I'); ?></option>
                            <option value='amc_2014_stage2' <?php echo ($rule_filter == "amc_2014_stage2") ? "selected" : ""; ?>>
                            <?php echo xlt('2014 Automated Measure Calculations (AMC) - Stage II'); ?></option>
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

                <?php if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) { ?>
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
                <?php

                 // Build a drop-down list of providers.
                 //

                 $query = "SELECT id, lname, fname FROM users WHERE " .
                  "authorized = 1 ORDER BY lname, fname"; //(CHEMED) facility filter

                 $ures = sqlStatement($query);

                 echo "   <select " . $dis_text . " id='form_provider' name='form_provider' class='form-control'>\n";
                 echo "    <option value=''>-- " . xlt('All (Cumulative)') . " --\n";

                                 echo "    <option value='collate_outer'";
                if ($provider == 'collate_outer') {
                    echo " selected";
                }

                                 echo ">-- " . xlt('All (Collated Format A)') . " --\n";

                                 echo "    <option value='collate_inner'";
                if ($provider == 'collate_inner') {
                    echo " selected";
                }

                                 echo ">-- " . xlt('All (Collated Format B)') . " --\n";

                while ($urow = sqlFetchArray($ures)) {
                    $provid = $urow['id'];
                    echo "    <option value='" . attr($provid) . "'";
                    if ($provid == $provider) {
                        echo " selected";
                    }

                    echo ">" . text($urow['lname'] . ", " . $urow['fname']) . "\n";
                }

                 echo "   </select>\n";

                ?>
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

                <?php if (($type_report == "amc") || ($type_report == "amc_2011") || ($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) { ?>
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
    ?>


<div id="report_results">
<table class="table">

<thead class='thead-light'>
 <th>
    <?php echo xlt('Title'); ?>
 </th>

 <th>
    <?php
    if ($type_report == 'cqm' || $type_report == 'cqm_2011' || $type_report == 'cqm_2014') {
        echo xlt('Initial Patient Population');
    } else {
        echo xlt('Total Patients');
    }
    ?>
  </th>

  <th>
    <?php if ($type_report == "amc") { ?>
        <?php echo xlt('Denominator'); ?></a>
    <?php } else { ?>
        <?php echo xlt('Applicable Patients') . ' (' . xlt('Denominator') . ')'; ?></a>
    <?php } ?>
  </th>

    <?php if ($type_report != "amc") { ?>
   <th>
        <?php echo xlt('Denominator Exclusion'); ?></a>
   </th>
    <?php }?>
    <?php if ($type_report == 'cqm' || $type_report == 'cqm_2011' || $type_report == 'cqm_2014') {?>
   <th>
        <?php echo xlt('Denominator Exception'); ?></a>
   </th>
    <?php } ?>

  <th>
    <?php if ($type_report == "amc") { ?>
        <?php echo xlt('Numerator'); ?></a>
    <?php } else { ?>
        <?php echo xlt('Passed Patients') . ' (' . xlt('Numerator') . ')'; ?></a>
    <?php } ?>
  </th>

  <th>
    <?php if ($type_report == "amc") { ?>
        <?php echo xlt('Failed'); ?></a>
    <?php } else { ?>
        <?php echo xlt('Failed Patients'); ?></a>
    <?php } ?>
  </th>

  <th>
    <?php echo xlt('Performance Percentage'); ?></a>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
    <?php

    if (empty($dataSheet)) {
        $dataSheet = [];
    }
    $firstProviderFlag = true;
    $firstPlanFlag = true;
    $existProvider = false;
    foreach ($dataSheet as $row) {
        ?>

<tr>

        <?php
        if (isset($row['is_main']) || isset($row['is_sub'])) {
            echo "<td class='detail'>";
            if (isset($row['is_main'])) {
                // is_sub is a special case of is_main whereas total patients, denominator, and excluded patients are taken
                // from is_main prior to it. So, need to store denominator patients from is_main for subsequent is_sub
                // to calculate the number of patients that failed.
                // Note that exlusion in the standard rules is not the same as in the cqm/amd and should not be in calculation
                // as is in the cqm/amc rules.
                $main_pass_filter = $row['pass_filter'];

                echo "<b>" . generate_display_field(array('data_type' => '1','list_id' => 'clinical_rules'), $row['id']) . "</b>";

                $tempCqmAmcString = "";
                if (($type_report == "cqm") || ($type_report == "cqm_2011") || ($type_report == "cqm_2014")) {
                    if (!empty($row['cqm_pqri_code'])) {
                        $tempCqmAmcString .= " " . xlt('PQRI') . ":" . text($row['cqm_pqri_code']) . " ";
                    }

                    if (!empty($row['cqm_nqf_code'])) {
                        $tempCqmAmcString .= " " . xlt('NQF') . ":" . text($row['cqm_nqf_code']) . " ";
                    }
                }

                if ($type_report == "amc") {
                    if (!empty($row['amc_code'])) {
                        $tempCqmAmcString .= " " . xlt('AMC-2011') . ":" . text($row['amc_code']) . " ";
                    }

                    if (!empty($row['amc_code_2014'])) {
                        $tempCqmAmcString .= " " . xlt('AMC-2014') . ":" . text($row['amc_code_2014']) . " ";
                    }
                }

                if ($type_report == "amc_2011") {
                    if (!empty($row['amc_code'])) {
                        $tempCqmAmcString .= " " . xlt('AMC-2011') . ":" . text($row['amc_code']) . " ";
                    }
                }

                if (($type_report == "amc_2014_stage1") || ($type_report == "amc_2014_stage2")) {
                    if (!empty($row['amc_code_2014'])) {
                        $tempCqmAmcString .= " " . xlt('AMC-2014') . ":" . text($row['amc_code_2014']) . " ";
                    }
                }

                if (!empty($tempCqmAmcString)) {
                    echo "(" . $tempCqmAmcString . ")";
                }

                if (!(empty($row['concatenated_label']))) {
                    echo ", " . xlt($row['concatenated_label']) . " ";
                }
            } else { // isset($row['is_sub'])
                echo generate_display_field(array('data_type' => '1','list_id' => 'rule_action_category'), $row['action_category']);
                echo ": " . generate_display_field(array('data_type' => '1','list_id' => 'rule_action'), $row['action_item']);
            }

            echo "</td>";

            if ($type_report == 'cqm' || $type_report == 'cqm_2011' || $type_report == 'cqm_2014') {
                echo "<td align='center'>" . text($row['initial_population']) . "</td>";
            } else {
                echo "<td align='center'>" . text($row['total_patients']) . "</td>";
            }

            if (isset($row['itemized_test_id']) && ($row['pass_filter'] > 0)) {
                echo "<td align='center'><a href='../main/finder/patient_select.php?from_page=cdr_report&pass_id=all&report_id=" . attr_url($report_id) . "&itemized_test_id=" . attr_url($row['itemized_test_id']) . "&numerator_label=" . attr_url($row['numerator_label'] ?? '') . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($row['pass_filter']) . "</a></td>";
            } else {
                echo "<td align='center'>" . text($row['pass_filter']) . "</td>";
            }

            if ($type_report != "amc") {
                // Note that amc will likely support in excluded items in the future for MU2
                if (($type_report != "standard") && isset($row['itemized_test_id']) && ($row['excluded'] > 0)) {
                    // Note standard reporting exluded is different than cqm/amc and will not support itemization
                    echo "<td align='center'><a href='../main/finder/patient_select.php?from_page=cdr_report&pass_id=exclude&report_id=" . attr_url($report_id) . "&itemized_test_id=" . attr_url($row['itemized_test_id']) . "&numerator_label=" . attr_url($row['numerator_label'] ?? '') . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($row['excluded']) . "</a></td>";
                } else {
                    echo "<td align='center'>" . text($row['excluded']) . "</td>";
                }
            }

            if ($type_report == 'cqm' || $type_report == 'cqm_2011' || $type_report == 'cqm_2014') {
                // Note that amc will likely support in exception items in the future for MU2
                if (isset($row['itemized_test_id']) && ($row['exception'] > 0)) {
                   // Note standard reporting exluded is different than cqm/amc and will not support itemization
                    echo "<td align='center'><a href='../main/finder/patient_select.php?from_page=cdr_report&pass_id=exception&report_id=" . attr_url($report_id) . "&itemized_test_id=" . attr_url($row['itemized_test_id']) . "&numerator_label=" . attr_url($row['numerator_label'] ?? '') . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($row['exception']) . "</a></td>";
                } else {
                     echo "<td align='center'>" . text($row['exception']) . "</td>";
                }
            }

            if (isset($row['itemized_test_id']) && ($row['pass_target'] > 0)) {
                echo "<td align='center'><a href='../main/finder/patient_select.php?from_page=cdr_report&pass_id=pass&report_id=" . attr_url($report_id) . "&itemized_test_id=" . attr_url($row['itemized_test_id']) . "&numerator_label=" . attr_url($row['numerator_label'] ?? '') . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($row['pass_target']) . "</a></td>";
            } else {
                echo "<td align='center'>" . text($row['pass_target']) . "</td>";
            }

            $failed_items = 0;
            if (isset($row['is_main'])) {
                if ($type_report == "standard") {
                    // Excluded is not part of denominator in standard rules so do not use in calculation
                    $failed_items = $row['pass_filter'] - $row['pass_target'];
                } else {
                    $failed_items = $row['pass_filter'] - $row['pass_target'] - $row['excluded'];
                }
            } else { // isset($row['is_sub'])
                // Excluded is not part of denominator in standard rules so do not use in calculation
                $failed_items = $main_pass_filter - $row['pass_target'];
            }

            if (isset($row['itemized_test_id']) && ($failed_items > 0)) {
                echo "<td align='center'><a href='../main/finder/patient_select.php?from_page=cdr_report&pass_id=fail&report_id=" . attr_url($report_id) . "&itemized_test_id=" . attr_url($row['itemized_test_id']) . "&numerator_label=" . attr_url($row['numerator_label'] ?? '') . "&csrf_token_form=" . attr_url(CsrfUtils::collectCsrfToken()) . "' onclick='top.restoreSession()'>" . text($failed_items) . "</a></td>";
            } else {
                echo "<td align='center'>" . text($failed_items) . "</td>";
            }

            echo "<td align='center'>" . text($row['percentage']) . "</td>";
        } elseif (isset($row['is_provider'])) {
           // Display the provider information
            if (!$firstProviderFlag && $_POST['form_provider'] == 'collate_outer') {
                echo "<tr><td>&nbsp</td></tr>";
            }

            echo "<td class='detail' align='center'><b>";
            echo xlt("Provider") . ": " . text($row['prov_lname']) . "," . text($row['prov_fname']);
            if (!empty($row['npi']) || !empty($row['federaltaxid'])) {
                echo " (";
                if (!empty($row['npi'])) {
                    echo " " . xlt('NPI') . ":" . text($row['npi']) . " ";
                }

                if (!empty($row['federaltaxid'])) {
                    echo " " . xlt('TID') . ":" . text($row['federaltaxid']) . " ";
                }

                   echo ")";
            }

               echo "</b></td>";
               $firstProviderFlag = false;
               $existProvider = true;
        } else { // isset($row['is_plan'])
            if (!$firstPlanFlag && $_POST['form_provider'] != 'collate_outer') {
                echo "<tr><td>&nbsp</td></tr>";
            }

            echo "<td class='detail' align='center'><b>";
            echo xlt("Plan") . ": ";
            echo generate_display_field(array('data_type' => '1','list_id' => 'clinical_plans'), $row['id']);
            if (!empty($row['cqm_measure_group'])) {
                echo " (" . xlt('Measure Group Code') . ": " . text($row['cqm_measure_group']) . ")";
            }

            echo "</b></td>";
            $firstPlanFlag = false;
        }
        ?>
 </tr>

        <?php
    }
    ?>
</tbody>
</table>
</div>  <!-- end of search results -->
<?php } else { ?>
<div id="instructions_text" class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to start report.'); ?>
</div>
<?php } ?>

<input type='hidden' name='form_new_report_id' id='form_new_report_id' value=''/>

</form>

</body>

</html>
