<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($dt{$field_prefix.'rec_review'})) $dt{$field_prefix.'rec_review'}='';
$nt=trim($dt{$field_prefix.'cc'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('',$nt);
}

$nt='';
if($dt{$field_prefix.'rec_review'}) $nt='Medical Records Were Reviewed';
if($nt) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine($nt,'');
}
?>
