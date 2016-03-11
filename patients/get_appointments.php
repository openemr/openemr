<?php
 // Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 //
 // This program is free software; you can redistribute it and/or
 // modify it under the terms of the GNU General Public License
 // as published by the Free Software Foundation; either version 2
 // of the License, or (at your option) any later version.
 //
 // Modified to support recurring appointments by Ian Jardine 2016.

	require_once("verify_session.php");
	///////
	require_once(dirname(__FILE__)."/../library/appointments.inc.php");

	$current_date2 = date('Y-m-d');
	$events = array();
	$apptNum = (int)$GLOBALS['patient_portal_appt_display_num'];
	if($apptNum != 0) $apptNum2 = abs($apptNum);
	else $apptNum2 = 20;
	$events = fetchNextXAppts($current_date2, $pid, $apptNum2);
	///////

	$count = 0;

	foreach($events as $row) {
		$count++;
		$dayname = xl(date("l", strtotime($row['pc_eventDate'])));
		$dispampm = "am";
		$disphour = substr($row['pc_startTime'], 0, 2) + 0;
		$dispmin  = substr($row['pc_startTime'], 3, 2);
		if ($disphour >= 12) {
			$dispampm = "pm";
			if ($disphour > 12) $disphour -= 12;
		}
		if ($row['pc_hometext'] != "") {
			$etitle = 'Comments'.": ".$row['pc_hometext']."\r\n";
		} else {
			$etitle = "";
		}
		if ($GLOBALS['portal_onsite_appt_modify']) {
			echo "<a href='add_edit_event_user.php?date=" . htmlspecialchars(preg_replace("/-/", "", $row['pc_eventDate']),ENT_QUOTES) . 
			"&eid=" . htmlspecialchars($row['pc_eid'],ENT_QUOTES) .
			"' class='edit_event iframe' title='" . htmlspecialchars($etitle,ENT_QUOTES) . "'>";
		}
		echo "<b>" . htmlspecialchars($row['pc_eventDate'] . " (" . $dayname . ")" ,ENT_NOQUOTES) . "</b><br>";
		echo htmlspecialchars("$disphour:$dispmin " . $dispampm . " " . $row['pc_catname'],ENT_NOQUOTES) . "<br>\n";
		echo htmlspecialchars($row['ufname'] . " " . $row['ulname'],ENT_NOQUOTES);
		if ($GLOBALS['portal_onsite_appt_modify']) {
			echo "</a><br>\n";
		}
		else {
			echo "<br>\n";
		}
	}
	if ($resNotNull) {
		if ( $count < 1 ) { echo "&nbsp;&nbsp;" . htmlspecialchars('No Appointments',ENT_NOQUOTES); }
	}
?>
