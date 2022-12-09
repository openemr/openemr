<?php
// Count to see if any are actually attached to this encounter 
// rather than just checking the array
$_tst = false;
if(!isset($include_plans)) $include_plans = false;
if(!isset($diag)) $diag = array();
foreach($diag as $prev) {
		if($prev['encounter'] == $encounter) $_tst = true;
}
if($_tst) {
	echo "<span class='wmtPrnHeader'>Diagnoses / Plans</span>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0' style='margin-top: 4px;'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel' style='width: 100px;'>Diagnosis</td>\n";
	echo "			<td class='wmtPrnLabel' style='width: 80px;'>Start Date</td>\n";
	echo "			<td class='wmtPrnLabel'>Title</td>\n";
	echo "		</tr>\n";
	$cnt=1;
	foreach($diag as $prev) {
		// Skip any problems that loaded but are not related to this encounter
		if($prev['encounter'] != $encounter) continue;
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
   	echo "		<tr>\n";
		echo "			<td class='wmtPrnBody '>",htmlspecialchars($prev['diagnosis'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBody ";
		// echo (($include_plans)?'':'wmtPrnBorder1L');
		echo "'>",htmlspecialchars($prev['begdate'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBody ";
		// echo (($include_plans)?'':'wmtPrnBorder1L');
		echo "'>",htmlspecialchars($prev['title'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		if($include_plans) {
			if($prev['comments']) {
				PrintSingleLine('Plan:',htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE),3);
				// PrintOverhead('Plan:',$prev['comments'],'3');
			}
		}	
		echo "		</tr>\n";
		$cnt++;
	}
	echo "	</table>\n";
	echo "<br>\n";
}
?>
