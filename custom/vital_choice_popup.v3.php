<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtstandard.inc');
$use_abnormal_flag = false;
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	$use_abnormal_flag = true;
}
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal');
$use_metric = FALSE;
if($GLOBALS['units_of_measurement'] == 2 || 
						$GLOBALS['units_of_measurement'] == 3) $use_metric = TRUE;
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
?>

<html>
<head>
<title><?php xl('Vital Forms On File','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
a:hover { color: blue; font-weight: bold; }
</style>

<script type="text/javascript">

function chooseVitals(vdata) {
	var vitals = {};
	var pairs = vdata.split('^^');
	var l = pairs.length;
	for(i=0; i<l; ++i) {
		var data = pairs[i].split('~~');
		vitals[data[0]] = data[1];	
	}
  if(opener.closed || ! opener.setVitals) {
   alert('The destination form was closed; I cannot act on your selection.');
  } else {
   opener.setVitals(vitals);
	}
  window.close();
  return false;
}
</script>
</head>

<?php
$patient = sqlQuery("Select lname, fname, DOB FROM patient_data WHERE pid=?", array($pid));
$patient_age = getPatientAge($patient{'DOB'});
$age = explode(' ', $patient_age);
$num = $age[0];
$frame = 'year';
if(isset($age[1])) $frame = $age[1];
?>

<body class="body_top">
<form method='post' name='theform' action='vital_choice_popup.php'>
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr><td height="1"></td></tr>
 <tr>
    <td><b>Vitals Currently on File For:&nbsp;<?php echo $patient{'fname'},' ',$patient{'lname'},' (',$pid,')'; ?></b></td>
 </tr>
 <tr><td height="1"></td></tr>
</table>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr style="background-color: #ddddff">
  <td><b>Click to Select a Vitals Record </b></td>
 </tr>
 <tr><td height="1"></td></tr>
</table>

<table border='0' cellpadding='4' width='90%'>
  <tr>
    <td>Date/Time</td>
    <td>Taken By</td>
    <td>Weight</td>
    <td>Height</td>
    <td>BMI</td>
    <td>BP</td>
    <td>Pulse</td>
    <td>Temp</td>
  </tr>
<?php
	$query = "SELECT form_vitals.*, forms.encounter, forms.`user` FROM forms, ".
				"form_vitals WHERE deleted != 1 AND forms.pid = ? AND ".
				"forms.form_name = 'Vitals' AND forms.form_id = form_vitals.id ".
				"ORDER BY `date` DESC LIMIT 15";
  $forms = sqlStatement($query, array($pid));
  $row_cnt = 0;
  while($row = sqlFetchArray($forms)) {
    unset($getValues);
	  $getValues = array();
		foreach($row as $key => $val) {
			if($key == 'weight') $row['weight_metric'] = convLbToKg($val);
			if($key == 'height') $row['height_metric'] = convInToCm($val);
			if($key == 'temperature') $row['temperature_metric'] = convFrToCl($val);
			if($suppress_decimal) {
				if($key == 'height' || $key == 'weight' || $key == 'pulse' || 
						$key == 'oxygen_saturation' || $key == 'respiration') {
					$val = intval($val);	
					$row[$key] = $val;
				}
			}
			$getValues[] =  $key . '~~' . $val;
		}
		$bp_abn = $hr_abn = $rsp_abn = $temp_abn = '';
		if($use_abnormal_flag) {
			$bp_abn = isAbnormalBps($num, $frame, $row{'bps'});
			if(!$bp_abn) $bp_abn = isAbnormalBpd($num, $frame, $row{'bpd'});
			$hr_abn = isAbnormalPulse($num, $frame, $row{'pulse'});
			$rsp_abn = isAbnormalRespiration($num, $frame, $row{'respiration'});
			$temp_abn = isAbnormalTemperature($num, $frame, $row{'temperature'});
			$bmi_abn = isAbnormalBMI($num, $frame, $row{'BMI'});
		}
	
		$v = join('^^', $getValues);
    $anchor = "<a href='javascript:;' onclick=\"return chooseVitals('$v')\" />";
    $bmi_anchor = "<a href='javascript:;' $bmi_abn onclick=\"return chooseVitals('$v')\" />";
    $bp_anchor = "<a href='javascript:;' $bp_abn onclick=\"return chooseVitals('$v')\" />";
    $hr_anchor = "<a href='javascript:;' $hr_abn onclick=\"return chooseVitals('$v')\" />";
    $rsp_anchor = "<a href='javascript:;' $rsp_abn onclick=\"return chooseVitals('$v')\" />";
    $temp_anchor = "<a href='javascript:;' $temp_abn onclick=\"return chooseVitals('$v')\" />";
    echo " <tr>";
    echo "  <td>$anchor {$row['date']}</a></td>\n";
    echo "  <td>$anchor {$row['user']}</a></td>\n";
    echo "  <td>$anchor ";
		echo $use_metric ? $row['weight_metric'] : $row['weight'];
		echo "</a></td>\n";
    echo "  <td>$anchor ";
		echo $use_metric? $row['height_metric'] : $row['height'];
		echo "</a></td>\n";
    echo "  <td>$bmi_anchor {$row['BMI']} - {$row['BMI_status']}</a></td>\n";
    echo "  <td>$bp_anchor {$row['bps']}/{$row['bpd']}</a></td>\n";
    echo "  <td>$hr_anchor {$row['pulse']}</a></td>\n";
    echo "  <td>$temp_anchor ";
		echo $use_metric ? $row['temperature_metric'] : $row['temperature'];
		echo "</a></td>\n";
    echo " </tr>";
    $row_cnt++;
  }
  if(!$row_cnt) {
    echo "<tr><td colspan='5' style='text-align: center; color: #FF0000'><b>No Vitals Found For This Patient</b></td></tr>\n";
  }
?>

</table>
</center>
</form>
</body>
</html>
