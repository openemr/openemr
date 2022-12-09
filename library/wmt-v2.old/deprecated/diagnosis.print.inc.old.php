<?php
// Count to see if any are actually attached to this encounter 
// rather than just checking the array
$chp_printed= false;
$_tst = false;
foreach($diag as $prev) {
		if($prev['encounter'] == $encounter) { $_tst = true; }
}
// Also Allow the print div and table to remain open
$close= true;
if(isset($leave_diag_div_open)) {
	if($leave_diag_div_open) { $close= false; }
}
if(isset($diag) && $_tst) {
	$chp_printed= true;
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Diagnosis / Plan</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "		<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "			<tr>\n";
	echo "				<td class='wmtPrnLabelBorderB' style='width: 40px;'>#</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB' style='width: 100px;'>Diagnosis</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB' style='width: 80px;'>Start Date</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB' style='width: 80px;'>End Date</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB'>Title</td>\n";
	echo "				<td class='wmtPrnLabelBorderLB'>Description</td>\n";
	echo "			</tr>\n";
	$cnt=1;
	foreach($diag as $prev) {
		// Skip any problems that loaded but are not related to this encounter
		if($prev['encounter'] != $encounter) { continue; }
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder=trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis']=trim(substr($prev['diagnosis'],0,$pos));
		}
		if($pos = strpos($prev['diagnosis'],':')) {
			// Also keep the type in case we update
			$code_type=trim(substr($prev['diagnosis'],0,$pos));
			$prev['diagnosis']=trim(substr($prev['diagnosis'],($pos+1)));
		}
		$desc = GetDiagDescription($code_type.':'.$prev['diagnosis']);
   	echo "			<tr>\n";
		echo "				<td class='wmtPrnLabel'>",$cnt,"&nbsp).</td>\n";
		echo "				<td class='wmtPrnBody'>",$prev['diagnosis'],"&nbsp;</td>\n";
		echo "				<td class='wmtPrnBody'>",$prev['begdate'],"&nbsp;</td>\n";
		echo "				<td class='wmtPrnBody'>",$prev['enddate'],"&nbsp;</td>\n";
		echo "				<td class='wmtPrnBody'>",$prev['title'],"&nbsp;</td>\n";
		echo "				<td class='wmtPrnBody'>",$desc,"&nbsp;</td>\n";
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td class='wmtPrnLabel' style='border-bottom: solid 1px black'>Plan:</td>\n";
		echo "				<td colspan='5' class='wmtPrnBody' style='border-bottom: solid 1px black'>",$prev['comments'],"&nbsp;</td>\n";
		$cnt++;
	}
	if($close) {
		echo "		</table>\n";
		echo "	</div>\n";
		echo "</div>\n";
	}
}
?>
