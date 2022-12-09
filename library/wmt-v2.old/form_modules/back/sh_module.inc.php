<?php 
// This module now has some spaghetti in-line javascript, there are 
// two different versions of 'ACE' extended alcohol questions, and another
// 'AUDIT' alcohol questionnaire and the smoking cessation qustions.
// These can all be toggled on and off in the form with user settings.
if(!isset($dt['tmp_cessation_disp_mode'])) $dt['tmp_cessation_disp_mode'] = 'none';
if(!isset($dt['tmp_ace_disp_mode'])) $dt['tmp_ace_disp_mode'] = 'none';
if(!isset($dt['tmp_t_ace_disp_mode'])) $dt['tmp_t_ace_disp_mode'] = 'none';
if(!isset($dt['tmp_audit_disp_mode'])) $dt['tmp_audit_disp_mode'] = 'none';
if(!isset($use_lifestyle)) $use_lifestyle = checkSettingMode('wmt::use_lifestyle','',$frmdir);
if(!isset($use_cessation)) $use_cessation = checkSettingMode('wmt::smoking_cessation','',$frmdir);
if(!isset($use_ace)) $use_ace = checkSettingMode('wmt::alcohol_ace','',$frmdir);
if(!isset($use_t_ace)) $use_t_ace = checkSettingMode('wmt::alcohol_t_ace','',$frmdir);
if(!isset($use_audit)) $use_audit = checkSettingMode('wmt::alcohol_audit','',$frmdir);
if(!isset($use_coffee)) $use_coffee = checkSettingMode('wmt::sh_coffee','',$frmdir);
if(!isset($expanded_sh)) $expanded_sh = checkSettingMode('wmt::sh_expanded','',$frmdir);
if(!isset($dt['tmp_expanded_sh_disp_mode'])) 
										$dt['tmp_expanded_sh_disp_mode'] = 'block';
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($first_pass)) $first_pass = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
$cessation_match_statuses = '~1~2~5~15~16~';
$local_fields = array('sex_active', 'sex_act_nt', 'bc', 'bc_chc', 
	'smoking_status', 'smoking', 'smoking_dt', 'alcohol', 'alcohol_note', 
	'alcohol_dt', 'drug_use', 'drug_note', 'drug_dt', 'coffee_use', 
	'coffee_note', 'coffee_dt', 'fyi_sh_nt', 'sex_nt');
if($expanded_sh) {
	include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');	
	$sh_questions = LoadList('Social_History_Questions');
	$sql = 'SELECT * FROM wmt_sh_data WHERE pid=? AND form_name=? '.
						'AND field_name=?';
	foreach($sh_questions as $q) {
		if($q['codes'] != '') $local_fields[] = 'sh_ex_'.$q['option_id'].'_chc';
		$local_fields[] = 'sh_ex_'.$q['option_id'].'_nt';
		if($first_pass) {
			if($q['codes'] != '') {
				$frow = sqlQuery($sql,array($pid,$frmdir,$q['option_id'].'_chc'));
				if(!isset($dt[$field_prefix.'sh_ex_'.$q['option_id'].'_chc'])) 
					$dt[$field_prefix.'sh_ex_'.$q['option_id'].'_chc'] = $frow{'content'};
			}
			$frow = sqlQuery($sql,array($pid,$frmdir,$q['option_id'].'_nt'));
			if(!isset($dt[$field_prefix.'sh_ex_'.$q['option_id'].'_nt'])) 
				$dt[$field_prefix.'sh_ex_'.$q['option_id'].'_nt'] = $frow{'content'};
		}
	}
}
if($use_lifestyle) {
	$flds = sqlListFields('wmt_lifestyle');
	$flds = array_slice($flds, 10);
	foreach($flds as $fld) { $local_fields[] = $fld; }
}
foreach($local_fields as $fld) {
	if(!isset($dt[$field_prefix.$fld])) $dt[$field_prefix.$fld] = '';
	if(!isset($pat_entries[$fld])) $pat_entries[$fld] = $portal_data_layout;
}

