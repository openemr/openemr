<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title) || $pane_title == '') $pane_title= xl('Current Medications','r');
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(!isset($fyi->fyi_med_nt)) $fyi->fyi_med_nt = '';
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse;'>
		<tr>
			<td class='wmtPrnLabel wmtPrnC' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Medication','e'); ?></td>
			<!-- td class='wmtPrnLabel wmtPrnC'><?php // xl('Quantity','e'); ?></td -->
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Dosage','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Sig','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Comments','e'); ?></td>
		</tr>
<?php 
$cnt=1;
if(isset($meds) && (count($meds) > 0)) {
	foreach($meds as $prev) {
		$sig1 = trim(ListLook($prev['route'],'drug_route'));
		if(substr($sig1,0,3) == "Add") $sig1 = '';
		if($sig1) $sig1 = ' ' . $sig1;
		$form = trim(ListLook($prev['form'],'drug_form'));
		if(substr($form,0,3) == "Add") $form = '';
		if($form) $form = ' ' . $form;
		$sig2 = trim(ListLook($prev['interval'],'drug_interval'));
		if(substr($sig2,0,3) == "Add") $sig2 = '';
		if($sig2) $sig2 = ' ' . $sig2;
		if(substr($prev['dosage'],0,3) == "Add") $prev['dosage'] = '';
		$sig1 = $prev['dosage'] . $form . $sig1 . $sig2;
		$size = trim($prev['size']);
		$unit = trim(ListLook($prev['unit'],'drug_units'));
		$size .= $unit;
		echo "<tr>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['date_added'], ENT_QUOTES, '', FALSE)."</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['drug'], ENT_QUOTES, '', FALSE)."</td>\n";
		// echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".$prev['quantity']."&nbsp;</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($size, ENT_QUOTES, '', FALSE)."</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($sig1, ENT_QUOTES, '', FALSE)."</td>\n";
		echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE)."</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
} else {
	echo "<tr>\n";
	echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel wmtPrnBorder1T'>",xl('No Detail on File','e'),"</td>\n";
	// echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
	echo "</tr>\n";
}
$tmp_col=5;
if($fyi->fyi_med_nt) {
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel wmtPrnBorder1T' colspan='$tmp_col'>&nbsp;",xl('Other Notes','e'),":</td>\n";
	echo "		</tr>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnIndentBody' colspan='$tmp_col'>",htmlspecialchars($fyi->fyi_med_nt, ENT_QUOTES, '', FALSE),"</td>\n";
	echo "		</tr>\n";
}
echo "</table>\n";
echo "</fieldset>\n";
$pane_printed = true;
?>
