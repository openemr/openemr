<?php
/**
 *
 * Tracker updates
 *
 * Copyright (C) 2016 MD Support <mdsupport@users.sourceforge.net>
 *
 */

require_once("../../interface/globals.php");
require_once("../encounter_events.inc.php");
//    |-->> ("../patient_tracker.inc.php");

// Exception for mismatch in encounter_events calendar_arrived function
$today=date('Y-m-d');
if (!empty($_GET['pid'])) {
	echo 'Arrival encounter '.calendar_arrived ($_GET['pid']).' created for '.$_GET['pid'];
	exit;
}
// Used for testing ajax code
// if (empty($_POST['update_spec'])) {
//  $_POST['update_spec'] = '{"id":{"get":""},"eid":{"get":87786},
//                             "status":{"get":"*","set":"@"}, "room":{"get":"","set":""}}';
// }
$update_spec = json_decode($_POST['update_spec'], true);
$col_map = array(
		"eid" => "cal.pc_eid",
		"id" => "ptr.id",
//		"room" => "pte.room",
//		"status" => "pte.status"
);

// Base select columns allowed to be updated here
$sql_curr = "SELECT cal.pc_eid AS eid, cal.pc_pid AS pid, cal.pc_apptstatus, 
	ptr.id AS id, ptr.apptdate, ptr.appttime, ptr.random_drug_test, ptr.encounter, 
	pte.room, pte.status 
FROM `openemr_postcalendar_events` cal 
LEFT JOIN patient_tracker ptr ON cal.pc_eid = ptr.eid 
LEFT JOIN patient_tracker_element pte ON ptr.id = pte.pt_tracker_id AND ptr.lastseq = pte.seq ";

// Minimnum validation - Use supplied eid to locate original appointment and related tracker records
// sql should fail if no validation input is provided
$sql_where = '';
$sql_param = array();
foreach ($col_map as $update_key => $sql_col) {
	if (empty($update_spec[$update_key]['get'])) { continue; }
	$sql_where .= (strlen($sql_where)>0 ? ' AND ' : '').$sql_col.'=?';
	array_push($sql_param, $update_spec[$update_key]['get']);
} 
$curr_rec = sqlQuery($sql_curr.' WHERE '.$sql_where, $sql_param);

$curr_rec['no_track'] = is_null($curr_rec['id']); 

// Run thru all 'get' validations
$resp = array(
		"status" => (empty($curr_rec['eid']) ? xl("Appointment record missing") : "success"),
		"details" => array($_POST['update_spec']),
);
// Update checks work only when trkr records exist
if (!$curr_rec['no_track']) {
	foreach ($update_spec as $update_key => $update_vals) {
		if ($curr_rec[$update_key] == $update_vals['get']) { continue; }
		
		$resp['status'] = xl("Information missing or changed");
		array_push($resp['details'], 
			sprintf('%s - %s "%s", %s "%s"', $update_key, xl('Expecting'), $update_vals['get'], 
				xl('Found'), $curr_rec[$update_key])
		);
	}
}
// Return errors and exit
if ($resp['status'] != 'success') {
	echo json_encode($resp);
	exit;
}

// Proceed with updates
if ($curr_rec['no_track']) {
	// Update calendar status - Either check-in and trigger tracker update by setting encounter or perform mere status change
	if (is_checkin($update_spec['status']['set'])) { // Create encounter if permitted
		if ($GLOBALS['auto_create_new_encounters']) {
			// Assume a single appointment per patient
			$curr_rec['encounter'] = calendar_arrived($curr_rec['pid']);
			array_push($resp['details'],
					sprintf('%s - %s',
							xl('Begin tracking new encounter'), $curr_rec['encounter'])
					);
			$curr_rec['apptdate'] = date('Y-m-d');
			$curr_rec['appttime'] = date('H:i:s');
		}
	} else {
		sqlStatement("UPDATE openemr_postcalendar_events SET pc_apptstatus=?, pc_informant=?, pc_time = NOW() 
				WHERE pc_eid=? and pc_apptstatus=?",
				array($update_spec['status']['set'], $_SESSION['authUserID'], $curr_rec['eid'], $update_spec['status']['get']));
	}
}
// If just status update prior to check-in, no encounter exists 
if (!empty($curr_rec['encounter'])) {
	array_push($resp['details'],
			sprintf('%s - %s',
					xl('Update tracking information for encounter'), $curr_rec['encounter'])
			);
	manage_tracker_status($curr_rec['apptdate'], $curr_rec['appttime'], $curr_rec['eid'], $curr_rec['pid'],
			$_SESSION["authUser"], $update_spec['status']['set'], $update_spec['room']['set'], $curr_rec['encounter']);
}

echo json_encode($resp);
exit;
?>