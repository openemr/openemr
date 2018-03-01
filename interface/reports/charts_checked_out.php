<?php
/**
 * This reports checkins and checkouts for a specified patient's chart.
 *
 * @package   OpenEMR
 * @link      http://www.open-emr.org
 * @author    Rod Roark <rod@sunsetsystems.com>
 * @author    Brady Miller <brady.g.miller@gmail.com>
 * @copyright Copyright (c) 2008-2010 Rod Roark <rod@sunsetsystems.com>
 * @copyright Copyright (c) 2017-2018 Brady Miller <brady.g.miller@gmail.com>
 * @license   https://github.com/openemr/openemr/blob/master/LICENSE GNU General Public License 3
 */


require_once("../globals.php");
require_once("$srcdir/patient.inc");

use OpenEMR\Core\Header;
use OpenEMR\Services\PatientService;

?>
<html>
<head>
    <title><?php echo xlt('Charts Checked Out'); ?></title>

    <?php Header::setupHeader(); ?>

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

<span class='title'><?php echo xlt('Report'); ?> - <?php echo xlt('Charts Checked Out'); ?></span>

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
$res = PatientService::getChartTrackerInformation();
$data_ctr = 0;
while ($row = sqlFetchArray($res)) {
    if ($data_ctr == 0) { ?>
    <table>
     <thead>
          <th> <?php echo xlt('Chart'); ?> </th>
          <th> <?php echo xlt('Patient'); ?> </th>
          <th> <?php echo xlt('Location'); ?> </th>
          <th> <?php echo xlt('As Of'); ?> </th>
     </thead>
     <tbody>
    <?php
    } ?>

 <tr>
  <td>
    <?php echo text($row['pubpid']); ?>
  </td>
  <td>
    <?php echo text($row['lname'] . ', ' . $row['fname'] . ' ' . $row['mname']); ?>
  </td>
  <td>
    <?php echo text($row['ulname'] . ', ' . $row['ufname'] . ' ' . $row['umname']); ?>
  </td>
  <td>
    <?php echo text(oeFormatDateTime($row['ct_when'], "global", true)); ?>
  </td>
 </tr>
<?php

$data_ctr++;
} // end while

if ($data_ctr < 1) { ?>
<span class='text'><?php echo xla('There are no charts checked out.'); ?></span>
<?php
}
?>

</tbody>
</table>
</div> <!-- end of results -->
</body>
</html>
