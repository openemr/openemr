<?php
if(!isset($dt['fyi_imm_nt'])) $dt['fyi_imm_nt'] = '';
if(isset($fyi->fyi_imm_nt)) $dt['fyi_imm_nt'] = $fyi->fyi_imm_nt;
if(!isset($imm)) $imm = array();
if((count($imm) > 0) || $dt['fyi_imm_nt'] != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Immunizations</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($imm) > 0) {
		echo " 	<tr>\n";
		echo " 		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>Date</td>\n";
		echo " 		<td class='wmtPrnLabelCenterBorderLB'>Immunization</td>\n";
		echo " 		<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
		echo " 	</tr>\n";
		foreach($imm as $prev) {
			echo "	<tr>\n";
			echo "		<td class='wmtPrnBodyBorderB'>",htmlspecialchars(substr($prev['administered_date'],0,10), ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
			echo "		<td class='wmtPrnBodyBorderLB'>",ImmLook($prev['cvx_code'],'immunizations'),"&nbsp;</td>\n";
			echo "		<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
			echo "	</tr>\n";
		}
	}
	if($dt['fyi_imm_nt'] != '') {
		echo "		<tr>\n";
		echo "			<td colspan='3' class='wmtPrnLabel'>Other Notes:</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='3' class='wmtPrnBody'>".htmlspecialchars($dt['fyi_imm_nt'],ENT_QUOTES,'',FALSE)."</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
