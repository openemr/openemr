<?php 
$flds = sqlListFields('form_gyn_exam');
$flds = array_slice($flds,7);
foreach($flds as $key => $fld) { 
	$gyn[$fld] = '';
	$dt[$fld] = '';
}
if(!isset($chp_title)) $chp_title = 'Gynecological Examination';
$fres = sqlQuery('SELECT * FROM form_gyn_exam WHERE link_id = ?'.
	' AND link_form = ?', array($id, $frmdir));
foreach($fres as $fld => $val) {
	if(substr($fld,0,4) != 'gyn_') continue;
	$dt[$fld] = $val;
}

$wnl = text(xl('WNL'));
$abn = text(xl('Abnormal'));
$oth = text(xl('Other')) . ': ';

// First just create a line of all items that are WNL
$chp_printed = false;
$prnt = '';
$nt = trim($dt['gyn_ext_comm']);
if($dt['gyn_ext_wnl'] == '1' && $nt == '') { 
	if($prnt != '')  $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Ext. Gen')) . ': </b>' . $wnl;
}
$nt = trim($dt['gyn_mea_comm']);
if($dt['gyn_mea_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Urethral Meatus')) . ': </b>' . $wnl;
}
$nt = trim($dt['gyn_ure_comm']);
if($dt['gyn_ure_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Urethra')) . ': </b>' . $wnl;
}
$nt = trim($dt['gyn_blad_comm']);
if($dt['gyn_blad_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Bladder')) . ': </b>' . $wnl;
}

$nt = '';
if($dt['gyn_vag_abn'] == 1)  $nt = 'no';
if($dt['gyn_vag_abn'] == 1)  $nt = 'no';
if($dt['gyn_vag_dc'] == 1)   $nt = 'no';
if($dt['gyn_vag_atro'] == 1) $nt = 'no';
if($dt['gyn_vag_atro_type']) $nt = 'no';
if($dt['gyn_vag_cys'] == 1)  $nt = 'no';
if($dt['gyn_vag_cys_type'])  $nt = 'no';
if($dt['gyn_vag_rec'] == 1)  $nt = 'no';
if($dt['gyn_vag_rec_type'])  $nt = 'no';
if($dt['gyn_vag_nt'])        $nt = 'no';
if($dt['gyn_vag_wnl'] == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Vagina')) . ': </b>' . $wnl;
}
$nt = '';
if($dt['gyn_cer_abs'] == 1)   $nt = 'no';
if($dt['gyn_cer_fri'] == 1)   $nt = 'no';
if($dt['gyn_cer_ant'] == 1)   $nt = 'no';
if($dt['gyn_cer_polyp'] == 1) $nt = 'no';
if($dt['gyn_cer_iud'] == 1)   $nt = 'no';
if($dt['gyn_cer_nt'])         $nt = 'no';
if($dt{'gyn_cer_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Cervix')) . ': </b>' . $wnl;
}
$nt = '';
if($dt['gyn_ut_abs'] == 1)    $nt = 'no';
if($dt['gyn_ut_retro'] == 1)  $nt = 'no';
if($dt['gyn_ut_tender'] == 1) $nt = 'no';
if($dt['gyn_ut_size'])        $nt = 'no';
if($dt['gyn_ut_pro'] == 1)    $nt = 'no';
if($dt['gyn_ut_nt'])          $nt = 'no';
if($dt{'gyn_ut_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt.=', ';
	$prnt .= '<b>' . text(xl('Uterus')) . ': </b>' . $wnl;
}
$nt = '';
if($dt['gyn_ad_absent'] == 1) $nt = 'no';
if($dt['gyn_ad_tender'] == 1) $nt = 'no';
if($dt['gyn_ad_enl'] == 1)    $nt = 'no';
if($dt['gyn_ad_firm'] == 1)   $nt = 'no';
if($dt['gyn_ad_mass'] == 1)   $nt = 'no';
if($dt['gyn_ad_nt'])          $nt = 'no';
if($dt{'gyn_ad_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Adnexa')) . ': </b>' . $wnl;
}
$nt = trim($dt{'gyn_rec_comm'});
if($dt{'gyn_rec_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', ';
	$prnt .= '<b>' . text(xl('Rectal')) . ': </b>' . $wnl;
}
$nt = trim($dt{'gyn_an_comm'});
if($dt{'gyn_an_wnl'} == '1' && $nt == '') { 
	if($prnt != '') $prnt .= ', </span>';
	$prnt .= '<b>' . text(xl('Anus/Perineum')) . ': </b>' . $wnl;
}
if(!empty($prnt)) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	echo "<tr><td colspan='2'>$prnt</td></tr>\n";
}

