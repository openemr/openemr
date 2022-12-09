<?php 
$flds = sqlListFields('form_gyn_exam');
$flds = array_slice($flds,7);
foreach($flds as $key => $fld) { 
	$gyn[$fld] = '';
	$dt[$fld] = '';
}
if(!isset($chp_title)) $chp_title = 'Gynecological Examination';
$fres = sqlQuery("SELECT * FROM form_gyn_exam WHERE link_id=?".
	" AND link_form=?", array($id, $frmdir));
foreach($fres as $fld => $val) {
	if(substr($fld,0,4) != 'gyn_') continue;
	$dt[$fld] = $val;
}

// First just create a line of all items that are WNL
$chp_printed = false;
$prnt = '';
$nt = trim($dt['gyn_ext_comm']);
if($dt['gyn_ext_wnl'] == '1' && $nt == '') { 
	if($prnt != '')  $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Ext. Gen: </span><span class='wmtPrnBody'>WNL";
}
$nt = trim($dt['gyn_mea_comm']);
if($dt['gyn_mea_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Urethral Meatus: </span><span class='wmtPrnBody'>WNL";
}
$nt = trim($dt['gyn_ure_comm']);
if($dt['gyn_ure_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Urethra: </span><span class='wmtPrnBody'>WNL";
}
$nt = trim($dt['gyn_blad_comm']);
if($dt['gyn_blad_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Bladder: </span><span class='wmtPrnBody'>WNL";
}
$nt = '';
if($dt['gyn_vag_abn'] == 1) $nt = 'no';
if($dt['gyn_vag_abn'] == 1) $nt = 'no';
if($dt['gyn_vag_dc'] == 1) $nt = 'no';
if($dt['gyn_vag_atro'] == 1) $nt = 'no';
if($dt['gyn_vag_atro_type']) $nt = 'no';
if($dt['gyn_vag_cys'] == 1) $nt = 'no';
if($dt['gyn_vag_cys_type']) $nt = 'no';
if($dt['gyn_vag_rec'] == 1) $nt = 'no';
if($dt['gyn_vag_rec_type']) $nt = 'no';
if($dt['gyn_vag_nt']) $nt = 'no';
if($dt['gyn_vag_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Vagina: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($dt['gyn_cer_abs'] == 1) $nt='no';
if($dt['gyn_cer_fri'] == 1) $nt='no';
if($dt['gyn_cer_ant'] == 1) $nt='no';
if($dt['gyn_cer_polyp'] == 1) $nt='no';
if($dt['gyn_cer_iud'] == 1) $nt='no';
if($dt['gyn_cer_nt']) $nt='no';
if($dt{'gyn_cer_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Cervix: </span><span class='wmtPrnBody'>WNL";
}
$nt='';
if($dt['gyn_ut_abs'] == 1) $nt='no';
if($dt['gyn_ut_retro'] == 1) $nt='no';
if($dt['gyn_ut_tender'] == 1) $nt='no';
if($dt['gyn_ut_size']) $nt='no';
if($dt['gyn_ut_pro'] == 1) $nt='no';
if($dt['gyn_ut_nt']) $nt='no';
if($dt{'gyn_ut_wnl'} ==  '1' && $nt == '') { 
	if($prnt != '') $prnt.=', </span>';
	$prnt.="<span class='wmtPrnLabel'>Uterus: </span><span class='wmtPrnBody'>WNL";
}
$nt = '';
if($dt['gyn_ad_absent'] == 1) $nt = 'no';
if($dt['gyn_ad_tender'] == 1) $nt = 'no';
if($dt['gyn_ad_enl'] == 1) $nt = 'no';
if($dt['gyn_ad_firm'] == 1) $nt = 'no';
if($dt['gyn_ad_mass'] == 1) $nt = 'no';
if($dt['gyn_ad_nt']) $nt = 'no';
if($dt{'gyn_ad_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Adnexa: </span><span class='wmtPrnBody'>WNL";
}
$nt = trim($dt{'gyn_rec_comm'});
if($dt{'gyn_rec_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Rectal: </span><span class='wmtPrnBody'>WNL";
}
$nt = trim($dt{'gyn_an_comm'});
if($dt{'gyn_an_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= "<span class='wmtPrnLabel'>Anus/Perineum: </span><span class='wmtPrnBody'>WNL";
}
if(!empty($prnt)) {
	$chp_printed = PrintChapter($chp_title,$chp_printed);
	$prnt .= '</span>';
	echo "<tr>\n";
	echo "	<td colspan='2'>$prnt</td>\n";
	echo "</tr>\n";
}

$nt=$chk=$prnt='';
if($dt{'gyn_ext_wnl'} ==  '1') $prnt='WNL';
if($dt{'gyn_ext_abn'} ==  '1') $prnt='Abnormal';
$nt=trim($dt{'gyn_ext_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Ext. Gen:',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_mea_wnl'} ==  '1') $prnt='WNL';
if($dt{'gyn_mea_abn'} ==  '1') $prnt='Abnormal';
$nt=trim($dt{'gyn_mea_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Urethral Meatus:',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_ure_wnl'} ==  '1') { $prnt='WNL'; }
if($dt{'gyn_ure_abn'} ==  '1') { $prnt='Abnormal'; }
$nt=trim($dt{'gyn_ure_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Urethral:',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_blad_wnl'} ==  '1') { $prnt='WNL'; }
if($dt{'gyn_blad_abn'} ==  '1') { $prnt='Abnormal'; }
$nt=trim($dt{'gyn_blad_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Bladder:',$prnt);
	}
}

$prnt=$nt='';
$chk=array();
if($dt{'gyn_vag_wnl'} ==  '1') { $chk[]='WNL'; }
if($dt{'gyn_vag_abn'} ==  '1') { $chk[]='Abnormal'; }
if($dt{'gyn_vag_dc'} ==  '1') { $chk[]='D/C'; }
if($dt{'gyn_vag_atro'} ==  '1') { $chk[]='Atrophic'; }
$nt=ListLook($dt{'gyn_vag_atro_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
if($dt{'gyn_vag_cys'} ==  '1') { $chk[]='Cystocele'; }
$nt=ListLook($dt{'gyn_vag_cys_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
if($dt{'gyn_vag_rec'} ==  '1') { $chk[]='Rectocele'; }
$nt=ListLook($dt{'gyn_vag_rec_type'},'WHC_1_to_3');
if($nt) { $chk[]='Grade: '.$nt; }
$nt = trim($dt{'gyn_vag_nt'});
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
if($dt{'gyn_cer_wnl'} ==  '1') { $chk[]='WNL'; }
if($dt{'gyn_cer_abs'} ==  '1') { $chk[]='Absent'; }
if($dt{'gyn_cer_fri'} ==  '1') { $chk[]='Friable'; }
if($dt{'gyn_cer_ant'} ==  '1') { $chk[]='Anteverted'; }
if($dt{'gyn_cer_polyp'} ==  '1') { $chk[]='Polyp'; }
if($dt{'gyn_cer_iud'} ==  '1') { $chk[]='IuD String'; }
$nt = trim($dt{'gyn_cer_nt'});
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
if($dt{'gyn_ut_wnl'} ==  '1') { $chk[]='WNL'; }
if($dt{'gyn_ut_abs'} ==  '1') { $chk[]='Absent'; }
if($dt{'gyn_ut_retro'} ==  '1') { $chk[]='Retroflexed'; }
if($dt{'gyn_ut_tender'} ==  '1') { $chk[]='Tender'; }
if($dt{'gyn_ut_size'} !=  '') { $chk[]='Size:&nbsp;'.$dt{'gyn_ut_size'}.'&nbsp;Wks'; }
if($dt{'gyn_ut_pro'} ==  '1') { $chk[]='Prolapsed'; }
$nt = trim($dt{'gyn_ut_nt'});
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
if($dt{'gyn_ad_wnl'} ==  '1') { $chk[]='WNL'; }
if($dt{'gyn_ad_absent'} ==  '1') { $chk[]='Absent'; }
if($dt{'gyn_ad_tender'} ==  '1') { $chk[]='Tender'; }
if($dt{'gyn_ad_enl'} ==  '1') { $chk[]='Enlarged'; }
if($dt{'gyn_ad_firm'} ==  '1') { $chk[]='Firm'; }
if($dt{'gyn_ad_mass'} ==  '1') { $chk[]='Mass'; }
$nt = trim($dt{'gyn_ad_nt'});
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
if($dt{'gyn_rec_wnl'} ==  '1') { $prnt='WNL'; }
$nt=trim($dt{'gyn_rec_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Rectal:',$prnt);
	}
}

$nt=$chk=$prnt='';
if($dt{'gyn_an_wnl'} ==  '1') { $prnt='WNL'; }
$nt=trim($dt{'gyn_an_comm'});
if($nt != '' || $prnt != 'WNL') {
	if(!empty($nt)) { $prnt=AppendItem($prnt,'Other:&nbsp;'.$nt); }
	if(!empty($prnt)) {
		$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
		PrintSingleLine('Anus/Perineum:',$prnt);
	}
}

$nt=trim($dt{'gyn_comment'});
if(!empty($nt)) {
	$chp_printed=PrintChapter('Gynecological Examination',$chp_printed);
	PrintSingleLine('Other Findings:',$nt);
}
?>

