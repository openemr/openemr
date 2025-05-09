<?php

/**
 * This module shows relative insurance usage by unique patients
 * that are seen within a given time period.  Each patient that had
 * a visit is counted only once, regardless of how many visits.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Header;

if (!AclMain::aclCheckCore('acct', 'rep_a')) {
    echo (new TwigContainer(null, $GLOBALS['kernel']))->getTwig()->render('core/unauthorized.html.twig', ['pageTitle' => xl("Patient Insurance Distribution")]);
    exit;
}

if (!empty($_POST)) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }
}

$form_from_date = (!empty($_POST['form_from_date'])) ?  DateToYYYYMMDD($_POST['form_from_date']) : '';
$form_to_date   = (!empty($_POST['form_to_date'])) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');

if (!empty($_POST['form_csvexport'])) {
    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=insurance_distribution.csv");
    header("Content-Description: File Transfer");
    // CSV headers:
    if (true) {
        echo csvEscape("Insurance") . ',';
        echo csvEscape("Charges") . ',';
        echo csvEscape("Visits") . ',';
        echo csvEscape("Patients") . ',';
        echo csvEscape("Pt Pct") . "\n";
    }
} else {
    ?>
<html>
<head>

<title><?php echo xlt('Patient Insurance Distribution'); ?></title>

    <?php Header::setupHeader('datetime-picker'); ?>

<script>
    $(function () {
    var win = top.printLogSetup ? top : opener.top;
    win.printLogSetup(document.getElementById('printbutton'));

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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Patient Insurance Distribution'); ?></span>

<div id="report_parameters_daterange">
    <?php echo text(oeFormatShortDate($form_from_date)) . " &nbsp; " . xlt("to{{Range}}") . " &nbsp; " . text(oeFormatShortDate($form_to_date)); ?>
</div>

<form name='theform' method='post' action='insurance_allocation_report.php' id='theform' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<div id="report_parameters">
<input type='hidden' name='form_refresh' id='form_refresh' value=''/>
<input type='hidden' name='form_csvexport' id='form_csvexport' value=''/>

<table>
 <tr>
  <td width='410px'>
    <div style='float:left'>

    <table class='text'>
        <tr>
            <td class='col-form-label'>
                <?php echo xlt('From'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
            </td>
            <td class='col-form-label'>
                <?php echo xlt('To{{Range}}'); ?>:
            </td>
            <td>
               <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
            </td>
        </tr>
    </table>

    </div>

  </td>
  <td class='h-100' valign='middle'>
    <table class='w-100 h-100' style='border-left:1px solid;'>
        <tr>
            <td>
                <div class="text-center">
          <div class="btn-group" role="group">
                      <a href='#' class='btn btn-secondary btn-save' onclick='$("#form_refresh").attr("value","true"); $("#form_csvexport").val(""); $("#theform").submit();'>
                            <?php echo xlt('Submit'); ?>
                      </a>
                        <?php if (!empty($_POST['form_refresh'])) { ?>
                        <a href='#' class='btn btn-secondary btn-print' id='printbutton'>
                                <?php echo xlt('Print'); ?>
                        </a>
                        <a href='#' class='btn btn-secondary btn-transmit' onclick='$("#form_csvexport").attr("value","true"); $("#theform").submit();'>
                            <?php echo xlt('Export to CSV'); ?>
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

</form>
</div> <!-- end parameters -->

<div id="report_results">
<table class='table'>

 <thead class='thead-light'>
 <th> <?php echo xlt('Primary Insurance'); ?> </th>
 <th> <?php echo xlt('Charges'); ?> </th>
 <th> <?php echo xlt('Visits'); ?> </th>
 <th> <?php echo xlt('Patients'); ?> </th>
 <th> <?php echo xlt('Pt %'); ?> </th>
 </thead>
 <tbody>
    <?php
} // end not export
if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    $query = "SELECT b.pid, b.encounter, SUM(b.fee) AS charges, " .
    "MAX(fe.date) AS date " .
    "FROM form_encounter AS fe, billing AS b " .
    "WHERE fe.date >= ? AND fe.date <= ? " .
    "AND b.pid = fe.pid AND b.encounter = fe.encounter " .
    "AND b.code_type != 'COPAY' AND b.activity > 0 AND b.fee != 0 " .
    "GROUP BY b.pid, b.encounter ORDER BY b.pid, b.encounter";

    $res = sqlStatement($query, array((!empty($form_from_date)) ? $form_from_date : '0000-00-00', $form_to_date));
    $insarr = array();
    $prev_pid = 0;
    $patcount = 0;

    while ($row = sqlFetchArray($res)) {
        $patient_id = $row['pid'];
        $encounter_date = $row['date'];
        $irow = sqlQuery("SELECT insurance_companies.name " .
        "FROM insurance_data, insurance_companies WHERE " .
        "insurance_data.pid = ? AND " .
        "insurance_data.type = 'primary' AND " .
        "(insurance_data.date <= ? OR insurance_data.date IS NULL) AND " .
        "insurance_companies.id = insurance_data.provider " .
        "ORDER BY insurance_data.date DESC LIMIT 1", array($patient_id, $encounter_date));
        $plan = (!empty($irow['name'])) ? $irow['name'] : '-- No Insurance --';
        $insarr[$plan]['visits'] = $insarr[$plan]['visits'] ?? null;
        $insarr[$plan]['visits'] += 1;
        $insarr[$plan]['charges'] = $insarr[$plan]['charges'] ?? null;
        $insarr[$plan]['charges'] += sprintf('%0.2f', $row['charges']);
        if ($patient_id != $prev_pid) {
            ++$patcount;
            $insarr[$plan]['patients'] =  $insarr[$plan]['patients'] ?? null;
            $insarr[$plan]['patients'] += 1;
            $prev_pid = $patient_id;
        }
    }

    ksort($insarr);

    foreach ($insarr as $key => $val) {
        if ($_POST['form_csvexport']) {
            echo csvEscape($key)                                                . ',';
            echo csvEscape(oeFormatMoney($val['charges']))                      . ',';
            echo csvEscape($val['visits'])                                      . ',';
            echo csvEscape($val['patients'])                                    . ',';
            echo csvEscape(sprintf("%.1f", $val['patients'] * 100 / $patcount)) . "\n";
        } else {
            ?>
     <tr>
      <td>
            <?php echo text($key); ?>
  </td>
  <td>
            <?php echo text(oeFormatMoney($val['charges'])); ?>
  </td>
  <td>
            <?php echo text($val['visits']); ?>
  </td>
  <td>
            <?php echo text($val['patients']); ?>
  </td>
  <td>
            <?php printf("%.1f", $val['patients'] * 100 / $patcount) ?>
  </td>
 </tr>
            <?php
        } // end not export
    } // end while
} // end if

if (empty($_POST['form_csvexport'])) {
    ?>

</tbody>
</table>
</div> <!-- end of results -->

</body>

</html>
    <?php
} // end not export
?>
