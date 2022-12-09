<?php 
$gyn = sqlQuery('SELECT * FROM `form_gyn_exam` WHERE `link_form` = ? AND ' .
	'`link_id` = ?', array($frmn, $id));
if(!isset($gyn{'id'})) {
	$flds = sqlListFields('form_gyn_exam');
	foreach($flds as $fld) {
		$gyn[$fld] = '';
	}
}

// First just create a line of all items that are WNL
$chp_printed=false;
$prnt='';
$nt=trim($gyn['gyn_ext_comm']);
if($gyn['gyn_ext_wnl'] ==  '1' && $nt == '') { 
	if($prnt != '')  $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Ext. Gen: </span><span class='wmtPrnBody'>WNL";
}
$nt=trim($gyn['gyn_mea_comm']);
if($gyn['gyn_mea_wnl'] ==  '1' && $nt == '') { 
	if($prnt != '')  $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Urethral Meatus: </span><span class='wmtPrnBody'>WNL";
}
$nt=trim($gyn['gyn_ure_comm']);
if($gyn['gyn_ure_wnl'] ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Urethra: </span><span class='wmtPrnBody'>WNL";
}
$nt=trim($gyn['gyn_blad_comm']);
if($gyn['gyn_blad_wnl'] ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Bladder: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($gyn['gyn_vag_abn'] == 1) $nt='no';
if($gyn['gyn_vag_abn'] == 1) $nt='no';
if($gyn['gyn_vag_dc'] == 1) $nt='no';
if($gyn['gyn_vag_atro'] == 1) $nt='no';
if($gyn['gyn_vag_atro_type']) $nt='no';
if($gyn['gyn_vag_cys'] == 1) $nt='no';
if($gyn['gyn_vag_cys_type']) $nt='no';
if($gyn['gyn_vag_rec'] == 1) $nt='no';
if($gyn['gyn_vag_rec_type']) $nt='no';
if($gyn['gyn_vag_nt']) $nt='no';
if($gyn['gyn_vag_wnl'] ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Vagina: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($gyn['gyn_cer_abs'] == 1) $nt='no';
if($gyn['gyn_cer_fri'] == 1) $nt='no';
if($gyn['gyn_cer_ant'] == 1) $nt='no';
if($gyn['gyn_cer_polyp'] == 1) $nt='no';
if($gyn['gyn_cer_iud'] == 1) $nt='no';
if($gyn['gyn_cer_nt']) $nt='no';
if($gyn{'gyn_cer_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Cervix: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($gyn['gyn_ut_abs'] == 1) $nt='no';
if($gyn['gyn_ut_retro'] == 1) $nt='no';
if($gyn['gyn_ut_tender'] == 1) $nt='no';
if($gyn['gyn_ut_size']) $nt='no';
if($gyn['gyn_ut_pro'] == 1) $nt='no';
if($gyn['gyn_ut_nt']) $nt='no';
if($gyn{'gyn_ut_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Uterus: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($gyn['gyn_ad_absent'] == 1) $nt='no';
if($gyn['gyn_ad_tender'] == 1) $nt='no';
if($gyn['gyn_ad_enl'] == 1) $nt='no';
if($gyn['gyn_ad_firm'] == 1) $nt='no';
if($gyn['gyn_ad_mass'] == 1) $nt='no';
if($gyn['gyn_ad_nt']) $nt='no';
if($gyn{'gyn_ad_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Adnexa: </span><span class='wmtPrnBody'>WNL";
}
$nt=trim($gyn{'gyn_rec_comm'});
if($gyn{'gyn_rec_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Rectal: </span><span class='wmtPrnBody'>WNL";
}
$nt=trim($gyn{'gyn_an_comm'});
if($gyn{'gyn_an_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Anus/Perineum: </span><span class='wmtPrnBody'>WNL";
}
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
	$prnt .= '</span>';
	echo "<tr>\n";
	echo "	<td colspan='2'>$prnt</td>\n";
	echo "</tr>\n";
}

$nt=$chk=$prnt='';
if($gyn{'gyn_ext_wnl'} ==  '1') $prnt='WNL';
if($gyn{'gyn_ext_abn'} ==  '1') $prnt='Abnormal';
$nt=trim($gyn{'gyn_ext_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Ext. Gen:',$prnt);
	}
}

$prnt=$nt='';
if($gyn{'gyn_mea_wnl'} ==  '1') $prnt='WNL';
if($gyn{'gyn_mea_abn'} ==  '1') $prnt='Abnormal';
$nt=trim($gyn{'gyn_mea_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Urethral Meatus:',$prnt);
	}
}

$prnt=$nt='';
if($gyn{'gyn_ure_wnl'} ==  '1') { $prnt='WNL'; }
if($gyn{'gyn_ure_abn'} ==  '1') { $prnt='Abnormal'; }
$nt=trim($gyn{'gyn_ure_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Urethral:',$prnt);
	}
}

$prnt=$nt='';
if($gyn{'gyn_blad_wnl'} ==  '1') { $prnt='WNL'; }
if($gyn{'gyn_blad_abn'} ==  '1') { $prnt='Abnormal'; }
$nt=trim($gyn{'gyn_blad_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Bladder:',$prnt);
	}
}

$prnt=$nt='';
$chk=array();
if($gyn{'gyn_vag_wnl'} ==  '1') { $chk[]='WNL'; }
if($gyn{'gyn_vag_abn'} ==  '1') { $chk[]='Abnormal'; }
if($gyn{'gyn_vag_dc'} ==  '1') { $chk[]='D/C'; }
if($gyn{'gyn_vag_atro'} ==  '1') { $chk[]='Atrophic'; }
$nt=ListLook($gyn{'gyn_vag_atro_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
if($gyn{'gyn_vag_cys'} ==  '1') { $chk[]='Cystocele'; }
$nt=ListLook($gyn{'gyn_vag_cys_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
if($gyn{'gyn_vag_rec'} ==  '1') { $chk[]='Rectocele'; }
$nt=ListLook($gyn{'gyn_vag_rec_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
$nt = trim($gyn{'gyn_vag_nt'});
$prnt=BuildPrintList($chk);
if(($prnt != 'WNL' && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Vagina:',$prnt);
		PrintSingleLine('',$nt);
	} else {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Vagina:',$nt);
	}
}

$prnt=$nt='';
$chk=array();
if($gyn{'gyn_cer_wnl'} ==  '1') { $chk[]='WNL'; }
if($gyn{'gyn_cer_abs'} ==  '1') { $chk[]='Absent'; }
if($gyn{'gyn_cer_fri'} ==  '1') { $chk[]='Friable'; }
if($gyn{'gyn_cer_ant'} ==  '1') { $chk[]='Anteverted'; }
if($gyn{'gyn_cer_polyp'} ==  '1') { $chk[]='Polyp'; }
if($gyn{'gyn_cer_iud'} ==  '1') { $chk[]='IuD String'; }
$nt = trim($gyn{'gyn_cer_nt'});
$prnt=BuildPrintList($chk);
if(($prnt != 'WNL' && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Cervix:',$prnt);
		PrintSingleLine('',$nt);
	} else {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Cervix:',$nt);
	}
}

$prnt=$nt='';
$chk=array();
if($gyn{'gyn_ut_wnl'} ==  '1') { $chk[]='WNL'; }
if($gyn{'gyn_ut_abs'} ==  '1') { $chk[]='Absent'; }
if($gyn{'gyn_ut_retro'} ==  '1') { $chk[]='Retroflexed'; }
if($gyn{'gyn_ut_tender'} ==  '1') { $chk[]='Tender'; }
if($gyn{'gyn_ut_size'} !=  '') { $chk[]='Size:&nbsp;'.$gyn{'gyn_ut_size'}.'&nbsp;Wks'; }
if($gyn{'gyn_ut_pro'} ==  '1') { $chk[]='Prolapsed'; }
$nt = trim($gyn{'gyn_ut_nt'});
$prnt=BuildPrintList($chk);
if(($prnt != 'WNL' && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Uterus:',$prnt);
		PrintSingleLine('',$nt);
	} else {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Uterus:',$nt);
	}
}

$prnt=$nt='';
$chk=array();
if($gyn{'gyn_ad_wnl'} ==  '1') { $chk[]='WNL'; }
if($gyn{'gyn_ad_absent'} ==  '1') { $chk[]='Absent'; }
if($gyn{'gyn_ad_tender'} ==  '1') { $chk[]='Tender'; }
if($gyn{'gyn_ad_enl'} ==  '1') { $chk[]='Enlarged'; }
if($gyn{'gyn_ad_firm'} ==  '1') { $chk[]='Firm'; }
if($gyn{'gyn_ad_mass'} ==  '1') { $chk[]='Mass'; }
$nt = trim($gyn{'gyn_ad_nt'});
$prnt=BuildPrintList($chk);
if(($prnt != 'WNL' && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Adnexa:',$prnt);
		PrintSingleLine('',$nt);
	} else {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Adnexa:',$nt);
	}
}

$nt=$chk=$prnt='';
if($gyn{'gyn_rec_wnl'} ==  '1') { $prnt='WNL'; }
$nt=trim($gyn{'gyn_rec_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Rectal:',$prnt);
	}
}

$nt=$chk=$prnt='';
if($gyn{'gyn_an_wnl'} ==  '1') { $prnt='WNL'; }
$nt=trim($gyn{'gyn_an_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Anus/Perineum:',$prnt);
	}
}

$nt=trim($gyn{'gyn_comment'});
if(!empty($nt)) {
	$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
	PrintSingleLine('Other Findings:',$nt);
}
if($chp_printed) CloseChapter();
?>

