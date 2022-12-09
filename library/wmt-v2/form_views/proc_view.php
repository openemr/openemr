<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($dt{$field_prefix.'proc_choices'})) $dt{$field_prefix.'proc_choices'} = '|';	
if(!isset($dt{$field_prefix.'proc_nt'})) $dt{$field_prefix.'proc_nt'} = '';	
$choices = explode('|', $dt{$field_prefix.'proc_choices'});
$nt = '';

$hdr_printed = false;
$nt = trim($dt{$field_prefix.'proc_nt'});
if($nt) {
	EE1_PrintNote($nt,$chp_title,'Notes:');
}
?>
