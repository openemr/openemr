<?php
// Copyright (C) 2008 Rod Roark <rod@sunsetsystems.com>
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

require_once("../interface/globals.php");
require_once("../library/wmt-v2/pap_track.inc");
require_once("../library/wmt-v2/us_track.inc");
require_once("../library/wmt-v2/bd_track.inc");
include_once("../library/wmt-v2/wmtstandard.inc");
if(isset($_SESSION['pid'])) $pid = $_SESSION['pid'];
if(isset($_SESSION['encounter'])) $encounter = $_SESSION['encounter'];
$_txt = date('Y-m-d H:i:s');
$_txt .= "  -> Link SESSION Pid ($pid) Enc [$encounter]\n";
// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
// echo "After Session Set: $pid - $encounter<br/>\n";
if(isset($_GET['pid'])) $pid = strip_tags($_GET['pid']);
if(isset($_GET['encounter'])) $encounter = strip_tags($_GET['encounter']);
$use_meds_not_rx = checkSettingMode('wmt::use_meds_not_rx');
$_txt = date('Y-m-d H:i:s');
$_txt .= "  -> Link GET Pid ($pid) Enc [$encounter]\n";
// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
// echo "After GET Set: $pid - $encounter<br/>\n";
$success = true;
$err = '';
$do_img = isset($_GET['do_img']) ? true : false;
$do_all = isset($_GET['do_all']) ? true : false;
$do_surg = isset($_GET['do_surg']) ? true : false;
$do_meds = isset($_GET['do_meds']) ? true : false;
$do_med_hist = isset($_GET['do_med_hist']) ? true : false;
$do_imm = isset($_GET['do_imm']) ? true : false;
$do_hosp = isset($_GET['do_hosp']) ? true : false;
$do_fh = isset($_GET['do_fh']) ? true : false;
$do_pmh = isset($_GET['do_pmh']) ? true : false;
$do_pp = isset($_GET['do_pp']) ? true : false;
$do_pap = isset($_GET['do_pap']) ? true : false;
$do_bd = isset($_GET['do_bd']) ? true : false;
$du_us = isset($_GET['do_us']) ? true : false;
$form_action="link_refresh_popup.php?&success=$success";

if($do_img) {
	$img=GetImageHistory($pid);
	foreach($img as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_img_history');
	}
}
if($do_all) {
	UnlinkTypeFromEncounter($pid, $encounter, 'allergy');
	$allergies=GetList($pid, 'allergy');
	$_txt = date('Y-m-d H:i:s');
	$_txt .= "   ->  Allergies Loaded During Relink ($pid) [$encounter]\n";
	// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
	foreach($allergies as $prev) { 
		$_txt = '** ALLERGY LOAD - ';
		$_txt .= 'ID: '.$prev['id'].'    PID: '.$prev['pid'];  
		$_txt .= '    Dated:  '.$prev['date']."\n";
		// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
		LinkListEntry($pid, $prev['id'], $encounter, 'allergy');
	}
}
if($do_surg) {
	$surg=GetList($pid, 'surgery');
	foreach($surg as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'surgery');
	}
}
if($do_meds) {
	$_txt = date('Y-m-d H:i:s');
	$_txt .= "   ->  Meds Loaded During Relink ($pid) [$encounter]\n";
	// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
	if($use_meds_not_rx) {
		UnlinkTypeFromEncounter($pid, $encounter, 'medication');
		$meds = GetList($pid, 'medication');
		$type = 'medication';
	} else {
		UnlinkTypeFromEncounter($pid, $encounter, 'prescriptions');
  	$meds=getActivePrescriptionsbyPatient($pid);
		$type = 'prescriptions';
	}
	foreach($meds as $prev) { 
		$_txt = '** MED LOAD - ';
		$_txt .= 'ID: '.$prev['id'].'    PID: '.$prev['patient_id'];  
		$_txt .= '    Dated:  '.$prev['datetime']."\n";
		// file_put_contents('../interface/forms/whc_comp/link_load.log', $_txt, FILE_APPEND);
		LinkListEntry($pid, $prev['id'], $encounter, $type);
	}
}

if($do_med_hist) {
	if($use_meds_not_rx) {
		$med_hist = GetList($pid, 'med_history');
		$type = 'medication';
	} else {
  	$med_hist=getInactivePrescriptionsbyPatient($pid);
		$type = 'prescription';
	}
	foreach($med_hist as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, $type);
	}
}
if($do_imm) {
	$imm=GetAllImmunizationsbyPatient($pid);
	foreach($imm as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'immunizations');
	}
}
if($do_hosp) {
	$hosp=GetList($pid, 'hospitalization');
	foreach($hosp as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'hospitalization');
	}
}
if($do_fh) {
	$fh=GetFamilyHistory($pid);
	foreach($fh as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_family_history');
	}
}
if($do_pmh) {
	$pmh=GetMedicalHistory($pid);
	foreach($pmh as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'wmt_med_history');
	}
}
if($do_pp) {
	$obhist=getPastPregnancies($pid);
	foreach($obhist as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'past_pregnancy');
	}
}
if($do_pap) {
	$pap_data=getAllPaps($pid);
	foreach($pap_data as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'pap');
	}
}
if($do_us) {
	$ultra=GetList($pid, 'ultrasound');
	foreach($ultra as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'ultrasound');
	}
}
if($do_bd) {
	$bd=GetList($pid, 'bonedensity');
	foreach($bd as $prev) { 
		LinkListEntry($pid, $prev['id'], $encounter, 'bonedensity');
	}
}
?>
<html>
<head>
<title><?php xl('Updating Links','e'); ?></title>
<link rel="stylesheet" href='<?php echo $GLOBALS['css_header'] ?>' type='text/css'>

<style>
td { font-size:10pt; }
</style>

<script type="text/javascript">

function close_refresh(success) {
  window.close();
  return true;
}

</script>

</head>

<form method='post' name='addform' action="<?php echo $form_action; ?>">
<center>
</table>

<table border='0' cellpadding='4'>
  <tr>
    <td>Checking Links: Encounter [<?php echo $encounter; ?>]  PID (<?php echo $pid; ?>)</td>
  </tr>
</table>
<body class="body_top" onLoad='close_refresh("<?php echo $success; ?>");' >
<br/><b>Linking Complete.....Window Closing</b><br/>
</center>
</form>
</body>
</html>
