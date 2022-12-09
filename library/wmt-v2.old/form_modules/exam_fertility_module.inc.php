<?php 
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
$local_fields = array('father_yes', 'father_no', 'dysmenorrhea', 'dys_nt',
	'pelvic_pain', 'pain_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
if(!$portal_mode) {
	$local_fields = array( 'tmp_vital_timestamp', 'vital_height', 'vital_weight', 
		'vital_bps', 'vital_bpd', 'vital_pulse', 'vital_BMI', 'vital_BMI_status', 
		'vital_leukocytes', 'vital_nitrite', 'vital_protein', 'vital_glucose', 
		'vital_blood', 'vital_specific_gravity', 'vital_ph', 'vital_ketones', 
		'vital_urobilinogen', 'vital_bilirubin');
	foreach($local_fields as $tmp) {
		if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	}
}
// All have notes
$local_fields = array('hus_empl', 'hus_tobacco', 'hus_drug', 'coital',
	'prev_hsg', 'prev_semen', 'prev_clomid', 'prev_gnrh', 'prev_iui', 'prev_ivf');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($dt[$field_prefix.$tmp.'_nt'])) $dt[$field_prefix.$tmp.'_nt']='';
}
?>
<?php if(!$portal_mode) { ?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>" colspan="2">Vital Signs:</td>
        <td class="<?php echo $portal_mode ? 'bkkLabelRed' : 'wmtLabelRed'; ?>" colspan="7"><?php echo $portal_mode ? 'Most Recent ' : ''; ?>Vitals Taken: <input name="vital_timestamp" id="vital_timestamp" type="text" class="<?php echo $portal_mode ? 'bkkLabelRed' : 'wmtLabelRed'; ?>" style="border: none;" readonly="readonly"  tabindex="-1" value="<?php echo $dt['tmp_vital_timestamp']; ?>" /></td>
				<td colspan="2">
				<?php if($portal_mode) { ?>
					&nbsp;
				<?php } else { ?>
					<a class="css_button" tabindex="-1" onClick="get_vitals();" href="javascript:;"><span>Find Other Vitals</span></a>
				<?php } ?>
				</td>
      </tr>
      <tr>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Height:</td>
        <td><input name="vital_height" id="vital_height" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 50px" value="<?php echo htmlspecialchars($dt{'vital_height'}, ENT_QUOTES, '', FALSE); ?>" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status')" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Weight:</td>
        <td><input name="vital_weight" id="vital_weight" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 50px" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_weight'}, ENT_QUOTES, '', FALSE); ?>" onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status')" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">BP:</td>
        <td><input name="vital_bps" id="vital_bps" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 50px" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_bps'}, ENT_QUOTES, '', FALSE); ?>" />
					&nbsp;/&nbsp;<input name="vital_bpd" id="vital_bpd" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 50px" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_bpd'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">HR:</td>
        <td><input name="vital_pulse" id="vital_pulse" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 60px" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_pulse'}, ENT_QUOTES, '', FALSE); ?>" onchange="NoDecimal('vital_pulse')" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">BMI:</td>
        <td><input name="vital_BMI" id="vital_BMI" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo $portal_mode ? 'tabindex="-1"' : ''; ?> style="width: 60px" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_BMI'}, ENT_QUOTES, '', FALSE); ?>" onchange="OneDecimal('vital_BMI')" /></td>
        <td><input name="vital_BMI_status" id="vital_BMI_status" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" type="text" <?php echo (($wrap_mode != 'new' || $portal_mode)?' readonly ':''); ?> value="<?php echo htmlspecialchars($dt{'vital_BMI_status'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
    </table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Leukocytes:</td>
				<td style="width: 80px"><input name="vital_leukocytes" id="vital_leukocytes" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_leukocytes'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Nitrite:</td>
				<td style="width: 80px"><input name="vital_nitrite" id="vital_nitrite" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_nitrite'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Protein:</td>
				<td style="width: 80px"><input name="vital_protein" id="vital_protein" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_protein'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Glucose:</td>
				<td style="width: 80px"><input name="vital_glucose" id="vital_glucose" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_glucose'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Blood:</td>
				<td style="width: 80px"><input name="vital_blood" id="vital_blood" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_blood'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Specific Gravity:</td>
				<td><input name="vital_specific_gravity" id="vital_specific_gravity" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_specific_gravity'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">pH:</td>
				<td><input name="vital_ph" id="vital_ph" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_ph'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Ketone:</td>
				<td><input name="vital_ketones" id="vital_ketones" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_ketones'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Urobilinogen:</td>
				<td><input name="vital_urobilinogen" id="vital_urobilinogen" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_urobilinogen'}, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Bilirubin:</td>
				<td><input name="vital_bilirubin" id="vital_bilirubin" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" tabindex="-1" readonly="readonly" value="<?php echo htmlspecialchars($dt{'vital_bilirubin'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
    </table>
    <div class="<?php echo $portal_mode ? 'bkkDottedB' : 'wmtDottedB'; ?>"></div>
	<?php } // End Not Portal Mode for Vital Inclusion ?>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
      <tr>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>" style="width: 12%">Husband / Partner:</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>" style="width: 2%"><input name="<?php echo $field_prefix; ?>father_yes" id="<?php echo $field_prefix; ?>father_yes" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'father_yes'} == '1')?' checked ':''); ?> onclick="VerifyYesChecks('<?php echo $field_prefix; ?>father_yes', '<?php echo $field_prefix; ?>father_no');" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" style="width: 180px;"><label for="<?php echo $field_prefix; ?>father_yes">Fathered a child</label></td>
				<td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>" style="width: 2%"><input name="<?php echo $field_prefix; ?>father_no" id="<?php echo $field_prefix; ?>father_no" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'father_no'} == '1')?' checked ':''); ?> onclick="VerifyNoChecks('<?php echo $field_prefix; ?>father_yes', '<?php echo $field_prefix; ?>father_no');" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" style="width: 200px;"><label for="<?php echo $field_prefix; ?>father_no">Has never fathered a child</label></td>
				<td>&nbsp;</td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['father_yes']['content'] && $pat_entries['father_yes']['content'] != $dt{$field_prefix.'father_yes'}) || ($pat_entries['father_no']['content'] && $pat_entries['father_no']['content'] != $dt{$field_prefix.'father_no'})) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>father_yes" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['father_yes']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>father_no" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['father_no']['content'] ? 'Checked': 'Unchecked'; ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>hus_empl" id="<?php echo $field_prefix; ?>hus_empl" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'hus_empl'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>hus_empl">Employed - Occupation:</label></td>
				<td colspan="4"><input name="<?php echo $field_prefix; ?>hus_empl_nt" id="<?php echo $field_prefix; ?>hus_empl_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'hus_empl_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['hus_empl']['content'] && $pat_entries['hus_empl']['content'] != $dt{$field_prefix.'hus_empl'}) || ($pat_entries['hus_empl_nt']['content'] && strpos($dt{$field_prefix.'hus_empl_nt'},$pat_entries['hus_empl_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_empl" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['hus_empl']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_empl_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['hus_empl_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>hus_tobacco" id="<?php echo $field_prefix; ?>hus_tobacco" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'hus_tobacco'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>hus_tobacco">Tobacco Use</label></td>
				<td><input name="<?php echo $field_prefix; ?>hus_tobacco_nt" id="<?php echo $field_prefix; ?>hus_tobacco_nt" type="text" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'hus_tobacco_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>hus_drug" id="<?php echo $field_prefix; ?>hus_drug" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'hus_drug'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>hus_drug">Drug Use</label></td>
				<td><input name="<?php echo $field_prefix; ?>hus_drug_nt" id="<?php echo $field_prefix; ?>hus_drug_nt" type="text" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'hus_drug_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['hus_tobacco']['content'] && $pat_entries['hus_tobacco']['content'] != $dt{$field_prefix.'hus_tobacco'}) || ($pat_entries['hus_tobacco_nt']['content'] && strpos($dt{$field_prefix.'hus_tobacco_nt'},$pat_entries['hus_tobacco_nt']['content']) === false) || ($pat_entries['hus_drug']['content'] && $pat_entries['hus_drug']['content'] != $dt{$field_prefix.'hus_drug'}) || ($pat_entries['hus_drug_nt']['content'] && strpos($dt{$field_prefix.'hus_drug_nt'},$pat_entries['hus_drug_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_tobacco" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['hus_tobacco']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_tobacco_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['hus_tobacco_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td  colspan="2"class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_drug" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['hus_drug']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hus_drug_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['hus_drug_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

    </table>
    <div class="<?php echo $portal_mode ? 'bkkDottedB' : 'wmtDottedB'; ?>"></div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
      <tr>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>" style="width: 2%"><input name="<?php echo $field_prefix; ?>coital" id="<?php echo $field_prefix; ?>coital" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'coital'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>" style="width: 15%"><label for="<?php echo $field_prefix; ?>coital">Coital Frequency</label></td>
        <td><input name="<?php echo $field_prefix; ?>coital_nt" id="<?php echo $field_prefix; ?>coital_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'coital_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['coital']['content'] && $pat_entries['coital']['content'] != $dt{$field_prefix.'coital'}) || ($pat_entries['coital_nt']['content'] && strpos($dt{$field_prefix.'coital_nt'},$pat_entries['coital_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>coital" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['coital']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>coital_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['coital_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>dysmenorrhea" id="<?php echo $field_prefix; ?>dysmenorrhea" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'dysmenorrhea'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>"><label for="<?php echo $field_prefix; ?>dysmenorrhea">Dysmenorrhea</label></td>
        <td><input name="<?php echo $field_prefix; ?>dys_nt" id="<?php echo $field_prefix; ?>dys_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'dys_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['dysmenorrhea']['content'] && $pat_entries['dysmenorrhea']['content'] != $dt{$field_prefix.'dysmenorrhea'}) || ($pat_entries['dys_nt']['content'] && strpos($dt{$field_prefix.'dys_nt'},$pat_entries['dys_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>dysmenorrhea" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['dysmenorrhea']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>dys_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['dys_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>pelvic_pain" id="<?php echo $field_prefix; ?>pelvic_pain" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'pelvic_pain'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>"><label for="<?php echo $field_prefix; ?>pelvic_pain">Pelvic Pain</label></td>
        <td><input name="<?php echo $field_prefix; ?>pain_nt" id="<?php echo $field_prefix; ?>pain_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pain_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['pelvic_pain']['content'] && $pat_entries['pelvic_pain']['content'] != $dt{$field_prefix.'pelvic_pain'}) || ($pat_entries['pain_nt']['content'] && strpos($dt{$field_prefix.'pain_nt'},$pat_entries['pain_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pelvic_pain" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['pelvic_pain']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pain_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pain_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
    </table>

    <div class="<?php echo $portal_mode ? 'bkkDottedB' : 'DottedB'; ?>"></div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
      <tr>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>" style="width: 15%">Previous Workup:</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>" style="width: 2%"><input name="<?php echo $field_prefix; ?>prev_hsg" id="<?php echo $field_prefix; ?>prev_hsg" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_hsg'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" style="width: 15%"><label for="<?php echo $field_prefix; ?>prev_hsg">HSG Results</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_hsg_nt" id="<?php echo $field_prefix; ?>prev_hsg_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_hsg_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_hsg']['content'] && $pat_entries['prev_hsg']['content'] != $dt{$field_prefix.'prev_hsg'}) || ($pat_entries['prev_hsg_nt']['content'] && strpos($dt{$field_prefix.'prev_hsg_nt'},$pat_entries['prev_hsg_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_hsg" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_hsg']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_hsg_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_hsg_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>prev_semen" id="<?php echo $field_prefix; ?>prev_semen" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_semen'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>prev_semen">Semen Analysis</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_semen_nt" id="<?php echo $field_prefix; ?>prev_semen_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_semen_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_semen']['content'] && $pat_entries['prev_semen']['content'] != $dt{$field_prefix.'prev_semen'}) || ($pat_entries['prev_semen_nt']['content'] && strpos($dt{$field_prefix.'prev_semen_nt'},$pat_entries['prev_semen_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_semen" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_semen']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_semen_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_semen_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel'; ?>">Previous Assistance:</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>prev_clomid" id="<?php echo $field_prefix; ?>prev_clomid" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_clomid'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>prev_clomid">Clomid</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_clomid_nt" id="<?php echo $field_prefix; ?>prev_clomid_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_clomid_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_clomid']['content'] && $pat_entries['prev_clomid']['content'] != $dt{$field_prefix.'prev_clomid'}) || ($pat_entries['prev_clomid_nt']['content'] && strpos($dt{$field_prefix.'prev_clomid_nt'},$pat_entries['prev_clomid_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_clomid" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_clomid']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_clomid_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_clomid_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>prev_gnrh" id="<?php echo $field_prefix; ?>prev_gnrh" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_gnrh'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>prev_gnrh">GnRH Agonists</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_gnrh_nt" id="<?php echo $field_prefix; ?>prev_gnrh_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_gnrh_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_gnrh']['content'] && $pat_entries['prev_gnrh']['content'] != $dt{$field_prefix.'prev_gnrh'}) || ($pat_entries['prev_gnrh_nt']['content'] && strpos($dt{$field_prefix.'prev_gnrh_nt'},$pat_entries['prev_gnrh_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_gnrh" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_gnrh']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_gnrh_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_gnrh_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>prev_iui" id="<?php echo $field_prefix; ?>prev_iui" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_iui'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>prev_iui">IUI</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_iui_nt" id="<?php echo $field_prefix; ?>prev_iui_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_iui_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_iui']['content'] && $pat_entries['prev_iui']['content'] != $dt{$field_prefix.'prev_iui'}) || ($pat_entries['prev_iui_nt']['content'] && strpos($dt{$field_prefix.'prev_iui_nt'},$pat_entries['prev_iui_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_iui" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_iui']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_iui_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_iui_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td>&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkR' : 'wmtBodyR'; ?>"><input name="<?php echo $field_prefix; ?>prev_ivf" id="<?php echo $field_prefix; ?>prev_ivf" type="checkbox" value="1" <?php echo (($dt{$field_prefix.'prev_ivf'} == '1')?' checked ':''); ?> /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><label for="<?php echo $field_prefix; ?>prev_ivf">IVF</label></td>
        <td><input name="<?php echo $field_prefix; ?>prev_ivf_nt" id="<?php echo $field_prefix; ?>prev_ivf_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'prev_ivf_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td> 
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['prev_ivf']['content'] && $pat_entries['prev_ivf']['content'] != $dt{$field_prefix.'prev_ivf'}) || ($pat_entries['prev_ivf_nt']['content'] && strpos($dt{$field_prefix.'prev_ivf_nt'},$pat_entries['prev_ivf_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_ivf" onclick="AcceptPortalData(this.id);"><?php echo $pat_entries['prev_ivf']['content'] ? 'Checked' : 'Unchecked'; ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>prev_ivf_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['prev_ivf_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
    </table>
