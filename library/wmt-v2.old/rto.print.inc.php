<?php
$title = 'Orders / RTO';
if(!isset($rto_data)) $rto_data = array();
if(isset($rto_title)) $title = $rto_title;
if(!isset($dt['fyi_action_nt'])) $dt['fyi_action_nt'] = '';
if(isset($fyi->fyi_action_nt)) $dt['fyi_action_nt'] = $fyi->fyi_action_nt;
if((count($rto_data) > 0) || $dt['fyi_action_nt']) {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>$title</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse;'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabelCenterBorderB' style='width: 140px'>Target Date</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Action</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
	echo "		</tr>\n";
	$cnt=1;
	foreach($rto_data as $rto) {
		echo "<tr>\n";
		echo "<td class='wmtPrnBodyBorderLB'>\n",$rto['rto_target_date'],"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>\n",ListLook($rto['rto_action'], 'RTO_Action'),"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>\n",$rto['rto_notes'],"&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
	if(isset($dt['fyi_action_nt'])) {
		if($dt['fyi_action_nt']) {
			echo "<tr>\n";
			echo "<td class='wmtPrnLabel' colspan='3'>Action Note:</td>\n";
			echo "</tr>\n";
			echo "<tr>\n";
			echo "<td class='wmtPrnBody' colspan='3'>".$dt['fyi_action_nt']."</td>\n";
			echo "</tr>\n";
		}
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
