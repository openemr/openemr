<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($draw_display)) $draw_display = true;
if(!isset($noload)) $noload = false;
$flds = sqlListFields('form_ext_exam2');
$flds = array_slice($flds,8);

if($form_mode == 'new' || $form_mode == 'update') {
	// JUST IN CASE WE NEED PRE-PROCESSING

} else {
	$ge = array();
	$notes = array();
	foreach($_POST as $key => $var) {
		if(substr($key,0,3) != 'ge_') continue;
		if(is_string($var)) $var = trim($var);
		// THE COMMENTS HAVE TO BE STORED AFTER WITH THE ID
		$tmp = substr($key,0,-3);
		if((substr($key, -3) == '_nt') && (in_array($flds, $tmp))) {
			echo "Note: [$key] -> For ($tmp)<br>\n";
			$notes[$tmp] = $var;
			unset($_POST[$key]);
			continue;
		}
		$ge[$key] = $var
		unset($_POST[$key]);
	}

	// FIRST SAVE THE EXAM RECORD IF NOT ALREAY SAVED
	if($frmdir == 'general_exam2' || $frmdir == 'ext_exam2') {
		$ge['ge_id'] = $id;
	} else {
		$ge['link_id'] = $encounter;
		$ge['link_name'] = 'encounter';
	}
	if($ge['ge_id']) {
  	$binds = array($pid, $_SESSION['authProvider'], $_SESSION['authUser'],
						$_SESSION['userauthorized'], 1);
  	$q1 = '';
  	foreach ($ge as $key => $val){
			if($key == 'ge_id') continue;
    	$q1 .= "`$key` = ?, ";
			$binds[] = $val;
  	}
		$binds[] = $ge['ge_id'];
  	sqlInsert('UPDATE `form_ext_exam2` SET `pid` = ?, `groupname` = ?, ' .
			'`user`=?, `authorized` = ?, `activity` = ?, ' . $q1 . '`date` = NOW() ' .
			'WHERE `id`=?', $binds);
	} else {
		unset($ge['ge_id']);
  	$newid = 
			wmtFormSubmit('form_ext_exam2',$ge,'',$_SESSION['userauthorized'],$pid);
		if($frmdir == 'general+_exam2' || $frmdir == 'ext_exam2') 
  		addForm($encounter,$ftitle,$newid,$frmdir,$pid,$_SESSION['userauthorized']);
			$id = $newid;
	}
	$ge['ge_id'] = $newid;

	// NOW PROCESS ALL THE COMMENTS
	foreach($notes as $key => $nt) {
		ProcessROSKeyComment($pid, $ge['ge_id'], $frmn, $key, $nt);
	}
}

// CHECK TO SEE IF WE LOAD FORM HISTORY IF THIS IS A NEW FORM
if($form_mode == 'new') {
	if($first_pass) {
		if(!$noload) {
		}
	}
} else if($frmdir == 'general_exam2' || $frmdir == 'ext_exam2' && $id) {
	// WAS CALLED AS A STAND-ALONE
	$sql = 'SELECT * FROM form_ext_exam2 WHERE id = ? AND pid = ?';
	$binds = array($id, $pid);
} else {
	// GET ENCOUNTER LEVEL FORM
	$sql = 'SELECT * FROM form_ext_exam2 WHERE link_id = ? AND link_name = ? ' .
		'AND pid = ?';
	$binds = array($encounter, 'encounter', $pid);
}
$ge = sqlQuery($sql, $binds);
if($ge) {
	$dt['ge_id'] = $ge['id'];
	$ge = array_slice($ge,8);
	foreach($ge as $key => $val) {
		$dt[$key] = $val;
	}
} else {
	foreach($flds as $fld) {
		$dt[$fld] = '';
	}
	$dt['ge_id' ] = '';
}
// LOAD ALL THE COMMENTS
if($dt['ge_id']) {
	foreach($flds as $fld) {
		$comment = GetROSKeyComment($dt['ge_id'], 'form_ext_exam2', $fld, $pid);
		if(isset($comment['note'])) $comment['note'];
		if($comment['note']) $dt[$fld . '_nt'] = $comment['note'];
	}
}

