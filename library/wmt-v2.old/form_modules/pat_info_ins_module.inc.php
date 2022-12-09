<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array('DOB', 'age', 'race', 'status', 
	'occupation', 'wmt_education', 'language', 'ethnicity', 'street', 'city',
	'state', 'postal_code', 'phone_home', 'phone_biz', 'phone_cell', 'email');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
<table width="100%"	border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; padding: 0px;">
	<tr>
		<td style="width: 50%; border-right: solid 1px black;">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td style="width: 35%" class="wmtL"><span class="wmtLabel2">Birth Date</span><br><input name="<?php echo $field_prefix; ?>DOB" id="<?php echo $field_prefix; ?>DOB" type="text" class="wmtDateInput" value="<?php echo htmlspecialchars($dt[$field_prefix.'DOB'], ENT_QUOTES, '', FALSE); ?>" onchange="CalcPatAge('<?php echo $field_prefix; ?>DOB', 'tmp_age');" onkeyup="datekeyup(this, mypcc)" onblur="dateblur(this, mypcc);" title="YYYY-MM-DD" />
       		<img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>DOB" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
					<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>DOB", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>DOB"});
					</script>
				<td style="width: 15%"><span class="wmtLabel2">Age</span><input name="tmp_age" id="tmp_age" type="text" tabindex="-1" style="background-color: #EAEAEA;" readonly class="wmtFullInput" value="<?php echo $patient->age; ?>" /></td>
				<td style="width: 25%"><span class="wmtLabel2">Race</span><select name="<?php echo $field_prefix; ?>race" id="<?php echo $field_prefix; ?>race" class="wmtFullInput">
				<?php ListSel($dt{$field_prefix.'race'}, 'race'); ?>
				</select></td>
				<td style="width: 25%"><span class="wmtLabel2">Marital Status</span><select name="<?php echo $field_prefix; ?>status" id="<?php echo $field_prefix; ?>status" class="wmtFullInput">
				<?php ListSel($dt{$field_prefix.'status'}, 'marital'); ?>
				</select></td>
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
				<td colspan="2"><span class="wmtLabel2">Occupation</span><input name="<?php echo $field_prefix; ?>occupation" id="<?php echo $field_prefix; ?>occupation" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'occupation'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2"><span class="wmtLabel2">Education (Last Completed)</span><input name="<?php echo $field_prefix; ?>wmt_education" id="<?php echo $field_prefix; ?>wmt_education" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_education'}, ENT_QUOTES, '', FALSE); ?>" /></td>
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
				<td colspan="4"><span class="wmtLabel2">Address</span><input name="<?php echo $field_prefix; ?>street" id="<?php echo $field_prefix; ?>street" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'street'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['street']['content'] && ($pat_entries['street']['content'] != $dt{$field_prefix.'street'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>street" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['street']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2"><span class="wmtLabel2">City</span><input name="<?php echo $field_prefix; ?>city" id="<?php echo $field_prefix; ?>city" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'city'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td><span class="wmtLabel2">State</span><input name="<?php echo $field_prefix; ?>state" id="<?php echo $field_prefix; ?>state" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'state'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td><span class="wmtLabel2">ZIP</span><input name="<?php echo $field_prefix; ?>postal_code" id="<?php echo $field_prefix; ?>postal_code" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'postal_code'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('city','state','postal_code');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>city" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['city']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>state" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['state']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>postal_code" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['postal_code']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td colspan="2"><span class="wmtLabel2">Home Phone</span><input name="<?php echo $field_prefix; ?>phone_home" id="<?php echo $field_prefix; ?>phone_home" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_home'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2"><span class="wmtLabel2">Work Phone</span><input name="<?php echo $field_prefix; ?>phone_biz" id="<?php echo $field_prefix; ?>phone_biz" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_biz'}, ENT_QUOTES, '', FALSE); ?>" /></td>
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

		</table></td>

		<td style="width: 50%">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td colspan="3"><span class="wmtLabel2">E-mail</span><input name="<?php echo $field_prefix; ?>email" id="<?php echo $field_prefix; ?>email" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt[$field_prefix.'email'], ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="2"><span class="wmtLabel2">Cell Phone</span><input name="<?php echo $field_prefix; ?>phone_cell" id="<?php echo $field_prefix; ?>phone_cell" type="text" class="wmtFullInput" value="<?php echo htmlspecialchars($dt[$field_prefix.'phone_cell'], ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['phone_cell']['content'] && ($pat_entries['phone_cell']['content'] != $dt{$field_prefix.'phone_cell'}) || $pat_entries['email']['content'] && ($pat_entries['email']['content'] != $dt{$field_prefix.'email'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>email" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['email']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_cell" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_cell']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>


			<!-- tr>
				<td colspan="3"><span class="wmtLabel2">Ethnicity</span><select name="<?php echo $field_prefix; ?>ethnicity" id="<?php echo $field_prefix; ?>ethnicity" class="wmtFullInput">
				<?php // ListSel($patient->ethnicity, 'ethnicity'); ?>
				</select></td>
				<td colspan="2"><span class="wmtLabel2">Language</span><select name="<?php echo $field_prefix; ?>language" id="<?php echo $field_prefix; ?>language" class="wmtFullInput">
				<?php // ListSel($patient->language, 'language'); ?>
				</select></td>
			</tr -->
			<tr>
				<td colspan="3"><span class="wmtLabel2"><?php xl('Primary Insurance','e'); ?></span>
					<input name="tmp_ins_primary" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(($patient->primary) ? $patient->primary : xl('No Insurance','e'), ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 20%;"><span class="wmtLabel2"><?php xl('Policy #','e'); ?></span><input name="tmp_ins_primary_policy" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_id, ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 20%;"><span class="wmtLabel2"><?php xl('Group #','e'); ?></span><input name="tmp_ins_primary_group" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_group, ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td style="width: 20%;"><span class="wmtLabel2"><?php xl('Insured First','e'); ?></span>
					<input name="tmp_ins_first" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_fname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 10%;"><span class="wmtLabel2"><?php xl('Middle','e'); ?></span>
				<input name="tmp_ins_middle" type"text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_mname, ENT_QUOTES, '', FALSE); ?>">
				</td>
				<td><span class="wmtLabel2"><?php xl('Last Name','e'); ?></span>
					<input name="tmp_ins_last" type"text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_lname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Birth Date','e'); ?></span>
					<input name="tmp_ins_DOB" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_DOB, ENT_QUOTES, '', FALSE); ?>">
				</td>
				<td><span class="wmtLabel2"><?php xl('Relationship','e'); ?></span>
					<input name="tmp_ins_relation" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(ListLook($patient->primary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td colspan="3"><span class="wmtLabel2"><?php xl('Secondary Insurance','e'); ?></span>
					<input name="tmp_ins_secondary" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Policy #','e'); ?></span><input name="tmp_ins_secondary_policy" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" tabindex="-1" readonly value="<?php echo htmlspecialchars($patient->secondary_id, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Group #','e'); ?></span><input name="tmp_ins_secondary_group" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" tabindex="-1" readonly value="<?php echo htmlspecialchars($patient->secondary_group, ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td><span class="wmtLabel2"><?php xl('Subscriber First','e'); ?></span>
					<input name="tmp_ins2_first" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_fname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Middle','e'); ?></span>
					<input name="tmp_ins2_middle" type"text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_mname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Last Name','e'); ?></span>
					<input name="tmp_ins2_last" type"text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_lname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('SS#','e'); ?></span><input name="tmp_ins2_ss" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_ss, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="wmtLabel2"><?php xl('Relationship','e'); ?></span>
					<input name="tmp_ins2_relation" type="text" class="wmtFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(ListLook($patient->secondary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
    </table></td>
	</tr>
</table>
