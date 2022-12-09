<?php
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="4">

			<!-- OUTER LOOP WILL BE THE MAIN CATEGORIES -->
			<?php foreach($dated_list_categories as $cat) { ?>
      <tr>
        <td class="wmtLabel" style="width: 350px;">&nbsp;<?php echo htmlspecialchars($cat['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
				<td class="wmtLabel wmtC" style="width: 40px;">Done</td>
				<td class="wmtLabel wmtDateCell wmtC">Date</td>
				<td class="wmtLabel">Comments</td>
      </tr>

			<?php
			foreach($dated_list_keys as $key) {
				if($cat['option_id'] != $key['notes']) continue;
				if(!isset($dated_list_selected[$key['option_id']][1])) 
												$dated_list_selected[$key['option_id']][1] = '';
				if(!isset($dated_list_selected[$key['option_id']][2])) 
												$dated_list_selected[$key['option_id']][2] = '';
				if(!isset($dated_list_selected[$key['option_id']][3])) 
												$dated_list_selected[$key['option_id']][3] = '';
				$base = 'tmp_dt_list_'.$cat['option_id'].'_'.$key['option_id'];
			?>
				
			<tr>
				<td class="wmtBody">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo htmlspecialchars($key['title'],ENT_QUOTES,'',FALSE); ?></td>
				<td class="wmtBody wmtC"><label for="<?php echo $base; ?>">&nbsp;&nbsp;&nbsp;<input name="<?php echo $base; ?>" id="<?php echo $base; ?>" type="checkbox" value="<?php echo $key['option_id']; ?>" <?php echo ($dated_list_selected[$key['option_id']][1]) != '' ? 'checked ' : ''; ?> />&nbsp;&nbsp;&nbsp;</label></td>
				<td class="wmtBody"><input name="<?php echo $base.'_dt'; ?>" id="<?php echo $base.'_dt'; ?>" type="text" class="wmtDateInput" onfocus="setDateListDefaultDate('<?php echo $base; ?>','<?php echo $base."_dt"; ?>');" value="<?php echo $dated_list_selected[$key['option_id']][2]; ?>" /></td>
				<td class="wmtBody"><input name="<?php echo $base.'_nt'; ?>" id="<?php echo $base.'_nt'; ?>" type="text" class="wmtFullInput" value="<?php echo $dated_list_selected[$key['option_id']][3]; ?>" /></td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="3">&nbsp;</td>
				<td><div style="float: left; padding-left: 30px;"><a href="javascript:;" class="css_button" onclick="setChecks('<?php echo 'tmp_dt_list_'.$cat['option_id'].'_'; ?>', '', true, '_dt'); " ><span>Check All As Complete</span></a></div>
				<div style="float: right; padding-right: 30px;"><a href="javascript:;" class="css_button" onclick="setChecks('<?php echo 'tmp_dt_list_'.$cat['option_id'].'_'; ?>', '', false, '_dt'); " ><span>Clear All Checks</span></a></div></td>
			</tr>
			<?php if($frmdir == 'ob_complete') { ?>
      <tr><td class="wmtLabel" colspan="4">&nbsp;<?php echo htmlspecialchars($cat['title'], ENT_QUOTES, '', FALSE); ?>&nbsp;Other Notes:</td></tr>
			<tr>
				<td colspan="4"><textarea name="tri_<?php echo $cat['option_id']; ?>_material_nt" id="tri_<?php echo $cat['option_id']; ?>_material_nt" rows="4" class="wmtFullInput"><?php echo htmlspecialchars($dt{'tri_'.$cat['option_id'].'_material_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
			<?php
			}
			?>
			
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
<?php ?>
