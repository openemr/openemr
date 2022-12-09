<?php

function GetROSKeyComment($id=0, $frmn='', $key='', $pid='') {
	if(!$key) return '';
	if(!$frmn && !$pid) return '';
	if(substr($key,-3) == '_nt') $key = substr($key,0,-3);
	$binds = array($id, $frmn, $key);
	$sql = 'SELECT note FROM form_wmt_form_notes WHERE '.
		'link_id=? AND link_name=? AND link_field=?';
	if(!$frmn) {
		$binds = array($pid, $key);
		$sql = 'SELECT note FROM form_wmt_form_notes WHERE '.
		'pid=? AND link_field=? ORDER BY date DESC';
	}
	 // echo "Using $sql<br>\n";
	 // echo "Attempting to get key: ";
   // print_r($binds);
   // echo "<br>\n";
	$frow = sqlQuery($sql, $binds);
	if(!isset($frow{'note'})) $frow{'note'} = '';
   // echo "Found: (".$frow{'note'}."]<br>\n";
	return($frow{'note'});	
}

function ProcessROSKeyComment($pid, $id, $frmn, $key='', $nt='', $source='') {
  if($frmn != 'dashboard' && (substr($key,0,6) == 'fh_rs_')) {
		$db = wmtDashboard::getPidDashboard($pid);
		$id = $db->id;
	}
	if(!$id || !$frmn) { 
		echo "<h>Fatal Error - Key Comment call with no form ID or Name</h><br>\n";
		echo "<br><b>Please report this to support</b><br><br>\n";
		exit;
	}
	if(!$key) return '';
	if(substr($key,-3) == '_nt') $key = substr($key,0,-3);
	if($nt == '') {
		$sql = "DELETE FROM form_wmt_form_notes WHERE link_id=? AND ".
			"link_name=? AND link_field=?";
		$parms = array($id, $frmn, $key);
		sqlStatementNoLog($sql, $parms);
	} else {
		$sql = "INSERT INTO form_wmt_form_notes ".
			"(date, pid, user, groupname, authorized, activity, link_id, link_name, ".
			"link_field, note, input_source) VALUES (NOW(), ?, ?, ?, ?, 1, ?, ?, ?, ".
			"?, ?) ON DUPLICATE KEY UPDATE note=?";
		$parms = array($pid, $_SESSION['authUser'], $_SESSION['authProvider'],
			$_SESSION['userauthorized'], $id, $frmn, $key, $nt, $source, $nt);
		sqlInsert($sql, $parms);
	}
}

function GenerateROSLine($id, $title, $chc='', $txt='', $section='', $off=true, $show_note=true, $style='wmt') {
	$nt = $id . '_nt';
	$td = $id . '_td';
	echo "			<tr>\n";
  echo "				<td style='width: 6px;'>&nbsp;</td>\n";
  echo "				<td class='".$style."Body wmtClick' onclick=\"toggleThroughSelect('$id'); ";
	if($off) echo "toggleROSChecksOff(document.getElementById('$id'),'$section'); ";
	echo "AdjustFocus('$nt');\" >$title</td>\n";
	echo "				<td style='width: 52px;' id='$td' >";
	echo "					<select class='wmtHoverAccent' name='$id' id='$id' class='".$style."Input' ";
	if($off) echo "onchange='toggleROSChecksOff(this,\"$section\");' ";
	echo ">\n";
	ListSel($chc,'Yes_No');
	echo "				</select></td>\n";
	if($show_note) {
		echo "				<td><input name='$nt' id='$nt' class='".$style."FullInput' type='text' value='".htmlspecialchars($txt, ENT_QUOTES)."' /></td>\n";
	}
	echo "				<td style='width: 6px;'>&nbsp;</td>\n";
	echo "			</tr>\n";
}

