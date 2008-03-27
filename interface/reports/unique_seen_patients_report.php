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
<?php html_header_show();?>
<title><?php xl('Front Office Receipts','e'); ?></title>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>
<script language="JavaScript">
 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';
</script>

<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #unipatreport_parameters {
        visibility: hidden;
        display: none;
    }
    #unipatreport_parameters_daterange {
        visibility: visible;
        display: inline;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #unipatreport_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

#unipatreport_parameters {
    width: 100%;
    background-color: #ddf;
}
#unipatreport_parameters table {
    border: none;
    border-collapse: collapse;
}
#unipatreport_parameters table td {
    padding: 3px;
}

#unipatreport_results {
    width: 100%;
    margin-top: 10px;
}
#unipatreport_results table {
   border: 1px solid black;
   width: 98%;
   border-collapse: collapse;
}
#unipatreport_results table thead {
    display: table-header-group;
    background-color: #ddd;
}
#unipatreport_results table th {
    border-bottom: 1px solid black;
}
#unipatreport_results table td {
    padding: 1px;
    margin: 2px;
    border-bottom: 1px solid #eee;
}
.unipatreport_totals td {
    background-color: #77ff77;
    font-weight: bold;
}
</style>
</head>

<body class="body_top">

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><?php xl('Unique Seen Patients','e'); ?></h2>

<div id="unipatreport_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<div id="unipatreport_parameters">
<form name='theform' method='post' action='unique_seen_patients_report.php'>
<table>
 <tr>
  <td>
   <?php xl('Visits From','e'); ?>:
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;<?php xl('To','e'); ?>:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>> &nbsp;
   <input type='submit' name='form_labels' value=<?php xl('Labels','e'); ?>>
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<div id="unipatreport_results">
<table>

 <thead>
  <th> <?php xl('Last Visit','e'); ?> </th>
  <th> <?php xl('Patient','e'); ?> </th>
  <th align='right'> <?php xl('Visits','e'); ?> </th>
  <th align='right'> <?php xl('Age','e'); ?> </th>
  <th> <?php xl('Sex','e'); ?> </th>
  <th> <?php xl('Race','e'); ?> </th>
  <th> <?php xl('Primary Insurance','e'); ?> </th>
  <th> <?php xl('Secondary Insurance','e'); ?> </th>
 </thead>
 <tbody>
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
  <td>
   <?php echo substr($row['edate'], 0, 10) ?>
  </td>
  <td>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'] ?>
  </td>
  <td style="text-align:center">
   <?php echo $row['ecount'] ?>
  </td>
  <td>
   <?php echo $age ?>
  </td>
  <td>
   <?php echo $row['sex'] ?>
  </td>
  <td>
   <?php echo $row['ethnoracial'] ?>
  </td>
  <td>
   <?php echo $row['cname1'] ?>
  </td>
  <td>
   <?php echo $row['cname2'] ?>
  </td>
 </tr>
<?php
   } // end not labels
   ++$totalpts;
  }

  if (!$_POST['form_labels']) {
?>
 <tr class='unipatreport_totals'>
  <td colspan='2'>
   <?php xl('Total Number of Patients','e'); ?>
  </td>
  <td style="padding-left: 20px;">
   <?php echo $totalpts ?>
  </td>
  <td colspan='5'>&nbsp;</td>
 </tr>

<?php
  } // end not labels
 } // end refresh or labels

 if (!$_POST['form_labels']) {
?>
</tbody>
</table>
</div>
</form>
</center>
</body>

<!-- stuff for the popup calendar -->
<style type="text/css">@import url(../../library/dynarch_calendar.css);</style>
<script type="text/javascript" src="../../library/dynarch_calendar.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_en.js"></script>
<script type="text/javascript" src="../../library/dynarch_calendar_setup.js"></script>
<script language="Javascript">
 Calendar.setup({inputField:"form_from_date", ifFormat:"%Y-%m-%d", button:"img_from_date"});
 Calendar.setup({inputField:"form_to_date", ifFormat:"%Y-%m-%d", button:"img_to_date"});
</script>
</html>
<?php
 } // end not labels
?>
