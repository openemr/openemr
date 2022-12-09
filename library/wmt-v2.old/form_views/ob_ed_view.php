<?php
if(!isset($field_prefix)) $field_prefix='';
$nt=trim($dt{$field_prefix.'tri_1_material_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintOverhead('1st Trimester Material:',$nt);
}
$nt=trim($dt{$field_prefix.'tri_2_material_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintOverhead('2nd Trimester Material:',$nt);
}
$nt=trim($dt{$field_prefix.'tri_3_material_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintOverhead('3rd Trimester Material:',$nt);
}
?>
