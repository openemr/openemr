<?php
if(!isset($ultra)) $ultra = array();
if(!isset($chp_title)) $chp_title = 'Ultrasounds';
$chp_printed = PrintChapter($chp_title, $chp_printed);
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
echo "		<tr>\n";
echo "			<td class='wmtPrnBorder1B wmtPrnC wmtPrnLabel' style='width: 90px'>Date</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel'>Type</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel'>Notes</td>\n";
echo "			<td class='wmtPrnBorder1L wmtPrnBorder1B wmtPrnC wmtPrnLabel' style='width: 90px'>Reviewed</td>\n";
echo "		</tr>\n";
$cnt=1;
if(count($ultra) > 0) {
	foreach($ultra as $prev) {
		echo "<tr>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1B'>",$prev['begdate'],"&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'>",$prev['title'],"&nbsp;</td>\n";
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
?>
