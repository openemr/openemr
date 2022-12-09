<?php
if(!isset($frmdir)) $frmdir = '';
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($dt['form_dt'])) $dt['form_dt'] = '';
$last_preg = '0000-00-00';
$old= sqlQuery("SELECT pid, id, pp_date_of_pregnancy FROM form_whc_pp ".
		"WHERE pid=? AND pp_date_of_pregnancy < ? ORDER BY pp_date_of_pregnancy ".
		"DESC LIMIT 1",array($pid, $dt['form_dt']));
if($old{'pp_date_of_pregnancy'}) $last_preg = $old{'pp_date_of_pregnancy'};
if(strlen($last_preg) == 7) $last_preg .= '-00';

if($form_mode == 'new' || $form_mode == 'update') {

} else {
	$pnv = array();
	foreach($_POST as $key => $var) {
		if(substr($key,0,3) != 'pn_') continue;
		if(is_string($var)) $var = trim($var);
		$tmp = explode('_', $key);
		$cnt = $tmp[count($tmp)-1];
		$tmp = strrpos($key, '_');
		$key_base = substr($key,0,$tmp);
		$pnv[$cnt][$key_base] = $var;
		unset($_POST[$key]);
	}
	foreach($pnv as $v) {
		if(!$v['pn_id']) {
			$new_id = AddPrenatal($pid, $v);
		} else {
			UpdatePrenatal($pid, $v);
		}
	}
}
$pn = GetPrenatalVisits($pid, 0, $last_preg, $dt['form_dt']);
$pn[] = array('id' => '', 'pn_date' => '', 'pn_weeks_gest' => '', 
	'pn_fundal_height' => '', 'pn_present' => '', 'pn_fhr' => '',
	'pn_fetal_move' => '', 'pn_preterm_labor' => '', 'pn_cervix_exam' => '', 
	'pn_bp' => '', 'pn_weight' => '', 'pn_weight_gain' => '', 
	'pn_urine_pro' => '', 'pn_urine_glu' => '', 'pn_edema' => '', 
	'pn_pain_scale' => '', 'pn_next_appt' => '', 'pn_dr_init' => '', 
	'pn_comment' => '');
