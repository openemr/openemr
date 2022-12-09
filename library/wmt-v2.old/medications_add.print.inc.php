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
	<table width='100%' border='0' cellspacing='0' cellpadding='0' style="border-collapse: collapse;">
		<tr>
			<td class='wmtPrnLabelCenterBorderB' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Medication','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Destination','e'); ?></td>
			<td class='wmtPrnLabelCenterBorderLB'><?php xl('Comments','e'); ?></td>
	</tr>
<?php
if(count($meds) > 0) {
	foreach($meds as $prev) {
?>
	<tr>
		<td class='wmtPrnBodyBorderB'><?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['destination'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBodyBorderLB'><?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
	</tr>
<?php
	}
} else {
	?>
		<tr>
		<td class='wmtPrnLabelBorderB'>&nbsp;</td>
		<td class='wmtPrnLabelBorderLB'><?php xl('None on File','e'); ?></td>
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
		<td class='wmtPrnBody' colspan='4'><?php echo htmlspecialchars($dt['fyi_med_nt'], ENT_QUOTES, '', FALSE); ?></td>
	</tr>
<?php 
}
?>
</table>
</div>
</div>
