<?php
 // Copyright (C) 2005-2009 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report cross-references appointments with encounters.
 // For a given date, show a line for each appointment with the
 // matching encounter, and also for each encounter that has no
 // matching appointment.  This helps to catch these errors:
 //
 // * Appointments with no encounter
 // * Encounters with no appointment
 // * Codes not justified
 // * Codes not authorized
 // * Procedure codes without a fee
 // * Fees assigned to diagnoses (instead of procedures)
 // * Encounters not billed
 //
 // For decent performance the following indexes are highly recommended:
 //   openemr_postcalendar_events.pc_eventDate
 //   forms.encounter
 //   billing.pid_encounter

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../custom/code_types.inc.php");

 $alertmsg = ''; // not used yet but maybe later
 $grand_total_charges    = 0;
 $grand_total_copays     = 0;
 $grand_total_encounters = 0;

 function bucks($amount) {
  if ($amount) printf("%.2f", $amount);
 }

 function endDoctor(&$docrow) {
  global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
  if (!$docrow['docname']) return;

  echo " <tr class='apptencreport_totals'>\n";
  echo "  <td colspan='4'>\n";
  echo "   &nbsp;Totals for " . $docrow['docname'] . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $docrow['encounters'] . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['charges']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['copays']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
  echo " </tr>\n";

  $grand_total_charges     += $docrow['charges'];
  $grand_total_copays      += $docrow['copays'];
  $grand_total_encounters  += $docrow['encounters'];

  $docrow['charges']     = 0;
  $docrow['copays']      = 0;
  $docrow['encounters']  = 0;
 }

 $form_facility  = isset($_POST['form_facility']) ? $_POST['form_facility'] : '';
 $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 $form_to_date = fixDate($_POST['form_to_date'], date('Y-m-d'));
 if ($_POST['form_search']) {
  $form_from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
  $form_to_date = fixDate($_POST['form_to_date'], "");

  // MySQL doesn't grok full outer joins so we do it the hard way.
  //
  $query = "( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, p.pubpid, " .
   "CONCAT( u.lname, ', ', u.fname ) AS docname " .
   "FROM openemr_postcalendar_events AS e " .
   "LEFT OUTER JOIN form_encounter AS fe " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
   // "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid WHERE ";
   "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
  if ($form_to_date) {
   $query .= "e.pc_eventDate >= '$form_from_date' AND e.pc_eventDate <= '$form_to_date' ";
  } else {
   $query .= "e.pc_eventDate = '$form_from_date' ";
  }
  if ($form_facility !== '') {
   $query .= "AND e.pc_facility = '$form_facility' ";
  }
  // $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
  $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   ") UNION ( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, p.pubpid, " .
   "CONCAT( u.lname, ', ', u.fname ) AS docname " .
   "FROM form_encounter AS fe " .
   "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
   // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
   "e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
   "LEFT OUTER JOIN users AS u ON BINARY u.username = BINARY f.user WHERE ";
  if ($form_to_date) {
   // $query .= "LEFT(fe.date, 10) >= '$form_from_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
   $query .= "fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
  } else {
   // $query .= "LEFT(fe.date, 10) = '$form_from_date' ";
   $query .= "fe.date >= '$form_from_date 00:00:00' AND fe.date <= '$form_from_date 23:59:59' ";
  }
  if ($form_facility !== '') {
   $query .= "AND fe.facility_id = '$form_facility' ";
  }
  $query .= ") ORDER BY docname, pc_eventDate, pc_startTime";

  $res = sqlStatement($query);
 }
?>
<html>
<head>
<?php html_header_show();?>
<link rel="stylesheet" href="<?php echo $css_header;?>" type="text/css">
<style type="text/css">

/* specifically include & exclude from printing */
@media print {
    #apptencreport_parameters {
        visibility: hidden;
        display: none;
    }
    #apptencreport_parameters_daterange {
        visibility: visible;
        display: inline;
    }
}