$smk_exists= $ace_exists= $t_ace_exists= $audit_exists= $alc_exists = false;
if($first_pass && $use_lifestyle) {
	// SET THE INITIAL SMOKING CESSATION DISPLAY - IF SMOKING STATUS OR DATA
	if(strpos($cessation_match_statuses, '~'.$dt[$field_prefix.'smoking_status'].'~') !== false) $smk_exists = true;
	if($dt[$field_prefix.'alcohol'] == 'currentalcohol') $alc_exists = true;
	if(!$smk_exists || !$alc_exists) {
		// SCAN FOR EXISTING DATA
		foreach($dt as $key => $val) {
			if(substr($key,0,9) == $field_prefix.'lf_sc_') {
				if($val) $smk_exists = true;	
			}
			if(substr($key,0,12) == substr($field_prefix.'lf_t_ace_',0,12) && $val) $t_ace_exists = true;	
			if(substr($key,0,10) == substr($field_prefix.'lf_ace_',0,10) && $val) $ace_exists = true;	
			if(substr($key,0,10) == substr($field_prefix.'lf_alc_',0,10) && $val) $audit_exists = true;	
		}
	}
	if($smk_exists) $dt['tmp_cessation_disp_mode'] = 'block';
	if((($ace_exists || $alc_exists) && $use_ace) || $use_ace == 'default') $dt['tmp_ace_disp_mode'] = 'block';
	if((($t_ace_exists || $alc_exists) && $use_t_ace) || $use_t_ace == 'default') $dt['tmp_t_ace_disp_mode'] = 'block';
	if((($audit_exists || $alc_exists) && $use_audit) || $use_audit == 'default') $dt['tmp_audit_disp_mode'] = 'block';
}
// echo "ACE Display Mode: ",$dt['tmp_ace_disp_mode'],"<br>\n";
// echo "T-ACE Display Mode: ",$dt['tmp_t_ace_disp_mode'],"<br>\n";
// echo "AUDIT Display Mode: ",$dt['tmp_audit_disp_mode'],"<br>\n";
?>
<table width="100%" border="0" cellspacing="0" cellpadding="3">
  <tr>
    <td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 120px;">Do you smoke?</td>
    <td style="width: 220px;"><select name="<?php echo $field_prefix; ?>smoking_status" id="<?php echo $field_prefix; ?>smoking_status" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" <?php echo (($use_cessation && $use_lifestyle) || $smk_exists) ? 'onchange="ToggleSmokeCessation(this);"' : ''; ?> >
