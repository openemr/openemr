<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
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

 include_once("../globals.php");
 include_once("../../library/patient.inc");
 include_once("../../custom/code_types.inc.php");

 $alertmsg = ''; // not used yet but maybe later

 function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
 }

 if ($_POST['form_search']) {
  $form_date = fixDate($_POST['form_date'], "");

  // MySQL doesn't grok full outer joins so we do it the hard way.
  //
  $query = "( " .
   "SELECT " .
   "e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, " .
   "u.lname AS docname " .
   "FROM openemr_postcalendar_events AS e " .
   "LEFT OUTER JOIN form_encounter AS fe " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
   "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
   "WHERE e.pc_eventDate = '$form_date' AND " .
   "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
   ") UNION ( " .
   "SELECT " .
   "e.pc_startTime, " .
   "fe.encounter, " .
   "f.authorized, " .
   "p.fname, p.lname, p.pid, " .
   "u.lname AS docname " .
   "FROM form_encounter AS fe " .
   "LEFT OUTER JOIN openemr_postcalendar_events AS e " .
   "ON LEFT(fe.date, 10) = e.pc_eventDate AND fe.pid = e.pc_pid AND " .
   "( e.pc_catid = 5 OR e.pc_catid = 9 OR e.pc_catid = 10 ) " .
   "LEFT OUTER JOIN forms AS f ON f.encounter = fe.encounter AND f.formdir = 'newpatient' " .
   "LEFT OUTER JOIN patient_data AS p ON p.pid = fe.pid " .
   "LEFT OUTER JOIN users AS u ON u.username = f.user " .
   "WHERE LEFT(fe.date, 10) = '$form_date' " .
   ") " .
   "ORDER BY docname, pc_startTime";

  $res = sqlStatement($query);
 }
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><? xl('Appointments and Encounters','e'); ?></title>
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
   <h2><? xl('Appointments and Encounters','e'); ?></h2>
  </td>
  <td align='right'>
   <? xl('Booking Date','e'); ?>:
   <input type='text' name='form_date' size='10' value='<? echo $_POST['form_date']; ?>'
    title='Date of appointments mm/dd/yyyy' >
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
   &nbsp;<? xl('Practitioner','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<? xl('Time','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<? xl('Patient','e'); ?>
  </td>
  <td class="dehead" align="right">
   <? xl('Chart','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <? xl('Encounter','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="right">
   <? xl('Charge','e'); ?>&nbsp;
  </td>
  <td class="dehead" align="center">
   <? xl('Billed','e'); ?>
  </td>
  <td class="dehead">
   &nbsp;<? xl('Error','e'); ?>
  </td>
 </tr>
<?
 if ($res) {
  $lastdocname = "";
  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $encounter  = $row['encounter'];
   $docname    = $row['docname'];

   $billed  = "Y";
   $errmsg  = "";
   $charges = 0;

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
    if ($code_types[$brow['code_type']]['fee']) {
     $charges += $brow['fee'];
     if ($brow['fee'] == 0 ) $errmsg = "Missing Fee";
    } else {
     if ($brow['fee'] != 0) $errmsg = "Misplaced Fee";
    }
   }
   if (! $charges) $billed = "";
?>
 <tr bgcolor='<? echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<? echo ($docname == $lastdocname) ? "" : $docname ?>
  </td>
  <td class="detail">
   &nbsp;<? echo substr($row['pc_startTime'], 0, 5) ?>
  </td>
  <td class="detail">
   &nbsp;<? echo $row['fname'] . " " . $row['lname'] ?>
  </td>
  <td class="detail" align="right">
   <? echo $row['pid'] ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? echo $row['encounter'] ?>&nbsp;
  </td>
  <td class="detail" align="right">
   <? bucks($charges) ?>&nbsp;
  </td>
  <td class="detail" align="center">
   <? echo $billed ?>
  </td>
  <td class="detail" align="left">
   &nbsp;<? echo $errmsg ?>
  </td>
 </tr>
<?
   $lastdocname = $docname;
  }
 }
?>

</table>

</form>
</center>
<script>
<?
	if ($alertmsg) {
		echo " alert('$alertmsg');\n";
	}
?>
</script>
</body>
</html>
