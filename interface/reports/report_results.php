<?php

/**
 * Generic script to list stored reports. Part of the module to allow the tracking,
 * storing, and viewing of reports.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2012-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once "$srcdir/options.inc.php";
require_once "$srcdir/clinical_rules.php";
require_once "$srcdir/report_database.inc";

use OpenEMR\ClinicialDecisionRules\AMC\CertificationReportTypes;
use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('patients', 'med')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Report Results/History")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_begin_date = DateTimeToYYYYMMDDHHMMSS($_POST['form_begin_date'] ?? '');
$form_end_date = DateTimeToYYYYMMDDHHMMSS($_POST['form_end_date'] ?? '');
?>

<html>

<head>

    <title><?php echo xlt('Report Results/History'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

    <script>
        $(function () {
            $('.datepicker').datetimepicker({
                <?php $datetimepicker_timepicker = true; ?>
                <?php $datetimepicker_showseconds = true; ?>
                <?php $datetimepicker_formatInput = true; ?>
                <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
                <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
            });
        });
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
               margin-top: 0;
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

<span class='title'><?php echo xlt('Report History/Results'); ?></span>

<form method='post' name='theform' id='theform' action='report_results.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<table>
 <tr>
  <td width='470px'>
    <div class="float-left">

    <table class='text'>

                   <tr>
                      <td class='col-form-label'>
                            <?php echo xlt('Begin Date'); ?>:
                      </td>
                      <td>
                         <input type='text' name='form_begin_date' id='form_begin_date' size='20' value='<?php echo attr(oeFormatDateTime($form_begin_date, 0, true)); ?>'
                            class='datepicker form-control' />
                      </td>
                   </tr>

                <tr>
                        <td class='col-form-label'>
                                <?php echo xlt('End Date'); ?>:
                        </td>
                        <td>
                           <input type='text' name='form_end_date' id='form_end_date' size='20' value='<?php echo attr(oeFormatDateTime($form_end_date, 0, true)); ?>'
                                class='datepicker form-control' />
                        </td>
                </tr>
    </table>
    </div>

  </td>
  <td class='h-100' align='left' valign='middle'>
    <table class='w-100 h-100' style='border-left: 1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
            <a href='#' id='search_button' class='btn btn-secondary btn-search' onclick='top.restoreSession(); $("#theform").submit()'>
                <?php echo xlt('Search'); ?>
            </a>
            <a href='#' id='refresh_button' class='btn btn-secondary btn-refresh' onclick='top.restoreSession(); $("#theform").submit()'>
                <?php echo xlt('Refresh'); ?>
            </a>
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



<div id="report_results">
<table class='table'>

 <thead class='thead-light'>
  <th class='text-center'>
    <?php echo xlt('Title'); ?>
  </th>

  <th class='text-center'>
    <?php echo xlt('Date'); ?>
  </th>

  <th class='text-center'>
    <?php echo xlt('Status'); ?>
  </th>

 </thead>
 <tbody>  <!-- added for better print-ability -->
<?php

$amc_report_types = CertificationReportTypes::getReportTypeRecords();

$res = listingReportDatabase($form_begin_date, $form_end_date);
while ($row = sqlFetchArray($res)) {
  // Figure out the title and link
    if ($row['type'] == "cqm") {
        if (!$GLOBALS['enable_cqm']) {
            continue;
        }

        $type_title = xl('Clinical Quality Measures (CQM)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif ($row['type'] == "cqm_2011") {
        if (!$GLOBALS['enable_cqm']) {
            continue;
        }

        $type_title = xl('2011 Clinical Quality Measures (CQM)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif ($row['type'] == "cqm_2014") {
        if (!$GLOBALS['enable_cqm']) {
            continue;
        }

        $type_title = xl('2014 Clinical Quality Measures (CQM)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif (CertificationReportTypes::isAMCReportType($row['type'])) {
        if (!$GLOBALS['enable_amc']) {
            continue;
        }
        $record = $amc_report_types[$row['type']];
        $type_title = $record['ruleset_title'];
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif ($row['type'] == "process_reminders") {
        if (!$GLOBALS['enable_cdr']) {
            continue;
        }

        $type_title = xl('Processing Patient Reminders');
        $link = "../batchcom/batch_reminders.php?report_id=" . attr_url($row["report_id"]);
    } elseif ($row['type'] == "process_send_reminders") {
        if (!$GLOBALS['enable_cdr']) {
            continue;
        }

        $type_title = xl('Processing and Sending Patient Reminders');
        $link = "../batchcom/batch_reminders.php?report_id=" . attr_url($row["report_id"]);
    } elseif ($row['type'] == "passive_alert") {
        if (!$GLOBALS['enable_cdr']) {
            continue;
        }

        $type_title = xl('Standard Measures (Passive Alerts)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif ($row['type'] == "active_alert") {
        if (!$GLOBALS['enable_cdr']) {
            continue;
        }

        $type_title = xl('Standard Measures (Active Alerts)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } elseif ($row['type'] == "patient_reminder") {
        if (!$GLOBALS['enable_cdr']) {
            continue;
        }

        $type_title = xl('Standard Measures (Patient Reminders)');
        $link = "cqm.php?report_id=" . attr_url($row["report_id"]) . "&back=list";
    } else {
        // Not identified, so give an unknown title
        $type_title = xl('Unknown') . "-" . $row['type'];
        $link = "";
    }
    ?>
<tr>
    <?php if ($row["progress"] == "complete") { ?>
      <td class='text-center'><a href='<?php echo $link; ?>' onclick='top.restoreSession()'><?php echo text($type_title); ?></a></td>
    <?php } else { ?>
      <td class='text-center'><?php echo text($type_title); ?></td>
    <?php } ?>
  <td class='text-center'><?php echo text(oeFormatDateTime($row["date_report"], "global", true)); ?></td>
    <?php if ($row["progress"] == "complete") { ?>
      <td class='text-center'><?php echo xlt("Complete") . " (" . xlt("Processing Time") . ": " . text($row['report_time_processing']) . " " . xlt("Minutes") . ")"; ?></td>
    <?php } else { ?>
      <td class='text-center'><?php echo xlt("Pending") . " (" . text($row["progress_items"]) . " / " . text($row["total_items"]) . " " . xlt("Patients Processed") . ")"; ?></td>
    <?php } ?>

</tr>

    <?php
} // $row = sqlFetchArray($res) while
?>
</tbody>
</table>
</div>  <!-- end of search results -->

</form>

</body>

</html>
