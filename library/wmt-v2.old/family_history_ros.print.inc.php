<?php
include_once($GLOBALS['srcdir'].'/wmt-v2/ros_functions.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');
$use_fh_ros_note = checkSettingMode('wmt::family_history_ros_note','',$frmdir);
$fh_yes = array();
$fh_no = array();
if(!isset($dashboard)) {
	if(!class_exists('wmtDashboard')) include_once($GLOBALS['srcdir'].'/wmt-v2/dashboard.class.php');
	$dashboard = wmtDashboard::getPidDashboard($pid);
}
if(!isset($fh_options)) {
	$fh_options = LoadList('Family_History_Choices','all','seq','',
		'AND (notes LIKE "%::'.$frmdir.'::%" || notes LIKE "%::all::%")');
}
$num_fh_options = count($fh_options);
if(!isset($fh_ros_position)) $fh_ros_position = 'bottom';
foreach($fh_options as $o) {
	$dt['tmp_fh_rs_'.$o['option_id'].'_nt'] = 
		GetROSKeyComment($dashboard->id,'dashboard','fh_rs_'.$o['option_id'],$pid);	
}

if($dashboard->db_fh_extra_yes) {
	$fh_yes = explode('|', $dashboard->db_fh_extra_yes);
	foreach($fh_options as $opt) {
		if(in_array($opt['option_id'], $fh_yes)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'y';
	}
}
if($dashboard->db_fh_extra_no) {
	$fh_no = explode('|', $dashboard->db_fh_extra_no);
	foreach($fh_options as $opt) {
		if(in_array($opt['option_id'], $fh_no)) $dt['tmp_fh_rs_'.$opt['option_id']] = 'n';
	}
}
$cols = 1;
if($chp_printed) $cols = 6;
$hdr_printed = false;
if(count($fh_yes) || count($fh_no)) {
	$nt = '';
	foreach($fh_yes as $chc) {
		$title = GetListTitleByKey($chc, 'Family_History_Choices', 'all', 'AND notes LIKE "%::'.$frmdir.'::"');
		if($title) {
			$comm = '';
			if($use_fh_ros_note) {
				$comm = $dt['tmp_fh_rs_'.$chc.'_nt'];
				if($comm) {
					$chp_printed = PrintChapter('Family History',$chp_printed);
					if(!$hdr_printed) PrintSingleLine('Has anyone in your family ever been diagnosed with:','',$cols);
					$hdr_printed = TRUE;
					PrintSingleLine('YES:',$title.'- '.$comm,$cols);
					continue;
				}
			}
			if($nt != '') $nt .= ', ';
			$nt .= $title;
		}
	}
	if($nt != '') {
		$chp_printed = PrintChapter('Family History',$chp_printed);
		if(!$hdr_printed) PrintSingleLine('Has anyone in your family ever been diagnosed with:','',$cols);
		$hdr_printed = TRUE;
		PrintSingleLine('YES:',$nt,$cols);
	}
	$nt = '';
	foreach($fh_no as $chc) {
		$title = GetListTitleByKey($chc, 'Family_History_Choices', 'all', 'AND notes LIKE "%::'.$frmdir.'::"');
		if($title) {
			$comm = '';
			if($use_fh_ros_note) {
				$comm = $dt['tmp_fh_rs_'.$chc.'_nt'];
				if($comm) {
					$chp_printed = PrintChapter('Family History',$chp_printed);
					if(!$hdr_printed) PrintSingleLine('Has anyone in your family ever been diagnosed with:','',$cols);
					$hdr_printed = TRUE;
					PrintSingleLine('NO:',$title.'- '.$comm,$cols);
					continue;
				}
			}
			if($nt != '') $nt .= ', ';
			$nt .= $title;
		}
	}
	if($nt != '') {
		$chp_printed = PrintChapter('Family History',$chp_printed);
		if(!$hdr_printed) PrintSingleLine('Has anyone in your family ever been diagnosed with:','',$cols);
		$hdr_printed = TRUE;
		PrintSingleLine('NO:',$nt,$cols);
	}
}

if($use_fh_ros_note) {
	foreach($fh_options as $o) {
		if($dt['tmp_fh_rs_'.$o['option_id'].'_nt'] != '') {
			if(in_array($o['option_id'], $fh_yes)) continue;
			if(in_array($o['option_id'], $fh_no)) continue;
			$chp_printed = PrintChapter('Family History',$chp_printed);
			if(!$hdr_printed) PrintSingleLine('Has anyone in your family ever been diagnosed with:','',$cols);
			$hdr_printed = TRUE;
			PrintSingleLine('',$o['title'].'- '.$dt['tmp_fh_rs_'.$o['option_id'].'_nt'],$cols);
		}
	}
}
?>
