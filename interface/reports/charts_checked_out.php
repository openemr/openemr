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
?>
<html>
<head>
<?php html_header_show(); ?>
<title><?php xl('Charts Checked Out','e'); ?></title>

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

<h2><?php xl('Charts Checked Out','e'); ?></h2>

<div id="thisreport_results">
<table>
 <thead>
  <th> <?php xl('Chart','e'); ?> </th>
  <th> <?php xl('Patient','e'); ?> </th>
  <th> <?php xl('Location','e'); ?> </th>
  <th> <?php xl('As Of','e'); ?> </th>
 </thead>
 <tbody>
<?php
/*********************************************************************
$query = "SELECT ct.ct_when, " .
  "u.username, u.fname AS ufname, u.mname AS umname, u.lname AS ulname, " .
  "p.pubpid, p.fname, p.mname, p.lname " .
  "FROM chart_tracker AS ct " .
  "LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = ct.ct_pid " .
  "WHERE (ct.ct_pid, ct.ct_when) in " .
  "(SELECT ct_pid, MAX(ct_when) FROM chart_tracker GROUP BY ct_pid) " .
  "AND ct.ct_userid != 0 " .
  "ORDER BY p.pubpid";
*********************************************************************/

// Oops, the above requires MySQL 4.1 or later and so it was rewritten
// as follows to use a temporary table.
//
sqlStatement("DROP TEMPORARY TABLE IF EXISTS cttemp");
sqlStatement("CREATE TEMPORARY TABLE cttemp SELECT " .
  "ct_pid, MAX(ct_when) AS ct_when FROM chart_tracker GROUP BY ct_pid");
$query = "SELECT ct.ct_when, " .
  "u.username, u.fname AS ufname, u.mname AS umname, u.lname AS ulname, " .
  "p.pubpid, p.fname, p.mname, p.lname " .
  "FROM chart_tracker AS ct " .
  "JOIN cttemp ON cttemp.ct_pid = ct.ct_pid AND cttemp.ct_when = ct.ct_when " .
  "LEFT OUTER JOIN users AS u ON u.id = ct.ct_userid " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = ct.ct_pid " .
  "WHERE ct.ct_userid != 0 " .
  "ORDER BY p.pubpid";

$res = sqlStatement($query);

while ($row = sqlFetchArray($res)) {
?>
 <tr>
  <td>
   <?php echo $row['pubpid']; ?>
  </td>
  <td>
   <?php echo $row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']; ?>
  </td>
  <td>
   <?php echo $row['ulname'] . ', ' . $row['ufname'] . ' ' . $row['umname']; ?>
  </td>
  <td>
   <?php echo $row['ct_when']; ?>
  </td>
 </tr>
<?php
} // end while
?>
</tbody>
</table>
</div> <!-- end of results -->
</center>
</body>
</html>
