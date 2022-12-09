<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array('DOB', 'age', 'race', 'status', 
	'occupation', 'wmt_education', 'language', 'ethnicity', 
	'phone_home', 'phone_biz', 'phone_cell', 'email', 'fyi');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
<table width="100%"	border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td style="width: 50%" style="border-right: solid 1px black">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td style="width: 25%" class="wmtBody"><span class="wmtBody4">Birth Date</span><input name="<?php echo $field_prefix; ?>DOB" id="<?php echo $field_prefix; ?>DOB" type="text" class="wmtFullInput2" value="<?php echo $dt{$field_prefix.'DOB'};?>" onchange="CalcPatAge('<?php echo $field_prefix; ?>DOB', '<?php echo $field_prefix; ?>age');" /></td>
				<td style="width: 25%" class="wmtBody"><span class="wmtBody4">Age</span><input name="<?php echo $field_prefix; ?>age" id="<?php echo $field_prefix; ?>age" type"text" disabled="disabled" class="wmtFullInput2" value="<?php echo $dt{$field_prefix.'age'};?>" /></td>
				<td style="width: 25%" class="wmtBody"><span class="wmtBody4">Race</span><select name="<?php echo $field_prefix; ?>race" id="<?php echo $field_prefix; ?>race" class="wmtFullInput2">
				<?php ListSel($dt{$field_prefix.'race'}, 'race'); ?>
				</select>
				</td>
				<td style="width: 25%" class="wmtBody"><span class="wmtBody4">Marital Status</span><select name="<?php echo $field_prefix; ?>status" id="<?php echo $field_prefix; ?>status" class="wmtFullInput2">
					<?php ListSel($dt{$field_prefix.'status'}, 'marital'); ?>
				</select>
				</td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('DOB','race','status');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>DOB" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['DOB']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>age" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['age']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>race" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['race']['content'], 'race'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>status" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['status']['content'], 'marital'), ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Occupation</span><input name="<?php echo $field_prefix; ?>occupation" id="<?php echo $field_prefix; ?>occupation" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'occupation'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Education (Last Completed)</span><input name="<?php echo $field_prefix; ?>wmt_education" id="<?php echo $field_prefix; ?>wmt_education" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_education'}, ENT_QUOTES, '', FALSE);?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['occupation']['content'] && ($pat_entries['occupation']['content'] != $dt{$field_prefix.'occupation'}) || $pat_entries['wmt_education']['content'] && ($pat_entries['wmt_education']['content'] != $dt{$field_prefix.'wmt_education'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>occupation" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['occupation']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>wmt_education" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wmt_education']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Language</span><select name="<?php echo $field_prefix; ?>language" id="<?php echo $field_prefix; ?>language" class="wmtFullInput2">
				<?php ListSel($dt{$field_prefix.'language'}, 'language'); ?>
				</select></td>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Ethnicity</span><select name="<?php echo $field_prefix; ?>ethnicity" id="<?php echo $field_prefix; ?>ethnicity" class="wmtFullInput2">
				<?php ListSel($dt{$field_prefix.'ethnicity'}, 'ethnicity'); ?>
				</select></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['language']['content'] && ($pat_entries['language']['content'] != $dt{$field_prefix.'language'}) || $pat_entries['ethnicity']['content'] && ($pat_entries['ethnicity']['content'] != $dt{$field_prefix.'ethnicity'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>language" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['language']['content'], 'language'), ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>ethnicity" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['ethnicity']['content'], 'ethnicity'), ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Home Phone</span><input name="<?php echo $field_prefix; ?>phone_home" id="<?php echo $field_prefix; ?>phone_home" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_home'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Work Phone</span><input name="<?php echo $field_prefix; ?>phone_biz" id="<?php echo $field_prefix; ?>phone_biz" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_biz'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['phone_home']['content'] && ($pat_entries['phone_home']['content'] != $dt{$field_prefix.'phone_home'}) || $pat_entries['phone_biz']['content'] && ($pat_entries['phone_biz']['content'] != $dt{$field_prefix.'phone_biz'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_home" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_home']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_biz" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_biz']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">Cell Phone</span><input name="<?php echo $field_prefix; ?>phone_cell" id="<?php echo $field_prefix; ?>phone_cell" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_cell'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2" class="wmtBody"><span class="wmtBody4">E-mail</span><input name="<?php echo $field_prefix; ?>email" id="<?php echo $field_prefix; ?>email" type="text" class="wmtFullInput2" value="<?php echo htmlspecialchars($dt{$field_prefix.'email'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['phone_cell']['content'] && ($pat_entries['phone_cell']['content'] != $dt{$field_prefix.'phone_cell'}) || $pat_entries['email']['content'] && ($pat_entries['email']['content'] != $dt{$field_prefix.'email'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_cell" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_cell']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>email" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['email']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

		</table></td>

		<td style="width: 50%">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtBody" style="width: 100%"><span class="wmtBody4">Current FYI:</span><textarea name="fyi" id="fyi" class="wmtFullInput2" rows="9"><?php echo htmlspecialchars($dt['fyi'], ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
		</table></td>
	</tr>
</table>
