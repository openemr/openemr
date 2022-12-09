<?php
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($portal_mode)) $portal_mode = false;
$local_fields = array('pat_blood_type', 'pat_rh_factor', 'group_b_strep', 
	'latex_allergy', 'drug_allergy', 'last_mp', 'hpv', 'age_men', 'last_pap', 'pap_nt', 
	'pap_hist_nt', 'wellness_nt', 'pflow', 'pfreq', 'pflow_dur', 'pfreq_days');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="border-collapse: separate; border-spacing: 4px 2px;">
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Blood Type:</td>
				<td><select name="<?php echo $field_prefix; ?>pat_blood_type" id="<?php echo $field_prefix; ?>pat_blood_type" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 45px;">
					<?php ListSel($dt{$field_prefix.'pat_blood_type'},'Blood_Types'); ?>
				</select>
				&nbsp;&nbsp;<select name="<?php echo $field_prefix; ?>pat_rh_factor" id="<?php echo $field_prefix; ?>pat_rh_factor" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 45px;">
					<?php ListSel($dt{$field_prefix.'pat_rh_factor'},'RH_Factor'); ?>
				</select></td>
				<td>&nbsp;</td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody' ;?>">Group B Strep:</td>
				<td><select name="<?php echo $field_prefix; ?>group_b_strep" id="<?php echo $field_prefix; ?>group_p_strep" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput' ;?>">
				<?php ListSel($dt{$field_prefix.'group_b_strep'}, 'PosNeg'); ?>
				</select></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkLabel' : 'wmtLabel' ;?>" colspan="2">Gynecological History:</td>
			</tr>
			<tr>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody' ;?>" style="width: 120px;">LMP:</td>
        <td class="<?php echo $portal_mode ? 'bkkDateCell' : 'wmtDateCell' ;?>"><input name="<?php echo $field_prefix; ?>last_mp" id="<?php echo $field_prefix; ?>last_mp" class="<?php echo $portal_mode ? 'bkkDateInput' : 'wmtDateInput' ;?>" type="text" value="<?php echo $dt{$field_prefix.'last_mp'}; ?>" onkeyup="datekeyup(this, mypcc);" onblur="dateblur(this, mypcc);" title="YYYY-MM-DD" /></td>
        <td style="width: 50px;"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_mp_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_mp", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_mp_dt"});
				</script>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody' ;?>">HPV Vaccinated:</td>
				<td><select name="<?php echo $field_prefix; ?>hpv" id="<?php echo $field_prefix; ?>hpv" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput' ;?>">
				<?php ListSel($dt{$field_prefix.'hpv'}, 'Yes_No'); ?>
				</select></td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody' ;?>"><?php echo $portal_mode ? 'Age at First Period' : 'Menarche'; ?>:</td>
        <td><input name="<?php echo $field_prefix; ?>age_men" id="<?php echo $field_prefix; ?>age_men" class="<?php echo $portal_mode ? 'bkkDateInput' : 'wmtDateInput' ;?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'age_men'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('last_mp','hpv','age_men');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_mp" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_mp']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hpv" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['hpv']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>age_men" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['age_men']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Last Pap:</td>
        <td class="<?php echo $portal_mode ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_pap" id="<?php echo $field_prefix; ?>last_pap" class="<?php echo $portal_mode ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo $dt{$field_prefix.'last_pap'}; ?>" onkeyup="datekeyup(this, mypcc);" onblur="dateblur(this, mypcc);" title="YYYY-MM-DD" /></td>
        <td><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_pap_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_pap", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_pap_dt"});
				</script>
        <td colspan="4"><input name="<?php echo $field_prefix; ?>pap_nt" id="<?php echo $field_prefix; ?>pap_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pap_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['last_pap']['content'] && $pat_entries['last_pap']['content'] != $dt{$field_prefix.'last_pap'}) || ($pat_entries['pap_nt']['content'] && $pat_entries['pap_nt']['content'] != $dt{$field_prefix.'pap_nt'})) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_pap" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_pap']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pap_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pap_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">History of Abnormal Pap</td>
        <td colspan="4"><textarea name="<?php echo $field_prefix; ?>pap_hist_nt" id="<?php echo $field_prefix; ?>pap_hist_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'pap_hist_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['pap_hist_nt']['content'] && 
				($pat_entries['pap_hist_nt']['content'] != $dt{$field_prefix.'pap_hist_nt'})) {
			?>
			<tr class="wmtPortalData">
				<td colspan="3">&nbsp;</td>
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pap_hist_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pap_hist_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

      <tr>
        <td colspan="4" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>"><b>Periods:</b>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Flow:&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pflow" id="pflow_heavy" type="radio" value="h" <?php echo (($dt{$field_prefix.'pflow'} == 'h')?' checked ':''); ?> />Heavy&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pflow" id="pflow_light" type="radio" value="l" <?php echo (($dt{$field_prefix.'pflow'} == 'l')?' checked ':''); ?> />Light&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pflow" id="pflow_normal" type="radio" value="n" <?php echo (($dt{$field_prefix.'pflow'} == 'n')?' checked ':''); ?> />Normal&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pflow" id="<?php echo $field_prefix; ?>pflow" type="radio" value="x" <?php echo (($dt{$field_prefix.'pflow'} == 'x')?' checked ':''); ?> />None&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pflow" id="pflow_meno" type="radio" value="m" <?php echo (($dt{$field_prefix.'pflow'} == 'm')?' checked ':''); ?> />Menopause</td>
        <td colspan="3" class="<?php echo $portal_mode ? 'bkkBody bkk' : 'wmtBody'; ?>">Frequency:
					<input name="<?php echo $field_prefix; ?>pfreq" id="pfreq_reg" type="radio" value="r" <?php echo (($dt{$field_prefix.'pfreq'} == 'r')?' checked ':''); ?> />Regular&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pfreq" id="pfreq_irr" type="radio" value="i" <?php echo (($dt{$field_prefix.'pfreq'} == 'i')?' checked ':''); ?> />Irregular&nbsp;&nbsp;&nbsp;
					<input name="<?php echo $field_prefix; ?>pfreq" id="<?php echo $field_prefix; ?>pfreq" type="radio" value="n" <?php echo (($dt{$field_prefix.'pfreq'} == 'n')?' checked ':''); ?> />None</td>
      </tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['pflow']['content'] && $pat_entries['pflow']['content'] != $dt{$field_prefix.'pflow'}) || ($pat_entries['pfreq']['content'] && $pat_entries['pfreq']['content'] != $dt{$field_prefix.'pfreq'})) {
				$pflow_text = 'No Selection';
				$pfreq_text = 'No Selection';
				if($pat_entries['pflow']['content'] == 'h') $pflow_text = 'Heavy';
				if($pat_entries['pflow']['content'] == 'l') $pflow_text = 'Light';
				if($pat_entries['pflow']['content'] == 'n') $pflow_text = 'Normal';
				if($pat_entries['pflow']['content'] == 'x') $pflow_text = 'None';
				if($pat_entries['pflow']['content'] == 'm') $pflow_text = 'Menopause';
				if($pat_entries['pfreq']['content'] == 'r') $pfreq_text = 'Regular';
				if($pat_entries['pfreq']['content'] == 'i') $pfreq_text = 'Irregular';
				if($pat_entries['pfreq']['content'] == 'n') $pfreq_text = 'None';
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="3" class="wmtBorderHighlight wmtBody" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pflow');"><span id="tmp_<?php echo $field_prefix; ?>pflow" style="display: none;"><?php echo $pat_entries['pflow']['content']; ?></span><b><?php echo $pflow_text; ?></b> is Selected</td>
				<td colspan="3" class="wmtBorderHighlight wmtBody" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pfreq');"><span id="tmp_<?php echo $field_prefix; ?>pfreq" style="display: none;"><?php echo $pat_entries['pfreq']['content']; ?></span><b><?php echo $pfreq_text; ?></b> is Selected</td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody bkkR' : 'wmtBodyR'; ?>">&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Duration:&nbsp;
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" colspan="2"><input name="<?php echo $field_prefix; ?>pflow_dur" id="<?php echo $field_prefix; ?>pflow_dur" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" style="width: 140px;" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pflow_dur'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;days</td>
				<td class="<?php echo $portal_mode ? 'bkkBody bkkR' : 'wmtBodyR'; ?>">Interval:&nbsp;&nbsp;&nbsp;&nbsp;</td>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" colspan="2"><input name="<?php echo $field_prefix; ?>pfreq_days" id="<?php echo $field_prefix; ?>pfreq_days" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>" style="width: 140px;" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pfreq_days'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;days</td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['pflow_dur']['content'] && $pat_entries['pflow_dur']['content'] != $dt{$field_prefix.'pflow_dur'}) || ($pat_entries['pfreq_days']['content'] && $pat_entries['pfreq_days']['content'] != $dt{$field_prefix.'pfreq_days'})) {
			?>
			<tr class="wmtPortalData">
				<td colspan="2">&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pflow_dur" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pflow_dur']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pfreq_days" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pfreq_days']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Other Notes:</td>
			</tr>
			<tr>
				<td colspan="7"><textarea name="<?php echo $field_prefix; ?>wellness_nt" id="<?php echo $field_prefix; ?>wellness_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'wellness_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['wellness_nt']['content'] && 
				($pat_entries['wellness_nt']['content'] != $dt{$field_prefix.'wellness_nt'})) {
			?>
			<tr class="wmtPortalData">
				<td colspan="7" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>wellness_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['wellness_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
    </table>
<?php
if($review = checkSettingMode('wmt::wellness_review','',$frmdir)) {
	$caller = 'wellness';
	$chk_title = 'Wellness';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.inc.php');
}
?>
