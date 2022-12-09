<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'last_hear', 'left_ear', 'right_ear', 'hear_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Hearing&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Hearing Test:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_hear" id="<?php echo $field_prefix; ?>last_hear" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hear'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hear" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hear", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_hear"});
			</script>
			<td>Left Ear:</td>
			<td><select name="<?php echo $field_prefix; ?>left_ear" id="<?php echo $field_prefix; ?>left_ear" class="wmtInput" style="width: 95px;">
				<?php ListSel($dt{$field_prefix.'left_ear'},'PassFail'); ?>
			</select></td>
			<td>Right Ear:</td>
			<td><select name="<?php echo $field_prefix; ?>right_ear" id="<?php echo $field_prefix; ?>right_ear" class="wmtInput" style="width: 95px;">
				<?php ListSel($dt{$field_prefix.'right_ear'},'PassFail'); ?>
			</select></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_hear','left_ear','right_ear');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_hear" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hear']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td><span class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>left_ear" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['left_ear']['content'],'PassFail'), ENT_QUOTES); ?></span></td>
				<td>&nbsp;</td>
				<td><span class="wmtBorderHighlight" style="width: 45px; padding-right: 30px;" id="tmp_<?php echo $field_prefix; ?>right_ear" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['right_ear']['content'],'PassFail'), ENT_QUOTES); ?></span></td>
			</tr>
			<?php
				}
			}
			?>
		<tr>
			<td class="wmtT">Hearing Notes:</td>
			<td colspan="6"><textarea name="<?php echo $field_prefix; ?>hear_nt" id="<?php echo $field_prefix; ?>hear_nt" class="wmtFullInput" rows="2"><?php echo htmlspecialchars($dt{$field_prefix.'hear_nt'}, ENT_QUOTES); ?></textarea></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('hear_nt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="6" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>hear_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['hear_nt']['content'], ENT_QUOTES); ?></td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
