<?php
$use_abnormal = false;
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	include_once($GLOBALS['srcdir'].'/patient.inc');
	$use_abnormal = true;
}
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal');
$hr_accent = $rsp_accent = $temp_accent = $bp_accent = $accent = '';
if($use_abnormal) {
	if(strpos($patient->age, ' ') !== false) {
		list($num, $frame) = split(' ', $patient->age);
	} else {
		$num = $patient->age;
		$frame = 'year';
	}
	$hr_accent = isAbnormalPulse($num, $frame, $vitals->pulse);
	$rsp_accent = isAbnormalRespiration($num, $frame, $vitals->respiration);
	$temp_accent = isAbnormalTemperature($num, $frame, $vitals->temperature);
	$bp_accent = isAbnormalBps($num, $frame, $vitals->bps);
	if(!$bp_accent) $bp_accent = isAbnormalBpd($num, $frame, $vitals->bpd);
	if($hr_accent || $rsp_accent || $temp_accent ||$bp_accent) 
		$accent = 'style="color: red; "';
}
?>
    <table width="100%" border="0" cellspacing="0" cellpadding="0">
      <tr>
        <td class="bkkLabel" colspan="2">Vitals Taken:</td> 
        <td colspan="3"><input name="vital_date" id="vital_date" type="text" class="bkkLabel <?php echo $accent ? 'bkkRed' : ''; ?>" style="border: none;" readonly="readonly" value="<?php echo htmlspecialchars($vitals->date, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td colspan="3"><a class="css_button" tabindex="-1" onClick="get_vitals();" href="javascript:;"><span>Find Other Vitals</span></a></td>
				<td colspan="5">&nbsp;</td>
      </tr>
			<tr>
				<td class="bkkBody">Height:</td>
				<td><input name="vital_height" id="vital_height" class="bkkInput" style="width: 60px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly ' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->height, ENT_QUOTES, '', FALSE); ?>" onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" /></td>
				<td class="bkkBody">Weight:</td>
				<td><input name="vital_weight" id="vital_weight" class="bkkInput" style="width: 60px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly ' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->weight, ENT_QUOTES, '', FALSE); ?>" onchange="UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); TimeStamp('vital_date');" /></td>
				<td class="bkkBody">BMI:</td>
				<td><input name="vital_BMI" id="vital_BMI" class="bkkInput" style="width: 60px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly ' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->BMI, ENT_QUOTES, '', FALSE); ?>" onchange="OneDecimal('bmi'); TimeStamp('vital_date');" /></td>
				<td colspan="2"><input name="vital_BMI_status" id="vital_BMI_status" class="bkkInput" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->BMI_status, ENT_QUOTES, '', FALSE); ?>" /></td>
				<td class="bkkBody">Pulse:</td>
				<td><input name="vital_pulse" id="vital_pulse" class="bkkInput <?php echo $hr_accent ? 'bkkRed' : ''; ?>" style="width: 50px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->pulse, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="bkkBody">Resp:</td>
				<td><input name="vital_respiration" id="vital_respiration" class="bkkInput <?php echo $rsp_accent ? 'bkkRed' : ''; ?>" style="width: 60px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly ' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->respiration, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php if($client_id != 'hcardio' && $client_id != 'ccc') { ?>
				<td class="bkkBody">Temp:</td>
				<?php } ?>
				<td><input name="vital_temperature" id="vital_temperature" class="bkkInput <?php echo $temp_accent ? 'bkkRed' : ''; ?>" style="width: 60px" type="<?php echo ($client_id == 'hcardio' || $client_id == 'ccc') ? 'hidden' : 'text'; ?>" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->temperature, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td style="width: 1%">&nbsp;</td>
			</tr>
		<!-- /table>
    <table width="100%" border="0" cellspacing="0" cellpadding="0" -->
			<tr>
				<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
				<td class="bkkBody">Supine BP:</td>
				<td><input name="vital_prone_bps" id="vital_prone_bps" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_prone_bpd" id="vital_prone_bpd" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php } else { ?>
				<td class="bkkBody">Seated BP:</td>
				<td><input name="vital_bps" id="vital_bps" class="bkkInput <?php echo $bp_accent ? 'bkkRed' : ''; ?>" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_bpd" id="vital_bpd" class="bkkInput <?php echo $bp_accent ? 'bkkRed' : ''; ?>" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php } ?>
				<?php if($client_id == 'cffm') { ?>
				<td class='bkkBody'>Seated 2 BP:</td>
				<td><input name="vital_prone_bps" id="vital_prone_bps" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_prone_bpd" id="vital_prone_bpd" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php } else if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
				<td class='bkkBody'>Seated BP:</td>
				<td><input name="vital_bps" id="vital_bps" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_bpd" id="vital_bpd" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php } else { ?>
				<td class='bkkBody'>Prone BP:</td>
				<td><input name="vital_prone_bps" id="vital_prone_bps" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_timestamp');" />/<input name="vital_prone_bpd" id="vital_prone_bpd" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php } ?>
				<td class="bkkBody">Standing BP:</td>
				<td><input name="vital_standing_bps" id="vital_standing_bps" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->standing_bps, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" />/<input name="vital_standing_bpd" id="vital_standing_bpd" class="bkkInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->standing_bpd, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<td class="bkkBody">Arm:</td>
				<td>
				<?php if($wrap_mode == 'never') { ?>
					<select name="vital_arm" id="vital_arm" class="bkkInput" style="width: 60px" onchange="TimeStamp('vital_date');" >
					<?php ListSel($vitals->arm,'Vital_Arm'); ?>
					</select></td>
				<?php } else { ?>
					<input name="vital_arm" id="vital_arm" class="bkkInput" style="width: 60px" readonly="readonly" value="<?php echo ListLook($vitals->arm,'Vital_Arm'); ?>" /></td>
				<?php } ?>
				<td class="bkkBody">O<sub>2</sub> Sat.:</td>
				<td><input name="vital_oxygen_saturation" id="vital_oxygen_saturation" class="bkkInput" style="width: 60px" type="text" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->oxygen_saturation, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php if($client_id != 'hcardio' && $client_id != 'ccc') { ?>
				<td class="bkkBody">Finger Stick:</td>
				<?php } ?>
				<td><input name="vital_diabetes_accucheck" id="vital_diabetes_accucheck" class="bkkFullInput" type="<?php echo ($client_id == 'hcardio' || $client_id == 'ccc') ? 'hidden' : 'text'; ?>" style="width: 60px" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				<?php if($client_id == 'uimda') { ?>
				<td class="bkkBody">HgbA1c:</td>
				<td><input name="vital_diabetes_accucheck" id="vital_diabetes_accucheck" class="bkkFullInput" type="<?php echo ($client_id == 'hcardio' || $client_id == 'ccc') ? 'hidden' : 'text'; ?>" style="width: 60px" <?php echo ($wrap_mode != 'new') ? 'readonly' : 'readonly'; ?> value="<?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES, '', FALSE); ?>" onchange="TimeStamp('vital_date');" /></td>
				</td>
				<?php } ?>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="bkkBody">Vitals Note:&nbsp;</td>
				<td colspan="11"><input name="vital_note" id="vital_note" class="bkkFullInput" type="text" readonly="readonly" value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES, '', FALSE); ?>" /></td>
			</tr>
    </table>
