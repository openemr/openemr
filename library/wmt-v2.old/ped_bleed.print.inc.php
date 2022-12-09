<?php
$chp_printed=false;
$chk=ListLook($ad{'pad_bld_dur_num'},'RTO_Number');
$chc=ListLook($ad{'pad_bld_dur_frame'},'RTO_Frame');
if($chk != '' || $chc != '') {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Duration of Rectal Bleeding:',$chk.'&nbsp;&nbsp;'.$chc);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_qual_red'} == '1') { $chk[]='Bright Red Blood'; }
if($ad{'pad_bld_qual_dark'} == '1') { $chk[]='Dark/Black Stools'; }
if($ad{'pad_bld_qual_mix'} == '1') { $chk[]='Blood Mixed with Mucous'; }
if($ad{'pad_bld_qual_tp'} == '1') { $chk[]='Blood Noted on Toilet Paper'; }
if($ad{'pad_bld_qual_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_qual_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Quality of Hematochezia:',$prnt);
}

$prnt=$nt='';
if($ad{'pad_bld_def'} == '1') { $prnt='Painful'; }
if($ad{'pad_bld_def'} == '2') { $prnt='Non-Painful'; }
$nt=trim($ad{'pad_bld_def_nt'});
if(!empty($prnt)) {
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Defecation:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_beh_stiff'} == '1') { $chk[]='Stiffening'; }
if($ad{'pad_bld_beh_cry'} == '1') { $chk[]='Crying'; }
if($ad{'pad_bld_beh_strain'} == '1') { $chk[]='Straining'; }
$nt=trim($ad{'pad_bld_beh_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Behavior:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_quant_strk'} == '1') { $chk[]='Streaks'; }
if($ad{'pad_bld_quant_lt'} == '1') { $chk[]='&lt;&nbsp;1/2&nbsp;tsp'; }
if($ad{'pad_bld_quant_clot'} == '1') { $chk[]='Clots'; }
if($ad{'pad_bld_quant_gt'} == '1') { $chk[]='&gt;&nbsp;1/2&nbsp;tsp'; }
if($ad{'pad_bld_quant_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_quant_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Quantity of Blood:',$prnt);
}

$chc=ListLook($ad{'pad_bld_freq'},'PC1_Bleed_Freq');
$nt=trim($ad{'pad_bld_freq_nt'});
if(!empty($chc)) {
	if(!empty($nt)) { $chc.='&nbsp;-&nbsp;&nbsp;'; }
}
$chc.=$nt;
if(!empty($chc)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Frequency:',$chc);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_stl_hard'} == '1') { $chk[]='Hard'; }
if($ad{'pad_bld_stl_soft'} == '1') { $chk[]='Soft'; }
if($ad{'pad_bld_stl_liq'} == '1') { $chk[]='Liquid'; }
if($ad{'pad_bld_stl_muc'} == '1') { $chk[]='Mucous'; }
if($ad{'pad_bld_stl_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_stl_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Consistency of Stool:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_imp_soft'} == '1') { $chk[]='Soft Stools'; }
if($ad{'pad_bld_imp_form'} == '1') { $chk[]='Formula'; }
if($ad{'pad_bld_imp_diet'} == '1') { $chk[]='Diet'; }
if($ad{'pad_bld_imp_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_imp_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
$lbl='Modifying Factors: ';
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine($lbl,'Improves: '.$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_wrs_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_bld_wrs_stress'} == '1') { $chk[]='Stress'; }
if($ad{'pad_bld_wrs_food'} == '1') { $chk[]='Foods'; }
if($ad{'pad_bld_wrs_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_wrs_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine($lbl,'Worsens: '.$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_bld_ass_abd'} == '1') { $chk[]='Abdominal Pain'; }
if($ad{'pad_bld_ass_naus'} == '1') { $chk[]='Nausea'; }
if($ad{'pad_bld_ass_epi'} == '1') { $chk[]='Epigastric Pain'; }
if($ad{'pad_bld_ass_urg'} == '1') { $chk[]='Urgency'; }
if($ad{'pad_bld_ass_loss'} == '1') { $chk[]='Weight Loss'; }
if($ad{'pad_bld_ass_vom'} == '1') { $chk[]='Vomiting'; }
if($ad{'pad_bld_ass_fev'} == '1') { $chk[]='Fever'; }
if($ad{'pad_bld_ass_const'} == '1') { $chk[]='Constipation'; }
if($ad{'pad_bld_ass_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_bld_ass_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine('Associated s/sx:',$prnt);
}

$prnt='';
if($ad{'pad_bld_24hr'} == '1') { $prnt='Yes - First bowel movement after birth within first 24 hours.'; }
if($ad{'pad_bld_24hr'} == '2') { $prnt='No - First bowel movement after birth was not within first 24 hours.'; }
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Hematochezia',$chp_printed);
	PrintSingleLine($prnt,'');
}

if($chp_printed) { CloseChapter(); }
?>

