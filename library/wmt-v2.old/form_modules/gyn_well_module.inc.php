<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;

$local_fields = array( 'last_mp', 'last_bone', 'last_mamm', 'mam_law',
	'hpv', 'last_hpv', 'last_pap', 'HCG', 'age_men', 'pflow', 'pfreq', 
	'pflow_dur', 'pfreq_days', 'bc_chc', 'bc', 'db_pap_hist_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
?>

<?php if($pat_sex == 'f') { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Gynecological&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtBody">LMP:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_mp" id="<?php echo $field_prefix; ?>last_mp" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mp'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_mp" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_mp", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_mp"});
			</script>
			<td>Last Bone Density:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_bone" id="<?php echo $field_prefix; ?>last_bone" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_bone'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_bone" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_bone", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_bone"});
			</script>
			<td>Last Mammogram:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_mamm" id="<?php echo $field_prefix; ?>last_mamm" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mamm'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_mamm" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_mamm", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_mamm"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_mp','last_bone','last_mamm');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_mp" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_mp']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_bone" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_bone']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_mamm" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_mamm']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
		<?php
			}
		}
		?>

		<tr>
			<td colspan="6">&nbsp;</td>
			<td colspan="2">Dense Breast Mammogram Law Informed?
			<td class="wmtR"><select name="<?php echo $field_prefix; ?>mam_law" id="<?php echo $field_prefix; ?>mam_law" class="wmtInput"><?php echo ListSel($dt[$field_prefix.'mam_law'], 'YesNo'); ?></select></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('mam_law');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>mam_law" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['mam_law']['content'], 'YesNo'), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
		<?php
			}
		}
		?>

		<tr>
			<td>HPV Vaccinated:</td>
			<td colspan="2"><select name="<?php echo $field_prefix; ?>hpv" id="<?php echo $field_prefix; ?>hpv" class="wmtInput">
				<?php echo ListSel($dt[$field_prefix.'hpv'], 'Yes_No'); ?>
			</select></td>
			<td class="wmtBody">Last HPV:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_hpv" id="<?php echo $field_prefix; ?>last_hpv" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hpv'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hpv" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hpv", ifFormat:"<?php echo $date_img_fmt; ?>", daFormat:"<?php echo $date_img_fmt; ?>", button:"img_<?php echo $field_prefix; ?>last_hpv"});
			</script>
			<td>Last Pap Smear:</td>
			<td class="wmtDateCell"><input name="<?php echo $field_prefix; ?>last_pap" id="<?php echo $field_prefix; ?>last_pap" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pap'},ENT_QUOTES); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="<?php echo $date_title_fmt; ?>" /></td>
			<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_pap" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_pap", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_pap"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('hpv','last_hpv','last_pap');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>hpv" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['hpv']['content'], 'Yes_No'), ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_hpv" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hpv']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>last_pap" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_pap']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td>History of Abn Pap:</td>
			<td colspan="8"><textarea name="db_pap_hist_nt" id="db_pap_hist_nt" rows="4" class="wmtFullInput" ><?php echo htmlspecialchars($dt['db_pap_hist_nt'], ENT_QUOTES); ?></textarea>
		</tr>

	<?php
	if($pat_entries_exist && !$portal_mode) {
		if($pat_entries['db_pap_hist_nt']['content'] && (strpos($dt['db_pap_hist_nt']['content'],$pat_entries['db_pap_hist_nt']['content']) === false)) {
	?>
		<tr class="wmtPortalData">
			<td>&nbsp;</td>
			<td class="wmtBorderHighlight" colspan="8" style="margin: 6px;" id="tmp_db_pap_hist_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['db_pap_hist_nt']['content'], ENT_QUOTES); ?></td>
		</tr>
<?php
		}
	}

