<?php 
global $chp_printed, $hdr_printed;
if(!isset($chp_title)) $chp_title = 'Breast Examination';
$flds = sqlListFields('form_breast_exam');
foreach($flds as $fld) {
	if(!isset($dt[$fld])) $dt[$fld] = '';
}
$fres = sqlQuery("SELECT * FROM form_breast_exam WHERE link_id=?".
	" AND link_form=?", array($id, $frmdir));
foreach($fres as $fld => $val) {
	if(substr($fld,0,4) != 'bre_') continue;
	$dt[$fld] = $val;
}

$chp_printed = false;
// TWO PASSES - FIRST IS RIGHT BREAST/NIPPLE, SECOND IS LEFT
$pass = 0;
$tag = 'r';
$side = 'Right ';

while($pass < 2) {
	$prefix = 'bre_b' . $tag;
	$hdr_printed = false;
	$cnt=0;
	while($cnt < 5) {
		$match='';
		if($cnt == 1 || $cnt == 3) $match = 'No Print';
		if($cnt == 2) $match = 'n';
		if($cnt == 4) $match = 'y';
		$prnt = $chc = $chk = '';
		$chc = strtolower(substr($dt{$prefix.'_axil'},0,1));
		$nt = trim($dt{$prefix.'_axil_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Axillary Nodes');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Axillary Nodes');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_axil'},$nt,'Axillary Nodes',
					$side.'Breast:',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_mass'},0,1));
		$nt = trim($dt{$prefix.'_mass_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Mass');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Mass');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_mass'},$nt,'Mass',
				$side.'Breast',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_imp'},0,1));
		$nt = trim($dt{$prefix.'_imp_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Implant');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Implant');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_imp'},$nt,'Implant',
				$side.'Breast',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_rmv'},0,1));
		$nt = trim($dt{$prefix.'_rmv_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Masectomy');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Masectomy');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_rmv'},$nt,'Masectomy',
				$side.'Breast',$match,'YesNo');
		}
		if(!empty($prnt)) PrintCompoundROS($prnt,$side.'Breast');
		$cnt++;
	}
	$nt = trim($dt{$prefix.'_nt'});
	if(!empty($nt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		$hdr_printed = PrintHeader($side.'Breast', $hdr_printed);
		PrintOverhead('Notes:', $nt);
	}
	
	$hdr_printed = false;
	$cnt = 0;
	$prefix = 'bre_n' . $tag;
	while($cnt < 5) {
		$match = '';
		if($cnt == 1 || $cnt == 3) $match = 'No Print';
		if($cnt == 2) $match = 'n';
		if($cnt == 4) $match = 'y';
		$prnt = $chc = $chk = '';
		$chc = strtolower(substr($dt{$prefix.'_ev'},0,1));
		$nt = trim($dt{$prefix.'_ev_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'Not Everted');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Everted');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_ev'},$nt,'Everted',
				$side.'Nipple',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_in'},0,1));
		$nt = trim($dt{$prefix.'_in_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'Not Inverted');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Inverted');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_in'},$nt,'Inverted',
				$side.'Nipple',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_mass'},0,1));
		$nt = trim($dt{$prefix.'_mass_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Mass');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Mass');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_mass'},$nt,'Mass',
				$side.'Nipple',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_dis'},0,1));
		$nt = trim($dt{$prefix.'_dis_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Discharge');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Discharge');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_dis'},$nt,'Discharge',
				$side.'Nipple',$match,'YesNo');
		}
		$chc = strtolower(substr($dt{$prefix.'_ret'},0,1));
		$nt = trim($dt{$prefix.'_ret_nt'});
		if(empty($nt)) {
			if($cnt == 1 && $chc == 'n') $prnt = AppendItem($prnt,'No Retraction');
			if($cnt == 3 && $chc == 'y') $prnt = AppendItem($prnt,'Retraction');
		} else {
			if($match == $chc) PrintROS($dt{$prefix.'_ret'},$nt,'Retraction',
				$side.'Nipple',$match,'YesNo');
		}
		if(!empty($prnt)) PrintCompoundROS($prnt,$side.'Nipple');
		$cnt++;
	}
	$nt=trim($dt{$prefix.'_nt'});
	if(!empty($nt)) {
		$chp_printed = PrintChapter($chp_title, $chp_printed);
		$hdr_printed = PrintHeader($side.'Nipple', $hdr_printed);
		PrintOverhead('Notes:', $nt);
	}
	$pass++;
	$tag = 'l';
	$side = 'Left ';
}
?>

