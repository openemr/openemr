<?php
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($client_id)) $client_id ='';
$chc = ListLook($dt{$field_prefix.'pat_blood_type'},'Blood_Types');
$chk = ListLook($dt{$field_prefix.'pat_rh_factor'},'RH_Factor');
if($chc || $chk) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Blood Type:', $chc.'&nbsp;&nbsp;'.$chk);
}
$chc = ListLook($dt{$field_prefix.'group_b_strep'},'PosNeg');
if($chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Group B Strep:', $chc);
}
$chc = ListLook($dt{$field_prefix.'latex_allergy'},'Yes_No');
if($chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Latex Allergy:', $chc);
}
$nt = trim($dt{$field_prefix.'drug_allergy'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Drug Allergy:', $nt);
}

$hdr_printed = false;
$nt = trim($dt{$field_prefix.'last_mp'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('LMP:', $nt);
}

$nt = ListLook($dt{$field_prefix.'hpv'},'Yes_No');
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('HPV Vaccinated:', $nt);
}

$nt = trim($dt{$field_prefix.'age_men'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Age Menarche:', $nt);
}

$chc = '';
if($dt{$field_prefix.'last_pap'}) $chc = 'Last Pap: '.$dt{$field_prefix.'last_pap'};
$nt = trim($dt{$field_prefix.'pap_nt'});
if($nt || $chc) {
	if(!$chc) $chc = 'Last Pap Notes:';
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$nt = trim($dt{$field_prefix.'pap_hist_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('History of Abnormal Pap:', $nt);
}

$chc = 'Periods: ';
if($dt{$field_prefix.'pflow'} == 'h') $chc .= 'Flow - Heavy'; 
if($dt{$field_prefix.'pflow'} == 'l') $chc .= 'Flow - Light'; 
if($dt{$field_prefix.'pflow'} == 'n') $chc .= 'Flow - Normal'; 
if($dt{$field_prefix.'pflow'} == 'x') $chc .= 'Flow - None'; 
if($dt{$field_prefix.'pflow'} == 'm') $chc .= 'Flow - Menopause'; 
$nt = trim($dt{$field_prefix.'pflow_dur'});
if($nt) $nt = 'Duration: '.$nt.' days';
if($chc == 'Periods: ') {
	$chc = '';
} else {
	$chc .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}
if($nt || $chc) {
	$chc .= $nt;
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc);
}

$chc = 'Frequency: ';
if($dt{$field_prefix.'pfreq'} == 'r') $chc .= 'Regular'; 
if($dt{$field_prefix.'pfreq'} == 'i') $chc .= 'Irregular'; 
if($dt{$field_prefix.'pfreq'} == 'n') $chc .= 'None'; 
$nt = trim($dt{$field_prefix.'pfreq_days'});
if($nt) $nt = 'Interval: '.$nt.' days';
if($chc == 'Frequency: ') {
	$chc = '';
} else {
	$chc .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
}
if($nt || $chc) {
	$chc .= $nt;
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc);
}

$nt = trim($dt{$field_prefix.'wellness_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Other Notes:', $nt);
}
?>
