<?php

/**
 * Pending orders.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2010-2013 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once "$srcdir/options.inc.php";

use OpenEMR\Common\Acl\AclMain;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Core\Header;

function thisLineItem($row)
{
    $provname = $row['provider_lname'];
    if (!empty($row['provider_fname'])) {
        $provname .= ', ' . $row['provider_fname'];
        if (!empty($row['provider_mname'])) {
            $provname .= ' ' . $row['provider_mname'];
        }
    }

    if ($_POST['form_csvexport']) {
        echo csvEscape($row['patient_name'  ]) . ',';
        echo csvEscape($row['pubpid'        ]) . ',';
        echo csvEscape(oeFormatShortDate($row['date_ordered'  ])) . ',';
        echo csvEscape($row['organization'  ]) . ',';
        echo csvEscape($provname) . ',';
        echo csvEscape($row['priority_name' ]) . ',';
        echo csvEscape($row['status_name'   ]) . "\n";
    } else {
        ?>
   <tr>
    <td class="detail"><?php echo text($row['patient_name'  ]); ?></td>
    <td class="detail"><?php echo text($row['pubpid'        ]); ?></td>
    <td class="detail"><?php echo text(oeFormatShortDate($row['date_ordered'  ])); ?></td>
    <td class="detail"><?php echo text($row['organization'  ]); ?></td>
    <td class="detail"><?php echo text($provname); ?></td>
    <td class="detail"><?php echo text($row['priority_name' ]); ?></td>
    <td class="detail"><?php echo text($row['status_name'   ]); ?></td>
 </tr>
        <?php
    } // End not csv export
}

if (!AclMain::aclCheckCore('acct', 'rep')) {
    die(xlt("Unauthorized access."));
}

$form_from_date = isset($_POST['form_from_date']) ? DateToYYYYMMDD($_POST['form_from_date']) : date('Y-m-d');
$form_to_date   = isset($_POST['form_to_date']) ? DateToYYYYMMDD($_POST['form_to_date']) : date('Y-m-d');
$form_facility  = $_POST['form_facility'] ?? null;

if (!empty($_POST['form_csvexport'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    header("Pragma: public");
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Content-Type: application/force-download");
    header("Content-Disposition: attachment; filename=pending_orders.csv");
    header("Content-Description: File Transfer");
  // CSV headers:
    echo csvEscape(xl('Patient')) . ',';
    echo csvEscape(xl('ID')) . ',';
    echo csvEscape(xl('Ordered')) . ',';
    echo csvEscape(xl('From')) . ',';
    echo csvEscape(xl('Procedure')) . ',';
    echo csvEscape(xl('Provider')) . ',';
    echo csvEscape(xl('Priority')) . ',';
    echo csvEscape(xl('Status')) . "\n";
} else { // not export
    ?>
<html>
<head>
    <title><?php echo xlt('Pending Orders') ?></title>

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
</head>

<body class='m-0'>
<center>

<h2><?php echo xlt('Pending Orders')?></h2>

<form method='post' action='pending_orders.php' onsubmit='return top.restoreSession()'>
<input type="hidden" name="csrf_token_form" value="<?php echo attr(CsrfUtils::collectCsrfToken()); ?>" />

<table class='border-0' cellpadding='3'>

 <tr>
  <td>
    <?php dropdown_facility($form_facility, 'form_facility', false); ?>
  </td>
  <td class='col-form-label'>
   &nbsp;<?php echo xlt('From')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo attr(oeFormatShortDate($form_from_date)); ?>'>
  </td>
  <td class='col-form-label'>
   &nbsp;<?php echo xlt('To{{Range}}')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo attr(oeFormatShortDate($form_to_date)); ?>'>
  </td>
 </tr>
 <tr>
  <td>
   <div class="btn-group" role="group">
    <button type='submit' class='btn btn-secondary btn-refresh' name='form_refresh'><?php echo xlt('Refresh'); ?></button>
    <button type='submit' class='btn btn-secondary btn-transmit' name='form_csvexport'><?php echo xlt('Export to CSV'); ?></button>
    <button type='button' class='btn btn-secondary btn-print' id='printbutton'><?php echo xlt('Print'); ?></button>
   </div>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table class='table border-0' cellpadding='1' cellspacing='2' width='98%'>
<thead class='thead-light'>
 <tr>
  <th class="dehead"><?php echo xlt('Patient'); ?></th>
  <th class="dehead"><?php echo xlt('ID'); ?></th>
  <th class="dehead"><?php echo xlt('Ordered'); ?></th>
  <th class="dehead"><?php echo xlt('From'); ?></th>
  <th class="dehead"><?php echo xlt('Provider'); ?></th>
  <th class="dehead"><?php echo xlt('Priority'); ?></th>
  <th class="dehead"><?php echo xlt('Status'); ?></th>
 </tr>
</thead>
    <?php
} // end not export

// If generating a report.
//
if (!empty($_POST['form_refresh']) || !empty($_POST['form_csvexport'])) {
    if (!CsrfUtils::verifyCsrfToken($_POST["csrf_token_form"])) {
        CsrfUtils::csrfNotVerified();
    }

    $sqlBindArray = array();
    $query = "SELECT po.patient_id, po.date_ordered, " .
    "pd.pubpid, " .
    "CONCAT(pd.lname, ', ', pd.fname, ' ', pd.mname) AS patient_name, " .
    "u1.lname AS provider_lname, u1.fname AS provider_fname, u1.mname AS provider_mname, " .
    "pp.name AS organization, " .
    "lop.title AS priority_name, " .
    "los.title AS status_name, " .
    "pr.procedure_report_id, pr.date_report, pr.report_status " .
    "FROM procedure_order AS po " .
    "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "JOIN patient_data AS pd ON pd.pid = po.patient_id " .
    "LEFT JOIN users AS u1 ON u1.id = po.provider_id " .
    "LEFT JOIN procedure_providers AS pp ON pp.ppid = po.lab_id " .
    "LEFT JOIN list_options AS lop ON lop.list_id = 'ord_priority' AND lop.option_id = po.order_priority AND lop.activity = 1 " .
    "LEFT JOIN list_options AS los ON los.list_id = 'ord_status' AND los.option_id = po.order_status AND los.activity = 1 " .
    "LEFT JOIN procedure_report AS pr ON pr.procedure_order_id = po.procedure_order_id " .
    "WHERE " .
    "po.date_ordered >= ? AND po.date_ordered <= ? AND " .
    "( pr.report_status IS NULL OR pr.report_status = 'prelim' )";
    array_push($sqlBindArray, $form_from_date, $form_to_date);

  // TBD: What if preliminary and final reports for the same order?

    if ($form_facility) {
        $query .= " AND fe.facility_id = ?";
        array_push($sqlBindArray, $form_facility);
    }

    $query .= " ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, " .
    "po.date_ordered, po.procedure_order_id";

    $res = sqlStatement($query, $sqlBindArray);
    while ($row = sqlFetchArray($res)) {
        thisLineItem($row);
    }
} // end report generation

if (empty($_POST['form_csvexport'])) {
    ?>

</table>
</form>
</center>
</body>

</html>
    <?php
} // End not csv export
?>
