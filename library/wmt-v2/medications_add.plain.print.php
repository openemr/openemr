<?php
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(isset($fyi->fyi_med_nt)) $dt['fyi_med_nt'] = $fyi->fyi_med_nt;
$dt['fyi_med_nt'] = trim($dt['fyi_med_nt']);
if(!isset($meds)) $meds = array();
?>
<span class="wmtPrnHeader">Current Medications</span>
	<table width='100%' border='0' cellspacing='0' cellpadding='0'>
		<tr>
			<td class="wmtPrnLabel" style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class="wmtPrnLabel"><?php xl('Medication','e'); ?></td>
			<td class="wmtPrnLabel"><?php xl('Destination','e'); ?></td>
			<td class="wmtPrnLabel"><?php xl('Comments','e'); ?></td>
		</tr>
<?php
if(count($meds) > 0) {
	foreach($meds as $prev) {
?>
		<tr>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['begdate'],ENT_QUOTES,'',FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['title'],ENT_QUOTES,'',FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['destination'],ENT_QUOTES,'',FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['comments'],ENT_QUOTES,'',FALSE); ?>&nbsp;</td>
		</tr>
<?php
	}
} else {
?>
		<tr>
			<td class="wmtPrnLabel" colspan="2"><?php xl('None on File','e'); ?></td>
			<td class="wmtPrnLabel">&nbsp;</td>
			<td class="wmtPrnLabel">&nbsp;</td>
		</tr>
<?php
}
if(!empty($dt['fyi_med_nt'])) {
?>
		<tr>
			<td class='wmtPrnLabel' colspan='2'><?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class='wmtPrnBody' colspan='4'><?php echo htmlspecialchars($dt['fyi_med_nt'],ENT_QUOTES,'',FALSE); ?></td>
		</tr>
<?php } ?>
</table>
<br>