/* specifically exclude some from the screen */
@media screen {
    #apptencreport_parameters_daterange {
        visibility: hidden;
        display: none;
    }
}

#apptencreport_parameters {
    width: 100%;
    margin: 10px;
    text-align: center;
    background-color: #ddf;
}
#apptencreport_parameters table {
    text-align: center;
    border: none;
    width: 100%;
    border-collapse: collapse;
}
#apptencreport_parameters table td {
    padding: 3px;
}

#apptencreport_results {
    width: 100%;
    margin-top: 10px;
}
#apptencreport_results table {
   border: 1px solid black;
   width: 98%;
   border-collapse: collapse;
}
#apptencreport_results table thead {
    display: table-header-group;
    background-color: #ddd;
}
#apptencreport_results table th {
    border-bottom: 1px solid black;
}
#apptencreport_results table td {
    padding: 1px;
    margin: 2px;
    border-bottom: 1px solid #eee;
}
.apptencreport_totals td {
    background-color: #77ff77;
    font-weight: bold;
}
</style>
<title><?php  xl('Appointments and Encounters','e'); ?></title>
</head>

<body class="body_top">
<center>

<h2><?php  xl('Appointments and Encounters','e'); ?></h2>
<div id="apptencreport_parameters_daterange">
<?php echo date("d F Y", strtotime($form_from_date)) ." &nbsp; to &nbsp; ". date("d F Y", strtotime($form_to_date)); ?>
</div>

<div id="apptencreport_parameters">
<form method='post' action='appt_encounter_report.php'>
<table>
 <tr>
  <td>
   <?php xl('Facility','e'); ?>:
<?php
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility ORDER BY name";
 $fres = sqlStatement($query);
 echo "   <select name='form_facility'>\n";
 echo "    <option value=''>-- All --\n";
 while ($frow = sqlFetchArray($fres)) {
  $facid = $frow['id'];
  echo "    <option value='$facid'";
  if ($facid == $form_facility) echo " selected";
  echo ">" . $frow['name'] . "\n";
 }
 echo "    <option value='0'";
 if ($form_facility === '0') echo " selected";
 echo ">-- Unspecified --\n";
 echo "   </select>\n";
?>
   <?php  xl('DOS','e'); ?>:
   <input type='text' name='form_from_date' id="form_from_date" size='10' value='<?php  echo $form_from_date; ?>'
    title='Date of appointments mm/dd/yyyy' >
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_from_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <?php  xl('to','e'); ?>:
   <input type='text' name='form_to_date' id="form_to_date" size='10' value='<?php  echo $form_to_date; ?>'
    title='Optional end date mm/dd/yyyy' >
   <img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22'
    id='img_to_date' border='0' alt='[?]' style='cursor:pointer'
    title='<?php xl('Click here to choose a date','e'); ?>'>
   &nbsp;
   <input type='checkbox' name='form_details'
    value='1'<?php if ($_POST['form_details']) echo " checked"; ?>><?php xl('Details','e') ?>
   &nbsp;
   <input type='submit' name='form_search' value='Search'>
   &nbsp;
   <input type='button' value='<?php xl('Print','e'); ?>' onclick='window.print()' />
  </td>
 </tr>
</table>
</div> <!-- end apptenc_report_parameters -->

<div id="apptencreport_results">
<table>

 <thead>
  <th> &nbsp;<?php  xl('Practitioner','e'); ?> </th>
  <th> &nbsp;<?php  xl('Time','e'); ?> </th>
  <th> &nbsp;<?php  xl('Patient','e'); ?> </th>
  <th> &nbsp;<?php  xl('ID','e'); ?> </th>
  <th> <?php  xl('Chart','e'); ?>&nbsp; </th>
  <th> <?php  xl('Encounter','e'); ?>&nbsp; </th>
  <th> <?php  xl('Charges','e'); ?>&nbsp; </th>
  <th> <?php  xl('Copays','e'); ?>&nbsp; </th>
  <th> <?php  xl('Billed','e'); ?> </th>
  <th> &nbsp;<?php  xl('Error','e'); ?> </th>
 </thead>
 <tbody>
