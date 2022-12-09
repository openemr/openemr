<?php
// Count to see if any are actually attached to this encounter 
// rather than just checking the array
$_print = false;
if(!isset($diag)) $diag = array();
if(!isset($include_plans)) $include_plans = false;
if(!isset($pane_title)) $pane_title = 'Problems / Diagnoses';
foreach($diag as $prev) {
		if($prev['encounter'] == $encounter) $_print = true;
		// echo "My Encounter: $encounter  And File: ".$prev['encounter']."<br>\n";
}
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='margin-top: 4px; '>
		<tr>
			<td class='wmtPrnLabel' style='width: 100px;'>&nbsp;&nbsp;Diagnosis</td>
			<td class='wmtPrnLabel' style='width: 80px;'>&nbsp;&nbsp;Start Date</td>
			<td class='wmtPrnLabel'>&nbsp;&nbsp;Title</td>
		</tr>
<?php 
if($_print) {
	$cnt=1;
	foreach($diag as $prev) {
		// Skip any problems that loaded but are not related to this encounter
		if($prev['encounter'] != $encounter) continue;
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder=trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis']=trim(substr($prev['diagnosis'],0,$pos));
		}
		$desc = GetDiagDescription($prev['diagnosis']);
		if($pos = strpos($prev['diagnosis'],':')) {
			$prev['diagnosis']=trim(substr($prev['diagnosis'],($pos+1)));
		}
   	echo "		<tr>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;",htmlspecialchars($prev['diagnosis'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T wmtPrnBorder1L";
		echo "'>&nbsp;",htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T wmtPrnBorder1L";
		echo "'>&nbsp;",htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE),"</td>\n";
		if($include_plans) {
			if($prev['comments']) {
				PrintSingleLine('Plan:',htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE),3,'border-top: solid 1px black');
			}
		}	
		echo "		</tr>\n";
		$cnt++;
	}
} else {
	echo "		<tr>\n";
	echo "			<td>&nbsp;</td>\n";
	echo "			<td class='wmtPrnBody'>No Detail on File</td>\n";
	echo "		</tr>\n";
}
$pane_printed = true;
?>
	</table>
</fieldset>
