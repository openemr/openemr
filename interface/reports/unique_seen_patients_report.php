<?php
 // Copyright (C) 2006, 2008 Rod Roark <rod@sunsetsystems.com>
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

 if ($_POST['form_labels']) {
  header("Pragma: public");
  header("Expires: 0");
  header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
  header("Content-Type: application/force-download");
  header("Content-Disposition: attachment; filename=labels.txt");
  header("Content-Description: File Transfer");
 }
 else {
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
</script>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><?php xl('Unique Seen Patients','e'); ?></h2>

<form name='theform' method='post' action='unique_seen_patients_report.php'>

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
   <input type='submit' name='form_refresh' value=<? xl('Refresh','e'); ?>> &nbsp;
   <input type='submit' name='form_labels' value=<? xl('Labels','e'); ?>>
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
   <?php xl('Last Visit','e'); ?>
  </td>
  <td class='dehead'>
   <?php xl('Patient','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Visits','e'); ?>
  </td>
  <td class='dehead' align='right'>
   <?php xl('Age','e'); ?>
  </td>
  <td class='dehead'>
   <?php xl('Sex','e'); ?>
  </td>
  <td class='dehead'>
   <?php xl('Race','e'); ?>
  </td>
  <td class='dehead'>
   <?php xl('Primary Insurance','e'); ?>
  </td>
  <td class='dehead'>
   <?php xl('Secondary Insurance','e'); ?>
  </td>
 </tr>
<?php
 } // end not generating labels

 if ($_POST['form_refresh'] || $_POST['form_labels']) {
  $totalpts = 0;

  $query = "SELECT " .
   "p.pid, p.fname, p.mname, p.lname, p.DOB, p.sex, p.ethnoracial, " .
   "p.street, p.city, p.state, p.postal_code, " .
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

   if ($_POST['form_labels']) {
    echo '"' . $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] . '","' .
      $row['street'] . '","' . $row['city'] . '","' . $row['state'] . '","' .
      $row['postal_code'] . '"' . "\n";
   }
   else { // not labels
?>
 <tr>
  <td class='detail'>
   <?php echo substr($row['edate'], 0, 10) ?>
  </td>
  <td class='detail'>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $row['ecount'] ?>
  </td>
  <td class='detail' align='right'>
   <?php echo $age ?>
  </td>
  <td class='detail'>
   <?php echo $row['sex'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['ethnoracial'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['cname1'] ?>
  </td>
  <td class='detail'>
   <?php echo $row['cname2'] ?>
  </td>
 </tr>
<?php
   } // end not labels
   ++$totalpts;
  }

  if (!$_POST['form_labels']) {
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
  } // end not labels
 } // end refresh or labels

 if (!$_POST['form_labels']) {
?>

</table>
</form>
</center>
</body>
</html>
<?php
 } // end not labels
?>