$max = count($pn);
if($draw_display) {
?>

<fieldset style="margin: 12px;"><legend class="wmtLabel">&nbsp;Pre-Pregnancy&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td style="width: 180px;"><span class="wmtLabel">Weight:&nbsp;&nbsp;</span><input name="pre_preg_wt" id="pre_preg_wt" type="text" class="wmtInput" style="width: 80px" value="<?php echo htmlspecialchars($dt{'pre_preg_wt'},ENT_QUOTES); ?>" onchange="UpdateBMI('pre_preg_ht','pre_preg_wt','pre_preg_BMI','pre_preg_BMI_status'); calc_all_weights('<?php echo count($pn); ?>');" /></td>
	<td style="width: 180px;"><span class="wmtLabel">Height:&nbsp;&nbsp;</span><input name="pre_preg_ht" id="pre_preg_ht" type="text" class="wmtInput" style="width: 80px" value="<?php echo htmlspecialchars($dt{'pre_preg_ht'},ENT_QUOTES); ?>" onchange="UpdateBMI('pre_preg_ht','pre_preg_wt','pre_preg_BMI','pre_preg_BMI_status');" /></td>
	<td style="width: 180px;"><span class="wmtLabel">BMI:&nbsp;&nbsp;</span><input name="pre_preg_BMI" id="pre_preg_BMI" type="text" class="wmtInput" style="width: 80px" value="<?php echo htmlspecialchars($dt{'pre_preg_BMI'},ENT_QUOTES); ?>" /></td>
	<td><span class="wmtLabel">BMI Satus:&nbsp;&nbsp;</span><input name="pre_preg_BMI_status" id="pre_preg_BMI_status" type="text" class="wmtInput" value="<?php echo htmlspecialchars($dt{'pre_preg_BMI_status'},ENT_QUOTES); ?>" /></td>
	<td class="wmtBodyR"><a href="javascript:;" class="css_button" tabindex="-1" onclick="get_vitals()"><span>Search Vitals</span></a></td>
	</tr>	
</td>
</table>
</fieldset>
<table width="100%" border="0" cellspacing="0" cellpadding="1" style="border-collapse: collapse;">
  <tr>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1B wmtC">&nbsp;Date&nbsp;&nbsp;</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Week Gest</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Fundal Height</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Present</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">FHR</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Fetal Move</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Preterm Labor</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Cervix Exam</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">BP</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Weight</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Weight Gain</td>
    <td class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC" colspan="2">Urine<br>Pro&nbsp;&nbsp;&nbsp;&nbsp;Glu</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Edema</td>
		<?php if($client_id != 'wcs') { ?>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Pain Scale</td>
		<?php } ?>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Next Appt</td>
    <td style="width: 5%;" class="wmtBody3 wmtBorder1T wmtBorder1L wmtBorder1B wmtC">Prov Init</td>
  </tr>
  <?php
	$comm_cols = 16;
	if($client_id == 'wcs') $comm_cols = 15;
  $cnt = 1;
	$max = count($pn);
  foreach($pn as $prev) {
	?>
  <tr>
    <td class='wmtBody2 wmtBorder1B'><input name='pn_id_<?php echo $cnt; ?>' name='pn_id_<?php echo $cnt; ?>' type='hidden' value="<?php echo $prev['id']; ?>" /><input name="pn_date_<?php echo $cnt; ?>" id="pn_date_<?php echo $cnt; ?>" class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_date'], ENT_QUOTES); ?>" onclick="setEmptyDate('pn_date_<?php echo $cnt; ?>');" onblur="calc_weeks_gest('upd_edd', 'pn_date_<?php echo $cnt; ?>', 'pn_weeks_gest_<?php echo $cnt; ?>');" /></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_weeks_gest_<?php echo $cnt; ?>' id='pn_weeks_gest_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_weeks_gest'], ENT_QUOTES); ?>" /></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fundal_height_<?php echo $cnt; ?>' id='pn_fundal_height_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_fundal_height'], ENT_QUOTES); ?>" /></td> 
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_present_<?php echo $cnt; ?>' id="pn_present_<?php echo $cnt; ?>" class='wmtFullInput'>
    <?php echo ListSel($prev['pn_present'],'WHC_Presentation'); ?>
    </select></td> 
		<?php if(checkSettingMode('wmt::fhr_text_entry','',$frmdir)) { ?>
		<td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fhr_<?php echo $cnt; ?>' id='pn_fhr_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_fhr'], ENT_QUOTES); ?>" /></td>
		<?php } else { ?>
		<td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_fhr_<?php echo $cnt; ?>' id='pn_fhr_<?php echo $cnt; ?>' class='wmtFullInput'>";
    <?php echo ListSel($prev['pn_fhr'],'WHC_FHR'); ?>
    </select></td>
		<?php } ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fetal_move_<?php echo $cnt; ?>' id='pn_fetal_move_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_fetal_move'], ENT_QUOTES); ?>" /></td>
		<?php if($client_id == 'wcs') { ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_preterm_labor_<?php echo $cnt; ?>' id='pn_preterm_labor_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_preterm_labor'], ENT_QUOTES); ?>" /></td>
		<?php } else { ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_preterm_labor_<?php echo $cnt; ?>' id='pn_preterm_labor_<?php echo $cnt; ?>' class='wmtFullInput'>
    <?php echo ListSel($prev['pn_preterm_labor'],'WHC_Labor'); ?>
    </select></td>
		<?php } ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_cervix_exam_<?php echo $cnt; ?>' id='pn_cervix_exam_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_cervix_exam'], ENT_QUOTES); ?>" /></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_bp_<?php echo $cnt; ?>' id='pn_bp_<?php echo $cnt; ?>'  class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_bp'], ENT_QUOTES); ?>"
		<?php if($cnt == $max) { ?>
		onfocus="visit_match_fill('form_dt','pn_date_<?php echo $cnt; ?>','pn_bp_<?php echo $cnt; ?>','<?php echo ($visit->bps || $visit->bpd) ? $visit->bps . '/' . $visit->bpd : ''; ?>');"
		<?php } ?>
		/></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_weight_<?php echo $cnt; ?>' id='pn_weight_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_weight'], ENT_QUOTES); ?>" onblur="calc_weight_gain('pre_preg_wt','pn_weight_<?php echo $cnt; ?>','pn_weight_gain_<?php echo $cnt; ?>');"
		<?php if($cnt == $max) { ?>
		onfocus="visit_match_fill('form_dt','pn_date_<?php echo $cnt; ?>','pn_weight_<?php echo $cnt; ?>','<?php echo $visit->weight; ?>');"
		<?php } ?>
		/></td>
		<?php 
		if(!$prev['pn_weight_gain']) {
			if(($prev['pn_weight'] && is_numeric($prev['pn_weight'])) && 
								($dt{'pre_preg_wt'} && is_numeric($dt{'pre_preg_wt'}))) {
				$prev['pn_weight_gain'] = $prev['pn_weight'] - $dt{'pre_preg_wt'};
			}
		}
		?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_weight_gain_<?php echo $cnt; ?>' id='pn_weight_gain_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_weight_gain'], ENT_QUOTES); ?>" /></td>
    <td style='width: 3%' class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_urine_pro_<?php echo $cnt; ?>' id='pn_urine_pro_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_urine_pro'], ENT_QUOTES); ?>"
		<?php if($cnt == $max) { ?>
		onfocus="visit_match_fill('form_dt','pn_date_<?php echo $cnt; ?>','pn_urine_pro_<?php echo $cnt; ?>','<?php echo $visit->protein; ?>');"
		<?php } ?>
		/></td>
    <td style='width: 3%' class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_urine_glu_<?php echo $cnt; ?>' id='pn_urine_glu_<?php echo $cnt; ?>' class='wmtInput' style='width: 94%;' type='text' value="<?php echo htmlspecialchars($prev['pn_urine_glu'], ENT_QUOTES); ?>"
		<?php if($cnt == $max) { ?>
		onfocus="visit_match_fill('form_dt','pn_date_<?php echo $cnt; ?>','pn_urine_glu_<?php echo $cnt; ?>','<?php echo $visit->glucose; ?>');"
		<?php } ?>
		/></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_edema_<?php echo $cnt; ?>' id='pn_edema_<?php echo $cnt; ?>' class='wmtFullInput'>
    <?php ListSel($prev['pn_edema'],'WHC_Edema'); ?>
    </select></td>
		<?php if($client_id != 'wcs') { ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_pain_scale_<?php echo $cnt; ?>' id='pn_pain_scale_<?php echo $cnt; ?>' class='wmtFullInput'>
    <?php ListSel($prev['pn_pain_scale'],'WHC_Pain'); ?>
    </select></td>
		<?php } ?>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_next_appt_<?php echo $cnt; ?>' id='pn_next_appt_<?php echo $cnt; ?>' class='wmtFullInput'>
    <?php ListSel($prev['pn_next_appt'],'WHC_Weeks'); ?>
    </select></td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_dr_init_<?php echo $cnt; ?>' id='pn_dr_init_<?php echo $cnt; ?>' class='wmtFullInput'>";
    <?php ListSel($prev['pn_dr_init'],'WHC_Initials'); ?>
    </select></td>
  </tr>
  <tr>
		<td class='wmtBody2 wmtBorder1B'>Comment</td>
    <td class='wmtBody2 wmtBorder1L wmtBorder1B' colspan='<?php echo $comm_cols; ?>'><textarea name='pn_comment_<?php echo $cnt; ?>' id='pn_comment_<?php echo $cnt; ?>' class='wmtFullInput' rows='2'><?php echo htmlspecialchars($prev['pn_comment'], ENT_QUOTES); ?></textarea></td>
  </tr>
<?php 
    $cnt++;
  }
	$comm_cols++;
?>
	<tr>
	<td class='wmtBody'>Problems:</td>
	</tr>
	<tr>
    <td colspan='<?php echo $comm_cols; ?>'><textarea name='visit_problems' id='visit_problems' class='wmtFullInput' rows='3'><?php echo htmlspecialchars($dt['visit_problems'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>
	<tr>
	<td class='wmtBody'>Comments:</td>
	</tr>
	<tr>
    <td colspan='<?php echo $comm_cols; ?>'><textarea name='visit_comments' id='visit_comments' class='wmtFullInput' rows='3'><?php echo htmlspecialchars($dt['visit_comments'], ENT_QUOTES, '', FALSE); ?></textarea></td>
	</tr>

</table>

<?php
}
?>
