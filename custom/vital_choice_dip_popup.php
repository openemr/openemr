<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
include_once($GLOBALS['srcdir'].'/patient.inc');
include_once($GLOBALS['srcdir'].'/wmt-v2/wmtSettings.inc');
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
	var test=arguments.length;
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
$patient = sqlQuery("Select lname, fname, DOB FROM patient_data WHERE pid=?", array($pid));
$patient_age = getPatientAge($patient{'DOB'});
list($num, $frame) = explode(' ', $patient_age);
if(!$frame) $frame = 'year';
?>

<body class="body_top">
<form method='post' name='theform' action='vital_choice_dip_popup.php'>
<center>

<table border='0' cellpadding='5' cellspacing='0'>
 <tr><td height="1"></td></tr>
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
    <td>Weight</td>
    <td>Height</td>
    <td>BP</td>
    <td>Pulse</td>
    <td>Resp</td>
    <td>Temp</td>
  </tr>
<?php
	$query = "SELECT form_vitals.*, forms.encounter, forms.user FROM forms, ".
		"form_vitals WHERE deleted = 0 AND forms.pid=? AND ".
		"forms.form_name='Vitals' ".  "AND forms.form_id=form_vitals.id ".
		"ORDER BY date DESC";
  $forms = sqlStatement($query, array($pid));
  $row_cnt = 0;
	$getValues = array();
  while($row = sqlFetchArray($forms)) {
		$cnt = 0;
		while($cnt < 30) {
			$getValues[$cnt]='';
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
		$getVrsp = '';
		if(isset($row{'respiration'})) { 
			$getValues[16] = $getVrsp = $row{'respiration'};
			if($suppress_decimal) $getValues[16] = $getVrsp = intval($row{'respiration'});
		}
		$getVtemp = '';
		if(isset($row{'temperature'})) $getValues[17] = $getVtemp = $row{'temperature'};
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
		$getValues[9] = $row{'specific_gravity'};
		$getValues[10] = $row{'ph'};
		$getValues[11] = $row{'leukocytes'};
		$getValues[12] = $row{'nitrite'};
		$getValues[13] = $row{'protein'};
		$getValues[14] = $row{'glucose'};
		$getValues[15] = $row{'ketones'};
		$getValues[16] = $row{'urobilinogen'};
		$getValues[17] = $row{'bilirubin'};
		$getValues[18] = $row{'blood'};
		$getValues[19] = $row{'hemoglobin'};
		$getValues[20] = $row{'HCG'};
    $getValues[24] = $row{'note'};



    $anchor = "<a href='javascript:;' onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    $bp_anchor = "<a href='javascript:;' $abn onclick=\"return setVitals('".join("','", $getValues)."')\" />";
    echo " <tr>";
    echo "  <td>$anchor $getVdate</a></td>\n";
    echo "  <td>$anchor $getVuser</a></td>\n";
    echo "  <td>$anchor $getVweight</a></td>\n";
    echo "  <td>$anchor $getVheight</a></td>\n";
    echo "  <td>$bp_anchor $getVbps/$getVbpd</a></td>\n";
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
