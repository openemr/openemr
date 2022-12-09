<?php
$chp_printed=false;
$chk=ListLook($ad{'pad_vom_dur_num'},'RTO_Number');
$chc=ListLook($ad{'pad_vom_dur_frame'},'RTO_Frame');
if($chk != '' || $chc != '') {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Duration of Vomiting:',$chk.'&nbsp;&nbsp;'.$chc);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_qual_proj'} == '1') { $chk[]='Projectile'; }
if($ad{'pad_vom_qual_gag'} == '1') { $chk[]='Gagging&nbsp;&amp;&nbsp;Forced'; }
if($ad{'pad_vom_qual_un'} == '1') { $chk[]='Uncomplicated'; }
if($ad{'pad_vom_qual_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_qual_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Quality of Vomiting:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_sev_night'} == '1') { $chk[]='Night Time Awakening'; }
if($ad{'pad_vom_sev_ill'} == '1') { $chk[]='Frequent Respiratory Illness'; }
if($ad{'pad_vom_sev_loss'} == '1') { $chk[]='Weight Loss'; }
if($ad{'pad_vom_sev_fuss'} == '1') { $chk[]='Fussy/Irritable'; }
if($ad{'pad_vom_sev_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_sev_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Severity of Vomiting:',$prnt);
}

$chk=$prnt=$nt='';
$chc1=ListLook($ad{'pad_vom_quant_num'},'One_to_Ten');
$chc2=ListLook($ad{'pad_vom_quant_meas'},'PC1_Vomit_Measure');
if($ad{'pad_vom_quant_oth'} == '1') { $chk='Other'; }
$nt=trim($ad{'pad_vom_quant_nt'});
if($chc1 != '' || $chc2 != '') { $prnt=$chc1.'&nbsp;'.$chc2; }
if($chk != '') { 
	if($prnt != '') { $prnt.=', '; }
	$prnt.=$chk;
}
if($prnt != '') {
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Quantity of Vomit:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_char_form'} == '1') { $chk[]='Formula'; }
if($ad{'pad_vom_char_bile'} == '1') { $chk[]='Bile'; }
if($ad{'pad_vom_char_blood'} == '1') { $chk[]='Blood'; }
if($ad{'pad_vom_char_muc'} == '1') { $chk[]='Mucous'; }
if($ad{'pad_vom_char_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_char_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Character of Emesis:',$prnt);
}

$chk=$prnt=$nt='';
$prnt=ListLook($ad{'pad_vom_freq_frame'},'PC1_Vomit_Freq');
$nt=trim($ad{'pad_vom_freq_nt'});
if($prnt != '') {
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Quantity of Vomit:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_time_day'} == '1') { $chk[]='Weekdays'; }
if($ad{'pad_vom_time_end'} == '1') { $chk[]='Weekends'; }
if($ad{'pad_vom_time_am'} == '1') { $chk[]='A.M.'; }
if($ad{'pad_vom_time_pm'} == '1') { $chk[]='P.M.'; }
if($ad{'pad_vom_time_aft'} == '1') { $chk[]='After Meals'; }
if($ad{'pad_vom_time_pri'} == '1') { $chk[]='Prior to Bowel Movement'; }
if($ad{'pad_vom_time_stress'} == '1') { $chk[]='Stressful Periods'; }
if($ad{'pad_vom_time_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_time_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Timing:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_imp_soft'} == '1') { $chk[]='Soft Stools'; }
if($ad{'pad_vom_imp_ant'} == '1') { $chk[]='Antacids'; }
if($ad{'pad_vom_imp_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_vom_imp_diet'} == '1') { $chk[]='Diet'; }
if($ad{'pad_vom_imp_rice'} == '1') { $chk[]='Rice Cereal'; }
if($ad{'pad_vom_imp_med'} == '1') { $chk[]='Other Meds'; }
if($ad{'pad_vom_imp_burp'} == '1') { $chk[]='Burping'; }
if($ad{'pad_vom_imp_up'} == '1') { $chk[]='Sitting in an Upright Position'; }
if($ad{'pad_vom_imp_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_imp_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$lbl='Modifying Factors:';
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine($lbl,'Improves: '.$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_wrs_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_vom_wrs_stress'} == '1') { $chk[]='Stress'; }
if($ad{'pad_vom_wrs_food'} == '1') { $chk[]='Foods'; }
if($ad{'pad_vom_wrs_move'} == '1') { $chk[]='Movement'; }
if($ad{'pad_vom_wrs_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_imp_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine($lbl,'Worsens: '.$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_vom_ass_abd'} == '1') { $chk[]='Abdominal Pain'; }
if($ad{'pad_vom_ass_naus'} == '1') { $chk[]='Nausea'; }
if($ad{'pad_vom_ass_epi'} == '1') { $chk[]='Epigastric Pain'; }
if($ad{'pad_vom_ass_burn'} == '1') { $chk[]='Burning in Chest'; }
if($ad{'pad_vom_ass_burn'} == '1') { $chk[]='Burning in Chest'; }
if($ad{'pad_vom_ass_loss'} == '1') { $chk[]='Weight Loss'; }
if($ad{'pad_vom_ass_dia'} == '1') { $chk[]='Diarrhea'; }
if($ad{'pad_vom_ass_fev'} == '1') { $chk[]='Fever'; }
if($ad{'pad_vom_ass_const'} == '1') { $chk[]='Constipation'; }
if($ad{'pad_vom_ass_head'} == '1') { $chk[]='Headaches'; }
if($ad{'pad_vom_ass_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_vom_ass_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Vomiting',$chp_printed);
	PrintSingleLine('Associated s/sx:',$prnt);
}

if($chp_printed) { CloseChapter(); }
?>

