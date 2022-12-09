<?php
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($client_id)) $client_id ='';
if(!isset($fyi->fyi_well_nt)) $fyi->fyi_med_nt = '';
$chp_printed = PrintChapter($chp_title, $chp_printed); 
$hdr_printed = false;
$nt = '';
$wellness_modules = LoadList('well_'.$frmdir);

foreach($wellness_modules as $wmod) {
	$winc = $wmod['option_id'] . '_view.php';
	if(is_file("./$winc")) {
		include("./$winc");
	} else if(is_file($GLOBALS['srcdir']."/wmt-v2/form_views/".$winc)) {
		include($GLOBALS['srcdir']."/wmt-v2/form_views/".$winc);
	}
}

if($fyi->fyi_well_nt) $nt = trim($fyi->fyi_well_nt);
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr = $hdr_printed ? '' : 'Other Notes:';
	if($hdr_printed) PrintSingleLine('&nbsp;', '&nbsp;');
	PrintSingleLine($hdr, htmlspecialchars($nt, ENT_QUOTES));
}
if($review = checkSettingMode('wmt::wellness_review','',$frmdir)) {
	$caller = 'wellness';
	$chk_title = 'Wellness';
	include($GLOBALS['srcdir'].'/wmt-v2/form_bricks/module_reviewed.print.php');
}

if($chp_printed) echo "<table>\n";
?>
