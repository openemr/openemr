<?php
 // Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists referrals for a given date range.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");

 $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $to_date   = fixDate($_POST['form_to_date'], date('Y-m-d'));
?>
<html>
<head>
<title><? xl('Referrals','e'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>

<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>

<script type="text/javascript" src="../../library/dialog.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="JavaScript">

<?php require($GLOBALS['srcdir'] . "/restoreSession.php"); ?>

 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';

 // The OnClick handler for referral display.
 function show_referral(transid) {
  dlgopen('../patient_file/transaction/print_referral.php?transid=' + transid,
   '_blank', 550, 400);
  return false;
 }

</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<center>

<h2><?php xl('Referrals','e'); ?></h2>

<form name='theform' method='post' action='referrals_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <? xl('From','e'); ?>:
   <input type='text' size='10' name='form_from_date' id='form_from_date'
    value='<?php echo $from_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
   &nbsp;<? xl('To','e'); ?>:
   <input type='text' size='10' name='form_to_date' id='form_to_date'
    value='<?php echo $to_date ?>'
    title='<?php xl('yyyy-mm-dd','e'); ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' />
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>' />
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
  <td class="dehead" nowrap>
   <?php xl('Refer To','e'); ?>
  </td>
  <td class='dehead' nowrap>
   <?php xl('Refer Date','e'); ?>
  </td>
  <td class='dehead' nowrap>
   <?php xl('Reply Date','e'); ?>
  </td>
  <td class='dehead' nowrap>
   <?php xl('Patient','e'); ?>
  </td>
  <td class='dehead' nowrap>
   <?php xl('Reason','e'); ?>
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $query = "SELECT t.id, t.refer_date, t.reply_date, t.body, " .
    "ut.organization, " .
    "CONCAT(uf.fname,' ', uf.lname) AS referer_name, " .
    "CONCAT(p.fname,' ', p.lname) AS patient_name " .
    "FROM transactions AS t " .
    "LEFT OUTER JOIN patient_data AS p ON p.pid = t.pid " .
    "LEFT OUTER JOIN users AS ut ON ut.id = t.refer_to " .
    "LEFT OUTER JOIN users AS uf ON uf.id = t.refer_from " .
    "WHERE t.title = 'Referral' AND " .
    "t.refer_date >= '$from_date' AND t.refer_date <= '$to_date' " .
    "ORDER BY ut.organization, t.refer_date, t.id";

  // echo "<!-- $query -->\n"; // debugging
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td class='detail'>
   <?php echo $row['organization'] ?>
  </td>
  <td class='detail'>
   <a href='' onclick="return show_referral(<?php echo $row['id']; ?>)">
   <?php echo $row['refer_date']; ?>&nbsp;
   </a>
  </td>
  <td class='detail'>
   <?php echo $row['reply_date'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['patient_name'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['body'] ?>
  </td>
 </tr>
<?php
  }
 }
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
