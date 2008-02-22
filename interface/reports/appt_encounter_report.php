<?php 
 // Copyright (C) 2005-2007 Rod Roark <rod@sunsetsystems.com>
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
  if ($amount)
   printf("%.2f", $amount);
 }

 function endDoctor(&$docrow) {
  global $grand_total_charges, $grand_total_copays, $grand_total_encounters;
  if (!$docrow['docname']) return;

  echo " <tr bgcolor='#ffff00'>\n";
  echo "  <td class='detail' colspan='4'>\n";
  echo "   &nbsp;Totals for " . $docrow['docname'] . "\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;" . $docrow['encounters'] . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['charges']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;"; bucks($docrow['copays']); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detail' colspan='2'>\n";
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

 if ($_POST['form_search']) {
  $form_date    = fixDate($_POST['form_date'], date('Y-m-d'));
  $form_to_date = fixDate($_POST['form_to_date'], "");

  // MySQL doesn't grok full outer joins so we do it the hard way.
  //
  $query = "( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, " .
   "u.lname AS docname " .
   "FROM openemr_postcalendar_events AS e " .
   "LEFT OUTER JOIN form_encounter AS fe " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
   // "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid WHERE ";
   "LEFT OUTER JOIN users AS u ON u.username = f.user WHERE ";
  if ($form_to_date) {
   $query .= "e.pc_eventDate >= '$form_date' AND e.pc_eventDate <= '$form_to_date' ";
  } else {
   $query .= "e.pc_eventDate = '$form_date' ";
  }
  // $query .= "AND ( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
  $query .= "AND e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   ") UNION ( " .
   "SELECT " .
   "e.pc_eventDate, e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, " .
   "u.lname AS docname " .
   "FROM form_encounter AS fe " .
   "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
   // "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
   "e.pc_pid != '' AND e.pc_apptstatus != '?' " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
   "LEFT OUTER JOIN users AS u ON u.username = f.user WHERE ";
  if ($form_to_date) {
   // $query .= "LEFT(fe.date, 10) >= '$form_date' AND LEFT(fe.date, 10) <= '$form_to_date' ";
   $query .= "fe.date >= '$form_date 00:00:00' AND fe.date <= '$form_to_date 23:59:59' ";
  } else {
   // $query .= "LEFT(fe.date, 10) = '$form_date' ";
   $query .= "fe.date >= '$form_date 00:00:00' AND fe.date <= '$form_date 23:59:59' ";
  }
  $query .= ") ORDER BY docname, pc_eventDate, pc_startTime";

  $res = sqlStatement($query);
 }
?>
<html>
<head>
<? html_header_show();?>
<link rel=stylesheet href="<?php echo $css_header;?>" type="text/css">
<title><?php  xl('Appointments and Encounters','e'); ?></title>
</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>
<center>

<form method='post' action='appt_encounter_report.php'>

<table border='0' cellpadding='5' cellspacing='0' width='98%'>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

 <tr bgcolor='#ddddff'>
  <td align='left'>
   <h2><?php  xl('Appointments and Encounters','e'); ?></h2>
  </td>
  <td align='right'>
   <?php  xl('DOS','e'); ?>:
   <input type='text' name='form_date' size='10' value='<?php  echo $_POST['form_date']; ?>'
    title='Date of appointments mm/dd/yyyy' >
   &nbsp;
   <?php  xl('to','e'); ?>:
   <input type='text' name='form_to_date' size='10' value='<?php  echo $_POST['form_to_date']; ?>'
    title='Optional end date mm/dd/yyyy' >
   &nbsp;
   <input type='checkbox' name='form_details'
    value='1'<? if ($_POST['form_details']) echo " checked"; ?>><?php xl('Details','e') ?>
   &nbsp;
   <input type='submit' name='form_search' value='Search'>
  </td>
 </tr>

 <tr>
  <td height="1" colspan="2">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   &nbsp;<?php  xl('Practitioner','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Time','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Patient','e'); ?>
  </td>
  <td class="dehead" align="right">
   <?php  xl('Chart','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php  xl('Encounter','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php  xl('Charges','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <?php  xl('Copays','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <?php  xl('Billed','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<?php  xl('Error','e'); ?>
  </td>
 </tr>
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
    if (! $brow['billed']) $billed = "";
    if (! $brow['authorized']) $errmsg = "Needs Auth";
    if ($code_types[$brow['code_type']]['just']) {
     if (! $brow['justify']) $errmsg = "Needs Justify";
    }
    if ($brow['code_type'] == 'COPAY') {
     $copays -= $brow['fee'];
     if ($brow['fee'] >= 0) $errmsg = "Copay not positive";
    } else if ($code_types[$brow['code_type']]['fee']) {
     $charges += $brow['fee'];
     if ($brow['fee'] == 0 ) $errmsg = "Missing Fee";
    } else {
     if ($brow['fee'] != 0) $errmsg = "Misplaced Fee";
    }
   }
   if (! $charges) $billed = "";

   $docrow['charges'] += $charges;
   $docrow['copays']  += $copays;
   if ($encounter) ++$docrow['encounters'];

   if ($_POST['form_details']) {
?>
 <tr>
  <td class="detail">
   &nbsp;<?php  echo ($docname == $docrow['docname']) ? "" : $docname ?>
  </td>
  <td class="detail">
   &nbsp;<?php if ($form_to_date) echo $row['pc_eventDate'] . ' ';
    echo substr($row['pc_startTime'], 0, 5) ?>
  </td>
  <td class="detail">
   &nbsp;<?php  echo $row['fname'] . " " . $row['lname'] ?>
  </td>
  <td class="detail" align="right">
   <?php  echo $row['pid'] ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  echo $encounter ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  bucks($charges) ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <?php  bucks($copays) ?>&nbsp;
  </td>
  <td class="detail" align="center">
   <?php  echo $billed ?>
  </td>
  <td class="detail" align="left">
   &nbsp;<?php  echo $errmsg ?>
  </td>
 </tr>
<?php
   } // end of details line

   $docrow['docname'] = $docname;
  } // end of row

  endDoctor($docrow);

  echo " <tr bgcolor='#77ff77'>\n";
  echo "  <td class='detail' colspan='4'>\n";
  echo "   &nbsp;Grand Totals\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;" . $grand_total_encounters . "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_charges); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detotal' align='right'>\n";
  echo "   &nbsp;"; bucks($grand_total_copays); echo "&nbsp;\n";
  echo "  </td>\n";
  echo "  <td class='detail' colspan='2'>\n";
  echo "   &nbsp;\n";
  echo "  </td>\n";
  echo " </tr>\n";

 }
?>

</table>

</form>
</center>
<script>
<?php 
	if ($alertmsg) {
		echo " alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
