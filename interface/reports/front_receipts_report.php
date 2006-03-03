<?
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists front office receipts for a given date range.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");

 $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));

 function bucks($amt) {
  return ($amt != 0.00) ? sprintf('%.2f', $amt) : '';
 }
?>
<html>
<head>
<title><? xl('Front Office Receipts','e'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for receipt display.
 function show_receipt(payid) {
  dlgopen('../patient_file/front_payment.php?receipt=1&payid=' + payid, '_blank', 550, 400);
  return false;
 }

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><? xl('Front Office Receipts','e'); ?></h2>

<form name='theform' method='post' action='front_receipts_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <? xl('From','e'); ?>:
   <input type='text' name='form_from_date' size='10' value='<? echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;<? xl('To','e'); ?>:
   <input type='text' name='form_to_date' size='10' value='<? echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
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
  <td class="dehead">
   <? xl('Time','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Patient','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('ID','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Method','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Source','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <? xl('Today','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <? xl('Previous','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <? xl('Total','e'); ?>
  </td>
 </tr>
<?
 if (true || $_POST['form_refresh']) {
  $total1 = 0.00;
  $total2 = 0.00;

  $query = "SELECT " .
   "r.id, r.dtime, r.method, r.source, r.amount1, r.amount2, " .
   "p.fname, p.mname, p.lname, p.pubpid " .
   "FROM  payments AS r " .
   "LEFT OUTER JOIN patient_data AS p ON " .
   "p.pid = r.pid " .
   "WHERE " .
   "r.dtime >= '$from_date 00:00:00' AND " .
   "r.dtime <= '$to_date 23:59:59' " .
   "ORDER BY r.dtime";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td class='detail'>
   <a href='' onclick='return show_receipt(<?php echo $row['id'] ?>)'>
   <?php echo substr($row['dtime'], 0, 16) ?>
   </a>
  </td>
  <td class='detail'>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['pubpid'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['method'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['source'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($row['amount1']) ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($row['amount2']) ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($row['amount1'] + $row['amount2']) ?>
  </td>
 </tr>
<?php
    $total1 += $row['amount1'];
    $total2 += $row['amount2'];
  }
?>

 <tr>
  <td class='dehead' colspan='8'>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td class='dehead' colspan='5'>
   <? xl('Totals','e'); ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($total1) ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($total2) ?>
  </td>
  <td class='detail' align='right'>
   <?php echo bucks($total1 + $total2) ?>
  </td>
 </tr>

<?php
 }
?>

</table>
</form>
</center>
</body>
</html>
