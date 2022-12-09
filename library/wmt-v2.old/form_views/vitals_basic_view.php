<?php
$use_abnormal = FALSE;
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	include_once($GLOBALS['srcdir'].'/patient.inc');
	$use_abnormal = true;
}
if(!isset($close_v_table)) $close_v_table = TRUE;
if(checkSettingMode('wmt::supress_abnormal_vitals','',$frmdir)) 
		$use_abnormal = false;
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal','',$frmdir);
$vitals = new wmtVitals($dt{'vid'}, $suppress_decimal);
if($vitals->BMI == '0.0') $vitals->BMI = '';
$hr_accent = $rsp_accent = $temp_accent = $bp_accent = '';
$bmi_accent = $accent = '';
if($use_abnormal) {
	$age = explode(' ', $patient->age);
	$num = $age[0];
	$frame = 'year';
	if(isset($age[1])) $frame = $age[1];
	$hr_accent = isAbnormalPulse($num, $frame, $vitals->pulse);
	$rsp_accent = isAbnormalRespiration($num, $frame, $vitals->respiration);
	$temp_accent = isAbnormalTemperature($num, $frame, $vitals->temperature);
	$bp_accent = isAbnormalBps($num, $frame, $vitals->bps);
	if(!$bp_accent) $bp_accent = isAbnormalBpd($num, $frame, $vitals->bpd);
	$bmi_accent = isAbnormalBMI($num, $frame, $vitals->BMI);
	if($hr_accent || $rsp_accent || $temp_accent ||$bp_accent || $bmi_accent) 
		$accent = 'style="color: red; "';
}

if(!isset($module['option_id'])) $module['option_id'] = '';
if(!isset($module['title'])) $module['titles'] = '';
$use_metric = FALSE;
if($GLOBALS['units_of_measurement'] == 2 || 
						$GLOBALS['units_of_measurement'] == 3) $use_metric = TRUE;
if(!$chp_printed)  $chp_printed = PrintChapter($chp_title, $chp_printed);
echo '</table>';
?>

<style type="text/css">
@media screen {
	#vital_report_table_not {
		font-size: 0.7em;
	}
}

@media print {
	#vital_report_table_not {
		font-size: 8pt;
	}
}
</style>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend>&nbsp;Vitals&nbsp;</legend>
<table width="100%" cellpadding="0" cellspacing="0" border="0" id="vital_report_table">
  <tr>
    <td class="wmtPrnLabel" colspan="2">Vitals Taken:</td> 
    <td colspan="3" class="wmtPrnLabel <?php echo $accent ? 'wmtPrnRed' : ''; ?>"><?php echo htmlspecialchars($vitals->date, ENT_QUOTES); ?></td>
		<td colspan="8">&nbsp;</td>
  </tr>
	<tr>
		<td>Height:</td>
		<td><span style="width: 60px"><?php echo $use_metric ? htmlspecialchars($vitals->height_metric, ENT_QUOTES) : htmlspecialchars($vitals->height, ENT_QUOTES); ?></span>
		<?php echo intval($vitals->statage) ?	'&nbsp;(' . intval($vitals->statage) . 'th %)' : ''; ?>
		</td>
		<td>Weight:</td>
		<td><span style="width: 60px"><?php echo $use_metric ? htmlspecialchars($vitals->weight_metric, ENT_QUOTES) : htmlspecialchars($vitals->weight, ENT_QUOTES); ?></span>
		<?php echo intval($vitals->wtage) ?	'&nbsp;(' . intval($vitals->wtage) . 'th %)' : ''; ?>
		</td>
		<td>BMI:</td>
		<td <?php echo $bmi_accent ? 'class="wmtPrnRed"' : ''; ?>><span style="width: 60px"><?php echo htmlspecialchars($vitals->BMI, ENT_QUOTES); ?></span>
		<?php echo intval($vitals->bmiage) ? '&nbsp;(' . intval($vitals->bmiage) . 'th %)' : ''; ?>
		</td>
		<td <?php echo $bmi_accent ? 'class="wmtPrnRed"' : ''; ?> colspan="2"><?php echo ($num > 17 && $frame == 'year') ? htmlspecialchars($vitals->BMI_status, ENT_QUOTES) : '&nbsp;'; ?></td>
		<td>Pulse:</td>
		<td <?php echo $hr_accent ? 'class="wmtPrnRed"' : ''; ?> ><?php echo htmlspecialchars($vitals->pulse, ENT_QUOTES); ?></td>
		<td>Resp:</td>
		<td <?php echo $rsp_accent ? 'class="wmtPrnRed"' : ''; ?> ><?php echo htmlspecialchars($vitals->respiration, ENT_QUOTES); ?></td>
		<?php if($client_id != 'hcardio' && $client_id != 'ccc') { ?>
		<td>Temp:</td>
		<td <?php echo $temp_accent ? 'class="wmtPrnRed"' : ''; ?> ><?php echo $use_metric ? htmlspecialchars($vitals->temperature_metric, ENT_QUOTES) : htmlspecialchars($vitals->temperature, ENT_QUOTES); ?></td>
		<?php } else { ?>
		<td colspan="2">&nbsp;</td>
		<?php } ?>
	</tr>
	<tr>
		<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
		<td>Supine BP:</td>
		<td <?php echo $bp_accent ? 'class="wmtRed"' : ''; ?> ><?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?></td>
		<?php } else { ?>
		<td>Seated BP:</td>
		<td <?php echo $bp_accent ? 'class="wmtRed"' : ''; ?> ><?php echo htmlspecialchars($vitals->bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES); ?></td>
		<?php } ?>

		<?php if($client_id == 'cffm') { ?>
		<td>Seated 2 BP:</td>
		<td><?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?></td>
		<?php } else if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
		<td>Seated BP:</td>
		<td <?php echo $bp_accent ? 'class="wmtRed"' : ''; ?> style="width: 60px"><?php echo htmlspecialchars($vitals->bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES); ?></td>
		<?php } else { ?>
		<td>Prone BP:</td>
		<td><?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?></td>
		<?php } ?>
		<td>Standing BP:</td>
		<td><?php echo htmlspecialchars($vitals->standing_bps, ENT_QUOTES); ?> / <?php echo htmlspecialchars($vitals->standing_bpd, ENT_QUOTES); ?></td>
		<td>Arm:</td>
		<td><?php echo ListLook($vitals->arm,'Vital_Arm'); ?></td>
		<td>O<sub>2</sub> Sat.:</td>
		<td><?php echo htmlspecialchars($vitals->oxygen_saturation, ENT_QUOTES); ?></td>
		<?php if($client_id == 'none') { ?>
		<td>Finger Stick:</td>
		<td><?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES); ?></td>
		<?php } ?>
		<td>HgbA1c:</td>
		<td><?php echo htmlspecialchars($vitals->HgbA1c, ENT_QUOTES); ?></td>
		</td>
	</tr>
<?php if($vitals->weight_counseling) { ?>
	<tr>
		<td colspan="8">Diet / Exercise / Weight Management Plan In Place</td>
	</tr>
<?php } ?>
<?php if($close_v_table) { ?>
<?php if($vitals->note) { ?>
	<tr>
		<td>Vitals Note:&nbsp;</td>
		<td colspan="13"><?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?></td>
	</tr>
<?php } ?>
</table>
</fieldset>
<?php
	// if($chp_printed) echo "<table>\n";
}
?>
