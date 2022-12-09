<?php
if(!isset($field_prefix)) $field_prefix='';
// echo "In the report prefix: $field_prefix <br>\n";
$nt=trim($dt{$field_prefix.'pat_instruct'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "	<tr>\n";
	echo "		<td class='wmtPrnBody'>",$nt,"</td>\n";
	echo "	</tr>\n";
}
?>
