<?php 
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
$local_fields = array('last_colon', 'last_bone', 'last_chol', 'last_rectal',
		'last_db_eye', 'last_db_foot', 'last-pft', 'last_aorta', 'last_bladder',
		'last_pap', 'last_mamm', 'last_mp', 'last_psa');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtLabelR">Last Colonoscopy:&nbsp;&nbsp;&nbsp;</td>
        <td><input name="<?php echo $field_prefix; ?>last_colon" id="<?php echo $field_prefix; ?>last_colon" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_colon'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
        <td class="wmtLabelR">Last Bone Density:&nbsp;&nbsp;&nbsp;</td>
        <td><input name="<?php echo $field_prefix; ?>last_bone" id="<?php echo $field_prefix; ?>last_bone" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_bone'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
        <td class="wmtLabelR">Last Cholesterol Check:&nbsp;&nbsp;&nbsp;</td>
        <td><input name="<?php echo $field_prefix; ?>last_chol" id="<?php echo $field_prefix; ?>last_chol" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_chol'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
      </tr>
			<tr>
				<td class="wmtLabelR">Last Rectal Exam:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_rectal" id="<?php echo $field_prefix; ?>last_rectal" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_rectal'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<td class="wmtLabelR">Last Diabetic Eye Exam:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_db_eye" id="<?php echo $field_prefix; ?>last_db_eye" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_eye'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<td class="wmtLabelR">Last Diabetic Foot Exam:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_db_foot" id="<?php echo $field_prefix; ?>last_db_foot" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_foot'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
			</tr>
			<?php 
			if($client_id == 'qhc') {
			?>
			<tr>
				<td class="wmtLabelR">Last PFT:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_pft" id="<?php echo $field_prefix; ?>last_pft" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pft'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<td class="wmtLabelR">Last Aorta Ultrasound:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_aorta" id="<?php echo $field_prefix; ?>last_aorta" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_aorta'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<td class="wmtLabelR">Last Bladder Ultrasound:&nbsp;&nbsp;&nbsp;</td>
				<td><input name="<?php echo $field_prefix; ?>last_bladder" id="<?php echo $field_prefix; ?>last_bladder" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_bladder'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
			</tr>
			<?php } ?>
			<tr>
				<?php if($pat_sex == 'f') { ?>
					<td class="wmtLabelR">Last Pap Smear:&nbsp;&nbsp;&nbsp;</td>
					<td><input name="<?php echo $field_prefix; ?>last_pap" id="<?php echo $field_prefix; ?>last_pap" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pap'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
					<td class="wmtLabelR">Last Mammogram:&nbsp;&nbsp;&nbsp;</td>
					<td><input name="<?php echo $field_prefix; ?>last_mamm" id="<?php echo $field_prefix; ?>last_mamm" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mamm'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
					<td class="wmtLabelR">LMP:&nbsp;&nbsp;&nbsp;</td>
					<td><input name="<?php echo $field_prefix; ?>last_mp" id="<?php echo $field_prefix; ?>last_mp" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mp'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<?php } else { ?>
					<td class="wmtLabelR">Last PSA:&nbsp;&nbsp;&nbsp;</td>
					<td><input name="<?php echo $field_prefix; ?>last_psa" id="<?php echo $field_prefix; ?>last_psa" class="wmtInput2" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_psa'}, ENT_QUOTES, '', FALSE); ?>" title="YYYY-MM-DD" /></td>
				<?php } ?>
			</tr>
    </table>
<?php ?>
