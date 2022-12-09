<?php 
if(!isset($field_prefix)) $field_prefix = '';
$_vit = false;
if($dt{'vital_height'} != 0 && $dt{'vital_height'} != '') $_vit = true;
if($dt{'vital_weight'} != 0 && $dt{'vital_weight'} != '') $_vit = true;
if($dt{'vital_bps'} != 0 && $dt{'vital_bps'} != '') $_vit = true;
if($dt{'vital_bpd'} != 0 && $dt{'vital_bpd'} != '') $_vit = true;
if($dt{'vital_pulse'} != 0 && $dt{'vital_pulse'} != '') $_vit = true;
if($dt{'vital_BMI'} != 0 && $dt{'vital_BMI'} != '') $_vit = true;
if($dt{'vital_BMI_status'} != '') $_vit = true;
$_dip = false;
if($dt{'vital_leukocytes'} != '') $_dip = true;
if($dt{'vital_nitrite'} != '') $_dip = true;
if($dt{'vital_protein'} != '') $_dip = true;
if($dt{'vital_glucose'} != '') $_dip = true;
if($dt{'vital_blood'} != '') $_dip = true;
if($dt{'vital_specific_gravity'} != '') $_dip = true;
if($dt{'vital_ph'} != '') $_dip = true;
if($dt{'vital_ketones'} != '') $_dip = true;
if($dt{'vital_urobilinogen'} != '') $_dip = true;
if($dt{'vital_bilirubin'} != '') $_dip = true;

if($_vit) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel'>Height:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_height'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Weight:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_weight'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>BP:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_bps'},"/",$dt{'vital_bpd'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Pulse:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_pulse'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>BMI:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_BMI'},"&nbsp;&nbsp;",$dt{'vital_BMI_status'},"</td>\n";
	echo "		</tr>\n";
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
}
if($_dip) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel'>Leukocytes:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_leukocytes'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Nitrite:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_nitrite'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Protein:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_protein'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Glucose:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_glucose'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Blood:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_blood'},"&nbsp;</td>\n";
	echo "		</tr>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel'>Specific Gravity:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_specific_gravity'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Ph:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_ph'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Ketones:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_ketones'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Urobiliniogen:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_urobilinogen'},"&nbsp;</td>\n";
	echo "			<td class='wmtPrnLabel'>Bilirubin:</td>\n";
	echo "			<td class='wmtPrnBody'>",$dt{'vital_bilirubin'},"&nbsp;</td>\n";
	echo "		</tr>\n";
	echo "	</table>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
}

$nt = '';
if($dt{$field_prefix.'father_yes'} == 1) $nt = 'Husband / Partner: Has Fathered a Child';
if($dt{$field_prefix.'father_no'} == 1) $nt = 'Husband / Partner: Has Never Fathered a Child';
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($nt);
}

$chc = 'Not Employed';
$nt = '';
if($dt{$field_prefix.'hus_empl'} == 1) $chc = 'Employed';
if($dt{$field_prefix.'hus_empl_nt'}) $nt = 'Occupation: '.$dt{$field_prefix.'hus_empl_nt'};
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$chc = 'No Tobacco Use';
$nt = '';
if($dt{$field_prefix.'hus_tobacco'} == 1) $chc = 'Uses Tobacco';
if($dt{$field_prefix.'hus_tobacco_nt'}) $nt = $dt{$field_prefix.'hus_tobacco_nt'};
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$chc = 'No Drug Use';
$nt = '';
if($dt{$field_prefix.'hus_drug'} == 1) $chc = 'Uses Drugs';
if($dt{$field_prefix.'hus_drug_nt'}) $nt = $dt{$field_prefix.'hus_drug_nt'};
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'coital'} == 1) $chc = 'Coital Frequency';
if($dt{$field_prefix.'coital_nt'}) {
	$chc = 'Coital Frequency:';
	$nt = $dt{$field_prefix.'coital_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'dysmenorrhea'} == 1) $chc = 'Dysmenorrhea';
if($dt{$field_prefix.'dys_nt'}) {
	$chc = 'Dysmenorrhea:';
	$nt = $dt{$field_prefix.'dys_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'pelvic_pain'} == 1) $chc = 'Pelvic Pain';
if($dt{$field_prefix.'pain_nt'}) {
	$chc = 'Pelvic Pain:';
	$nt = $dt{$field_prefix.'pain_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine($chc, $nt);
}

$hdr_printed = false;
$chc = $nt = '';
if($dt{$field_prefix.'prev_hsg'} == 1) $chc = 'HSG Results';
if($dt{$field_prefix.'prev_hsg_nt'}) {
	$chc = 'HSG Results:';
	$nt = $dt{$field_prefix.'prev_hsg_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Workup(s)', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'prev_semen'} == 1) $chc = 'Semen Analysis';
if($dt{$field_prefix.'prev_semen_nt'}) {
	$chc = 'Semen Analysis:';
	$nt = $dt{$field_prefix.'prev_semen_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Workup(s)', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}

$hdr_printed = false;
$chc = $nt = '';
if($dt{$field_prefix.'prev_clomid'} == 1) $chc = 'Clomid';
if($dt{$field_prefix.'prev_clomid_nt'}) {
	$chc = 'Clomid:';
	$nt = $dt{$field_prefix.'prev_clomid_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Assistance', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'prev_gnrh'} == 1) $chc = 'GnRH Agonists';
if($dt{$field_prefix.'prev_gnrh_nt'}) {
	$chc = 'GnRH Agonists:';
	$nt = $dt{$field_prefix.'prev_gnrh_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Assistance', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'prev_iui'} == 1) $chc = 'IUI';
if($dt{$field_prefix.'prev_iui_nt'}) {
	$chc = 'IUI:';
	$nt = $dt{$field_prefix.'prev_iui_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Assistance', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}

$chc = $nt = '';
if($dt{$field_prefix.'prev_ivf'} == 1) $chc = 'IVF';
if($dt{$field_prefix.'prev_ivf_nt'}) {
	$chc = 'IVF:';
	$nt = $dt{$field_prefix.'prev_ivf_nt'};
}
if($chc || $nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader('Previous Assistance', $hdr_printed);
	PrintSingleLine('', $chc.' '.$nt);
}
?>
