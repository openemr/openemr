<?php
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($dt[$field_prefix.'curr_obgyn'])) $dt[$field_prefix.'curr_obgyn'] = '';
if(!isset($dt[$field_prefix.'trying'])) $dt[$field_prefix.'trying'] = '';
// All have notes
$local_fields = array('ectopic', 'ivf', 'unexplained', 'nk_cell');
foreach($local_fields as $tmp)  {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($dt[$field_prefix.$tmp.'_nt'])) $dt[$field_prefix.$tmp.'_nt']='';
}
?>
    <table width="100%" border="0" cellspacing="2" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
      <tr>
        <td style="width: 25%;" class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>">Current Ob/Gyn Doctor:</td>
        <td style="width: 25%;"><input name="<?php echo $field_prefix; ?>curr_obgyn" id="<?php echo $field_prefix; ?>curr_obgyn" type="text" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" value="<?php echo htmlspecialchars($dt{$field_prefix.'curr_obgyn'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td style="width: 25%;" class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>">How long have you been trying to conceive?</td>
        <td style="width: 25%;"><input name="<?php echo $field_prefix; ?>trying" id="<?php echo $field_prefix; ?>trying" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'trying'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['curr_obgyn']['content'] && strpos($dt{$field_prefix.'curr_obgyn'}, $pat_entries['curr_obgyn']['content']) === false) || ($pat_entries['trying']['content'] && strpos($dt{$field_prefix.'trying'}, $pat_entries['trying']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>curr_obgyn" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['curr_obgyn']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>trying" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['trying']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
		</table>

    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
			<tr>
				<td colspan="3" class="<?php echo (($portal_mode)?'bkkLabel':'wmtLabel'); ?>">Have you had any of the following:</td>
			<?php if($portal_mode) { ?>
				<td class="bkkLabel">If yes, please explain:</td>
			<?php } ?>
			</tr>
      <tr>
				<td style="width: 12px;">&nbsp;</td>
        <td style="width: 30%;" class="<?php echo (($portal_mode)?'bkkBody':'wmtBody'); ?>">Ectopic Pregnancy:</td>
        <td style="width: 40px;"><select name="<?php echo $field_prefix; ?>ectopic" id="<?php echo $field_prefix; ?>ectopic" class="<?php echo (($portal_mode)?'bkkInput':'wmtInput'); ?>">
          <?php ListSel($dt{$field_prefix.'ectopic'},'Yes_No'); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>ectopic_nt" id="<?php echo $field_prefix; ?>ectopic_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'ectopic_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['ectopic']['content'] && $pat_entries['ectopic']['content'] != $dt{$field_prefix.'ectopic'}) || ($pat_entries['ectopic_nt']['content'] && strpos($dt{$field_prefix.'ectopic_nt'}, $pat_entries['ectopic_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2">&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>ectopic" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['ectopic']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>ectopic_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['ectopic_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
				<td style="width: 12px;">&nbsp;</td>
        <td class="<?php echo (($portal_mode)?'bkkBody':'wmtBody'); ?>">IVF Failure:</td>
        <td><select name="<?php echo $field_prefix; ?>ivf" id="<?php echo $field_prefix; ?>ivf" class="<?php echo (($portal_mode)?'bkkInput':'wmtInput'); ?>">
          <?php ListSel($dt{$field_prefix.'ivf'},'Yes_No'); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>ivf_nt" id="<?php echo $field_prefix; ?>ivf_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'ivf_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['ivf']['content'] && $pat_entries['ivf_nt']['content'] != $dt{$field_prefix.'ivf'}) || ($pat_entries['ivf_nt']['content'] && strpos($dt{$field_prefix.'ivf_nt'}, $pat_entries['ivf_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2">&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>ivf" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['ivf']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>ivf_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['ivf_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
				<td style="width: 12px;">&nbsp;</td>
        <td class="<?php echo (($portal_mode)?'bkkBody':'wmtBody'); ?>">Diagnosed with "Unexplained Infertility":</td>
        <td><select name="<?php echo $field_prefix; ?>unexplained" id="<?php echo $field_prefix; ?>unexplained" class="<?php echo (($portal_mode)?'bkkInput':'wmtInput'); ?>">
          <?php ListSel($dt{$field_prefix.'unexplained'},'Yes_No'); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>unexplained_nt" id="<?php echo $field_prefix; ?>unexplained_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'unexplained_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['unexplained']['content'] && $pat_entries['unexplained']['content'] != $dt{$field_prefix.'unexplained'}) || ($pat_entries['unexplained_nt']['content'] && strpos($dt{$field_prefix.'unexplained_nt'}, $pat_entries['unexplained_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2">&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>unexplained" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['unexplained']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>unexplained_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['unexplained_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
				<td style="width: 12px;">&nbsp;</td>
        <td class="<?php echo (($portal_mode)?'bkkBody':'wmtBody'); ?>">Tested for Natural Killer (NK) Cell:</td>
        <td><select name="<?php echo $field_prefix; ?>nk_cell" id="<?php echo $field_prefix; ?>nk_cell" class="<?php echo (($portal_mode)?'bkkInput':'wmtInput'); ?>">
          <?php ListSel($dt{$field_prefix.'nk_cell'},'Yes_No'); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>nk_cell_nt" id="<?php echo $field_prefix; ?>nk_cell_nt" class="<?php echo (($portal_mode)?'bkkFullInput':'wmtFullInput'); ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'nk_cell_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>
			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['nk_cell']['content'] && $pat_entries['nk_cell']['content'] != $dt{$field_prefix.'nk_cell'}) || ($pat_entries['nk_cell_nt']['content'] && strpos($dt{$field_prefix.'nk_cell_nt'}, $pat_entries['nk_cell_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2">&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>nk_cell" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['nk_cell']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>nk_cell_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['nk_cell_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
				 
    </table>
<?php ?>
