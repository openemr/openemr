<?php
// For the ROS section, we will do 5 passes on each sub-section to tighten 
// up the print view, first time through is for blank choices with a comment, 
// second time through is to concatonate all the 'no' answers
// and print on one line, next pass is to detail 'no' answers with comments,
// 4th pass is all 'yes' choices, last pass is yes with comment lines.

if($rs{'ee1_rs_const_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Constitutional Symptoms');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_fev_nt'});
	$chk=strtolower($rs{'ee1_rs_fev'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Fever/Chills'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Fever/Chills','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_fev'},$rs{'ee1_rs_fev_nt'},'Fever / Chills','Constitutional Symptoms',$match);
	}
	$nt=trim($rs{'ee1_rs_loss_nt'});
	$chk=strtolower($rs{'ee1_rs_loss'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Weight Loss'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Weight Loss','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_loss'},$rs{'ee1_rs_loss_nt'},'Weight Loss','Constitutional Symptoms',$match);
	}
	$nt=trim($rs{'ee1_rs_gain_nt'});
	$chk=strtolower($rs{'ee1_rs_gain'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Weight Gain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Weight Gain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_gain'},$rs{'ee1_rs_gain_nt'},'Weight Gain','Constitutional Symptoms',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Constitutional Symptoms'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_msk_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Musculoskeletal');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_jnt_nt'});
	$chk=strtolower($rs{'ee1_rs_jnt'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Joint Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Joint Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_jnt'},$rs{'ee1_rs_jnt_nt'},'Joint Pain','Musculoskeletal',$match);
	}
	$nt=trim($rs{'ee1_rs_stiff_nt'});
	$chk=strtolower($rs{'ee1_rs_stiff'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Joint Stiffness'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Joint Stiffness','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_stiff'},$rs{'ee1_rs_stiff_nt'},'Joint Stiffness','Musculoskeletal',$match);
	}
	$nt=trim($rs{'ee1_rs_wk_nt'});
	$chk=strtolower($rs{'ee1_rs_wk'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Muscle Weakness'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Muscle Weakness','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_wk'},$rs{'ee1_rs_wk_nt'},'Muscle Weakness','Musculoskeletal',$match);
	}
	$nt=trim($rs{'ee1_rs_mpain_nt'});
	$chk=strtolower($rs{'ee1_rs_mpain'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Muscle Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Muscle Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_mpain'},$rs{'ee1_rs_mpain_nt'},'Muscle Pain','Musculoskeletal',$match);
	}
	$nt=trim($rs{'ee1_rs_ply_up_nt'});
	$chk=strtolower($rs{'ee1_rs_ply_up'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Polymyalgia Above the Waist'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Polymyalgia Above the Waist','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_ply_up'},$rs{'ee1_rs_ply_up_nt'},'Polymyalgia Above Waist','Musculoskeletal',$match);
	}
	$nt=trim($rs{'ee1_rs_ply_dn_nt'});
	$chk=strtolower($rs{'ee1_rs_ply_dn'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Polymyalgia Below the Waist'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Polymyalgia Below the Waist','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_ply_dn'},$rs{'ee1_rs_ply_dn_nt'},'Polymyalgia Below Waist','Musculoskeletal',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Musculoskeletal'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_skin_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Skin');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_rash_nt'});
	$chk=strtolower($rs{'ee1_rs_rash'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Rash or Sores'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Rash/Sores','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_rash'},$rs{'ee1_rs_rash_nt'},'Rash / Sores','Skin',$match);
	}
	$nt=trim($rs{'ee1_rs_ml_nt'});
	$chk=strtolower($rs{'ee1_rs_ml'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Mole Changes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Mole Changes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_ml'},$rs{'ee1_rs_ml_nt'},'Mole Changes','Skin',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Skin'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_breast_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Breast');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_nip_nt'});
	$chk=strtolower($rs{'ee1_rs_nip'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Nipple Discharge'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Nipple Discharge','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_nip'},$rs{'ee1_rs_nip_nt'},'Nipple Discharge','Breast',$match);
	}
	$nt=trim($rs{'ee1_rs_lmp_nt'});
	$chk=strtolower($rs{'ee1_rs_lmp'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Lumps'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Lumps','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_lmp'},$rs{'ee1_rs_lmp_nt'},'Lumps','Breast',$match);
	}
	$nt=trim($rs{'ee1_rs_skn_nt'});
	$chk=strtolower($rs{'ee1_rs_skn'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Skin Changes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Skin Changes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_skn'},$rs{'ee1_rs_skn_nt'},'Skin Changes','Breast',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Breast'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_neuro_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Neurologic');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_diz_nt'});
	$chk=strtolower($rs{'ee1_rs_diz'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Dizziness'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dizziness','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_diz'},$rs{'ee1_rs_diz_nt'},'Dizziness','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_sz_nt'});
	$chk=strtolower($rs{'ee1_rs_sz'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Seizures'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Seizures','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_sz'},$rs{'ee1_rs_sz_nt'},'Seizures','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_numb_nt'});
	$chk=strtolower($rs{'ee1_rs_numb'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Numbness/Tingling'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Numbness/Tingling','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_numb'},$rs{'ee1_rs_numb_nt'},'Numbness / Tingling','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_head_nt'});
	$chk=strtolower($rs{'ee1_rs_head'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Headaches'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Headaches','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_head'},$rs{'ee1_rs_head_nt'},'Headaches','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_strength_nt'});
	$chk=strtolower($rs{'ee1_rs_strength'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Muscle Strength'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Muscle Strength','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_strength'},$rs{'ee1_rs_strength_nt'},'Muscle Strength','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_tremor_nt'});
	$chk=strtolower($rs{'ee1_rs_tremor'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Tremors'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Tremors','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_tremor'},$rs{'ee1_rs_tremor_nt'},'Tremors','Neurologic',$match);
	}
	$nt=trim($rs{'ee1_rs_dysarthria_nt'});
	$chk=strtolower($rs{'ee1_rs_dysarthria'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Dysarthria'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dysarthria','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dysarthria'},$rs{'ee1_rs_dysarthria_nt'},'Dysarthria','Neurologic',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Neurologic'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_endocrine_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Endocrine');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_hair_nt'});
	$chk=strtolower($rs{'ee1_rs_hair'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hair Loss'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hair Loss','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hair'},$rs{'ee1_rs_hair_nt'},'Hair Loss','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_acne_nt'});
	$chk=strtolower($rs{'ee1_rs_acne'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Acne'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Acne','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_acne'},$rs{'ee1_rs_acne_nt'},'Acne','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_hotcold_nt'});
	$chk=strtolower($rs{'ee1_rs_hotcold'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Heat/Cold Intolerance'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Heat/Cold Intolerance','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hotcold'},$rs{'ee1_rs_hotcold_nt'},'Heat / Cold Intolerance','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_diabetes_nt'});
	$chk=strtolower($rs{'ee1_rs_diabetes'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Diabetes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Diabetes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_diabetes'},$rs{'ee1_rs_diabetes_nt'},'Diabetes','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_thyroid_nt'});
	$chk=strtolower($rs{'ee1_rs_thyroid'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Thyroid Problems'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Thyroid Problems','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_thyroid'},$rs{'ee1_rs_thyroid_nt'},'Thyroid Problems','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_tired_nt'});
	$chk=strtolower($rs{'ee1_rs_tired'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Fatigue'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Fatigue','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_tired'},$rs{'ee1_rs_tired_nt'},'Fatigue','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_voice_nt'});
	$chk=strtolower($rs{'ee1_rs_voice'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Change in Voice'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Change in Voice','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_voice'},$rs{'ee1_rs_voice_nt'},'Change in Voice','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_dysphagia_nt'});
	$chk=strtolower($rs{'ee1_rs_dysphagia'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Dysphagia'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dysphagia','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dysphagia'},$rs{'ee1_rs_dysphagia_nt'},'Dysphagia','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_odyno_nt'});
	$chk=strtolower($rs{'ee1_rs_odyno'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Odynophagia'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Odynophagia','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_odyno'},$rs{'ee1_rs_odyno_nt'},'Odynophagia','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_polyuria_nt'});
	$chk=strtolower($rs{'ee1_rs_polyuria'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Polyuria'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Polyuria','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_polyuria'},$rs{'ee1_rs_polyuria_nt'},'Polyuria','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_polydipsia_nt'});
	$chk=strtolower($rs{'ee1_rs_polydipsia'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Polydipsia'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Polydipsia','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_polydipsia'},$rs{'ee1_rs_polydipsia_nt'},'Polydipsia','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_nightmare_nt'});
	$chk=strtolower($rs{'ee1_rs_nightmare'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Nightmares'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Nightmares','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_nightmare'},$rs{'ee1_rs_nightmare_nt'},'Nightmares','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_nightswt_nt'});
	$chk=strtolower($rs{'ee1_rs_nightswt'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Night Sweats'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Night Sweats','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_nightswt'},$rs{'ee1_rs_nightswt_nt'},'Night Sweats','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_brittle_nt'});
	$chk=strtolower($rs{'ee1_rs_brittle'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Brittle Nails'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Brittle Nails','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_brittle'},$rs{'ee1_rs_brittle_nt'},'Brittle Nails','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_sweat_nt'});
	$chk=strtolower($rs{'ee1_rs_sweat'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Excessive Sweating'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Excessive Sweating','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_sweat'},$rs{'ee1_rs_sweat_nt'},'Excessive Sweating','Endocrine',$match);
	}
	$nt=trim($rs{'ee1_rs_neck_nt'});
	$chk=strtolower($rs{'ee1_rs_neck'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Change in Neck Size'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Change in Neck Size','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_neck'},$rs{'ee1_rs_neck_nt'},'Change in Neck Size','Endocrine',$match);
	}
	if($pat_sex == 'f') {
		$nt=trim($rs{'ee1_rs_menses_nt'});
		$chk=strtolower($rs{'ee1_rs_menses'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Irregular Menses'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Irregular Menses','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_menses'},$rs{'ee1_rs_menses_nt'},'Irregular Menses','Endocrine',$match);
		}
	}
	$nt=trim($rs{'ee1_rs_hirs_nt'});
	$chk=strtolower($rs{'ee1_rs_hirs'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hirsutism'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hursutism','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hirs'},$rs{'ee1_rs_hirs_nt'},'Hirsutism','Endocrine',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Endocrine'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_gastro_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Gastrointestinal');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_naus_nt'});
	$chk=strtolower($rs{'ee1_rs_naus'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Nausea'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Nausea','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_naus'},$rs{'ee1_rs_naus_nt'},'Nausea','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_vomit_nt'});
	$chk=strtolower($rs{'ee1_rs_vomit'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Vomiting'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Vomiting','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_vomit'},$rs{'ee1_rs_vomit_nt'},'Vomiting','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_ref_nt'});
	$chk=strtolower($rs{'ee1_rs_ref'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Reflux'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Reflux','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_ref'},$rs{'ee1_rs_ref_nt'},'Reflux','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_anal_p_nt'});
	$chk=strtolower($rs{'ee1_rs_anal_p'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Anal Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Anal Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_anal_p'},$rs{'ee1_rs_anal_p_nt'},'Anal Pain','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_jaun_nt'});
	$chk=strtolower($rs{'ee1_rs_jaun'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Jaundice'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Jaundice','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_jaun'},$rs{'ee1_rs_jaun_nt'},'Jaundice','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_bow_nt'});
	$chk=strtolower($rs{'ee1_rs_bow'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Change in Bowel Habits'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Change in Bowel Habits','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_bow'},$rs{'ee1_rs_bow_nt'},'Change in Bowel Habits','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_dia_nt'});
	$chk=strtolower($rs{'ee1_rs_dia'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Diarrhea'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Diarrhea','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dia'},$rs{'ee1_rs_dia_nt'},'Diarrhea','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_const_nt'});
	$chk=strtolower($rs{'ee1_rs_const'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Constipation'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Constipation','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_const'},$rs{'ee1_rs_const_nt'},'Constipation','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_melena_nt'});
	$chk=strtolower($rs{'ee1_rs_melena'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Melena'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Melena','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_melena'},$rs{'ee1_rs_melena_nt'},'Melena','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_hematemesis_nt'});
	$chk=strtolower($rs{'ee1_rs_hematemesis'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hematemesis'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hematemesis','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hematemesis'},$rs{'ee1_rs_hematemesis_nt'},'Hematemesis','Gastrointestinal',$match);
	}
	$nt=trim($rs{'ee1_rs_hematochezia_nt'});
	$chk=strtolower($rs{'ee1_rs_hematochezia'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hematochezia'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hematochezia','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hematochezia'},$rs{'ee1_rs_hematochezia_nt'},'Hematochezia','Gastrointestinal',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Gastrointestinal'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_cardio_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Cardiovascular');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_cpain_nt'});
	$chk=strtolower($rs{'ee1_rs_cpain'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Chest Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Chest Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_cpain'},$rs{'ee1_rs_cpain_nt'},'Chest Pain','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_breathe_nt'});
	$chk=strtolower($rs{'ee1_rs_breathe'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Difficulty Breathing'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Difficulty Breathing','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_breathe'},$rs{'ee1_rs_breathe_nt'},'Difficulty Breathing','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_swell_nt'});
	$chk=strtolower($rs{'ee1_rs_swell'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Swelling'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Swelling','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_swell'},$rs{'ee1_rs_swell_nt'},'Swelling','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_palp_nt'});
	$chk=strtolower($rs{'ee1_rs_palp'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Palpitations'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Palpitations','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_palp'},$rs{'ee1_rs_palp_nt'},'Palpitations','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_jaw_nt'});
	$chk=strtolower($rs{'ee1_rs_jaw'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Jaw Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Jaw Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_jaw'},$rs{'ee1_rs_jaw_nt'},'Jaw Pain','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_arm_nt'});
	$chk=strtolower($rs{'ee1_rs_arm'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Arm Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Arm Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_arm'},$rs{'ee1_rs_arm_nt'},'Arm Pain','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_back_nt'});
	$chk=strtolower($rs{'ee1_rs_back'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Back Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Back Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_back'},$rs{'ee1_rs_back_nt'},'Back Pain','Cardiovascular',$match);
	}
	$nt=trim($rs{'ee1_rs_acute_nt'});
	$chk=strtolower($rs{'ee1_rs_acute'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Acute Nitroglycerin Use'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Acute Nitroglycerin Use','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_acute'},$rs{'ee1_rs_acute_nt'},'Acute Nitroglycerin Use','Cardiovascular',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Cardiovascular'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_imm_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Allergic/Immunologic');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_hay_nt'});
	$chk=strtolower($rs{'ee1_rs_hay'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hay Fever'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hay Fever','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hay'},$rs{'ee1_rs_hay_nt'},'Hay Fever','Allergic/Immunologic',$match);
	}
	$nt=trim($rs{'ee1_rs_med_nt'});
	$chk=strtolower($rs{'ee1_rs_med'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Medications'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Medications','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_med'},$rs{'ee1_rs_med_nt'},'Medications','Allergic/Immunologic',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Allergic/Immunologic'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_resp_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Respiratory');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_whz_nt'});
	$chk=strtolower($rs{'ee1_rs_whz'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Wheezing'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Wheezing','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_whz'},$rs{'ee1_rs_whz_nt'},'Wheezing','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_shrt_nt'});
	$chk=strtolower($rs{'ee1_rs_shrt'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Shortness of Breath'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Shortness of Breath','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_shrt'},$rs{'ee1_rs_shrt_nt'},'Shortness of Breath','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_cgh_nt'});
	$chk=strtolower($rs{'ee1_rs_cgh'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Cough'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Cough','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_cgh'},$rs{'ee1_rs_cgh_nt'},'Cough','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_slp_nt'});
	$chk=strtolower($rs{'ee1_rs_slp'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Sleep Apnea'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Sleep Apnea','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_slp'},$rs{'ee1_rs_slp_nt'},'Sleep Apnea','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_spu_nt'});
	$chk=strtolower($rs{'ee1_rs_spu'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Sputum'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Sputum','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_spu'},$rs{'ee1_rs_spu_nt'},'Sputum','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_dys_nt'});
	$chk=strtolower($rs{'ee1_rs_dys'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Dyspnea on Exertion'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dyspnea on Exertion','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dys'},$rs{'ee1_rs_dys_nt'},'Dyspnea on Exertion','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_hemoptysis_nt'});
	$chk=strtolower($rs{'ee1_rs_hemoptysis'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hemoptysis'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hemoptysis','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hemoptysis'},$rs{'ee1_rs_hemoptysis_nt'},'Hemoptysis','Respiratory',$match);
	}
	$nt=trim($rs{'ee1_rs_snore_nt'});
	$chk=strtolower($rs{'ee1_rs_snore'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Snoring'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Snoring','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_snore'},$rs{'ee1_rs_snore_nt'},'Snoring','Respiratory',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Respiratory'); }
	$cnt++;
}
$hdr_printed=false;
if($rs{'ee1_rs_eyes_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Eyes');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_blr_nt'});
	$chk=strtolower($rs{'ee1_rs_blr'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Blurred Vision'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Blurred Vision','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_blr'},$rs{'ee1_rs_blr_nt'},'Blurred Vision','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_dbl_nt'});
	$chk=strtolower($rs{'ee1_rs_dbl'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Double Vision'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Double Vision','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dbl'},$rs{'ee1_rs_dbl_nt'},'Double Vision','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_vis_nt'});
	$chk=strtolower($rs{'ee1_rs_vis'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Vision Changes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Vision Changes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_vis'},$rs{'ee1_rs_vis_nt'},'Vision Changes','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_vloss_nt'});
	$chk=strtolower($rs{'ee1_rs_vloss'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Loss of Vision'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Loss of Vision','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_vloss'},$rs{'ee1_rs_vloss_nt'},'Loss of Vision','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_blind_nt'});
	$chk=strtolower($rs{'ee1_rs_blind'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'Not Blind'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Blind','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_blind'},$rs{'ee1_rs_blind_nt'},'Blind','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_mac_nt'});
	$chk=strtolower($rs{'ee1_rs_mac'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Macular Degeneration'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Macular Degeneration','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_mac'},$rs{'ee1_rs_mac_nt'},'Macular Degeneration','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_vpain_nt'});
	$chk=strtolower($rs{'ee1_rs_vpain'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_vpain'},$rs{'ee1_rs_vpain_nt'},'Pain','Eyes',$match);
	}
	$nt=trim($rs{'ee1_rs_dry_nt'});
	$chk=strtolower($rs{'ee1_rs_dry'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'Not Dry'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dry','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dry'},$rs{'ee1_rs_dry_nt'},'Dry','Eyes',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Eyes'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_ent_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Ear/Nose/Throat/Mouth');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_sore_nt'});
	$chk=strtolower($rs{'ee1_rs_sore'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Sore Throat'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Sore Throat','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_sore'},$rs{'ee1_rs_sore_nt'},'Sore Throat','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_sin_nt'});
	$chk=strtolower($rs{'ee1_rs_sin'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Sinus Problems'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Sinus Problems','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_sin'},$rs{'ee1_rs_sin_nt'},'Sinus Problems','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_hear_nt'});
	$chk=strtolower($rs{'ee1_rs_hear'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hearing Problems'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hearing Problems','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hear'},$rs{'ee1_rs_hear_nt'},'Hearing Problems','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_tin_nt'});
	$chk=strtolower($rs{'ee1_rs_tin'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Tinnitus'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Tinnitus','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_tin'},$rs{'ee1_rs_tin_nt'},'Tinnitus','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_hot_nt'});
	$chk=strtolower($rs{'ee1_rs_hot'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hot Flashes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hot Flashes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hot'},$rs{'ee1_rs_hot_nt'},'Hot Flashes','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_lymph_nt'});
	$chk=strtolower($rs{'ee1_rs_lymph'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Swollen Lymph Nodes'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Swollen Lymph Nodes','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_lymph'},$rs{'ee1_rs_lymph_nt'},'Swollen Lymph Nodes','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_mass_nt'});
	$chk=strtolower($rs{'ee1_rs_mass'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Mass'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Mass','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_mass'},$rs{'ee1_rs_mass_nt'},'Mass','Ear/Nose/Throat/Mouth',$match);
	}
	$nt=trim($rs{'ee1_rs_epain_nt'});
	$chk=strtolower($rs{'ee1_rs_epain'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Ear Pain'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Ear Pain','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_epain'},$rs{'ee1_rs_epain_nt'},'Ear Pain','Ear/Nose/Throat/Mouth',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Ear/Nose/Throat/Mouth'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_lymph_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Lymphatic');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_swl_nt'});
	$chk=strtolower($rs{'ee1_rs_swl'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Swollen Glands'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Swollen Glands','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_swl'},$rs{'ee1_rs_swl_nt'},'Swollen Glands','Lymphatic',$match);
	}
	$nt=trim($rs{'ee1_rs_brse_nt'});
	$chk=strtolower($rs{'ee1_rs_brse'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Frequent Bruising'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Frequent Bruising','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_brse'},$rs{'ee1_rs_brse_nt'},'Frequent Bruising','Lymphatic',$match);
	}
	$nt=trim($rs{'ee1_rs_nose_nt'});
	$chk=strtolower($rs{'ee1_rs_nose'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Chronic Nose Bleeds'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Chronic Nose Bleeds','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_nose'},$rs{'ee1_rs_nose_nt'},'Chronic Nose Bleeds','Lymphatic',$match);
	}
	$nt=trim($rs{'ee1_rs_trait_nt'});
	$chk=strtolower($rs{'ee1_rs_trait'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Sickle Cell/Trait'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Sickle Cell/Trait','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_trait'},$rs{'ee1_rs_trait_nt'},'Sickle Cell/Trait','Lymphatic',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Lymphatic'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_psych_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Psychiatric');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_dep_nt'});
	$chk=strtolower($rs{'ee1_rs_dep'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Depressed Mood/Crying'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Depressed Mood/Crying','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_dep'},$rs{'ee1_rs_dep_nt'},'Depressed Mood/Crying','Psychiatric',$match);
	}
	$nt=trim($rs{'ee1_rs_anx_nt'});
	$chk=strtolower($rs{'ee1_rs_anx'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Anxiety'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Anxiety','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_anx'},$rs{'ee1_rs_anx_nt'},'Anxiety','Psychiatric',$match);
	}
	$nt=trim($rs{'ee1_rs_sui_nt'});
	$chk=strtolower($rs{'ee1_rs_sui'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Thoughts of Suicide'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Thoughts of Suicide','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_sui'},$rs{'ee1_rs_sui_nt'},'Thougts of Suicide','Psychiatric',$match);
	}
	$nt=trim($rs{'ee1_rs_hom_nt'});
	$chk=strtolower($rs{'ee1_rs_hom'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Thoughts of Homicide'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Thoughts of Homicide','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hom'},$rs{'ee1_rs_hom_nt'},'Thougts of Homicide','Psychiatric',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Psychiatric'); }
	$cnt++;
}

