<?php
if(!function_exists('GetEncounterForToday')) {

function GetEncounterForToday($thisPid)
{
	$thisEnc = '';
	if(!$thisPid) return $thisEnc;
	$sql = "SELECT id, date, encounter, provider_id FROM form_encounter ".
		"WHERE pid=? AND SUBSTRING(date,1,10)=? ORDER BY encounter DESC LIMIT 1";
	$frow = sqlQuery($sql, array($thisPid, date('Y-m-d')));
	if($frow{'id'}) $thisEnc = $frow{'encounter'};
	if(!$thisEnc) $thisEnc = date('Ymd');
	return($thisEnc);	
}

function LoadLinkedTriggers($thisId, $pid)
{
	// THIS FUNCTION CREATES KEYS FOR ANY JAVASCRIPT CHECKS THAT NEED
	// TO HAPPEN FROM THE RTO SCREEN
	$sql = "SHOW TABLES LIKE 'wmt_rto_links'";
	$tres = sqlStatement($sql);
	$trow = sqlFetchArray($tres);
	$frm = '';
	if(is_array($trow)) {
		if(count($trow)) $frm = array_shift($trow);
	}
	if($frm != 'wmt_rto_links') return false;
	$key = false;
 	$sql = "SELECT * FROM wmt_rto_links WHERE rto_id=? AND pid=?";
 	$lres = sqlStatement($sql, array($thisId, $pid));
	while($lrow = sqlFetchArray($lres)) {
		if($lrow{'form_name'} == 'surg1') {
			$tres = sqlStatement("SELECT id, pid, sc1_surg_date FROM form_surg1 ".
				"WHERE id=? AND pid=?",array($lrow{'form_id'},$pid)); 
			$trow = sqlFetchArray($tres);
			if($trow{'id'} == $lrow{'form_id'}) {
				if($key) $key .= '|';
				if($trow{'sc1_surg_date'}) $key = 'test_target_dt^'.$trow{'sc1_surg_date'};
			}
		}
	}
	return($key);
}

function getAllRTO($thisPid)
{
  $sql = "SELECT * FROM form_rto WHERE pid=? ".
			"ORDER BY rto_target_date, rto_status DESC";
	$all=array();
  $res = sqlStatement($sql, array($thisPid));
  for($iter =0;$row = sqlFetchArray($res);$iter++) { 
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}
  return $all;
}

function getPatientTasks($thisPid, $status='', $order='DESC')
{
  $sql = "SELECT * FROM form_rto WHERE rto_type='task' AND pid=? ";
	if($status) $sql .= "AND rto_status=? ";
			"ORDER BY date $order";
	$all=array();
	$binds = array($thisPid);
	if($status) $binds[] = $status;
  $res = sqlStatement($sql, $binds);
  for($iter =0;$row = sqlFetchArray($res);$iter++) { 
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}
  return $all;
}

function getRTObyStatus($thisPid, $status='p', $order='DESC')
{
  $sql = "SELECT * FROM form_rto WHERE pid=? ".
			"AND rto_status=? ORDER BY rto_target_date $order";
	$all = array();
  $res = sqlStatement($sql, array($thisPid, $status));
  for($iter =0;$row = sqlFetchArray($res);$iter++) {
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}
  return $all;
}

function getRTObyId($thidPid, $thisId='')
{
	$all = array();
	if(!$thisId) return $all;
  $row = sqlQuery('SELECT * FROM form_rto WHERE id=?', array($thisId));
	if(!$thisPid) $thisPid = $row{'pid'};
	$links = LoadLinkedTriggers($row{'id'}, $thisPid);
	if($links) {
		$settings = explode('|', $links);
		foreach($settings as $test) {
			$tmp = explode('^',$test);
			$key = $tmp[0];
			$val = $tmp[1];
			$row[$key] = $val;
		}
	}
	$all[] = $row;
  return $all;
}

function CreateNoteText($thisNum='',$thisFrame='',$thisAction='',$thisDate='',$thisTarget='',$thisDr='',$notes='')
{
	$num = ListLook($thisNum,'RTO_Number');
	$frame = ListLook($thisFrame,'RTO_Frame');
	$action = ListLook($thisAction,'RTO_Action');
	$text = "!!Reminder!! Order/RTO Scheduled ";
	if(!empty($thisDate) && !empty($thisNum) && !empty($thisFrame)) {
		$text .= "$num $frame from [$thisDate] ";
	}
	if(!empty($thisTarget)) $text .= "Approximately on <$thisTarget>";
	if(!empty($action)) $text .= '  For: '.$action;
	$dr = UserLook($thisDr);
	if(!empty($dr)) $text .= "\rOrdered By: ".$dr;
	$text .= "\r$notes\r";
	return $text;
}

function AddRTO($thisPid,$num='',$frame='',$stat='',$note='',$resp='',$action='', $dt='', $target='', $by='', $repeat=NULL, $stop='', $case='', $stat1 = 0)
{
	$test = false;
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($note) || !empty($action) || !empty($target)) {
		if($dt == '' || $dt == 0) $dt = date('Y-m-d');
		if($repeat == NULL) $repeat = 0;
		
		$note = $note;
  	$sql="INSERT INTO form_rto (date, pid, activity, user, groupname, ".
			"authorized, rto_date, rto_num, rto_frame, rto_status, rto_notes, ".
			"rto_resp_user, rto_action, rto_last_touch, rto_target_date, ".
			"rto_ordered_by, rto_repeat, rto_stop_date, rto_case, rto_stat) VALUES ".
			"(NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?)";
		$parms= array($thisPid, '1', $_SESSION['authUser'], 
			$_SESSION['authProvider'], $_SESSION['userauthorized'], $dt, $num, $frame,
			$stat, $note, $resp, $action, $target, $by, $repeat, $stop, $case, $stat1);
		
  	$test = sqlInsert($sql, $parms);
	}
	return $test;
}

