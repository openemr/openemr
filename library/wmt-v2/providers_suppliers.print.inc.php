<?php 
$_print= false;
if(!isset($surg_note)) { $surg_note= ''; }
if((isset($surg) && count($surg) > 0) || $surg_note != '') { $_print= true; }
if($_print) {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Surgeries</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($pmh) > 0) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>Date</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Type of Surgery</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Performed By</td>\n";
		echo "	</tr>\n";
	}
	foreach($surg as $prev) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnBodyBorderB'>",$prev['begdate'],"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",$prev['title'],"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",$prev['comments'],"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",$prev['referredby'],"&nbsp;</td>\n";
		echo "	</tr>\n";
	}
	if($surg_note != '') {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='4' class='wmtPrnBody'>$surg_note</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