$hdr_printed=false;
if($rs{'ee1_rs_gen_hpi'} == '1') {
	EE1_PrintROS_RefertoHPI('Genitourinary');
}
$cnt=0;
while($cnt < 5) {
	$prnt=$nt=$chk='';
	$match='';
	if($cnt == 1 || $cnt == 3) { $match='No Print'; }
	if($cnt == 2) { $match='n'; }
	if($cnt == 4) { $match='y'; }
	$nt=trim($rs{'ee1_rs_leak_nt'});
	$chk=strtolower($rs{'ee1_rs_leak'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Incontinence'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Incontinence','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_leak'},$rs{'ee1_rs_leak_nt'},'Incontinence','Genitourinary',$match);
	}
	$nt=trim($rs{'ee1_rs_ret_nt'});
	$chk=strtolower($rs{'ee1_rs_ret'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Urine Retention'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Urine Retention','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_ret'},$rs{'ee1_rs_ret_nt'},'Urine Retention','Genitourinary',$match);
	}
	if($pat_sex == 'f') {
		$nt=trim($rs{'ee1_rs_vag_nt'});
		$chk=strtolower($rs{'ee1_rs_vag'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Vaginal Discharge'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Vaginal Discharge','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_vag'},$rs{'ee1_rs_vag_nt'},'Vaginal Discharge','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_bleed_nt'});
		$chk=strtolower($rs{'ee1_rs_bleed'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Abnormal Bleeding'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Abnormal Bleeding','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_bleed'},$rs{'ee1_rs_bleed_nt'},'Abnormal Bleeding','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_pp_nt'});
		$chk=strtolower($rs{'ee1_rs_pp'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Painful Periods'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Painful Periods','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_pp'},$rs{'ee1_rs_pp_nt'},'Painful Periods','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_sex_nt'});
		$chk=strtolower($rs{'ee1_rs_sex'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Painful Intercourse'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Painful Intercourse','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_sex'},$rs{'ee1_rs_sex_nt'},'Painful Intercourse','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_fib_nt'});
		$chk=strtolower($rs{'ee1_rs_fib'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Fibroids'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Fibroids','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_fib'},$rs{'ee1_rs_fib_nt'},'Fibroids','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_inf_nt'});
		$chk=strtolower($rs{'ee1_rs_inf'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Infertility'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Infertility','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_inf'},$rs{'ee1_rs_inf_nt'},'Infertility','Genitourinary',$match);
		}
	}
	$nt=trim($rs{'ee1_rs_urg_nt'});
	$chk=strtolower($rs{'ee1_rs_urg'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Urgency'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Urgency','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_urg'},$rs{'ee1_rs_urg_nt'},'Urgency','Genitourinary',$match);
	}
	$nt=trim($rs{'ee1_rs_hematuria_nt'});
	$chk=strtolower($rs{'ee1_rs_hematuria'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Hematuria'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Hematuria','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_hematuria'},$rs{'ee1_rs_hematuria_nt'},'Hematuria','Genitourinary',$match);
	}
	$nt=trim($rs{'ee1_rs_nocturia_nt'});
	$chk=strtolower($rs{'ee1_rs_nocturia'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Nocturia'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Nocturia','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_nocturia'},$rs{'ee1_rs_nocturia_nt'},'Nocturia','Genitourinary',$match);
	}
	if($pat_sex == 'f') {
		$nt=trim($rs{'ee1_rs_low_nt'});
		$chk=strtolower($rs{'ee1_rs_low'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Lack of Sexual Desire'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Low Sexual Desire','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_low'},$rs{'ee1_rs_low_nt'},'Low Sexual Desire','Genitourinary',$match);
		}
	}
	if($pat_sex == 'm') {
		$nt=trim($rs{'ee1_rs_ed_nt'});
		$chk=strtolower($rs{'ee1_rs_ed'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Erectile Dysfunction'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Erectile Dysfunction','Patient Confirms: '); }
		}	 else {
			EE1_PrintROS($rs{'ee1_rs_ed'},$rs{'ee1_rs_ed_nt'},'Erectile Dysfunction','Genitourinary',$match);
		}
		$nt=trim($rs{'ee1_rs_libido_nt'});
		$chk=strtolower($rs{'ee1_rs_libido'});
		if(empty($nt)) { 
			if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Decrease in Libido'); }
			if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Decreased Libido','Patient Confirms: '); }
		} else {
			EE1_PrintROS($rs{'ee1_rs_libido'},$rs{'ee1_rs_libido_nt'},'Decreased Libido','Genitourinary',$match);
		}
	}
	$nt=trim($rs{'ee1_rs_weaks_nt'});
	$chk=strtolower($rs{'ee1_rs_weaks'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Weak Stream'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Weak Stream','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_weaks'},$rs{'ee1_rs_weaks_nt'},'Weak Stream','Genitourinary',$match);
	}
	$nt=trim($rs{'ee1_rs_drib_nt'});
	$chk=strtolower($rs{'ee1_rs_drib'});
	if(empty($nt)) { 
		if($cnt == 1 && ($chk == 'n')) { $prnt=EE1_AppendItem($prnt,'No Dribbling'); }
		if($cnt == 3 && ($chk == 'y')) { $prnt=EE1_AppendItem($prnt,'Dribbling','Patient Confirms: '); }
	} else {
		EE1_PrintROS($rs{'ee1_rs_drib'},$rs{'ee1_rs_drib_nt'},'Dribbling','Genitourinary',$match);
	}
	if(!empty($prnt)) { EE1_PrintCompoundROS($prnt,'Genitourinary'); }
	$cnt++;
}

$nt=trim($rs{'ee1_rs_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel'>Other Notes:</td>\n";
	echo "		</tr>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnIndentText' colspan='3'>$nt</td>\n";
	echo "		</tr>\n";
}
?>
