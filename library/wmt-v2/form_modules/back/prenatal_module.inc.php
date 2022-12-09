<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($frmdir)) $frmdir = '';
if(!isset($portal_mode)) $portal_mode = false;
if(!isset($pat_entries_exist)) $pat_entries_exist = false;
?>

<fieldset style="margin: 12px;"><legend class="wmtLabel">&nbsp;Pre-Pregnancy&nbsp;</legend>
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
		<td style="width: 130px;"><span class="wmtLabel">Weight:&nbsp;&nbsp;</span><input name="pre_preg_wt" id="pre_preg_wt" type="text" class="wmtInput" style="width: 80px" value="<?php echo $dt{'pre_preg_wt'}; ?>" onchange="UpdateBMI('pre_preg_ht','pre_preg_wt','pre_preg_BMI','pre_preg_BMI_status'); calc_all_weights();" /></td>
	<td style="width: 130px;"><span class="wmtLabel">Height:&nbsp;&nbsp;</span><input name="pre_preg_ht" id="pre_preg_ht" type="text" class="wmtInput" style="width: 80px" value="<?php echo $dt{'pre_preg_ht'}; ?>" onchange="UpdateBMI('pre_preg_ht','pre_preg_wt','pre_preg_BMI','pre_preg_BMI_status');" /></td>
	<td style="width: 130px;"><span class="wmtLabel">BMI:&nbsp;&nbsp;</span><input name="pre_preg_BMI" id="pre_preg_BMI" type="text" class="wmtInput" style="width: 80px" value="<?php echo $dt{'pre_preg_BMI'}; ?>" /></td>
	<td><span class="wmtLabel">BMI Satus:&nbsp;&nbsp;</span><input name="pre_preg_BMI_status" id="pre_preg_BMI_status" type="text" class="wmtInput" value="<?php echo $dt{'pre_preg_BMI_status'}; ?>" /></td>
	<td colspan="2" class="wmtBodyR"><a href="javascript:;" class="css_button" tabindex="-1" onclick="get_vitals()"><span>Search Vitals</span></a></td>
	</tr>	
