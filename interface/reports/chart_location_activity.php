<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This reports checkins and checkouts for a specified patient's chart.

require_once("../globals.php");
require_once("$srcdir/patient.inc");
require_once("$srcdir/options.inc.php");

$form_patient_id = trim($_POST['form_patient_id']);
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Chart Location Activity','e'); ?></title>

<link rel='stylesheet' href='<?php echo $css_header ?>' type='text/css'>
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #thisreport_parameters {
        visibility: hidden;
        display: none;
    }
    #thisreport_parameters_daterange {
        visibility: visible;
        display: inline;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #thisreport_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

#thisreport_parameters {
    width: 100%;
    background-color: #ddf;
}
#thisreport_parameters table {
    border: none;
    border-collapse: collapse;
}
#thisreport_parameters table td {
    padding: 3px;
}

#thisreport_results {
    width: 100%;
    margin-top: 10px;
}
#thisreport_results table {
   border: 1px solid black;
   border-collapse: collapse;
}
#thisreport_results table thead {
    display: table-header-group;
    background-color: #ddd;
}
#thisreport_results table th {
    border-bottom: 1px solid black;
    font-size: 0.7em;
    text-align: left;
    padding: 1px 4px 1px 4px;
}
#thisreport_results table td {
    padding: 1px;
    margin: 2px;
    border-bottom: 1px solid black;
    font-size: 0.7em;
    padding: 1px 4px 1px 4px;
}
.thisreport_totals td {
    background-color: #77ff77;
    font-weight: bold;
}
</style>
</head>

<body class="body_top">

<center>

<h2><?php xl('Chart Location Activity','e'); ?></h2>

<?php
$curr_pid = $pid;
$ptrow = array();
if (!empty($form_patient_id)) {
  $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pubpid = '$form_patient_id' ORDER BY pid LIMIT 1";
  $ptrow = sqlQuery($query);
  if (empty($ptrow)) {
    $curr_pid = 0;
    echo "<font color='red'>" . xl('Chart ID') . " '" . $form_patient_id . "' " . xl('not found!') . "</font><br />&nbsp;<br />";
  }
  else {
    $curr_pid = $ptrow['pid'];
  }
}
else if (!empty($curr_pid)) {
  $query = "SELECT pid, pubpid, fname, mname, lname FROM patient_data WHERE " .
    "pid = '$curr_pid'";
  $ptrow = sqlQuery($query);
  $form_patient_id = $ptrow['pubpid'];
}
if (!empty($ptrow)) {
  echo '<h3>' . xl('for','','',' ');
  echo $ptrow['lname'] . ', ' . $ptrow['fname'] . ' ' . $ptrow['mname'] . ' ';
  echo "(" . $ptrow['pubpid'] . ")";
  echo "</h3>\n";
}
?>

<div id="thisreport_parameters_daterange">
</div>

<div id="thisreport_parameters">

<form name='theform' method='post' action='chart_location_activity.php'>

<table>
 <tr>
  <td>
   <?php xl('Patient ID','e'); ?>:
   <input type='text' name='form_patient_id' size='10' maxlength='31' value='<?php echo $form_patient_id ?>'
    title='<?php xl('Patient ID','e'); ?>' />
   &nbsp;
   <input type='submit' name='form_refresh' value=<?php xl('Refresh','e'); ?>>
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>
</table>
</div> <!-- end of parameters -->

<div id="thisreport_results">
<table>
 <thead>
  <th> <?php xl('Time','e'); ?> </th>
  <th> <?php xl('Destination','e'); ?> </th>
 </thead>
 <tbody>
<?php
$row = array();
if (!empty($ptrow)) {
  $query = "SELECT ct.ct_when, ct.ct_userid, ct.ct_location, " .
    "u.username, u.fname, u.mname, u.lname " .
    "FROM chart_tracker AS ct " .
    "LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid " .
    "WHERE ct.ct_pid = '$curr_pid' " .
    "ORDER BY ct.ct_when DESC";
  $res = sqlStatement($query);

  while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td>
   <?php echo $row['ct_when']; ?>
  </td>
  <td>
<?php
    if (!empty($row['ct_location'])) {
      echo generate_display_field(array('data_type'=>'1','list_id'=>'chartloc'),$row['ct_location']);
    }
    else if (!empty($row['ct_userid'])) {
      echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname'];
    }
?>
  </td>
 </tr>
<?php
  } // end while
 } // end if
?>
</tbody>
</table>
</div> <!-- end of results -->
</form>
</center>
</body>
</html>
