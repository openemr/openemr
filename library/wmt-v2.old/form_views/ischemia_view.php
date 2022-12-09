<?php
$nt=trim($dt{'ischemia_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintOverhead('',$nt);
}
?>
