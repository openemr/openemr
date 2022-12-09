<?php 
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
if(!isset($fyi->fyi_well_nt)) $fyi->fyi_well_nt = '';
if(!isset($dt['fyi_well_nt'])) $dt['fyi_well_nt'] = $fyi->fyi_well_nt;
$local_fields = array( 'tmp_vital_timestamp', 'height', 'weight', 'bps', 'bpd',
	'pulse', 'BMI', 'BMI_status', 'last_chol', 'last_lipid', 'last_hepc', 
	'last_lipo', 'last_tri', 'last_urine_alb', 'last_hgba1c', 'last_ekg', 
	'last_pft', 'last_colon', 'last_fecal', 'last_barium', 'last_sigmoid', 
	'last_psa', 'last_rectal', 'last_glaucoma', 'last_db_screen', 'last_db_eye',
	'last_db_foot', 'last_db_dbsmt', 'last_mp', 'last_bone', 'last_mamm',
	'last_php', 'hpv', 'last_hpv', 'last_pap', 'HCG', 'age_men', 'pflow',
	'pflow_dur', 'pfreq', 'pfreq_days', 'last_dental', 'last_dental_nt',
	'last_hear', 'left_ear', 'right_ear', 'hear_nt', 'pat_blood_type',
	'pat_rh_factor', 'last_hgba1c_val', 'fyi_well_nt', 'mam_law'
);
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
	if(!isset($pat_entries[$tmp])) $pat_entries[$tmp] = $portal_data_layout;
}
$wellness_modules = LoadList('well_'.$frmdir);

foreach($wellness_modules as $wmod) {
	if(is_file("./$wmod")) {
		include("./$wmod");
	} else if(is_file($GLOBALS['srcdir']."/wmt-v2/form_modules/".$wmod)) {
		include($GLOBALS['srcdir']."/wmt-v2/form_modules/".$wmod);
	}
}
?>
<?php 
$include_vitals = checkSettingMode('wmt::wellness_vitals','',$frmdir);
if($include_vitals != '') {
?>
	<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Vitals&nbsp;</legend>
<?php
	$vitals_module = 'vitals_'.$include_vitals.'_module.inc.php';
	if(is_file("./$vitals_module")) {
		include("./$vitals_module");
	} else if(is_file($GLOBALS['srcdir']."/wmt-v2/form_modules/".$vitals_module)) {
		include($GLOBALS['srcdir']."/wmt-v2/form_modules/".$vitals_module);
	}
	echo '</fieldset>';
	echo "\n";
}
?>

