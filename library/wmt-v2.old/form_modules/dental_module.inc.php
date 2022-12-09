<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'last_dental', 'last_dental_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Dental&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Dental Exam:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_dental" id="<?php echo $field_prefix; ?>last_dental" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_dental'}, ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_dental" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_dental", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>",button:"img_<?php echo $field_prefix; ?>last_dental"});
				</script>
			<td style="width: 22%;">&nbsp;</td>
			<td class="wmtDateCell">&nbsp;</td>
			<td class="wmtCalendarCell">&nbsp;</td>
			<td style="width: 22%;">&nbsp;</td>
			<td class="wmtDateCell">&nbsp;</td>
			<td class="wmtCalendarCell">&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_dental');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_dental" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_dental']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
		<tr>
			<td class="wmtT">Dental Exam Notes:</td>
			<td colspan="8"><textarea name="<?php echo $field_prefix; ?>last_dental_nt" id="<?php echo $field_prefix; ?>last_dental_nt" class="wmtFullInput" rows="2"><?php echo htmlspecialchars($dt{$field_prefix.'last_dental_nt'}, ENT_QUOTES); ?></textarea></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_dental_nt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="8" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_dental_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_dental_nt']['content'], ENT_QUOTES); ?></td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
