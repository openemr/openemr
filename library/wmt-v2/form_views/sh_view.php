<?php
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($report_mode)) $report_mode = 'by_form';
if(!isset($print_style)) $print_style = 'basic';
if(!isset($chp_title)) $chp_title = 'Social History & Lifestyle';
if(!class_exists('wmtLifestyle')) 
						include_once($GLOBALS['srcdir'].'/wmt-v2/lifestyle.class.php');

if(!isset($expanded_sh)) $expanded_sh = checkSettingMode('wmt::sh_expanded','',$frmdir);
if($report_mode == 'by_form') {
	$lifestyle = wmtLifestyle::getFormLifestyle($pid, $frmdir, $id);
} else if($report_mode == 'recent') {
	$lifestyle = wmtLifestyle::getRecentLifestyle($pid);
}
$smk_exists = $ace_exists = $t_ace_exists = $audit_exists = false;
$flds = array('coffee_use', 'coffee_dt', 'coffee_note',
	'smoking_status', 'smoking', 'smoking_dt', 'alcohol', 'alcohol_note',
	'alcohol_dt', 'drug_use', 'drug_note', 'drug_dt');
foreach($flds as $fld) { 
	if(!isset($dt[$field_prefix.$fld])) $dt[$field_prefix.$fld] = '';
}
if($lifestyle->id) {
	foreach($lifestyle as $key => $val) {
		if(substr($key,0,6) == 'lf_sc_' && $val != '') $smk_exists = true;
		if(substr($key,0,7) == 'lf_ace_' && $val != '') $ace_exists = true;
		if(substr($key,0,9) == 'lf_t_ace_' && $val != '') $t_ace_exists = true;
		if(substr($key,0,7) == 'lf_alc_' && $val != '') $audit_exists = true;
	}
}
if($expanded_sh) {
	include_once($GLOBALS['srcdir'].'/wmt-v2/list_tools.inc');	
	$sh_questions = LoadList('Social_History_Questions');
	$sql = 'SELECT * FROM wmt_sh_data WHERE pid=? AND form_name=? '.
						'AND field_name=?';
	foreach($sh_questions as $q) {
		if($q['codes'] != '') $local_fields[] = 'sh_ex_'.$q['option_id'].'_chc';
		$local_fields[] = 'sh_ex_'.$q['option_id'].'_nt';
		if($q['codes'] != '') {
			$frow = sqlQuery($sql,array($pid,$frmdir,$q['option_id'].'_chc'));
			if(!isset($dt[$field_prefix.'sh_ex_'.$q['option_id'].'_chc'])) 
				$dt[$field_prefix.'sh_ex_'.$q['option_id'].'_chc'] = $frow{'content'};
		}
		$frow = sqlQuery($sql,array($pid,$frmdir,$q['option_id'].'_nt'));
		if(!isset($dt[$field_prefix.'sh_ex_'.$q['option_id'].'_nt'])) 
				$dt[$field_prefix.'sh_ex_'.$q['option_id'].'_nt'] = $frow{'content'};
	}
}

$chk = trim(ListLook($dt{$field_prefix.'smoking_status'},'smoking_status'));
$nt = trim($dt{$field_prefix.'smoking'});
if($chk || $nt) { 
	$chk = "<span class='wmtPrnLabel'>Do You Smoke?&nbsp;&nbsp;</span><span class='wmtPrnBody'>$chk</span>";
	$chk .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wmtPrnBody'>".text($nt)."</span>";
}
$nt = trim($dt{$field_prefix.'smoking_dt'});
if(!empty($nt) && $nt != '0000-00-00' && $nt != '0') {
	$chk .= "<span class='wmtPrnLabel'>&nbsp;&nbsp;-&nbsp;&nbsp;Date Quit:&nbsp;</span><span class='wmtPrnBody'>".text($nt)."</span>";
}
if(!empty($chk)) {
	$chp_printed = PrintChapter($chp_title,$chp_printed);
	echo "	<tr><td colspan='3'>$chk</td></tr>\n";
}