<?php if(checkSettingMode('wmt::wellness_blood','',$frmdir)) { ?>
<!-- fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : ' wmtLabel'; ?>">&nbsp;Blood&nbsp;&amp;&nbsp;Urine Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Blood Type:</td>
			<td colspan="2"><select name="<?php echo $field_prefix; ?>pat_blood_type" id="<?php echo $field_prefix; ?>pat_blood_type" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 45px;">
				<?php ListSel($dt{$field_prefix.'pat_blood_type'},'Blood_Types'); ?>
			</select>
			&nbsp;&nbsp;<select name="<?php echo $field_prefix; ?>pat_rh_factor" id="<?php echo $field_prefix; ?>pat_rh_factor" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 65px;">
				<?php ListSel($dt{$field_prefix.'pat_rh_factor'},'RH_Factor'); ?>
			</select></td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Cholesterol Check:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_chol" id="<?php echo $field_prefix; ?>last_chol" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_chol'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_chol" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_chol", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_chol"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Hepatitis C Test:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_hepc" id="<?php echo $field_prefix; ?>last_hepc" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hepc'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hepc" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hepc", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_hepc"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('pat_blood_type','pat_rh_factor','last_chol','last_hepc');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="2"><span class="wmtBorderHighlight wmtBody" style="width: 45px; padding-right: 25px;" id="tmp_<?php echo $field_prefix; ?>pat_blood_type" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['pat_blood_type']['content'],'Blood_Types'), ENT_QUOTES, '', FALSE); ?></span>
				&nbsp;&nbsp;<span class="wmtBorderHighlight wmtBody" style="width: 45px; padding-right: 30px;" id="tmp_<?php echo $field_prefix; ?>pat_rh_factor" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['pat_rh_factor']['content'],'RH_Factor'), ENT_QUOTES, '', FALSE); ?></span></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_chol" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_chol']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_hepc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hepc']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Lipid Panel:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_lipid" id="<?php echo $field_prefix; ?>last_lipid" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_lipid'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_lipid" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_lipid", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_lipid"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Lipoprotein:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_lipo" id="<?php echo $field_prefix; ?>last_lipo" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_lipo'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_lipo" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_lipo", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_lipo"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Triglycerides:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_tri" id="<?php echo $field_prefix; ?>last_tri" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_tri'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_tri" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_tri", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_tri"});
				</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_lipid','last_lipo','last_tri');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_lipid" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_lipid']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_lipo" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_lipo']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_tri" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_tri']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Urine Micro Alb:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_urine_alb" id="<?php echo $field_prefix; ?>last_urine_alb" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_urine_alb'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_urine_alb" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_urine_alb", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_urine_alb"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last HgbA1c:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_hgba1c" id="<?php echo $field_prefix; ?>last_hgba1c" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hgba1c'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hgba1c" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hgba1c", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_hgba1c"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last HgbA1c Value:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_hgba1c_val" id="<?php echo $field_prefix; ?>last_hgba1c_val" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hgba1c_val'},ENT_QUOTES,'',FALSE); ?>" /></td>
			<td>&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_urine_alb','last_hgba1c','last_hgba1c_val');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_urine_alb" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_urine_alb']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_hgba1c" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hgba1c']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_hgba1c_val" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hgba1c_val']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset -->
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_cardio','',$frmdir)) { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Cardio&nbsp;&amp;&nbsp;Pulmonary Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last EKG:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_ekg" id="<?php echo $field_prefix; ?>last_ekg" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_ekg'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_ekg" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_ekg", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_ekg"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last PFT:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_pft" id="<?php echo $field_prefix; ?>last_pft" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pft'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_pft" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_pft", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_pft"});
				</script>
			<td style="width: 22%;">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>">&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_ekg','last_pft');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_ekg" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_ekg']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_pft" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_pft']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_colon','',$frmdir)) { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Colon&nbsp;<?php echo (($pat_sex == 'f')?"":"&amp;&nbsp;Prostate&nbsp;"); ?></legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Colonoscopy/Cologuard:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_colon" id="<?php echo $field_prefix; ?>last_colon" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_colon'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_colon" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_colon", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_colon"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Fecal Occult Blood Test:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_fecal" id="<?php echo $field_prefix; ?>last_fecal" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_fecal'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_fecal" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_fecal", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_fecal"});
				</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Barium Enema:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_barium" id="<?php echo $field_prefix; ?>last_barium" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_barium'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_barium" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_barium", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_barium"});
				</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_colon','last_fecal','last_barium');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_colon" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_colon']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_fecal" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_fecal']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_barium" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_barium']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Flexible Sigmoidoscopy:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_sigmoid" id="<?php echo $field_prefix; ?>last_sigmoid" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_sigmoid'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
		 	<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_sigmoid" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_sigmoid", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_sigmoid"});
				</script>
			<?php if($pat_sex == 'f') { ?>
			<?php } else { ?>
			<td class='<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>'>Last PSA:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_psa" id="<?php echo $field_prefix; ?>last_psa" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_psa'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_psa" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_psa", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_psa"});
				</script>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Rectal Exam:</td>
				<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_rectal" id="<?php echo $field_prefix; ?>last_rectal" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_rectal'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
				<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_rectal" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_rectal", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_rectal"});
				</script>
			<?php } ?>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			if($pat_sex == 'f') {
				$keys = array('last_sigmoid');
			} else {
				$keys = array('last_sigmoid','last_psa','last_rectal');
			}
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_sigmoid" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_sigmoid']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<?php if($pat_sex == 'f') { ?>
				<?php } else { ?>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_psa" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_psa']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_rectal" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_rectal']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
					}
				}
			}
			?>
	</table>
