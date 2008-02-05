<?
 // Copyright (C) 2005 Rod Roark <rod@sunsetsystems.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.

 // This report shows upcoming appointments with filtering and
 // sorting by patient, practitioner, appointment type, and date.

 include_once("../globals.php");
 include_once("../../library/patient.inc");

 $alertmsg = ''; // not used yet but maybe later

 // For each sorting option, specify the ORDER BY argument.
 //
 $ORDERHASH = array(
  'doctor'  => 'lower(u.lname), lower(u.fname), pc_eventDate, pc_startTime',
  'patient' => 'lower(p.lname), lower(p.fname), pc_eventDate, pc_startTime',
  'time'    => 'pc_eventDate, pc_startTime, lower(u.lname), lower(u.fname)',
  'type'    => 'pc_catname, pc_eventDate, pc_startTime, lower(u.lname), lower(u.fname)'
 );

 function bucks($amount) {
  if ($amount)
   printf("%.2f", $amount);
 }

 $patient = $_REQUEST['patient'];

 if ($patient && ! $_POST['form_from_date']) {
  // If a specific patient, default to 2 years ago.
  $tmp = date('Y') - 2;
  $from_date = date("$tmp-m-d");
 } else {
  $from_date = fixDate($_POST['form_from_date'], date('Y-m-d'));
 }
 $to_date   = fixDate($_POST['form_to_date'], '');
 $provider  = $_POST['form_provider'];
 $facility  = $_POST['form_facility'];  //(CHEMED) facility filter

 $form_orderby = $ORDERHASH[$_REQUEST['form_orderby']] ?
  $_REQUEST['form_orderby'] : 'time';
 $orderby = $ORDERHASH[$form_orderby];

 $where = "e.pc_pid != '' AND e.pc_eventDate >= '$from_date'";

 if ($to_date ) $where .= " AND e.pc_eventDate <= '$to_date'";
 if ($provider) $where .= " AND e.pc_aid = '$provider'";
 //(CHEMED) facility filter
 $facility_filter = '';
 if ($facility) {
     $event_facility_filter = " AND e.pc_facility = '$facility'";
     $provider_facility_filter = " AND users.facility_id = '$facility'";
 }
 //END (CHEMED)
 if ($patient ) $where .= " AND e.pc_pid = '$patient'";

 // Get the info.
 //
 $query = "SELECT " .
  "e.pc_eventDate, e.pc_startTime, e.pc_catid, e.pc_eid, " .
  "p.fname, p.mname, p.lname, p.pid, " .
  "u.fname AS ufname, u.mname AS umname, u.lname AS ulname, " .
  "c.pc_catname " .
  "FROM openemr_postcalendar_events AS e " .
  "LEFT OUTER JOIN patient_data AS p ON p.pid = e.pc_pid " .
  "LEFT OUTER JOIN users AS u ON u.id = e.pc_aid " .
  "LEFT OUTER JOIN openemr_postcalendar_categories AS c ON c.pc_catid = e.pc_catid " .
  "WHERE $where $event_facility_filter ORDER BY $orderby";  //(CHEMED) facility filter

 $res = sqlStatement($query);
?>
<html>
<head>
<link rel=stylesheet href="<?echo $css_header;?>" type="text/css">
<title><?php xl('Appointments Report','e'); ?></title>

<script type="text/javascript" src="../../library/overlib_mini.js"></script>
<script type="text/javascript" src="../../library/calendar.js"></script>
<script type="text/javascript" src="../../library/textformat.js"></script>
<script type="text/javascript" src="../../library/dialog.js"></script>

<script LANGUAGE="JavaScript">

 var mypcc = '<?php echo $GLOBALS['phone_country_code'] ?>';

 function dosort(orderby) {
  var f = document.forms[0];
  f.form_orderby.value = orderby;
  f.submit();
  return false;
 }

 function oldEvt(eventid) {
  dlgopen('../main/calendar/add_edit_event.php?eid=' + eventid, '_blank', 550, 270);
 }

 function refreshme() {
  // location.reload();
  document.forms[0].submit();
 }

</script>

</head>

<body leftmargin='0' topmargin='0' marginwidth='0' marginheight='0'>

<!-- Required for the popup date selectors -->
<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>

<center>

<h2><?php xl('Appointments Report','e'); ?></h2>

<form method='post' name='theform' action='appointments_report.php'>