function calculateRTOOffsetDate($num='', $frame='', $dt='') {
	if($dt == '') $dt = date('Y-m-d');
	if(!$num || !$frame) return $dt;
	if(strtolower($frame) == 'w') {
		$num = $num * 7;
		$frame = 'd';
	}
	$y = substr($dt,0,4);
	$m = substr($dt,5,2);
	$d = substr($dt,-2);
	if(strtolower($frame) == 'd') {
		$date = mktime(0,0,0,$m,($d + $num),$y);
	} else if(strtolower($frame) == 'm') {
		$date = mktime(0,0,0,$m + $num,$d,$y);
	} else if(strtolower($frame) == 'y') {
		$date = mktime(0,0,0,$m,$d,$y + $num);
	}
	$offset = date('Y-m-d', $date);
	return($offset);
}

function calculateRTOTarget($event, $dt, $interval='') {
	if(!$event) return false;
	if($dt == '') $dt = date('Y-m-d');
	if($interval == '') {
		$options = sqlQuery('SELECT * FROM list_options WHERE list_id=? AND '.
				'option_id=?', array('RTO_Action',$event));
		if(!isset($options['codes'])) $options['codes'] = '';
		$interval = $options['codes'];
	}
	$num = $frame = '';
	if($interval) list($num, $frame) = explode('|', $interval);
	if(!$num || !$frame) return $dt;
	$target = calculateRTOOffsetDate($num, $frame, $dt);
	return($target);
}

function DeleteRTO($thisPid, $item)
{
	// echo "Adding RTO in the library for PID: $thisPid</br>\n";
	$test = false;
  if(!VerifyPatientID($thisPid)) return false;
	if(!$item) return false;
  $sql="DELETE FROM form_rto WHERE id=? AND pid=?";
  sqlStatement($sql, array($item, $thisPid));

	// Delete the links if the table exists
	$sql = "SHOW TABLES LIKE 'wmt_rto_links'";
	$tres = sqlStatement($sql);
	$trow = sqlFetchArray($tres);
	$frm = array_shift($trow);
	if($frm == 'wmt_rto_links') {
  	$sql = "DELETE FROM wmt_rto_links WHERE rto_id=? AND pid=?";
  	sqlStatement($sql, array($item, $thisPid));
	}
	return $test;
}

