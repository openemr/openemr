<?php
if(!isset($field_prefix)) $field_prefix='';
$nt=trim($dt{$field_prefix.'assess'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "	<tr>\n";
	echo "		<td class='wmtPrnBody'>",$nt,"</td>\n";
	echo "	</tr>\n";
}
?>