<table border='0' cellpadding='3'>

 <tr>
  <td>
<?php //(CHEMED) Facility filter

 xl('Facility','e');
 // Build a drop-down list of facilities.
 //
 $query = "SELECT id, name FROM facility WHERE " .
  "service_location = 1 ORDER BY name ";
 $ures = sqlStatement($query);
 echo "   <select name='form_facility' onChange='document.all.theform.submit()'>\n";
 echo "    <option value=''>-- All --\n";
 while ($urow = sqlFetchArray($ures)) {
  $fid = $urow['id'];
  echo "    <option value='$fid'";
  if ($fid == $_POST['form_facility']) echo " selected";
  echo ">" . $urow['name'] . "\n";
 }
 echo "   </select>\n";
//END (CHEMED) Facility filter?>

   <?php xl('Provider','e'); ?>:
<?
 // Build a drop-down list of providers.
 //
 $query = "SELECT id, lname, fname FROM users WHERE ".
  "authorized = 1 $provider_facility_filter ORDER BY lname, fname"; //(CHEMED) facility filter
 $ures = sqlStatement($query);
 echo "   <select name='form_provider'>\n";
 echo "    <option value=''>-- All --\n";
 while ($urow = sqlFetchArray($ures)) {
  $provid = $urow['id'];
  echo "    <option value='$provid'";
  if ($provid == $_POST['form_provider']) echo " selected";
  echo ">" . $urow['lname'] . ", " . $urow['fname'] . "\n";
 }
 echo "   </select>\n";
?>

   &nbsp;<?php  xl('From','e'); ?>:
   <input type='text' name='form_from_date' size='10' value='<?php echo $from_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_from_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;<?php  xl('To','e'); ?>:
   <input type='text' name='form_to_date' size='10' value='<?php echo $to_date ?>'
    onkeyup='datekeyup(this,mypcc)' onblur='dateblur(this,mypcc)' title='yyyy-mm-dd'>
   <a href="javascript:show_calendar('theform.form_to_date')"
    title=".xl('Click here to choose a date')."
    ><img src='../pic/show_calendar.gif' align='absbottom' width='24' height='22' border='0'></a>
   &nbsp;
   <input type='submit' name='form_refresh' value='<?php  xl('Refresh','e'); ?>'>
  </td>
 </tr>

 <tr>
  <td height="1">
  </td>
 </tr>

</table>

<table border='0' cellpadding='1' cellspacing='2' width='98%'>

 <tr bgcolor="#dddddd">
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('doctor')"
   <?php if ($form_orderby == "doctor") echo " style=\"color:#00cc00\"" ?>><?php  xl('Provider','e'); ?> </a>
  </td>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('time')"
   <?php if ($form_orderby == "time") echo " style=\"color:#00cc00\"" ?>><?php  xl('Time','e'); ?></a>
  </td>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('patient')"
   <?php if ($form_orderby == "patient") echo " style=\"color:#00cc00\"" ?>><?php  xl('Patient','e'); ?></a>
  </td>
  <td class="dehead">
   <a href="nojs.php" onclick="return dosort('type')"
   <?php if ($form_orderby == "type") echo " style=\"color:#00cc00\"" ?>><?php  xl('Type','e'); ?></a>
  </td>
 </tr>
<?
 if ($res) {
  $lastdocname = "";
  while ($row = sqlFetchArray($res)) {
   $patient_id = $row['pid'];
   $docname  = $row['ulname'] . ', ' . $row['ufname'] . ' ' . $row['umname'];
   $errmsg  = "";
?>
 <tr bgcolor='<?php echo $bgcolor ?>'>
  <td class="detail">
   &nbsp;<?php echo ($docname == $lastdocname) ? "" : $docname ?>
  </td>
  <td class="detail">
   &nbsp;<a href='javascript:oldEvt(<?echo $row['pc_eid'] ?>)'>
   <?php echo $row['pc_eventDate'] . ' ' . substr($row['pc_startTime'], 0, 5) ?>
   </a>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['fname'] . " " . $row['lname'] ?>
  </td>
  <td class="detail">
   &nbsp;<?php echo $row['pc_catname'] ?>
  </td>
 </tr>
<?
   $lastdocname = $docname;
  }
 }
?>

</table>

<input type="hidden" name="form_orderby" value="<?php echo $form_orderby ?>" />
<input type="hidden" name="patient" value="<?php echo $patient ?>" />

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
