<?php
/**
 *
 * Copyright (C) 2010-2013 Rod Roark <rod@sunsetsystems.com>
 * Copyright (C) 2017 Brady Miller <brady.g.miller@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author  Rod Roark <rod@sunsetsystems.com>
 * @author  Brady Miller <brady.g.miller@gmail.com>
 * @link    http://www.open-emr.org
 */

use OpenEMR\Core\Header;
require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once "$srcdir/options.inc.php";

function thisLineItem($row) {
  $provname = $row['provider_lname'];
  if (!empty($row['provider_fname'])) {
    $provname .= ', ' . $row['provider_fname'];
    if (!empty($row['provider_mname'])) {
      $provname .= ' ' . $row['provider_mname'];
    }
  }

  if ($_POST['form_csvexport']) {
    echo '"' . addslashes($row['patient_name'  ]) . '",';
    echo '"' . addslashes($row['pubpid'        ]) . '",';
    echo '"' . addslashes(oeFormatShortDate($row['date_ordered'  ])) . '",';
    echo '"' . addslashes($row['organization'  ]) . '",';
    echo '"' . addslashes($provname             ) . '",';
    echo '"' . addslashes($row['priority_name' ]) . '",';
    echo '"' . addslashes($row['status_name'   ]) . '"' . "\n";
  }
  else {
?>
 <tr>
  <td class="detail"><?php echo $row['patient_name'  ]; ?></td>
  <td class="detail"><?php echo $row['pubpid'        ]; ?></td>
  <td class="detail"><?php echo oeFormatShortDate($row['date_ordered'  ]); ?></td>
  <td class="detail"><?php echo $row['organization'  ]; ?></td>
  <td class="detail"><?php echo $provname; ?></td>
  <td class="detail"><?php echo $row['priority_name' ]; ?></td>
  <td class="detail"><?php echo $row['status_name'   ]; ?></td>
 </tr>
<?php
  } // End not csv export
}

if (! acl_check('acct', 'rep')) die(xl("Unauthorized access."));

$form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
$form_to_date   = fixDate($_POST['form_to_date']  , date('Y-m-d'));
$form_facility  = $_POST['form_facility'];

if ($_POST['form_csvexport']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=pending_orders.csv");
  header("Content-Description: File Transfer");
  // CSV headers:
  echo '"' . xl('Patient') . '",';
  echo '"' . xl('ID') . '",';
  echo '"' . xl('Ordered') . '",';
  echo '"' . xl('From') . '",';
  echo '"' . xl('Procedure') . '",';
  echo '"' . xl('Provider') . '",';
  echo '"' . xl('Priority') . '",';
  echo '"' . xl('Status') . '"' . "\n";
}
else { // not export
?>
<html>
<head>

<title><?php xl('Pending Orders','e') ?></title>

<?php Header::setupHeader('datetime-picker'); ?>

<script language="JavaScript">

 $(document).ready(function() {
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

<h2><?php xl('Pending Orders','e')?></h2>

<form method='post' action='pending_orders.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <?php dropdown_facility(strip_escape_custom($form_facility), 'form_facility', false); ?>
  </td>
  <td class='control-label'>
   &nbsp;<?php echo xlt('From')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    title='yyyy-mm-dd'>
  </td>
  <td class='control-label'>
   &nbsp;<?php echo xlt('To')?>:
  </td>
  <td>
   <input type='text' class='datepicker form-control' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    title='yyyy-mm-dd'>
  </td>
 </tr>
 <tr>
  <td>
   <div class="btn-group" role="group">
    <button type='submit' class='btn btn-default btn-refresh' name='form_refresh'><?php echo xlt('Refresh'); ?></button>
    <button type='submit' class='btn btn-default btn-transmit' name='form_csvexport'><?php echo xlt('Export to CSV'); ?></button>
    <button type='button' class='btn btn-default btn-print' id='printbutton'><?php echo xlt('Print'); ?></button>
   </div>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>
 <tr bgcolor="#dddddd">
  <td class="dehead"><?php xl('Patient','e'  ) ?></td>
  <td class="dehead"><?php xl('ID','e'       ) ?></td>
  <td class="dehead"><?php xl('Ordered','e'  ) ?></td>
  <td class="dehead"><?php xl('From','e'     ) ?></td>
  <td class="dehead"><?php xl('Provider','e' ) ?></td>
  <td class="dehead"><?php xl('Priority','e' ) ?></td>
  <td class="dehead"><?php xl('Status','e'   ) ?></td>
 </tr>
<?php
} // end not export

// If generating a report.
//
if ($_POST['form_refresh'] || $_POST['form_csvexport']) {
  $from_date = $form_from_date;
  $to_date   = $form_to_date;

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
    "po.date_ordered >= '$from_date' AND po.date_ordered <= '$to_date' AND " .
    "( pr.report_status IS NULL OR pr.report_status = 'prelim' )";

  // TBD: What if preliminary and final reports for the same order?

  if ($form_facility) {
    $query .= " AND fe.facility_id = '$form_facility'";
  }
  $query .= " ORDER BY pd.lname, pd.fname, pd.mname, po.patient_id, " .
    "po.date_ordered, po.procedure_order_id";

  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
    thisLineItem($row);
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
