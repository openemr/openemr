<?php
if(!isset($fh)) $fh = array();
if(!isset($dt['fyi_fh_nt'])) $dt['fyi_fh_nt'] = '';
if(isset($fyi->fyi_fh_nt)) $dt['fyi_fh_nt'] = $fyi->fyi_fh_nt;
if(isset($dashboard)) {
	if(!isset($dt['db_fh_non_contrrib'])) $dt['db_fh_non_contrib'] = $dashboard->db_fh_non_contrib;
	if(!isset($dt['db_fh_adopted'])) $dt['db_fh_adopted'] = $dashboard->db_fh_adopted;
}
$fh_ros_position = strtolower(checkSettingMode('wmt::fh_ros_display','',$frmdir));
if($fh_ros_position == 'top') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.print.inc.php');
}
if((count($fh) > 0))  {
	$border='';
	if($chp_printed) {
		echo "	</table>\n";
		echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
		$border='wmtPrnBorderT';
	} else {
		$chp_printed=PrintChapter('Family History',$chp_printed); 
	}
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabelCenterBorderB $border' style='width: 45px;'>Who</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB $border' style='width: 65px;'>Deceased</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB $border' style='width: 65px;'>Current Age</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB $border' style='width: 65px;'>Age at Death</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB $border'>Condition</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB $border'>Notes</td>\n";
	echo "		</tr>\n";
	foreach($fh as $prev) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnBodyBorderB'>",ListLook($prev['fh_who'],'Family_Relationships'),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",ListLook($prev['fh_deceased'],'YesNo'),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['fh_age'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['fh_age_dead'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",ListLook($prev['fh_type'],'Family_History_Problems'),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['fh_nt'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "		</tr>\n";
	}
}
if($dt['db_fh_non_contrib'] || $dt['db_fh_adopted']) {
	$chp_printed=PrintChapter('Family History',$chp_printed); 
	if($dt['db_fh_adopted']) PrintSingleLine('Patient Is Adopted','',5);
	if($dt['db_fh_non_contrib']) PrintSingleLine('Family History is Non-Contributory','',5);
}
if($fh_ros_position == 'middle') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.print.inc.php');
}
if($fyi->fyi_fh_nt) {
	$chp_printed=PrintChapter('Family History',$chp_printed); 
	PrintOverhead('Other Notes:',$fyi->fyi_fh_nt,5);
}
if($fh_ros_position == 'bottom') {
	include($GLOBALS['srcdir'].'/wmt-v2/family_history_ros.print.inc.php');
}
?>