<!-- SELECTION OF 'option_id' 1, 2, 5, 15 or 16 
			SHOULD TRIGGER THE LARGER SMOKING CESSATION QUESTIONNAIRE-->
			<?php ListSel($dt{$field_prefix.'smoking_status'}, 'smoking_status'); ?>
		</select></td>
		<script type="text/javascript">
			function ToggleSmokeCessation(source) {
				<?php if(!$use_cessation) { ?>
				return true;
				<?php } ?>
				var opt_match = '~1~2~5~15~16~';
				if(opt_match.indexOf('~'+source.value+'~') != -1) {
					document.getElementById('smoke_cessation').style.display='block';
					document.getElementById('tmp_cessation_check').checked = true; 
					document.getElementById('tmp_cessation_disp_mode').value='block;'
				}
			}
		</script>
    <td><input name="<?php echo $field_prefix; ?>smoking" id="<?php echo $field_prefix; ?>smoking" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'smoking'}, ENT_QUOTES, '', FALSE); ?>" /></td>
    <td class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>" style="width: 100px;">Dt Quit (if appl):</td>
    <td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>smoking_dt" id="<?php echo $field_prefix; ?>smoking_dt" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo $dt{$field_prefix.'smoking_dt'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
    <td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" align="absbottom" width="20" height="20" id="img_<?php echo $field_prefix; ?>smoking_dt" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date"></td>
		<script type="text/javascript">
			Calendar.setup({inputField:"<?php echo $field_prefix; ?>smoking_dt", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>smoking_dt"});
		</script>
  </tr>
 
	<?php
	if($pat_entries_exist && !$portal_mode) {
		$inc = false;
		$keys = array('smoking_status','smoking','smoking_dt');
		foreach($keys as $key => $val) {
			if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
		}
		if($inc) {
	?>
	<tr class="wmtPortalData">
		<td>&nbsp;</td>
		<td class="wmtBody wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>smoking_status" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['smoking_status']['content'],'smoking_status'), ENT_QUOTES, '', FALSE); ?></td>
		<td class="wmtBody wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>smoking" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['smoking']['content'], ENT_QUOTES, '', FALSE); ?></td>
		<td>&nbsp;</td>
		<td class="wmtBody wmtBorderHighlight" id="tmp_<?php echo $field_prefix; ?>smoking_dt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['smoking_dt']['content'], ENT_QUOTES, '', FALSE); ?></td>
	</tr>
	<?php
		}
	}
	?>

	<!-- SMOKING CESSATION QUESTIONS IF CONFIGURED TO SHOW -->
	<?php if($use_cessation && $use_lifestyle) { ?>
	<tr>
		<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">&nbsp;&nbsp;&nbsp;
			<input name="tmp_cessation_check" id="tmp_cessation_check" type="checkbox" value="show" <?php echo ($dt['tmp_cessation_disp_mode'] == 'none') ? '' : 'checked'; ?> onchange="ToggleDivDisplay('smoke_cessation', 'tmp_cessation_check', 'tmp_cessation_disp_mode');" />
			&nbsp;&nbsp;<label for="tmp_cessation_check"><i>Smoking Cessation Questionnaire</i></label>
		</td>
	</tr>
</table>

		<div id="smoke_cessation" style="width: 100%; display: <?php echo $dt['tmp_cessation_disp_mode']; ?>;">
			<fieldset style="margin: 6px; padding: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Smoking Cessation&nbsp;</legend>
			<table width="100%" border="0" cellspacing="0" cellpadding="3">
				<tr>
					<td style="width: 50%;" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Are you aware of the risks of smoking to your health and well being?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_risks" id="<?php echo $field_prefix; ?>lf_sc_risks" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_risks'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you tried to quit smoking?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_tried" id="<?php echo $field_prefix; ?>lf_sc_tried" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_tried'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">What is the longest period you have gone without smoking?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_wo" id="<?php echo $field_prefix; ?>lf_sc_wo" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_wo'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Why did you start smoking again?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_why_start" id="<?php echo $field_prefix; ?>lf_sc_why_start" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_why_start'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you tried a structured treament plan?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_treat" id="<?php echo $field_prefix; ?>lf_sc_treat" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_treat'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you tried patches and/or gum?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_patch" id="<?php echo $field_prefix; ?>lf_sc_patch" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_patch'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">What is the primary reason you feel you have not been able to quit smoking?</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_reason" id="<?php echo $field_prefix; ?>lf_sc_reason" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_reason'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
				<tr>
					<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Patient referred to:</td>
					<td><input name="<?php echo $field_prefix; ?>lf_sc_referred" id="<?php echo $field_prefix; ?>lf_sc_referred" type="text" class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtFullInput'; ?>" value="<?php echo htmlspecialchars($dt[$field_prefix.'lf_sc_referred'], ENT_QUOTES, '', FALSE); ?>" /></td>
				</tr>
			</table>
			</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php } ?>

      <tr>
        <td style="width: 120px;" class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Alcohol use</td>
        <td style="width: 220px;"><select name="<?php echo $field_prefix; ?>alcohol" id="<?php echo $field_prefix; ?>alcohol" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>" <?php echo ($use_lifestyle && ($use_ace || $use_t_ace || $use_audit)) ? 'onchange="ToggleAlcoholAudit(this);"' : ''; ?> >
          <?php AlcoholUseListSel($dt{$field_prefix.'alcohol'}); ?>
        </select></td>
					<script type="text/javascript">
					function ToggleAlcoholAudit(source) {
						var opt_match = 'currentalcohol';
						if(opt_match.indexOf(source.value) != -1) {
							<?php if($use_ace == 'default') { ?>
							document.getElementById('alcohol_ace').style.display = 'block';
							document.getElementById('tmp_ace_check').checked = true; 
							document.getElementById('tmp_ace_disp_mode').value = 'block;'
							<?php } ?>
							<?php if($use_t_ace == 'default') { ?>
							document.getElementById('alcohol_t_ace').style.display = 'block';
							document.getElementById('tmp_t_ace_check').checked = true; 
							document.getElementById('tmp_t_ace_disp_mode').value = 'block;'
							<?php } ?>
							<?php if($use_audit == 'default') { ?>
							document.getElementById('alcohol_audit').style.display = 'block';
							document.getElementById('tmp_audit_check').checked = true; 
							document.getElementById('tmp_audit_disp_mode').value = 'block;'
							<?php } ?>
						}
					}
					</script>
        <td><input name="<?php echo $field_prefix; ?>alcohol_note" id="<?php echo $field_prefix; ?>alcohol_note" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'alcohol_note'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td style="width: 100px;" class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>">Dt Quit (if appl):</td>
        <td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>alcohol_dt" id="<?php echo $field_prefix; ?>alcohol_dt" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo $dt{$field_prefix.'alcohol_dt'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" align="absbottom" width="20" height="20" id="img_<?php echo $field_prefix; ?>alcohol_dt" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date"></td>
					<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>alcohol_dt", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>alcohol_dt"});
					</script>
      </tr>
 
			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('alcohol','alcohol_note','alcohol_dt');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>alcohol" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['alcohol']['content'],'Alcohol_Use_Status'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>alcohol_note" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['alcohol_note']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>alcohol_dt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['alcohol_dt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<!-- ALCOHOL 'ACE' QUESTIONNAIRE IF CONFIGURED -->
			<?php if($use_ace && $use_lifestyle) { ?>
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">&nbsp;&nbsp;&nbsp;
					<input name="tmp_ace_check" id="tmp_ace_check" type="checkbox" value="show" <?php echo ($dt['tmp_ace_disp_mode'] == 'none') ? '' : 'checked'; ?> onchange="ToggleDivDisplay('alcohol_ace', 'tmp_ace_check', 'tmp_ace_disp_mode');" />
					&nbsp;&nbsp;<label for="tmp_ace_check"><i>ACE Questionnaire</i></label>
				</td>
			</tr>
		</table>

		<div id="alcohol_ace" style="width: 100%; display: <?php echo $dt['tmp_ace_disp_mode']; ?>;">
				<fieldset style="margin: 6px; padding: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;ACE Questionnaire&nbsp;</legend>
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
							<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you ever felt you should cut down on your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_ace_t" id="<?php echo $field_prefix; ?>lf_ace_t" class="wmtInput" onchange="total_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_ace_t'},'Yes_No'); ?></select></td>
						</tr>
						<tr>
							<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have people annoyed you by crticizing your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_ace_a" id="<?php echo $field_prefix; ?>lf_ace_a" class="wmtInput" onchange="total_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_ace_a'},'Yes_No'); ?></select></td>
						</tr>
						<tr>
							<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you ever felt bad or guilty about your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_ace_c" id="<?php echo $field_prefix; ?>lf_ace_c" class="wmtInput" onchange="total_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_ace_c'},'Yes_No'); ?></select></td>
						</tr>
						<tr>
							<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtBody'; ?>">Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_ace_e" id="<?php echo $field_prefix; ?>lf_ace_e" class="wmtInput" onchange="total_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_ace_e'},'Yes_No'); ?></select></td>
						</tr>
						<tr>
							<td class="wmtBody"><b>Test Score:&nbsp;&nbsp;</b>Each 'Yes' Answer counts as 1 point. A score of less than 2 is considered passing, while 2 or more is considered failing.</td>
							<td class="wmtB wmtR"><input name="<?php echo $field_prefix; ?>lf_ace_tot" id="<?php echo $field_prefix; ?>lf_ace_tot" class="wmtInput wmtR" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'lf_ace_tot'}, ENT_QUOTES, '', FALSE); ?>" title="Please Enter A Numeric Value" /></td>
						</tr>
					</table>
				</fieldset>
			</div>
			<script type="text/javascript">

