<?php

// Update any other existing RTO's in case they changed
$cnt=1;
// echo "All the data: ";
// print_r($dt);
// echo "<br>\m";
// echo '<pre>';
// print_r($dt);
// echo '</pre>';
// exit();
while($cnt <= $dt['tmp_rto_cnt']) {
	if($dt['rto_status_'.$cnt] == '') $dt['rto_status_'.$cnt] = 'p';
	if(!isset($dt['rto_repeat_'.$cnt])) $dt['rto_repeat_'.$cnt] = '';
	if(!isset($dt['rto_stat_'.$cnt])) $dt['rto_stat_'.$cnt] = 0;
	// echo "Going to Update ($cnt)<br>\n";

	$rto_data_bup = getRTObyId($pid, $dt['rto_id_'.$cnt]);

	UpdateRTO($pid,$dt['rto_id_'.$cnt],$dt['rto_num_'.$cnt],
		$dt['rto_frame_'.$cnt],$dt['rto_status_'.$cnt],$dt['rto_notes_'.$cnt],
		$dt['rto_resp_'.$cnt],$dt['rto_action_'.$cnt],$dt['rto_date_'.$cnt],
		$dt['rto_target_date_'.$cnt],$dt['rto_ordered_by_'.$cnt],false,
		$dt['rto_repeat_'.$cnt],$dt['rto_stop_date_'.$cnt], $dt['rto_case_'.$cnt], $dt['rto_stat_'.$cnt]);
	
	// OEMR - Change
	rtoBeforeSave($pid);

	$cnt++;
}

if($dt['rto_status'] == '') $dt['rto_status'] = 'p';
if(!isset($dt['rto_repeat'])) $dt['rto_repeat'] = '';
$test = AddRTO($pid,$dt['rto_num'],$dt['rto_frame'],$dt['rto_status'],
	$dt['rto_notes'],$dt['rto_resp_user'],$dt['rto_action'],$dt['rto_date'],
	$dt['rto_target_date'],$dt['rto_ordered_by'],$dt['rto_repeat'],
	$dt['rto_stop_date'], $dt['rto_case'], $dt['rto_stat']);
if($test) {
	if($mode == 'new_rto_save') {
		$neworderid = $test;
	}

	$text = CreateNoteText($dt['rto_num'],$dt['rto_frame'],$dt['rto_action'],
		$dt['rto_date'],$dt['rto_target_date'],$dt['rto_ordered_by'],
		$dt['rto_notes']);
	$title = 'New Orders';
	$noteId = addPnote($pid,$text,$_SESSION['userauthorized'],'1',$title,$dt['rto_resp_user']);

	/* OEMR - Changes */
	$relation_id = isset($noteId) && !empty($noteId) ? $noteId : NULL;
	saveOrderLog("INTERNAL_NOTE", $test, $relation_id, NULL, $pid, 'Created', $_SESSION['authUserID']);
	/* End */
}