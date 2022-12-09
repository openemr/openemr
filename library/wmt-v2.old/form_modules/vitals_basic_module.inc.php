<?php
// TODO - INCORPORATE $GLOBALS['units_of_measurement'] TO HIDE/SHOW METRIC
// 1 = SHOW BOTH, MAIN USIT IS US
// 2 = SHOW BOTH, MAIN UNIT IS METRIC
// 3 = SHOW US ONLY
// 4 - SHOW METRIC ONLY
// TODO - BUILD JAVASCRIPT CHECKING OF ABNORMAL VITALS REAL TIME
$use_abnormal = FALSE;
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal','',$frmdir);
$allow_vital_edit = checkSettingMode('wmt::allow_vital_edit','',$frmdir);
$use_weight_counseling = checkSettingMode('wmt::weight_counsel','',$frmdir);
if(!isset($draw_display)) $draw_display = TRUE;
if(!isset($close_v_table)) $close_v_table= TRUE;
if(!isset($form_event_logging)) 
	$form_event_logging = checkSettingMode('wmt::form_logging','',$frmdir);
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	include_once($GLOBALS['srcdir'].'/patient.inc');
	$use_abnormal = TRUE;
	$log = "INSERT FORM $frmdir MODE [$form_mode] ($pid) '$encounter:$id' " .
		"SET VITALS TO USE ABNORMAL LIBRARY";
	if($form_event_logging && $first_pass) auditSQLEvent($log, TRUE);
} else {
	$log = "INSERT FORM $frmdir MODE [$form_mode] ($pid) '$encounter:$id' " .
		"SET VITALS NOT USING ABNORMAL LIBRARY";
	if($form_event_logging && $first_pass) auditSQLEvent($log, TRUE);
}

if(!isset($dt{'vid'})) $dt{'vid'} = '';
if($form_mode == 'new' && $first_pass) {
	$vitals = wmtVitals::getVitalsByEncounter($encounter, $pid, $suppress_decimal);
	if(!$vitals->vital_id) 
		$vitals->timestamp = 'No Vitals Recorded for this Encounter';
	$log = "INSERT FORM NEW $frmdir MODE [$form_mode] ($pid) '$encounter:$id' " .
		"FOUND ENCOUNTER VITALS FORM ID ($vitals->vid)";
	if($form_event_logging) auditSQLEvent($log, TRUE);
} else {
	$vitals = new wmtVitals($dt['vid'], $suppress_decimal);
	$log = "INSERT FORM UPDATE $frmdir MODE [$form_mode] ($pid) " .
		"$encounter:$id' LOADED LINKED VITALS FORM ID ($vitals->vid)";
	if($form_event_logging) auditSQLEvent($log, TRUE);
}

if(checkSettingMode('wmt::supress_abnormal_vitals','',$frmdir)) 
		$use_abnormal = FALSE;
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal');
$hr_accent = $rsp_accent = $temp_accent = $bp_accent = '';
$bmi_accent = $accent = '';
$age = explode(' ', $patient->age);
$num = $age[0];
$frame = 'year';
if(isset($age[1])) $frame = $age[1];
if($use_abnormal) {
	$hr_accent = isAbnormalPulse($num, $frame, $vitals->pulse);
	$rsp_accent = isAbnormalRespiration($num, $frame, $vitals->respiration);
	$temp_accent = isAbnormalTemperature($num, $frame, $vitals->temperature);
	$bp_accent = isAbnormalBps($num, $frame, $vitals->bps);
	if(!$bp_accent) $bp_accent = isAbnormalBpd($num, $frame, $vitals->bpd);
	$bmi_accent = isAbnormalBMI($num, $frame, $vitals->BMI);
	if($hr_accent || $rsp_accent || $temp_accent || $bp_accent || $bmi_accent) 
		$accent = 'style="color: red; "';
}
if(!isset($GLOBALS['wmt::adult_weight_counsel_item'])) $GLOBALS['wmt::adult_weight_counsel_item'] = 'act_wt';
$weight_counsel_item = $GLOBALS['wmt::adult_weight_counsel_item'];

$use_metric = FALSE;
$show_metric = FALSE;
if($GLOBALS['units_of_measurement'] == 2 || 
						$GLOBALS['units_of_measurement'] == 4) $use_metric = TRUE;
if($GLOBALS['units_of_measurement'] == 1) $show_metric = TRUE;

