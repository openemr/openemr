<?
 // Copyright (C) 2006 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report lists patients that were seen within a given date
 // range.

 include_once("../globals.php");
 include_once("$srcdir/patient.inc");

 $from_date = fixDate($_POST['form_from_date'], date('Y-01-01'));
 $to_date   = fixDate($_POST['form_to_date'], date('Y-12-31'));
?>
<html>
<head>
<title><? xl('Patient List','e'); ?></title>
<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">
 var mypcc = '<? echo $GLOBALS['phone_country_code'] ?>';
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><? xl('Patient List','e'); ?></h2>

<form name='theform' method='post' action='patient_list.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
   <? xl('Visits From','e'); ?>:
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

<table border='0' cellpadding='1' cellspacing='3' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <? xl('Last Visit','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Patient','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Street','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('City','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('State','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Zip','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Home Phone','e'); ?>
  </td>
  <td class='dehead'>
   <? xl('Work Phone','e'); ?>
  </td>
 </tr>
<?
 if ($_POST['form_refresh']) {
  $totalpts = 0;

  /****
  $query = "SELECT " .
   "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
   "p.postal_code, p.phone_home, p.phone_biz, " .
   "count(e.date) AS ecount, max(e.date) AS edate, " .
   "c1.name AS cname1, c2.name AS cname2 " .
   "FROM patient_data AS p " .
   "JOIN form_encounter AS e ON " .
   "e.pid = p.pid AND " .
   "e.date >= '$from_date 00:00:00' AND " .
   "e.date <= '$to_date 23:59:59' " .
   "LEFT OUTER JOIN insurance_data AS i1 ON " .
   "i1.pid = p.pid AND i1.type = 'primary' " .
   "LEFT OUTER JOIN insurance_companies AS c1 ON " .
   "c1.id = i1.provider " .
   "LEFT OUTER JOIN insurance_data AS i2 ON " .
   "i2.pid = p.pid AND i2.type = 'secondary' " .
   "LEFT OUTER JOIN insurance_companies AS c2 ON " .
   "c2.id = i2.provider " .
   "GROUP BY p.lname, p.fname, p.mname, p.pid " .
   "ORDER BY p.lname, p.fname, p.mname, p.pid";
  $res = sqlStatement($query);
  while ($row = sqlFetchArray($res)) {
  ****/

  $query = "SELECT " .
   "p.fname, p.mname, p.lname, p.street, p.city, p.state, " .
   "p.postal_code, p.phone_home, p.phone_biz, p.pid, " .
   "count(e.date) AS ecount, max(e.date) AS edate, " .
   "i1.date AS idate1, i2.date AS idate2, " .
   "c1.name AS cname1, c2.name AS cname2 " .
   "FROM patient_data AS p " .
   "JOIN form_encounter AS e ON " .
   "e.pid = p.pid AND " .
   "e.date >= '$from_date 00:00:00' AND " .
   "e.date <= '$to_date 23:59:59' " .
   "LEFT OUTER JOIN insurance_data AS i1 ON " .
   "i1.pid = p.pid AND i1.type = 'primary' " .
   "LEFT OUTER JOIN insurance_companies AS c1 ON " .
   "c1.id = i1.provider " .
   "LEFT OUTER JOIN insurance_data AS i2 ON " .
   "i2.pid = p.pid AND i2.type = 'secondary' " .
   "LEFT OUTER JOIN insurance_companies AS c2 ON " .
   "c2.id = i2.provider " .
   "GROUP BY p.lname, p.fname, p.mname, p.pid, i1.date, i2.date " .
   "ORDER BY p.lname, p.fname, p.mname, p.pid, i1.date DESC, i2.date DESC";
  $res = sqlStatement($query);

  $prevpid = 0;
  while ($row = sqlFetchArray($res)) {
   if ($row['pid'] == $prevpid) continue;
   $prevpid = $row['pid'];

  /****/

   $age = '';
   if ($row['DOB']) {
    $dob = $row['DOB'];
    $tdy = $row['edate'];
    $ageInMonths = (substr($tdy,0,4)*12) + substr($tdy,5,2) -
                   (substr($dob,0,4)*12) - substr($dob,5,2);
    $dayDiff = substr($tdy,8,2) - substr($dob,8,2);
    if ($dayDiff < 0) --$ageInMonths;
    $age = intval($ageInMonths/12);
   }
?>
 <tr>
  <td class='detail'>
   <?php echo substr($row['edate'], 0, 10) ?>
  </td>
  <td class='detail'>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['street'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['city'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['state'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['postal_code'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['phone_home'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['phone_biz'] ?>
  </td>
 </tr>
<?php
   ++$totalpts;
  }
?>

 <tr>
  <td class='dehead' colspan='8'>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td class='dehead' colspan='3'>
   <? xl('Total Number of Patients','e'); ?>
  </td>
  <td class='detail' colspan='4'>
   <?php echo $totalpts ?>
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
