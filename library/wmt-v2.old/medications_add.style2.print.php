<?php
if(!isset($fyi->fyi_med_nt)) $fyi->fyi_med_nt = '';
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Current Medications','r');
if(!isset($meds)) $meds = array();
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse; margin-top: 4px;'>
		<tr>
			<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Medication','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Destination','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC wmtPrnBorder1B'><?php xl('Comments','e'); ?></td>
		</tr>
<?php
if(count($meds) > 0) {
	foreach($meds as $prev) {
?>
		<tr>
			<td class='wmtPrnBody wmtPrnBorder1B'><?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'><?php echo htmlspecialchars($prev['destination'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1B'><?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
	<?php
	}
} else {
	?>
	<tr>
		<td class='wmtPrnLabel wmtPrnBorder1B'>&nbsp;</td>
		<td class='wmtPrnLabel wmtPrnBorder1L wmtPrnBorder1B'><?php xl('No Detail on File','e'); ?></td>
		<td class='wmtPrnLabel wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;</td>
		<td class='wmtPrnLabel wmtPrnBorder1L wmtPrnBorder1B'>&nbsp;</td>
	</tr>
<?php 
}
if($fyi->fyi_med_nt) {
?>
		<tr>
			<td class='wmtPrnLabel' colspan='2'><?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class='wmtPrnBody' colspan='4'><?php echo htmlspecialchars($fyi->fyi_med_nt, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php
}
$pane_printed = true;
?>
	</table>
</fieldset>
