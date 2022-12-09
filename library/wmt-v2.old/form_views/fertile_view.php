<?php
if(!isset($field_prefix)) $field_prefix = '';

$nt = trim($dt{$field_prefix.'curr_obgyn'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Current Ob/Gyn Doctor:', $nt);
}

$nt = trim($dt{$field_prefix.'trying'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('How long have you been trying to conceive?', $nt);
}

$hdr_printed = false;
$chc = ListLook($dt{$field_prefix.'ectopic'}, 'Yes_No');
$nt = trim($dt{$field_prefix.'ectopic_nt'});
if($nt || $chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr_printed,'Have you had any of the following:');
	if($chc && $nt)  $chc .= ' - ';
	$chc .= $nt;	
	PrintSingleLine('Ectopic Pregnancy:', $chc);
}

$chc = ListLook($dt{$field_prefix.'ivf'}, 'Yes_No');
$nt = trim($dt{$field_prefix.'ivf_nt'});
if($nt || $chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr_printed,'Have you had any of the following:');
	if($chc && $nt)  $chc .= ' - ';
	$chc .= $nt;	
	PrintSingleLine('IVF Failure:', $chc);
}

$chc = ListLook($dt{$field_prefix.'unexplained'}, 'Yes_No');
$nt = trim($dt{$field_prefix.'unexplained_nt'});
if($nt || $chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr_printed,'Have you had any of the following:');
	if($chc && $nt)  $chc .= ' - ';
	$chc .= $nt;	
	PrintSingleLine('Diagnosed with "Uncexplained Infertility":', $chc);
}

$chc = ListLook($dt{$field_prefix.'nk_cell'}, 'Yes_No');
$nt = trim($dt{$field_prefix.'nk_cell_nt'});
if($nt || $chc) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr_printed,'Have you had any of the following:');
	if($chc && $nt)  $chc .= ' - ';
	$chc .= $nt;	
	PrintSingleLine('Tested for Natural Killer (NK) Cell:', $chc);
}
?>
