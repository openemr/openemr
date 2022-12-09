<?php

function GetAllHipFollowUps($thisPid, $enc='', $frm='') {
	if($frm == '') {
		echo "<h>ERROR - Follow Up Array called with No Form Set</h>\n";
		exit;
	}
  $sql = "SELECT * FROM form_hip_follow WHERE pid=? AND link_form=? ".
		"ORDER BY id";
	$parms=array($thisPid, $frm);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, form_hip_follow.* ".
				"FROM form_wmt_ll LEFT JOIN form_hip_follow ON ".
				"form_wmt_ll.list_id=form_hip_follow.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type=? AND ".
				"form_hip_follow.link_form=? ORDER BY form_hip_follow.id";
		$parms=array($thisPid, $enc, 'wmt_hip_follow', $frm);
	}
	$all=array();
  $res = sqlStatement($sql, $parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function SetHFUFieldNames() {
	$fu= array('id' => 'id', 
		'date' => '',
		'pid' => '',
		'user' => '', 
		'link_form' => '',
		'list_id' => '',
		'hf_main_label' => '',
		'hf_rom_label' => '',
		'hf_def_label' => '',
		'hf_comp_label' => '',
		'hf_xray_label' => '',
		'hf_visit_num' => 'Weeks Out', 
		'hf_visit_dt' => 'Visit Date',
		'hf_weight' => 'Weight',
		'hf_thigh_pain' => 'Thigh Pain',
		'hf_groin_pain' => 'Groin Pain',
		'hf_lat_pain' => 'Lateral Pain',
		'hf_post_pain' => 'Posterior Pain',
		'hf_limp' => 'Limp',
		'hf_supp' => 'Support',
		'hf_dist' => 'Distance',
		'hf_stair' => 'Stairs',
		'hf_trans' => "Transportation<br><span class='wmtBody'>Able to Enter</span>",
		'hf_ss' => 'Shoes&nbsp;&amp;&nbsp;Socks',
		'hf_sit' => 'Stting',
		'hf_rom_flex' => 'Flexion',
		'hf_rom_abd' => 'Abduction',
		'hf_rom_ext' => 'Ext Rotation',
		'hf_rom_int' => 'Int Rotation',
		'hf_rom_add' => 'Adduction',
		'hf_def_def' => 'Deformity',
		'hf_def_flex' => 'Flexion Cont',
		'hf_def_add' => 'Adduction',
		'hf_def_int' => 'Int Rot In Ext',
		'hf_def_len' => 'Length Discrep',
		'hf_comp_comp' => 'Complications',
		'hf_comp_dis' => 'Dislocation',
		'hf_comp_inf' => 'Infection',
		'hf_comp_fem' => 'Loose Fem CMP',
		'hf_comp_ace' => 'Loose Acet CMP',
		'hf_comp_sub' => 'Subluxation',
		'hf_comp_oth' => 'Other',
		'hf_xray_un' => 'Unremarkable',
		'hf_xray_nt' => 'Notes',
	);
	return($fu);
}

function BuildHipFollowUpArray($vis=array(), $fnames=array()) {
	$fu_table=array();
	$flds=sqlListFields('form_hip_follow');
	$fu_rom_label=array('ROM');
	$fu_def_label=array('');
	$fu_comp_label=array('');
	$fu_xray_label=array('X-Ray');
	// This puts the label at the front of the column
	foreach($flds as $key => $fld) {
		// echo "Assigning Key: $key to Field: $fld</br>\n";
		// echo "Assigning Label: ".$fu_field_names[$fld]."</br>\n";
		// Force a label row into the table here
		if($fld == 'hf_rom_flex') { $fu_table['label_rom']= $fu_rom_label; }
		if($fld == 'hf_def_def') { $fu_table['label_def']= $fu_def_label; }
		if($fld == 'hf_comp_comp') { $fu_table['label_comp']= $fu_comp_label; }
		if($fld == 'hf_xray_un') { $fu_table['label_xray']= $fu_xray_label; }
		$fu_table[$fld][0]= $fnames[$fld];
	}
	// $fu_table['label_clear'][0] = array('');

	$col_cnt=1;
	$row_cnt=1;
	foreach($vis as $prev) {
		foreach($prev as $key => $val) {
			// echo "$key&nbsp;&nbsp;::&nbsp;&nbsp;$val</br>\n";
			$fu_table[$key][$col_cnt] = $val;
		}
		$col_cnt++;
	}
	// This will put the blank entry at the end of the table
	foreach($flds as $key => $fld) {
		$fu_table[$fld][$col_cnt]= '';
	}
	$col_cnt++;
	// Then space it out with empties so it looks better
	while($col_cnt <= 7) {
		foreach($flds as $key => $fld) {
			$fu_table[$fld][$col_cnt]= '';
		}
		$col_cnt++;
	}
	return($fu_table);
}

function OnlyHipWeight($fu_test= array())
{
	$ret= true;
	foreach($fu_test as $key => $data) {
		$data= trim($data);
		if(!empty($data) && $key != 'hf_weight') { $ret= false; }
		if(!$ret) { return $ret; }
	}
	return $ret;
}

function SaveHipFollowUps($thisPid,$enc,$fu_save= array(),$max=1,$link='')
{
	if(!VerifyPatientID($thisPid)) { return false; }
	if($link == '') {
		echo "<h>Can NOT Save Follow Ups without a form</h>\n";
		exit;
	}
	$fu_recs= array();
	// First move all our inputs into an array of arrays (per record)
	foreach($fu_save as $lbl => $val) {
		$pos= strrpos($lbl, '_');
		if(!$pos) { continue; }	
		$lbl_base= substr($lbl, 0, $pos);
		$cnt= substr($lbl, $pos+1);
		// echo "Label: $lbl   Pos: $pos   Count: $cnt   Base: $lbl_base   Value: $val<br/>\n";
		$fu_recs[$cnt][$lbl_base]= $val;
	}

	// First set the max used, and respect empty spaces	
	$highest_used= -1;
	$cnt= 0;
	foreach($fu_recs as $fu) {
		if(array_filter($fu)) { $highest_used= $cnt; }
		$cnt++ ;
	}
	// echo "Count: $cnt   Highest Used: $highest_used<br/>\n";

	// Now process each of the member (record) arrays
	$cnt= 0;
	foreach($fu_recs as $fu) {
		if($cnt > $highest_used) { break; }
		if(OnlyHipWeight($fu)) { continue; }
		if($fu['hf_id'] == '') {
			// First create the record, then update with the values we want to save
			// echo "Created a new Record<br/>\n";
			$sql = 'INSERT INTO form_hip_follow (date, pid, user, link_form) '.
					'VALUES (NOW(), ?, ?, ?)';
			$fu_id= sqlInsert($sql, array($thisPid, $_SESSION['authUser'], $link));
		} else {
			$fu_id= $fu['hf_id'];
		}
		// echo "Now completing the values<br/>\n";
		$q1= 'UPDATE form_hip_follow SET ';
		$parms= array();
		foreach($fu as $key => $val) {
			if($key == 'hf_id') { continue; }
			if($key == 'link_form') { $val = $link; }
			$q1 .= "$key=?, ";
			$parms[]= $val;
		}
		$parms[]= $fu_id;
		$parms[]= $thisPid;
		$q1 .= ' date=NOW() WHERE id=? AND pid=?';
		// echo "Statement: $q1<br/>\n";
		// echo print_r($parms),"<br/>\n";
		sqlInsert($q1, $parms);
		$cnt++;
		LinkListEntry($thisPid, $fu_id, $enc, 'wmt_hip_follow', false, true);
	}
	// echo "Exited Loop, Count: $cnt   Max: $highest_used<br/>\n";	
}

?>
