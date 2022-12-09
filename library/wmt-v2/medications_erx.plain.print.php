<?php
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(isset($fyi->fyi_med_nt)) $dt['fyi_med_nt'] = $fyi->fyi_med_nt;
$dt['fyi_med_nt'] = trim($dt['fyi_med_nt']);
if(!isset($meds)) $meds = array();
echo "<span class='wmtPrnHeader'>",xl('Current Medications','e'),"</span>\n";
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse;'>\n";
$cnt=1;
if(count($meds) > 0) {
	echo "	<tr>\n";
	echo "		<td class='wmtPrnLabel' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel'>",xl('Medication','e'),"</td>\n";
	// echo "		<td class='wmtPrnLabel wmtPrnC'>",xl('Quantity','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel'>",xl('Dosage','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel'>",xl('Sig','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel'>",xl('Comments','e'),"</td>\n";
	echo "	</tr>\n";
	foreach($meds as $prev) {
		$sig1 = trim(ListLook($prev['route'],'drug_route'));
		if(substr($sig1,0,3) == "Add") $sig1 = '';
		if($sig1) $sig1 = ' '.$sig1;
		$form = trim(ListLook($prev['form'],'drug_form'));
		if(substr($form,0,3) == "Add") $form = '';
		if($form) $form = ' '.$form;
		$sig2 = trim(ListLook($prev['interval'],'drug_interval'));
		if(substr($sig2,0,3) == "Add") $sig2 = '';
		if($sig2) $sig2 = ' '.$sig2;
		if(substr($prev['dosage'],0,3) == "Add") $prev['dosage'] = '';
		$sig1 = $prev['dosage'] . $form . $sig1 . $sig2;
		$size = trim($prev['size']);
		$unit = trim(ListLook($prev['unit'],'drug_units'));
		$size .= $unit;
		echo "<tr>\n";
		echo "<td class='wmtPrnBody'>".htmlspecialchars($prev['date_added'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody'>".htmlspecialchars($prev['drug'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		// echo "<td class='wmtPrnBody'>".$prev['quantity']."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody'>".htmlspecialchars($size, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody'>".htmlspecialchars($sig1, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody'>".htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
} else if(empty($dt['fyi_med_nt'])) {
	echo "<tr>\n";
	echo "<td class='wmtPrnLabel' colspan='2'>",xl('None on File','e'),"</td>\n";
	// echo "<td class='wmtPrnLabel'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel'>&nbsp;</td>\n";
	echo "</tr>\n";
}
$tmp_col=5;
if(!empty($dt['fyi_med_nt'])) {
	if(count($meds) > 0) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel' colspan='$tmp_col'>",xl('Other Notes','e'),":</td>\n";
		echo "		</tr>\n";
	}
	echo "		<tr>\n";
	echo "			<td class='wmtPrnBody' colspan='$tmp_col'>",htmlspecialchars($dt['fyi_med_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
	echo "		</tr>\n";
}
echo "</table>\n";
echo "<br>\n";
?>
