<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($chp_title)) $chp_title = 'Fix The Yes/No Chapter Title';
if(!isset($dt['yes_no_nt'])) $dt['yes_no_nt'] = '';
if(!isset($yes_choices)) $yes_choices = '';
if(!isset($no_choices)) $no_choices = '';
$hdr_printed = false;
if($frmdir == 'acog_complete')  $hdr =  "Includes patient, baby's father or anyone in either family with:";

foreach($yes_no_options as $o) {
	$nt = '';
	if(strpos($yes_choices, '~'.$o['option_id'].'~') !== false) $nt = 'YES&nbsp;-&nbsp;';
	if(strpos($no_choices, '~'.$o['option_id'].'~') !== false) $nt = 'NO&nbsp;&nbsp;-&nbsp;';
	if($nt) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		$hdr_printed = PrintHeader($hdr, $hdr_printed);
		PrintSingleLine($nt, $o['title']);
	}
}

$nt = trim($dt{$field_prefix.'yes_no_nt'});
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintHeader($hdr, $hdr_printed);
	PrintOverhead('Comments / Description:', $nt);
}
?>
