<?php
if(!isset($pmh)) $pmh = array();
if(!isset($dt['fyi_pmh_nt'])) $dt['fyi_pmh_nt'] = '';
if(isset($fyi->fyi_pmh_nt)) $dt['fyi_pmh_nt'] = $fyi->fyi_pmh_nt;
if((count($pmh) > 0) || $dt['fyi_pmh_nt'] != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "	<div class='wmtPrnCollapseBar'>\n";
	echo "		<span class='wmtPrnChapter'>Medical History</span>\n"; 
	echo "	</div>\n";
	echo "	<div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($pmh) > 0) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabelCenterBorderB'>Issue</td>\n";
		echo "			<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
		echo "		</tr>\n";
	}
	foreach($pmh as $prev) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnBodyBorderB'>",ListLook($prev['pmh_type'],'Medical_History_Problems'),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['pmh_nt'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		</tr>\n";
	}
	if($dt['fyi_pmh_nt']!= '') {
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_pmh_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "		</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