function GenerateYesNoLine($pre, $id, $title, $yes_list='', $no_list='') {
	$yes_id = $pre . $id . '_yes';
	$no_id = $pre . $id . '_no';
	echo "			<tr>\n";
  echo "				<td style='width: 6px;'>&nbsp;</td>\n";
  echo "				<td class='wmtBody'>$title</td>\n";
	echo "				<td style='width: 50px;'>";
	echo "					<input name='$yes_id' id='$yes_id' type='checkbox' ";
	if(strpos($yes_list, '~'.$id.'~') !== false) echo " checked ";
	echo "value='$id' onchange='TogglePair(\"$yes_id\",\"$no_id\");' /></td>\n";
	echo "				<td style='width: 50px;'>";
	echo "					<input name='$no_id' id='$no_id' type='checkbox' ";
	if(strpos($no_list, '~'.$id.'~') !== false) echo " checked ";
	echo "value='$id' onchange='TogglePair(\"$no_id\",\"$yes_id\");' /></td>\n";
	echo "				<td style='width: 6px;'>&nbsp;</td>\n";
	echo "			</tr>\n";
}

function GenerateHiddenROS($id, $chc='') {
	$chc = trim($chc);
	if(!$chc) return '';
	echo "		<input name='$id' id='$id'  type='hidden' value='".htmlspecialchars($chc, ENT_QUOTES)."' />\n";
}

function GenerateHiddenYesNo($pre, $id, $yes='', $no='') {
	$new_id = '';
	if(strpos($yes, '~'.$id.'~') !== false) $new_id = $pre . $id. '_yes';
	if(strpos($no, '~'.$id.'~') !== false) $new_id = $pre . $id. '_no';
	echo "		<input name='$new_id' id='$new_id'  type='hidden' value='$id' />\n";
}

function LoadROSFormComments($id, $frm) {
	$all = array();
	if(!$id || !$frm) return $all;
  $rlist= sqlStatementNoLog("SELECT * FROM form_wmt_form_notes WHERE ".
		"link_id=? AND link_name=?", array($id, $frm));
  while($row = sqlFetchArray($rlist)) {
  	$all[$row{'link_field'}] = $row{'note'};
	}
	return $all;
}

function loadFormROS($module, $fid, $fname) {
	global $ros_options, $wmt_ros, $rs;
	if($module == 'ros2' || $module == 'ent_ros') {
		if($fid) $wmt_ros = sqlQuery("SELECT * FROM form_wmt_ros WHERE link_id=? ".
			"AND link_name=?", array($fid, $fname));
		if(!isset($wmt_ros{'id'})) $wmt_ros{'id'} = '';
		foreach($rs as $key => $val) { $rs[$key] = ''; }
		if(!$wmt_ros{'id'}) {
			
			$flds = sqlListFields('form_wmt_ros');
			$flds = array_slice($flds,7);
			foreach($flds as $key => $fld) { $wmt_ros[$fld]=''; }
		}
		if($wmt_ros{'ros_yes'} == '') $wmt_ros{'ros_yes'} = '|';
		if($wmt_ros{'ros_no'} == '') $wmt_ros{'ros_no'} = '|';
		$chc = explode('|', $wmt_ros{'ros_yes'});
		foreach($chc as $key) { $rs[$key] = 'y'; }
		$chc = explode('|', $wmt_ros{'ros_no'});
		foreach($chc as $key) { $rs[$key] = 'n'; }

		foreach($ros_options as $o) {
			$rs[$o['option_id'].'_nt'] = 
					GetROSKeyComment($fid, $fname, $o['option_id']);
		}
		
	} else {
  	if($fid) $rs = sqlQuery("SELECT * FROM form_ext_ros WHERE ee1_link_id=? ".
			"AND ee1_link_name=?", array($fid, $fname));
		if(!$rs{'id'}) {
			$flds = sqlListFields('form_ext_ros');
			$flds = array_slice($flds,7);
			foreach($flds as $key => $fld) { $rs[$fld]=''; }
		}
	}
}

function loadFormComments(&$dt, $module, $fid, $fname, $pid) {
	$flds = sqlListFields($fname);
	foreach($flds as $fld) {
		$comment = GetROSKeyComment($fid, $fname, $fld, $pid);
		if($comment) $dt[$fld . '_nt'] = $comment;
		if(!isset($dt[$fld . '_nt'])) $dt[$fld . '_nt'] = '';
	}	
}

?>
