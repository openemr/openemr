<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'last_ekg', 'last_pft');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Cardio&nbsp;&amp;&nbsp;Pulmonary Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last EKG:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_ekg" id="<?php echo $field_prefix; ?>last_ekg" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_ekg'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_ekg" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_ekg", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_ekg"});
				</script>
			<td style="width: 22%;">Last PFT:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_pft" id="<?php echo $field_prefix; ?>last_pft" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pft'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_pft" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_pft", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_pft"});
				</script>
			<td style="width: 22%;">&nbsp;</td>
			<td class="wmtDateCell">&nbsp;</td>
			<td class="wmtCalendarCell">&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_ekg','last_pft');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_ekg" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_ekg']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_pft" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_pft']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
