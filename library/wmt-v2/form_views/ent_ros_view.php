<?php
// For the ROS section, we will do 5 sections on each sub-section to tighten 
// up the print view, first time through is for blank choices with a comment, 
// second time through is to concatonate all the 'no' answers
// and print on one line, next pass is to detail 'no' answers with comments,
// 4th pass is all 'yes' choices, last pass is yes with comment lines.
if(!isset($rs{'ros_yes'})) { $rs{'ros_yes'} = '|'; }	
if(!isset($rs{'ros_no'})) { $rs{'ros_no'} = '|'; }	
$yes = explode('|', $rs{'ros_yes'});
$no = explode('|', $rs{'ros_no'});
$comments = LoadROSFormComments($id, $frmn);

$ros_order_array = array(
	array('con', 'constitutional', 'Constitutional'),
	array('eye', 'eyes', 'Eyes'),
	array('ear', 'ears', 'Ears'),
	array('hear', 'hear', 'Hearing'),
	array('nose', 'nose', 'Nose'),
	array('sinus', 'sinus', 'Sinuses'),
	array('throat', 'throat', 'Throat'),
	array('neck', 'neck', 'Neck'),
	array('sleep', 'sleep', 'Sleep'),
	array('ent', 'ent', 'Ear/Nose/Throat/Mouth'),
	array('rsp', 'respiratory', 'Respiratory'),
	array('geni', 'genito', 'Genitourinary'),
	array('neu', 'neurologic', 'Neurologic'),
	array('end', 'endocrine', 'Endocrine'),
	array('lym', 'lymphatic', 'Lymphatic'),
	array('imm', 'allergic', 'Immunologic'),
	array('breast', 'breast', 'Breasts'),
	array('car', 'cardio', 'Cardiovascular'),
	array('gas', 'gastro', 'Gastrointestinal'),
	array('msc', 'muscle', 'Musculoskeletal/Extremities'),
	array('skin', 'skin', 'Skin/Hair/Nails'),
	array('psy', 'psychiatric', 'Psychiatric')
);

foreach($ros_order_array as $ros_type) {
	$hdr_printed=false;
	
	if($rs{'ros_'.$ros_type[1].'_hpi'} == '1') {
		EE1_PrintROS_RefertoHPI($ros_type[2]);
	}
	if($rs{'ros_'.$ros_type[1].'_none'} == '1') {
		EE1_PrintROS_NoProblem($ros_type[2]);
	}
	// FIRST DETAIL ANY COMMENTS WITH NO 'YES/NO' CHOICE
	foreach($comments as $key => $note) {
		// print "Key: $key  - Note: $note  Type: ".$ros_type[0]."<br>\n";
		if(substr($key,0,7) != substr('rs_'.$ros_type[0].'_',0,7)) { continue; }
		// $key = substr($key,0,-3);
		if(in_array($key, $yes)) { continue; }
		if(in_array($key, $no)) { continue; }
		$title = GetListTitleByKey($key,'Ext_ROS_Keys');
		EE1_PrintROS('', $note, $title, $ros_type[2]);
	}

	// NOW LIST ALL THE 'NO' CHOICES WITH NO COMMENT
	$prnt = '';
	foreach($no as $choice) {
		if(substr($choice,0,7) != substr('rs_'.$ros_type[0].'_',0,7)) { continue; }
		if(isset($comments[$choice])) { continue; }
		$title = GetListTitleByKey($choice,'Ext_ROS_Keys');
		$prnt = EE1_AppendItem($prnt, $title, 'Patient Denies: ');
	}
	if($prnt) { EE1_PrintCompoundROS($prnt,$ros_type[2]); }

	// NOW LIST ALL THE 'YES' CHOICES WITH NO COMMENT
	$prnt = '';
	foreach($yes as $choice) {
		if(substr($choice,0,7) != substr('rs_'.$ros_type[0].'_',0,7)) { continue; }
		if(isset($comments[$choice])) { continue; }
		$title = GetTitleByKey($choice,'Ext_ROS_Keys');
		$prnt = EE1_AppendItem($prnt, $title, 'Patient Indicates: ');
	}
	if($prnt) { EE1_PrintCompoundROS($prnt,$ros_type[2]); }

	// NOW LIST ALL THE 'NO' CHOICES WITH A COMMENT
	$prnt = '';
	foreach($no as $choice) {
		if(substr($choice,0,7) != substr('rs_'.$ros_type[0].'_',0,7)) { continue; }
		if(!isset($comments[$choice])) { continue; }
		$note = trim($comments[$choice]);
		$title = GetListTitleByKey($choice,'Ext_ROS_Keys');
		EE1_PrintROS('n',$note,$title,$ros_type[2],'n');
	}

	// NOW LIST ALL THE 'YES' CHOICES WITH A COMMENT
	$prnt = '';
	foreach($yes as $choice) {
		if(substr($choice,0,7) != substr('rs_'.$ros_type[0].'_',0,7)) { continue; }
		if(!isset($comments[$choice])) { continue; }
		$note = trim($comments[$choice]);
		$title = GetListTitleByKey($choice,'Ext_ROS_Keys');
		EE1_PrintROS('y',$note,$title,$ros_type[2],'y');
	}
}

$hdr_printed=false;
if($rs{'ros_nt'}) {
	EE1_PrintNote($rs{'ros_nt'},$chp_title,'General Notes:');
}
?>
