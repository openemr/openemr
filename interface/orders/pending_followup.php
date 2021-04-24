<?php

/**
 * pending followup
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2019 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("../../library/patient.inc");
require_once("../../custom/code_types.inc.php");

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;
use OpenEMR\Services\FacilityService;

$facilityService = new FacilityService();

function thisLineItem($row, $codetype, $code)
{
    global $code_types;

    $provname = $row['provider_lname'];
    if (!empty($row['provider_fname'])) {
        $provname .= ', ' . $row['provider_fname'];
        if (!empty($row['provider_mname'])) {
            $provname .= ' ' . $row['provider_mname'];
        }
    }

    $crow = sqlQuery("SELECT code_text FROM codes WHERE " .
    "code_type = ? AND " .
    "code = ? LIMIT 1", array($code_types[$codetype]['id'], $code));
    $code_text = $crow['code_text'];

    if ($_POST['form_csvexport']) {
        echo csvEscape($row['patient_name'  ]) . ',';
        echo csvEscape($row['pubpid'        ]) . ',';
        echo csvEscape($row['date_ordered'  ]) . ',';
        echo csvEscape($row['procedure_name']) . ',';
        echo csvEscape($provname) . ',';
        echo csvEscape($code) . ',';
        echo csvEscape($code_text) . "\n";
    } else {
        ?>
   <tr>
    <td class="detail"><?php echo text($row['patient_name'  ]); ?></td>
    <td class="detail"><?php echo text($row['pubpid'        ]); ?></td>
    <td class="detail"><?php echo text($row['date_ordered'  ]); ?></td>
    <td class="detail"><?php echo text($row['procedure_name']); ?></td>
    <td class="detail"><?php echo text($provname);              ?></td>
    <td class="detail"><?php echo text($code);                  ?></td>
    <td class="detail"><?php echo text($code_text);             ?></td>
 </tr>
        <?php
    } // End not csv export
}

if (! AclMain::aclCheckCore('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=pending_followup.csv");
    header("Content-Description: File Transfer");
  // CSV headers:
    echo csvEscape(xl('Patient')) . ',';
    echo csvEscape(xl('ID')) . ',';
    echo csvEscape(xl('Ordered')) . ',';
    echo csvEscape(xl('Procedure')) . ',';
    echo csvEscape(xl('Provider')) . ',';
    echo csvEscape(xl('Code')) . ',';
    echo csvEscape(xl('Service')) . "\n";
} else { // not export
    ?>
<html>
<head>

    <?php Header::setupHeader(['datetime-picker']); ?>

<title><?php echo xlt('Pending Followup from Results') ?></title>

<script>
    $(function () {
        var win = top.printLogSetup ? top : opener.top;
        win.printLogSetup(document.getElementById('printbutton'));

        $('.datepicker').datetimepicker({
            <?php $datetimepicker_timepicker = false; ?>
            <?php $datetimepicker_showseconds = false; ?>
            <?php $datetimepicker_formatInput = false; ?>
            <?php require($GLOBALS['srcdir'] . '/js/xl/jquery-datetimepicker-2-5-4.js.php'); ?>
            <?php // can add any additional javascript settings to datetimepicker here; need to prepend first setting with a comma ?>
        });
    });
</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php echo xlt('Pending Followup from Results')?></h2>

<form method='post' action='pending_followup.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table border='0' cellpadding='3'>

 <tr>
  <td>
    <?php
  // Build a drop-down list of facilities.
  //
    $fres = $facilityService->getAllFacility();
    echo "   <select name='form_facility'>\n";
    echo "    <option value=''>-- All Facilities --\n";
    foreach ($fres as $frow) {
        $facid = $frow['id'];
        echo "    <option value='" . attr($facid) . "'";
        if ($facid == $form_facility) {
            echo " selected";
        }

        echo ">" . text($frow['name']) . "\n";
    }

    echo "   </select>\n";
    ?>
   &nbsp;<?php echo xlt('From:'); ?>
   <input type='text' class='datepicker' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr($form_from_date); ?>'
    title='yyyy-mm-dd'>

   &nbsp;<?php echo xlt('To{{Range}}'); ?>:
   <input type='text' class='datepicker' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr($form_to_date); ?>'
    title='yyyy-mm-dd'>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?php echo xla('Refresh') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php echo xla('Export to CSV') ?>">
   &nbsp;
   <input type='button' value='<?php echo xla('Print'); ?>' id='printbutton' />
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#dddddd">
  <td class="dehead"><?php echo xlt('Patient') ?></td>
  <td class="dehead"><?php echo xlt('ID') ?></td>
  <td class="dehead"><?php echo xlt('Ordered') ?></td>
  <td class="dehead"><?php echo xlt('Procedure') ?></td>
  <td class="dehead"><?php echo xlt('Provider') ?></td>
  <td class="dehead"><?php echo xlt('Code') ?></td>
  <td class="dehead"><?php echo xlt('Service') ?></td>
 </tr>
    <?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $sqlBindArray = array();

    $from_date = $form_from_date;
    $to_date   = $form_to_date;

    $query = "SELECT po.patient_id, po.encounter_id, po.date_ordered, " .
    "pd.pubpid, " .
    "CONCAT(pd.lname, ', ', pd.fname, ' ', pd.mname) AS patient_name, " .
    "pto.name AS procedure_name, " .
    "pts.related_code, " .
    "u1.lname AS provider_lname, u1.fname AS provider_fname, u1.mname AS provider_mname, " .
    "pr.procedure_report_id, pr.date_report, pr.report_status " .
    "FROM procedure_order AS po " .
    "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "JOIN patient_data AS pd ON pd.pid = po.patient_id " .
    "JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
    "JOIN procedure_result AS ps ON ps.procedure_report_id = pr.procedure_report_id " .
    "AND ps.abnormal != '' AND ps.abnormal != 'no' " .
    "JOIN procedure_type AS pto ON pto.procedure_type_id = po.procedure_type_id " .
    "JOIN procedure_type AS pts ON pts.procedure_type_id = ps.procedure_type_id " .
    "AND pts.related_code != '' " .
    "LEFT JOIN users AS u1 ON u1.id = po.provider_id " .
    "WHERE " .
    "po.date_ordered >= ? AND po.date_ordered <= ?";

    array_push($sqlBindArray, $from_date, $to_date);

    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= " ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, " .
    "po.date_ordered, po.procedure_order_id";

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        $patient_id = $row['patient_id'];
        $date_ordered = $row['date_ordered'];

        $relcodes = explode(';', $row['related_code']);
        foreach ($relcodes as $codestring) {
            if ($codestring === '') {
                continue;
            }

            list($codetype, $code) = explode(':', $codestring);

            $brow = sqlQuery("SELECT count(*) AS count " .
            "FROM billing AS b, form_encounter AS fe WHERE " .
            "b.pid = ? AND " .
            "b.code_type = ? AND " .
            "b.code = ? AND " .
            "b.activity = 1 AND " .
            "fe.pid = b.pid AND fe.encounter = b.encounter AND " .
            "fe.date >= ?", array($patient_id, $codetype, $code, $date_ordered . ' 00:00:00'));

            // If there was such a service, then this followup is not pending.
            if (!empty($brow['count'])) {
                continue;
            }

            thisLineItem($row, $codetype, $code);
        }
    }
} // end report generation

if (! $_POST['form_csvexport']) {
    ?>

</table>
</form>
</center>
</body>
</html>
    <?php
} // End not csv export
?>
