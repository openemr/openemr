<?
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists destroyed drug lots within a specified date
 // range.

 require_once("../globals.php");
 require_once("$srcdir/patient.inc");
 require_once("../drugs/drugs.inc.php");

 $form_from_date  = fixDate($_POST['form_from_date'], date('Y-01-01'));
 $form_to_date    = fixDate($_POST['form_to_date']  , date('Y-m-d'));
?>
<html>
<head>
<? html_header_show();?>
<title><? xl('Destroyed Drugs','e'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<style  type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><? xl('Destroyed Drugs','e'); ?></h2>

<form name='theform' method='post' action='destroyed_drugs_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <? xl('From','e'); ?>:
   <input type='text' name='form_from_date' id='form_from_date'
    size='10' value='<? echo $form_from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>

   &nbsp;<? xl('To','e'); ?>:
   <input type='text' name='form_to_date' id='form_to_date'
    size='10' value='<? echo $form_to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_date' border='0' alt='[?]' style='cursor:pointer'
    title='Click here to choose a date'>

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
   <? xl('Drug Name','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('NDC','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Lot','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Qty','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Date Destroyed','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Method','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Witness','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Notes','e'); ?>
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $where = "i.destroy_date >= '$form_from_date' AND " .
   "i.destroy_date <= '$form_to_date'";

  $query = "SELECT i.inventory_id, i.lot_number, i.on_hand, i.drug_id, " .
   "i.destroy_date, i.destroy_method, i.destroy_witness, i.destroy_notes, " .
   "d.name, d.ndc_number " .
   "FROM drug_inventory AS i " .
   "LEFT OUTER JOIN drugs AS d ON d.drug_id = i.drug_id " .
   "WHERE $where " .
   "ORDER BY d.name, i.drug_id, i.destroy_date, i.lot_number";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  $last_drug_id = 0;
  while ($row = sqlFetchArray($res)) {
   $drug_name       = $row['name'];
   $ndc_number      = $row['ndc_number'];
   if ($row['drug_id'] == $last_drug_id) {
    $drug_name  = '&nbsp;';
    $ndc_number = '&nbsp;';
   }
?>
 <tr>
  <td class='detail'>
   <?php echo $drug_name ?>
  </td>
  <td class='detail'>
   <?php echo $ndc_number ?>
  </td>
  <td class='detail'>
   <a href='../drugs/destroy_lot.php?drug=<?php echo $row['drug_id'] ?>&lot=<?php echo $row['inventory_id'] ?>'
    style='color:#0000ff' target='_blank'>
   <?php echo $row['lot_number'] ?>
   </a>
  </td>
  <td class='detail'>
   <?php echo $row['on_hand'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['destroy_date'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['destroy_method'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['destroy_witness'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['destroy_notes'] ?>
  </td>
 </tr>
<?php
   $last_drug_id = $row['drug_id'];
  } // end while
 } // end if
?>

</table>
</form>
</center>
<script language='JavaScript'>
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</body>
</html>
