<?php
// Count to see if any are actually attached to this encounter 
// rather than just checking the array
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($use_plans)) $use_plans = true;
if(!isset($diag)) $diag = array();
if(!isset($pane_title)) $pane_title = 'Problems / Diagnoses';
$_print = false;
$_max = 0;
foreach($diag as $prev) {
	if($prev['encounter'] == $encounter) { 
		$_print = true;
		$_max++;
	}
}
?>
<fieldset style='border: solid 1px black; margin: 0px; padding: 0px;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3'>
		<tr>
			<td class='wmtPrnLabel' style='width: 100px;'>&nbsp;&nbsp;Diagnosis</td>
			<td class='wmtPrnLabel' style='width: 80px;'>Start Date</td>
			<td class='wmtPrnLabel'>Title</td>
		</tr>
<?php
if($_print) {
	$cnt=1;
	foreach($diag as $prev) {
		// Skip any problems that loaded but are not related to this encounter
		if($prev['encounter'] != $encounter) continue;
		if(!$prev['billing_id'] && $frmdir == 'definable_fee') continue;
		if($pos = strpos($prev['diagnosis'],';')) {
			$remainder=trim(substr($prev['diagnosis'],($pos+1)));
			$prev['diagnosis']=trim(substr($prev['diagnosis'],0,$pos));
		}
		$desc = GetDiagDescription($prev['diagnosis']);
		if($pos = strpos($prev['diagnosis'],':')) {
			$prev['diagnosis']=trim(substr($prev['diagnosis'],($pos+1)));
		}
   	echo "		<tr>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;&nbsp;",htmlspecialchars($prev['diagnosis'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T'>",htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBody wmtPrnBorder1T'>",htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "		</tr>\n";
		if($use_plans && $prev['comments'] != '') { 
			echo "		<tr>\n";
			echo "			<td class='wmtPrnLabel' ";
			echo ">&nbsp;&nbsp;Plan:</td>\n";
			echo "			<td colspan='2' class='wmtPrnBody' ";
			echo ">",htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
			echo "		</tr>\n";
		}
		$cnt++;
	}
} else {
  echo "		<tr>\n";
	echo "			<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "			<td class='wmtPrnBody wmtPrnBorder1T' colspan='3'>No Detail on File</td>\n";
  echo "		</tr>\n";
}
$pane_printed = true;
?>
	</table>
</fieldset>
