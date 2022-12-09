<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$local_fields = array('ins1_auth', 'ins2_auth', 'ins3_auth');
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
				<td colspan="3"><span class="bkkLabel2"><?php xl('Primary Insurance','e'); ?></span>
					<input name="tmp_ins_primary" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(($patient->primary) ? $patient->primary : xl('No Insurance','e'), ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 20%;"><span class="bkkLabel2"><?php xl('Policy #','e'); ?></span><input name="tmp_ins_primary_policy" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_id, ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 20%;"><span class="bkkLabel2"><?php xl('Group #','e'); ?></span><input name="tmp_ins_primary_group" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_group, ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td style="width: 20%;"><span class="bkkLabel2"><?php xl('Insured First','e'); ?></span>
					<input name="tmp_ins_first" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_fname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td style="width: 10%;"><span class="bkkLabel2"><?php xl('Middle','e'); ?></span>
				<input name="tmp_ins_middle" type"text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_mname, ENT_QUOTES, '', FALSE); ?>">
				</td>
				<td><span class="bkkLabel2"><?php xl('Last Name','e'); ?></span>
					<input name="tmp_ins_last" type"text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_lname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Birth Date','e'); ?></span>
					<input name="tmp_ins_DOB" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_DOB, ENT_QUOTES, '', FALSE); ?>">
				</td>
				<td><span class="bkkLabel2"><?php xl('Relationship','e'); ?></span>
					<input name="tmp_ins_relation" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(ListLook($patient->primary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td colspan="3"><span class="bkkLabel2"><?php xl('Contact/Attn','e'); ?></span>
					<input name="tmp_ins_attn" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(($patient->primary_attn), ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Phone','e'); ?></span><input name="tmp_ins_primary_phone" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_phone, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2" style="color: red;"><?php xl('Authorization','e'); ?></span><input name="ins1_auth" id="ins1_auth" type="text" class="bkkFullInput" style="border: solid 1px red;" value="<?php echo htmlspecialchars($dt['ins1_auth'], ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
		</table></td>

		<td style="width: 50%">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td colspan="3"><span class="bkkLabel2"><?php xl('Secondary Insurance','e'); ?></span>
					<input name="tmp_ins_secondary" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Policy #','e'); ?></span><input name="tmp_ins_secondary_policy" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" tabindex="-1" readonly value="<?php echo htmlspecialchars($patient->secondary_id, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Group #','e'); ?></span><input name="tmp_ins_secondary_group" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" tabindex="-1" readonly value="<?php echo htmlspecialchars($patient->secondary_group, ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td><span class="bkkLabel2"><?php xl('Subscriber First','e'); ?></span>
					<input name="tmp_ins2_first" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_fname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Middle','e'); ?></span>
					<input name="tmp_ins2_middle" type"text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_mname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Last Name','e'); ?></span>
					<input name="tmp_ins2_last" type"text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_lname, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('SS#','e'); ?></span><input name="tmp_ins2_ss" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_ss, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Relationship','e'); ?></span>
					<input name="tmp_ins2_relation" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(ListLook($patient->secondary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
			<tr>
				<td colspan="3"><span class="bkkLabel2"><?php xl('Contact/Attn','e'); ?></span>
					<input name="tmp_ins2_attn" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars(($patient->secondary_attn), ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2"><?php xl('Phone','e'); ?></span><input name="tmp_ins2_primary_phone" type="text" class="bkkFullInput" style="background-color: #EAEAEA;" readonly tabindex="-1" value="<?php echo htmlspecialchars($patient->secondary_phone, ENT_QUOTES, '', FALSE); ?>"></td>
				<td><span class="bkkLabel2" style="color: red;"><?php xl('Authorization','e'); ?></span><input name="ins2_auth" id="ins2_auth" type="text" class="bkkFullInput" style="border: solid 1px red;" value="<?php echo htmlspecialchars($dt['ins2_auth'], ENT_QUOTES, '', FALSE); ?>"></td>
			</tr>
    </table></td>
	</tr>
</table>
