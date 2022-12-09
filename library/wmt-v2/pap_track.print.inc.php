<?php
if(isset($pap_data) && count($pap_data) > 0) {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Pap Tracking</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "<table width='100%'	border='0' cellspacing='0' cellpadding='0'>\n";
	echo "	<tr>\n";
	echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 80px'>Date</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB' colspan='2'>Lab/Test/Result</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB'>Comments</td>\n";
	echo "	</tr>\n";
	foreach($pap_data as $pap) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnBody'>",$pap['pt_date'],"&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderL'>".ListLook($pap['pt_lab'], 'PT_Labs')."&nbsp;</td>\n";
		echo "		<td class='wmtPrnBody'>".ListLook($pap['pt_test'], 'PT_Tests')."&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderL'>",ListLook($pap['pt_hpv_result'],'PT_HPV_Results'),"</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td class='wmtPrnBodyBorderB'>&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB' colspan='2'>".$pap['pt_result_text']."&nbsp;</td>\n";
		echo "		<td class='wmtPrnBodyBorderLB' rowspan='2'>",$pap['pt_result_nt'],"&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