<?php 
 if ($res) {
  $docrow = array('docname' => '', 'charges' => 0, 'copays' => 0, 'encounters' => 0);

  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $encounter  = $row['encounter'];
   $docname    = $row['docname'] ? $row['docname'] : 'Unknown';

   if ($docname != $docrow['docname']) {
    endDoctor($docrow);
   }

   $billed  = "Y";
   $errmsg  = "";
   $charges = 0;
   $copays  = 0;

   // Scan the billing items for status and fee total.
   //
   $query = "SELECT code_type, authorized, billed, fee, justify " .
    "FROM billing WHERE " .
    "pid = '$patient_id' AND encounter = '$encounter' AND activity = 1";
   $bres = sqlStatement($query);
   //
   while ($brow = sqlFetchArray($bres)) {
    if ($code_types[$brow['code_type']]['fee'] && !$brow['billed'])
      $billed = "";
    if (!$GLOBALS['simplified_demographics'] && !$brow['authorized'])
      $errmsg = "Needs Auth";
    if ($code_types[$brow['code_type']]['just']) {
     if (! $brow['justify']) $errmsg = "Needs Justify";
    }
    if ($brow['code_type'] == 'COPAY') {
     $copays -= $brow['fee'];
     if ($brow['fee'] >= 0) $errmsg = "Copay not positive";
    } else if ($code_types[$brow['code_type']]['fee']) {
     $charges += $brow['fee'];
     if ($brow['fee'] == 0 && !$GLOBALS['ippf_specific']) $errmsg = "Missing Fee";
    } else {
     if ($brow['fee'] != 0) $errmsg = "Fee is not allowed";
    }
   }

   if (!$errmsg) {
     if (!$billed) $errmsg = $GLOBALS['simplified_demographics'] ?
       "Not checked out" : "Not billed";
     if (!$encounter) $errmsg = "No visit";
   }

   if (! $charges) $billed = "";

   $docrow['charges'] += $charges;
   $docrow['copays']  += $copays;
   if ($encounter) ++$docrow['encounters'];

   if ($_POST['form_details']) {
?>
 <tr>
  <td>
   &nbsp;<?php  echo ($docname == $docrow['docname']) ? "" : $docname ?>
  </td>
  <td>
   &nbsp;<?php 
    if ($form_to_date) {
        echo $row['pc_eventDate'] . '<br>';
        echo substr($row['pc_startTime'], 0, 5);
    }
    ?>
  </td>
  <td>
   &nbsp;<?php  echo $row['fname'] . " " . $row['lname'] ?>
  </td>
  <td>
   &nbsp;<?php  echo $row['pubpid'] ?>
  </td>
  <td align='right'>
   <?php  echo $row['pid'] ?>&nbsp;
  </td>
  <td align='right'>
   <?php  echo $encounter ?>&nbsp;
  </td>
  <td align='right'>
   <?php  bucks($charges) ?>&nbsp;
  </td>
  <td align='right'>
   <?php  bucks($copays) ?>&nbsp;
  </td>
  <td>
   <?php  echo $billed ?>
  </td>
  <td style='color:#cc0000'>
   &nbsp;<?php  echo xl($errmsg); ?>
  </td>
 </tr>
<?php
   } // end of details line

   $docrow['docname'] = $docname;
  } // end of row

  endDoctor($docrow);

  echo " <tr class='apptencreport_totals'>\n";
  echo "  <td colspan='5'>\n";
  echo "   &nbsp;" . xl('Grand Totals') . "\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;" . $grand_total_encounters . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_charges); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_copays); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
  echo " </tr>\n";

 }
?>
</tbody>
</table>
</div> <!-- end the apptenc_report_results -->

</form>
</center>
<script>
<?php if ($alertmsg) { echo " alert('$alertmsg');\n"; } ?>
</script>
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
