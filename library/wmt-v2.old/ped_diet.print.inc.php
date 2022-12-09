<?php
$chp_printed=false;

$prnt=$nt='';
$chk=array();
if($ad{'pad_diet_form'} == '1') { $chk[]='Formula'; }
if($ad{'pad_diet_fed'} == '1') { $chk[]='Breast Fed'; }
if($ad{'pad_diet_oth'} == '1') { $chk[]='Other'; }
$nt=trim($ad{'pad_diet_type_nt'});
if(count($chk) > 0) {
	$prnt=BuildPrintList($chk);
	if(!empty($nt)) { $prnt.='&nbsp;-&nbsp;&nbsp;'; }
}
$prnt.=$nt;
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Diet',$chp_printed);
	PrintSingleLine('Diet Type:',$prnt);
}

$prnt=$nt='';
$chc=ListLook($ad{'pad_diet_tube'},'EE1_YesNo');
$chk= array();
if($ad{'pad_diet_ttype'}) { $chk[] = "Tube Type&nbsp;-&nbsp;".$ad{'pad_diet_ttype'}; }
if($ad{'pad_diet_tsize'}) { $chk[] = "Tube Size&nbsp;-&nbsp;".$ad{'pad_diet_tsize'}; }
if(count($chk) > 0) { $nt = BuildPrintList($chk); }
if($nt || $chc != '') {
	$chp_printed=PrintChapter('Diet',$chp_printed);
	PrintSingleLine('Feeding Tube:',$chc.'&nbsp;&nbsp;'.$nt);
}

$prnt=$nt='';
$chk = array();
if($ad{'pad_diet_chc'}) { $chk[]="Formula Choice&nbsp;-&nbsp;".$ad{'pad_diet_chc'}; }
if($ad{'pad_diet_amt'}) { $chk[]="Amount&nbsp;-&nbsp;".$ad{'pad_diet_amt'}; }
if($ad{'pad_diet_rate'}) { $chk[]="Rate&nbsp;-&nbsp;".$ad{'pad_diet_rate'}; }
if(count($chk) > 0) { $prnt = BuildPrintList($chk); }
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Diet',$chp_printed);
	PrintSingleLine('Specifics:',$prnt);
}

$prnt=trim($ad{'pad_diet_require'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Diet',$chp_printed);
	PrintOverhead('Special Requirements:',$prnt);
}

$prnt=trim($ad{'pad_diet_nt'});
if(!empty($prnt)) {
	$chp_printed=PrintChapter('Diet',$chp_printed);
	PrintOverhead('Other Notes:',$prnt);
}

if($chp_printed) { CloseChapter(); }
?>