if($draw_display) {
?>
<script type="text/javascript">

function setVitals(Vals)
{
  var f = document.forms[0];
  var num = <?php echo $num; ?>;
	var frame = '<?php echo $frame; ?>';
  var vid = Vals['id'];

  if (vid) {
    var l = f.elements.length;
    for(i=0; i<l; i++) {
      if(f.elements[i].name.indexOf('vital_') == 0) {
        f.elements[i].style.color = 'black';
      }
    }
    document.getElementById('vid').value = vid;
  	for(var key in Vals) {
			var fld = document.getElementById('vital_'+key);
			if(fld != null) {
				fld.value = Vals[key];
				<?php if($use_abnormal) { ?>
				var abnFunc = key.charAt(0).toUpperCase() + key.slice(1);
				abnFunc = 'isAbnormal' + abnFunc;
				if(typeof window[abnFunc] === "function") {
					var tst = window[abnFunc](num, frame, 'vital_'+key, 'vital_date');
				}
				<?php } ?>
			}
  	}
  }
}

</script>
<script type="text/javascript" src="<?php echo FORM_JS_DIR; ?>vitals.js"></script>

<?php 
if($use_abnormal) {
	echo '<script type="text/javascript">';
	include($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.js');
	echo '</script>';
}
if($use_weight_counseling) {
?>
<script type="text/javascript">
	// THE FUNCTION TO CALL THE AJAX GOES HERE
	function setWeightCounseling(chk) {
		var webroot = '<?php echo $GLOBALS['webroot']; ?>';
		var item = '<?php echo $weight_counsel_item; ?>';
		var mode = 'remove'
		if(chk.checked) mode = 'add';
		if(mode == 'remove') return false;
	
		var output = 'error';
		$.ajax({
			type: "POST",
			url: webroot + "/library/wmt-v2/ajax/rule_patient_data.ajax.php",
			datatype: "html",
			data: {
				mode: mode,
				pid: <?php echo $pid; ?>,
				item: item
			},
			success: function(result) {
				if(result['error'] || result == '') {
					output = '';
					alert('Could NOT Meet Clinical Target\n'+result['error']);
				} else {
					output = result;
				}
			},
			async: true
		});
		return output;
	}
</script>
<?php } ?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Vitals&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td colspan="2">Vitals Taken:</td> 
			<td colspan="3"><input name="vital_date" id="vital_date" type="text" style="border: none; background-color: white;<?php echo $accent ? ' color: red;' : ''; ?>" readonly disabled value="<?php echo htmlspecialchars($vitals->date, ENT_QUOTES); ?>" /></td>
			<td colspan="3">
			<?php if($frmdir != 'dashboard') { ?>
			<a class="css_button" tabindex="-1" onClick="getVitals('<?php echo $GLOBALS['webroot']; ?>','<?php echo $pid; ?>');" href="javascript:;"><span>Choose Vitals</span></a>
			<?php } ?>
			&nbsp;</td>
			<td colspan="5"><input name="vid" id="vid" type="hidden" value="<?php echo $vitals->vid; ?>" />&nbsp;</td>
		</tr>
		<tr>
			<td>Height (<?php echo $use_metric ? 'cm' : 'in'; ?>):</td>
			<td><input name="vital_height" id="vital_height" class="wmtInput" style="width: 50px" type="<?php echo $vitals->use_metric ? 'hidden' : 'text'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->height, ENT_QUOTES); ?>" onchange="convIntoCm('vital_height'); UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); <?php echo $use_abnormal ? "isAbnormalBMI('$num', '$frame', 'vital_BMI', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" />
			<input name="vital_height_metric" id="vital_height_metric" class="wmtInput" style="width: 50px" type="<?php echo $vitals->use_metric ? 'text' : 'hidden'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->height_metric, ENT_QUOTES); ?>" onchange="convCmtoIn('vital_height'); UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); <?php echo $use_abnormal ? "isAbnormalBMI('$num', '$frame', 'vital_BMI', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" />
			<?php echo $vitals->statage ?	'&nbsp;(' . $vitals->statage . 'th percentile)' : ''; ?>
			</td>
			<td>Weight (<?php echo $use_metric ? 'kg' : 'lb'; ?>):</td>
			<td><input name="vital_weight" id="vital_weight" class="wmtInput" style="width: 50px" type="<?php echo $vitals->use_metric ? 'hidden' : 'text'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->weight, ENT_QUOTES); ?>" onchange="convLbtoKg('vital_weight'); UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); <?php echo $use_abnormal ? "isAbnormalBMI('$num', '$frame', 'vital_BMI', 'vital_date');" : ''; ?>TimeStamp('vital_date');" />
			<input name="vital_weight_metric" id="vital_weight_metric" class="wmtInput" style="width: 50px" type="<?php echo $vitals->use_metric ? 'text' : 'hidden'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->weight_metric, ENT_QUOTES); ?>" onchange="convKgtoLb('vital_weight'); UpdateBMI('vital_height', 'vital_weight', 'vital_BMI', 'vital_BMI_status'); <?php echo $use_abnormal ? "isAbnormalBMI('$num', '$frame', 'vital_BMI', 'vital_date');" : ''; ?>TimeStamp('vital_date');" />
			<?php echo $vitals->wtage ?	'&nbsp;(' . $vitals->wtage . 'th percentile)' : ''; ?>
			</td>
			<td>BMI:</td>
			<td><input name="vital_BMI" id="vital_BMI" class="wmtInput <?php echo $bmi_accent ? 'wmtRed' : ''; ?>" style="width: 50px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->BMI, ENT_QUOTES); ?>" onchange="OneDecimal('bmi'); <?php echo $use_abnormal ? "isAbnormalBMI('$num', '$frame', 'vital_BMI', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" />
			<?php echo $vitals->bmiage ? '&nbsp;(' . $vitals->bmiage . 'th percentile)' : ''; ?>
			</td>
			<td colspan="2"><input name="vital_BMI_status" id="vital_BMI_status" class="wmtInput <?php echo $bmi_accent ? 'wmtRed' : ''; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> type="<?php echo ($num > 17 && $frame == 'year') ? 'text' : 'hidden'; ?>" value="<?php echo htmlspecialchars($vitals->BMI_status, ENT_QUOTES); ?>" />&nbsp;</td>
			<td>Pulse:</td>
			<td><input name="vital_pulse" id="vital_pulse" class="wmtInput <?php echo $hr_accent ? 'wmtRed' : ''; ?>" style="width: 50px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->pulse, ENT_QUOTES); ?>" onchange="<?php echo $use_abnormal ? "isAbnormalPulse('$num', '$frame', 'vital_pulse', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" /></td>
			<td>Resp:</td>
			<td><input name="vital_respiration" id="vital_respiration" class="wmtInput <?php echo $rsp_accent ? 'wmtRed' : ''; ?>" style="width: 50px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly ' : ''; ?> value="<?php echo htmlspecialchars($vitals->respiration, ENT_QUOTES); ?>" onchange="<?php echo $use_abnormal ? "isAbnormalRespiration('$num', '$frame', 'vital_respiration', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" /></td>
<?php if($client_id != 'hcardio' && $client_id != 'ccc') { ?>
			<td>Temp: (<?php echo $use_metric ? 'C' : 'F'; ?>)</td>
<?php } ?>
			<td><input name="vital_temperature" id="vital_temperature" class="wmtInput <?php echo $temp_accent ? 'wmtRed' : ''; ?>" style="width: 50px" type="<?php echo ($client_id == 'hcardio' || $client_id == 'ccc' || $use_metric) ? 'hidden' : 'text'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->temperature, ENT_QUOTES); ?>" onchange="convFtoC('vital_temperature'); <?php echo $use_abnormal ? "isAbnormalTemperature('$num', '$frame', 'vital_temperature', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" />
			<input name="vital_temperature_metric" id="vital_temperature_metric" class="wmtInput <?php echo $temp_accent ? 'wmtRed' : ''; ?>" style="width: 50px" type="<?php echo ($client_id == 'hcardio' || $client_id == 'ccc' || !$use_metric) ? 'hidden' : 'text'; ?>" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->temperature_metric, ENT_QUOTES); ?>" onchange="convCtoF('vital_temperature'); <?php echo $use_abnormal ? "isAbnormalTemperature('$num', '$frame', 'vital_temperature', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" /></td>
			<td style="width: 1%">&nbsp;</td>
		</tr>
		<tr>
<?php if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
			<td>Supine BP:</td>
			<td><input name="vital_prone_bps" id="vital_prone_bps" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /> /
			<input name="vital_prone_bpd" id="vital_prone_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
<?php } else { ?>
			<td>Seated BP:</td>
			<td><input name="vital_bps" id="vital_bps" class="wmtInput <?php echo $bp_accent ? 'wmtRed' : ''; ?>" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->bps, ENT_QUOTES); ?>" onchange="<?php echo $use_abnormal ? "isAbnormalBps('$num', '$frame', 'vital_bps', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" /> /
			&nbsp;<input name="vital_bpd" id="vital_bpd" class="wmtInput <?php echo $bp_accent ? 'wmtRed' : ''; ?>" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES); ?>" onchange="<?php echo $use_abnormal ? "isAbnormalBpd('$num', '$frame', 'vital_bpd', 'vital_date'); " : ''; ?>TimeStamp('vital_date');" /></td>
<?php } ?>
<?php if($client_id == 'cffm') { ?>
			<td>Seated 2 BP:</td>
			<td><input name="vital_prone_bps" id="vital_prone_bps" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /> /&nbsp;<input name="vital_prone_bpd" id="vital_prone_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
<?php } else if($client_id == 'hcardio' || $client_id == 'ccc') { ?>
			<td>Seated BP:</td>
			<td><input name="vital_bps" id="vital_bps" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->bps, ENT_QUOTES); ?>" onchange="<?php $use_abnormal ? "" : ''; ?>TimeStamp('vital_date');" /> /
			&nbsp;<input name="vital_bpd" id="vital_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->bpd, ENT_QUOTES); ?>" onchange="<?php $use_abnormal ? "" : ''; ?>TimeStamp('vital_date');" /></td>
<?php } else { ?>
			<td>Prone BP:</td>
			<td><input name="vital_prone_bps" id="vital_prone_bps" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bps, ENT_QUOTES); ?>" onchange="TimeStamp('vital_timestamp');" /> /
			&nbsp;<input name="vital_prone_bpd" id="vital_prone_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->prone_bpd, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
<?php } ?>
			<td>Standing BP:</td>
			<td><input name="vital_standing_bps" id="vital_standing_bps" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->standing_bps, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /> /
			&nbsp;<input name="vital_standing_bpd" id="vital_standing_bpd" class="wmtInput" style="width: 30px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->standing_bpd, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
			<td>Arm:</td>
			<td>
<?php if($wrap_mode != 'new' && !$allow_vital_edit) { ?>
			<input name="vital_arm" id="vital_arm" class="wmtInput" style="width: 70px" readonly="readonly" value="<?php echo ListLook($vitals->arm,'Vital_Arm'); ?>" /></td>
<?php } else { ?>
			<select name="vital_arm" id="vital_arm" class="wmtInput" style="width: 70px" onchange="TimeStamp('vital_date');" >
			<?php ListSel($vitals->arm,'Vital_Arm'); ?>
			</select></td>
<?php } ?>
			<td>O<sub>2</sub> Sat.:</td>
			<td><input name="vital_oxygen_saturation" id="vital_oxygen_saturation" class="wmtInput" style="width: 50px" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->oxygen_saturation, ENT_QUOTES); ?>" onchange="<?php $use_abnormal ? "" : ''; ?>TimeStamp('vital_date');" /></td>
			<td>Finger Stick:</td>
			<td><input name="vital_diabetes_accucheck" id="vital_diabetes_accucheck" class="wmtFullInput" type="text" style="width: 50px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->diabetes_accucheck, ENT_QUOTES); ?>" onchange="TimeStamp('vital_date');" /></td>
			<td>HgbA1c:</td>
			<td><input name="vital_HgbA1c" id="vital_HgbA1c" class="wmtFullInput" type="text" style="width: 50px" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->HgbA1c, ENT_QUOTES); ?>" onchange="<?php $use_abnormal ? "" : ''; ?>TimeStamp('vital_date');" /></td>
<?php // } ?>
			<td>&nbsp;</td>
		</tr>
<?php if($use_weight_counseling) { ?>
		<tr>
			<td colspan="8"><input name="vital_weight_counseling" id="vital_weight_counseling" type="checkbox" value="1" <?php echo $vitals->weight_counseling ? 'checked="checked"' : ''; ?> onchange="setWeightCounseling(this);" />&nbsp;<label for="vital_weight_counseling">Diet / Exercise / Weight Management Plan In Place</label></td>
		</tr>
<?php } ?>
<?php if($close_v_table) { ?>
		<tr>
			<td>Vitals Note:&nbsp;</td>
			<td colspan="13"><input name="vital_note" id="vital_note" class="wmtFullInput" type="text" <?php echo ($wrap_mode != 'new' && !$allow_vital_edit) ? 'readonly' : ''; ?> value="<?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?>" /></td>
		</tr>
	</table>
</fieldset>
<?php } // END OF CLOSE TABLE AND FIELDSET ?>

<?php } // END OF DRAW DISPLAY ?>