?>


		<tr>
			<td>Last HCG Result:</td>
			<td colspan="2"><input name="<?php echo $field_prefix; ?>HCG" id="<?php echo $field_prefix; ?>HCG" class="wmtDateInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'HCG'},ENT_QUOTES); ?>" /></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('HCG');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>HCG" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['HCG']['content'], ENT_QUOTES); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="wmtBody">Periods:</td>
			<td class="wmtBody" colspan="2">Age Menarche:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>age_men" id="<?php echo $field_prefix; ?>age_men" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'age_men'}, ENT_QUOTES); ?>" /></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('age_men');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && (strpos($dt{$field_prefix.'age_men'},$pat_entries[$val]['content']) === false)) $inc= true;
			}
			if($inc) {
			?>
		<tr class="wmtPortalData">
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td colspan="3" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>age_men" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['age_men']['content'], ENT_QUOTES); ?></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="wmtBody">Flow:</td>
			<td class="wmtBody" colspan="3">
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_heavy" type="radio" value="h" <?php echo (($dt{$field_prefix.'pflow'} == 'h')?' checked ':''); ?> /><label for="pflow_heavy">Heavy&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_light" type="radio" value="l" <?php echo (($dt{$field_prefix.'pflow'} == 'l')?' checked ':''); ?> /><label for="pflow_light">Light&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_normal" type="radio" value="n" <?php echo (($dt{$field_prefix.'pflow'} == 'n')?' checked ':''); ?> /><label for="pflow_normal">Normal&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_meno" type="radio" value="m" <?php echo (($dt{$field_prefix.'pflow'} == 'm')?' checked ':''); ?> /><label for="pflow_meno">Menopause&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="<?php echo $field_prefix; ?>pflow" type="radio" value="x" <?php echo (($dt{$field_prefix.'pflow'} == 'x')?' checked ':''); ?> /><label for="pflow">None</label></td>
			<td class="wmtR" colspan="2">Frequency:</td>
			<td colspan="3">
				<input name="<?php echo $field_prefix; ?>pfreq" id="pfreq_reg" type="radio" value="r" <?php echo (($dt{$field_prefix.'pfreq'} == 'r')?' checked ':''); ?> /><label for="pfreq_reg">Regular&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pfreq" id="pfreq_irr" type="radio" value="i" <?php echo (($dt{$field_prefix.'pfreq'} == 'i')?' checked ':''); ?> /><label for="pfreq_irr">Irregular&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pfreq" id="<?php echo $field_prefix; ?>pfreq" type="radio" value="n" <?php echo (($dt{$field_prefix.'pfreq'} == 'n')?' checked ':''); ?> /><label for="pfreq_none">None</label></td>
			<td>&nbsp;</td>
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
			<td colspan="3" class="wmtBorderHighlight" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pflow');"><span id="tmp_<?php echo $field_prefix; ?>pflow" style="display: none;"><?php echo $pat_entries['pflow']['content']; ?></span><b><?php echo $pflow_text; ?></b> is Selected</td>
			<td>&nbsp;</td>
			<td colspan="3" class="wmtBorderHighlight" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pfreq');"><span id="tmp_<?php echo $field_prefix; ?>pfreq" style="display: none;"><?php echo $pat_entries['pfreq']['content']; ?></span><b><?php echo $pfreq_text; ?></b> is Selected</td>
		</tr>
		<?php
			}
		}
		?>

		<tr>
			<td>Duration:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>pflow_dur" id="<?php echo $field_prefix; ?>pflow_dur" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pflow_dur'}, ENT_QUOTES); ?>" />&nbsp;&nbsp;days</td>
			<td class="wmtR" colspan="2">Interval:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>pfreq_days" id="<?php echo $field_prefix; ?>pfreq_days" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pfreq_days'}, ENT_QUOTES); ?>" />&nbsp;&nbsp;days</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			if(($pat_entries['pflow_dur']['content'] && $pat_entries['pflow_dur']['content'] != $dt{$field_prefix.'pflow_dur'}) || ($pat_entries['pfreq_days']['content'] && $pat_entries['pfreq_days']['content'] != $dt{$field_prefix.'pfreq_days'})) {
		?>
		<tr class="wmtPortalData">
			<td>&nbsp;</td>
			<td colspan="3" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>pflow_dur" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pflow_dur']['content'], ENT_QUOTES); ?></td>
			<td colspan="2">&nbsp;</td>
			<td colspan="2" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>pfreq_days" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pfreq_days']['content'], ENT_QUOTES); ?></td>
		</tr>
		<?php
			}
		}
		?>

		<tr>
			<td>Birth Control:</td>
			<td><select name="<?php echo $field_prefix; ?>bc_chc" id="<?php echo $field_prefix; ?>bc_chc" class="wmtInput">
			<?php ListSel($dt{$field_prefix.'bc_chc'}, 'Birth_Control_Methods'); ?></select></td>
			<td colspan="4"><input name="<?php echo $field_prefix; ?>bc" id="<?php echo $field_prefix; ?>bc" class="wmtFullInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'bc'}, ENT_QUOTES); ?>" /></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			if($pat_entries['bc']['content'] && (strpos($dt{$field_prefix.'bc'},$pat_entries['bc']['content']) === false)) {
			?>
		<tr class="wmtPortalData">
			<td>&nbsp;</td>
			<td class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>bc_chc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['bc_chc']['content'],'Birth_Control_Methods'), ENT_QUOTES); ?></td>
			<td colspan="4" class="wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>bc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['bc']['content'], ENT_QUOTES); ?></td>
		</tr>
		<?php
			}
		}
		?>
	</table>
</fieldset>
<?php } ?>

