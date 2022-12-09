<?php 
$chp_printed=false;

$prnt=$nt='';
$chk=array();
if($ad{'pad_bwl_dx_crhon'} == '1') { $chk[]='Crohn\'s'; }
if($ad{'pad_bwl_dx_ulcer'} == '1') { $chk[]='Ulcerative Colitis'; }
if(trim($ad{'pad_bwl_dx_ppd'} != '')) { 
	$chk[]='PPD Result: '.trim($ad{'pad_bwl_dx_ppd'}); 
}
if($ad{'pad_bwl_dx_ind'} == '1') { $chk[]='Indeterminate Involvement'; }
if(trim($ad{'pad_bwl_dx_ugi'} != '')) { 
	$chk[]='UGI/EGD/SBFT: '.trim($ad{'pad_bwl_dx_ugi'}); 
}
if(trim($ad{'pad_bwl_dx_colon'} != '')) { 
	$chk[]='Colon: '.trim($ad{'pad_bwl_dx_colon'}); 
}
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
$lbl='Diagnosis:';
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if(trim($ad{'pad_bwl_last_ppd'} != '')) { 
	$chk[]='Last PPD: '.trim($ad{'pad_bwl_last_ppd'}); 
}
if(trim($ad{'pad_bwl_last_dexa'} != '')) { 
	$chk[]='Last Dexa Scan: '.trim($ad{'pad_bwl_last_dexa'}); 
}
if(trim($ad{'pad_bwl_last_eye'} != '')) { 
	$chk[]='Last Eye Exam: '.trim($ad{'pad_bwl_last_eye'}); 
}
if(trim($ad{'pad_bwl_last_remi'} != '')) { 
	$chk[]='Last Remicade: '.trim($ad{'pad_bwl_last_remi'}); 
}
if(trim($ad{'pad_bwl_prom'} != '')) { 
	$chk[]='Prometheseus IBD: '.trim($ad{'pad_bwl_prom'}); 
}
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,$prnt);
}

$prnt=trim($ad{'pad_bwl_pain'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Abdominal Pain:',$prnt);
}

$prnt=trim($ad{'pad_bwl_stl_num'});
$lbl='Stools:';
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,'Number - '.$prnt);
	$lbl='';
}
$prnt=trim($ad{'pad_bwl_stl_con'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,'Consistency - '.$prnt);
	$lbl='';
}
$prnt=trim($ad{'pad_bwl_stl_blood'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,'Blood - '.$prnt);
	$lbl='';
}
$prnt=trim($ad{'pad_bwl_stl_urg'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine($lbl,'Urgency - '.$prnt);
}

$prnt=trim($ad{'pad_bwl_energy'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Energy:',$prnt);
}

$prnt=trim($ad{'pad_bwl_diet'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Appetite/Diet/Weight:',$prnt);
}

$prnt=trim($ad{'pad_bwl_joint'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Joints:',$prnt);
}

$prnt=trim($ad{'pad_bwl_fev'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Fever:',$prnt);
}

$prnt=trim($ad{'pad_bwl_school'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('School:',$prnt);
}

$prnt=trim($ad{'pad_bwl_last_hgb'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Last Hgb:',$prnt);
}

$prnt=trim($ad{'pad_bwl_oth'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Other Therapy:',$prnt);
}

$prnt=trim($ad{'pad_bwl_last_esr'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Inflammatory Bowel Disease',$chp_printed);
	PrintSingleLine('Last ESR:',$prnt);
}

if($chp_printed) { CloseChapter(); }
?>