function UpdateRTOLinks($rto,$pid,$stat='',$action='',$target='')
{
	$sql = "SHOW TABLES LIKE 'wmt_rto_links'";
	$tres = sqlStatement($sql);
	$trow = sqlFetchArray($tres);
	$frm = array_shift($trow);
	if($frm != 'wmt_rto_links') return false;

 	$sql="SELECT * FROM wmt_rto_links WHERE rto_id=? AND pid=?";
 	$lres = sqlStatement($sql, array($rto, $pid));
	$lrow = sqlFetchArray($lres);
	$form_table = 'form_'.$lrow{'form_name'};
	$fid = $lrow{'form_id'};
	$value = checkSettingMode('wmt|'.$lrow{'form_name'}.'|'.
				$lrow{'link_category'});
	if(!$value) return false;

	// echo "Our Key Value is: $value<br>\n";
	$fields = array();
	if($value) $fields = explode('|', $value);
	$date_target_field = array_shift($fields);
	$date_target_value = $target;
	$done_target_field = array_shift($fields);
	$done_target_value = 0;
	// echo "Date Target: $date_target_field<br>\n";
	// echo "Done Target: $done_target_field<br>\n";
	// echo "Our Action: $action<br>\n";
	if($action == 'sa') {
		$date_target_value = '';
	}
	if($action == 'ref_pend') {
	}
	if($action == 'ref_rcv') {
		$done_target_value = 1;
	}
	// echo "Completed: $completed_status   Ours: $stat<br>\n";
	// echo "Form Table: $form_table<br>\n";
	if(isComplete($stat)) {
		if($form_table == 'form_whc_iud') {
			if(!$date_target_value) $date_target_value = date('Y-m-d');
			$done_target_value = 1;
		}
	} else {
		if($form_table == 'form_whc_iud') {
			$date_target_value = '';
			$done_target_value = '';
		}
	}
	// echo "Target Date Set: $date_target_value<br>\n";
	// echo "Target Done Set: $done_target_value<br>\n";
	if(!$date_target_value && !$done_target_value) return false;
	$update = '';
	$parms = array();
	if($date_target_field) { 
		$update = "$date_target_field=?";
		$parms[] = $date_target_value;
	}
	// echo "This is the done target field! : $done_target_field<br>\n";
	if($done_target_field) { 
		// echo "We should be here!!<br>\n";
		if($update) $update .= ', ';
		$update .= "$done_target_field=?";
		$parms[] = $done_target_value;
	}
	// echo "Update: $update<br>\n";
	if(!$update) return false;
	$parms[] = $fid;
	$parms[] = $pid;
	$sql = "UPDATE $form_table SET $update WHERE id=? AND pid=?";
	$test = sqlStatement($sql, $parms);
	// echo "SQL Statement: $sql<br>\n";
	// echo "Date Target Value: $date_target_value<br>\n";
	// echo "Done Target Value: $done_target_value<br>\n";
	// echo "Form: $form_table  ID ($fid)<br>\n";
}

