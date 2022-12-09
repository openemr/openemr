<?php
if(!isset($dt['tmp_form_dt'])) $dt['tmp_form_dt'] = date('Y-m-d');
$flds = sqlListFields('form_reproductive1');
$flds = array_slice($flds,11);
foreach($flds as $fld) {
	if(!isset($dt[$fld])) $dt[$fld] = '';
}
if(!isset($dt['tmp_rp1_chc_y'])) $dt['tmp_rp1_chc_y'] = '';
if(!isset($dt['tmp_rp1_bc_y'])) $dt['tmp_rp1_bc_y'] = '';
if(!isset($dt['tmp_rp1_chc_n'])) $dt['tmp_rp1_chc_n'] = '';
if(!isset($dt['tmp_rp1_bc_n'])) $dt['tmp_rp1_bc_n'] = '';
?>
<script type="text/javascript">
function ToggleBCDivs(bc) {
	var test = bc.value.substring(0,1);
	test = test.toLowerCase();
	if(test == 'y') {
		document.getElementById('kids_yes').style.display = 'block';
		document.getElementById('tmp_yes_disp_mode').style.display = 'block';
		document.getElementById('kids_no').style.display = 'none';
		document.getElementById('tmp_no_disp_mode').style.display = 'none';
		document.forms[0].elements['rp1_get_preg_nt'].value = '';
	} else if(test == 'n') {
		document.getElementById('kids_yes').style.display = 'none';
		document.getElementById('tmp_yes_disp_mode').style.display = 'none';
		document.getElementById('kids_no').style.display = 'block';
		document.getElementById('tmp_no_disp_mode').style.display = 'block';
		document.forms[0].elements['rp1_how_many_kids'].value = '';
		document.forms[0].elements['rp1_age_to_have'].value = '';
		document.forms[0].elements['rp1_age_apart'].value = '';
	} else {
		document.forms[0].elements['tmp_rp1_chc_y'].selectedIndex = 0;
		document.forms[0].elements['tmp_rp1_chc_y'].value = '';
		document.forms[0].elements['tmp_rp1_bc_y'].value = '';
		document.forms[0].elements['tmp_rp1_chc_n'].selectedIndex = 0;
		document.forms[0].elements['tmp_rp1_chc_n'].value = '';
		document.forms[0].elements['tmp_rp1_bc_n'].value = '';
		document.forms[0].elements['rp1_get_preg_nt'].value = '';
		document.forms[0].elements['rp1_how_many_kids'].value = '';
		document.forms[0].elements['rp1_age_to_have'].value = '';
		document.forms[0].elements['rp1_age_apart'].value = '';
		document.getElementById('kids_yes').style.display = 'none';
		document.getElementById('tmp_yes_disp_mode').style.display = 'none';
		document.getElementById('kids_no').style.display = 'none';
		document.getElementById('tmp_no_disp_mode').style.display = 'none';
	}
}
</script>
	<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="bkkLabel">&nbsp;Reproductive Life Plan&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php if($frmdir == 'dashboard') { ?>
		<tr>
		<td colspan="2" class="bkkRed"><span class="bkkLabel">Questionnaire Date:&nbsp;&nbsp;</span><span class="bkkBody"><?php echo $dt{'tmp_form_dt'}; ?></span></td>
		</tr>
		<?php } ?>
		<tr>
			<td style="width: 74px;"><select name="rp1_soon_preg" id="rp1_soon_preg" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_soon_preg'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL" style="width: 350px;">Do you want to become pregnant within the next year?</td>
			<td><input name="rp1_soon_preg_nt" id="rp1_soon_preg_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_soon_preg_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_want_kids" id="rp1_want_kids" class="bkkInput" onchange="ToggleBCDivs(this);" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_want_kids'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Do you want to have children one day?</td>
			<td><input name="rp1_want_kids_nt" id="rp1_want_kids_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_want_kids_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	<div id="kids_yes" style="display: <?php echo $dt['tmp_yes_disp_mode']; ?>">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width: 74px;"><select name="tmp_rp1_chc_y" id="tmp_rp1_chc_y" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php ListSel($dt{'tmp_rp1_chc_y'}, 'YesNo'); ?></select></td>
			<td class="bkkBody bkkL" style="width: 350px;">Are you currently using birth control?</td>
			<td><input name="tmp_rp1_bc_y" id=tmp_rp1_bc_y" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'tmp_rp1_bc_y'}, ENT_QUOTES, '', FALSE); ?>" title="Describe birth control if yes" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><input name="rp1_how_many_kids" id="rp1_how_many_kids" class="bkkInput" value="<?php echo htmlspecialchars($dt{'rp1_how_many_kids'}, ENT_QUOTES, '', FALSE); ?>" title="Number"  <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>/></td>
			<td class="bkkBody bkkL">How many children would you like?</td>
		</tr>
		<tr>
			<td><input name="rp1_age_to_have" id="rp1_age_to_have" class="bkkInput" value="<?php echo htmlspecialchars($dt{'rp1_age_to_have'}, ENT_QUOTES, '', FALSE); ?>" title="Age in years" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
			<td class="bkkBody bkkL">What age would you like to have children?</td>
		</tr>
		<tr>
			<td><input name="rp1_age_apart" id="rp1_age_apart" class="bkkInput" value="<?php echo htmlspecialchars($dt{'rp1_age_apart'}, ENT_QUOTES, '', FALSE); ?>" title="A number in years" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
			<td class="bkkBody bkkL">How far apart would you like your children to be?</td>
		</tr>
	</table>
	</div>
	<div id="kids_no" style="display: <?php echo $dt['tmp_no_disp_mode']; ?>">
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width: 74px;"><select name="tmp_rp1_chc_n" id="tmp_rp1_chc_n" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php ListSel($dt{'tmp_rp1_chc_n'}, 'YesNo'); ?></select></td>
			<td class="bkkBody bkkL" style="width: 350px;">Are you currently using birth control?</td>
			<td><input name="tmp_rp1_bc_n" id=tmp_rp1_bc_n" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'tmp_rp1_bc_n'}, ENT_QUOTES, '', FALSE); ?>" title="Describe birth control if yes" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td class="bkkBody bkkL">What will you do if you become pregnant?</td>
			<td><input name="rp1_get_preg_nt" id="rp1_get_preg_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_get_preg_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	</div>
	</fieldset>
	<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="bkkLabel">&nbsp;Dietary Information&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width: 55px;"><select name="rp1_diet_unhealthy" id="rp1_diet_unhealthy" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_diet_unhealthy'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL" style="width: 350px;">Do you tend to eat an unhealthy diet?</td>
			<td><input name="rp1_diet_unhealthy_nt" id="rp1_diet_unhealthy_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_diet_unhealthy_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_diet_overeat" id="rp1_diet_overeat" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_diet_overeat'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Do you tend to over eat?</td>
			<td><input name="rp1_diet_overeat_nt" id="rp1_diet_overeat_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_diet_overeat_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_diet_undereat" id="rp1_diet_undereat" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_diet_undereat'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Do you tend to under eat?</td>
			<td><input name="rp1_diet_undereat_nt" id="rp1_diet_undereat_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_diet_undereat_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	</fieldset>
	<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="bkkLabel">&nbsp;Emotional Health&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width: 55px;"><select name="rp1_emo_bounce" id="rp1_emo_bounce" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_emo_bounce'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL" style="width: 350px;">When you feel sad do you bounce back quickly?</td>
			<td><input name="rp1_emo_bounce_nt" id="rp1_emo_bounce_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_emo_bounce_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_emo_sad" id="rp1_emo_sad" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_emo_sad'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Do you feel sad for more than two weeks at a time?</td>
			<td><input name="rp1_emo_sad_nt" id="rp1_emo_sad_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_emo_sad_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_emo_anx" id="rp1_emo_anx" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_emo_anx'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Do you feel nervous, anxious or worried?</td>
			<td><input name="rp1_emo_anx_nt" id="rp1_emo_anx_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_emo_anx_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_emo_abuse" id="rp1_emo_abuse" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_emo_abuse'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Is there anyone in your life that is physically abusive?</td>
			<td><input name="rp1_emo_abuse_nt" id="rp1_emo_abuse_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_emo_abuse_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_emo_mean" id="rp1_emo_mean" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_emo_mean'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Is there anyone in your life who often says hurtful or mean things?</td>
			<td><input name="rp1_emo_mean_nt" id="rp1_emo_mean_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_emo_mean_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	</fieldset>
	<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="bkkLabel">&nbsp;Personal Goals&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td style="width: 55px;"><select name="rp1_pg_vitamin_chc" id="rp1_pg_vitamin_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_vitamin_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL" style="width: 350px;">Take a daily vitamin?</td>
			<td><input name="rp1_pg_vitamin_nt" id="rp1_pg_vitamin_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_vitamin_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_smoke_chc" id="rp1_pg_smoke_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_smoke_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Quit or reduce the amount I smoke?</td>
			<td><input name="rp1_pg_smoke_nt" id="rp1_pg_smoke_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_smoke_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_condom_chc" id="rp1_pg_condom_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_condom_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Increase or always use a condom?</td>
			<td><input name="rp1_pg_condom_nt" id="rp1_pg_condom_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_condom_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_bc_chc" id="rp1_pg_bc_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_bc_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Use birth control continuously?</td>
			<td><input name="rp1_pg_bc_nt" id="rp1_pg_bc_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_bc_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_exercise_chc" id="rp1_pg_exercise_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_exercise_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Start or increase amount of exercise?</td>
			<td><input name="rp1_pg_exercise_nt" id="rp1_pg_exercise_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_exercise_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_gain_chc" id="rp1_pg_gain_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_gain_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Increase my weight?</td>
			<td><input name="rp1_pg_gain_nt" id="rp1_pg_gain_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_gain_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_lose_chc" id="rp1_pg_lose_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_lose_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Decrease my weight?</td>
			<td><input name="rp1_pg_lose_nt" id="rp1_pg_lose_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_lose_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_maintain_chc" id="rp1_pg_maintain_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_maintain_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Maintain my weight?</td>
			<td><input name="rp1_pg_maintain_nt" id="rp1_pg_maintain_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_maintain_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_alcohol_chc" id="rp1_pg_alcohol_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_alcohol_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Quit or reduce the amount of alcohol I use?</td>
			<td><input name="rp1_pg_alcohol_nt" id="rp1_pg_alcohol_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_alcohol_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td><select name="rp1_pg_drug_chc" id="rp1_pg_drug_chc" class="bkkInput" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?>>
				<?php echo ListSel($dt{'rp1_pg_drug_chc'},'YesNo'); ?>
			</select></td>
			<td class="bkkBody bkkL">Quit or reduce the amount of drugs I use?</td>
			<td><input name="rp1_pg_drug_nt" id="rp1_pg_drug_nt" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pg_drug_nt'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	</fieldset>
	<fieldset style="border: solid 1px gray; margin: 4px;"><legend class="bkkLabel">&nbsp;Professional Goals&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="bkkBody bkkL" style="width: 150px;">Education Plan:<td>
			<td><input name="rp1_pro_ed" id="rp1_pro_ed" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pro_ed'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
		<tr>
			<td class="bkkBody bkkL" style="width: 150px;">Employment Plan:<td>
			<td><input name="rp1_pro_emp" id="rp1_pro_emp" class="bkkFullInput" value="<?php echo htmlspecialchars($dt{'rp1_pro_emp'}, ENT_QUOTES, '', FALSE); ?>" <?php echo ($frmdir == 'dashboard') ? 'disabled' : ''; ?> /></td>
		</tr>
	</table>
	</fieldset>