function total_ace(pre)
{
	var tot = new Number;
	tot = document.getElementById(pre+'lf_ace_tot').value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  // alert("T Value: "+document.getElementById(pre+'t_ace_t').value);
	t = 0;
  if(document.getElementById(pre+'lf_ace_t').selectedIndex == 1) t = 1;
	new_tot += t;
	t = 0;
  if(document.getElementById(pre+'lf_ace_a').selectedIndex == 1) t = 1;
	new_tot += t;
	t = 0;
  if(document.getElementById(pre+'lf_ace_c').selectedIndex == 1) t = 1;
	new_tot += t;
	t = 0;
  if(document.getElementById(pre+'lf_ace_e').selectedIndex == 1) t = 1;
	new_tot += t;

	new_tot= parseInt(new_tot);
	document.getElementById(pre+'lf_ace_tot').value= new_tot;
	return true;
}
			</script>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php } ?>

			<!-- ALCOHOL 'T-ACE' QUESTIONNAIRE IF CONFIGURED -->
			<?php if($use_t_ace && $use_lifestyle) { ?>
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">&nbsp;&nbsp;&nbsp;
					<input name="tmp_t_ace_check" id="tmp_t_ace_check" type="checkbox" value="show" <?php echo ($dt['tmp_t_ace_disp_mode'] == 'none') ? '' : 'checked'; ?> onchange="ToggleDivDisplay('alcohol_t_ace', 'tmp_t_ace_check', 'tmp_t_ace_disp_mode');" />
					&nbsp;&nbsp;<label for="tmp_t_ace_check"><i>T-ACE Alcohol Questionnaire</i></label>
				</td>
			</tr>
		</table>

		<div id="alcohol_t_ace" style="width: 100%; display: <?php echo $dt['tmp_t_ace_disp_mode']; ?>;">
				<fieldset style="margin: 6px; padding: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;T-ACE Alcohol Questionnaire&nbsp;</legend>
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
        			<td class="wmtBody"><b>T</b>&nbsp;&nbsp;<i>Tolerance:</i>&nbsp;&nbsp;How many drinks does it take to make you feel high?</td>
							<td><input name="<?php echo $field_prefix; ?>lf_t_ace_t" id="<?php echo $field_prefix; ?>lf_t_ace_t" class="wmtInput" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'lf_t_ace_t'}, ENT_QUOTES, '', FALSE); ?>" onchange="total_t_ace('<?php echo $field_prefix; ?>');" title="Please Enter A Numeric Value" /></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>A</b>&nbsp;&nbsp;Have people <i>annoyed</i> you by criticizing your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_t_ace_a" id="<?php echo $field_prefix; ?>lf_t_ace_a" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_t_ace_a'},'Yes_No'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>C</b>&nbsp;&nbsp;Have you ever felt you ought to <i>cut down</i> on your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_t_ace_c" id="<?php echo $field_prefix; ?>lf_t_ace_c" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_t_ace_c'},'Yes_No'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>E</b>&nbsp;&nbsp;<i>Eye opener</i>&nbsp;&nbsp;Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?</td>
							<td class="wmtT wmtR"><select name="<?php echo $field_prefix; ?>lf_t_ace_e" id="<?php echo $field_prefix; ?>lf_t_ace_e" class="wmtInput" onchange="total_t_ace('<?php echo $field_prefix; ?>');">
							<?php ListSel($dt{$field_prefix.'lf_t_ace_e'},'Yes_No'); ?></select></td>
      			</tr>
						<tr>
							<td class="wmtBody"><b>Test Score:&nbsp;&nbsp;&nbsp;&nbsp;</b>The T-ACE, which is based on the CAGE, is valuable for identifying a range of use, including lifetime and prenatal use, based on the DSM-III-R criteria. A score of 2 or more is considered positive. Affirmative answers to questions A, C or E = 1 point each. Reporting tolerance to more than two drinks (the T question) = 2 points.</td>
							<td class="wmtB wmtR"><input name="<?php echo $field_prefix; ?>lf_t_ace_tot" id="<?php echo $field_prefix; ?>lf_t_ace_tot" class="wmtInput wmtR" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'lf_t_ace_tot'}, ENT_QUOTES, '', FALSE); ?>" title="Please Enter A Numeric Value" /></td>
						</tr>
					</table>
				</fieldset>
			</div>
			<script type="text/javascript">

