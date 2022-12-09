<?php 
	$chk = '';
	if($dt{'iui_done'} == 1) { $chk = 'IUI Performed'; }
	if($chk) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine($chk,'');
	}
	$user = UserLook($dt{'iui_receiver'});
	if($user == '') { $user = 'Not Specified'; }
	if($user) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Received By:',$user);
	}
	$user = UserNameFromID($dt{'iui_performer'});
	if($user == '') { $user = 'Not Specified'; }
	if($user) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Performed By:',$user);
	}
	$chc = ListLook($dt{'iui_explained'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Procedure Explained:',$chc);
	}
	$chc = ListLook($dt{'iui_pt_supine'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Patient Placed in Supine Position:',$chc);
	}
	$chc = ListLook($dt{'iui_spec_exam'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Speculum Exam Done:',$chc);
	}
	$chc = ListLook($dt{'iui_cath_difficult'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	$nt = trim($dt{'iui_cath_nt'});
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Catheter Placed in CX - Difficulty?:',$chc);
		if($nt) { PrintSingleLine('&nbsp;&nbsp;&nbsp;','Notes:&nbsp;&nbsp;'.$nt); }
	}
	$chc = ListLook($dt{'iui_specimen_slow'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	$nt = trim($dt{'iui_specimen'});
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Specimen Infused Slowly into CX:',$chc);
		if($nt) { PrintSingleLine('&nbsp;&nbsp;&nbsp;','Notes:&nbsp;&nbsp;'.$nt); }
	}
	$nt = trim($dt{'iui_supine_min'});
	if(!$nt) { $nt = 'Not Specified'; }
	if($nt) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Patient Remained in Supine Position for '.$nt.' minutes.');
	}
	$chc = ListLook($dt{'iui_post_explained'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Post Procedure Instructions Explained:',$chc);
	}
	$chc = ListLook($dt{'iui_have_sex'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('If fresh specimen, advised sexual intercourse again this pm or within 12 to 24 hours if able:',$chc);
	}
	$chc = ListLook($dt{'iui_hot_tub'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Instructed to avoid hot tubs for 24 hours:',$chc);
	}
	$chc = ListLook($dt{'iui_UPT'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Advised patient to check UPT in 12 to 24 days and call with the results:',$chc);
	}
	$nt = trim($dt{'iui_meds'});
	if($nt) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintOverhead('Medications:',$nt);
	}
	$chc = ListLook($dt{'iui_call'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Instructed patient to call f pain or bleeding, fever or any other concerns:',$chc);
	}
	$chc = ListLook($dt{'iui_checkup'},'YesNo');
	if(!$chc) { $chc = 'Not Specified'; }
	if($chc) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintSingleLine('Instructed patient to checkup in 12 days and call us with the results:',$chc);
	}

	$nt = trim($dt{'iui_nt'});
	if($nt) {
		$chp_printed = PrintChapter('IUI',$chp_printed, '', '');
		PrintOverhead('Comments:',$nt);
	}
?>