</td>
</table>
</fieldset>
<table width="100%" border="0" cellspacing="0" cellpadding="1">
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
  $cnt = 0;
  while ($cnt < ($dt['tmp_first_empty'] + 2)) {
    echo "<tr>\n";
    echo "  <td class='wmtBody2 wmtBorder1B'><input name='pn_dt_$cnt' id='pn_dt_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_dt_'.$cnt]."' onclick='setEmptyDate(\"pn_dt_$cnt\");' onblur='calc_weeks_gest(\"upd_edd\", \"pn_dt_$cnt\", \"pn_gest_$cnt\");' /></td>\n";
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_gest_$cnt' id='pn_gest_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_gest_'.$cnt]."' /></td>\n";
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fundal_$cnt' id='pn_fundal_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_fundal_'.$cnt]."' /></td>\n"; 
    echo "  <td  class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_pres_$cnt' id='pn_pres_$cnt' class='wmtFullInput'>";
    echo ListSel($dt['pn_pres_'.$cnt],'WHC_Presentation');
    echo "</select></td>\n"; 
		if(checkSettingMode('wmt::fhr_text_entry','',$frmdir)) {
			echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fhr_$cnt' id='pn_fhr_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_fhr_'.$cnt]."' /></td>\n"; 
		} else {
			echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_fhr_$cnt' id='pn_fhr_$cnt' class='wmtFullInput'>";
    	echo ListSel($dt['pn_fhr_'.$cnt],'WHC_FHR');
    	echo "</select></td>\n"; 
		}
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_fetal_$cnt' id='pn_fetal_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_fetal_'.$cnt]."' /></td>\n"; 
		if($client_id == 'wcs') {
    	echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_pre_$cnt' id='pn_pre_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_pre_'.$cnt]."' /></td>\n"; 
		} else {
    	echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_pre_$cnt' id='pn_pre_$cnt' class='wmtFullInput'>";
    	echo ListSel($dt['pn_pre_'.$cnt],'WHC_Labor');
    	echo "</select></td>\n"; 
		}
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_cervix_$cnt' id='pn_cervix_$cnt' class='wmtInput' style='width: 94%;'  type='text' value='".$dt['pn_cervix_'.$cnt]."' /></td>";
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_bp_$cnt' id='pn_bp_$cnt'  class='wmtInput' style='width: 94%;' type='text' value='".$dt['pn_bp_'.$cnt]."' ";
		if($cnt == $dt['tmp_first_empty']) echo "onfocus='visit_match_fill(\"form_dt\",\"pn_dt_$cnt\",\"pn_bp_$cnt\",\"".$visit->bps.'/'.$visit->bpd."\");' ";
		echo "/></td>\n"; 

    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_weight_$cnt' id='pn_weight_$cnt' class='wmtInput' style='width: 94%;' type='text' value='".$dt['pn_weight_'.$cnt]."' onblur='calc_weight_gain(\"pre_preg_wt\",\"pn_weight_$cnt\",\"pn_gain_$cnt\");' ";
		if($cnt == $dt['tmp_first_empty']) echo "onfocus='visit_match_fill(\"form_dt\",\"pn_dt_$cnt\",\"pn_weight_$cnt\",\"".$visit->weight."\");' ";
		echo "/></td>\n"; 
		if(!$dt['pn_gain_'.$cnt]) {
			if(($dt['pn_weight_'.$cnt] && is_numeric($dt['pn_weight_'.$cnt])) && 
								($dt{'pre_preg_wt'} && is_numeric($dt{'pre_preg_wt'}))) {
				$dt['pn_gain_'.$cnt] = $dt['pn_weight_'.$cnt] - $dt{'pre_preg_wt'};
			}
		}
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_gain_$cnt' id='pn_gain_$cnt' class='wmtInput' style='width: 94%;' type='text' value='".$dt['pn_gain_'.$cnt]."' /></td>\n"; 
    echo "  <td style='width: 3%' class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_urine_p_$cnt' id='pn_urine_p_$cnt' class='wmtInput' style='width: 94%;' type='text' value='".$dt['pn_urine_p_'.$cnt]."'";
		if($cnt == $dt['tmp_first_empty']) echo " onfocus='visit_match_fill(\"form_dt\",\"pn_dt_$cnt\",\"pn_urine_p_$cnt\",\"".$visit->protein."\");' ";
		echo " /></td>\n"; 
    echo "  <td style='width: 3%' class='wmtBody2 wmtBorder1L wmtBorder1B'><input name='pn_urine_g_$cnt' id='pn_urine_g_$cnt' class='wmtInput' style='width: 94%;' type='text' value='".$dt['pn_urine_g_'.$cnt]."'";
		if($cnt == $dt['tmp_first_empty']) echo "onfocus='visit_match_fill(\"form_dt\",\"pn_dt_$cnt\",\"pn_urine_g_$cnt\",\"".$visit->glucose."\");' ";
		 echo " /></td>\n"; 
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_edema_$cnt' id='pn_edema_$cnt' class='wmtFullInput'>";
    echo ListSel($dt['pn_edema_'.$cnt],'WHC_Edema');
    echo "</select></td>\n"; 
		if($client_id != 'wcs') {
    	echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_pain_$cnt' id='pn_pain_$cnt' class='wmtFullInput'>";
    	echo ListSel($dt['pn_pain_'.$cnt],'WHC_Pain');
    	echo "</select></td>\n"; 
		}
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_next_$cnt' id='pn_next_$cnt' class='wmtFullInput'>";
    echo ListSel($dt['pn_next_'.$cnt],'WHC_Weeks');
    echo "</select></td>\n"; 
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B'><select name='pn_init_$cnt' id='pn_init_$cnt' class='wmtFullInput'>";
    echo ListSel($dt['pn_init_'.$cnt],'WHC_Initials');
    echo "</select></td>\n"; 
    echo "</tr>\n";
    echo "<tr>\n";
		echo "	<td class='wmtBody2 wmtBorder1B'>Comment</td>\n";
    echo "  <td class='wmtBody2 wmtBorder1L wmtBorder1B' colspan='$comm_cols'><textarea name='pn_comm_$cnt' id='pn_comm_$cnt' class='wmtFullInput' rows='2'>".$dt['pn_comm_'.$cnt]."</textarea></td>\n"; 
    echo "</tr>\n";
    $cnt++;
  }
	$comm_cols++;
	echo "<tr>\n";
	echo "<td class='wmtBody'>Problems:</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
  echo "  <td colspan='$comm_cols'><textarea name='visit_problems' id='visit_problems' class='wmtFullInput' rows='3'>";
	echo htmlspecialchars($dt['visit_problems'], ENT_QUOTES, '', FALSE);
	echo "</textarea></td>\n"; 
	echo "</tr>\n";
	echo "<tr>\n";
	echo "<td class='wmtBody'>Comments:</td>\n";
	echo "</tr>\n";
	echo "<tr>\n";
  echo "  <td colspan='$comm_cols'><textarea name='visit_comments' id='visit_comments' class='wmtFullInput' rows='3'>";
	echo htmlspecialchars($dt['visit_comments'], ENT_QUOTES, '', FALSE);
	echo "</textarea></td>\n"; 
	echo "</tr>\n";
  ?>

</table>
<script type="text/javascript">

function calc_weight_gain(old_weight, new_weight, target)
{
	if(document.getElementById(old_weight) == null) return false;
	if(document.getElementById(new_weight) == null) return false;
	if(document.getElementById(target) == null) return false;
	var w1 = document.getElementById(old_weight).value;
	var w2 = document.getElementById(new_weight).value;
	
	if(isNaN(w1) || isNaN(w2)) return false;
	if(!w1 || !w2) return false;
	var w3 = w2 - w1;
	document.getElementById(target).value = w3.toFixed(2);
}

function calc_all_weights()
{
	var cnt = parseInt(0);
	while (cnt < <?php echo ($dt['tmp_first_empty'] + 2); ?>) {
		calc_weight_gain("pre_preg_wt", "pn_weight_"+cnt, "pn_gain_"+cnt);
		cnt++;
	}
	return true;
}

function visit_match_fill(form_dt, visit_dt, target, source)
{
	// alert("Form Date: "+form_dt);
	// alert("Visit Date: "+visit_dt);
	// alert("Target: "+target);
	// alert("Source: "+source);
	if(document.getElementById(form_dt) == null) return false;
	if(document.getElementById(visit_dt) == null) return false;
	if(document.getElementById(target) == null) return false;
	if(document.getElementById(form_dt).value != document.getElementById(visit_dt).value) return false;
	if(document.getElementById(target).value == '') document.getElementById(target).value = source;
}

function calc_weeks_gest(final_edd_field, visit_dt_field, target)
{
	// alert("Final EDD: "+final_edd_field);
	// alert("Visit Date: "+visit_dt_field);
	// alert("Target: "+target);
	if(document.getElementById(final_edd_field) == null) return false;
	if(document.getElementById(visit_dt_field) == null) return false;
	if(document.getElementById(target) == null) return false;
	var final_edd_dt = document.getElementById(final_edd_field).value;
	if(final_edd_dt == 0 || final_edd_dt == '') return false;	
	var visit_dt = document.getElementById(visit_dt_field).value;
	if(visit_dt == 0 || visit_dt == '') return false;	
	final_edd_dt = new Date(final_edd_dt);
	visit_dt = new Date(visit_dt);
	if(visit_dt == 'Invalid Date') {
		return false;
	}
	if(final_edd_dt == 'Invalid Date') {
		alert("Final EDD Is Not a Valid Date, Use 'YYYY-MM-DD' to Auto-Calc Weeks Gestation");
		return false;
	}
	var end_seconds = final_edd_dt.getTime();
	var start_seconds = visit_dt.getTime();
	var diff = (end_seconds - start_seconds);
	if(diff <= 0) {
		return false;
	}
	// This changes our difference to the number of days
	diff = Math.floor(diff / 86400000);
	// Now subtract this from days of gestation, figuring 282 for now
	diff = 280 - diff;
	// First get the leftover days to express as a fraction
	var days = diff % 7;
	// Then to weeks
	diff = (diff / 7);
	var weeks = Math.floor(diff);
	if(days) weeks = weeks + " " + days + "/" + 7;
	document.getElementById(target).value = weeks;
}

function calc_all_weeks()
{
	var cnt = parseInt(0);
	while (cnt < <?php echo ($dt['tmp_first_empty'] + 2); ?>) {
		calc_weeks_gest("upd_edd", "pn_dt_"+cnt, "pn_gest_"+cnt);
		cnt++;
	}
	return(true);
}
</script>

<?php ?>
