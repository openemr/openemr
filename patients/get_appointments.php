<?php
/**
 *
 * Copyright (C) 2016-2017 Jerry Padgett <sjpadgett@gmail.com>
 * Copyright (C) 2011 Cassian LUP <cassi.lup@gmail.com>
 *
 * LICENSE: This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 3
 * of the License, or (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://opensource.org/licenses/gpl-license.php>;.
 *
 * @package OpenEMR
 * @author Cassian LUP <cassi.lup@gmail.com>
 * @author Jerry Padgett <sjpadgett@gmail.com>
 * @link http://www.open-emr.org 
 *
 */
 require_once ("verify_session.php");

$query = "SELECT e.pc_eid, e.pc_aid, e.pc_title, e.pc_eventDate, " . "e.pc_startTime, e.pc_hometext, e.pc_apptstatus, u.fname, u.lname, u.mname, " . 
"c.pc_catname " . "FROM openemr_postcalendar_events AS e, users AS u, " .
"openemr_postcalendar_categories AS c WHERE " . "e.pc_pid = ? AND e.pc_eventDate >= CURRENT_DATE AND " . "u.id = e.pc_aid AND e.pc_catid = c.pc_catid " . "ORDER BY e.pc_eventDate, e.pc_startTime";

$res = sqlStatement ( $query, array (
		$pid
) );

if (sqlNumRows ( $res ) > 0) {
	$count = 0;
	echo '<table id="appttable" style="width:100%;background:#eee;" class="table table-striped fixedtable"><thead>
</thead><tbody>';
	while ( $row = sqlFetchArray ( $res ) ) {
		$count ++;
		$dayname = xl ( date ( "l", strtotime ( $row ['pc_eventDate'] ) ) );
		$dispampm = "am";
		$disphour = substr ( $row ['pc_startTime'], 0, 2 ) + 0;
		$dispmin = substr ( $row ['pc_startTime'], 3, 2 );
		if ($disphour >= 12) {
			$dispampm = "pm";
			if ($disphour > 12)
				$disphour -= 12;
		}
		if ($row ['pc_hometext'] != "") {
			$etitle = 'Comments' . ": " . $row ['pc_hometext'] . "\r\n";
		} else {
			$etitle = "";
		}
		echo "<tr><td><p>";
		echo "<a href='#' onclick='editAppointment(0," . htmlspecialchars ( $row ['pc_eid'], ENT_QUOTES ) . ')' . "' title='" . htmlspecialchars ( $etitle, ENT_QUOTES ) . "'>";
		echo "<b>" . htmlspecialchars ( $dayname . ", " . $row ['pc_eventDate'], ENT_NOQUOTES ) . "</b><br>";
		echo htmlspecialchars ( "$disphour:$dispmin " . $dispampm . " " . $row ['pc_catname'], ENT_NOQUOTES ) . "<br>";
		echo htmlspecialchars ( $row ['fname'] . " " . $row ['lname'], ENT_NOQUOTES ) . "<br>";
		echo htmlspecialchars ( "Status: " . $row ['pc_apptstatus'] , ENT_NOQUOTES );
		echo "</a></p></td></tr>";
		
	}
	if (isset ( $res ) && $res != null) {
		if ($count < 1) {
			echo "&nbsp;&nbsp;" . xlt('None');
		}
	}
} else { // if no appts
	echo xlt('No Appointments');
}
echo '</tbody></table>';
?>
<div style='margin: 5px 0 5px'>
	<a href='#' onclick="editAppointment('add',<?php echo $pid; ?>)"><button
			class='btn btn-primary pull-right'><?php echo xlt('Schedule New Appointment'); ?></button></a>
</div>
<script>
	function editAppointment(mode,deid){
		if(mode == 'add'){
			var title = 'Request New Appointment';
			var mdata = {pid:deid};
		}
		else{
			var title = 'Edit Appointment';
			var mdata = {eid:deid};
		}
		var params = {
			buttons: [
			   { text: 'Cancel', close: true, style: 'default' },
			   //{ text: 'Print', close: false, style: 'success', click: showCustom }
			],
			title: title,
			url: './add_edit_event_user.php',
			data: mdata
		};
		return eModal
			.ajax(params)
			.then(function () {  });
	};
</script>