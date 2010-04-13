<?php
// Copyright (C) 2010 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/acl.inc");
require_once("$srcdir/formatting.inc.php");

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
    echo '"' . addslashes($row['procedure_name']) . '",';
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
  <td class="detail"><?php echo $row['procedure_name']; ?></td>
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
<?php html_header_show();?>
<title><?php xl('Pending Orders','e') ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<h2><?php xl('Pending Orders','e')?></h2>

<form method='post' action='pending_orders.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?php
  // Build a drop-down list of facilities.
  //
  $query = "SELECT id, name FROM facility ORDER BY name";
  $fres = sqlStatement($query);
  echo "   <select name='form_facility'>\n";
  echo "    <option value=''>-- All Facilities --\n";
  while ($frow = sqlFetchArray($fres)) {
    $facid = $frow['id'];
    echo "    <option value='$facid'";
    if ($facid == $form_facility) echo " selected";
    echo ">" . $frow['name'] . "\n";
  }
  echo "   </select>\n";
?>
   &nbsp;<?xl('From:','e')?>
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;To:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value="<?php xl('Refresh','e') ?>">
   &nbsp;
   <input type='submit' name='form_csvexport' value="<?php xl('Export to CSV','e') ?>">
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
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
  <td class="dehead"><?php xl('Procedure','e') ?></td>
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
    "pt1.name AS procedure_name, " .
    "u1.lname AS provider_lname, u1.fname AS provider_fname, u1.mname AS provider_mname, " .
    "u2.organization, " .
    "lop.title AS priority_name, " .
    "los.title AS status_name, " .
    "pr.procedure_report_id, pr.date_report, pr.report_status " .
    "FROM procedure_order AS po " .
    "JOIN form_encounter AS fe ON fe.pid = po.patient_id AND fe.encounter = po.encounter_id " .
    "JOIN patient_data AS pd ON pd.pid = po.patient_id " .
    "LEFT JOIN users AS u1 ON u1.id = po.provider_id " .
    "LEFT JOIN procedure_type AS pt1 ON pt1.procedure_type_id = po.procedure_type_id " .
    "LEFT JOIN users AS u2 ON u2.id = pt1.lab_id " .
    "LEFT JOIN list_options AS lop ON lop.list_id = 'ord_priority' AND lop.option_id = po.order_priority " .
    "LEFT JOIN list_options AS los ON los.list_id = 'ord_status' AND los.option_id = po.order_status " .
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

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<?php include_once("{$GLOBALS['srcdir']}/dynarch_calendar_en.inc.php"); ?>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>

</html>
<?php
} // End not csv export
?>
