<?php

function GetAllKneeFollowUps($thisPid, $enc='', $link='') {
	if($link == '') {
		echo "<h>No Specific form Link Provided....Exiting</h>\n";
		exit;
	}
  $sql = "SELECT * FROM form_knee_follow WHERE pid=? AND link_form=? ".
				"ORDER BY id";
	$parms=array($thisPid, $link);
	if($enc != '') {
  	$sql = "SELECT form_wmt_ll.list_id, form_knee_follow.* ".
				"FROM form_wmt_ll LEFT JOIN form_knee_follow ON ".
				"form_wmt_ll.list_id=form_knee_follow.id WHERE form_wmt_ll.pid=? ".
				"AND form_wmt_ll.encounter_id=? AND form_wmt_ll.list_type=? ".
				"AND form_knee_follow.link_form=? ORDER BY form_knee_follow.id";
		$parms=array($thisPid, $enc, 'wmt_knee_follow', $link);
	}
	$all=array();
  $res = sqlStatement($sql, $parms);
  for($iter =0;$row = sqlFetchArray($res);$iter++)
  $all[$iter] = $row;
  return $all;
}

function SetKFUFieldNames() {
	$fu= array('id' => 'id', 
		'list_id' => '',
		'date' => '',
		'pid' => '',
		'user' => '', 
		'link_form' => '',
		'kf_lax_label' => 'Laxity',
		'kf_align_label' => 'Alignment',
		'kf_walk_pain_label' => 'Pain-Walking',
		'kf_visit_dt' => 'Visit Date',
		'kf_vis_num' => 'Weeks Out',
		'kf_weight' => 'Weight',
		'kf_rom' => 'Range of Motion',
		'kf_ext_lag' => 'Extension Lag',
		'kf_lax_var' => 'Varus (degrees)',
		'kf_lax_val' => 'Valgus (degrees)',
		'kf_lax_ant' => 'Anterior (mm)',
		'kf_lax_post' => 'Posterior (mm)',
		'kf_align_var' => 'Varus (degrees)',
		'kf_align_val' => 'Valgus (degrees)',
		'kf_walk_pain' => 'Pain-Walking',
		'kf_rest_pain' => 'Pain-Resting',
		'kf_stair_pain' => 'Pain-Stairs',
		'kf_func' => 'Function',
		'kf_supp' => 'Support',
		'kf_stair' => 'Stairs',
		'kf_comp_comp' => 'Complications',
		'kf_comp_def' => 'Deformity',
		'kf_comp_dis' => 'Dislocation',
		'kf_comp_inf' => 'Infection',
		'kf_comp_pain' => 'Pain',
		'kf_comp_fx' => 'Patellar Fx.',
		'kf_comp_tend' => 'Pat. Tend. Rpt.',
		'kf_comp_pat' => 'Pat. Wear',
		'kf_comp_tib' => 'Tibial Wear',
		'kf_comp_sub' => 'Subluxation',
		'kf_comp_oth' => 'Other',
		'kf_pat_wnl' => 'Patella',
		'kf_pat_app' => 'Apprehension',
		'kf_pat_cre' => 'Crepitance',
		'kf_pat_dis' => 'Dislocation',
		'kf_pat_high' => 'High Riding',
		'kf_pat_low' => 'Low Riding',
		'kf_pat_lat' => 'Lateral',
		'kf_pat_sub' => 'Subluxable',
		'kf_xray_un' => 'Unremarkable',
		'kf_xray_dis' => 'Dislocation',
		'kf_xray_fem' => 'Fx. Fem. Shaft',
		'kf_xray_tib' => 'Fx. Tib. Shaft',
		'kf_xray_loose_fem' => 'Loose Fem Comp',
		'kf_xray_loose_tib' => 'Loose Tib Comp',
		'kf_xray_loose_pat' => 'Loose Pat Comp',
		'kf_xray_mig_fem' => 'Migration Fem.',
		'kf_xray_mig_tib' => 'Migration Tib.',
		'kf_xray_oth' => 'Other',
		'kf_screw_med' => 'Medial',
		'kf_screw_ant_med' => 'Ant. Medial',
		'kf_screw_post_med' => 'Post. Medial',
		'kf_screw_lat' => 'Lateral',
		'kf_screw_ant_lat' => 'Ant. Lateral',
		'kf_screw_post_lat' => 'Post. Lateral',
		'kf_screw_central' => 'Central'
	);
	return($fu);
}

function BuildKneeFollowUpArray($vis=array(), $fnames=array()) {
	$flds=sqlListFields('form_knee_follow');
	$fu_table=array();

	$fu_main_label=array();
	$fu_lax_label=array('Laxity');
	$fu_align_label=array('Alignment');
	$fu_comp_label=array('');
	$fu_pat_label=array('');
	$fu_xray_label=array('X-Ray');
	$fu_screw_label=array('Screw Lucency');
	// This puts the label at the front of the column
	foreach($flds as $key => $fld) {
		// echo "Assigning Key: $key to Field: $fld</br>\n";
		// echo "Assigning Label: ".$fu_field_names[$fld]."</br>\n";
		// Force a label row into the table here
		if($fld == 'kf_lax_var') { $fu_table['label_lax']= $fu_lax_label; }
		if($fld == 'kf_align_var') { $fu_table['label_align']= $fu_align_label; }
		if($fld == 'kf_comp_comp') { $fu_table['label_comp']= $fu_comp_label; }
		if($fld == 'kf_pat_wnl') { $fu_table['label_pat']= $fu_pat_label; }
		if($fld == 'kf_xray_un') { $fu_table['label_xray']= $fu_xray_label; }
		if($fld == 'kf_screw_med') { $fu_table['label_screw']= $fu_screw_label; }
		$fu_table[$fld][0]= $fnames[$fld];
	}
	// $fu_table['label_clear'][0] = array();

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

function OnlyWeight($fu_test= array())
{
	$ret= true;
	foreach($fu_test as $key => $data) {
		$data= trim($data);
		if(!empty($data) && $key != 'kf_weight') { $ret= false; }
		if(!$ret) { return $ret; }
	}
	return $ret;
}

function SaveKneeFollowUps($thisPid,$enc,$fu_save=array(),$max=1,$link='')
{
	if($link == '') {
		echo "<h>No Specific form Link Provided....Exiting</h>\n";
		exit;
	}
	if(!VerifyPatientID($thisPid)) { return false; }
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
		if(OnlyWeight($fu)) { continue; }
		if($fu['kf_id'] == '') {
			// First create the record, then update with the values we want to save
			// echo "Created a new Record<br/>\n";
			$sql = 'INSERT INTO form_knee_follow (date, pid, user, link_form) '.
					'VALUES (NOW(), ?, ?, ?)';
			$fu_id= sqlInsert($sql, array($thisPid, $_SESSION['authUser'], $link));
		} else {
			$fu_id= $fu['kf_id'];
		}
		// echo "Now completing the values<br/>\n";
		$q1= 'UPDATE form_knee_follow SET ';
		$parms= array();
		foreach($fu as $key => $val) {
			if($key == 'kf_id') { continue; }
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
		LinkListEntry($thisPid, $fu_id, $enc, 'wmt_knee_follow', false, true);
	}
	// echo "Exited Loop, Count: $cnt   Max: $highest_used<br/>\n";	
}

?>
