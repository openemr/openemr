<?php
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
echo "		<tr>\n";
echo "			<td class='wmtPrnBorder1B wmtPrnC wmtPrnLabel' style='width: 90px'>Date</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel'>Result</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel'>Notes</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel' style='width: 90px'>Reviewed</td>\n";
echo "		</tr>\n";
$cnt=1;
if(isset($bone) && (count($bone) > 0)) {
	foreach($bone as $prev) {
		echo "<tr>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1B'>",$prev['begdate'],"&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>",$prev['extrainfo'],"&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>",$prev['comments']."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>",$prev['referredby'],"&nbsp;</td>\n";
		echo "</tr>\n";
	}
} else {
		echo "<tr>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1B'>None on File</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;</td>\n";
		echo "</tr>\n";
}
echo "		</table>\n";
?>
