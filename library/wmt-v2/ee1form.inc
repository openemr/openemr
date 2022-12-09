<?php 

if(!function_exists('EE1_VitalsTest')) {
function EE1_VitalsTest($vrec,$ht,$wt,$bps,$bpd,$bmi,$bmi_status='',$hr='',$arm='',$lbps='',$lbpd='',$stbps='',$stbpd='',$pox='',$accu='',$resp='',$temp='') {
  $add=false;
  // If there is no vitals record, and we captured data =>  ADD
  if($vrec == '' || $vrec == '0') $add=true;
  $vitals_entered = ($ht > 0 && ($ht != ''));
  if(!$vitals_entered) $vitals_entered= ($wt > 0 && $wt != '');
  if(!$vitals_entered) $vitals_entered= ($bps > 0 && $bps != '');
  if(!$vitals_entered) $vitals_entered= ($bpd > 0 && $bpd != '');
  if(!$vitals_entered) $vitals_entered= ($bmi > 0 && $bmi != '');
  if(!$vitals_entered) $vitals_entered= ($bmi_status != '');
  if(!$vitals_entered) $vitals_entered= ($hr != 0 && $hr != '');
  if(!$vitals_entered) $vitals_entered= ($arm != '');
  if(!$vitals_entered) $vitals_entered= ($lbps != '' && $lbps > 0);
  if(!$vitals_entered) $vitals_entered= ($lbpd != '' && $lbpd > 0);
  if(!$vitals_entered) $vitals_entered= ($stbps != '' && $stbps > 0);
  if(!$vitals_entered) $vitals_entered= ($stbpd != '' && $stbpd > 0);
  if(!$vitals_entered) $vitals_entered= ($pox != '' && ($pox > 0));
  if(!$vitals_entered) $vitals_entered= ($accu != '' && ($accu > 0));
  if(!$vitals_entered) $vitals_entered= ($resp != '' && ($resp > 0));
  if(!$vitals_entered) $vitals_entered= ($temp != '' && ($temp > 0));
  if($add && $vitals_entered) return true;
  $add=false;
  if($vrec != '' && $vrec != '0' && $vitals_entered) {
    $vitals=sqlQuery('SELECT * FROM form_vitals WHERE id=?',array($vrec));
    if($vitals) {
      if($ht != $vitals{'height'}) $add=true;
			// echo "Height Check Result ($add)<br>\n";
      if($wt != $vitals{'weight'}) $add=true;
			// echo "Weight Check Result ($add)<br>\n";
      if($bps!= $vitals{'bps'}) $add=true;
			// echo "BS Check Result ($add)<br>\n";
      if($bpd != $vitals{'bpd'}) $add=true;
			// echo "BD Check Result ($add)<br>\n";
      if($bmi != $vitals{'BMI'}) $add=true;
			// echo "BMI Check Result ($add)<br>\n";
      if($bmi_status != $vitals{'BMI_status'}) $add=true;
			// echo "BMI Status Check Result ($add)<br>\n";
      if($hr != $vitals{'pulse'}) $add=true;
			// echo "Pulse Check Result ($add)<br>\n";
      if($arm != $vitals{'arm'}) $add=true;
			// echo "Arm Check Result ($add)<br>\n";
      if($lbps != $vitals{'prone_bps'}) $add=true;
			// echo "Prone BS Check Result ($add)<br>\n";
      if($lbpd != $vitals{'prone_bpd'}) $add=true;
			// echo "Prone BD Check Result ($add)<br>\n";
      if($stbps != $vitals{'standing_bps'}) $add=true;
			// echo "Standing BS Check Result ($add)<br>\n";
      if($stbpd != $vitals{'standing_bpd'}) $add=true;
			// echo "Standing BD Check Result ($add)<br>\n";
      if($pox != $vitals{'oxygen_saturation'}) $add=true;
			// echo "Oxygen Check Result ($add)<br>\n";
      if($accu != $vitals{'diabetes_accucheck'}) $add=true;
			// echo "Accu Check Result ($add)<br>\n";
      if($resp != $vitals{'respiration'}) $add=true;
			// echo "Respiration Check Result ($add)<br>\n";
      if($temp != $vitals{'temperature'}) $add=true;
			// echo "Temp Check Result ($add)<br>\n";
    }
  }
 return $add;
}

function EE1_PrintROS($yn='', $note='', $title='', $section='', $match='')
{
	global $chp_printed, $hdr_printed;
	if($yn != $match) { return false; }
	$yn=ListLook($yn,'EE1_YesNo');
	$note=trim($note);
  if(!empty($yn) || !empty($note)) {
    $chp_printed=PrintChapter('Review of Systems', $chp_printed);
    $hdr_printed=PrintHeader($section, $hdr_printed);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentBody' style='width: 30%'>$title</td>\n";
		echo "			<td class='wmtPrnBody' style='width: 5%'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function EE1_PrintCompoundROS($note='', $section='')
{
	global $chp_printed, $hdr_printed;
	$note=trim($note);
  if(!empty($note)) {
    $chp_printed=PrintChapter('Review of Systems', $chp_printed);
    $hdr_printed=PrintHeader($section, $hdr_printed);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function EE1_PrintROS_RefertoHPI($section='')
{
	global $chp_printed, $hdr_printed;
  $chp_printed=PrintChapter('Review of Systems', $chp_printed);
  $hdr_printed=PrintHeader($section.": Refer to HPI for Details", $hdr_printed);
}

function EE1_PrintROS_NoProblem($section='')
{
	global $chp_printed, $hdr_printed;
  $chp_printed=PrintChapter('Review of Systems', $chp_printed);
  $hdr_printed=PrintHeader($section.": No Problems Indicated", $hdr_printed);
}


function EE1_PrintGE_YN($type='',$yn='',$note='',$section='',$subsection='')
{
	global $chp_printed, $chp_title, $hdr_printed, $sub_printed;
	$note=trim($note);
  if(!empty($yn) || !empty($note) || !empty($type)) {
		$yn = ListLook($yn, 'EE1_YesNo');
    $chp_printed = PrintChapter('General Physical Exam', $chp_printed);
    $hdr_printed = PrintHeader($section, $hdr_printed);
		if(!empty($subsection)) {
			$sub_printed = PrintSubSection($subsection, $sub_printed);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentBody'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody' style='width: 70%;'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function EE1_PrintGE($type='',$chc='',$note='',$section='',$subsection='')
{
	global $chp_printed, $chp_title, $hdr_printed, $sub_printed;
	$note=trim($note);
  if(!empty($chc) || !empty($note) || !empty($type)) {
    $chp_printed = PrintChapter('General Physical Exam', $chp_printed);
    $hdr_printed = PrintHeader($section, $hdr_printed);
		if(!empty($subsection)) {
			$sub_printed = PrintSubSection($subsection, $sub_printed);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentBody'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$chc</td>\n";
  	echo "  		<td class='wmtPrnBody' style='width: 70%;'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function EE1_PrintCompoundGE($note='', $section='', $subsection='')
{
	global $chp_printed, $chp_title, $hdr_printed, $sub_printed;
	$note=trim($note);
  if(!empty($note)) {
    $chp_printed = PrintChapter('General Physical Exam', $chp_printed);
    $hdr_printed = PrintHeader($section, $hdr_printed);
		if(!empty($subsection)) {
			$sub_printed = PrintSubSection($subsection, $sub_printed);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function EE1_PrintNote($note='', $chapter='', $section='', $sub='')
{
	global $chp_printed, $chp_title, $hdr_printed, $sub_printed;
	$note=trim($note);
  if(!empty($note)) {
    $chp_printed = PrintChapter($chapter, $chp_printed);
    $hdr_printed = PrintHeader($section, $hdr_printed);
		if(!empty($sub)) {
			$sub_printed = PrintSubSection($sub, $sub_printed);
		}
 		echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
	}
}

function EE1_AppendItem($existing='',$new='',$prefix='')
{
	if($new=='') { return($existing); }
	$existing=trim($existing);
	if(!empty($existing)) { 
		$existing .= ', ';
	} else {
		$existing = $prefix;
	}
	$existing .= $new;
	return($existing);
}

function EE1_GESelLine($section, $id, $label, $chc, $txt, $list='EE1_YesNo', $nt_style='', $chng=true, $suppress_nt=false, $addl_change='') {
	$id = $section . '_' . $id;
	$norm = $section . '_norm_exam';
	if(substr($section,-4) == 'eyer' || substr($section,-4) == 'eyel') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'earr' || substr($section,-4) == 'earl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-3) == 'brr' || substr($section,-3) == 'brl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'nipr' || substr($section,-4) == 'nipl') {
		$norm = substr($section,0,-4) . 'br_norm_exam';
	}
	$nt = $id . '_nt';
	echo "		<tr>\n";
	echo "			<td class='wmtBody wmtR'><select name='$id' id='$id' class='Input'";
	if($chng) { 
		echo " onchange='document.getElementById(\"$norm\").checked=false; ";
		if($addl_change) { 
			$addl = $section.'_'.$addl_change;
			echo " document.getElementById(\"$addl\").checked=false; ";
		}
		echo "'";
	}
	echo " >";
	ListSel($chc,$list); 
	echo "</select></td>\n";
	echo "			<td class='wmtBody' onclick=\"toggleThroughSelect('$id'); ";
	if($chng) { 
		echo "document.getElementById('$norm').checked=false; ";
		if($addl_change) { 
			$addl = $section.'_'.$addl_change;
			echo " document.getElementById('$addl').checked=false; ";
		}
		echo '"';
	}
	echo ">$label&nbsp;</td>\n";
	if(!$suppress_nt) {
		echo "			<td";
		if($nt_style) echo " style='$nt_style'";
		echo "><input name='$nt' id='$nt' class='wmtFullInput' type='text' value='".
				attr($txt)."'";
		if($chng) { 
			echo " onchange='document.getElementById(\"$norm\").checked=false; ";
			if($addl_change) { 
				$addl = $section.'_'.$addl_change;
				echo " document.getElementById(\"$addl\").checked=false; ";
			}
			echo "'";
		}
		echo " /></td>\n";
	}
	echo "		</tr>\n";
}

function EE1_GESelTextArea($section, $id, $label, $chc, $txt, $list='EE1_YesNo', $nt_style='', $chng=true, $nt_rows='4') {
	$id = $section . '_' . $id;
	$norm = $section . '_norm_exam';
	if(substr($section,-4) == 'eyer' || substr($section,-4) == 'eyel') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'earr' || substr($section,-4) == 'earl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-3) == 'brr' || substr($section,-3) == 'brl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'nipr' || substr($section,-4) == 'nipl') {
		$norm = substr($section,0,-4) . 'br_norm_exam';
	}
	$nt = $id . '_nt';
	echo "		<tr>\n";
	echo "			<td class='wmtBody wmtR'><select name='$id' id='$id' class='Input'";
	if($chng) echo " onchange='document.getElementById(\"$norm\").checked=false; '";
	echo " >";
	ListSel($chc,$list); 
	echo "</select></td>\n";
	echo "			<td class='wmtBody' onclick=\"toggleThroughSelect('$id'); ";
	if($chng) echo "document.getElementById('$norm').checked=false; ";
	echo '"';
	echo " >$label&nbsp;</td>\n";
	echo "			<td";
	if($nt_style) echo " style='$nt_style'";
	if($nt_rows) echo " rowspan='$nt_rows'";
	echo "><textarea name='$nt' id='$nt' class='wmtFullInput' rows='$nt_rows'";
	if($chng) echo " onchange='document.getElementById(\"$norm\").checked=false; '";
	echo ">",attr($txt);

	echo "</textarea></td>\n";
	echo "		</tr>\n";
}

function EE1_GECheckLine($section, $id, $label, $chc, $txt, $nt_style='', $chng=true, $suppress_nt=false) {
	$id = $section . '_' . $id;
	$norm = $section . '_norm_exam';
	if(substr($section,-4) == 'eyer' || substr($section,-4) == 'eyel') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'earr' || substr($section,-4) == 'earl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-3) == 'brr' || substr($section,-3) == 'brl') {
		$norm = substr($section,0,-1) . '_norm_exam';
	}
	if(substr($section,-4) == 'nipr' || substr($section,-4) == 'nipl') {
		$norm = substr($section,0,-4) . 'br_norm_exam';
	}
	$nt = $id . '_nt';
	echo "		<tr>\n";
	echo "			<td class='wmtBody wmtR'><input name='$id' id='$id' type='checkbox' value='1' ";
	echo $chc == '1' ? 'checked' : '';
	if($chng) echo " onchange='document.getElementById(\"$norm\").checked=false; '";
	echo " /></td>\n";
	echo "			<td class='wmtBody'";
	if($suppress_nt) echo " colspan='2'";
	echo "><label for='$id'>$label&nbsp;</label></td>\n";
	if(!$suppress_nt) {
		echo "			<td";
		if($nt_style) echo " style='$nt_style'";
		echo "><input name='$nt' id='$nt' class='wmtFullInput' type='text' value='".
				attr($txt)."'";
		if($chng) echo " onchange='document.getElementById(\"$norm\").checked=false; '";
		echo " /></td>\n";
	}
	echo "		</tr>\n";
}

function EE1_GEMultiCheckLine($section, $options, $choices, $chng=true) {
	echo "		<tr>\n";
	$cnt = 0;
	$norm = $section.'_norm_exam';
	foreach($options as $val) {
		$id = 'tmp_'.$section.'_'.$val;
		$key = ucwords(str_replace("_", " ", $val));
		if(!$cnt) {
			echo "			<td>&nbsp;</td>";
			echo "<td class='wmtBody' colspan='2'>";
		}
		echo "		<input name='$id' id='$id' type='checkbox' value='$val'";
		if($chng) echo " onchange='document.getElementById(\"$norm\").checked=false; '";
		echo (in_array($val, $choices)) ? ' checked ' : '';
		echo " />";
		echo "<label for='$id'>$key&nbsp;</label>&nbsp;&nbsp;&nbsp;&nbsp;";
		$cnt++;
	}
	echo "</td>";
	echo "		</tr>\n";
}

function EE1_GenPrintChoice($title, $chc, $nt, $y='y', $n='n', $list='EE1_YesNo') {
	global $hdr, $sub, $cnt, $prnt;
	$yes = explode("~", $y);
	$no = explode("~", $n);
	$sel = ListLook($chc, $list);
	$selection = $title . $sel;
	$nt = trim($nt);
	if(empty($nt)) {
		if($chc) {
			if($cnt == 1 && in_array($chc,$no)) $prnt = AppendItem($prnt, $selection);
			if($cnt == 3 && in_array($chc,$yes)) $prnt = AppendItem($prnt, $selection);
		}
	} else {
		if($cnt == 0 && $chc == '') {
			EE1_PrintGE($title, '', $nt, $hdr, $sub);
		}
		if($cnt == 2 && in_array($chc,$no)) {
			EE1_PrintGE($title, $sel, $nt, $hdr, $sub);
		}
		if($cnt == 4 && in_array($chc,$yes)) {
			EE1_PrintGE($title, $sel, $nt, $hdr, $sub);
		}
	}
}

function EE1_GenPrintCheck($title, $chc, $nt) {
	global $hdr, $sub, $cnt, $prnt;
	$nt = trim($nt);
	if($chc || $nt) {
		if(empty($nt)) {
			if($chc == 1 && $cnt == 1) $prnt = EE1_AppendItem($prnt,$title);
		} else {
			if($chc == 1 && $cnt == 4) {
				EE1_PrintGE($title, '', $nt, $hdr, $sub);
			} else if($cnt == 0 && !$chc) {
				EE1_PrintNote($nt,'General Physical Exam',$hdr,$sub);
			}
		}
	}
}

}
?>
