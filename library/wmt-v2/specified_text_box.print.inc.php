<?php
if(!isset($field_name)) $field_name='nt';
if(!isset($field_prefix)) $field_prefix='';
if(!isset($dt[$field_prefix.$field_name])) $dt[$field_prefix.$field_name] = '';
$nt = trim($dt{$field_prefix.$field_name});
$lbl = 'Notes:';
if(isset($module['notes']) && $module['notes'] != '') {
	if(strpos(strtoupper($module['notes']), 'NEGATIVE') === false) {
		$lbl = $module['notes'];
	}
}
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintOverhead('Notes:',$nt);
}

?>
