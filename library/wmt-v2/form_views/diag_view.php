<?php
if(!isset($field_prefix)) $field_prefix='';
if(!isset($dt[$field_prefix.'plan'])) $dt[$field_prefix.'plan'] = '';
$leave_diag_div_open = true;
include($GLOBALS['srcdir'].'/wmt-v2/diagnosis.print.inc.php');

$nt = trim($dt{$field_prefix.'plan'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "	<tr>\n";
	echo "		<td class='wmtPrnBody' colspan='3'>$nt</td>\n";
	echo "	</tr>\n";
}

?>
