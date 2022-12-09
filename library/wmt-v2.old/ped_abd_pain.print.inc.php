<?php
$chp_printed=false;
$chk=ListLook($ad{'pad_abd_dur_num'},'RTO_Number');
$chc=ListLook($ad{'pad_abd_dur_frame'},'RTO_Frame');
if($chk != '' || $chc != '') {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Duration of Pain:',$chk.'&nbsp;&nbsp;'.$chc);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_loc_epi'} == '1') { $chk[]='Epigastric'; }
if($ad{'pad_abd_loc_low'} == '1') { $chk[]='Lower'; }
if($ad{'pad_abd_loc_dif'} == '1') { $chk[]='Diffuse'; }
if($ad{'pad_abd_loc_peri'} == '1') { $chk[]='Periumbilical'; }
if($ad{'pad_abd_loc_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_abd_loc_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Location of Pain:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_qual_cramp'} == '1') { $chk[]='Cramping'; }
if($ad{'pad_abd_qual_stab'} == '1') { $chk[]='Stabbing'; }
if($ad{'pad_abd_qual_burn'} == '1') { $chk[]='Burning'; }
if($ad{'pad_abd_qual_dull'} == '1') { $chk[]='Dull/Achy'; }
if($ad{'pad_abd_qual_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_abd_qual_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Quality of Pain:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_sev_wake'} == '1') { $chk[]='Night Time Awakening'; }
if($ad{'pad_abd_sev_stop'} == '1') { $chk[]='Stops Routine Momentarily'; }
if($ad{'pad_abd_sev_cry'} == '1') { $chk[]='Cries WIth Pain'; }
$chc=ListLook($ad{'pad_abd_sev_scale'},'One_To_Ten');
if($chc != '') { $chk[]='Scale: '.$chc; }
$nt=trim($ad{'pad_abd_sev_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Severity of Pain:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_time_day'} == '1') { $chk[]='Weekdays'; }
if($ad{'pad_abd_time_end'} == '1') { $chk[]='Weekends'; }
if($ad{'pad_abd_time_am'} == '1') { $chk[]='A.M.'; }
if($ad{'pad_abd_time_pm'} == '1') { $chk[]='P.M.'; }
if($ad{'pad_abd_time_bef'} == '1') { $chk[]='Before Meals'; }
if($ad{'pad_abd_time_aft'} == '1') { $chk[]='After Meals'; }
if($ad{'pad_abd_time_prior'} == '1') { $chk[]='Prior ro Bowel Movement'; }
if($ad{'pad_abd_time_stress'} == '1') { $chk[]='Stressful Periods'; }
if($ad{'pad_abd_time_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_abd_time_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Timing:',$prnt);
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_imp_ant'} == '1') { $chk[]='Antacids'; }
if($ad{'pad_abd_imp_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_abd_imp_bow'} == '1') { $chk[]='Bowel Movement'; }
if($ad{'pad_abd_imp_med'} == '1') { $chk[]='Other Meds'; }
if($ad{'pad_abd_imp_rest'} == '1') { $chk[]='Rest/Sleep'; }
if($ad{'pad_abd_imp_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_abd_imp_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
$lbl='Modifying Factors: ';
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine($lbl,'Improves: '.$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_wrs_school'} == '1') { $chk[]='School Days'; }
if($ad{'pad_abd_wrs_eat'} == '1') { $chk[]='Eating'; }
if($ad{'pad_abd_wrs_lay'} == '1') { $chk[]='Lying Down'; }
if($ad{'pad_abd_wrs_stress'} == '1') { $chk[]='Stress'; }
if($ad{'pad_abd_wrs_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_abd_wrs_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine($lbl,'Worsens: '.$prnt);
	$lbl='';
}

$prnt=$nt='';
$chk=array();
if($ad{'pad_abd_ass_vomit'} == '1') { $chk[]='Vomiting'; }
if($ad{'pad_abd_ass_blood'} == '1') { $chk[]='Bloody or Bilious'; }
if($ad{'pad_abd_ass_naus'} == '1') { $chk[]='Nausea'; }
if($ad{'pad_abd_ass_reflux'} == '1') { $chk[]='Reflux Symptoms'; }
if($ad{'pad_abd_ass_dia'} == '1') { $chk[]='Diarrhea'; }
if($ad{'pad_abd_ass_const'} == '1') { $chk[]='Constipation'; }
if($ad{'pad_abd_ass_stool'} == '1') { $chk[]='Bloody Stools'; }
if($ad{'pad_abd_ass_loss'} == '1') { $chk[]='Weight Loss'; }
if($ad{'pad_abd_ass_gain'} == '1') { $chk[]='Weight Gain'; }
if($ad{'pad_abd_ass_fev'} == '1') { $chk[]='Fever'; }
if($ad{'pad_abd_ass_pal'} == '1') { $chk[]='Pallor'; }
$nt=trim($ad{'pad_abd_ass_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Abdominal Pain',$chp_printed);
	PrintSingleLine('Associated s/sx',$prnt);
}

if($chp_printed) { CloseChapter(); }

?>