$nt = $chk = $prnt = '';
if($dt{'gyn_ext_wnl'} == 1) $prnt = $wnl;
if($dt{'gyn_ext_abn'} == 1) $prnt = $abn;
$nt=trim($dt{'gyn_ext_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Ext. Gen: ',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_mea_wnl'} == 1) $prnt = $wnl;
if($dt{'gyn_mea_abn'} == 1) $prnt = $abn;
$nt=trim($dt{'gyn_mea_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title,$chp_printed);
		PrintSingleLine('Urethral Meatus: ',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_ure_wnl'} == 1) $prnt = $wnl;
if($dt{'gyn_ure_abn'} == 1) $prnt = $abn;
$nt=trim($dt{'gyn_ure_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Urethral: ',$prnt);
	}
}

$prnt=$nt='';
if($dt{'gyn_blad_wnl'} == 1) $prnt = $wnl;
if($dt{'gyn_blad_abn'} == 1) $prnt = $abn;
$nt = trim($dt{'gyn_blad_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Bladder: ',$prnt);
	}
}

$prnt = $nt = '';
$chk = array(); 
$grd = text(xl('Grade'));
if($dt{'gyn_vag_wnl'} == 1) $chk[] = $wnl;
if($dt{'gyn_vag_abn'} == 1) $chk[] = $abn;
if($dt{'gyn_vag_dc'} == 1) $chk[] = text(xl('D/C'));
if($dt{'gyn_vag_atro'} == 1) $chk[] = text(xl('Atrophic'));
$nt = $dt{'gyn_vag_atro_type'};
if($nt) $chk[] = $grd . ': ' . $nt;
if($dt{'gyn_vag_cys'} == 1) $chk[] = text(xl('Cystocele'));
$nt = $dt{'gyn_vag_cys_type'};
if($nt) $chk[] = $grd . ': ' . $nt;
if($dt{'gyn_vag_rec'} == 1) $chk[] = text(xl('Rectocele'));
$nt = $dt{'gyn_vag_rec_type'};
if($nt) $chk[] = $grd . ': ' . $nt;
$nt = trim($dt{'gyn_vag_nt'});
$prnt = BuildPrintList($chk);
if(($prnt != 'WNL' && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Vagina: ', $prnt);
		PrintSingleLine('', $nt);
	} else {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Vagina: ', $nt);
	}
}

$prnt = $nt = '';
$chk = array();
if($dt{'gyn_cer_wnl'} == 1) $chk[] = $wnl;
if($dt{'gyn_cer_abs'} == 1) $chk[] = text(xl('Absent'));
if($dt{'gyn_cer_fri'} == 1) $chk[] = text(xl('Friable'));
if($dt{'gyn_cer_ant'} == 1) $chk[] = text(xl('Anteverted'));
if($dt{'gyn_cer_polyp'} == 1) $chk[] = text(xl('Polyp'));
if($dt{'gyn_cer_iud'} == 1) $chk[] = text(xl('IuD String'));
$nt = trim($dt{'gyn_cer_nt'});
$prnt = BuildPrintList($chk);
if(($prnt != $wnl && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Cervix: ', $prnt);
		PrintSingleLine('', $nt);
	} else {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Cervix: ',$nt);
	}
}

$prnt=$nt='';
$chk=array();
if($dt{'gyn_ut_wnl'} == 1) $chk[] = $wnl;
if($dt{'gyn_ut_abs'} == 1) $chk[] = text(xl('Absent'));
if($dt{'gyn_ut_retro'} == 1) $chk[] = text(xl('Retroflexed'));
if($dt{'gyn_ut_tender'} == 1) $chk[] = text(xl('Tender'));
if($dt{'gyn_ut_size'} != '') $chk[] = text(xl('Size')) . ': ' . 
	$dt{'gyn_ut_size'} . ' ' . text(xl('Wks'));
if($dt{'gyn_ut_pro'} == 1) $chk[] = text(xl('Prolapsed'));
$nt = trim($dt{'gyn_ut_nt'});
$prnt = BuildPrintList($chk);
if(($prnt != $wnl && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Uterus:',$prnt);
		PrintSingleLine('',$nt);
	} else {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Uterus:',$nt);
	}
}

$prnt = $nt = '';
$chk = array();
if($dt{'gyn_ad_wnl'} == 1) $chk[] = $wnl;
if($dt{'gyn_ad_absent'} == 1) $chk[] = text(xl('Absent'));
if($dt{'gyn_ad_tender'} == 1) $chk[] = text(xl('Tender'));
if($dt{'gyn_ad_enl'} == 1) $chk[] = text(xl('Enlarged'));
if($dt{'gyn_ad_firm'} == 1) $chk[] = text(xl('Firm'));
if($dt{'gyn_ad_mass'} == 1) $chk[] = text(xl('Mass'));
$nt = trim($dt{'gyn_ad_nt'});
$prnt = BuildPrintList($chk);
if(($prnt != $wnl && count($chk) > 0) || $nt) {
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Adnexa:', $prnt);
		PrintSingleLine('', $nt);
	} else {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Adnexa:', $nt);
	}
}

$nt = $chk = $prnt = '';
if($dt{'gyn_rec_wnl'} == 1) $prnt = $wnl;
$nt = trim($dt{'gyn_rec_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Rectal:', $prnt);
	}
}

$nt = $chk = $prnt = '';
if($dt{'gyn_an_wnl'} == 1) $prnt = $wnl;
$nt=trim($dt{'gyn_an_comm'});
if($nt != '' || $prnt != $wnl) {
	if(!empty($nt)) $prnt = AppendItem($prnt, $oth . $nt);
	if(!empty($prnt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Anus/Perineum:', $prnt);
	}
}

$nt=trim($dt{'gyn_comment'});
if(!empty($nt)) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Other Findings:', $nt);
}
?>

