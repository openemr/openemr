<?php
if(!isset($field_prefix)) $field_prefix = '';
$nt=trim($dt{$field_prefix.'hpi'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintOverhead('',$nt);
}
?>