function total_t_ace(pre)
{
	var tot = new Number;
	tot = document.getElementById(pre+'lf_t_ace_tot').value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
  // alert("T Value: "+document.getElementById(pre+'t_ace_t').value);
  t = parseInt(document.getElementById(pre+'lf_t_ace_t').value);
	if(!isNaN(t)) {
		if(t > 2) new_tot += 2;
	}
	t = 0;
  if(document.getElementById(pre+'lf_t_ace_a').selectedIndex == 1) t = 1;
	new_tot += t;
	// alert("New Value: "+new_tot);
	t = 0;
  if(document.getElementById(pre+'lf_t_ace_c').selectedIndex == 1) t = 1;
	new_tot += t;
	t = 0;
  if(document.getElementById(pre+'lf_t_ace_e').selectedIndex == 1) t = 1;
	new_tot += t;

	new_tot= parseInt(new_tot);
	document.getElementById(pre+'lf_t_ace_tot').value= new_tot;
	return true;
}
			</script>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php } ?>

			<!-- ALCOHOL 'AUDIT' QUESTIONNAIRE IF CONFIGURED -->
			<?php if($use_audit && $use_lifestyle) { ?>
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" colspan="3">&nbsp;&nbsp;&nbsp;
					<input name="tmp_audit_check" id="tmp_audit_check" type="checkbox" value="show" <?php echo ($dt['tmp_audit_disp_mode'] == 'none') ? '' : 'checked'; ?> onchange="ToggleDivDisplay('alcohol_audit', 'tmp_audit_check', 'tmp_audit_disp_mode');" />
					&nbsp;&nbsp;<label for="tmp_audit_check"><i>Alcohol 'AUDIT' Questionnaire</i></label>
				</td>
			</tr>
		</table>

		<div id="alcohol_audit" style="width: 100%; display: <?php echo $dt['tmp_audit_disp_mode']; ?>;">
				<fieldset style="margin: 6px; padding: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;AUDIT Alcohol Questionnaire&nbsp;</legend>
					<table width="100%" border="0" cellspacing="0" cellpadding="3">
						<tr>
        			<td class="wmtBody"><b>1.</b>&nbsp;&nbsp;How often do you have a drink containing alcohol?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_often" id="<?php echo $field_prefix; ?>lf_alc_often" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_often'},'AUDIT_Q_1'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>2.</b>&nbsp;&nbsp;How many drinks containing alcohol do you have on a typical day when you are drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_many" id="<?php echo $field_prefix; ?>lf_alc_many" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_many'},'AUDIT_Q_2'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>3.</b>&nbsp;&nbsp;How often do you have six or more drinks on one occasion?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_often_gt" id="<?php echo $field_prefix; ?>lf_alc_often_gt" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_often_gt'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>4.</b>&nbsp;&nbsp;How often during the last year have you found that you were not able to stop drinking once you had started?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_no_stop" id="<?php echo $field_prefix; ?>lf_alc_no_stop" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_no_stop'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>5.</b>&nbsp;&nbsp;How often during the last year have you failed to do what was normally expected from you because of drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_fail" id="<?php echo $field_prefix; ?>lf_alc_fail" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_fail'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>6.</b>&nbsp;&nbsp;How often during the last year have you needed a first drink in the morning to get yourself going after a heavy drinking session?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_morning" id="<?php echo $field_prefix; ?>lf_alc_morning" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_morning'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>7.</b>&nbsp;&nbsp;How often during the last year have you had a feeling of guilt or remorse after drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_guilt" id="<?php echo $field_prefix; ?>lf_alc_guilt" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_guilt'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>8.</b>&nbsp;&nbsp;How often during the last year have you been unable to remember what happened the night before because you had been drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_memory" id="<?php echo $field_prefix; ?>lf_alc_memory" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_memory'},'AUDIT_Q_3_8'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>9.</b>&nbsp;&nbsp;Have you or someone else been injured as a result of your drinking?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_injure" id="<?php echo $field_prefix; ?>lf_alc_injure" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_injure'},'AUDIT_Q_9_10'); ?></select></td>
      			</tr>
						<tr>
        			<td class="wmtBody"><b>10.</b>&nbsp;Has a relative or friend, or a doctor of other health worker been concerned about your drinking or suggested you cut down?</td>
							<td class="wmtR"><select name="<?php echo $field_prefix; ?>lf_alc_concern" id="<?php echo $field_prefix; ?>lf_alc_concern" class="wmtInput" onchange="total_audit('<?php echo $field_prefix; ?>');" />
							<?php ListSel($dt{$field_prefix.'lf_alc_concern'},'AUDIT_Q_9_10'); ?></select></td>
      			</tr>
						<tr>
							<td class="wmtBody"><b>Test Score:&nbsp;&nbsp;&nbsp;&nbsp;</b>The Alcohol Use Disorders Test (AUDIT) can detect alcohol problems experienced in the last year. A score of 8+ on the AUDIT generally indicates harmful or hazardous drinking.</td>
							<td class="wmtB wmtR"><input name="<?php echo $field_prefix; ?>lf_alc_tot" id="<?php echo $field_prefix; ?>lf_alc_tot" class="wmtInput wmtR" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'lf_alc_tot'}, ENT_QUOTES, '', FALSE); ?>" title="Please Enter A Numeric Value" /></td>
						</tr>
					</table>
				</fieldset>
			</div>
			<script type="text/javascript">

