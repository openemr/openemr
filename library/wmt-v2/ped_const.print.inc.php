<?php 
$chp_printed=false;
$chk=ListLook($ad{'pad_const_dur_num'},'RTO_Number');
$chc=ListLook($ad{'pad_const_dur_frame'},'RTO_Frame');
if($chk != '' || $chc != '') {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Duration of Constipation:',$chk.'&nbsp;&nbsp;'.$chc);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_bm_hard'} == '1') { $chk[]='Hard'; }
if($ad{'pad_const_bm_soft'} == '1') { $chk[]='Soft'; }
if($ad{'pad_const_bm_blood'} == '1') { $chk[]='Blood in Stool'; }
if($ad{'pad_const_bm_pain'} == '1') { $chk[]='Painful'; }
if($ad{'pad_const_bm_soil'} == '1') { $chk[]='Soiling Underwear'; }
if($ad{'pad_const_bm_ball'} == '1') { $chk[]='Stool in Balls'; }
if($ad{'pad_const_bm_loose'} == '1') { $chk[]='Loose Watery (or Liquid)'; }
if($ad{'pad_const_bm_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_const_bm_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Quality of BM:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_beh_stiff'} == '1') { $chk[]='Stiffening'; }
if($ad{'pad_const_beh_cry'} == '1') { $chk[]='Crying'; }
if($ad{'pad_const_beh_hold'} == '1') { $chk[]='Stool Witholding'; }
$nt=trim($ad{'pad_const_beh_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Behavior:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_quant_sm'} == '1') { $chk[]='Small'; }
if($ad{'pad_const_quant_md'} == '1') { $chk[]='Medium'; }
if($ad{'pad_const_quant_lg'} == '1') { $chk[]='Large'; }
if($ad{'pad_const_quant_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_const_quant_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Quantity of Stool:',$prnt);
}

$chk='';
$chc=ListLook($ad{'pad_const_freq_num'},'RTO_Number');
if($ad{'pad_const_freq'} == '1' || $chc != '') { $chk='1 stool q '; }
$nt=trim($ad{'pad_const_freq_nt'});
if($chk != '' || $chc != '') {
	$prnt=$chk.' '.$chc.' days&nbsp;&nbsp;';
	if($nt != '') { $prnt.='-&nbsp;'.$nt; }
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Frequency:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_imp_med'} == '1') { $chk[]='Medications'; }
if($ad{'pad_const_imp_beh'} == '1') { $chk[]='Behavior Modification'; }
if($ad{'pad_const_imp_diet'} == '1') { $chk[]='Diet'; }
if($ad{'pad_const_imp_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_const_imp_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$lbl='Modifying Factors: ';
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine($lbl,'Improves: '.$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_wrs_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_const_wrs_stress'} == '1') { $chk[]='Stress'; }
if($ad{'pad_const_wrs_food'} == '1') { $chk[]='Foods'; }
if($ad{'pad_const_wrs_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_const_wrs_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine($lbl,'Worsens: '.$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_const_ass_abd'} == '1') { $chk[]='Abdominal Pain'; }
if($ad{'pad_const_ass_naus'} == '1') { $chk[]='Nausea'; }
if($ad{'pad_const_ass_epi'} == '1') { $chk[]='Epigastric Pain'; }
if($ad{'pad_const_ass_urg'} == '1') { $chk[]='Urgency'; }
if($ad{'pad_const_ass_wght'} == '1') { $chk[]='Weight Loss'; }
if($ad{'pad_const_ass_vom'} == '1') { $chk[]='Vomiting'; }
if($ad{'pad_const_ass_acc'} == '1') {
	$chk[]='Stool Accidents&nbsp;&nbsp;'.
			ListLook($ad{'pad_const_ass_num'},'RTO_Number').'&nbsp;&nbsp;'.
			ListLook($ad{'pad_const_ass_freq'},'Stool_Frequency');
}
if($ad{'pad_const_ass_fev'} == '1') { $chk[]='Fever'; }
if($ad{'pad_const_ass_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_const_ass_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine('Associated s/sx:',$prnt);
}

$prnt='';
if($ad{'pad_const_24hr'} == '1') { $prnt='Yes - First bowel movement after birth within first 24 hours.'; }
if($ad{'pad_const_24hr'} == '2') { $prnt='No - First bowel movement after birth was not within first 24 hours.'; }
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Constipation',$chp_printed);
	PrintSingleLine($prnt,'');
}

if($chp_printed) { CloseChapter(); }
?>