</fieldset>
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_hearing','',$frmdir)) { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : ' wmtLabel'; ?>">&nbsp;Hearing&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Hearing Test:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_hear" id="<?php echo $field_prefix; ?>last_hear" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hear'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hear" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hear", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_hear"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Left Ear:</td>
			<td><select name="<?php echo $field_prefix; ?>left_ear" id="<?php echo $field_prefix; ?>left_ear" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 95px;">
				<?php ListSel($dt{$field_prefix.'left_ear'},'PassFail'); ?>
			</select></td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Right Ear:</td>
			<td><select name="<?php echo $field_prefix; ?>right_ear" id="<?php echo $field_prefix; ?>right_ear" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" style="width: 95px;">
				<?php ListSel($dt{$field_prefix.'right_ear'},'PassFail'); ?>
			</select></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_hear','left_ear','right_ear');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_hear" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hear']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td><span class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>left_ear" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['left_ear']['content'],'PassFail'), ENT_QUOTES, '', FALSE); ?></span></td>
				<td>&nbsp;</td>
				<td><span class="wmtBorderHighlight wmtBody" style="width: 45px; padding-right: 30px;" id="tmp_<?php echo $field_prefix; ?>pat_rh_factor" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['right_ear']['content'],'PassFail'), ENT_QUOTES, '', FALSE); ?></span></td>
			</tr>
			<?php
				}
			}
			?>
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody bkkT' : 'wmtBody wmtT'; ?>">Hearing Notes:</td>
			<td colspan="6"><textarea name="<?php echo $field_prefix; ?>hear_nt" id="<?php echo $field_prefix; ?>hear_nt" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="2"><?php echo htmlspecialchars($dt{$field_prefix.'hear_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('hear_nt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="6" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hear_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['hear_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_dental','',$frmdir)) { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Dental&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Dental Exam:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_dental" id="<?php echo $field_prefix; ?>last_dental" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_dental'}, ENT_QUOTES, '', FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_dental" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<script type="text/javascript">
				Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_dental", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_dental"});
				</script>
			<td style="width: 22%;">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>">&nbsp;</td>
			<td style="width: 22%;">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>">&nbsp;</td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>">&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_dental');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_dental" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_dental']['content'], ENT_QUOTES, '', FALSE); ?></td>
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
			<td class="<?php echo ($portal_mode) ? 'bkkBody bkkT' : 'wmtBody wmtT'; ?>">Dental Exam Notes:</td>
			<td colspan="8"><textarea name="<?php echo $field_prefix; ?>last_dental_nt" id="<?php echo $field_prefix; ?>last_dental_nt" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="2"><?php echo htmlspecialchars($dt{$field_prefix.'last_dental_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_dental_nt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="8" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_dental_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_dental_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_diabetes','',$frmdir)) { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Diabetes Related&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Diabetes Screening:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_db_screen" id="<?php echo $field_prefix; ?>last_db_screen" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_screen'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_screen" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_screen", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_db_screen"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Diabetic Eye Exam:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_db_eye" id="<?php echo $field_prefix; ?>last_db_eye" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_eye'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_eye" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_eye", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_db_eye"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Diabetic Foot Exam:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_db_foot" id="<?php echo $field_prefix; ?>last_db_foot" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_foot'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_foot" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_foot", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_db_foot"});
			</script>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_db_screen','last_db_eye','last_db_foot');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_db_screen" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_screen']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_db_eye" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_eye']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_db_foot" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_foot']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Glaucoma Screening:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_glaucoma" id="<?php echo $field_prefix; ?>last_glaucoma" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_glaucoma'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_glaucoma" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_glaucoma", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_glaucoma"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Self-Management Training:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_db_dbsmt" id="<?php echo $field_prefix; ?>last_db_dbsmt" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_db_dbsmt'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_db_dbsmt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_db_dbsmt", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_db_dbsmt"});
			</script>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

		<?php
		if($pat_entries_exist && !$portal_mode) {
			$inc = false;
			$keys = array('last_glaucoma','last_db_dbsmt');
			foreach($keys as $key => $val) {
				if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
			}
			if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_glaucoma" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_glaucoma']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_db_dbsmt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_db_dbsmt']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>
	</table>
</fieldset>
<?php } ?>

