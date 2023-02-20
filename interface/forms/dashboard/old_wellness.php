

<?php if($wellness_display) { ?>
<div class="wmtMainContainer"><!-- Start of the Wellness Box -->
	<div class="wmtCollapseBar" id="DBWellCollapseBar" style="border-bottom: <?php echo (($dt['tmp_well_disp_mode'] == 'block')?'solid 1px black':'none'); ?>" onclick="togglePanel('DBWellBox','DBWellImageL','DBWellImageR','DBWellCollapseBar','','tmp_well_disp_mode')">
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<?php
			if($dt['tmp_well_disp_mode'] == 'block') {
				echo "<td><img id='DBWellImageL' src='../../../library/wmt/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Wellness</td>\n";
				echo "<td style='text-align: right'><img id='DBWellImageR' src='../../../library/wmt/fill-090.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			} else {
				echo "<td><img id='DBWellImageL' src='../../../library/wmt/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
				echo "<td class='wmtChapter'>Wellness</td>\n";
				echo "<td style='text-align: right'><img id='DBWellImageR' src='../../../library/wmt/fill-270.png' border='0' alt='Show/Hide' title='Show/Hide' /></td>\n";
			}
			?>
		</tr>
		</table>
	</div><!-- End of the General Exam collapse Bar -->
	<div id="DBWellBox" class="wmtCollapseBoxWhite" style="padding: 3px; display: <?php echo $dt['tmp_well_disp_mode']; ?>">
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Vitals&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<?php
					if($vital_timestamp) {
						echo "<td class='wmtLabelRed' colspan='6'>Vitals Taken: ".$vital_timestamp."</td>\n";
					}
				?> 
			</tr>
			<tr>
				<td class="wmtBody">Height:&nbsp;&nbsp;<input name="db_height" id="db_height" class="wmtInput" style="width: 60px" type="text" value="<?php echo $dt{'db_height'}; ?>" onchange="UpdateBMI('db_height', 'db_weight', 'db_BMI', 'db_BMI_status')" /></td>
				<td class="wmtBody">Weight:&nbsp;&nbsp;<input name="db_weight" id="db_weight" class="wmtInput" style="width: 60px" type="text" value="<?php echo $dt{'db_weight'}; ?>" onchange="UpdateBMI('db_height', 'db_weight', 'db_BMI', 'db_BMI_status');" /></td>
				<td class="wmtBody">Blood Pressure:&nbsp;&nbsp;<input name="db_bps" id="db_bps" class="wmtInput" style="width: 30px" type="text" value="<?php echo $dt{'db_bps'}; ?>" />&nbsp;/&nbsp;<input name="db_bpd" id="db_bpd" class="wmtInput" style="width: 30px" type="text" value="<?php echo $dt{'db_bpd'}; ?>" /></td>
				<td class="wmtBody">BMI:&nbsp;&nbsp;<input name="db_BMI" id="db_BMI" class="wmtInput" style="width: 60px" type="text" value="<?php echo $dt{'db_BMI'}; ?>" onchange="OneDecimal('db_BMI');" /></td>
				<td><input name="db_BMI_status" id="db_BMI_status" class="wmtInput" type="text" value="<?php echo $dt{'db_BMI_status'}; ?>" /></td>
			</tr>
		</table>
		</fieldset>
		<?php if(checkSettingMode('wmt::db_wellness_hearing')) { ?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Hearing Screen&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<?php
					if($hearing_timestamp) {
						echo "<td class='wmtLabelRed' colspan='6'>Last Hearing Screen: ".$hearing_timestamp."</td>\n";
					}
				?> 
			</tr>
			<tr>
        <td class="wmtBody">Hearing Screen Performed:</td>
				<td><select name="db_hear_screen" id="db_hear_screen" class="wmtInput">
					<?php echo ListSel($dt['db_hear_screen'], 'yesno'); ?>
				</select></td>
        <td class="wmtBody">Left Ear:</td>
				<td><select name="db_hearl_result" id="db_hearl_result" class="wmtInput">
					<?php echo ListSel($dt['db_hearl_result'], 'PassFail'); ?>
				</select></td>
        <td class="wmtBody">Right Ear:</td>
				<td><select name="db_hearr_result" id="db_hearr_result" class="wmtInput">
					<?php echo ListSel($dt['db_hearr_result'], 'PassFail'); ?>
				</select></td>
			</tr>
		</table>
		</fieldset>
		<?php } ?>
		<!-- <div class="wmtDottedB"></div> -->
		<?php if(checkSettingMode('wmt::db_wellness_blood')) { ?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Blood&nbsp;&amp;&nbsp;Urine Tests&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtBody">Blood Type:</td>
				<td colspan="2"><select name="blood_type" id="blood_type" class="wmtInput">
					<?php echo ListSel($dt{'blood_type'},'Blood_Types'); ?></select>
					&nbsp;&nbsp;<select name="rh_factor" id="rh_factor" class="wmtInput">
					<?php echo ListSel($dt{'rh_factor'},'RH_Factor'); ?></select></td>
        <td class="wmtBody">Last Cholesterol Check:</td>
        <td class="wmtDateCell"><input name="db_last_chol" id="db_last_chol" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_chol'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_chol_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php if($client_id != 'cffm') { ?>
        <td class="wmtBody">Last Hepatitis C Test:</td>
        <td class="wmtDateCell"><input name="db_last_hepc" id="db_last_hepc" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_hepc'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_hepc_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php }  else { ?>
        <td class="wmtBody" style="width: 25%;">&nbsp;</td>
        <td class="wmtDateCell">&nbsp;</td>
        <td class="wmtCalendarCell">&nbsp;</td>
				<?php } ?>
			</tr>
			<?php if($client_id != 'cffm') { ?>
			<tr>
        <td class="wmtBody" style="width: 22%;">Last Lipid Panel:</td>
        <td class="wmtDateCell"><input name="db_last_lipid" id="db_last_lipid" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_lipid'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_lipid_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last Lipoprotein:</td>
        <td class="wmtDateCell"><input name="db_last_lipo" id="db_last_lipo" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_lipo'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_lipo_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last Triglycerides:</td>
        <td class="wmtDateCell"><input name="db_last_tri" id="db_last_tri" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_tri'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_tri_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			</tr>
			<?php } ?>
			<tr>
				<td class="wmtBody">Last Urine Micro Alb:</td>
        <td class="wmtDateCell"><input name="db_last_urine_alb" id="db_last_urine_alb" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_urine_alb'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_urine_alb_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td class="wmtBody">Last HgbA1c:</td>
        <td class="wmtDateCell"><input name="db_last_hgba1c" id="db_last_hgba1c" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_hgba1c'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_hgba1c_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			</tr>
		</table>
		</fieldset>
		<?php } ?>
		<?php if(checkSettingMode('wmt::db_wellness_cardio')) { ?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Cardio&nbsp;&amp;&nbsp;Pulmonary Tests&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
        <td class="wmtBody" style="width: 22%;">Last EKG:</td>
        <td class="wmtDateCell"><input name="db_last_ekg" id="db_last_ekg" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_ekg'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_ekg_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last PFT:</td>
        <td class="wmtDateCell"><input name="db_last_pft" id="db_last_pft" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_pft'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_pft_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td style="width: 22%;">&nbsp;</td>
				<td class="wmtDateCell">&nbsp;</td>
				<td class="wmtCalendarCell">&nbsp;</td>
			</tr>
		</table>
		</fieldset>
		<?php } ?>
		<?php if(checkSettingMode('wmt::db_wellness_colon')) { ?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Colon&nbsp;<?php echo (($pat_sex == 'f')?"":"&amp;&nbsp;Prostate&nbsp;"); ?></legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="wmtBody" style="width: 22%;">Last Colonoscopy:</td>
        <td class="wmtDateCell"><input name="db_last_colon" id="db_last_colon" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_colon'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_colon_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last Fecal Occult Blood Test:</td>
        <td class="wmtDateCell"><input name="db_last_fecal" id="db_last_fecal" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_fecal'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_fecal_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php if($client_id != 'cffm') { ?>
        <td class="wmtBody" style="width: 22%;">Last Barium Enema:</td>
        <td class="wmtDateCell"><input name="db_last_barium" id="db_last_barium" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_barium'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_barium_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php } else { ?>
				<td style="width: 16%;">&nbsp;</td>
				<td style="width: 16%;">&nbsp;</td>
				<?php } ?>
			</tr>
			<tr>
				<?php if($client_id != 'cffm') { ?>
        <td class="wmtBody">Last Flexible Sigmoidoscopy:</td>
        <td class="wmtDateCell"><input name="db_last_sigmoid" id="db_last_sigmoid" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_sigmoid'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_sigmoid_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php } ?>
				<?php if($pat_sex == 'f') { ?>
				<?php } else { ?>
					<td class='wmtBody'>Last PSA:</td>
        	<td class="wmtDateCell"><input name="db_last_psa" id="db_last_psa" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_psa'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        	<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_psa_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
					<td class="wmtBody">Last Rectal Exam:</td>
        	<td class="wmtDateCell"><input name="db_last_rectal" id="db_last_rectal" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_rectal'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        	<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_rectal_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<?php } ?>
      </tr>
		</table>
		</fieldset>
		<?php } ?>
		<?php if(checkSettingMode('wmt::db_wellness_diabetes')) { ?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Diabetes Related&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
				<td class="wmtBody" style="width: 22%;">Last Diabetes Screening:</td>
        	<td class="wmtDateCell"><input name="db_last_db_screen" id="db_last_db_screen" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_db_screen'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        	<td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_db_screen_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td class="wmtBody" style="width: 22%;">Last Diabetic Eye Exam:</td>
        <td class="wmtDateCell"><input name="db_last_db_eye" id="db_last_db_eye" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_db_eye'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_db_eye_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td class="wmtBody" style="width: 22%;">Last Diabetic Foot Exam:</td>
        <td class="wmtDateCell"><input name="db_last_db_foot" id="db_last_db_foot" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_db_foot'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_db_foot_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			</tr>
			<tr>
				<td class="wmtBody">Last Glaucoma Screening:</td>
        <td class="wmtDateCell"><input name="db_last_glaucoma" id="db_last_glaucoma" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_glaucoma'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_glaucoma_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td class="wmtBody">Last Self-Management Training:</td>
        <td class="wmtDateCell"><input name="db_last_db_dbsmt" id="db_last_db_dbsmt" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_db_dbsmt'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_db_dbsmt_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			</tr>
		</table>
		</fieldset>
		<?php
		}
		if($pat_sex == 'f') {
		?>
		<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Gynecological&nbsp;</legend>
		<table width="100%" border="0" cellspacing="0" cellpadding="0">
			<tr>
        <td class="wmtBody" style="width: 22%;">LMP:</td>
        <td class="wmtDateCell"><input name="db_last_mp" id="db_last_mp" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_mp'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_mp_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last Bone Density:</td>
        <td class="wmtDateCell"><input name="db_last_bone" id="db_last_bone" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_bone'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_bone_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
        <td class="wmtBody" style="width: 22%;">Last Mammogram:</td>
        <td class="wmtDateCell"><input name="db_last_mamm" id="db_last_mamm" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_mamm'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_mamm_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
      </tr>
			<tr>
        <td class="wmtBody">HPV Vaccinated:</td>
				<td colspan="2"><select name="db_hpv" id="db_hpv" class="wmtInput">
					<?php echo ListSel($dt['db_hpv'], 'YesNo'); ?>
				</select></td>
        <td class="wmtBody">Last HPV:</td>
        <td class="wmtDateCell"><input name="db_last_hpv" id="db_last_hpv" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_hpv'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_hpv_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
				<td class='wmtBody'>Last Pap Smear:</td>
        <td class="wmtDateCell"><input name="db_last_pap" id="db_last_pap" class="wmtDateInput" type="text" value="<?php echo $dt{'db_last_pap'}; ?>" onkeyup="datekeyup(this,mypcc)" onblur="dateblur(this,mypcc)" title="YYYY-MM-DD" /></td>
        <td class="wmtCalendarCell"><img src="<?php echo $GLOBALS['rootdir']; ?>/pic/show_calendar.gif" width="20" height="20" id="img_last_pap_dt" border="0" alt="[?]" style="vertical-align: bottom; cursor:pointer;" title="Click here to choose a date"></td>
			</tr>
			<tr>
        <td class="wmtBody">Last HCG Result:</td>
        <td colspan="2"><input name="db_hcg" id="db_hcg" class="wmtDateInput" type="text" value="<?php echo $dt{'db_hcg'}; ?>" /></td>
			</tr>
      <tr>
        <td class="wmtLabel">Periods:</td>
        <td class="Body" colspan="2">Age Menarche:</td>
        <td colspan="3"><input name="db_age_men" id="db_age_men" class="wmtFullInput" type="text" value="<?php echo $dt{'db_age_men'}; ?>" /></td>
			</tr>
			<tr>
				<td class="wmtBody">Flow:</td>
        <td class="wmtBody" colspan="3"><input name="db_pflow" id="db_pflow_heavy" type="radio" value="h" <?php echo (($dt{'db_pflow'} == 'h')?' checked ':''); ?> /><label for="db_pflow_heavy">Heavy&nbsp;</label>&nbsp;&nbsp;<input name="db_pflow" id="db_pflow_light" type="radio" value="l" <?php echo (($dt{'db_pflow'} == 'l')?' checked ':''); ?> /><label for="db_pflow_light">Light&nbsp;</label>&nbsp;&nbsp;<input name="db_pflow" id="db_pflow_normal" type="radio" value="n" <?php echo (($dt{'db_pflow'} == 'n')?' checked ':''); ?> /><label for="db_pflow_normal">Normal&nbsp;</label>&nbsp;&nbsp;<input name="db_pflow" id="db_pflow_meno" type="radio" value="m" <?php echo (($dt{'db_pflow'} == 'm')?' checked ':''); ?> /><label for="db_pflow_meno">Menopause&nbsp;</label>&nbsp;&nbsp;<input name="db_pflow" id="db_pflow" type="radio" value="x" <?php echo (($dt{'db_pflow'} == 'x')?' checked ':''); ?> /><label for="db_pflow">None</label></td>
        <td class="wmtBody wmtR" colspan="2">Frequency:</td>
        <td class="wmtBody" colspan="3"><input name="db_pfreq" id="db_pfreq_reg" type="radio" value="r" <?php echo (($dt{'db_pfreq'} == 'r')?' checked ':''); ?> /><label for="db_pfreq_reg">Regular&nbsp;</label>&nbsp;&nbsp;<input name="db_pfreq" id="db_pfreq_irr" type="radio" value="i" <?php echo (($dt{'db_pfreq'} == 'i')?' checked ':''); ?> /><label for="db_pfreq_irr">Irregular&nbsp;</label>&nbsp;&nbsp;<input name="db_pfreq" id="db_pfreq_none" type="radio" value="n" <?php echo (($dt{'db_pfreq'} == 'n')?' checked ':''); ?> /><label for="db_pfreq_none">None</label></td>
        <td></td>
      </tr>
			<tr>
        <td class="Body">Duration:</td>
        <td colspan="3"><input name="db_pflow_dur" id="db_pflow_dur" class="wmtInput" type="text" value="<?php echo $dt{'db_pflow_dur'}; ?>" />&nbsp;&nbsp;days</td>
        <td class="Body wmtR" colspan="2">Interval:</td>
        <td colspan="3"><input name="db_pfreq_days" id="db_pfreq_days" class="wmtInput" type="text" value="<?php echo $dt{'db_pfreq_days'}; ?>" />&nbsp;&nbsp;days</td>
			</tr>
		</table>
		</fieldset>
		<?php
		}
		?>
	</div>
</div><!-- End of the Wellness box -->
<?php } ?>
