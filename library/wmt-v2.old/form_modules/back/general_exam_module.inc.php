<?php 
if(!isset($field_prefix)) $field_previx = '';
$include_exam_vitals = checkSettingMode('wmt::exam_vitals','',$frmdir);
if($include_exam_vitals != '') {
	$vitals_module = 'vitals_'.$include_exam_vitals.'_module.inc.php';
	if(is_file("./$vitals_module")) {
		include("./$vitals_module");
	} else if(is_file($GLOBALS['srcdir']."/wmt-v2/form_modules/".$vitals_module)) {
		include($GLOBALS['srcdir']."/wmt-v2/form_modules/".$vitals_module);
	}
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtLabel">Notes (for dictation):</td>
			<td>&nbsp;</td>
			<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('ee1_ge_dictate');" href="javascript:;"><span>Clear Dictation</span></a></div></td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ee1_ge_dictate" id="ee1_ge_dictate" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ee1_ge_dictate'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
		<tr>
			<td class="wmtLabel">Diagnostic Tests:</td>
			<td>&nbsp;</td>
			<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('ee1_diagnostics_nt');" href="javascript:;"><span>Clear Tests</span></a></div></td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ee1_diagnostics_nt" id="ee1_diagnostics_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ee1_diagnostics_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		<?php } ?>
		<tr>
			<td class="wmtBody"><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
			<td><div style="float: left; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="showAllExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
			<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="SetExamNormal('<?php echo $client_id; ?>','<?php echo $pat_sex; ?>');" href="javascript:;"><span>Set Exam ALL Normal</span></a></div></td>
			<td><div style="float: right; padding-right; 10px"><a class="css_button" tabindex="-1" onClick="ClearExam('<?php echo $client_id; ?>');" href="javascript:;"><span>Clear Exam</span></a></div></td>
		</tr>
		</table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_gen" id="tmp_ge_gen" type="checkbox" value="1" <?php echo (($dt['tmp_ge_gen'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_gen','tmp_ge_gen_disp','tmp_ge_gen_button_disp');" /><label for="tmp_ge_gen">General:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_gen_norm_exam" id="ee1_ge_gen_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_gen_norm_exam'] == 1)?' checked':''); ?> onChange="setGEGeneralNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_gen_norm_exam">Set General Exam Normal</label></td>
			<td><div name="tmp_ge_gen_button_disp" id="tmp_ge_gen_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_gen_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_gen_');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_gen_disp" style="display: <?php echo $dt['tmp_ge_gen_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php 
		EE1_GECheckLine('ee1_ge_gen','norm','Normal Habitus',$dt{'ee1_ge_gen_norm'},$dt{'ee1_ge_gen_norm_nt'}, 'width: 75%;');
		EE1_GECheckLine('ee1_ge_gen','dev','Well Developed',$dt{'ee1_ge_gen_dev'},$dt{'ee1_ge_gen_dev_nt'});
		EE1_GECheckLine('ee1_ge_gen','groom','Well Groomed',$dt{'ee1_ge_gen_groom'},$dt{'ee1_ge_gen_groom_nt'});
		EE1_GECheckLine('ee1_ge_gen','dis','No Acute Distress',$dt{'ee1_ge_gen_dis'},$dt{'ee1_ge_gen_dis_nt'});
		if($client_id != 'sfa') {
			EE1_GESelLine('ee1_ge_gen','jaun','Jaundice',$dt{'ee1_ge_gen_jaun'},$dt{'ee1_ge_gen_jaun_nt'});
			EE1_GESelLine('ee1_ge_gen','waste','Wasting',$dt{'ee1_ge_gen_waste'},$dt{'ee1_ge_gen_waste_nt'});
			EE1_GESelLine('ee1_ge_gen','sleep','Sleep Pattern',$dt{'ee1_ge_gen_sleep'},$dt{'ee1_ge_gen_sleep_nt'}, 'NormAbnorm');
		}
		?>
		<tr>
			<td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ee1_ge_gen_nt" id="ee1_ge_gen_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_gen_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_head" id="tmp_ge_head" type="checkbox" value="1" <?php echo (($dt['tmp_ge_head'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_head','tmp_ge_head_disp','tmp_ge_head_button_disp');" /><label for="tmp_ge_head">Head:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_hd_norm_exam" id="ee1_ge_hd_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_hd_norm_exam'] == 1)?' checked':''); ?> onChange="setGEHeadNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_hd_norm_exam">Set Head Exam Normal</label></td>
			<td><div name="tmp_ge_head_button_disp" id="tmp_ge_head_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_head_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_hd_');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_head_disp" style="display: <?php echo $dt['tmp_ge_head_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
		EE1_GECheckLine('ee1_ge_hd','atra','Atraumatic',$dt{'ee1_ge_hd_atra'},$dt{'ee1_ge_hd_atra_nt'}, 'width: 70%;');
		EE1_GECheckLine('ee1_ge_hd','norm','Normocephalic',$dt{'ee1_ge_hd_norm'},$dt{'ee1_ge_hd_norm_nt'});
		// if($client_id == 'sfa') {
			EE1_GESelLine('ee1_ge_hd','feat','Facial Features',$dt{'ee1_ge_hd_feat'},$dt{'ee1_ge_hd_feat_nt'}, 'Facial_Features');
			EE1_GESelLine('ee1_ge_hd','ant','Anterior Fontanel',$dt{'ee1_ge_hd_ant'},$dt{'ee1_ge_hd_ant_nt'}, 'ant_font');
			$hd_list = array('head_mid-line', 'deformity', 'lesion', 'flaky_scalp',
				'nits_visible', 'edema', 'erythema');
			EE1_GEMultiCheckLine('ee1_ge_hd', $hd_list, $hd_chks);
		// }
		?>
    <tr>
      <td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ee1_ge_hd_nt" id="ee1_ge_hd_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_hd_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_eyes" id="tmp_ge_eyes" type="checkbox" value="1" <?php echo (($dt['tmp_ge_eyes'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_eyes','tmp_ge_eyes_disp','tmp_ge_eyes_button_disp');" /><label for="tmp_ge_eyes">Eyes:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_eye_norm_exam" id="ee1_ge_eye_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_eye_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEyesNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_eye_norm_exam">Set Eye Exam Normal</label></td>
			<td><div name="tmp_ge_eyes_button_disp" id="tmp_ge_eyes_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_eyes_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_eye');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_eyes_disp" style="display: <?php echo $dt['tmp_ge_eyes_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ee1_ge_eye','pupil','Pupils',$dt{'ee1_ge_eye_pupil'},$dt{'ee1_ge_eye_pupil_nt'}, 'EE1_Pupil', 'width: 70%;', false);
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Fundoscopic</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_eye','hem','Hemorrhage',$dt{'ee1_ge_eye_hem'},$dt{'ee1_ge_eye_hem_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ee1_ge_eye','exu','Exudate',$dt{'ee1_ge_eye_exu'},$dt{'ee1_ge_eye_exu_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ee1_ge_eye','av','AV Nicking',$dt{'ee1_ge_eye_av'},$dt{'ee1_ge_eye_av_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ee1_ge_eye','pap','Papilledema',$dt{'ee1_ge_eye_pap'},$dt{'ee1_ge_eye_pap_nt'}, 'Yes_No', '', false);
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Eye</td>
    	</tr>
			<?php
			if($client_id == 'sfa') {
				EE1_GECheckLine('ee1_ge_eyer','norm','No Abnormalities',$dt{'ee1_ge_eyer_norm'},$dt{'ee1_ge_eyer_norm_nt'});
			} else {
				EE1_GESelLine('ee1_ge_eyer','norm','No Abnormalities',$dt{'ee1_ge_eyer_norm'},$dt{'ee1_ge_eyer_norm_nt'}, 'NormAbnorm');
			}
			EE1_GESelLine('ee1_ge_eyer','exo','Exophthalmos',$dt{'ee1_ge_eyer_exo'},$dt{'ee1_ge_eyer_exo_nt'});
			EE1_GESelLine('ee1_ge_eyer','stare','Stare',$dt{'ee1_ge_eyer_stare'},$dt{'ee1_ge_eyer_stare_nt'});
			EE1_GESelLine('ee1_ge_eyer','lag','Lid Lag',$dt{'ee1_ge_eyer_lag'},$dt{'ee1_ge_eyer_lag_nt'});
			EE1_GECheckLine('ee1_ge_eyer','scleral','No Scleral Injection',$dt{'ee1_ge_eyer_scleral'},$dt{'ee1_ge_eyer_scleral_nt'});
			EE1_GESelLine('ee1_ge_eyer','eomi','EOMI',$dt{'ee1_ge_eyer_eomi'},$dt{'ee1_ge_eyer_eomi_nt'});
			EE1_GESelLine('ee1_ge_eyer','perrl','PERRL',$dt{'ee1_ge_eyer_perrl'},$dt{'ee1_ge_eyer_perrl_nt'});
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Eye</td>
    	</tr>
			<?php
			if($client_id == 'sfa') {
				EE1_GECheckLine('ee1_ge_eyel','norm','No Abnormalities',$dt{'ee1_ge_eyel_norm'},$dt{'ee1_ge_eyel_norm_nt'});
			} else {
				EE1_GESelLine('ee1_ge_eyel','norm','No Abnormalities',$dt{'ee1_ge_eyel_norm'},$dt{'ee1_ge_eyel_norm_nt'}, 'NormAbnorm');
			}
			EE1_GESelLine('ee1_ge_eyel','exo','Exophthalmos',$dt{'ee1_ge_eyel_exo'},$dt{'ee1_ge_eyel_exo_nt'});
			EE1_GESelLine('ee1_ge_eyel','stare','Stare',$dt{'ee1_ge_eyel_stare'},$dt{'ee1_ge_eyel_stare_nt'});
			EE1_GESelLine('ee1_ge_eyel','lag','Lid Lag',$dt{'ee1_ge_eyel_lag'},$dt{'ee1_ge_eyel_lag_nt'});
			EE1_GECheckLine('ee1_ge_eyel','scleral','No Scleral Injection',$dt{'ee1_ge_eyel_scleral'},$dt{'ee1_ge_eyel_scleral_nt'});
			EE1_GESelLine('ee1_ge_eyel','eomi','EOMI',$dt{'ee1_ge_eyel_eomi'},$dt{'ee1_ge_eyel_eomi_nt'});
			EE1_GESelLine('ee1_ge_eyel','perrl','PERRL',$dt{'ee1_ge_eyel_perrl'},$dt{'ee1_ge_eyel_perrl_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_eye_nt" id="ee1_ge_eye_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_eye_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_ears" id="tmp_ge_ears" type="checkbox" value="1" <?php echo (($dt['tmp_ge_ears'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_ears','tmp_ge_ears_disp','tmp_ge_ears_button_disp');" /><label for="tmp_ge_ears">Ears:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_ear_norm_exam" id="ee1_ge_ear_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_ear_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEarsNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_ear_norm_exam">Set Ear Exam Normal</label></td>
			<td><div name="tmp_ge_ears_button_disp" id="tmp_ge_ears_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_ears_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ear');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_ears_disp" style="display: <?php echo $dt['tmp_ge_ears_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Ear</td>
    	</tr>
    	<tr>
				<td>&nbsp;</td>
      	<td class="wmtBody">Tympanic Membrane</td>
				<td style="width: 70%;"><input name="ee1_ge_earr_tym_nt" id="ee1_ge_earr_tym_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_earr_tym_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_earr','clear','Clear',$dt{'ee1_ge_earr_clear'},$dt{'ee1_ge_earr_clear_nt'});
			EE1_GESelLine('ee1_ge_earr','perf','Perforation',$dt{'ee1_ge_earr_perf'},$dt{'ee1_ge_earr_perf_nt'});
			EE1_GESelLine('ee1_ge_earr','ret','Retraction',$dt{'ee1_ge_earr_ret'},$dt{'ee1_ge_earr_ret_nt'});
			EE1_GESelLine('ee1_ge_earr','bulge','Bulging',$dt{'ee1_ge_earr_bulge'},$dt{'ee1_ge_earr_bulge_nt'});
			$label = ($client_id == 'sfa') ? 'Drainage' : 'Pus';
			EE1_GESelLine('ee1_ge_earr','pus','Drainage',$dt{'ee1_ge_earr_pus'},$dt{'ee1_ge_earr_pus_nt'}, 'ear_drain');
			EE1_GESelLine('ee1_ge_earr','ceru','Cerumen',$dt{'ee1_ge_earr_ceru'},$dt{'ee1_ge_earr_ceru_nt'});
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Ear</td>
    	</tr>
    	<tr>
				<td>&nbsp;</td>
      	<td class="wmtBody">Tympanic Membrane</td>
				<td><input name="ee1_ge_earl_tym_nt" id="ee1_ge_earl_tym_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_earl_tym_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_earl','clear','Clear',$dt{'ee1_ge_earl_clear'},$dt{'ee1_ge_earl_clear_nt'});
			EE1_GESelLine('ee1_ge_earl','perf','Perforation',$dt{'ee1_ge_earl_perf'},$dt{'ee1_ge_earl_perf_nt'});
			EE1_GESelLine('ee1_ge_earl','ret','Retraction',$dt{'ee1_ge_earl_ret'},$dt{'ee1_ge_earl_ret_nt'});
			EE1_GESelLine('ee1_ge_earl','bulge','Bulging',$dt{'ee1_ge_earl_bulge'},$dt{'ee1_ge_earl_bulge_nt'});
			$label = ($client_id == 'sfa') ? 'Drainage' : 'Pus';
			EE1_GESelLine('ee1_ge_earl','pus','Drainage',$dt{'ee1_ge_earl_pus'},$dt{'ee1_ge_earl_pus_nt'}, 'ear_drain');
			EE1_GESelLine('ee1_ge_earl','ceru','Cerumen',$dt{'ee1_ge_earl_ceru'},$dt{'ee1_ge_earl_ceru_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_ear_nt" id="ee1_ge_ear_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_ear_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_nose" id="tmp_ge_nose" type="checkbox" value="1" <?php echo (($dt['tmp_ge_nose'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_nose','tmp_ge_nose_disp','tmp_ge_nose_button_disp');" /><label for="tmp_ge_nose">Nose:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_nose_norm_exam" id="ee1_ge_nose_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_nose_norm_exam'] == 1)?' checked':''); ?> onChange="setGENoseNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_nose_norm_exam">Set Nose Exam Normal</label></td>
			<td><div name="tmp_ge_nose_button_disp" id="tmp_ge_nose_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_nose_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_nose_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_nose_disp" style="display: <?php echo $dt['tmp_ge_nose_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtBody" colspan="2">Nasal Mucosa</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_nose','ery','Erythema',$dt{'ee1_ge_nose_ery'},$dt{'ee1_ge_nose_ery_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ee1_ge_nose','swell','Swelling',$dt{'ee1_ge_nose_swell'},$dt{'ee1_ge_nose_swell_nt'});
			EE1_GESelLine('ee1_ge_nose','pall','Pallor',$dt{'ee1_ge_nose_pall'},$dt{'ee1_ge_nose_pall_nt'});
			EE1_GESelLine('ee1_ge_nose','polps','Polyps',$dt{'ee1_ge_nose_polps'},$dt{'ee1_ge_nose_polps_nt'});
			EE1_GESelLine('ee1_ge_nose','sept','Septum',$dt{'ee1_ge_nose_sept'},$dt{'ee1_ge_nose_sept_nt'},'EE1_Septum');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_nose_nt" id="ee1_ge_nose_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_nose_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_mouth" id="tmp_ge_mouth" type="checkbox" value="1" <?php echo (($dt['tmp_ge_mouth'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_mouth','tmp_ge_mouth_disp','tmp_ge_mouth_button_disp');" /><label for="tmp_ge_mouth">Mouth:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_mouth_norm_exam" id="ee1_ge_mouth_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_mouth_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMouthNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_mouth_norm_exam">Set Mouth Exam Normal</label></td>
			<td><div name="tmp_ge_mouth_button_disp" id="tmp_ge_mouth_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_mouth_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_mouth_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_mouth_disp" style="display: <?php echo $dt['tmp_ge_mouth_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ee1_ge_mouth','moist','Moist Mucus Membranes',$dt{'ee1_ge_mouth_moist'},$dt{'ee1_ge_mouth_moist_nt'}, 'width: 70%;');
			if($client_id == 'cffm') {
				EE1_GECheckLine('ee1_ge_mouth','les','Clear of Suspicious Lesions',$dt{'ee1_ge_mouth_les'},$dt{'ee1_ge_mouth_les_nt'});
				EE1_GESelLine('ee1_ge_mouth','dent','Dentition',$dt{'ee1_ge_mouth_dent'},$dt{'ee1_ge_mouth_dent_nt'},'EE1_Denture');
			}
			?>
			<tr>
				<td class="wmtBody" colspan="2">Gums</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_mouth','gm_red','Reddened',$dt{'ee1_ge_mouth_gm_red'},$dt{'ee1_ge_mouth_gm_red_nt'});
			EE1_GESelLine('ee1_ge_mouth','gm_swell','Swollen',$dt{'ee1_ge_mouth_gm_swell'},$dt{'ee1_ge_mouth_gm_swell_nt'});
			EE1_GESelLine('ee1_ge_mouth','gm_bld','Bleeding',$dt{'ee1_ge_mouth_gm_bld'},$dt{'ee1_ge_mouth_gm_bld_nt'});
			?>
			<tr>
				<td class="wmtBody" colspan="2">Teeth</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_mouth','th_car','Caries',$dt{'ee1_ge_mouth_th_car'},$dt{'ee1_ge_mouth_th_car_nt'});
			EE1_GESelLine('ee1_ge_mouth','th_pd','Poor Dentition',$dt{'ee1_ge_mouth_th_pd'},$dt{'ee1_ge_mouth_th_pd_nt'});
			EE1_GESelLine('ee1_ge_mouth','th_er','Erupting',$dt{'ee1_ge_mouth_th_er'},$dt{'ee1_ge_mouth_th_er_nt'});
			$mouth_list = array( 'sores', 'cracked,_dry_lips', 'cheilosis',
				'perioral_cyanosis');
			EE1_GEMultiCheckLine('ee1_ge_mouth', $mouth_list, $mouth_chks);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_mouth_nt" id="ee1_ge_mouth_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_mouth_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_throat" id="tmp_ge_throat" type="checkbox" value="1" <?php echo (($dt['tmp_ge_throat'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_throat','tmp_ge_throat_disp','tmp_ge_throat_button_disp');" /><label for="tmp_ge_throat">Throat:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_thrt_norm_exam" id="ee1_ge_thrt_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_thrt_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThroatNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_thrt_norm_exam">Set Throat Exam Normal</label></td>
			<td><div name="tmp_ge_throat_button_disp" id="tmp_ge_throat_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_throat_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thrt_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_throat_disp" style="display: <?php echo $dt['tmp_ge_throat_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ee1_ge_thrt','ery','No Erythema',$dt{'ee1_ge_thrt_ery'},$dt{'ee1_ge_thrt_ery_nt'}, 'width: 70%;');
			EE1_GECheckLine('ee1_ge_thrt','exu','No Exudate',$dt{'ee1_ge_thrt_exu'},$dt{'ee1_ge_thrt_exu_nt'});
			?>
			<tr>
				<td class="wmtBody" colspan="2">Tonsils</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_thrt','ton_exu','Exudate',$dt{'ee1_ge_thrt_ton_exu'},$dt{'ee1_ge_thrt_ton_exu_nt'});
			EE1_GESelLine('ee1_ge_thrt','ton_en','Enlarged Size',$dt{'ee1_ge_thrt_ton_en'},$dt{'ee1_ge_thrt_ton_en_nt'}, 'tonsil_size');
			?>
			<tr>
				<td class="wmtBody" colspan="2">Uvula</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_thrt','uvu_mid','Midline',$dt{'ee1_ge_thrt_uvu_mid'},$dt{'ee1_ge_thrt_uvu_mid_nt'});
			EE1_GESelLine('ee1_ge_thrt','uvu_swell','Swollen',$dt{'ee1_ge_thrt_uvu_swell'},$dt{'ee1_ge_thrt_uvu_swell_nt'});
			EE1_GESelLine('ee1_ge_thrt','uvu_dev','Deviated',$dt{'ee1_ge_thrt_uvu_dev'},$dt{'ee1_ge_thrt_uvu_dev_nt'}, 'left_right');
			?>
			<tr>
				<td class="wmtBody" colspan="2">Palate</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_thrt','pal_swell','Swelling',$dt{'ee1_ge_thrt_pal_swell'},$dt{'ee1_ge_thrt_pal_swell_nt'});
			EE1_GESelLine('ee1_ge_thrt','pal_pet','Petechiae',$dt{'ee1_ge_thrt_pal_pet'},$dt{'ee1_ge_thrt_pal_pet_nt'});
			$thrt_list = array( 'peritonsillar_abscess', 'cobblestoning',
				'mucous_visible');
			EE1_GEMultiCheckLine('ee1_ge_thrt', $thrt_list, $thrt_chks);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_thrt_nt" id="ee1_ge_thrt_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_thrt_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_neck" id="tmp_ge_neck" type="checkbox" value="1" <?php echo (($dt['tmp_ge_neck'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_neck','tmp_ge_neck_disp','tmp_ge_neck_button_disp');" /><label for="tmp_ge_neck">Neck:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_nk_norm_exam" id="ee1_ge_nk_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_nk_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeckNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_nk_norm_exam">Set Neck Exam Normal</label></td>
			<td><div name="tmp_ge_neck_button_disp" id="tmp_ge_neck_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_neck_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>','ge_nk_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_neck_disp" style="display: <?php echo $dt['tmp_ge_neck_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
      	<td class="wmtBody wmtR"><input name="ee1_ge_nk_sup" id="ee1_ge_nk_sup" type="checkbox" value="1" <?php echo (($dt['ee1_ge_nk_sup'] == 1)?' checked':''); ?> onChange="document.getElementById('ee1_ge_nk_norm_exam').checked=false;" /></td>
				<td class="wmtBody"><label for="ee1_ge_nk_sup">Supple</label></td>
				<td class="wmtBody" style="width: 70%;">Notes:</td>
			</tr>
			<?php
			// EE1_GECheckLine('ee1_ge_nk','sup','Supple',$dt{'ee1_ge_nk_sup'},$dt{'ee1_ge_nk_sup_nt'}, 'width: 70%;');
			EE1_GESelTextArea('ee1_ge_nk','brit','Bruit',$dt{'ee1_ge_nk_brit'},$dt{'ee1_ge_nk_brit_nt'});
			EE1_GESelLine('ee1_ge_nk','jvp','JVP',$dt{'ee1_ge_nk_jvp'},'','Yes_No','',true,true);
			EE1_GESelLine('ee1_ge_nk','lymph','Lymphadenopathy',$dt{'ee1_ge_nk_lymph'},'','Yes_No','',true,true);
			EE1_GECheckLine('ee1_ge_nk','trach','Trachea Midline',$dt{'ee1_ge_nk_trach'},'', '', true, true);
			?>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_thyroid" id="tmp_ge_thyroid" type="checkbox" value="1" <?php echo (($dt['tmp_ge_thyroid'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_thyroid','tmp_ge_thyroid_disp','tmp_ge_thyroid_button_disp');" /><label for="tmp_ge_thyroid">Thyroid:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_thy_norm_exam" id="ee1_ge_thy_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_thy_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThyroidNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_thy_norm_exam">Set Thyroid Exam Normal</label></td>
			<td><div name="tmp_ge_thyroid_button_disp" id="tmp_ge_thyroid_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_thyroid_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thy_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_thyroid_disp" style="display: <?php echo $dt['tmp_ge_thyroid_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ee1_ge_thy','norm','Normal Size',$dt{'ee1_ge_thy_norm'},$dt{'ee1_ge_thy_norm_nt'}, 'width: 70%;');
			EE1_GESelLine('ee1_ge_thy','nod','Nodules',$dt{'ee1_ge_thy_nod'},$dt{'ee1_ge_thy_nod_nt'});
			if($client_id != 'cffm' && $client_id != 'sfa') {
				EE1_GESelLine('ee1_ge_thy','brit','Bruit',$dt{'ee1_ge_thy_brit'},$dt{'ee1_ge_thy_brit_nt'});
			}
			EE1_GESelLine('ee1_ge_thy','tnd','Tenderness',$dt{'ee1_ge_thy_tnd'},$dt{'ee1_ge_thy_tnd_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_thy_nt" id="ee1_ge_thy_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_thy_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_lymph" id="tmp_ge_lymph" type="checkbox" value="1" <?php echo (($dt['tmp_ge_lymph'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_lymph','tmp_ge_lymph_disp','tmp_ge_lymph_button_disp');" /><label for="tmp_ge_lymph">Lymphadenopathy:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_lym_norm_exam" id="ee1_ge_lym_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_lym_norm_exam'] == 1)?' checked':''); ?> onChange="setGELymphNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_lym_norm_exam">Set Lymphadenopathy Exam Normal</label></td>
			<td><div name="tmp_ge_lymph_button_disp" id="tmp_ge_lymph_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_lymph_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_lym_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_lymph_disp" style="display: <?php echo $dt['tmp_ge_lymph_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ee1_ge_lym','cerv','Cervical',$dt{'ee1_ge_lym_cerv'},$dt{'ee1_ge_lym_cerv_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ee1_ge_lym','sup','Supraclavicular',$dt{'ee1_ge_lym_sup'},$dt{'ee1_ge_lym_sup_nt'});
			EE1_GESelLine('ee1_ge_lym','ax','Axillary',$dt{'ee1_ge_lym_ax'},$dt{'ee1_ge_lym_ax_nt'});
			EE1_GESelLine('ee1_ge_lym','in','Inguinal',$dt{'ee1_ge_lym_in'},$dt{'ee1_ge_lym_in_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_lym_nt" id="ee1_ge_lym_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_lym_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_breast" id="tmp_ge_breast" type="checkbox" value="1" <?php echo (($dt['tmp_ge_breast'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_breast','tmp_ge_breast_disp','tmp_ge_breast_button_disp');" /><label for="tmp_ge_breast">Breasts:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_br_norm_exam" id="ee1_ge_br_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_br_norm_exam'] == 1)?' checked':''); ?> onChange="setGEBreastNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_br_norm_exam">Set Breast Exam Normal</label></td>
			<td><div name="tmp_ge_breast_button_disp" id="tmp_ge_breast_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_breast_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_br'); clearGESection('<?php echo $client_id; ?>', 'ge_nip'); "><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_breast_disp" style="display: <?php echo $dt['tmp_ge_breast_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			if($client_id == 'cffm' || $client_id == 'sfa') {
				EE1_GESelLine('ee1_ge_br','sym','Symmetrical',$dt{'ee1_ge_br_sym'},$dt{'ee1_ge_br_sym_nt'}, 'Yes_No', 'width: 70%;');
			}
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Breast</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_brr','axil','Axillary Nodes',$dt{'ee1_ge_brr_axil'},$dt{'ee1_ge_brr_axil_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ee1_ge_brr','mass','Mass/Lesion',$dt{'ee1_ge_brr_mass'},$dt{'ee1_ge_brr_mass_nt'});
			EE1_GESelLine('ee1_ge_brr','tan','Tanner',$dt{'ee1_ge_brr_tan'},$dt{'ee1_ge_brr_tan_nt'}, 'one_to_five');
			EE1_GESelLine('ee1_ge_brr','chng','Skin Changes',$dt{'ee1_ge_brr_chng'},$dt{'ee1_ge_brr_chng_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_brr_nt" id="ee1_ge_brr_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_brr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Nipple</td>
    	</tr>

			<?php
			EE1_GESelLine('ee1_ge_nipr','ev','Everted',$dt{'ee1_ge_nipr_ev'},$dt{'ee1_ge_nipr_ev_nt'});
			EE1_GESelLine('ee1_ge_nipr','in','Inverted',$dt{'ee1_ge_nipr_in'},$dt{'ee1_ge_nipr_in_nt'});
			EE1_GESelLine('ee1_ge_nipr','mass','Mass',$dt{'ee1_ge_nipr_mass'},$dt{'ee1_ge_nipr_mass_nt'});
			EE1_GESelLine('ee1_ge_nipr','dis','Discharge',$dt{'ee1_ge_nipr_dis'},$dt{'ee1_ge_nipr_dis_nt'});
			EE1_GESelLine('ee1_ge_nipr','ret','Retraction',$dt{'ee1_ge_nipr_ret'},$dt{'ee1_ge_nipr_ret_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_nipr_nt" id="ee1_ge_nipr_nt" class="FullInput" rows="2"><?php echo htmlspecialchars($dt{'ee1_ge_nipr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Breast</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_brl','axil','Axillary Nodes',$dt{'ee1_ge_brl_axil'},$dt{'ee1_ge_brl_axil_nt'});
			EE1_GESelLine('ee1_ge_brl','mass','Mass/Lesion',$dt{'ee1_ge_brl_mass'},$dt{'ee1_ge_brl_mass_nt'});
			EE1_GESelLine('ee1_ge_brl','tan','Tanner',$dt{'ee1_ge_brl_tan'},$dt{'ee1_ge_brl_tan_nt'}, 'one_to_five');
			EE1_GESelLine('ee1_ge_brl','chng','Skin Changes',$dt{'ee1_ge_brl_chng'},$dt{'ee1_ge_brl_chng_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_brl_nt" id="ee1_ge_brl_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_brl_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Nipple</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_nipl','ev','Everted',$dt{'ee1_ge_nipl_ev'},$dt{'ee1_ge_nipl_ev_nt'});
			EE1_GESelLine('ee1_ge_nipl','in','Inverted',$dt{'ee1_ge_nipl_in'},$dt{'ee1_ge_nipl_in_nt'});
			EE1_GESelLine('ee1_ge_nipl','mass','Mass',$dt{'ee1_ge_nipl_mass'},$dt{'ee1_ge_nipl_mass_nt'});
			EE1_GESelLine('ee1_ge_nipl','dis','Discharge',$dt{'ee1_ge_nipl_dis'},$dt{'ee1_ge_nipl_dis_nt'});
			EE1_GESelLine('ee1_ge_nipl','ret','Retraction',$dt{'ee1_ge_nipl_ret'},$dt{'ee1_ge_nipl_ret_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_nipl_nt" id="ee1_ge_nipl_nt" class="FullInput" rows="2"><?php echo htmlspecialchars($dt{'ee1_ge_nipl_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_cardio" id="tmp_ge_cardio" type="checkbox" value="1" <?php echo (($dt['tmp_ge_cardio'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_cardio','tmp_ge_cardio_disp','tmp_ge_cardio_button_disp');" /><label for="tmp_ge_cardio">Cardiovascular:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_cr_norm_exam" id="ee1_ge_cr_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_cr_norm_exam'] == 1)?' checked':''); ?> onChange="setGECardioNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_cr_norm_exam">Set Cardiovascular Exam Normal</label></td>
			<td><div name="tmp_ge_cardio_button_disp" id="tmp_ge_cardio_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_cardio_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_cr_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_cardio_disp" style="display: <?php echo $dt['tmp_ge_cardio_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ee1_ge_cr','norm','Regular Rate &amp; Rhythm',$dt{'ee1_ge_cr_norm'},$dt{'ee1_ge_cr_norm_nt'}, 'Yes_No', 'width: 70%;');
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ee1_ge_cr_mur" id="ee1_ge_cr_mur" class="Input" onchange="toggleLineDetail(this, 'ee1_ge_cr_mur_dtl', 'tmp_mur_dtl'); document.getElementById('ee1_ge_cr_norm_exam').checked=false;" >
					<?php ListSel($dt{'ee1_ge_cr_mur'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Murmur&nbsp;&nbsp;
					<select name="ee1_ge_cr_mur_dtl" id="ee1_ge_cr_mur_dtl" class="Input" style="display: <?php echo (($dt{'ee1_ge_cr_mur'} == 'y') ? 'inline' : 'none'); ?>;" ><?php ListSel($dt{'ee1_ge_cr_mur_dtl'}, 'one_to_six'); ?></select><span id="tmp_mur_dtl" style="display: <?php echo (($dt{'ee1_ge_cr_mur'} == 'y') ? 'inline' : 'none'); ?>" >&nbsp;&nbsp;/&nbsp;6</span></td>
      	<td><input name="ee1_ge_cr_mur_nt" id="ee1_ge_cr_mur_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_cr_mur_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ee1_ge_cr_norm_exam').checked=false;" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_cr','gall','Gallops',$dt{'ee1_ge_cr_gall'},$dt{'ee1_ge_cr_gall_nt'});
			EE1_GESelLine('ee1_ge_cr','click','Clicks',$dt{'ee1_ge_cr_click'},$dt{'ee1_ge_cr_click_nt'});
			EE1_GESelLine('ee1_ge_cr','rubs','Rubs',$dt{'ee1_ge_cr_rubs'},$dt{'ee1_ge_cr_rubs_nt'});
			EE1_GESelLine('ee1_ge_cr','extra','Extra Sound',$dt{'ee1_ge_cr_extra'},$dt{'ee1_ge_cr_extra_nt'});
			EE1_GESelLine('ee1_ge_cr','pmi','PMI in 5th ICS in MCL',$dt{'ee1_ge_cr_pmi'},$dt{'ee1_ge_cr_pmi_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ee1_ge_cr_nt" id="ee1_ge_cr_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_cr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_pulmo" id="tmp_ge_pulmo" type="checkbox" value="1" <?php echo (($dt['tmp_ge_pulmo'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_pulmo','tmp_ge_pulmo_disp','tmp_ge_pulmo_button_disp');" /><label for="tmp_ge_pulmo">Pulmonary:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_pul_norm_exam" id="ee1_ge_pul_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_pul_norm_exam'] == 1)?' checked':''); ?> onChange="setGEPulmoNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_pul_norm_exam">Set Pulmonary Exam Normal</label></td>
			<td><div name="tmp_ge_pulmo_button_disp" id="tmp_ge_pulmo_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_pulmo_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_pul_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_pulmo_disp" style="display: <?php echo $dt['tmp_ge_pulmo_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    	<tr>
      	<td class="wmtBody wmtR"><select name="ee1_ge_pul_clear" id="ee1_ge_pul_clear" class="Input" onChange="document.getElementById('ee1_ge_pul_norm_exam').checked=false;" >
					<?php ListSel($dt{'ee1_ge_pul_clear'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Clear to Auscultation</td>
      	<td class="wmtBody" style="width: 70%;">Notes:</td>
    	</tr>
			<?php
			EE1_GESelTextArea('ee1_ge_pul','rales','Rales',$dt{'ee1_ge_pul_rales'},$dt{'ee1_ge_pul_rales_nt'}, 'Yes_No', '', true, '5');
			EE1_GESelLine('ee1_ge_pul','whz','Wheezes',$dt{'ee1_ge_pul_whz'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ee1_ge_pul','ron','Rhonchi',$dt{'ee1_ge_pul_ron'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ee1_ge_pul','dec','Decreased Breath Sounds',$dt{'ee1_ge_pul_dec'},'', 'Yes_No', '', true, true);
			EE1_GESelLine('ee1_ge_pul','crack','Crackles',$dt{'ee1_ge_pul_crack'}, '', 'Yes_No', '', true, true);
			?>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_gastro" id="tmp_ge_gastro" type="checkbox" value="1" <?php echo (($dt['tmp_ge_gastro'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_gastro','tmp_ge_gastro_disp','tmp_ge_gastro_button_disp');" /><label for="tmp_ge_gastro">Gastrointestinal:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_gi_norm_exam" id="ee1_ge_gi_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_gi_norm_exam'] == 1)?' checked':''); ?> onChange="setGEGastroNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_gi_norm_exam">Set Gastrointestinal Exam Normal</label></td>
			<td><div name="tmp_ge_gastro_button_disp" id="tmp_ge_gastro_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_gastro_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_gi_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_gastro_disp" style="display: <?php echo $dt['tmp_ge_gastro_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php
			EE1_GESelLine('ee1_ge_gi','soft','Soft',$dt{'ee1_ge_gi_soft'},$dt{'ee1_ge_gi_soft_nt'}, 'Yes_No', 'width: 70%;');
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ee1_ge_gi_tend" id="ee1_ge_gi_tend" class="Input" onchange="document.getElementById('ee1_ge_gi_norm_exam').checked = false;" >
					<?php
					if($client_id == 'sfa') {
						ListSel($dt{'ee1_ge_gi_tend'},'Yes_No');
					} else {
						ListSel($dt{'ee1_ge_gi_tend'},'EE1_Tender');
					}
					?>
				</select></td>
      	<td class="wmtBody">Tender&nbsp;&nbsp;<select name="ee1_ge_gi_tend_loc" id="ee1_ge_gi_tend_loc" class="Input" onchange="document.getElementById('ee1_ge_gi_norm_exam').checked = false;" >
					<?php ListSel($dt{'ee1_ge_gi_tend_loc'},'EE1_GI_Location'); ?>
				</select></td>
				<td><input name="ee1_ge_gi_tend_nt" id="ee1_ge_gi_tend_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_gi_tend_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ee1_ge_gi_norm_exam').checked=false;" /></td> 
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_gi','dis','Distended',$dt{'ee1_ge_gi_dis'},$dt{'ee1_ge_gi_dis_nt'}, 'EE1_Distended');
			EE1_GESelLine('ee1_ge_gi','scar','Scar',$dt{'ee1_ge_gi_scar'},$dt{'ee1_ge_gi_scar_nt'});
			EE1_GESelLine('ee1_ge_gi','asc','Ascites',$dt{'ee1_ge_gi_asc'},$dt{'ee1_ge_gi_asc_nt'});
			EE1_GESelLine('ee1_ge_gi','pnt','Point Tenderness',$dt{'ee1_ge_gi_pnt'},$dt{'ee1_ge_gi_pnt_nt'});
			EE1_GESelLine('ee1_ge_gi','grd','Guarding',$dt{'ee1_ge_gi_grd'},$dt{'ee1_ge_gi_grd_nt'});
			EE1_GESelLine('ee1_ge_gi','reb','Rebound',$dt{'ee1_ge_gi_reb'},$dt{'ee1_ge_gi_reb_nt'});
			EE1_GESelLine('ee1_ge_gi','mass','Mass',$dt{'ee1_ge_gi_mass'},$dt{'ee1_ge_gi_mass_nt'});
			EE1_GESelLine('ee1_ge_gi','hern','Hernia',$dt{'ee1_ge_gi_hern'},$dt{'ee1_ge_gi_hern_nt'});
			$hrn_list = array( 'ventral', 'incisional', 'umbilical', 'inguinal');
			EE1_GEMultiCheckLine('ee1_ge_gi_her', $hrn_list, $hrn_chks);
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ee1_ge_gi_bowel" id="ee1_ge_gi_bowel" class="Input" onchange="toggleLineDetail(this, 'ee1_ge_gi_bwl_dtl'); document.getElementById('ee1_ge_gi_norm_exam').checked=false;" >
					<?php ListSel($dt{'ee1_ge_gi_bowel'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Bowel Sounds&nbsp;&nbsp;
					<select name="ee1_ge_gi_bwl_dtl" id="ee1_ge_gi_bwl_dtl" class="Input" style="display: <?php echo (($dt{'ee1_ge_gi_bowel'} == 'y')?'inline':'none'); ?>;" ><?php ListSel($dt{'ee1_ge_gi_bwl_dtl'}, 'bowel_detail'); ?></select></span></td>
      	<td><input name="ee1_ge_gi_bowel_nt" id="ee1_ge_gi_bowel_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_gi_bowel_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ee1_ge_gi_norm_exam').checked=false;" /></td>
    	</tr>

			<?php
			EE1_GESelLine('ee1_ge_gi','hepa','Hepatomegaly',$dt{'ee1_ge_gi_hepa'},$dt{'ee1_ge_gi_hepa_nt'});
			EE1_GESelLine('ee1_ge_gi','spleno','Splenomegaly',$dt{'ee1_ge_gi_spleno'},$dt{'ee1_ge_gi_spleno_nt'});
			?>
			<tr>
				<td class="wmtLabel">Notes:</td>
			<tr>
				<td colspan="3"><textarea name="ee1_ge_gi_nt" id="ee1_ge_gi_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_gi_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_neuro" id="tmp_ge_neuro" type="checkbox" value="1" <?php echo (($dt['tmp_ge_neuro'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_neuro','tmp_ge_neuro_disp','tmp_ge_neuro_button_disp');" /><label for="tmp_ge_neuro">Neurological:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_neu_norm_exam" id="ee1_ge_neu_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_neu_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeuroNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_neu_norm_exam">Set Neurological Exam Normal</label></td>
			<td><div name="tmp_ge_neuro_button_disp" id="tmp_ge_neuro_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_neuro_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_neu_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_neuro_disp" style="display: <?php echo $dt['tmp_ge_neuro_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php
			EE1_GESelLine('ee1_ge_neu','ao','Alert &amp; Oriented',$dt{'ee1_ge_neu_ao'},$dt{'ee1_ge_neu_ao_nt'}, 'EE1_AO', 'width: 70%');
			EE1_GESelLine('ee1_ge_neu','cn','CN II - XII Intact',$dt{'ee1_ge_neu_cn'},$dt{'ee1_ge_neu_cn_nt'});
			?>
    	<tr>
      	<td colspan="2" class="wmtLabel">DTRs</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_neu','bicr','Right Bicep',$dt{'ee1_ge_neu_bicr'},$dt{'ee1_ge_neu_bicr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','bicl','Left Bicep',$dt{'ee1_ge_neu_bicl'},$dt{'ee1_ge_neu_bicl_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','trir','Right Tricep',$dt{'ee1_ge_neu_trir'},$dt{'ee1_ge_neu_trir_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','tril','Left Tricep',$dt{'ee1_ge_neu_tril'},$dt{'ee1_ge_neu_tril_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','brar','Right Brachioradialis',$dt{'ee1_ge_neu_brar'},$dt{'ee1_ge_neu_brar_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','bral','Left Brachioradialis',$dt{'ee1_ge_neu_bral'},$dt{'ee1_ge_neu_bral_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','patr','Right Patella',$dt{'ee1_ge_neu_patr'},$dt{'ee1_ge_neu_patr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','patl','Left Patella',$dt{'ee1_ge_neu_patl'},$dt{'ee1_ge_neu_patl_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','achr','Right Achilles',$dt{'ee1_ge_neu_achr'},$dt{'ee1_ge_neu_achr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ee1_ge_neu','achl','Left Achilles',$dt{'ee1_ge_neu_achl'},$dt{'ee1_ge_neu_achl_nt'}, 'EE1_DTR');
			?>
    	<tr>
      	<td class="wmtLabel" colspan="2">Strength</td>
    	</tr>
			<?php
			EE1_GESelLine('ee1_ge_neu','pup','Proximal Upper',$dt{'ee1_ge_neu_pup'},$dt{'ee1_ge_neu_pup_nt'}, 'Zero_to_5');
			EE1_GESelLine('ee1_ge_neu','plow','Proximal Lower',$dt{'ee1_ge_neu_plow'},$dt{'ee1_ge_neu_plow_nt'}, 'Zero_to_5');
			EE1_GESelLine('ee1_ge_neu','dup','Distal Upper',$dt{'ee1_ge_neu_dup'},$dt{'ee1_ge_neu_dup_nt'}, 'Zero_to_5');
			EE1_GESelLine('ee1_ge_neu','dlow','Distal Lower',$dt{'ee1_ge_neu_dlow'},$dt{'ee1_ge_neu_dlow_nt'}, 'Zero_to_5');
			EE1_GESelLine('ee1_ge_neu','tn','Tone',$dt{'ee1_ge_neu_tn'},$dt{'ee1_ge_neu_tn_nt'}, 'neuro_tone');
			?>
    	<tr>
      	<td class="wmtLabel" colspan="2">Coordination / Cerebellar</td>
    	</tr>
			<tr>
				<td class="wmtBody wmtR"><input name="ee1_ge_neu_cc_norm" id="ee1_ge_neu_cc_norm" type="checkbox" value="1" <?php echo (($dt{'ee1_ge_neu_cc_norm'} == 1)?'checked':''); ?> onchange="toggleCCNorm(this);" /></td>
				<td class="wmtBody"><label for="ee1_ge_neu_cc_norm">No Abnormalities</td>
				<td>&nbsp;</td>
			</tr>
			<?
			EE1_GESelLine('ee1_ge_neu','cc_fn','Finger / Nose',$dt{'ee1_ge_neu_cc_fn'},$dt{'ee1_ge_neu_cc_fn_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ee1_ge_neu','cc_hs','Heel / Shin',$dt{'ee1_ge_neu_cc_hs'},$dt{'ee1_ge_neu_cc_hs_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ee1_ge_neu','cc_ra','Rapid Alternating',$dt{'ee1_ge_neu_cc_ra'},$dt{'ee1_ge_neu_cc_ra_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ee1_ge_neu','cc_rm','Romberg',$dt{'ee1_ge_neu_cc_rm'},$dt{'ee1_ge_neu_cc_rm_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ee1_ge_neu','cc_pd','Pronator Drift',$dt{'ee1_ge_neu_cc_pd'},$dt{'ee1_ge_neu_cc_pd_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			if($client_id != 'cffm') {
				EE1_GESelLine('ee1_ge_neu','sns_chc','Sensation',$dt{'ee1_ge_neu_sns_chc'},$dt{'ee1_ge_neu_sns_chc_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			}
			?>
			
    	<tr>
      	<td class="wmtBody"><?php echo (($client_id != 'cffm') ? 'Notes:' : 'Sensation:'); ?></td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_neu_sense" id="ee1_ge_neu_sense" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_neu_sense'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_musc" id="tmp_ge_musc" type="checkbox" value="1" <?php echo (($dt['tmp_ge_musc'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_musc','tmp_ge_musc_disp','tmp_ge_musc_button_disp');" /><label for="tmp_ge_musc">Musculoskeletal / Back:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_ms_norm_exam" id="ee1_ge_ms_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_ms_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMuscNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_ms_norm_exam">Set Musculoskeletal/Back Exam Normal</label></td>
			<td><div name="tmp_ge_musc_button_disp" id="tmp_ge_musc_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_musc_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ms_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_musc_disp" style="display: <?php echo $dt['tmp_ge_musc_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			if($client_id == 'cffm') {
				EE1_GECheckLine('ee1_ge_ms','intact','Intact w/o Atrophy',$dt{'ee1_ge_ms_intact'},$dt{'ee1_ge_ms_intact_nt'});
			}
			EE1_GESelLine('ee1_ge_ms','mass','Mass',$dt{'ee1_ge_ms_mass'},$dt{'ee1_ge_ms_mass_nt'}, 'Yes_No', 'width: 70%');
			EE1_GESelLine('ee1_ge_ms','tnd','Tenderness',$dt{'ee1_ge_ms_tnd'},$dt{'ee1_ge_ms_tnd_nt'});
			EE1_GESelLine('ee1_ge_ms','scl','Scoliosis',$dt{'ee1_ge_ms_scl'},$dt{'ee1_ge_ms_scl_nt'});
			EE1_GESelLine('ee1_ge_ms','cval','CVA Tenderness on L',$dt{'ee1_ge_ms_cval'},$dt{'ee1_ge_ms_cval_nt'});
			EE1_GESelLine('ee1_ge_ms','cvar','CVA Tenderness on R',$dt{'ee1_ge_ms_cvar'},$dt{'ee1_ge_ms_cvar_nt'});
			EE1_GESelLine('ee1_ge_ms','lim','ROM Limited',$dt{'ee1_ge_ms_lim'},$dt{'ee1_ge_ms_lim_nt'});
			EE1_GESelLine('ee1_ge_ms','def','Deformity',$dt{'ee1_ge_ms_def'},$dt{'ee1_ge_ms_def_nt'});
			EE1_GESelLine('ee1_ge_ms','full','ROM Full',$dt{'ee1_ge_ms_full'},$dt{'ee1_ge_ms_full_nt'});
			EE1_GESelLine('ee1_ge_ms','gait','Gait',$dt{'ee1_ge_ms_gait'},$dt{'ee1_ge_ms_gait_nt'}, 'NormAbnorm');
			$ms_list = array( 'wheelchair', 'walker', 'prosthetics_/_orthotics');
			EE1_GEMultiCheckLine('ee1_ge_ms', $ms_list, $ms_chks);
			EE1_GECheckLine('ee1_ge_ms','norm','Moves all extremities well and equally',$dt{'ee1_ge_ms_norm'}, '', '', true, true);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_ms_nt" id="ee1_ge_ms_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ee1_ge_ms_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_ext" id="tmp_ge_ext" type="checkbox" value="1" <?php echo (($dt['tmp_ge_ext'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_ext','tmp_ge_ext_disp','tmp_ge_ext_button_disp');" /><label for="tmp_ge_ext">Extremities:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_ext_norm_exam" id="ee1_ge_ext_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_ext_norm_exam'] == 1)?' checked':''); ?> onChange="setGEExtNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_ext_norm_exam">Set Extremities Exam Normal</label></td>
			<td><div name="tmp_ge_ext_button_disp" id="tmp_ge_ext_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_ext_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ext_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_ext_disp" style="display: <?php echo $dt['tmp_ge_ext_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
    	<tr>
      	<td class="wmtBody wmtR"><select name="ee1_ge_ext_edema" id="ee1_ge_ext_edema" class="Input" onchange="toggleLineDetail(this, 'ee1_ge_ext_edema_chc'); document.getElementById('ee1_ge_ext_norm_exam').checked=false; ">
						<?php ListSel($dt{'ee1_ge_ext_edema'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Edema&nbsp;&nbsp;&nbsp;<select name="ee1_ge_ext_edema_chc" id="ee1_ge_ext_edema_chc" class="Input" style="display: <?php echo (($dt{'ee1_ge_ext_edema'} == 'y')? 'inline' : 'none'); ?>;" >
					<?php ListSel($dt{'ee1_ge_ext_edema_chc'},'Edema'); ?>
				</select></td>
      	<td style="width: 70%;"><input name="ee1_ge_ext_edema_nt" id="ee1_ge_ext_edema_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ee1_ge_ext_edema_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ee1_ge_ext_norm_exam').checked=false; "/></td>
			</tr>

    	<tr>
      	<td class="wmtLabel" colspan="2">Pulses</td>
    	</tr>
			<?php 
			EE1_GESelLine('ee1_ge_ext','pls_rad','Radial',$dt{'ee1_ge_ext_pls_rad'}, '', 'Zero_to_4', 'width: 70%', true, true);
			EE1_GESelLine('ee1_ge_ext','pls_dors','Dorsalis Pedis',$dt{'ee1_ge_ext_pls_dors'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ee1_ge_ext','pls_post','Posterior Tibial',$dt{'ee1_ge_ext_pls_post'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ee1_ge_ext','pls_pop','Popliteal',$dt{'ee1_ge_ext_pls_pop'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ee1_ge_ext','pls_fem','Femoral',$dt{'ee1_ge_ext_pls_fem'}, '', 'Zero_to_4', '', true, true);
			?>
			<tr>
				<td class="wmtLabel" colspan="2">Capillary Refill</td>
			</tr>
			<?php
			EE1_GESelLine('ee1_ge_ext','refill',' &lt; 3 Seconds',$dt{'ee1_ge_ext_refill'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ee1_ge_ext','club','Clubbing',$dt{'ee1_ge_ext_club'}, $dt{'ee1_ge_ext_club_nt'});
			EE1_GESelLine('ee1_ge_ext','cyan','Cyanosis',$dt{'ee1_ge_ext_cyan'}, $dt{'ee1_ge_ext_cyan_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_ext_nt" id="ee1_ge_ext_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_ext_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_dia" id="tmp_ge_dia" type="checkbox" value="1" <?php echo (($dt['tmp_ge_dia'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_dia','tmp_ge_dia_disp','tmp_ge_dia_button_disp');" /><label for="tmp_ge_dia">Diabetic Foot:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_db_norm_exam" id="ee1_ge_db_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_db_norm_exam'] == 1)?' checked':''); ?> onChange="setGEFootNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_db_norm_exam">Set Diabetic Foot Exam Normal</label></td>
			<td><div name="tmp_ge_dia_button_disp" id="tmp_ge_dia_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_dia_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_db_');"><span>Clear</span></a></div></td>
			</div></td>
		</tr>
		</table>
		<div id="tmp_ge_dia_disp" style="display: <?php echo $dt['tmp_ge_dia_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GESelLine('ee1_ge_db','prop','Proprioception',$dt{'ee1_ge_db_prop'}, $dt{'ee1_ge_db_prop_nt'}, 'NormAbnorm', 'width: 70%;');
			EE1_GESelLine('ee1_ge_db','vib','Vibration Sense',$dt{'ee1_ge_db_vib'}, $dt{'ee1_ge_db_vib_nt'}, 'NormAbnorm');
			EE1_GESelLine('ee1_ge_db','sens','Sensation to Monofilament Testing',$dt{'ee1_ge_db_sens'}, $dt{'ee1_ge_db_sens_nt'}, 'NormAbnorm');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_db_nt" id="ee1_ge_db_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_db_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
			</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_test" id="tmp_ge_test" type="checkbox" value="1" <?php echo (($dt['tmp_ge_test'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_test','tmp_ge_test_disp','tmp_ge_test_button_disp');" /><label for="tmp_ge_test">Genitalia:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_te_norm_exam" id="ee1_ge_te_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_te_norm_exam'] == 1)?' checked':''); ?> onChange="setGETestesNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_te_norm_exam">Set Genitalia Exam Normal</label></td>
			<td><div name="tmp_ge_test_button_disp" id="tmp_ge_test_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_test_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_te_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_test_disp" style="display: <?php echo $dt['tmp_ge_test_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<tr>
				<td class="wmtLabel" colspan="2"><?php echo ($pat_sex == 'm') ? 'Penile' : 'Vulva'; ?></td>
			</tr>
			<?php 
			if($pat_sex == 'm') {
				EE1_GESelLine('ee1_ge_te','cir','Circumcised',$dt{'ee1_ge_te_cir'}, $dt{'ee1_ge_te_cir_nt'}, 'Yes_No', 'width: 70%;');
				EE1_GESelLine('ee1_ge_te','les','Lesions',$dt{'ee1_ge_te_les'}, $dt{'ee1_ge_te_les_nt'});
				EE1_GESelLine('ee1_ge_te','dis','Discharge',$dt{'ee1_ge_te_dis'}, $dt{'ee1_ge_te_dis_nt'});
				EE1_GESelLine('ee1_ge_te','size','Testes Size',$dt{'ee1_ge_te_size'}, $dt{'ee1_ge_te_size_nt'}, 'EE1_Testes_Size');
				EE1_GESelLine('ee1_ge_te','palp','Palpitaation',$dt{'ee1_ge_te_palp'}, $dt{'ee1_ge_te_palp_nt'}, 'HardSoft');
				EE1_GESelLine('ee1_ge_te','mass','Mass',$dt{'ee1_ge_te_mass'}, $dt{'ee1_ge_te_mass_nt'});
				EE1_GESelLine('ee1_ge_te','tend','Tender',$dt{'ee1_ge_te_tend'}, $dt{'ee1_ge_te_tend_nt'});
				EE1_GESelLine('ee1_ge_te','ery','Erythema',$dt{'ee1_ge_te_ery'}, $dt{'ee1_ge_te_ery_nt'});
			} else {
				EE1_GESelLine('ee1_ge_te','lmaj','Labia Majora',$dt{'ee1_ge_te_lmaj'}, $dt{'ee1_ge_te_lmaj_nt'}, 'NormAbnorm', 'width: 70%;');
				EE1_GESelLine('ee1_ge_te','lmin','Labia Minora',$dt{'ee1_ge_te_lmin'}, $dt{'ee1_ge_te_lmin_nt'}, 'NormAbnorm');
				EE1_GESelLine('ee1_ge_te','intro','Introitus',$dt{'ee1_ge_te_intro'}, $dt{'ee1_ge_te_intro_nt'}, 'NormAbnorm');
				EE1_GESelLine('ee1_ge_te','urethra','Urethra',$dt{'ee1_ge_te_urethra'}, $dt{'ee1_ge_te_urethra_nt'}, 'NormAbnorm');
				EE1_GESelLine('ee1_ge_te','clit','Clitorus',$dt{'ee1_ge_te_clit'}, $dt{'ee1_ge_te_clit'}, 'NormAbnorm');
			}
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_te_nt" id="ee1_ge_te_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_te_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_rectal" id="tmp_ge_rectal" type="checkbox" value="1" <?php echo (($dt['tmp_ge_rectal'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_rectal','tmp_ge_rectal_disp','tmp_ge_rectal_button_disp');" /><label for="tmp_ge_rectal">Rectal:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_rc_norm_exam" id="ee1_ge_rc_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_rc_norm_exam'] == 1)?' checked':''); ?> onChange="setGERectalNormal('<?php echo $pat_sex; ?>','<?php echo $client_id; ?>',this);" /><label for="ee1_ge_rc_norm_exam">Set Rectal Exam Normal</label></td>
			<td><div name="tmp_ge_rectal_button_disp" id="tmp_ge_rectal_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_rectal_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_rc_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_rectal_disp" style="display: <?php echo $dt['tmp_ge_rectal_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GESelLine('ee1_ge_rc','tone','Tone',$dt{'ee1_ge_rc_tone'}, $dt{'ee1_ge_rc_tone_nt'}, 'EE1_Tone', 'width: 70%;');
			EE1_GESelLine('ee1_ge_rc','ext','External Hemorrhoid',$dt{'ee1_ge_rc_ext'}, $dt{'ee1_ge_rc_ext_nt'});
			if($pat_sex != 'f') {
				EE1_GESelLine('ee1_ge_rc','pro','Prostate Size',$dt{'ee1_ge_rc_pro'}, $dt{'ee1_ge_rc_pro_nt'}, 'EE1_Prostate');
			}
			EE1_GESelLine('ee1_ge_rc','bog','Boggy',$dt{'ee1_ge_rc_bog'}, $dt{'ee1_ge_rc_bog_nt'});
			EE1_GESelLine('ee1_ge_rc','hard','Hard',$dt{'ee1_ge_rc_hard'}, $dt{'ee1_ge_rc_hard_nt'});
			EE1_GESelLine('ee1_ge_rc','mass','Masses',$dt{'ee1_ge_rc_mass'}, $dt{'ee1_ge_rc_mass_nt'});
			EE1_GESelLine('ee1_ge_rc','tend','Tender',$dt{'ee1_ge_rc_tend'}, $dt{'ee1_ge_rc_tend_nt'});
			$lbl = ($client_id == 'cffm') ? 'Stool GWIAC' : 'Stool';
			EE1_GESelLine('ee1_ge_rc','color',$lbl,$dt{'ee1_ge_rc_color'}, $dt{'ee1_ge_rc_color_nt'},'EE1_Stool_Color');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_rc_nt" id="ee1_ge_rc_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ee1_ge_rc_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_skin" id="tmp_ge_skin" type="checkbox" value="1" <?php echo (($dt['tmp_ge_skin'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_skin','tmp_ge_skin_disp','tmp_ge_skin_button_disp');" /><label for="tmp_ge_skin">Skin:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_skin_norm_exam" id="ee1_ge_skin_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_skin_norm_exam'] == 1)?' checked':''); ?> onChange="setGESkinNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_skin_norm_exam">Set Skin Exam Normal</label></td>
			<td><div name="tmp_ge_skin_button_disp" id="tmp_ge_skin_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_skin_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_skin_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_skin_disp" style="display: <?php echo $dt['tmp_ge_skin_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<?php 
		if($client_id == 'cffm') {
			EE1_GECheckLine('ee1_ge_skin','app','Normal Appendages',$dt{'ee1_ge_skin_app'}, $dt{'ee1_ge_skin_app_nt'}, 'width: 70%;');
			EE1_GECheckLine('ee1_ge_skin','les','No Suspicious Lesions Noted',$dt{'ee1_ge_skin_les'}, $dt{'ee1_ge_skin_app_nt'});
			EE1_GESelLine('ee1_ge_skin','ver','Veracities',$dt{'ee1_ge_skin_ver'}, $dt{'ee1_ge_skin_ver_nt'});
		} else {
			EE1_GESelLine('ee1_ge_skin','jau','Jaundice',$dt{'ee1_ge_skin_jau'}, $dt{'ee1_ge_skin_jau_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ee1_ge_skin','con','Contusion',$dt{'ee1_ge_skin_con'}, $dt{'ee1_ge_skin_con_nt'});
			EE1_GESelLine('ee1_ge_skin','ecc','Ecchymosis',$dt{'ee1_ge_skin_ecc'}, $dt{'ee1_ge_skin_ecc_nt'});
			EE1_GESelLine('ee1_ge_skin','rash','Rash',$dt{'ee1_ge_skin_rash'}, $dt{'ee1_ge_skin_rash_nt'});
			EE1_GESelLine('ee1_ge_skin','abs','Abscess/Cellulitis',$dt{'ee1_ge_skin_abs'}, $dt{'ee1_ge_skin_abs_nt'});
			EE1_GESelLine('ee1_ge_skin','lac','Laceration/Abrasion',$dt{'ee1_ge_skin_lac'}, $dt{'ee1_ge_skin_lac_nt'});
		}
		?>
    <tr>
      <td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
			<td colspan="3"><textarea name="ee1_ge_skin_nt" id="ee1_ge_skin_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ee1_ge_skin_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_psych" id="tmp_ge_psych" type="checkbox" value="1" <?php echo (($dt['tmp_ge_psych'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_psych','tmp_ge_psych_disp','tmp_ge_psych_button_disp');" /><label for="tmp_ge_psych">Psychiatric:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ee1_ge_psych_norm_exam" id="ee1_ge_psych_norm_exam" type="checkbox" value="1" <?php echo (($dt['ee1_ge_psych_norm_exam'] == 1)?' checked':''); ?> onChange="setGEPsychNormal('<?php echo $client_id; ?>',this);" /><label for="ee1_ge_psych_norm_exam">Set Psychiatric Exam Normal</label></td>
			<td><div name="tmp_ge_psych_button_disp" id="tmp_ge_psych_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_psych_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_psych_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_psych_disp" style="display: <?php echo $dt['tmp_ge_psych_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GECheckLine('ee1_ge_psych','judge','Assessment of Judgment/Insight',$dt{'ee1_ge_psych_judge'}, $dt{'ee1_ge_psych_judge_nt'}, 'width: 70%;');
			EE1_GECheckLine('ee1_ge_psych','orient','Orientation to Time, Place, Person',$dt{'ee1_ge_psych_orient'}, $dt{'ee1_ge_psych_orient_nt'});
			EE1_GECheckLine('ee1_ge_psych','memory','Assessment of Memory (Recent/Remoter)',$dt{'ee1_ge_psych_memory'}, $dt{'ee1_ge_psych_memory_nt'});
			EE1_GECheckLine('ee1_ge_psych','mood','Assessment of Mood/Affect',$dt{'ee1_ge_psych_mood'}, $dt{'ee1_ge_psych_mood_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ee1_ge_psych_nt" id="ee1_ge_psych_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ee1_ge_psych_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    </table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td class="wmtBody"><b><i>Use the category checkboxes to reveal/hide these sections</i></b></td>
			<td><div style="float: left; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="showAllExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Show ALL Sections</span></a></div>&nbsp;&nbsp;&nbsp;&nbsp;
			<div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="hideAllExamSections('<?php echo $pat_sex; ?>');" href="javascript:;"><span>Hide ALL Sections</span></a></div>
			<!-- div style="float: left; padding-right: 10px;"><a class="css_button" href="javascript:;" onclick="wmtOpen('../../../custom/document_popup.php?pid=<?php echo $pid; ?>', '_blank', 800, 600);"><span>View Documents</span></a></div --></td>
		</tr>
		</table>
<?php ?>
