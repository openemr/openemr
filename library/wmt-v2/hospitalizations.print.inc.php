<?php
if(!isset($hosp)) $hosp = array();
if(!isset($dt['fyi_admissions_nt'])) $dt['fyi_admissions_nt'] = '';
if(isset($fyi->fyi_admissions_nt)) 
		$dt['fyi_admissions_nt'] = $fyi->fyi_admissions_nt;
if((count($hosp) > 0) || $dt['fyi_admissions_nt'] != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "	<div class='wmtPrnCollapseBar'>\n";
	echo "		<span class='wmtPrnChapter'>Admissions</span>\n";
	echo "	</div>\n";
	echo "	<div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>Date</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Facility</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Reason</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Comments</td>\n";
	echo "		</tr>\n";
	foreach($hosp as $prev) {
		echo "<tr>\n";
		echo "<td class='wmtPrnBodyBorderB'>",htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['extrainfo'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "</tr>\n";
	}
	if($dt['fyi_admissions_nt'] != '') {
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='4' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_admissions_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>

