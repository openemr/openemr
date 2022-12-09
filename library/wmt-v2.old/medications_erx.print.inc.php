<?php
if(!isset($meds)) $meds = array();
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(isset($fyi->fyi_med_nt)) $dt['fyi_med_nt'] = $fyi->fyi_med_nt;
?>
<div class='wmtPrnMainContainer'>
<div class='wmtPrnCollapseBar'>
	<span class='wmtPrnChapter'><?php xl('Current Medications','e'); ?></span>
</div>
<div class='wmtPrnCollapseBox'>
	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td class='wmtPrnLabelCenterBorderB' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Medication','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Dosage','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Sig','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Comments','e'); ?></td>
		</tr>
<?php
$cnt=1;
if(count($meds) > 0) {
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
		if(substr($prev['dosage'],0,3) == "Add") $sig2 = '';
		$sig1 = $prev['dosage'] . $form . $sig1 . $sig2;
		$size = trim($prev['size']);
		$unit = trim(ListLook($prev['unit'],'drug_units'));
		$size .= $unit;
		echo "<tr>\n";
		echo "<td class='wmtPrnBodyBorderB'>".htmlspecialchars($prev['date_added'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($prev['drug'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($size, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($sig1, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
} else {
?>
		<tr>
			<td class='wmtPrnLabelBorderB'>&nbsp;</td>
			<td class='wmtPrnLabelBorderLB'><?php xl('None on File','e'); ?></td>
			<td class='wmtPrnLabelBorderLB'>&nbsp;</td>
			<td class='wmtPrnLabelBorderLB'>&nbsp;</td>
			<td class='wmtPrnLabelBorderLB'>&nbsp;</td>
		</tr>
<?php
}
if(!empty($dt['fyi_med_nt'])) {
?>
		<tr>
			<td class='wmtPrnLabel' colspan='2'><?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class='wmtPrnBody' colspan='5'><?php echo htmlspecialchars($dt['fyi_med_nt'], ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php
}
?>
	</table>
</div>
</div>
