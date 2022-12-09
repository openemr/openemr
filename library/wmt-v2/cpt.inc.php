<?php
if(isset($rto_data) && count($rto_data) > 0) {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Orders / RTO</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabelCenterBorderB' style='width: 140px'>Time Frame</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB' style='width: 50%'>Notes</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Status</td>\n";
	echo "		</tr>\n";
	$cnt=1;
	foreach($rto_data as $rto) {
		echo "<tr>\n";
		echo "<td class='wmtPrnBodyBorderB'>",ListLook($rto['rto_num'], 'RTO_Number'),"&nbsp;&nbsp;",ListLook($rto['rto_frame'],'RTO_Frame'),"&nbsp;from&nbsp;",$rto['rto_date'],"</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>\n",$rto['rto_notes'],"&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>\n",ListLook($rto['rto_status'], 'RTO_Status'),"&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
