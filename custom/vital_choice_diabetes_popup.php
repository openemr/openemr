<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once('../interface/globals.php');
require_once($GLOBALS['srcdir'].'/patient.inc');
require_once($GLOBALS['srcdir'].'/wmt-v2/wmtSettings.inc');
$use_abnormal_flag = false;
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	$use_abnormal_flag = true;
}
$suppress_decimal = checkSettingMode('wmt::suppress_vital_decimal');
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
?>

<html>
<head>
<title><?php xl('Vital Lookup','e'); ?></title>
<link rel="stylesheet" href='<?php echo $css_header ?>' type='text/css'>

<style>
td { font-size:10pt; }
a:hover { color: blue; font-weight: bold; }
</style>

<script type="text/javascript">

function setVitals() {
	var test = arguments.length;
	var vitals=[];
	for( var i = 0; i < arguments.length; ++i) {
		vitals.push(arguments[i]);	
	}
  if (opener.closed || ! opener.set_vitals)
   alert('The destination form was closed; I cannot act on your selection.');
  else
   opener.set_vitals(vitals);
  window.close();
  return false;
}
</script>
</head>

<?php
$patient = sqlQuery("Select lname, fname, ss, DOB FROM patient_data WHERE pid=?", array($pid));
$patient_age = getPatientAge($patient{'DOB'});
list($num, $frame) = explode(' ', $patient_age);
if(!$frame) $frame = 'year';
?>

<body class="body_top">
<form method='post' name='theform' action='vital_choice_diabetes_popup.php'>
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr><td height="1"> </td></tr>
 <tr>
    <td><b>Vitals Currently on File For:&nbsp;&nbsp;<?php echo $patient{'fname'},' ',$patient{'lname'},' (',$pid,')'; ?></b></td>
 </tr>
 <tr><td height="1"></td></tr>
</table>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr style="background-color: #ddddff">
  <td><b>Click to Select a Vitals Record </b></td>
 </tr>
 <tr><td height="1"></td></tr>
</table>

<table border='0' cellpadding='4'>
  <tr>
    <td>Date/Time</td>
    <td>Taken By</td>
    <td>BP</td>
    <td>Weight</td>
    <td>Height</td>
    <td>Pulse</td>
    <td>Resp</td>
    <td>Temp</td>
  </tr>
<?php
	$query = 'SELECT form_vitals.*, forms.encounter, forms.user FROM forms ' .
		'LEFT JOIN form_vitals ON forms.form_id = form_vitals.id ' .
		'WHERE deleted = 0 AND forms.pid = ? AND forms.formdir = ? ' .
		'ORDER BY date DESC';
  $forms = sqlStatement($query, array($pid, 'vitals'));
  $row_cnt = 0;
	$getValues = array();
  while($row = sqlFetchArray($forms)) {
		$cnt = 0;
		while($cnt < 40) {
			$getValues[$cnt] = '';
			$cnt++;
		}
    $getVuser = $row{'user'};
    $getValues[0] = $getVid = $row{'id'};
    $getValues[1] = $getVheight = $row{'height'};
    $getValues[2] = $getVweight = $row{'weight'};
		if($suppress_decimal) {
    	$getValues[1] = $getVheight = intval($row{'height'});
    	$getValues[2] = $getVweight = intval($row{'weight'});
		}
    $getValues[3] = $getVbps = $row{'bps'};
    $getValues[4] = $getVbpd = $row{'bpd'};
    $getValues[5] = $getVhr = $row{'pulse'};
		if($suppress_decimal) {
    	$getValues[5] = $getVhr = intval($row{'pulse'});
		}
    $getValues[6] = $getVbmi = $row{'BMI'};
    $getValues[7] = $getVstat = $row{'BMI_status'};
    $getValues[8] = $getVdate = $row{'date'};
		if(isset($row{'arm'})) $getValues[9]=$row{'arm'};
		if(isset($row{'prone_bps'})) $getValues[10] = $row{'prone_bps'};
		if(isset($row{'prone_bpd'})) $getValues[11] = $row{'prone_bpd'};
		if(isset($row{'standing_bps'})) $getValues[12] = $row{'standing_bps'};
		if(isset($row{'standing_bpd'})) $getValues[13] = $row{'standing_bpd'};
		if(isset($row{'diabetes_accucheck'})) $getValues[14] = $row{'diabetes_accucheck'};
		if(isset($row{'oxygen_saturation'})) { 
			$getValues[15] = $row{'oxygen_saturation'}; 
			if($suppress_decimal) $getValues[15] = intval($row{'oxygen_saturation'});
		}
		$getVrsp = '';
		if(isset($row{'respiration'})) { 
			$getValues[16] = $getVrsp = $row{'respiration'};
			if($suppress_decimal) $getValues[16] = $getVrsp = intval($row{'respiration'});
		}
		$getVtemp = '';
		if(isset($row{'temperature'})) $getValues[17] = $getVtemp = $row{'temperature'};
		if(isset($row{'peak_flow'})) $getValues[18] = $row{'peak_flow'};
		if(isset($row{'temp_method'})) $getValues[19] = $row{'temp_method'};
    $getValues[20] = $row{'note'};
		$bp_abn = $hr_abn = $rsp_abn = $temp_abn = '';
		if($use_abnormal_flag) {
			$bp_abn = isAbnormalBps($num, $frame, $getVbps);
			if(!$bp_abn) $bp_abn = isAbnormalBpd($num, $frame, $getVbpd);
			if($bp_abn) $getValues[24] = 1;

			$hr_abn = isAbnormalPulse($num, $frame, $row{'pulse'});
			if($hr_abn)  $getValues[21] = 1;
			$rsp_abn = isAbnormalRespiration($num, $frame, $row{'respiration'});
			if($rsp_abn) $getValues[22] = 1;
			$temp_abn = isAbnormalTemperature($num, $frame, $row{'temperature'});
			if($temp_abn) $getValues[23] = 1;
		}
		if(isset($row{'HgbA1c'})) $getValues[24] = $row{'HgbA1c'};
		if(isset($row{'TC'})) $getValues[25] = $row{'TC'};
		if(isset($row{'LDL'})) $getValues[26] = $row{'LDL'};
		if(isset($row{'HDL'})) $getValues[27] = $row{'HDL'};
		if(isset($row{'trig'})) $getValues[28] = $row{'trig'};
		if(isset($row{'microalbumin'})) $getValues[29] = $row{'microalbumin'};
		if(isset($row{'BUN'})) $getValues[30] = $row{'BUN'};
		if(isset($row{'cr'})) $getValues[31] = $row{'cr'};
    $anchor = "<a href='' onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    $bp_anchor = "<a href='javascript:;' $bp_abn onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    $hr_anchor = "<a href='javascript:;' $hr_abn onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    $rsp_anchor = "<a href='javascript:;' $rsp_abn onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    $temp_anchor = "<a href='javascript:;' $temp_abn onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    echo " <tr>";
    echo "  <td>$anchor $getVdate</a></td>\n";
    echo "  <td>$anchor $getVuser</a></td>\n";
    echo "  <td>$anchor $getVweight</a></td>\n";
    echo "  <td>$anchor $getVheight</a></td>\n";
    echo "  <td>$bp_anchor $getVbps/$getVbpd</a></td>\n";
    echo "  <td>$hr_anchor $getVhr</a></td>\n";
    echo "  <td>$rsp_anchor $getVrsp</a></td>\n";
    echo "  <td>$temp_anchor $getVtemp</a></td>\n";
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
