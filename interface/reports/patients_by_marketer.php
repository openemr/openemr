<?php

/**
 * Patients by Marketer/Referral Source Report
 *
 * This report lists patients grouped by their referral source (marketer).
 * Useful for tracking marketing effectiveness and patient acquisition.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    MBC Development Team
 * @copyright Copyright (c) 2026 MBC
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc.php");
require_once("$srcdir/options.inc.php");

use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$from_date = (!empty($_POST['form_from_date'])) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-01-01');
$to_date = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility = empty($_POST['form_facility']) ? '' : $_POST['form_facility'];
$form_provider = empty($_POST['form_provider']) ? 0 : intval($_POST['form_provider']);
$form_marketer = $_POST['form_marketer'] ?? '';
$form_show_details = !empty($_POST['form_show_details']);

// In the case of CSV export only, a download will be forced.
if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=patients_by_marketer.csv");
    header("Content-Description: File Transfer");
} else {
    ?>
<html>
<head>

<title><?php echo xlt('Patients by Marketer'); ?></title>

    <?php Header::setupHeader(['datetime-picker', 'report-helper']); ?>

<script>

$(function () {
    oeFixedHeaderSetup(document.getElementById('mymaintable'));
    top.printLogSetup(document.getElementById('printbutton'));

    $('.datepicker').datetimepicker({
        <?php $datetimepicker_timepicker = false; ?>
        <?php $datetimepicker_showseconds = false; ?>
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
        margin-bottom: 10px;
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
    #report_results {
        width: 100%;
    }
}

.marketer-header {
    background-color: #f0f0f0;
    font-weight: bold;
}

.marketer-count {
    font-size: 1.1em;
    color: #333;
}

</style>

</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position: absolute; visibility: hidden; z-index: 1000;"></div>

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patients by Marketer'); ?></span>

<div id="report_parameters_daterange">
    <?php if (!(empty($to_date) && empty($from_date))) { ?>
        <?php echo xlt('Registration Date') . ': ' . text(oeFormatShortDate($from_date)) . " &nbsp; " . xlt('to{{Range}}') . " &nbsp; " . text(oeFormatShortDate($to_date)); ?>
    <?php } ?>
</div>

<form name='theform' id='theform' method='post' action='patients_by_marketer.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">

<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='70%'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Facility'); ?>:
            </td>
            <td>
                <?php dropdown_facility($form_facility, 'form_facility', true); ?>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('Provider'); ?>:
            </td>
            <td>
                <?php
                generate_form_field(['data_type' => 10, 'field_id' => 'provider', 'empty_title' => '-- All --'], ($_POST['form_provider'] ?? ''));
                ?>
            </td>
        </tr>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Marketer/Referral Source'); ?>:
            </td>
            <td>
                <input type='text' class='form-control' name='form_marketer' id='form_marketer' size='20' value='<?php echo attr($form_marketer); ?>' placeholder='<?php echo xla('Leave blank for all'); ?>'>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('Show Details'); ?>:
            </td>
            <td>
                <input type='checkbox' name='form_show_details' id='form_show_details' <?php echo $form_show_details ? 'checked' : ''; ?>>
            </td>
        </tr>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('Registration From'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($from_date)); ?>'>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input class='datepicker form-control' type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($to_date)); ?>'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class="h-100" align='left' valign='middle'>
    <table class="w-100 h-100" style='border-left: 1px solid;'>
        <tr>
            <td>
        <div class="text-center">
                  <div class="btn-group" role="group">
                    <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_csvexport").val(""); $("#form_refresh").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Submit'); ?>
                    </a>
                    <?php if (!empty($_POST['form_refresh'])) { ?>
                    <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                        <?php echo xlt('Export to CSV'); ?>
                    </a>
                      <a href='#' id='printbutton' class='btn btn-secondary btn-print'>
                            <?php echo xlt('Print'); ?>
                      </a>
                    <?php } ?>
              </div>
        </div>
            </td>
        </tr>
    </table>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

    <?php
} // end not form_csvexport

if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    // Build the query
    $sqlArrayBind = [];
    $query = "SELECT " .
        "p.pid, p.pubpid, p.fname, p.mname, p.lname, p.DOB, p.sex, " .
        "p.street, p.city, p.state, p.postal_code, " .
        "p.phone_home, p.phone_cell, p.email, " .
        "p.regdate, p.referral_source, " .
        "f.name AS facility_name " .
        "FROM patient_data AS p " .
        "LEFT JOIN facility AS f ON f.id = p.ref_providerID ";

    $where_clauses = [];

    // Date range filter (based on registration date)
    if (!empty($from_date)) {
        $where_clauses[] = "p.regdate >= ?";
        $sqlArrayBind[] = $from_date;
    }
    if (!empty($to_date)) {
        $where_clauses[] = "p.regdate <= ?";
        $sqlArrayBind[] = $to_date . ' 23:59:59';
    }

    // Facility filter
    if (!empty($form_facility)) {
        $where_clauses[] = "p.ref_providerID = ?";
        $sqlArrayBind[] = $form_facility;
    }

    // Provider filter
    if ($form_provider) {
        $where_clauses[] = "p.providerID = ?";
        $sqlArrayBind[] = $form_provider;
    }

    // Marketer filter
    if (!empty($form_marketer)) {
        $where_clauses[] = "p.referral_source LIKE ?";
        $sqlArrayBind[] = '%' . $form_marketer . '%';
    }

    if (!empty($where_clauses)) {
        $query .= "WHERE " . implode(" AND ", $where_clauses) . " ";
    }

    $query .= "ORDER BY p.referral_source, p.lname, p.fname";

    $res = sqlStatement($query, $sqlArrayBind);

    // Group results by marketer
    $marketerGroups = [];
    $totalPatients = 0;

    while ($row = sqlFetchArray($res)) {
        $marketer = $row['referral_source'] ?: '(No Referral Source)';
        if (!isset($marketerGroups[$marketer])) {
            $marketerGroups[$marketer] = [];
        }
        $marketerGroups[$marketer][] = $row;
        $totalPatients++;
    }

    // Sort by marketer name
    ksort($marketerGroups);

    if ($_POST['form_csvexport']) {
        // CSV export
        if ($form_show_details) {
            // Detailed CSV with patient info
            echo csvEscape(xl('Marketer')) . ',';
            echo csvEscape(xl('Patient ID')) . ',';
            echo csvEscape(xl('Last Name')) . ',';
            echo csvEscape(xl('First Name')) . ',';
            echo csvEscape(xl('DOB')) . ',';
            echo csvEscape(xl('Sex')) . ',';
            echo csvEscape(xl('Phone')) . ',';
            echo csvEscape(xl('Email')) . ',';
            echo csvEscape(xl('Registration Date')) . "\n";

            foreach ($marketerGroups as $marketer => $patients) {
                foreach ($patients as $row) {
                    echo csvEscape($marketer) . ',';
                    echo csvEscape($row['pubpid']) . ',';
                    echo csvEscape($row['lname']) . ',';
                    echo csvEscape($row['fname']) . ',';
                    echo csvEscape(oeFormatShortDate($row['DOB'])) . ',';
                    echo csvEscape($row['sex']) . ',';
                    echo csvEscape($row['phone_home'] ?: $row['phone_cell']) . ',';
                    echo csvEscape($row['email']) . ',';
                    echo csvEscape(oeFormatShortDate($row['regdate'])) . "\n";
                }
            }
        } else {
            // Summary CSV
            echo csvEscape(xl('Marketer')) . ',';
            echo csvEscape(xl('Patient Count')) . ',';
            echo csvEscape(xl('Percentage')) . "\n";

            foreach ($marketerGroups as $marketer => $patients) {
                $count = count($patients);
                $percentage = $totalPatients > 0 ? round(($count / $totalPatients) * 100, 1) : 0;
                echo csvEscape($marketer) . ',';
                echo csvEscape($count) . ',';
                echo csvEscape($percentage . '%') . "\n";
            }

            echo "\n";
            echo csvEscape(xl('Total')) . ',';
            echo csvEscape($totalPatients) . ',';
            echo csvEscape('100%') . "\n";
        }
    } else {
        // HTML output
        ?>

<div id="report_results">

<?php if ($form_show_details) { ?>
    <!-- Detailed view with patient lists -->
    <?php foreach ($marketerGroups as $marketer => $patients) { ?>
        <h4 class="mt-4 mb-2">
            <?php echo text($marketer); ?>
            <span class="badge badge-primary ml-2"><?php echo text(count($patients)); ?> <?php echo xlt('patients'); ?></span>
        </h4>
        <table class='table table-sm table-striped' id='mymaintable'>
            <thead class='thead-light'>
                <th><?php echo xlt('Patient ID'); ?></th>
                <th><?php echo xlt('Name'); ?></th>
                <th><?php echo xlt('DOB'); ?></th>
                <th><?php echo xlt('Sex'); ?></th>
                <th><?php echo xlt('Phone'); ?></th>
                <th><?php echo xlt('Email'); ?></th>
                <th><?php echo xlt('Registered'); ?></th>
            </thead>
            <tbody>
            <?php foreach ($patients as $row) { ?>
                <tr>
                    <td><?php echo text($row['pubpid']); ?></td>
                    <td><?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?></td>
                    <td><?php echo text(oeFormatShortDate($row['DOB'])); ?></td>
                    <td><?php echo text($row['sex']); ?></td>
                    <td><?php echo text($row['phone_home'] ?: $row['phone_cell']); ?></td>
                    <td><?php echo text($row['email']); ?></td>
                    <td><?php echo text(oeFormatShortDate($row['regdate'])); ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    <?php } ?>
<?php } else { ?>
    <!-- Summary view -->
    <table class='table' id='mymaintable'>
        <thead class='thead-light'>
            <th><?php echo xlt('Marketer / Referral Source'); ?></th>
            <th class='text-right'><?php echo xlt('Patient Count'); ?></th>
            <th class='text-right'><?php echo xlt('Percentage'); ?></th>
        </thead>
        <tbody>
        <?php foreach ($marketerGroups as $marketer => $patients) {
            $count = count($patients);
            $percentage = $totalPatients > 0 ? round(($count / $totalPatients) * 100, 1) : 0;
            ?>
            <tr>
                <td><?php echo text($marketer); ?></td>
                <td class='text-right'><?php echo text($count); ?></td>
                <td class='text-right'><?php echo text($percentage); ?>%</td>
            </tr>
        <?php } ?>
        </tbody>
        <tfoot>
            <tr class='report_totals'>
                <td><strong><?php echo xlt('Total'); ?></strong></td>
                <td class='text-right'><strong><?php echo text($totalPatients); ?></strong></td>
                <td class='text-right'><strong>100%</strong></td>
            </tr>
        </tfoot>
    </table>
<?php } ?>

</div> <!-- end of results -->
        <?php
    } // end not export
} // end if refresh or export

if (empty($_POST['form_refresh']) && empty($_POST['form_csvexport'])) {
    ?>
<div class='text'>
    <?php echo xlt('Please input search criteria above, and click Submit to view results.'); ?>
</div>
    <?php
}

if (empty($_POST['form_csvexport'])) {
    ?>

</form>
</body>

</html>
    <?php
} // end not export
?>
