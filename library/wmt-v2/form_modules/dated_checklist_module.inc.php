<?php
if(!isset($pat_sex)) $pat_sex = strtolower(substr($patient->sex,0,1));
?>
<table width="95%" border="0" cellspacing="0" cellpadding="0" style="white-space: normal; margin: 4px 10px 4px 10px;">

<!-- OUTER LOOP WILL BE THE MAIN CATEGORIES -->
<?php foreach($dated_list_categories as $cat) { ?>
	<tr>
		<td class="wmtLabel">&nbsp;<?php echo htmlspecialchars($cat['title'], ENT_QUOTES); ?>&nbsp;</td>
		<td class="wmtLabel wmtC" style="width: 40px;">Done</td>
		<td class="wmtLabel wmtDateCell wmtC">Date</td>
		<td class="wmtLabel">Comments</td>
	</tr>

	<?php
	foreach($dated_list_keys as $key) {
		$flags = explode('::', $key['notes']);
		// POSITION ONE IS THE HEADER IT BELONGS TO
		// POSITION TWO IS THE SEX FILTER
		// POSITION THREE IS A MINIMUM AGE FILTER
		// POSITION FOUR IS A MAXIMUM AGE FILTER
		if(!isset($flags[0])) $flags[0] = '';
		if(!isset($flags[1])) $flags[1] = '';
		if(!isset($flags[2])) $flags[2] = '';
		if(!isset($flags[3])) $flags[3] = '';
		if($cat['option_id'] != $flags[0]) continue;
		if($flags[1] != '' && $flags[1] != $pat_sex && $pat_sex != '') continue;
		if($flags[2] != '' && $patient->age < $flags[2]) continue;
		if($flags[3] != '' && $patient->age > $flags[3]) continue;
		if(!isset($dated_list_selected[$key['option_id']][1])) 
										$dated_list_selected[$key['option_id']][1] = '';
		if(!isset($dated_list_selected[$key['option_id']][2])) 
										$dated_list_selected[$key['option_id']][2] = '';
		if(!isset($dated_list_selected[$key['option_id']][3])) 
										$dated_list_selected[$key['option_id']][3] = '';
		$base = 'tmp_dt_list_'.$cat['option_id'].'_'.$key['option_id'];
	?>
				
	<tr style="max-height: 20px;">
		<td><?php echo htmlspecialchars($key['title'],ENT_QUOTES); ?></td>
		<td style="vertical-align: bottom;"><label for="<?php echo $base; ?>">&nbsp;&nbsp;&nbsp;<input name="<?php echo $base; ?>" id="<?php echo $base; ?>" type="checkbox" value="<?php echo $key['option_id']; ?>" <?php echo ($dated_list_selected[$key['option_id']][1]) != '' ? 'checked ' : ''; ?> />&nbsp;&nbsp;&nbsp;</label></td>
		<td><input name="<?php echo $base.'_dt'; ?>" id="<?php echo $base.'_dt'; ?>" type="text" class="wmtDateInput" onfocus="setDateListDefaultDate('<?php echo $base; ?>','<?php echo $base."_dt"; ?>');" value="<?php echo $dated_list_selected[$key['option_id']][2]; ?>" /></td>
		<td><input name="<?php echo $base.'_nt'; ?>" id="<?php echo $base.'_nt'; ?>" type="text" class="wmtFullInput" value="<?php echo $dated_list_selected[$key['option_id']][3]; ?>" /></td>
	</tr>
	<?php } ?>
	<tr>
		<td colspan="2">&nbsp;</td>
		<td colspan="2"><div style="float: left; padding-left: 30px;"><a href="javascript:;" class="css_button" onclick="setChecks('<?php echo 'tmp_dt_list_'.$cat['option_id'].'_'; ?>', '', true, '_dt'); " ><span>Check All As Complete</span></a></div>
			<div style="float: right; padding-right: 30px;"><a href="javascript:;" class="css_button" onclick="setChecks('<?php echo 'tmp_dt_list_'.$cat['option_id'].'_'; ?>', '', false, '_dt'); " ><span>Clear All Checks</span></a></div></td>
	</tr>
	<?php if($frmdir == 'ob_complete') { ?>
  <tr><td class="wmtLabel" colspan="4">&nbsp;<?php echo htmlspecialchars($cat['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;Other Notes:</td></tr>
	<tr>
		<td colspan="4"><textarea name="tri_<?php echo $cat['option_id']; ?>_material_nt" id="tri_<?php echo $cat['option_id']; ?>_material_nt" rows="4" class="wmtFullInput"><?php echo htmlspecialchars($dt{'tri_'.$cat['option_id'].'_material_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
	<?php } ?>
			
<?php } ?>
</table>
				
		<!-- This div is for all the 'DO NOT USE' keys to retain history -->
<div style="display: none;">
<?php
foreach($dated_list_keys_unused as $key) {
	if(in_array($key['option_id'], $dated_list_selected)) {
		GenerateHiddenInput('tmp_dt_list_u_'.$key['option_id'],
																								$key['option_id']);
		GenerateHiddenInput('tmp_dt_list_u_'.$key['option_id'].'_dt',
												$dated_list_selected[$key['option_id']][1]);
		GenerateHiddenInput('tmp_dt_list_u_'.$key['option_id'].'_dt',
												$dated_list_selected[$key['option_id']][2]);
	}
}
?>
</div>

<script src="<?php echo $GLOBALS['webroot']; ?>/library/wmt-v2/ros_functions.js" type="text/javascript"></script>
<script type="text/javascript">
function setDateListDefaultDate(check, dt)
{
	if(document.getElementById(check).checked) {
		setEmptyDate(dt);
	}
}
</script>
<?php ?>
