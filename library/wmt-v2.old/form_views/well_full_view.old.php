<?php
if(!isset($field_prefix)) $field_prefix = false;
if(!isset($client_id)) $client_id ='';
if(!isset($fyi->fyi_well_nt)) $fyi->fyi_med_nt = '';
$chp_printed = PrintChapter('Wellness', $chp_printed); 
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


if($hdr_printed) {
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
}
$hdr_printed = false;
$hdr = 'Blood &amp; Urine Tests';

$chc = ListLook($dt{$field_prefix.'pat_blood_type'},'Blood_Types');
$chk = ListLook($dt{$field_prefix.'pat_rh_factor'},'RH_Factor');
$txt = '';
if($chc || $chk) {
	$txt = "<span class='wmtPrnLabel'>Blood Type:&nbsp;</span><span class='wmtPrnBody'>".htmlspecialchars($chc,ENT_QUOTES,'',FALSE)."&nbsp;&nbsp;".htmlspecialchars($chk,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_chol'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Cholesterol Check:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_hepc'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Hep C Test:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_lipid'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Lipid Panel:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_lipo'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Lipoprotein:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_tri'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Triglycerides:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_urine_alb'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Urine Micro Alb:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_hgba1c'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last HgbA1c:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

$nt = trim($dt{$field_prefix.'last_hgba1c_val'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last HgbA1c Value:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}

if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}

$hdr_printed = false;
$hdr = 'Cardio &amp; Pulmonary Tests';
$txt = '';
$nt = trim($dt{$field_prefix.'last_ekg'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last EKG:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_pft'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last PFT:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}

$hdr_printed = false;
$hdr = 'Colon';
if($pat_sex == 'm') $hdr .= ' &amp; Prostate';
$txt = '';
$nt = trim($dt{$field_prefix.'last_colon'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Colon:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_fecal'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Fecal Occult Bloot Test:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_barium'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Barium Enema:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_sigmoid'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Flexible Sigmoidoscopy:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_psa'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last PSA:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_rectal'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Rectal Exam:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}

$hdr_printed = false;
$hdr = 'Hearing';
$txt = '';
$nt = trim($dt{$field_prefix.'last_hear'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Hearing Test:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$chc = ListLook($dt{$field_prefix.'left_ear'},'PassFail');
$chk = ListLook($dt{$field_prefix.'right_ear'},'PassFail');
if($chc != '' || $chk != '') {
	if($txt != '') $txt .= $span_delim;
	if($chc == '') $chc = 'Not Specified';
	$txt .= "<span class='wmtPrnLabel'>Left Ear:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($chc,ENT_QUOTES,'',FALSE)."</span>";
	if($chk == '') $chk = 'Not Specified';
	$txt .= "<span class='wmtPrnLabel'>Right Ear:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($chk,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}
$nt = trim($dt{$field_prefix.'hear_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('Notes:',htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}

$hdr_printed = false;
$hdr = 'Dental';
$txt = '';
$nt = trim($dt{$field_prefix.'last_dental'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Dental:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}
$nt = trim($dt{$field_prefix.'last_dental_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('Notes:',htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}

$hdr_printed = false;
$hdr = 'Diabetes Related';
$txt = '';
$nt = trim($dt{$field_prefix.'last_db_screen'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Diabetes Screening:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_db_eye'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Diabetic Eye Exam:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_db_foot'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Diabetic Foot Exam:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_glaucoma'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Glaucoma Screening:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_db_dbsmt'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Self Management Training:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}

$hdr_printed = false;
$hdr = 'Gynecological';
$txt = '';
$nt = trim($dt{$field_prefix.'last_mp'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>LMP:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_bone'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Bone Density:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_mamm'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Mammogram:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = ListLook($dt{$field_prefix.'hpv'},'Yes_No');
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>HPV Vaccinated:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_hpv'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last HPV:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'last_pap'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last Pap Smear:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
$nt = trim($dt{$field_prefix.'HCG'});
if($nt) {
	if($txt != '') $txt .= $span_delim;
	$txt .= "<span class='wmtPrnLabel'>Last HCG Result:&nbsp;</span><span class='wmtPrnBody'>";
	$txt .= htmlspecialchars($nt,ENT_QUOTES,'',FALSE)."</span>";
}
if($txt !=  '') {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	echo "<tr><td>$txt</td></tr>\n";
}

$nt = ListLook($dt{$field_prefix.'mam_law'},'YesNo','');
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('Dense Breast Mammogram Law Informed?', htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}

$nt = trim($dt{$field_prefix.'pap_nt'});
if($nt || $chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('Last Pap Notes:', htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}
$nt = trim($dt{$field_prefix.'pap_hist_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('History of Abnormal Pap:', htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}

$nt = trim($dt{$field_prefix.'age_men'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintSingleLine('Age Menarche:', htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
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
$hdr_printed = false;

if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Other Notes:', htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
	$hdr_printed = true;
}

if($fyi->fyi_well_nt) $nt = trim($fyi->fyi_well_nt);
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr = $hdr_printed ? '' : 'Other Notes:';
	if($hdr_printed) PrintSingleLine('&nbsp;', '&nbsp;');
	PrintSingleLine($hdr, htmlspecialchars($nt,ENT_QUOTES,'',FALSE));
}
?>