function UpdateRTO($thisPid,$item,$num='',$frame='',$stat='',$note='',$resp='',$action='', $dt='', $target='', $by='', $force_msg=false, $repeat=NULL, $stop=NULL, $case=NULL, $stat1 = NULL)
{
  if(!VerifyPatientID($thisPid)) return false;
	if(!empty($num) || !empty($frame) || !empty($stat) || !empty($note) || 
		!empty($resp) || !empty($action) || ($repeat != NULL) || ($stop != NULL)) {
		// If the responsible user or action has changed we need to store
		$rrow = sqlQuery('SELECT form_rto.*, '.
			'o.codes, o.title FROM form_rto LEFT JOIN '.
			'(SELECT * FROM list_options WHERE list_id="RTO_Action") AS o '.
			'on option_id = rto_action '.
			'WHERE pid=? AND id=?', array($thisPid, $item));

		// echo "This is our existing Row ($thisPid, $item): ";
		// print_r($rrow);
		// echo "<br>\n";
		$status_change = $rto_completed = false;
		if($stat != $rrow{'rto_status'}) {
			$status_change = true;
			if(isComplete($stat)) $rto_completed = true;
		}

		$resp_change = false;
		if($resp != $rrow{'rto_resp_user'}) {
			sqlStatement("UPDATE form_rto SET rto_last_resp_user=? ".
				"WHERE pid=? AND id=?", array($resp, $thisPid, $item));
			$resp_change= true;
		}
		if($resp_change || $force_msg) {
			$text= CreateNoteText($num, $frame, $action, $dt, $target, $by, $note);
			addPnote($thisPid,$text,$_SESSION['userauthorized'],'1','New Orders',$resp);
		}

		if($action != $rrow{'rto_action'}) {
			sqlStatement("UPDATE form_rto SET rto_last_action=? ".
				"WHERE pid=? AND id=?", array($action, $thisPid, $item));
		}

		// Is This is Billable Action and is it complete?
		// if($rrow{'codes'}) {
			// echo "Billable<br>\n";
			// echo "Complete Status Is: $completed_status<br>\n";
			// if($stat == $completed_status) {
				// echo "Adding Forms<br>\n";
				// $thisEnc = GetEncounterForToday($thisPid);
				// addForm($thisEnc, 'Completed Orders', 0, $thisPid, 
								// $_SESSION['userauthorized']);
				// LinkListEntry($thisPid, $item, $thisEnc, 'rto', false, true); 
			// }
		// }

		// ONLY IF CHANGED DO WE REALLY WANT UPDATE AND LOG THE TOUCHING
		if($repeat == NULL) $repeat = 0;
		if($num == '') $num = 0;
		if($rrow['rto_num'] == '') $rrow['rto_num'] = 0;
		/*
		if($dt != $rrow['rto_date']) echo "Date is a culrpit!<br>\n";
		if($num != $rrow['rto_num']) echo "Number is a culrpit!<br>\n";
		if($frame != $rrow['rto_frame']) echo "Frame is a culrpit!<br>\n";
		if($stat != $rrow['rto_status']) echo "Status is a culrpit!<br>\n";
		if($note != $rrow['rto_notes']) echo "Notes is a culrpit!<br>\n";
		if($resp != $rrow['rto_resp_user']) echo "Resp User is a culrpit!<br>\n";
		if($action != $rrow['rto_action']) echo "Action is a culrpit!<br>\n";
		if($target != $rrow['rto_target_date']) echo "Target is a culrpit!<br>\n";
		if($by != $rrow['rto_ordered_by']) echo "By is a culrpit!<br>\n";
		if($repeat != $rrow['rto_repeat']) echo "Repeat is a culrpit!<br>\n";
		if($stop != $rrow['rto_stop_date']) echo "Stop is a culrpit!<br>\n";
		exit;
		*/
		if( ($dt != $rrow['rto_date']) || ($num != $rrow['rto_num']) ||
			($frame != $rrow['rto_frame']) || ($stat != $rrow['rto_status']) ||
			($note != $rrow['rto_notes']) || ($resp != $rrow['rto_resp_user']) ||
			($action != $rrow['rto_action']) || ($target != $rrow['rto_target_date'])
			|| ($by != $rrow['rto_ordered_by']) || ($repeat != $rrow['rto_repeat'])
			|| ($stop != $rrow['rto_stop_date']) || ($case != $rrow['rto_case']) || ($stat1 !== NULL && $stat1 != $rrow['rto_stat'])) {

			$parms= array($dt, $num, $frame, $stat, $note, $resp, $action, $target, 
				$by, $repeat, $stop, $case, $_SESSION['authUserID'], $thisPid, $item);

			$dQuery = "";
			if($stat1 !== NULL) {
				$dQuery =", rto_stat = ? ";
				$parms= array($dt, $num, $frame, $stat, $note, $resp, $action, $target, 
				$by, $repeat, $stop, $case, $_SESSION['authUserID'], $stat1, $thisPid, $item);
			}

  		sqlStatement("UPDATE form_rto SET rto_last_touch=NOW(), rto_date=?, ".
				"rto_num=?, rto_frame=?, rto_status=?, rto_notes=?, rto_resp_user=?, ".
				"rto_action=?, rto_target_date=?, rto_ordered_by=?, rto_repeat=?, ".
				"rto_stop_date=?, rto_case=?, rto_touch_by=? $dQuery WHERE pid=? AND id=?",
			 	$parms);
		}

		// NOW DEAL WITH CREATING A NEW REPEATING EVENT IF APPLICABLE
		if($repeat && $rto_completed) resolveRepeatingRTO($item);	
	}

	UpdateRTOLinks($item, $thisPid, $stat, $action, $target);
}

function resolveRepeatingRTO($id)
{
	$rto = getRTObyId('',$id);
	if(checkSettingMode('wmt::repeat_rto_by_due_date')) {
		$from_date = $rto['rto_target_date'];
	} else {
		$from_date = date('Y-m-d');
	}
 	$new_target = calculateRTOTarget($rto{'rto_action'}, $from_date, 
		$rto{'rto_num'}.'|'.$rto{'rto_frame'});
	if($rto{'rto_stop_date'} && $rto{'rto_stop_date'} != '0000-00-00') {
		if($new_target >= $rto['rto_stop_date']) return false;
	}
	// CHECK FOR A TIME WINDOW TO CHECK FOR THIS EVENT
	$action = sqlQuery('SELECT * FROM list_options WHERE list_id=? '.
		'AND option_id=?',array('RTO_Action',$rto['rto_action']));
	$binds = array($rto{'pid'}, $rto{'rto_action'}, 'p');
	$sql = 'SELECT rto.* FROM form_rto AS rto WHERE '.
		'rto.pid = ? AND rto_action = ? AND rto_status = ? ';
	$num = $frame = '';
	if($action{'mapping'}) {
		list($num, $frame) = explode('|',$action{'mapping'});
		$from = calculateRTOOffsetDate((-1 * $num), $frame, $target_date);
		$to = calculateRTOOffsetDate($num, $frame, $target_date);
		$sql .= 'AND rto_target_date >= ? AND rto_target_date <= ? ';
		$binds[] = $from;
		$binds[] = $to;
	}
	$sql .= 'ORDER BY date DESC LIMIT 1';
	echo "This is the check: $sql< br>\n";
	print_r($binds);
	echo "<br>\n";
	$exists = sqlQuery($sql, $binds);
	if(!isset($exists{'id'})) $exists{'id'} = '';
	if($exists{'id'}) return;
	echo "No existing conflict round, continuing<br>\n";
	$test = AddRTO($rto{'pid'},$rto{'rto_num'},$rto{'rto_frame'},'p',$rto{'rto_notes'},$rto{'rto_resp_user'},$rto{'rto_action'},date('Y-m-d'),$new_target,$rto{'rto_ordered_by'},$rto{'rto_repeat'},$rto{'rto_stop_date'});
	if($test) {
		$text = CreateNoteText($rto{'rto_num'},$rto{'rto_frame'},$rto{'rto_action'},date('Y-m-d'),$new_target,$rto{'rto_ordered_by'},$rto{'rto_notes'});
		$title = 'New Orders';
		$text .= "\rThis order automatically generated as a repeating order";
		addPnote($pid,$text,$_SESSION['userauthorized'],'1',$title,$rto{'rto_resp_user'});
	}
	
}