function total_audit(pre)
{
	var tot = new Number;
	tot = document.getElementById(pre+'lf_alc_tot').value;
	var new_tot = new Number;
	new_tot = 0;
	var t = new Number;
	var o = '';
	var audit_fields = ['often', 'many', 'often_gt', 'no_stop', 'fail',
		'morning', 'guilt', 'memory', 'injure', 'concern'];
	for(i = 0; i < audit_fields.length; i++) {
		o = document.getElementById(pre+'lf_alc_'+audit_fields[i]);	
		t = o.options[o.selectedIndex].value;
		t = parseInt(t);
		if(!isNaN(t)) new_tot += t;
	}

	new_tot= parseInt(new_tot);
	document.getElementById(pre+'lf_alc_tot').value= new_tot;
	return true;
}
			</script>
    <table width="100%" border="0" cellspacing="0" cellpadding="3">
			<?php } ?>


      <tr>
        <td style="width: 120px;" class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Other Drugs</td>
        <td style="width: 220px;"><select name="<?php echo $field_prefix; ?>drug_use" id="<?php echo $field_prefix; ?>drug_use" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>">
          <?php DrugUseListSel($dt{$field_prefix.'drug_use'}); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>drug_note" id="<?php echo $field_prefix; ?>drug_note" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'drug_note'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td style="width: 100px;" class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>">Dt Quit (if appl):</td>
        <td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>drug_dt" id="<?php echo $field_prefix; ?>drug_dt" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo $dt{$field_prefix.'drug_dt'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" align="absbottom" width="20" height="20" id="img_<?php echo $field_prefix; ?>drug_dt" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date"></td>
					<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>drug_dt", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>drug_dt"});
					</script>
      </tr>
 
			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('drug_use','drug_note','drug_dt');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_use" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['drug_use']['content'],'Drug_Use_Status'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_note" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['drug_note']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_dt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['drug_dt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<?php if($use_coffee) { ?>
      <tr>
        <td style="width: 120px;" class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">Caffeine</td>
        <td style="width: 220px;"><select name="<?php echo $field_prefix; ?>coffee_use" id="<?php echo $field_prefix; ?>coffee_use" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>">
          <?php ListSel($dt{$field_prefix.'coffee_use'},'Caffeine_Use'); ?>
        </select></td>
        <td><input name="<?php echo $field_prefix; ?>coffee_note" id="<?php echo $field_prefix; ?>coffee_note" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'coffee_note'}, ENT_QUOTES, '', FALSE); ?>" /></td>
        <td style="width: 100px;" class="<?php echo ($portal_mode) ? 'bkkBody bkkR' : 'wmtBody wmtR'; ?>">Dt Quit (if appl):</td>
        <td class="<?php echo ($portal_mode) ? 'bkkDateCell' : 'wmtDateCell'; ?>"><input name="<?php echo $field_prefix; ?>coffee_dt" id="<?php echo $field_prefix; ?>coffee_dt" class="<?php echo ($portal_mode) ? 'bkkDateInput' : 'wmtDateInput'; ?>" type="text" value="<?php echo $dt{$field_prefix.'coffee_dt'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="<?php echo ($portal_mode) ? 'bkkCalendarCell' : 'wmtCalendarCell'; ?>"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" align="absbottom" width="20" height="20" id="img_<?php echo $field_prefix; ?>coffee_dt" border="0" alt="[?]" style="cursor:pointer" title="Click here to choose a date"></td>
					<script type="text/javascript">
					Calendar.setup({inputField:"<?php echo $field_prefix; ?>coffee_dt", ifFormat:"%Y-%m-%d", button:"img_<?php echo $field_prefix; ?>coffee_dt"});
					</script>
      </tr>
 
			<?php
			if($pat_entries_exist && !$portal_mode) {
				$inc = false;
				$keys = array('coffee_use','coffee_note','coffee_dt');
				foreach($keys as $key => $val) {
					if($pat_entries[$val]['content'] && ($pat_entries[$val]['content'] != $dt{$field_prefix.$val})) $inc= true;
				}
				if($inc) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_use" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['coffee_use']['content'],'Caffeine_Use'), ENT_QUOTES, '', FALSE); ?></td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_note" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['coffee_note']['content'], ENT_QUOTES, '', FALSE); ?></td>
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>drug_dt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['cofee_dt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
		}  // End of the use coffee part
		?>

		<?php if($field_prefix != 'ee1_') { ?>
			<tr>
				<td style="width: 120px;" class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody' ;?>">Sexually active?&nbsp;&nbsp;&nbsp;</td>
				<td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>" style="width: 220px;">
					<div style="float: left; width: 100px;"><input name="<?php echo $field_prefix; ?>sex_active" id="sex_active_yes" type="checkbox" value="y" <?php echo (($dt{$field_prefix.'sex_active'} == 'y') ? 'checked' : ''); ?> onClick="TogglePair('sex_active_yes','sex_active_no');" /><label for="sex_active_yes">&nbsp;Yes&nbsp;</label></div>
					<div style="float: right; width: 100px;"><input name="<?php echo $field_prefix; ?>sex_active" id="sex_active_no" type="checkbox" value="n" <?php echo (($dt{$field_prefix.'sex_active'} == 'n') ? 'checked' : ''); ?> onClick="TogglePair('sex_active_no','sex_active_yes');" /><label for="sex_active_no">&nbsp;No&nbsp;</label></div></td>
        <td colspan="5"><input name="<?php echo $field_prefix; ?>sex_act_nt" id="<?php echo $field_prefix; ?>sex_act_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'sex_act_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if(($pat_entries['sex_active']['content'] && $pat_entries['sex_active']['content'] != $dt{$field_prefix.'sex_active'}) || ($pat_entries['sex_act_nt']['content'] && strpos($dt{$field_prefix.'sex_act_nt'},$pat_entries['sex_act_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td>
					<div style="float: left; width: 100px;" class="wmtBorderHighlight wmtBody" id="tmp_sex_active_yes" onclick="AcceptPortalData(this.id,'sex_active_no');"><?php echo ($pat_entries['sex_active']['content'] == 'y') ? 'Checked' : 'Unchecked'; ?>&nbsp;&nbsp;&nbsp;</div>
				<div style="float: right; width: 100px;" class="wmtBorderHighlight wmtBody" id="tmp_sex_active_no" onclick="AcceptPortalData(this.id,'sex_active_yes');"><?php echo ($pat_entries['sex_active']['content'] == 'n') ? 'Checked' : 'Unchecked'; ?></div></td>
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>sex_act_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['sex_act_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
				<td class="<?php echo $portal_mode ? 'bkkBody bkkT' : 'wmtBody wmtT'; ?>">Sexual History</td>
        <td colspan="5"><textarea name="<?php echo $field_prefix; ?>sex_nt" id="<?php echo $field_prefix; ?>sex_nt" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="3"><?php echo htmlspecialchars($dt{$field_prefix.'sex_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['sex_nt']['content'] && (strpos($dt{$field_prefix.'sex_nt'},$pat_entries['sex_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="5" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>sex_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['sex_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

			<tr>
        <td class="<?php echo $portal_mode ? 'bkkBody' : 'wmtBody'; ?>">Birth Control:</td>
        <td><select name="<?php echo $field_prefix; ?>bc_chc" id="<?php echo $field_prefix; ?>bc_chc" class="<?php echo $portal_mode ? 'bkkInput' : 'wmtInput'; ?>">
				<?php ListSel($dt{$field_prefix.'bc_chc'}, 'YesNo'); ?></select></td>
        <td colspan="4"><input name="<?php echo $field_prefix; ?>bc" id="<?php echo $field_prefix; ?>bc" class="<?php echo $portal_mode ? 'bkkFullInput' : 'wmtFullInput'; ?>" type="text" value="<?php echo htmlspecialchars($dt{$field_prefix.'bc'}, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['bc']['content'] && (strpos($dt{$field_prefix.'bc'},$pat_entries['bc']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>bc_chc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars(ListLook($pat_entries['bc_chc']['content'],'YesNo'), ENT_QUOTES, '', FALSE); ?></td>
				<td colspan="4" class="wmtBorderHighlight wmtBody" id="tmp_<?php echo $field_prefix; ?>bc" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['bc']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>

		<?php } // The end of the ext exam exception ?>

      <tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody bkkT' : 'wmtBody wmtT'; ?>">Other Notes:</td>
				<td colspan="5">
					<?php $_tmp = ($portal_mode) ? 'prt_' : ''; ?>
					<textarea name="<?php echo $_tmp; ?>fyi_sh_nt" id="<?php echo $_tmp; ?>fyi_sh_nt" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="3"><?php echo htmlspecialchars($dt{$_tmp.'fyi_sh_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>

			<?php
			if($pat_entries_exist && !$portal_mode) {
				if($pat_entries['fyi_sh_nt']['content'] && (strpos($dt{'fyi_sh_nt'},$pat_entries['fyi_sh_nt']['content']) === false)) {
			?>
			<tr class="wmtPortalData">
				<td>&nbsp;</td>
				<td colspan="5" class="wmtBorderHighlight wmtBody" id="tmp_fyi_sh_nt" onclick="AcceptPortalData(this.id);"><?php echo htmlspecialchars($pat_entries['fyi_sh_nt']['content'], ENT_QUOTES, '', FALSE); ?></td>
			</tr>
			<?php
				}
			}
			?>
    </table>
		<input name="tmp_cessation_disp_mode" id="tmp_cessation_disp_mode" type="hidden" tabindex="-1" value="<?php echo $dt['tmp_cessation_disp_mode']; ?>" />
		<input name="tmp_ace_disp_mode" id="tmp_ace_disp_mode" type="hidden" tabindex="-1" value="<?php echo $dt['tmp_ace_disp_mode']; ?>" />
		<input name="tmp_t_ace_disp_mode" id="tmp_t_ace_disp_mode" type="hidden" tabindex="-1" value="<?php echo $dt['tmp_t_ace_disp_mode']; ?>" />
		<input name="tmp_t_audit_disp_mode" id="tmp_audit_disp_mode" type="hidden" tabindex="-1" value="<?php echo $dt['tmp_audit_disp_mode']; ?>" />
<?php ?>

	
<?php if($expanded_sh) { ?>
<div id="expanded_sh" style="margin: 8px; display: <?php echo $dt['tmp_expanded_sh_disp_mode']; ?>;">
	<!--fieldset style="margin: 6px; padding: 6px;"><legend class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">&nbsp;Lifestyle / Enviromnment Considerations&nbsp;</legend-->
		<table width="100%" border="0" cellspacing="0" cellpadding="3">
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkLabel' : 'wmtLabel'; ?>">Lifestyle / Environmental Considerations</td>	
		<?php 
		foreach($sh_questions as $q) {
			$chc = '';
			if($q['codes']) $chc = $field_prefix.'sh_ex_'.$q['option_id'].'_chc';
			$nt = $field_prefix.'sh_ex_'.$q['option_id'].'_nt';
			if($q['seq'] < 0 || 
						(strpos('DO NOT USE',strtoupper($q['notes'])) !== false)) {
				if($chc) {
			?>
			<input name="<?php echo $chc; ?>" id="<?php echo $chc; ?>" type="hidden" tabindex="-1" value="<?php echo $dt[$chc]; ?>" />
				<?php } ?>
			<input name="<?php echo $nt; ?>" id="<?php echo $nt; ?>" type="hidden" tabindex="-1" value="<?php echo $dt[$nt]; ?>" />
			<?php
			} else {
		?>
			<tr>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>" style="width: 320px;" ><?php echo $q['title']; ?>: </td>
				<?php if($q['codes']) { ?>
				<td class="wmtL">
					<select name="<?php echo $chc; ?>" id="<?php echo $chc; ?>" class="<?php echo ($portal_mode) ? 'bkkInput' : 'wmtInput'; ?>">
					<?php ListSel($dt[$chc],$q['codes']); ?>
				</select></td>
				<?php } else { ?>
				<td class="<?php echo ($portal_mode) ? 'bkkBody' : 'wmtBody'; ?>">&nbsp;</td>
				<?php } ?>
			</tr>
			<tr>
				<td colspan="2"><textarea name="<?php echo $nt; ?>" id="<?php echo $nt; ?>" class="<?php echo ($portal_mode) ? 'bkkFullInput' : 'wmtFullInput'; ?>" rows="3"><?php echo htmlspecialchars($dt[$nt],ENT_QUOTES,'',FALSE); ?></textarea></td>
		<?php 
			}
		}
		?>
		</table>
	<!--/fieldset-->
</div>
<?php } ?>
