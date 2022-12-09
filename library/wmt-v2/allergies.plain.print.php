<?php
if(!isset($allergy_add_allowed)) 
				$allergy_add_allowed = checkSettingMode('wmt::db_allergy_add');
if(!isset($dt['fyi_allergy_nt'])) $dt['fyi_allergy_nt'] = '';
if(isset($fyi->fyi_allergy_nt)) $dt['fyi_allergy_nt'] = $fyi->fyi_allergy_nt;
if(!isset($allergies)) $allergies = array();
?>
<span class="wmtPrnHeader"><?php xl('Allergies','e'); ?></span>
	<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; margin-top: 4px;">
<?php 
if(count($allergies) > 0) {
?>
		<tr>
			<td class='wmtPrnLabel' style='width: 95px'><?php xl('Start Date','e'); ?></td>
			<td class='wmtPrnLabel'><?php xl('Title','e'); ?></td>
			<td class='wmtPrnLabel'><?php xl('Reaction','e'); ?></td>
			<td class='wmtPrnLabel'><?php xl('Comments','e'); ?></td>
		</tr>
	<?php
	foreach($allergies as $prev) {
	?>
		<tr>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<?php if($allergy_add_allowed) { ?>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['reaction'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
			<?php } else { ?>
			<td class='wmtPrnBody'><?php echo ListLook($prev['outcome'],'outcome'); ?>&nbsp;</td>
			<?php } ?>
			<td class='wmtPrnBody'><?php echo htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
<?php 
	}
} else if (!$dt['fyi_allergy_nt']) {
?>
	<tr>
		<td class='wmtPrnLabel' colspan="2">&nbsp;<?php xl('None on File','e'); ?></td>
		<td class='wmtPrnLabel'>&nbsp;</td>
		<td class='wmtPrnLabel'>&nbsp;</td>
	</tr>
<?php
}
if(!empty($dt['fyi_allergy_nt'])) {
	if(count($allergies) > 0) {
?>
		<tr>
			<td class='wmtPrnLabel' colspan='4'><?php xl('Other Notes','e'); ?>:</td>
		</tr>
<?php } ?>
		<tr>
			<td class='wmtPrnBody' colspan='4'><?php echo htmlspecialchars($dt['fyi_allergy_nt'], ENT_QUOTES, '', FALSE); ?></td>
		</tr>
<?php
}
?>
	</table>
</fieldset>
<br>
