<?php
include($GLOBALS['srcdir'].'/wmt/family_history.print.inc.php');

// Print the extra family history stuff
// Set up the family history extras from the yes and no list
$fh_labels = array('tmp_fh_rs_dia' => 'Diabetes Mellitus', 
	'tmp_fh_rs_coronary' => 'Coronary Artery Disease', 
	'tmp_fh_rs_htn' => 'HTN',
	'tmp_fh_rs_hyper' => 'Hyperlipidemia', 
	'tmp_fh_rs_thy' => 'Thyroid Cancer',
	'tmp_fh_rs_colon' => 'Colon Cancer', 
	'tmp_fh_rs_lung', 'Lung Cancer',
	'tmp_fh_rs_hd' => 'Heart Disease', 
	'tmp_fh_rs_cardiac' => 'Sudden Cardiac Death',
	'tmp_fh_rs_vhd' => 'Valvular Heart Disease', 
	'tmp_fh_rs_arr' => 'Arrhythmia');

$no='';
$yes='';
if($dt['ee1_fh_extra_yes']) {
	$fh_yes = explode('|', $dt['ee1_fh_extra_yes']);
	foreach($fh_yes as $opt) {
		$yes= AppendItem($yes, $fh_labels[$opt].
									"&nbsp;-&nbsp;<span class='wmtLabel'>YES</span>");
	}
}
if($dt['ee1_fh_extra_no']) {
	$fh_no = explode('|', $dt['ee1_fh_extra_no']);
	foreach($fh_no as $opt) {
		$no= AppendItem($no, $fh_labels[$opt].
									"&nbsp;-&nbsp;<span class='wmtLabel'>NO</span>");
	}
}

if(!empty($no) || !empty($yes)) {
	if($chp_printed) {
		echo "	</table>\n";
		echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	}
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintHeader('Has anyone in your family ever been diagnosed with:', false);
	if(!empty($no)) { PrintSingleLine($no); }
	if(!empty($yes)) { PrintSingleLine($yes); }
}

$nt=trim($dt{'ee1_fh_notes'});
if(!empty($nt)) {
	if($chp_printed) {
		echo "	</table>\n";
		echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	}
	$chp_printed=PrintChapter($chp_printed,$chp_printed);
	PrintOverhead('Other Notes:',$nt);
}
$nt='';
if($dt{'ee1_fh_non_contrib'}) { $nt='Non-Contributory'; }
if($dt{'ee1_fh_adopted'}) { $nt=AppendItem($nt,'Patient is Adopted'); }
if(!empty($nt)) {
	if($chp_printed) {
		echo "	</table>\n";
		echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	}
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine($nt);
}
?>
