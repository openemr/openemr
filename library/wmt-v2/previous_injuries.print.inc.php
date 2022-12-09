<?php 
if(!isset($inj)) $inj= array();
if(!isset($fyi->fyi_inj_nt)) $fyi->fyi_inj_nt = '';
if((count($inj) > 0) || $fyi->fyi_inj_nt != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Injuries</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($inj) > 0) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>Date</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Type of Injury</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Hospitalized?</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
		echo "	</tr>\n";
	}
	foreach($inj as $prev) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnBodyBorderB'>",htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",ListLook($prev['extrainfo'],'YesNo'),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "	</tr>\n";
	}
	if($fyi->fyi_inj_nt != '') {
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='4' class='wmtPrnBody'>".htmlspecialchars($fyi->fyi_inj_nt, ENT_QUOTES, '', FALSE)."</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
