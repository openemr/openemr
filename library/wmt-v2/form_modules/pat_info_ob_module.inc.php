<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$email_field = 'email';
if(!(isset($GLOBALS['wmt::use_email_direct'])))
	$GLOBALS['wmt::use_email_direct'] = FALSE;
if($GLOBALS['wmt::use_email_direct']) $email_field = 'email_direct';
$local_fields = array('DOB', 'age', 'race', 'status', 
	'occupation', 'wmt_education', 'language', 'ethnicity', 
	'wmt_partner_name', 'wmt_partner_ph', 'wmt_father_name', 'wmt_father_ph',
	'contact_relationship', 'phone_contact', 'street', 'city', 'state',
	'postal_code', 'phone_home', 'phone_biz', 'phone_cell', $email_field);
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp] = '';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
<table width="100%"  border="0" cellspacing="0" cellpadding="0">
  <tr>
    <td style="width: 50%">
    <table width="100%" border="0" cellspacing="0" cellpadding="3" style="border-right: solid 1px black">
      <tr>
        <td style="width: 25%" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Birth Date</span><input name="<?php echo $field_prefix; ?>DOB" id="<?php echo $field_prefix; ?>DOB" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" title="YYYY-MM-DD" value="<?php echo htmlspecialchars($dt{$field_prefix.'DOB'}, ENT_QUOTES, '', FALSE); ?>" onchange="CalcPatAge('<?php echo $field_prefix; ?>DOB', '<?php echo $field_prefix; ?>age')" /></td>
        <td style="width: 25%" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Age</span><input name="<?php echo $field_prefix; ?>age" id="<?php echo $field_prefix; ?>age" type"text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" readonly tabindex="-1" value="<?php echo htmlspecialchars($dt{$field_prefix.'age'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td style="width: 25%" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Race</span><select name="<?php echo $field_prefix; ?>race" id="<?php echo $field_prefix; ?>race" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>">
        	<?php ListSel($dt{$field_prefix.'race'}, 'race'); ?>
        </select></td>
        <td style="width: 25%" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Marital Status</span><select name="<?php echo $field_prefix; ?>status" id="<?php echo $field_prefix; ?>status" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>">
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
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Occupation</span><input name="<?php echo $field_prefix; ?>occupation" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'occupation'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      	<td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Education (Last Completed)</span><input name="<?php echo $field_prefix; ?>wmt_education" id="<?php echo $field_prefix; ?>wmt_education" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_education'}, ENT_QUOTES, '', FALSE); ?>" /></td>
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
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Language</span><select name="<?php echo $field_prefix; ?>language" id="<?php echo $field_prefix; ?>language" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>">
          <?php ListSel($dt{$field_prefix.'language'}, 'language'); ?>
        </select></td>
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Ethnicity</span><select name="<?php echo $field_prefix; ?>ethnicity" id="<?php echo $field_prefix; ?>ethnicity" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>">
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
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Husband/Domestic Partner</span><input name="<?php echo $field_prefix; ?>wmt_partner_name" id="<?php echo $field_prefix; ?>wmt_partner_name" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_partner_name'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Phone</span><input name="<?php echo $field_prefix; ?>wmt_partner_ph" id="<?php echo $field_prefix; ?>wmt_partner_ph" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_partner_ph'}, ENT_QUOTES, '', FALSE); ?>" /></td>
       </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['wmt_partner_name']['content'] && ($pat_entries['wmt_partner_name']['content'] != $dt{$field_prefix.'wmt_partner_name'}) || $pat_entries['wmt_partner_ph']['content'] && ($pat_entries['wmt_partner_ph']['content'] != $dt{$field_prefix.'wmt_partner_ph'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_home" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wmt_partner_name']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_biz" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wmt_partner_ph']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

       <tr>
         <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Father of Baby</span><input name="<?php echo $field_prefix; ?>wmt_father_name" id="<?php echo $field_prefix; ?>wmt_father_name" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_father_name'}, ENT_QUOTES, '', FALSE); ?>" /></td>
         <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Phone</span><input name="<?php echo $field_prefix; ?>wmt_father_ph" id="<?php echo $field_prefix; ?>wmt_father_ph" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'wmt_father_ph'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['wmt_father_name']['content'] && ($pat_entries['wmt_father_name']['content'] != $dt{$field_prefix.'wmt_father_name'}) || $pat_entries['wmt_father_ph']['content'] && ($pat_entries['wmt_father_ph']['content'] != $dt{$field_prefix.'wmt_father_ph'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_home" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wmt_father_name']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>father_ph" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wmt_father_ph']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

    </table></td>

    <td style="width: 50%">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td colspan="3" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Address</span><input name="<?php echo $field_prefix; ?>street" id="<?php echo $field_prefix; ?>street" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'street'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
      <tr>
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">City</span><input name="<?php echo $field_prefix; ?>city" id="<?php echo $field_prefix; ?>city" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'city'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">State</span><select name="<?php echo $field_prefix; ?>state" id="<?php echo $field_prefix; ?>state" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>">
        	<?php ListSel($dt{$field_prefix.'state'}, 'state'); ?>
        </select></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('street','city','state');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>street" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['street']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>city" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['city']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>state" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['state']['content'], 'state'), ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtBody4'; ?>">ZIP</span><input name="<?php echo $field_prefix; ?>postal_code" id="postal_code" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'postal_code'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Home Phone</span><input name="<?php echo $field_prefix; ?>phone_home" id="<?php echo $field_prefix; ?>phone_home" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_home'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Work Phone</span><input name="<?php echo $field_prefix; ?>phone_biz" id="<?php echo $field_prefix; ?>phone_biz" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_biz'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('postal_code','phone_home','phone_biz');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>postal_code" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['postal_code']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_home" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_home']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_biz" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_biz']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Insurance Carrier / Medicaid #</span><input name="tmp_carrier" id="tmp_carrier" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" disabled="disabled" tabindex="-1" value="<?php echo htmlspecialchars($patient->primary, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Policy #</span><input name="tmp_policy" id="tmp_policy" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" disabled="disabled" tabindex="-1" value="<?php echo htmlspecialchars($patient->primary_id, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
      <tr>
        <td colspan="2" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Emergency Contact</span><input name="<?php echo $field_prefix; ?>contact_relationship" id="<?php echo $field_prefix; ?>contact_relationship" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'contact_relationship'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><span class="<?php echo $portal_mode ? 'bkkBody4' : 'wmtBody4'; ?>">Emergency Phone</span><input name="<?php echo $field_prefix; ?>phone_contact" id="<?php echo $field_prefix; ?>phone_contact" type="text" class="<?php echo $portal_mode ? 'bkkFullInput2' : 'wmtFullInput2'; ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'phone_contact'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['contact_relationship']['content'] && ($pat_entries['contact_relationship']['content'] != $dt{$field_prefix.'contact_relationship'}) || $pat_entries['phone_contact']['content'] && ($pat_entries['phone_contact']['content'] != $dt{$field_prefix.'phone_contact'})) { 
			?>
			<tr class="wmtPortalData">
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>contact_relationship" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['contact_relationship']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>phone_contact" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['phone_contact']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

    </table></td>
  </tr>
</table>
