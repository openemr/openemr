<?
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists prescriptions and their dispensations according
 // to various input selection criteria.

 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("../drugs/drugs.inc.php");

 $form_from_date  = fixDate($_POST['form_from_date'], date('Y-01-01'));
 $form_to_date    = fixDate($_POST['form_to_date']  , date('Y-m-d'));
 $form_patient_id = trim($_POST['form_patient_id']);
 $form_drug_name  = trim($_POST['form_drug_name']);
 $form_lot_number = trim($_POST['form_lot_number']);
?>
<html>
<head>
<? html_header_show();?>
<title><? xl('Prescriptions and Dispensations','e'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for receipt display.
 function show_receipt(payid) {
  // dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
  return false;
 }

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><? xl('Prescriptions and Dispensations','e'); ?></h2>

<form name='theform' method='post' action='prescriptions_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <? xl('From','e'); ?>:
   <input type='text' name='form_from_date' size='10' value='<? echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;<? xl('To','e'); ?>:
   <input type='text' name='form_to_date' size='10' value='<? echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;<? xl('Patient ID','e'); ?>:
   <input type='text' name='form_patient_id' size='6' maxlength='6' value='<? echo $form_patient_id ?>'
    title='Optional numeric patient ID' />
   &nbsp;<? xl('Drug','e'); ?>:
   <input type='text' name='form_drug_name' size='10' maxlength='250' value='<? echo $form_drug_name ?>'
    title='Optional drug name, use % as a wildcard' />
   &nbsp;<? xl('Lot','e'); ?>:
   <input type='text' name='form_lot_number' size='10' maxlength='20' value='<? echo $form_lot_number ?>'
    title='Optional lot number, use % as a wildcard' />
   &nbsp;
   <input type='submit' name='form_refresh' value=<? xl('Refresh','e'); ?>>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class='dehead'>
   <? xl('Patient','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('ID','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('RX','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Drug Name','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('NDC','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Units','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Refills','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Instructed','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Reactions','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Dispensed','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Qty','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Manufacturer','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Lot','e'); ?>
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $where = "r.date_modified >= '$form_from_date' AND " .
   "r.date_modified <= '$form_to_date'";
  if ($form_patient_id) $where .= " AND r.patient_id = '$form_patient_id'";
  if ($form_drug_name ) $where .= " AND d.name LIKE '$form_drug_name'";
  if ($form_lot_number) $where .= " AND i.lot_number LIKE '$form_lot_number'";

  $query = "SELECT r.id, r.patient_id, " .
   "r.date_modified, r.dosage, r.route, r.interval, r.refills, " .
   "d.name, d.ndc_number, d.form, d.size, d.unit, d.reactions, " .
   "s.sale_id, s.sale_date, s.quantity, " .
   "i.manufacturer, i.lot_number, i.expiration, " .
   "p.fname, p.lname, p.mname " .
   "FROM prescriptions AS r " .
   "LEFT OUTER JOIN drugs AS d ON d.drug_id = r.drug_id " .
   "LEFT OUTER JOIN drug_sales AS s ON s.prescription_id = r.id " .
   "LEFT OUTER JOIN drug_inventory AS i ON i.inventory_id = s.inventory_id " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = r.patient_id " .
   "WHERE $where " .
   "ORDER BY p.lname, p.fname, r.patient_id, r.id, s.sale_id";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  $last_patient_id      = 0;
  $last_prescription_id = 0;
  while ($row = sqlFetchArray($res)) {
   $patient_name    = $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
   $patient_id      = $row['patient_id'];
   $prescription_id = $row['id'];
   $drug_name       = $row['name'];
   $ndc_number      = $row['ndc_number'];
   $drug_units      = $row['size'] . ' ' . $unit_array[$row['unit']];
   $refills         = $row['refills'];
   $reactions       = $row['reactions'];
   $instructed      = $row['dosage'] . ' ' . $form_array[$row['form']] . ' ' .
                      $interval_array[$row['interval']];
   if ($row['patient_id'] == $last_patient_id) {
    $patient_name = '&nbsp;';
    $patient_id   = '&nbsp;';
    if ($row['id'] == $last_prescription_id) {
     $prescription_id = '&nbsp;';
     $drug_name       = '&nbsp;';
     $ndc_number      = '&nbsp;';
     $drug_units      = '&nbsp;';
     $refills         = '&nbsp;';
     $reactions       = '&nbsp;';
     $instructed      = '&nbsp;';
    }
   }
?>
 <tr>
  <td class='detail'>
   <?php echo $patient_name ?>
  </td>
  <td class='detail'>
   <?php echo $patient_id ?>
  </td>
  <td class='detail'>
   <?php echo $prescription_id ?>
  </td>
  <td class='detail'>
   <?php echo $drug_name ?>
  </td>
  <td class='detail'>
   <?php echo $ndc_number ?>
  </td>
  <td class='detail'>
   <?php echo $drug_units ?>
  </td>
  <td class='detail'>
   <?php echo $refills ?>
  </td>
  <td class='detail'>
   <?php echo $instructed ?>
  </td>
  <td class='detail'>
   <?php echo $reactions ?>
  </td>
  <td class='detail'>
   <a href='../drugs/dispense_drug.php?sale_id=<?php echo $row['sale_id'] ?>'
    style='color:#0000ff' target='_blank'>
    <?php echo $row['sale_date'] ?>
   </a>
  </td>
  <td class='detail'>
   <?php echo $row['quantity'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['manufacturer'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['lot_number'] ?>
  </td>
 </tr>
<?php
   $last_prescription_id = $row['id'];
   $last_patient_id = $row['patient_id'];
  } // end while
 } // end if
?>

</table>
</form>
</center>
</body>
</html>
