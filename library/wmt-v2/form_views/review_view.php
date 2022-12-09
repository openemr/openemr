<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($dt{$field_prefix.'review_nt'})) $dt{$field_prefix.'review_nt'} = '';
$nt = trim($dt{$field_prefix.'review_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "	<tr>\n";
	echo "		<td class='wmtPrnBody'>",$nt,"</td>\n";
	echo "	</tr>\n";
}
?>
