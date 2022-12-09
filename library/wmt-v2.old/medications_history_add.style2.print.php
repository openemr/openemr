<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($med_hist)) $med_hist = array();
if(!isset($dt['fyi_medhist_nt'])) $dt['fyi_medhist_nt'] = '';
if(!isset($fyi->fyi_medhist_nt)) $fyi->fyi_medhist_nt = '';
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php xl('Current Medications','e'); ?>&nbsp;</legend>
<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse;'>
	<tr>
		<td class='wmtPrnLabel wmtPrnC' style='width: 95px'><?php xl('Start Date','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Medication','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('End Date','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Destination','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Comments','e'); ?></td>
	</tr>
<?php
if(count($med_hist) > 0) {
		foreach($med_hist as $prev) {
?>
	<tr>
		<td class='wmtPrnBody wmtPrnBorder1B'><?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBody wmtPrnBorder1B wmtPrnBorder1L'><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBody wmtPrnBorder1B wmtPrnBorder1L'><?php echo htmlspecialchars($prev['enddate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBody wmtPrnBorder1B wmtPrnBorder1L'><?php echo htmlspecialchars($prev['destination'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		<td class='wmtPrnBody wmtPrnBorder1B wmtPrnBorder1L'><?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
	</tr>
<?php
	}
} else {
?>
	<tr>
		<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>
		<td class='wmtPrnLabel wmtPrnBorder1T'><?php xl('No Detail on File','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>
		<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>
		<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>
	</tr>
<?php
}
if($fyi->fyi_medhist_nt)) {
?>
	<tr>
		<td class='wmtPrnLabel wmtPrnBorder1T' colspan='5'><?php xl('Other Notes','e'); ?>:</td>
	</tr>
	<tr>
		<td class='wmtPrnBody' colspan='5'><?php echo htmlspecialchars($fyi->fyi_medhist_nt, ENT_QUOTES, '', FALSE); ?></td>
	</tr>
<?php
}
$pane_printed = true;
?>
</table>
</fieldset>