function CheckForLinkedRTO($thisPid,$form_name,$form_id,$category='')
{
	$ret = false;
	if(!$thisPid) ReportMissingPID();
	if(!$form_name) {
		echo "<h><b>Order/RTO Link Check Called With NO Form Name</b></h><br>\n";
		echo "<h2>Please report this error and the circumstances to support.</h2<br>\n";
		exit;
	}
	if(!$form_id) ReportMissingID();
	$parms = array($form_name, $form_id, $thisPid, $category);
	$fres = sqlStatement("SELECT * FROM wmt_rto_links WHERE form_name=? AND ".
			"form_id=? AND pid=? AND link_category=?",$parms);
	if($fres) {
		$frow = sqlFetchArray($fres);
		$ret = $frow{'rto_id'};
	}	
	return $ret;
}

function GetAllLinkedRTO($thisPid,$form_name,$form_id,$category='')
{
	$all = array();
	if(!$thisPid) ReportMissingPID();
	if(!$form_name) {
		echo "<h><b>Order/RTO Link Check Called With NO Form Name</b></h><br>\n";
		echo "<h2>Please report this error and the circumstances to support.</h2<br>\n";
		exit;
	}
	if(!$form_id) ReportMissingID();
	$binds = array($form_name, $form_id, $thisPid);
	$sql = "SELECT rto_id, form_id, form_rto.* FROM wmt_rto_links ".
			" LEFT JOIN form_rto ON (rto_id = form_rto.id) WHERE form_name=? AND ".
			"form_id=? AND wmt_rto_links.pid=?";
	if($category) {
		$sql .= " AND link_category=?";
		$binds[] = $category;
	}
  for($iter =0;$row = sqlFetchArray($fres);$iter++) {
		$links = LoadLinkedTriggers($row{'id'}, $thisPid);
		if($links) {
			$settings = explode('|', $links);
			foreach($settings as $test) {
				$tmp = explode('^',$test);
				$key = $tmp[0];
				$val = $tmp[1];
				$row[$key] = $val;
			}
		}
		$all[] = $row;
	}	
	return $all;
}

function AddRTOLink($thisPid,$form_name,$form_id,$rto_id,$category='')
{
	$ret = false;
	if(!$thisPid) ReportMissingPID();
	if(!$form_name) {
		echo "<h><b>Order/RTO Add Link Called With NO Form Name</b></h><br>\n";
		echo "<h2>Please report this error and the circumstances to support.</h2<br>\n";
		exit;
	}
	if(!$rto_id) {
		echo "<h><b>Order/RTO Add Link Called With NO RTO Link ID</b></h><br>\n";
		echo "<h2>Please report this error and the circumstances to support.</h2<br>\n";
		exit;
	}
	if(!$form_id) ReportMissingID();
	
	$parms = array($form_name, $form_id, $rto_id, $thisPid, $category);
	$test = sqlInsert("INSERT INTO wmt_rto_links (form_name, form_id, rto_id, ".
			"pid, link_category) VALUES (?,?,?,?,?)",$parms);
	return $test;
}

function isComplete($status)
{
 	$sql='SELECT * FROM list_options WHERE UPPER(list_id)="RTO_STATUS" AND '.
		'option_id=?';
 	$lrow = sqlQuery($sql, array($status));
	if(strpos(strtolower($lrow{'notes'}),'complete') !== false) return true;
	return false;
}
}
?>
