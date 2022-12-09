<?php 
if(!isset($surg)) $surg= array();
if(!isset($dt['fyi_surg_nt'])) $dt['fyi_surg_nt'] = '';
if(isset($fyi->fyi_surg_nt)) $surg_note= $fyi->fyi_surg_nt;
if((count($surg) > 0) || $dt['fyi_surg_nt'] != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Surgeries</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($surg) > 0) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>Date</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Type of Surgery</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Hospitalized?</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>Performed By</td>\n";
		echo "	</tr>\n";
	}
	foreach($surg as $prev) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnBodyBorderB'>",htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",ListLook($prev['extrainfo'],'YesNo'),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['referredby'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "	</tr>\n";
	}
	if($dt['fyi_surg_nt'] != '') {
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='4' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_surg_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