// Smoking Cessation Questionnaire If Applicable
if($smk_exists) {
	$chp_printed = PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Smoking Cessation Questionnaire:', '', 3);
  PrintSingleLineLeftRight('Are you aware of the risks of smoking to your health and well being?',attr($lifestyle->lf_sc_risks),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('Have you tried to quit smoking?',attr($lifestyle->lf_sc_tried),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('What is the longest period of time you have gone without smoking?',attr($lifestyle->lf_sc_wo),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('Why did you start smoking?',attr($lifestyle->lf_sc_why_start),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('Have you tried a structured treatment plan?',attr($lifestyle->lf_sc_treat),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('Have you tried patches and/or gum?',attr($lifestyle->lf_sc_patch),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('What is the primary reason you feel you have not been able to quit smoking?',attr($lifestyle->lf_sc_reason),3, '', 'wmtPrnBody');
  PrintSingleLineLeftRight('Patient referred to:',attr($lifestyle->lf_sc_referred),3, '', 'wmtPrnBody');
}

$chk = AlcoholUseListLook($dt{$field_prefix.'alcohol'});
$nt = trim($dt{$field_prefix.'alcohol_note'});
if($chk || $nt) { 
	$chk = "<span class='wmtPrnLabel'>Alcohol Use?&nbsp;&nbsp;</span><span class='wmtPrnBody'>$chk</span>";
	$chk .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wmtPrnBody'>".text($nt)."</span>";
}
$nt=trim($dt{$field_prefix.'alcohol_dt'});
if(!empty($nt) && $nt != '0000-00-00' && $nt != '0') {
	$chk .= "<span class='wmtPrnLabel'>&nbsp;&nbsp;-&nbsp;&nbsp;Date Quit:&nbsp;</span><span class='wmtPrnBody'>".$nt."</span>";
}
if(!empty($chk)) {
	$chp_printed = PrintChapter($chp_title,$chp_printed);
	echo "	<tr><td colspan='3'>$chk</td></tr>\n";
}

if($ace_exists) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Additional Alcohol Questionnaire','',3);
	PrintSingleLineLeftRight('Have you ever felt you should cut down on your drinking?',ListLook($lifestyle->lf_ace_t, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Have people annoyed you by criticizing your drinking?',ListLook($lifestyle->lf_ace_a, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Have you ever felt bad or guilty about your drinking?',ListLook($lifestyle->lf_ace_c, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?',ListLook($lifestyle->lf_ace_e, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Test Score: Each \'Yes\' answer counts as 1 point. A score of less than 2 is considered passing, while 2 or greater is considered failing.',htmlspecialchars($lifestyle->lf_ace_tot, ENT_QUOTES, '', FALSE),3, '', 'wmtPrnBody');
}

if($t_ace_exists) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('T-ACE Questionnaire','',3);
	PrintSingleLineLeftRight('T. Tolerance - How many drinks does it take to make you feel high?',htmlspecialchars($lifestyle->lf_t_ace_t, ENT_QUOTES, '', FALSE),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('A. Annoyed - Have people annoyed you by criticizing your drinking?',ListLook($lifestyle->lf_t_ace_a, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('C. Cut Down - Have you ever felt you ought to cut down on your drinking?',ListLook($lifestyle->lf_t_ace_c, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('E. Eye Opener - Have you ever had a drink first thing in the morning to steady your nerves or get rid of a hangover?',ListLook($lifestyle->lf_t_ace_e, 'Yes_No'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Test Score: The T-ACE, which is based on the CAGE, is valuable for identifying a range of use, including lifetime and prenatal use, based on the DSM-III-R criteria. A score of 2 or more is considered positive. Affirmative answers to questions A, C or E = 1 point each. Reporting tolerance to more than two drinks (the T question) = 2 points.',htmlspecialchars($lifestyle->lf_t_ace_tot, ENT_QUOTES, '', FALSE),3, '', 'wmtPrnBody');
}

if($audit_exists) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('AUDIT Alcohol Questionnaire','',3);
	PrintSingleLineLeftRight('1.  How often do you have a drink containing alcohol?',ListLook($lifestyle->lf_alc_often, 'AUDIT_Q_1'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('2.  How many drinks containing alcohol do you have on a typical day when you are drinking?',ListLook($lifestyle->lf_alc_many, 'AUDIT_Q_2'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('3.  How often do you have six or more drinks on one occasion?',ListLook($lifestyle->lf_alc_often_gt, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('4.  How often in during the last year have you found that you were not able to stop drinking once you had started?',ListLook($lifestyle->lf_alc_no_stop, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('5.  How often during the last year have you failed to do what was normally expected from you because fo drinking?',ListLook($lifestyle->lf_alc_fail, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('6.  How often during the last year have you needed a first drink in the morning to get yourself going after a heavy drinking session?',ListLook($lifestyle->lf_alc_morning, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('7.  How often during the last year have you had a feeling of guilt or remorse after drinking?',ListLook($lifestyle->lf_alc_guilt, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('8.  How often during the last year have you been unable to remember what happened the night before because you had been drinking?',ListLook($lifestyle->lf_alc_memory, 'AUDIT_Q_3_8'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('9.  Have you or someone else been injured as a result of your drinking?',ListLook($lifestyle->lf_alc_injure, 'AUDIT_Q_9_10'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('10. Has a relative or friend, or a doctor or other health worker been concerned about your drinking or suggested you cut down?',ListLook($lifestyle->lf_alc_concern, 'AUDIT_Q_9_10'),3, '', 'wmtPrnBody');
	PrintSingleLineLeftRight('Test Score: The Alcohol Use Disorders Test (AUDIT) can detect alcohol problems experienced in the last year. A score of 8+ on the AUDIT generally indicates harmful or hazardous drinking.',htmlspecialchars($lifestyle->lf_alc_tot, ENT_QUOTES, '', FALSE),3, '', 'wmtPrnBody');
}

$chk = DrugUseListLook($dt{$field_prefix.'drug_use'});
$nt = trim($dt{$field_prefix.'drug_note'});
if($chk || $nt) {
	$chk = "<span class='wmtPrnLabel'>Do you use any street drugs?&nbsp;&nbsp;</span><span class='wmtPrnBody'>$chk</span>&nbsp;&nbsp;&nbsp;&nbsp;";
	$chk .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wmtPrnBody'>$nt</span>";
}
$nt = trim($dt{$field_prefix.'drug_dt'});
if(!empty($nt) && $nt != '0000-00-00' && $nt != '0') {
	$chk .= "<span class='wmtPrnLabel'>&nbsp;&nbsp;-&nbsp;&nbsp;Date Quit:&nbsp;</span><span class='wmtPrnBody'>$nt</span>";
}
if(!empty($chk)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	echo "	<tr><td>$chk</td></tr>\n";
}

$chk = ListLook($dt{$field_prefix.'coffee_use'},'Caffeine_Use');
$nt = trim($dt{$field_prefix.'coffee_note'});
if($chk || $nt) {
	$chk = "<span class='wmtPrnLabel'>Do you use Caffeine?&nbsp;&nbsp;</span><span class='wmtPrnBody'>$chk</span>&nbsp;&nbsp;&nbsp;&nbsp;";
	$chk .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<span class='wmtPrnBody'>$nt</span>";
}
$nt = trim($dt{$field_prefix.'coffee_dt'});
if(!empty($nt) && $nt != '0000-00-00' && $nt != '0') {
	$chk .= "<span class='wmtPrnLabel'>&nbsp;&nbsp;-&nbsp;&nbsp;Date Quit:&nbsp;</span><span class='wmtPrnBody'>$nt</span>";
}
if(!empty($chk)) {
	$chp_printed = PrintChapter($chp_title,$chp_printed);
	echo "	<tr><td>$chk</td></tr>\n";
}

if($field_prefix != 'ee1_') {
	$nt = '';
	$chk = ListLook($patient->sex, 'sex');
	if($chk) $nt = '<span class="wmtPrintLabel">Gender: </span><span class="wmtPrnBody">'.$chk.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	$chk = ListLook($dt[$field_prefix.'sex_orient'],'Sexual_Orientation');
	if($chk) $nt .= '<span class="wmtPrintLabel">Orientation: </span><span class="wmtPrnBody">'.$chk.'</span>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	if($dt[$field_prefix.'sex_orient_nt']) $nt .= '-&nbsp<span class="wmtPrnBody">'.$dt[$field_prefix.'sex_orient_nt'].'</span>';	
	if(!empty($nt)) {
		$chp_printed = PrintChapter($chp_title,$chp_printed);
		echo "	<tr><td>$nt</td></tr>\n";
	}

	$chc = '';
	if(!isset($dt{$field_prefix.'sex_act_nt'})) $dt{$field_prefix.'sex_act_nt'} = '';
	if(!isset($dt{$field_prefix.'sex_active'})) $dt{$field_prefix.'sex_active'} = '';
	if($dt{$field_prefix.'sex_active'} == 'y') $chc = 'Sexually Active';
	if($dt{$field_prefix.'sex_active'} == 'n') $chc = 'Not Sexually Active';
	$nt = trim($dt{$field_prefix.'sex_act_nt'});
	if($nt || $chc) {
		if(!$chc) $chc = 'Sexually Active Notes:';
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine($chc, $nt);
	}


	if(!isset($dt{$field_prefix.'sex_nt'})) $dt{$field_prefix.'sex_nt'}='';
	$nt = trim($dt{$field_prefix.'sex_nt'});
	if($nt) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Sexual History:', $nt);
	}

	if(!isset($dt{$field_prefix.'sex_sti'})) $dt{$field_prefix.'sex_sti'}='';
	if(!isset($dt{$field_prefix.'sex_sti_nt'})) $dt{$field_prefix.'sex_sti_nt'}='';
	$chc = ListLook($dt[$field_prefix.'sex_sti'],'YN_DECLINE');
	$nt = trim($dt[$field_prefix.'sex_sti_nt']);
	if($nt || $chc) {
		if($nt && $chc) $chc .= ' - ';
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		PrintSingleLine('Ever had a STI?', $chc . $nt);
	}
}

/*
if(!isset($dt{$field_prefix.'bc'})) $dt{$field_prefix.'bc'}='';
if(!isset($dt{$field_prefix.'bc_chc'})) $dt{$field_prefix.'bc_chc'}='';
$chc = ListLook($dt[$field_prefix.'bc_chc'],'YesNo');
$nt = trim($dt{$field_prefix.'bc'});
if($chc != '') {
	if($nt != '') $nt = ' - '.$nt;
	$nt = $chc . $nt;
}
if($nt) {
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	PrintSingleLine('Birth Control:', $nt);
}
*/

$nt = '';
if(isset($fyi->fyi_sh_nt)) $nt = trim($fyi->fyi_sh_nt);
if(isset($dt['fyi_sh_nt'])) $nt = trim($dt{'fyi_sh_nt'});
if(!empty($nt)) {
	$chp_printed=PrintChapter($chp_title,$chp_printed);
	PrintSingleLine('Other Notes:',$nt);
}

if($expanded_sh) {
	$hdr_printed = false;
	$hdr_title = 'Lifestyle / Environmental Considerations';
	foreach($sh_questions as $q) {
		$chc_key = $field_prefix.'sh_ex_'.$q['option_id'].'_chc';
		$nt_key = $field_prefix.'sh_ex_'.$q['option_id'].'_nt';
		if(!isset($dt[$chc_key])) $dt[$chc_key] = '';
		if(!isset($dt[$nt_key])) $dt[$nt_key] = '';
		$chc = '';
		if($q['codes'] && ($dt[$chc_key] != '')) {
			$chc = ListLook($dt[$chc_key],$q['codes'],'');
		}
		$nt = $dt[$nt_key];
		if($nt != '' || $chc != '') {
			$chp_printed = PrintChapter($chp_title,$chp_printed);
			$hdr_printed = PrintHeader($hdr_title,$hdr_printed);
			PrintSingleLine($q['title'].':',$chc);
			PrintOverhead('',$nt);
		}
	}
}

?>