<?php if(checkSettingMode('wmt::wellness_gyn','',$frmdir)) { ?>
<?php if($pat_sex == 'f') { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Gynecological&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">LMP:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_mp" id="<?php echo $field_prefix; ?>last_mp" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mp'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_mp" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_mp", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_mp"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Bone Density:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_bone" id="<?php echo $field_prefix; ?>last_bone" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_bone'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_bone" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_bone", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_bone"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 22%;">Last Mammogram:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_mamm" id="<?php echo $field_prefix; ?>last_mamm" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_mamm'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_mamm" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_mamm", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_mamm"});
			</script>
		</tr>
		<tr>
			<td colspan="6">&nbsp;</td>
			<td colspan="2">Dense Breast Mammogram Law Informed?
			<td class="wmtR"><select name="<?php echo $field_prefix; ?>mam_law" id="<?php echo $field_prefix; ?>mam_law" class="wmtInput"><?php echo ListSel($dt[$field_prefix.'mam_law'], 'YesNo'); ?></select></td>
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
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_mp" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_mp']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_bone" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_bone']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_mamm" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_mamm']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">HPV Vaccinated:</td>
			<td colspan="2"><select name="<?php echo $field_prefix; ?>hpv" id="<?php echo $field_prefix; ?>hpv" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>">
				<?php echo ListSel($dt[$field_prefix.'hpv'], 'Yes_No'); ?>
			</select></td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last HPV:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_hpv" id="<?php echo $field_prefix; ?>last_hpv" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_hpv'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_hpv" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>last_hpv", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>last_hpv"});
			</script>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last Pap Smear:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>last_pap" id="<?php echo $field_prefix; ?>last_pap" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'last_pap'},ENT_QUOTES,'',FALSE); ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
			<td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_<?php echo $field_prefix; ?>last_pap" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
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
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>hpv" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['hpv']['content'], 'Yes_No'), ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_hpv" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_hpv']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>last_pap" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['last_pap']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Last HCG Result:</td>
			<td colspan="2"><input name="<?php echo $field_prefix; ?>HCG" id="<?php echo $field_prefix; ?>HCG" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'HCG'},ENT_QUOTES,'',FALSE); ?>" /></td>
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
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>HCG" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['HCG']['content'], ENT_QUOTES, '', FALSE); ?></td>
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
			<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Periods:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="2">Age Menarche:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>age_men" id="<?php echo $field_prefix; ?>age_men" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'age_men'}, ENT_QUOTES, '', FALSE); ?>" /></td>
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
				<td colspan="3" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>age_men" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['age_men']['content'], ENT_QUOTES, '', FALSE); ?></td>
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
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Flow:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_heavy" type="radio" value="h" <?php echo (($dt{$field_prefix.'pflow'} == 'h')?' checked ':''); ?> /><label for="pflow_heavy">Heavy&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_light" type="radio" value="l" <?php echo (($dt{$field_prefix.'pflow'} == 'l')?' checked ':''); ?> /><label for="pflow_light">Light&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_normal" type="radio" value="n" <?php echo (($dt{$field_prefix.'pflow'} == 'n')?' checked ':''); ?> /><label for="pflow_normal">Normal&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="pflow_meno" type="radio" value="m" <?php echo (($dt{$field_prefix.'pflow'} == 'm')?' checked ':''); ?> /><label for="pflow_meno">Menopause&nbsp;</label>&nbsp;&nbsp;
				<input name="<?php echo $field_prefix; ?>pflow" id="<?php echo $field_prefix; ?>pflow" type="radio" value="x" <?php echo (($dt{$field_prefix.'pflow'} == 'x')?' checked ':''); ?> /><label for="pflow">None</label></td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>" colspan="2">Frequency:</td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">
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
				<td colspan="3" class="wmtBorderHighlight wmtBody" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pflow');"><span id="tmp_<?php echo $field_prefix; ?>pflow" style="display: none;"><?php echo $pat_entries['pflow']['content']; ?></span><b><?php echo $pflow_text; ?></b> is Selected</td>
				<td>&nbsp;</td>
				<td colspan="3" class="wmtBorderHighlight wmtBody" onclick="AcceptPortalData('tmp_<?php echo $field_prefix; ?>pfreq');"><span id="tmp_<?php echo $field_prefix; ?>pfreq" style="display: none;"><?php echo $pat_entries['pfreq']['content']; ?></span><b><?php echo $pfreq_text; ?></b> is Selected</td>
			</tr>
			<?php
				}
			}
			?>

		<tr>
			<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Duration:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>pflow_dur" id="<?php echo $field_prefix; ?>pflow_dur" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pflow_dur'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;&nbsp;days</td>
			<td class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>" colspan="2">Interval:</td>
			<td colspan="3"><input name="<?php echo $field_prefix; ?>pfreq_days" id="<?php echo $field_prefix; ?>pfreq_days" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'pfreq_days'}, ENT_QUOTES, '', FALSE); ?>" />&nbsp;&nbsp;days</td>
		</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['pflow_dur']['content'] && $pat_entries['pflow_dur']['content'] != $dt{$field_prefix.'pflow_dur'}) || ($pat_entries['pfreq_days']['content'] && $pat_entries['pfreq_days']['content'] != $dt{$field_prefix.'pfreq_days'})) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="3" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pflow_dur" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pflow_dur']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="2">&nbsp;</td>
				<td colspan="2" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>pfreq_days" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['pfreq_days']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

</fieldset>
<?php } ?>
<?php } ?>
	
<table width="100%" border="0" cellspacing="0" cellpadding="0">
<tr>
	<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Notes:</td>
</tr>
<tr>
	<td colspan="3"><textarea name="fyi_well_nt" id="fyi_well_nt" rows="4" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" ><?php echo htmlspecialchars($dt['fyi_well_nt'], ENT_QUOTES, '', FALSE); ?></textarea></td>
</tr>
</table>

	<?php
	if($pat_entries_exist && !$portal_mode) {
		if($pat_entries['fyi_well_nt']['content'] && (strpos($dt['fyi_well_nt']['content'],$pat_entries['fyi_well_nt']['content']) === false)) {
	?>
	<div class="wmtLabel wmtPortalData wmtL" style="margin: 6px;">Notes input by the patient via the portal:</div>
	<div class="wmtPortalData wmtBorderHighlight wmtBody wmtL" style="margin: 6px;" id="tmp_fyi_well_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_well_nt']['content'], ENT_QUOTES, '', FALSE); ?></div>
<?php
	}
}
?>
