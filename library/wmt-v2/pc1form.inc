<?php 

function PC1_VitalsTest($vrec,$ht,$wt,$bps,$bpd,$bmi,$bmi_status='',$hr='',$arm='',$lbps='',$lbpd='',$stbps='',$stbpd='',$pox='',$accu='',$resp='',$temp='',$meth='') {
  $add=false;
  // If there is no vitals record, and we captured data =>  ADD
  if($vrec == '' || $vrec == '0') { $add=true; }
  $vitals_entered = ($ht > 0);
  if(!$vitals_entered) { $vitals_entered= ($wt > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bps > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bpd > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bpd > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bmi > 0); }
  if(!$vitals_entered) { $vitals_entered= ($bmi_status != ''); }
  if(!$vitals_entered) { $vitals_entered= ($hr != 0); }
  if(!$vitals_entered) { $vitals_entered= ($arm != ''); }
  if(!$vitals_entered) { $vitals_entered= ($lbps != ''); }
  if(!$vitals_entered) { $vitals_entered= ($lbpd != ''); }
  if(!$vitals_entered) { $vitals_entered= ($stbps != ''); }
  if(!$vitals_entered) { $vitals_entered= ($stbpd != ''); }
  if(!$vitals_entered) { $vitals_entered= ($pox != ''); }
  if(!$vitals_entered) { $vitals_entered= ($accu != ''); }
  if(!$vitals_entered) { $vitals_entered= ($resp != ''); }
  if(!$vitals_entered) { $vitals_entered= ($temp != ''); }
  if(!$vitals_entered) { $vitals_entered= ($meth != ''); }
  if($add && $vitals_entered) { return true; }
  $add=false;
  if($vrec != '' && $vrec != '0' && $vitals_entered) {
    $fres=sqlStatement("SELECT * FROM form_vitals WHERE id='".$vrec."'");
    $vitals=sqlFetchArray($fres);
    if($vitals) {
      if($ht != $vitals{'height'}) { $add=true; }
      if($wt != $vitals{'weight'}) { $add=true; }
      if($bps!= $vitals{'bps'}) { $add=true; }
      if($bpd != $vitals{'bpd'}) { $add=true; }
      if($bmi != $vitals{'BMI'}) { $add=true; }
      if($bmi_status != $vitals{'BMI_status'}) { $add=true; }
      if($hr != $vitals{'pulse'}) { $add=true; }
      if($arm != $vitals{'arm'}) { $add=true; }
      if($lbps != $vitals{'prone_bps'}) { $add=true; }
      if($lbpd != $vitals{'prone_bpd'}) { $add=true; }
      if($stbps != $vitals{'standing_bps'}) { $add=true; }
      if($stbpd != $vitals{'standing_bpd'}) { $add=true; }
      if($pox != $vitals{'oxygen_saturation'}) { $add=true; }
      if($accu != $vitals{'diabetes_accucheck'}) { $add=true; }
      if($resp != $vitals{'respiration'}) { $add=true; }
      if($temp != $vitals{'temperature'}) { $add=true; }
    }
  }
 return $add;
}

function PC1_PrintCheckNote($check, $note, $title='')
{
  if($check == 1 || (!empty($note))) {
    $GLOBALS['chapter_printed']=EE1_PrintChapter('Past Medical History', $GLOBALS['chapter_printed']);
  	echo "  <tr><td class='wmtPrnLabel'>$title</td>\n";
  	echo "  <td class='wmtPrnBody'>$note</td></tr>\n";
  }
}

function PC1_PrintROS($yn='', $note='', $title='', $section='', $match='')
{
	if($yn != $match) { return false; }
	$yn=ListLook($yn,'EE1_YesNo');
	$note=trim($note);
  if(!empty($yn) || !empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' style='width: 20%'>$title</td>\n";
		echo "			<td class='wmtPrnLabel' style='width: 5%'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PC1_PrintCompoundROS($note='', $section='')
{
	$note=trim($note);
  if(!empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PC1_PrintROS_RefertoHPI($section='')
{
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section.":&nbsp;&nbsp;<span class='wmtPrnBody'>Refer to HPI for Details</span>", $GLOBALS['hdr_printed']);
}

function PC1_PrintGE_YN($type='',$yn='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($yn) || !empty($note) || !empty($type)) {
		$yn=ListLook($yn, 'EE1_YesNo');
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$yn</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PC1_PrintGE($type='',$chc='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($chc) || !empty($note) || !empty($type)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel'>$type</td>\n";
		echo "			<td class='wmtPrnBody'>$chc</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PC1_PrintCompoundGE($note='', $section='', $subsection='')
{
	$note=trim($note);
  if(!empty($note)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='3'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PC1_PrintNote($note='')
{
 	echo "  	<tr>\n";
	echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
	echo "		</tr>\n";
}

function PC1_AppendItem($existing='',$new='')
{
	if($new=='') { return($existing); }
	$existing=trim($existing);
	if(!empty($existing)) { 
		$existing.=', ';
	}
	$existing.=$new;
	return($existing);
}

?>
