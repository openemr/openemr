<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title = xl('Allergies','r');
if(!isset($allergy_add_allowed)) 
				$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
if(!isset($fyi->fyi_allergy_nt)) $fyi->fyi_allergy_nt = '';
if(!isset($allergies)) $allergies = array();
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse; margin-top: 4px;'>
		<tr>
			<td class='wmtPrnLabel wmtPrnC' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Title','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Reaction','e'); ?></td>
			<td class='wmtPrnLabel wmtPrnC'><?php xl('Comments','e'); ?></td>
		</tr>
<?php
if(count($allergies) > 0) {
	foreach($allergies as $prev) {
?>
		<tr>
			<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;<?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?></td>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;<?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?></td>
			<?php if($allergy_add_allowed) { ?>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;<?php echo htmlspecialchars($prev['reaction'], ENT_QUOTES, '', FALSE); ?></td>
			<?php } else { ?>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;<?php echo htmlspecialchars(ListLook($prev['outcome'],'outcome'), ENT_QUOTES, '', FALSE); ?></td>
			<?php } ?>
			<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;<?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php
	}
} else {
?>
		<tr>
			<td class='wmtPrnLabel'>&nbsp;</td>
			<td class='wmtPrnLabel'>&nbsp;<?php xl('No Detail on File','e'); ?></td>
			<td class='wmtPrnLabel'>&nbsp;</td>
			<td class='wmtPrnLabel'>&nbsp;</td>
		</tr>
<?php
}
if($fyi->fyi_allergy_nt) {
?>
		<tr>
			<td class='wmtPrnLabel wmtPrnBorder1T' colspan='4'>&nbsp;<?php xl('Other Notes','e'); ?>:</td>
		</tr>
		<tr>
			<td class='wmtPrnBody' colspan='4'><?php echo htmlspecialchars($fyi->fyi_allergy_nt, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php 
}
$pane_printed = true;
?>
	</table>
</fieldset>