if($draw_display) {

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
			<td><input type="hidden" name="ge_id" id="ge_id" value="<?php echo $dt['ge_id']; ?>" /> &nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('ge_dictate');" href="javascript:;"><span>Clear Dictation</span></a></div></td>
		</tr>
    <tr>
      <td colspan="5"><textarea name="ge_dictate" id="ge_dictate" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ge_dictate'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
		<tr>
			<td class="wmtLabel">Diagnostic Tests:</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td><div style="float: right; padding-right: 10px;"><a class="css_button" tabindex="-1" onClick="ClearThisField('ge_diagnostics');" href="javascript:;"><span>Clear Tests</span></a></div></td>
		</tr>
    <tr>
      <td colspan="4"><textarea name="ge_diagnostics" id="ge_diagnostics" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ge_diagnostics'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
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
      <td class="wmtBody" style="width: 30%;"><input name="ge_gen_norm_exam" id="ge_gen_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_gen_norm_exam'] == 1)?' checked':''); ?> onChange="setGEGeneralNormal('<?php echo $client_id; ?>',this);" /><label for="ge_gen_norm_exam">Set General Exam Normal</label></td>
			<td><div name="tmp_ge_gen_button_disp" id="tmp_ge_gen_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_gen_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_gen_');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_gen_disp" style="display: <?php echo $dt['tmp_ge_gen_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php 
		EE1_GECheckLine('ge_gen','norm','Normal Habitus',$dt{'ge_gen_norm'},$dt{'ge_gen_norm_nt'}, 'width: 75%;');
		EE1_GECheckLine('ge_gen','dev','Well Developed',$dt{'ge_gen_dev'},$dt{'ge_gen_dev_nt'});
		EE1_GECheckLine('ge_gen','groom','Well Groomed',$dt{'ge_gen_groom'},$dt{'ge_gen_groom_nt'});
		EE1_GECheckLine('ge_gen','dis','No Acute Distress',$dt{'ge_gen_dis'},$dt{'ge_gen_dis_nt'});
		if($client_id != 'sfa') {
			EE1_GESelLine('ge_gen','jaun','Jaundice',$dt{'ge_gen_jaun'},$dt{'ge_gen_jaun_nt'});
			EE1_GESelLine('ge_gen','waste','Wasting',$dt{'ge_gen_waste'},$dt{'ge_gen_waste_nt'});
			EE1_GESelLine('ge_gen','sleep','Sleep Pattern',$dt{'ge_gen_sleep'},$dt{'ge_gen_sleep_nt'}, 'NormAbnorm');
		}
		?>
		<tr>
			<td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ge_gen_nt" id="ge_gen_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_gen_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_head" id="tmp_ge_head" type="checkbox" value="1" <?php echo (($dt['tmp_ge_head'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_head','tmp_ge_head_disp','tmp_ge_head_button_disp');" /><label for="tmp_ge_head">Head:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_hd_norm_exam" id="ge_hd_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_hd_norm_exam'] == 1)?' checked':''); ?> onChange="setGEHeadNormal('<?php echo $client_id; ?>',this);" /><label for="ge_hd_norm_exam">Set Head Exam Normal</label></td>
			<td><div name="tmp_ge_head_button_disp" id="tmp_ge_head_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_head_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_hd_');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_head_disp" style="display: <?php echo $dt['tmp_ge_head_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
		<?php
		EE1_GECheckLine('ge_hd','atra','Atraumatic',$dt{'ge_hd_atra'},$dt{'ge_hd_atra_nt'}, 'width: 70%;');
		EE1_GECheckLine('ge_hd','norm','Normocephalic',$dt{'ge_hd_norm'},$dt{'ge_hd_norm_nt'});
		// if($client_id == 'sfa') {
			EE1_GESelLine('ge_hd','feat','Facial Features',$dt{'ge_hd_feat'},$dt{'ge_hd_feat_nt'}, 'Facial_Features');
			EE1_GESelLine('ge_hd','ant','Anterior Fontanel',$dt{'ge_hd_ant'},$dt{'ge_hd_ant_nt'}, 'ant_font');
			$hd_list = array('head_mid-line', 'deformity', 'lesion', 'flaky_scalp',
				'nits_visible', 'edema', 'erythema');
			EE1_GEMultiCheckLine('ge_hd', $hd_list, $hd_chks);
		// }
		?>
    <tr>
      <td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
      <td colspan="3"><textarea name="ge_hd_nt" id="ge_hd_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_hd_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_eyes" id="tmp_ge_eyes" type="checkbox" value="1" <?php echo (($dt['tmp_ge_eyes'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_eyes','tmp_ge_eyes_disp','tmp_ge_eyes_button_disp');" /><label for="tmp_ge_eyes">Eyes:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_eye_norm_exam" id="ge_eye_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_eye_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEyesNormal('<?php echo $client_id; ?>',this);" /><label for="ge_eye_norm_exam">Set Eye Exam Normal</label></td>
			<td><div name="tmp_ge_eyes_button_disp" id="tmp_ge_eyes_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_eyes_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_eye');"><span>Clear</span></a></div></td>
    </tr>
		</table>
		<div id="tmp_ge_eyes_disp" style="display: <?php echo $dt['tmp_ge_eyes_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ge_eye','pupil','Pupils',$dt{'ge_eye_pupil'},$dt{'ge_eye_pupil_nt'}, 'EE1_Pupil', 'width: 70%;', false);
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Fundoscopic</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_eye','hem','Hemorrhage',$dt{'ge_eye_hem'},$dt{'ge_eye_hem_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ge_eye','exu','Exudate',$dt{'ge_eye_exu'},$dt{'ge_eye_exu_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ge_eye','av','AV Nicking',$dt{'ge_eye_av'},$dt{'ge_eye_av_nt'}, 'Yes_No', '', false);
			EE1_GESelLine('ge_eye','pap','Papilledema',$dt{'ge_eye_pap'},$dt{'ge_eye_pap_nt'}, 'Yes_No', '', false);
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Eye</td>
    	</tr>
			<?php
			if($client_id == 'sfa') {
				EE1_GECheckLine('ge_eyer','norm','No Abnormalities',$dt{'ge_eyer_norm'},$dt{'ge_eyer_norm_nt'});
			} else {
				EE1_GESelLine('ge_eyer','norm','No Abnormalities',$dt{'ge_eyer_norm'},$dt{'ge_eyer_norm_nt'}, 'NormAbnorm');
			}
			EE1_GESelLine('ge_eyer','exo','Exophthalmos',$dt{'ge_eyer_exo'},$dt{'ge_eyer_exo_nt'});
			EE1_GESelLine('ge_eyer','stare','Stare',$dt{'ge_eyer_stare'},$dt{'ge_eyer_stare_nt'});
			EE1_GESelLine('ge_eyer','lag','Lid Lag',$dt{'ge_eyer_lag'},$dt{'ge_eyer_lag_nt'});
			EE1_GECheckLine('ge_eyer','scleral','No Scleral Injection',$dt{'ge_eyer_scleral'},$dt{'ge_eyer_scleral_nt'});
			EE1_GESelLine('ge_eyer','eomi','EOMI',$dt{'ge_eyer_eomi'},$dt{'ge_eyer_eomi_nt'});
			EE1_GESelLine('ge_eyer','perrl','PERRL',$dt{'ge_eyer_perrl'},$dt{'ge_eyer_perrl_nt'});
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Eye</td>
    	</tr>
			<?php
			if($client_id == 'sfa') {
				EE1_GECheckLine('ge_eyel','norm','No Abnormalities',$dt{'ge_eyel_norm'},$dt{'ge_eyel_norm_nt'});
			} else {
				EE1_GESelLine('ge_eyel','norm','No Abnormalities',$dt{'ge_eyel_norm'},$dt{'ge_eyel_norm_nt'}, 'NormAbnorm');
			}
			EE1_GESelLine('ge_eyel','exo','Exophthalmos',$dt{'ge_eyel_exo'},$dt{'ge_eyel_exo_nt'});
			EE1_GESelLine('ge_eyel','stare','Stare',$dt{'ge_eyel_stare'},$dt{'ge_eyel_stare_nt'});
			EE1_GESelLine('ge_eyel','lag','Lid Lag',$dt{'ge_eyel_lag'},$dt{'ge_eyel_lag_nt'});
			EE1_GECheckLine('ge_eyel','scleral','No Scleral Injection',$dt{'ge_eyel_scleral'},$dt{'ge_eyel_scleral_nt'});
			EE1_GESelLine('ge_eyel','eomi','EOMI',$dt{'ge_eyel_eomi'},$dt{'ge_eyel_eomi_nt'});
			EE1_GESelLine('ge_eyel','perrl','PERRL',$dt{'ge_eyel_perrl'},$dt{'ge_eyel_perrl_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_eye_nt" id="ge_eye_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_eye_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_ears" id="tmp_ge_ears" type="checkbox" value="1" <?php echo (($dt['tmp_ge_ears'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_ears','tmp_ge_ears_disp','tmp_ge_ears_button_disp');" /><label for="tmp_ge_ears">Ears:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_ear_norm_exam" id="ge_ear_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_ear_norm_exam'] == 1)?' checked':''); ?> onChange="setGEEarsNormal('<?php echo $client_id; ?>',this);" /><label for="ge_ear_norm_exam">Set Ear Exam Normal</label></td>
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
				<td style="width: 70%;"><input name="ge_earr_tym_nt" id="ge_earr_tym_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_earr_tym_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ge_earr','clear','Clear',$dt{'ge_earr_clear'},$dt{'ge_earr_clear_nt'});
			EE1_GESelLine('ge_earr','perf','Perforation',$dt{'ge_earr_perf'},$dt{'ge_earr_perf_nt'});
			EE1_GESelLine('ge_earr','ret','Retraction',$dt{'ge_earr_ret'},$dt{'ge_earr_ret_nt'});
			EE1_GESelLine('ge_earr','bulge','Bulging',$dt{'ge_earr_bulge'},$dt{'ge_earr_bulge_nt'});
			$label = ($client_id == 'sfa') ? 'Drainage' : 'Pus';
			EE1_GESelLine('ge_earr','pus','Drainage',$dt{'ge_earr_pus'},$dt{'ge_earr_pus_nt'}, 'ear_drain');
			EE1_GESelLine('ge_earr','ceru','Cerumen',$dt{'ge_earr_ceru'},$dt{'ge_earr_ceru_nt'});
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Ear</td>
    	</tr>
    	<tr>
				<td>&nbsp;</td>
      	<td class="wmtBody">Tympanic Membrane</td>
				<td><input name="ge_earl_tym_nt" id="ge_earl_tym_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_earl_tym_nt'}, ENT_QUOTES, '', FALSE); ?>" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ge_earl','clear','Clear',$dt{'ge_earl_clear'},$dt{'ge_earl_clear_nt'});
			EE1_GESelLine('ge_earl','perf','Perforation',$dt{'ge_earl_perf'},$dt{'ge_earl_perf_nt'});
			EE1_GESelLine('ge_earl','ret','Retraction',$dt{'ge_earl_ret'},$dt{'ge_earl_ret_nt'});
			EE1_GESelLine('ge_earl','bulge','Bulging',$dt{'ge_earl_bulge'},$dt{'ge_earl_bulge_nt'});
			$label = ($client_id == 'sfa') ? 'Drainage' : 'Pus';
			EE1_GESelLine('ge_earl','pus','Drainage',$dt{'ge_earl_pus'},$dt{'ge_earl_pus_nt'}, 'ear_drain');
			EE1_GESelLine('ge_earl','ceru','Cerumen',$dt{'ge_earl_ceru'},$dt{'ge_earl_ceru_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_ear_nt" id="ge_ear_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_ear_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_nose" id="tmp_ge_nose" type="checkbox" value="1" <?php echo (($dt['tmp_ge_nose'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_nose','tmp_ge_nose_disp','tmp_ge_nose_button_disp');" /><label for="tmp_ge_nose">Nose:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_nose_norm_exam" id="ge_nose_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_nose_norm_exam'] == 1)?' checked':''); ?> onChange="setGENoseNormal('<?php echo $client_id; ?>',this);" /><label for="ge_nose_norm_exam">Set Nose Exam Normal</label></td>
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
			EE1_GESelLine('ge_nose','ery','Erythema',$dt{'ge_nose_ery'},$dt{'ge_nose_ery_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ge_nose','swell','Swelling',$dt{'ge_nose_swell'},$dt{'ge_nose_swell_nt'});
			EE1_GESelLine('ge_nose','pall','Pallor',$dt{'ge_nose_pall'},$dt{'ge_nose_pall_nt'});
			EE1_GESelLine('ge_nose','polps','Polyps',$dt{'ge_nose_polps'},$dt{'ge_nose_polps_nt'});
			EE1_GESelLine('ge_nose','sept','Septum',$dt{'ge_nose_sept'},$dt{'ge_nose_sept_nt'},'EE1_Septum');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_nose_nt" id="ge_nose_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_nose_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_mouth" id="tmp_ge_mouth" type="checkbox" value="1" <?php echo (($dt['tmp_ge_mouth'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_mouth','tmp_ge_mouth_disp','tmp_ge_mouth_button_disp');" /><label for="tmp_ge_mouth">Mouth:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_mouth_norm_exam" id="ge_mouth_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_mouth_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMouthNormal('<?php echo $client_id; ?>',this);" /><label for="ge_mouth_norm_exam">Set Mouth Exam Normal</label></td>
			<td><div name="tmp_ge_mouth_button_disp" id="tmp_ge_mouth_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_mouth_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_mouth_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_mouth_disp" style="display: <?php echo $dt['tmp_ge_mouth_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ge_mouth','moist','Moist Mucus Membranes',$dt{'ge_mouth_moist'},$dt{'ge_mouth_moist_nt'}, 'width: 70%;');
			if($client_id == 'cffm') {
				EE1_GECheckLine('ge_mouth','les','Clear of Suspicious Lesions',$dt{'ge_mouth_les'},$dt{'ge_mouth_les_nt'});
				EE1_GESelLine('ge_mouth','dent','Dentition',$dt{'ge_mouth_dent'},$dt{'ge_mouth_dent_nt'},'EE1_Denture');
			}
			?>
			<tr>
				<td class="wmtBody" colspan="2">Gums</td>
			</tr>
			<?php
			EE1_GESelLine('ge_mouth','gm_red','Reddened',$dt{'ge_mouth_gm_red'},$dt{'ge_mouth_gm_red_nt'});
			EE1_GESelLine('ge_mouth','gm_swell','Swollen',$dt{'ge_mouth_gm_swell'},$dt{'ge_mouth_gm_swell_nt'});
			EE1_GESelLine('ge_mouth','gm_bld','Bleeding',$dt{'ge_mouth_gm_bld'},$dt{'ge_mouth_gm_bld_nt'});
			?>
			<tr>
				<td class="wmtBody" colspan="2">Teeth</td>
			</tr>
			<?php
			EE1_GESelLine('ge_mouth','th_car','Caries',$dt{'ge_mouth_th_car'},$dt{'ge_mouth_th_car_nt'});
			EE1_GESelLine('ge_mouth','th_pd','Poor Dentition',$dt{'ge_mouth_th_pd'},$dt{'ge_mouth_th_pd_nt'});
			EE1_GESelLine('ge_mouth','th_er','Erupting',$dt{'ge_mouth_th_er'},$dt{'ge_mouth_th_er_nt'});
			$mouth_list = array('sores', 'cracked_dry_lips', 'cheilosis',
				'perioral_cyanosis');
			EE1_GEMultiCheckLine('ge_mouth', $mouth_list, $mouth_chks);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_mouth_nt" id="ge_mouth_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_mouth_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_throat" id="tmp_ge_throat" type="checkbox" value="1" <?php echo (($dt['tmp_ge_throat'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_throat','tmp_ge_throat_disp','tmp_ge_throat_button_disp');" /><label for="tmp_ge_throat">Throat:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_thrt_norm_exam" id="ge_thrt_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_thrt_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThroatNormal('<?php echo $client_id; ?>',this);" /><label for="ge_thrt_norm_exam">Set Throat Exam Normal</label></td>
			<td><div name="tmp_ge_throat_button_disp" id="tmp_ge_throat_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_throat_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thrt_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_throat_disp" style="display: <?php echo $dt['tmp_ge_throat_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ge_thrt','ery','No Erythema',$dt{'ge_thrt_ery'},$dt{'ge_thrt_ery_nt'}, 'width: 70%;');
			EE1_GECheckLine('ge_thrt','exu','No Exudate',$dt{'ge_thrt_exu'},$dt{'ge_thrt_exu_nt'});
			?>
			<tr>
				<td class="wmtBody" colspan="2">Tonsils</td>
			</tr>
			<?php
			EE1_GESelLine('ge_thrt','ton_exu','Exudate',$dt{'ge_thrt_ton_exu'},$dt{'ge_thrt_ton_exu_nt'});
			EE1_GESelLine('ge_thrt','ton_en','Enlarged Size',$dt{'ge_thrt_ton_en'},$dt{'ge_thrt_ton_en_nt'}, 'tonsil_size');
			?>
			<tr>
				<td class="wmtBody" colspan="2">Uvula</td>
			</tr>
			<?php
			EE1_GESelLine('ge_thrt','uvu_mid','Midline',$dt{'ge_thrt_uvu_mid'},$dt{'ge_thrt_uvu_mid_nt'});
			EE1_GESelLine('ge_thrt','uvu_swell','Swollen',$dt{'ge_thrt_uvu_swell'},$dt{'ge_thrt_uvu_swell_nt'});
			EE1_GESelLine('ge_thrt','uvu_dev','Deviated',$dt{'ge_thrt_uvu_dev'},$dt{'ge_thrt_uvu_dev_nt'}, 'left_right');
			?>
			<tr>
				<td class="wmtBody" colspan="2">Palate</td>
			</tr>
			<?php
			EE1_GESelLine('ge_thrt','pal_swell','Swelling',$dt{'ge_thrt_pal_swell'},$dt{'ge_thrt_pal_swell_nt'});
			EE1_GESelLine('ge_thrt','pal_pet','Petechiae',$dt{'ge_thrt_pal_pet'},$dt{'ge_thrt_pal_pet_nt'});
			$thrt_list = array( 'peritonsillar_abscess', 'cobblestoning',
				'mucous_visible');
			EE1_GEMultiCheckLine('ge_thrt', $thrt_list, $thrt_chks);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_thrt_nt" id="ge_thrt_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_thrt_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_neck" id="tmp_ge_neck" type="checkbox" value="1" <?php echo (($dt['tmp_ge_neck'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_neck','tmp_ge_neck_disp','tmp_ge_neck_button_disp');" /><label for="tmp_ge_neck">Neck:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_nk_norm_exam" id="ge_nk_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_nk_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeckNormal('<?php echo $client_id; ?>',this);" /><label for="ge_nk_norm_exam">Set Neck Exam Normal</label></td>
			<td><div name="tmp_ge_neck_button_disp" id="tmp_ge_neck_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_neck_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>','ge_nk_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_neck_disp" style="display: <?php echo $dt['tmp_ge_neck_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
      	<td class="wmtBody wmtR"><input name="ge_nk_sup" id="ge_nk_sup" type="checkbox" value="1" <?php echo (($dt['ge_nk_sup'] == 1)?' checked':''); ?> onChange="document.getElementById('ge_nk_norm_exam').checked=false;" /></td>
				<td class="wmtBody"><label for="ge_nk_sup">Supple</label></td>
				<td class="wmtBody" style="width: 70%;">Notes:</td>
			</tr>
			<?php
			// EE1_GECheckLine('ge_nk','sup','Supple',$dt{'ge_nk_sup'},$dt{'ge_nk_sup_nt'}, 'width: 70%;');
			EE1_GESelTextArea('ge_nk','brit','Bruit',$dt{'ge_nk_brit'},$dt{'ge_nk_brit_nt'});
			EE1_GESelLine('ge_nk','jvp','JVP',$dt{'ge_nk_jvp'},'','Yes_No','',true,true);
			EE1_GESelLine('ge_nk','lymph','Lymphadenopathy',$dt{'ge_nk_lymph'},'','Yes_No','',true,true);
			EE1_GECheckLine('ge_nk','trach','Trachea Midline',$dt{'ge_nk_trach'},'', '', true, true);
			?>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_thyroid" id="tmp_ge_thyroid" type="checkbox" value="1" <?php echo (($dt['tmp_ge_thyroid'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_thyroid','tmp_ge_thyroid_disp','tmp_ge_thyroid_button_disp');" /><label for="tmp_ge_thyroid">Thyroid:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_thy_norm_exam" id="ge_thy_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_thy_norm_exam'] == 1)?' checked':''); ?> onChange="setGEThyroidNormal('<?php echo $client_id; ?>',this);" /><label for="ge_thy_norm_exam">Set Thyroid Exam Normal</label></td>
			<td><div name="tmp_ge_thyroid_button_disp" id="tmp_ge_thyroid_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_thyroid_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_thy_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_thyroid_disp" style="display: <?php echo $dt['tmp_ge_thyroid_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GECheckLine('ge_thy','norm','Normal Size',$dt{'ge_thy_norm'},$dt{'ge_thy_norm_nt'}, 'width: 70%;');
			EE1_GESelLine('ge_thy','nod','Nodules',$dt{'ge_thy_nod'},$dt{'ge_thy_nod_nt'});
			if($client_id != 'cffm' && $client_id != 'sfa') {
				EE1_GESelLine('ge_thy','brit','Bruit',$dt{'ge_thy_brit'},$dt{'ge_thy_brit_nt'});
			}
			EE1_GESelLine('ge_thy','tnd','Tenderness',$dt{'ge_thy_tnd'},$dt{'ge_thy_tnd_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_thy_nt" id="ge_thy_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_thy_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_lymph" id="tmp_ge_lymph" type="checkbox" value="1" <?php echo (($dt['tmp_ge_lymph'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_lymph','tmp_ge_lymph_disp','tmp_ge_lymph_button_disp');" /><label for="tmp_ge_lymph">Lymphadenopathy:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_lym_norm_exam" id="ge_lym_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_lym_norm_exam'] == 1)?' checked':''); ?> onChange="setGELymphNormal('<?php echo $client_id; ?>',this);" /><label for="ge_lym_norm_exam">Set Lymphadenopathy Exam Normal</label></td>
			<td><div name="tmp_ge_lymph_button_disp" id="tmp_ge_lymph_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_lymph_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_lym_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_lymph_disp" style="display: <?php echo $dt['tmp_ge_lymph_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ge_lym','cerv','Cervical',$dt{'ge_lym_cerv'},$dt{'ge_lym_cerv_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ge_lym','sup','Supraclavicular',$dt{'ge_lym_sup'},$dt{'ge_lym_sup_nt'});
			EE1_GESelLine('ge_lym','ax','Axillary',$dt{'ge_lym_ax'},$dt{'ge_lym_ax_nt'});
			EE1_GESelLine('ge_lym','in','Inguinal',$dt{'ge_lym_in'},$dt{'ge_lym_in_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_lym_nt" id="ge_lym_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_lym_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_breast" id="tmp_ge_breast" type="checkbox" value="1" <?php echo (($dt['tmp_ge_breast'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_breast','tmp_ge_breast_disp','tmp_ge_breast_button_disp');" /><label for="tmp_ge_breast">Breasts:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_br_norm_exam" id="ge_br_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_br_norm_exam'] == 1)?' checked':''); ?> onChange="setGEBreastNormal('<?php echo $client_id; ?>',this);" /><label for="ge_br_norm_exam">Set Breast Exam Normal</label></td>
			<td><div name="tmp_ge_breast_button_disp" id="tmp_ge_breast_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_breast_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_br'); clearGESection('<?php echo $client_id; ?>', 'ge_nip'); "><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_breast_disp" style="display: <?php echo $dt['tmp_ge_breast_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			if($client_id == 'cffm' || $client_id == 'sfa') {
				EE1_GESelLine('ge_br','sym','Symmetrical',$dt{'ge_br_sym'},$dt{'ge_br_sym_nt'}, 'Yes_No', 'width: 70%;');
			}
			?>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Breast</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_brr','axil','Axillary Nodes',$dt{'ge_brr_axil'},$dt{'ge_brr_axil_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ge_brr','mass','Mass/Lesion',$dt{'ge_brr_mass'},$dt{'ge_brr_mass_nt'});
			EE1_GESelLine('ge_brr','tan','Tanner',$dt{'ge_brr_tan'},$dt{'ge_brr_tan_nt'}, 'one_to_five');
			EE1_GESelLine('ge_brr','chng','Skin Changes',$dt{'ge_brr_chng'},$dt{'ge_brr_chng_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_brr_nt" id="ge_brr_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_brr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Right Nipple</td>
    	</tr>

			<?php
			EE1_GESelLine('ge_nipr','ev','Everted',$dt{'ge_nipr_ev'},$dt{'ge_nipr_ev_nt'});
			EE1_GESelLine('ge_nipr','in','Inverted',$dt{'ge_nipr_in'},$dt{'ge_nipr_in_nt'});
			EE1_GESelLine('ge_nipr','mass','Mass',$dt{'ge_nipr_mass'},$dt{'ge_nipr_mass_nt'});
			EE1_GESelLine('ge_nipr','dis','Discharge',$dt{'ge_nipr_dis'},$dt{'ge_nipr_dis_nt'});
			EE1_GESelLine('ge_nipr','ret','Retraction',$dt{'ge_nipr_ret'},$dt{'ge_nipr_ret_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_nipr_nt" id="ge_nipr_nt" class="FullInput" rows="2"><?php echo htmlspecialchars($dt{'ge_nipr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Breast</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_brl','axil','Axillary Nodes',$dt{'ge_brl_axil'},$dt{'ge_brl_axil_nt'});
			EE1_GESelLine('ge_brl','mass','Mass/Lesion',$dt{'ge_brl_mass'},$dt{'ge_brl_mass_nt'});
			EE1_GESelLine('ge_brl','tan','Tanner',$dt{'ge_brl_tan'},$dt{'ge_brl_tan_nt'}, 'one_to_five');
			EE1_GESelLine('ge_brl','chng','Skin Changes',$dt{'ge_brl_chng'},$dt{'ge_brl_chng_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_brl_nt" id="ge_brl_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_brl_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
    	<tr>
      	<td class="wmtLabel" colspan="3">Left Nipple</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_nipl','ev','Everted',$dt{'ge_nipl_ev'},$dt{'ge_nipl_ev_nt'});
			EE1_GESelLine('ge_nipl','in','Inverted',$dt{'ge_nipl_in'},$dt{'ge_nipl_in_nt'});
			EE1_GESelLine('ge_nipl','mass','Mass',$dt{'ge_nipl_mass'},$dt{'ge_nipl_mass_nt'});
			EE1_GESelLine('ge_nipl','dis','Discharge',$dt{'ge_nipl_dis'},$dt{'ge_nipl_dis_nt'});
			EE1_GESelLine('ge_nipl','ret','Retraction',$dt{'ge_nipl_ret'},$dt{'ge_nipl_ret_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_nipl_nt" id="ge_nipl_nt" class="FullInput" rows="2"><?php echo htmlspecialchars($dt{'ge_nipl_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_cardio" id="tmp_ge_cardio" type="checkbox" value="1" <?php echo (($dt['tmp_ge_cardio'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_cardio','tmp_ge_cardio_disp','tmp_ge_cardio_button_disp');" /><label for="tmp_ge_cardio">Cardiovascular:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_cr_norm_exam" id="ge_cr_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_cr_norm_exam'] == 1)?' checked':''); ?> onChange="setGECardioNormal('<?php echo $client_id; ?>',this);" /><label for="ge_cr_norm_exam">Set Cardiovascular Exam Normal</label></td>
			<td><div name="tmp_ge_cardio_button_disp" id="tmp_ge_cardio_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_cardio_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_cr_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_cardio_disp" style="display: <?php echo $dt['tmp_ge_cardio_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
			<?php
			EE1_GESelLine('ge_cr','norm','Regular Rate &amp; Rhythm',$dt{'ge_cr_norm'},$dt{'ge_cr_norm_nt'}, 'Yes_No', 'width: 70%;');
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ge_cr_mur" id="ge_cr_mur" class="Input" onchange="toggleLineDetail(this, 'ge_cr_mur_dtl', 'tmp_mur_dtl'); document.getElementById('ge_cr_norm_exam').checked=false;" >
					<?php ListSel($dt{'ge_cr_mur'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Murmur&nbsp;&nbsp;
					<select name="ge_cr_mur_dtl" id="ge_cr_mur_dtl" class="Input" style="display: <?php echo (($dt{'ge_cr_mur'} == 'y') ? 'inline' : 'none'); ?>;" ><?php ListSel($dt{'ge_cr_mur_dtl'}, 'one_to_six'); ?></select><span id="tmp_mur_dtl" style="display: <?php echo (($dt{'ge_cr_mur'} == 'y') ? 'inline' : 'none'); ?>" >&nbsp;&nbsp;/&nbsp;6</span></td>
      	<td><input name="ge_cr_mur_nt" id="ge_cr_mur_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_cr_mur_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ge_cr_norm_exam').checked=false;" /></td>
    	</tr>
			<?php
			EE1_GESelLine('ge_cr','gall','Gallops',$dt{'ge_cr_gall'},$dt{'ge_cr_gall_nt'});
			EE1_GESelLine('ge_cr','click','Clicks',$dt{'ge_cr_click'},$dt{'ge_cr_click_nt'});
			EE1_GESelLine('ge_cr','rubs','Rubs',$dt{'ge_cr_rubs'},$dt{'ge_cr_rubs_nt'});
			EE1_GESelLine('ge_cr','extra','Extra Sound',$dt{'ge_cr_extra'},$dt{'ge_cr_extra_nt'});
			EE1_GESelLine('ge_cr','pmi','PMI in 5th ICS in MCL',$dt{'ge_cr_pmi'},$dt{'ge_cr_pmi_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
      	<td colspan="3"><textarea name="ge_cr_nt" id="ge_cr_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_cr_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_pulmo" id="tmp_ge_pulmo" type="checkbox" value="1" <?php echo (($dt['tmp_ge_pulmo'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_pulmo','tmp_ge_pulmo_disp','tmp_ge_pulmo_button_disp');" /><label for="tmp_ge_pulmo">Pulmonary:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_pul_norm_exam" id="ge_pul_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_pul_norm_exam'] == 1)?' checked':''); ?> onChange="setGEPulmoNormal('<?php echo $client_id; ?>',this);" /><label for="ge_pul_norm_exam">Set Pulmonary Exam Normal</label></td>
			<td><div name="tmp_ge_pulmo_button_disp" id="tmp_ge_pulmo_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_pulmo_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_pul_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_pulmo_disp" style="display: <?php echo $dt['tmp_ge_pulmo_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    	<tr>
      	<td class="wmtBody wmtR"><select name="ge_pul_clear" id="ge_pul_clear" class="Input" onChange="document.getElementById('ge_pul_norm_exam').checked=false;" >
					<?php ListSel($dt{'ge_pul_clear'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Clear to Auscultation</td>
      	<td class="wmtBody" style="width: 70%;">Notes:</td>
    	</tr>
			<?php
			EE1_GESelTextArea('ge_pul','rales','Rales',$dt{'ge_pul_rales'},$dt{'ge_pul_rales_nt'}, 'Yes_No', '', true, '5');
			EE1_GESelLine('ge_pul','whz','Wheezes',$dt{'ge_pul_whz'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ge_pul','ron','Rhonchi',$dt{'ge_pul_ron'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ge_pul','dec','Decreased Breath Sounds',$dt{'ge_pul_dec'},'', 'Yes_No', '', true, true);
			EE1_GESelLine('ge_pul','crack','Crackles',$dt{'ge_pul_crack'}, '', 'Yes_No', '', true, true);
			?>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_gastro" id="tmp_ge_gastro" type="checkbox" value="1" <?php echo (($dt['tmp_ge_gastro'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_gastro','tmp_ge_gastro_disp','tmp_ge_gastro_button_disp');" /><label for="tmp_ge_gastro">Gastrointestinal:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_gi_norm_exam" id="ge_gi_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_gi_norm_exam'] == 1)?' checked':''); ?> onChange="setGEGastroNormal('<?php echo $client_id; ?>',this);" /><label for="ge_gi_norm_exam">Set Gastrointestinal Exam Normal</label></td>
			<td><div name="tmp_ge_gastro_button_disp" id="tmp_ge_gastro_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_gastro_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_gi_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_gastro_disp" style="display: <?php echo $dt['tmp_ge_gastro_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php
			EE1_GESelLine('ge_gi','soft','Soft',$dt{'ge_gi_soft'},$dt{'ge_gi_soft_nt'}, 'Yes_No', 'width: 70%;');
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ge_gi_tend" id="ge_gi_tend" class="Input" onchange="document.getElementById('ge_gi_norm_exam').checked = false;" >
					<?php
					if($client_id == 'sfa') {
						ListSel($dt{'ge_gi_tend'},'Yes_No');
					} else {
						ListSel($dt{'ge_gi_tend'},'EE1_Tender');
					}
					?>
				</select></td>
      	<td class="wmtBody">Tender&nbsp;&nbsp;<select name="ge_gi_tend_loc" id="ge_gi_tend_loc" class="Input" onchange="document.getElementById('ge_gi_norm_exam').checked = false;" >
					<?php ListSel($dt{'ge_gi_tend_loc'},'EE1_GI_Location'); ?>
				</select></td>
				<td><input name="ge_gi_tend_nt" id="ge_gi_tend_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_gi_tend_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ge_gi_norm_exam').checked=false;" /></td> 
    	</tr>
			<?php
			EE1_GESelLine('ge_gi','dis','Distended',$dt{'ge_gi_dis'},$dt{'ge_gi_dis_nt'}, 'EE1_Distended');
			EE1_GESelLine('ge_gi','scar','Scar',$dt{'ge_gi_scar'},$dt{'ge_gi_scar_nt'});
			EE1_GESelLine('ge_gi','asc','Ascites',$dt{'ge_gi_asc'},$dt{'ge_gi_asc_nt'});
			EE1_GESelLine('ge_gi','pnt','Point Tenderness',$dt{'ge_gi_pnt'},$dt{'ge_gi_pnt_nt'});
			EE1_GESelLine('ge_gi','grd','Guarding',$dt{'ge_gi_grd'},$dt{'ge_gi_grd_nt'});
			EE1_GESelLine('ge_gi','reb','Rebound',$dt{'ge_gi_reb'},$dt{'ge_gi_reb_nt'});
			EE1_GESelLine('ge_gi','mass','Mass',$dt{'ge_gi_mass'},$dt{'ge_gi_mass_nt'});
			EE1_GESelLine('ge_gi','hern','Hernia',$dt{'ge_gi_hern'},$dt{'ge_gi_hern_nt'});
			$hrn_list = array( 'ventral', 'incisional', 'umbilical', 'inguinal');
			EE1_GEMultiCheckLine('ge_gi_her', $hrn_list, $hrn_chks);
			?>
    	<tr>
      	<td class="wmtBody wmtR"><select name="ge_gi_bowel" id="ge_gi_bowel" class="Input" onchange="toggleLineDetail(this, 'ge_gi_bwl_dtl'); document.getElementById('ge_gi_norm_exam').checked=false;" >
					<?php ListSel($dt{'ge_gi_bowel'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Bowel Sounds&nbsp;&nbsp;
					<select name="ge_gi_bwl_dtl" id="ge_gi_bwl_dtl" class="Input" style="display: <?php echo (($dt{'ge_gi_bowel'} == 'y')?'inline':'none'); ?>;" ><?php ListSel($dt{'ge_gi_bwl_dtl'}, 'bowel_detail'); ?></select></span></td>
      	<td><input name="ge_gi_bowel_nt" id="ge_gi_bowel_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_gi_bowel_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ge_gi_norm_exam').checked=false;" /></td>
    	</tr>

			<?php
			EE1_GESelLine('ge_gi','hepa','Hepatomegaly',$dt{'ge_gi_hepa'},$dt{'ge_gi_hepa_nt'});
			EE1_GESelLine('ge_gi','spleno','Splenomegaly',$dt{'ge_gi_spleno'},$dt{'ge_gi_spleno_nt'});
			?>
			<tr>
				<td class="wmtLabel">Notes:</td>
			<tr>
				<td colspan="3"><textarea name="ge_gi_nt" id="ge_gi_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_gi_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
			</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_neuro" id="tmp_ge_neuro" type="checkbox" value="1" <?php echo (($dt['tmp_ge_neuro'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_neuro','tmp_ge_neuro_disp','tmp_ge_neuro_button_disp');" /><label for="tmp_ge_neuro">Neurological:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_neu_norm_exam" id="ge_neu_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_neu_norm_exam'] == 1)?' checked':''); ?> onChange="setGENeuroNormal('<?php echo $client_id; ?>',this);" /><label for="ge_neu_norm_exam">Set Neurological Exam Normal</label></td>
			<td><div name="tmp_ge_neuro_button_disp" id="tmp_ge_neuro_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_neuro_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_neu_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_neuro_disp" style="display: <?php echo $dt['tmp_ge_neuro_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php
			EE1_GESelLine('ge_neu','ao','Alert &amp; Oriented',$dt{'ge_neu_ao'},$dt{'ge_neu_ao_nt'}, 'EE1_AO', 'width: 70%');
			EE1_GESelLine('ge_neu','cn','CN II - XII Intact',$dt{'ge_neu_cn'},$dt{'ge_neu_cn_nt'});
			?>
    	<tr>
      	<td colspan="2" class="wmtLabel">DTRs</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_neu','bicr','Right Bicep',$dt{'ge_neu_bicr'},$dt{'ge_neu_bicr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','bicl','Left Bicep',$dt{'ge_neu_bicl'},$dt{'ge_neu_bicl_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','trir','Right Tricep',$dt{'ge_neu_trir'},$dt{'ge_neu_trir_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','tril','Left Tricep',$dt{'ge_neu_tril'},$dt{'ge_neu_tril_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','brar','Right Brachioradialis',$dt{'ge_neu_brar'},$dt{'ge_neu_brar_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','bral','Left Brachioradialis',$dt{'ge_neu_bral'},$dt{'ge_neu_bral_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','patr','Right Patella',$dt{'ge_neu_patr'},$dt{'ge_neu_patr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','patl','Left Patella',$dt{'ge_neu_patl'},$dt{'ge_neu_patl_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','achr','Right Achilles',$dt{'ge_neu_achr'},$dt{'ge_neu_achr_nt'}, 'EE1_DTR');
			EE1_GESelLine('ge_neu','achl','Left Achilles',$dt{'ge_neu_achl'},$dt{'ge_neu_achl_nt'}, 'EE1_DTR');
			?>
    	<tr>
      	<td class="wmtLabel" colspan="2">Strength</td>
    	</tr>
			<?php
			EE1_GESelLine('ge_neu','pup','Proximal Upper',$dt{'ge_neu_pup'},$dt{'ge_neu_pup_nt'}, 'Zero_to_5');
			EE1_GESelLine('ge_neu','plow','Proximal Lower',$dt{'ge_neu_plow'},$dt{'ge_neu_plow_nt'}, 'Zero_to_5');
			EE1_GESelLine('ge_neu','dup','Distal Upper',$dt{'ge_neu_dup'},$dt{'ge_neu_dup_nt'}, 'Zero_to_5');
			EE1_GESelLine('ge_neu','dlow','Distal Lower',$dt{'ge_neu_dlow'},$dt{'ge_neu_dlow_nt'}, 'Zero_to_5');
			EE1_GESelLine('ge_neu','tn','Tone',$dt{'ge_neu_tn'},$dt{'ge_neu_tn_nt'}, 'neuro_tone');
			?>
    	<tr>
      	<td class="wmtLabel" colspan="2">Coordination / Cerebellar</td>
    	</tr>
			<tr>
				<td class="wmtBody wmtR"><input name="ge_neu_cc_norm" id="ge_neu_cc_norm" type="checkbox" value="1" <?php echo (($dt{'ge_neu_cc_norm'} == 1)?'checked':''); ?> onchange="toggleCCNorm(this);" /></td>
				<td class="wmtBody"><label for="ge_neu_cc_norm">No Abnormalities</td>
				<td>&nbsp;</td>
			</tr>
			<?
			EE1_GESelLine('ge_neu','cc_fn','Finger / Nose',$dt{'ge_neu_cc_fn'},$dt{'ge_neu_cc_fn_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ge_neu','cc_hs','Heel / Shin',$dt{'ge_neu_cc_hs'},$dt{'ge_neu_cc_hs_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ge_neu','cc_ra','Rapid Alternating',$dt{'ge_neu_cc_ra'},$dt{'ge_neu_cc_ra_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ge_neu','cc_rm','Romberg',$dt{'ge_neu_cc_rm'},$dt{'ge_neu_cc_rm_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			EE1_GESelLine('ge_neu','cc_pd','Pronator Drift',$dt{'ge_neu_cc_pd'},$dt{'ge_neu_cc_pd_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			if($client_id != 'cffm') {
				EE1_GESelLine('ge_neu','sns_chc','Sensation',$dt{'ge_neu_sns_chc'},$dt{'ge_neu_sns_chc_nt'}, 'NormAbnorm', '', true, false, 'cc_norm');
			}
			?>
			
    	<tr>
      	<td class="wmtBody"><?php echo (($client_id != 'cffm') ? 'Notes:' : 'Sensation:'); ?></td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_neu_sense" id="ge_neu_sense" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_neu_sense'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_musc" id="tmp_ge_musc" type="checkbox" value="1" <?php echo (($dt['tmp_ge_musc'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_musc','tmp_ge_musc_disp','tmp_ge_musc_button_disp');" /><label for="tmp_ge_musc">Musculoskeletal / Back:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_ms_norm_exam" id="ge_ms_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_ms_norm_exam'] == 1)?' checked':''); ?> onChange="setGEMuscNormal('<?php echo $client_id; ?>',this);" /><label for="ge_ms_norm_exam">Set Musculoskeletal/Back Exam Normal</label></td>
			<td><div name="tmp_ge_musc_button_disp" id="tmp_ge_musc_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_musc_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ms_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_musc_disp" style="display: <?php echo $dt['tmp_ge_musc_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			if($client_id == 'cffm') {
				EE1_GECheckLine('ge_ms','intact','Intact w/o Atrophy',$dt{'ge_ms_intact'},$dt{'ge_ms_intact_nt'});
			}
			EE1_GESelLine('ge_ms','mass','Mass',$dt{'ge_ms_mass'},$dt{'ge_ms_mass_nt'}, 'Yes_No', 'width: 70%');
			EE1_GESelLine('ge_ms','tnd','Tenderness',$dt{'ge_ms_tnd'},$dt{'ge_ms_tnd_nt'});
			EE1_GESelLine('ge_ms','scl','Scoliosis',$dt{'ge_ms_scl'},$dt{'ge_ms_scl_nt'});
			EE1_GESelLine('ge_ms','cval','CVA Tenderness on L',$dt{'ge_ms_cval'},$dt{'ge_ms_cval_nt'});
			EE1_GESelLine('ge_ms','cvar','CVA Tenderness on R',$dt{'ge_ms_cvar'},$dt{'ge_ms_cvar_nt'});
			EE1_GESelLine('ge_ms','lim','ROM Limited',$dt{'ge_ms_lim'},$dt{'ge_ms_lim_nt'});
			EE1_GESelLine('ge_ms','def','Deformity',$dt{'ge_ms_def'},$dt{'ge_ms_def_nt'});
			EE1_GESelLine('ge_ms','full','ROM Full',$dt{'ge_ms_full'},$dt{'ge_ms_full_nt'});
			EE1_GESelLine('ge_ms','gait','Gait',$dt{'ge_ms_gait'},$dt{'ge_ms_gait_nt'}, 'NormAbnorm');
			$ms_list = array( 'wheelchair', 'walker', 'prosthetics_/_orthotics');
			EE1_GEMultiCheckLine('ge_ms', $ms_list, $ms_chks);
			EE1_GECheckLine('ge_ms','norm','Moves all extremities well and equally',$dt{'ge_ms_norm'}, '', '', true, true);
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_ms_nt" id="ge_ms_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ge_ms_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_ext" id="tmp_ge_ext" type="checkbox" value="1" <?php echo (($dt['tmp_ge_ext'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_ext','tmp_ge_ext_disp','tmp_ge_ext_button_disp');" /><label for="tmp_ge_ext">Extremities:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_ext_norm_exam" id="ge_ext_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_ext_norm_exam'] == 1)?' checked':''); ?> onChange="setGEExtNormal('<?php echo $client_id; ?>',this);" /><label for="ge_ext_norm_exam">Set Extremities Exam Normal</label></td>
			<td><div name="tmp_ge_ext_button_disp" id="tmp_ge_ext_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_ext_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_ext_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_ext_disp" style="display: <?php echo $dt['tmp_ge_ext_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
    	<tr>
      	<td class="wmtBody wmtR"><select name="ge_ext_edema" id="ge_ext_edema" class="Input" onchange="toggleLineDetail(this, 'ge_ext_edema_chc'); document.getElementById('ge_ext_norm_exam').checked=false; ">
						<?php ListSel($dt{'ge_ext_edema'},'Yes_No'); ?>
				</select></td>
      	<td class="wmtBody">Edema&nbsp;&nbsp;&nbsp;<select name="ge_ext_edema_chc" id="ge_ext_edema_chc" class="Input" style="display: <?php echo (($dt{'ge_ext_edema'} == 'y')? 'inline' : 'none'); ?>;" >
					<?php ListSel($dt{'ge_ext_edema_chc'},'Edema'); ?>
				</select></td>
      	<td style="width: 70%;"><input name="ge_ext_edema_nt" id="ge_ext_edema_nt" class="FullInput" type="text" value="<?php echo htmlspecialchars($dt{'ge_ext_edema_nt'}, ENT_QUOTES, '', FALSE); ?>" onchange="document.getElementById('ge_ext_norm_exam').checked=false; "/></td>
			</tr>

    	<tr>
      	<td class="wmtLabel" colspan="2">Pulses</td>
    	</tr>
			<?php 
			EE1_GESelLine('ge_ext','pls_rad','Radial',$dt{'ge_ext_pls_rad'}, '', 'Zero_to_4', 'width: 70%', true, true);
			EE1_GESelLine('ge_ext','pls_dors','Dorsalis Pedis',$dt{'ge_ext_pls_dors'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ge_ext','pls_post','Posterior Tibial',$dt{'ge_ext_pls_post'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ge_ext','pls_pop','Popliteal',$dt{'ge_ext_pls_pop'}, '', 'Zero_to_4', '', true, true);
			EE1_GESelLine('ge_ext','pls_fem','Femoral',$dt{'ge_ext_pls_fem'}, '', 'Zero_to_4', '', true, true);
			?>
			<tr>
				<td class="wmtLabel" colspan="2">Capillary Refill</td>
			</tr>
			<?php
			EE1_GESelLine('ge_ext','refill',' &lt; 3 Seconds',$dt{'ge_ext_refill'}, '', 'Yes_No', '', true, true);
			EE1_GESelLine('ge_ext','club','Clubbing',$dt{'ge_ext_club'}, $dt{'ge_ext_club_nt'});
			EE1_GESelLine('ge_ext','cyan','Cyanosis',$dt{'ge_ext_cyan'}, $dt{'ge_ext_cyan_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_ext_nt" id="ge_ext_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_ext_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_dia" id="tmp_ge_dia" type="checkbox" value="1" <?php echo (($dt['tmp_ge_dia'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_dia','tmp_ge_dia_disp','tmp_ge_dia_button_disp');" /><label for="tmp_ge_dia">Diabetic Foot:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_db_norm_exam" id="ge_db_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_db_norm_exam'] == 1)?' checked':''); ?> onChange="setGEFootNormal('<?php echo $client_id; ?>',this);" /><label for="ge_db_norm_exam">Set Diabetic Foot Exam Normal</label></td>
			<td><div name="tmp_ge_dia_button_disp" id="tmp_ge_dia_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_dia_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_db_');"><span>Clear</span></a></div></td>
			</div></td>
		</tr>
		</table>
		<div id="tmp_ge_dia_disp" style="display: <?php echo $dt['tmp_ge_dia_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GESelLine('ge_db','prop','Proprioception',$dt{'ge_db_prop'}, $dt{'ge_db_prop_nt'}, 'NormAbnorm', 'width: 70%;');
			EE1_GESelLine('ge_db','vib','Vibration Sense',$dt{'ge_db_vib'}, $dt{'ge_db_vib_nt'}, 'NormAbnorm');
			EE1_GESelLine('ge_db','sens','Sensation to Monofilament Testing',$dt{'ge_db_sens'}, $dt{'ge_db_sens_nt'}, 'NormAbnorm');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_db_nt" id="ge_db_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_db_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
			</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_test" id="tmp_ge_test" type="checkbox" value="1" <?php echo (($dt['tmp_ge_test'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_test','tmp_ge_test_disp','tmp_ge_test_button_disp');" /><label for="tmp_ge_test">Genitalia:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_te_norm_exam" id="ge_te_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_te_norm_exam'] == 1)?' checked':''); ?> onChange="setGETestesNormal('<?php echo $client_id; ?>',this);" /><label for="ge_te_norm_exam">Set Genitalia Exam Normal</label></td>
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
				EE1_GESelLine('ge_te','cir','Circumcised',$dt{'ge_te_cir'}, $dt{'ge_te_cir_nt'}, 'Yes_No', 'width: 70%;');
				EE1_GESelLine('ge_te','les','Lesions',$dt{'ge_te_les'}, $dt{'ge_te_les_nt'});
				EE1_GESelLine('ge_te','dis','Discharge',$dt{'ge_te_dis'}, $dt{'ge_te_dis_nt'});
				EE1_GESelLine('ge_te','size','Testes Size',$dt{'ge_te_size'}, $dt{'ge_te_size_nt'}, 'EE1_Testes_Size');
				EE1_GESelLine('ge_te','palp','Palpitaation',$dt{'ge_te_palp'}, $dt{'ge_te_palp_nt'}, 'HardSoft');
				EE1_GESelLine('ge_te','mass','Mass',$dt{'ge_te_mass'}, $dt{'ge_te_mass_nt'});
				EE1_GESelLine('ge_te','tend','Tender',$dt{'ge_te_tend'}, $dt{'ge_te_tend_nt'});
				EE1_GESelLine('ge_te','ery','Erythema',$dt{'ge_te_ery'}, $dt{'ge_te_ery_nt'});
			} else {
				EE1_GESelLine('ge_te','lmaj','Labia Majora',$dt{'ge_te_lmaj'}, $dt{'ge_te_lmaj_nt'}, 'NormAbnorm', 'width: 70%;');
				EE1_GESelLine('ge_te','lmin','Labia Minora',$dt{'ge_te_lmin'}, $dt{'ge_te_lmin_nt'}, 'NormAbnorm');
				EE1_GESelLine('ge_te','intro','Introitus',$dt{'ge_te_intro'}, $dt{'ge_te_intro_nt'}, 'NormAbnorm');
				EE1_GESelLine('ge_te','urethra','Urethra',$dt{'ge_te_urethra'}, $dt{'ge_te_urethra_nt'}, 'NormAbnorm');
				EE1_GESelLine('ge_te','clit','Clitorus',$dt{'ge_te_clit'}, $dt{'ge_te_clit'}, 'NormAbnorm');
			}
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_te_nt" id="ge_te_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_te_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>

    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_rectal" id="tmp_ge_rectal" type="checkbox" value="1" <?php echo (($dt['tmp_ge_rectal'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_rectal','tmp_ge_rectal_disp','tmp_ge_rectal_button_disp');" /><label for="tmp_ge_rectal">Rectal:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_rc_norm_exam" id="ge_rc_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_rc_norm_exam'] == 1)?' checked':''); ?> onChange="setGERectalNormal('<?php echo $pat_sex; ?>','<?php echo $client_id; ?>',this);" /><label for="ge_rc_norm_exam">Set Rectal Exam Normal</label></td>
			<td><div name="tmp_ge_rectal_button_disp" id="tmp_ge_rectal_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_rectal_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_rc_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_rectal_disp" style="display: <?php echo $dt['tmp_ge_rectal_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GESelLine('ge_rc','tone','Tone',$dt{'ge_rc_tone'}, $dt{'ge_rc_tone_nt'}, 'EE1_Tone', 'width: 70%;');
			EE1_GESelLine('ge_rc','ext','External Hemorrhoid',$dt{'ge_rc_ext'}, $dt{'ge_rc_ext_nt'});
			if($pat_sex != 'f') {
				EE1_GESelLine('ge_rc','pro','Prostate Size',$dt{'ge_rc_pro'}, $dt{'ge_rc_pro_nt'}, 'EE1_Prostate');
			}
			EE1_GESelLine('ge_rc','bog','Boggy',$dt{'ge_rc_bog'}, $dt{'ge_rc_bog_nt'});
			EE1_GESelLine('ge_rc','hard','Hard',$dt{'ge_rc_hard'}, $dt{'ge_rc_hard_nt'});
			EE1_GESelLine('ge_rc','mass','Masses',$dt{'ge_rc_mass'}, $dt{'ge_rc_mass_nt'});
			EE1_GESelLine('ge_rc','tend','Tender',$dt{'ge_rc_tend'}, $dt{'ge_rc_tend_nt'});
			$lbl = ($client_id == 'cffm') ? 'Stool GWIAC' : 'Stool';
			EE1_GESelLine('ge_rc','color',$lbl,$dt{'ge_rc_color'}, $dt{'ge_rc_color_nt'},'EE1_Stool_Color');
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_rc_nt" id="ge_rc_nt" class="FullInput" rows="3"><?php echo htmlspecialchars($dt{'ge_rc_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    	</tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_skin" id="tmp_ge_skin" type="checkbox" value="1" <?php echo (($dt['tmp_ge_skin'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_skin','tmp_ge_skin_disp','tmp_ge_skin_button_disp');" /><label for="tmp_ge_skin">Skin:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_skin_norm_exam" id="ge_skin_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_skin_norm_exam'] == 1)?' checked':''); ?> onChange="setGESkinNormal('<?php echo $client_id; ?>',this);" /><label for="ge_skin_norm_exam">Set Skin Exam Normal</label></td>
			<td><div name="tmp_ge_skin_button_disp" id="tmp_ge_skin_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_skin_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_skin_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_skin_disp" style="display: <?php echo $dt['tmp_ge_skin_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
		<?php 
		if($client_id == 'cffm') {
			EE1_GECheckLine('ge_skin','app','Normal Appendages',$dt{'ge_skin_app'}, $dt{'ge_skin_app_nt'}, 'width: 70%;');
			EE1_GECheckLine('ge_skin','les','No Suspicious Lesions Noted',$dt{'ge_skin_les'}, $dt{'ge_skin_app_nt'});
			EE1_GESelLine('ge_skin','ver','Veracities',$dt{'ge_skin_ver'}, $dt{'ge_skin_ver_nt'});
		} else {
			EE1_GESelLine('ge_skin','jau','Jaundice',$dt{'ge_skin_jau'}, $dt{'ge_skin_jau_nt'}, 'Yes_No', 'width: 70%;');
			EE1_GESelLine('ge_skin','con','Contusion',$dt{'ge_skin_con'}, $dt{'ge_skin_con_nt'});
			EE1_GESelLine('ge_skin','ecc','Ecchymosis',$dt{'ge_skin_ecc'}, $dt{'ge_skin_ecc_nt'});
			EE1_GESelLine('ge_skin','rash','Rash',$dt{'ge_skin_rash'}, $dt{'ge_skin_rash_nt'});
			EE1_GESelLine('ge_skin','abs','Abscess/Cellulitis',$dt{'ge_skin_abs'}, $dt{'ge_skin_abs_nt'});
			EE1_GESelLine('ge_skin','lac','Laceration/Abrasion',$dt{'ge_skin_lac'}, $dt{'ge_skin_lac_nt'});
		}
		?>
    <tr>
      <td class="wmtLabel">Notes:</td>
		</tr>
    <tr>
			<td colspan="3"><textarea name="ge_skin_nt" id="ge_skin_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ge_skin_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
    </tr>
		</table>
		</fieldset>
		</div>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
    <tr>
      <td class="wmtLabel" style="width: 20%;"><input name="tmp_ge_psych" id="tmp_ge_psych" type="checkbox" value="1" <?php echo (($dt['tmp_ge_psych'] == 1)?' checked':''); ?> onChange="toggleGenSubSection('tmp_ge_psych','tmp_ge_psych_disp','tmp_ge_psych_button_disp');" /><label for="tmp_ge_psych">Psychiatric:</label></td>
      <td class="wmtBody" style="width: 30%;"><input name="ge_psych_norm_exam" id="ge_psych_norm_exam" type="checkbox" value="1" <?php echo (($dt['ge_psych_norm_exam'] == 1)?' checked':''); ?> onChange="setGEPsychNormal('<?php echo $client_id; ?>',this);" /><label for="ge_psych_norm_exam">Set Psychiatric Exam Normal</label></td>
			<td><div name="tmp_ge_psych_button_disp" id="tmp_ge_psych_button_disp" style="float: left; display: <?php echo $dt['tmp_ge_psych_disp']; ?>;"><a href="javascript:;" tabindex="-1" class="css_button" onClick="clearGESection('<?php echo $client_id; ?>', 'ge_psych_');"><span>Clear</span></a></div></td>
		</tr>
		</table>
		<div id="tmp_ge_psych_disp" style="display: <?php echo $dt['tmp_ge_psych_disp']; ?>;">
		<fieldset style="margin: 5px; padding: 5px;">
		<table width="100%" border="0" cellpadding="0" cellspacing="0">
			<?php 
			EE1_GECheckLine('ge_psych','judge','Assessment of Judgment/Insight',$dt{'ge_psych_judge'}, $dt{'ge_psych_judge_nt'}, 'width: 70%;');
			EE1_GECheckLine('ge_psych','orient','Orientation to Time, Place, Person',$dt{'ge_psych_orient'}, $dt{'ge_psych_orient_nt'});
			EE1_GECheckLine('ge_psych','memory','Assessment of Memory (Recent/Remoter)',$dt{'ge_psych_memory'}, $dt{'ge_psych_memory_nt'});
			EE1_GECheckLine('ge_psych','mood','Assessment of Mood/Affect',$dt{'ge_psych_mood'}, $dt{'ge_psych_mood_nt'});
			?>
    	<tr>
      	<td class="wmtLabel">Notes:</td>
			</tr>
    	<tr>
				<td colspan="3"><textarea name="ge_psych_nt" id="ge_psych_nt" class="FullInput" rows="4"><?php echo htmlspecialchars($dt{'ge_psych_nt'}, ENT_QUOTES, '', FALSE); ?></textarea></td>
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
<?php 
} // END OF DRAW DISPLAY
?>
