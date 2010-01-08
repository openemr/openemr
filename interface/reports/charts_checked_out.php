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
    #report_parameters {
        visibility: hidden;
        display: none;
    }
    #report_parameters_daterange {
        visibility: visible;
        display: inline;
    }
    #report_results table {
       margin-top: 0px;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #report_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

</style>
</head>

<body class="body_top">

<span class='title'><?php xl('Report','e'); ?> - <?php xl('Charts Checked Out','e'); ?></span>

<div id="report_results">
<br/>
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

$data_ctr = 0;
while ($row = sqlFetchArray($res)) {

if ( $data_ctr == 0 ) { ?>
<table>
 <thead>
  <th> <?php xl('Chart','e'); ?> </th>
  <th> <?php xl('Patient','e'); ?> </th>
  <th> <?php xl('Location','e'); ?> </th>
  <th> <?php xl('As Of','e'); ?> </th>
 </thead>
 <tbody>
<?php  } ?>

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

$data_ctr++;
} // end while

if ( $data_ctr < 1 ) { ?>
<span class='text'><?php xl('There are no charts checked out.','e'); ?></span>
<?php
}
?>

</tbody>
</table>
</div> <!-- end of results -->
</body>
</html>
