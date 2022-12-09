<?php
$thaw_done = false;
if($analysis{'anl_thaw_date'}) { $thaw_done = true; }
if($analysis{'anl_thaw_form'}) { $thaw_done = true; }
if($analysis{'anl_thaw_mot'}) { $thaw_done = true; }
if($analysis{'anl_thaw_prog'}) { $thaw_done = true; }
if($analysis{'anl_thaw_tms'}) { $thaw_done = true; }
if($analysis{'anl_thaw_note'}) { $thaw_done = true; }
if($thaw_done) {
	$chp_printed = PrintChapter('Post Thaw Analysis',$chp_printed);
	$nt = ($analysis{'anl_thaw_date'} == '') ? 'Not Specified' : $analysis{'anl_thaw_date'};
	PrintSingleLine('Date Thawed:',$nt);

	PrintSingleLine('Post-thaw Concentration:',$analysis{'anl_thaw_form'});
	PrintSingleLine('Post-thaw Motility:',$analysis{'anl_thaw_mot'});
	$abn = '';
	if($analysis['anl_thaw_prog'] == 1) { $abn = '&nbsp;&nbsp;&nbsp;&nbsp;<b>* ABNORMAL *</b>'; }
	PrintSingleLine('Post-thaw Progression:',$analysis{'anl_thaw_prog'}.$abn);
	PrintSingleLine('Post-thaw TMS:',$analysis{'anl_thaw_tms'});
	$nt = trim($analysis{'anl_thaw_note'});
	if($nt) {
		$chp_printed = PrintChapter('Post Thaw Analysis',$chp_printed);
		PrintOverhead('Comments:',$nt);
	}
} else {
	// For now we just leave it blank
}
?>
