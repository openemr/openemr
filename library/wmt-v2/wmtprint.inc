<?php
if(!function_exists('MultPrint')) {
function MultPrint($thisArray, $thisList, $sep = ', ') {
  $dispVal = '';
  if(!is_array($thisArray)) return $dispVal;
  if(count($thisArray) < 1) return $dispVal;
  foreach ($thisArray as $rrow) {
    $lres = sqlQuery('SELECT * FROM `list_options` WHERE ' .
         '`list_id` = ? AND `option_id` = ?', array($thisList, $rrow));
    if($dispVal) $dispVal .= $sep;
    $dispVal .= text($lres['title']);
  }
  return $dispVal;
}
}

if(!function_exists('PrintFamHist')) {
function printFamHist($check=0, $note='', $who='', $title='')
{
	if($check || $note || $who ) {
  	echo "<tr>\n";
  	echo "  <td class='PrintLabel'>&nbsp;&nbsp;",htmlspecialchars($title,ENT_QUOTES,'',FALSE),"</td>\n";
  	echo "  <td class='PrintBody'>".MultPrintInline($who)."</td>\n";
  	echo "  <td class='PrintBody'>".htmlspecialchars($note,ENT_QUOTES,'',FALSE)."</td>\n";
		echo "</tr>\n";
		return true;
	} else {
 	  return false;
	}
}
}

if(!function_exists('PrintChapter')) {
function PrintChapter($title, $printed=false, $tstyle='', $bstyle='border-collapse: collapse;')
{
  if($printed) return true;
  echo "<div class='wmtPrnMainContainer'>\n";
  echo "	<div class='wmtPrnCollapseBar'>\n";
  echo "	<span class='wmtPrnChapter'>",text($title),"</span>\n";
  echo "	</div>\n";
  echo "	<div class='wmtPrnCollapseBox' ";
	if($bstyle) echo "style='$bstyle' ";
	echo ">\n";
  echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'";
	if(!empty($tstyle)) echo " style='$tstyle'";
	echo ">\n";
  if($title == 'Review of Systems') {
    echo "	<tr>\n";
    echo "		<td class='wmtPrnLabel' colspan='3'>The patient reports the current following problems:</td>\n";
    echo "	</tr>\n";
  }
  return true;
}
}

if(!function_exists('PrintChapterStyle2')) {
function PrintChapterStyle2($title, $printed, $tstyle='')
{
  if($printed) return true;
  echo "<fieldset style='border: solid 1px black; padding: 0px; padding-top: 4px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;",htmlspecialchars($title,ENT_QUOTES,'',FALSE),"&nbsp;</legend>\n";
  echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'";
	if(!empty($tstyle)) echo " style='$tstyle'";
	echo ">\n";
  if($title == 'Review of Systems') {
    echo "	<tr>\n";
    echo "		<td class='wmtPrnLabel' colspan='3'>The patient reports the current following problems:</td>\n";
    echo "	</tr>\n";
  }
  return true;
}
}

if(!function_exists('PrintChapterPlainStyle')) {
function PrintChapterPlainStyle($title, $printed, $tstyle='')
{
  if($printed) return true;
  echo "<span class='wmtPrnHeader'>",htmlspecialchars($title,ENT_QUOTES,'',FALSE),"</span>\n";
  echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'";
	if(!empty($tstyle)) echo " style='$tstyle'";
	echo ">\n";
  if($title == 'Review of Systems') {
    echo "	<tr>\n";
    echo "		<td class='wmtPrnLabel' colspan='3'>The patient reports the current following problems:</td>\n";
    echo "	</tr>\n";
  }
  return true;
}
}

if(!function_exists('CloseChapter')) {
function CloseChapter()
{
	global $chp_printed;
	$chp_printed = FALSE;
	echo "	</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
}

if(!function_exists('CloseChapterStyle2')) {
function CloseChapterStyle2()
{
	echo "	</table>\n";
	echo "</fieldset>\n";
	echo "<br>\n";
}
}

if(!function_exists('CloseChapterPlainStyle')) {
function CloseChapterPlainStyle()
{
	echo "	</table>\n";
	echo "<br>\n";
}
}

if(!function_exists('PrintHeader')) {
function PrintHeader($title, $printed, $bar=false)
{
  if($printed) return true;
	if($bar) {
		echo "<tr><td colspan='3'><div style='border-bottom: solid 1px gray; margin: 4px;'></div></td></tr>\n";
	}
  if(!$title) return true;
  echo "<tr>\n";
  echo "<td class='wmtPrnHeader' colspan='3'>",text(xl($title)),"&nbsp;</td>\n";
  echo "</tr>\n";
  return true;
}
}

if(!function_exists('PrintBlockHeader')) {
function PrintBlockHeader($title, $printed)
{
  if($printed) return true;
  if(!$title) return true;
  echo "<tr>\n";
  echo "<td class='wmtPrnHeadingBar' colspan='3'>$title&nbsp;</td>\n";
  echo "</tr>\n";
  return true;
}
}

if(!function_exists('PrintSubSection')) {
function PrintSubSection($title, $printed)
{
  if($printed) return true;
  echo "<tr>\n";
  echo "<td class='wmtPrnSubSection' colspan='3'>$title&nbsp;</td>\n";
  echo "</tr>\n";
  return true;
}
}

function BuildPrintLine($existing='', $label='', $txt='', $sep=';')
{
	if($label && $txt) {
		if($existing) $existing .= $sep . '&nbsp;&nbsp;';
		if($label) $label = '<b>' . text(xl($label)) . '</b>';
		$existing .= $label . ' ' . text($txt);
	}
	return($existing);
}

if(!function_exists('PrintSingleLine')) {
function PrintSingleLine($lbl='',$txt='',$span='3', $tstyle='', $return=FALSE)
{
	global $chp_printed, $hdr_printed, $chp_title;
	$out = '';
	$lbl = trim($lbl);
	if(!empty($lbl) || !empty($txt)) {
		if(substr($lbl,-1) == ':') {
			$lbl = text(xl($lbl)) . ':';
		} else {
			$lbl = text(xl($lbl));
		}
		$out = "<tr>\n";
		$out .= "	<td colspan='$span'";
		if(!empty($tstyle)) $out .=  " style='$tstyle'";
		$out .= "><span class='wmtPrnLabel'>$lbl </span>";
		$out .= "<span class='wmtPrnBody'> " . $txt . "</span></td>\n";
		$out .= "</tr>\n";
	}
	if($return) {
		return($out);
	} else {
		echo $out;
	}
}
}

if(!function_exists('PrintSingleLineLeftRight')) {
function PrintSingleLineLeftRight($lbl='',$txt='',$span='1', $tstyle='', $lbl_class='wmtPrnLabel',$lbl_only = false)
{
	global $chp_printed, $hdr_printed, $chp_title;
	if((empty($lbl) && $lbl_only) || !empty($txt)) {
		echo "		<tr>\n";
		echo "			<td colspan='$span'";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo "><span style='width: 50%;' class='$lbl_class'>$lbl&nbsp;&nbsp</span><span class='wmtPrnBody'>$txt</span></td>\n";
		echo "		</tr>\n";
	}
}
}

if(!function_exists('PrintTwoColumn')) {
function PrintTwoColumn($lbl1='',$txt1='',$lbl2='',$txt2='', $tstyle='')
{
	global $chp_printed, $hdr_printed, $chp_title;
	$_printed = false;
	if(!empty($lbl1) || !empty($txt1)) {
		echo "		<tr>\n";
		echo "			<td ";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo "><span class='wmtPrnLabel'>$lbl1&nbsp;&nbsp</span><span class='wmtPrnBody'>$txt1</span></td>\n";
		$_printed = true;
	}
	if(!empty($lbl2) || !empty($txt2)) {
		if(!$_printed) {
			echo "		<tr>\n";
			echo "			<td";
			if(!empty($tstyle)) echo " style='$tstyle'";
			echo ">&nbsp;</td>\n";
		}
		echo "			<td ";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo "><span class='wmtPrnLabel'>$lbl2&nbsp;&nbsp</span><span class='wmtPrnBody'>$txt2</span></td>\n";
		$_printed = true;
	} else if($_printed) {
		echo "			<td";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo ">&nbsp;</td>\n";
	}
	if($_printed) echo "		</tr>\n";
}
}

if(!function_exists('PrintOverhead')) {
function PrintOverhead($lbl='',$txt='',$span=1,$tstyle='',$indent=false)
{
	global $chp_printed, $hdr_printed, $chp_title;
	// $chp_printed = PrintChapter($chp_title, $chp_printed);
	if(!empty($lbl) && !empty($txt)) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel'";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo " colspan='$span'>",text(xl($lbl)),"</td>\n";
		echo "		</tr>\n";
	}
	if(!empty($txt)) {
		echo "		<tr>\n";
		echo "			<td class='";
		echo $indent ? 'wmtPrnIndentBody' : 'wmtPrnBody';
		echo "'";
		if(!empty($tstyle)) echo " style='$tstyle'";
		echo " colspan='$span'>",text($txt),"</td>\n";
		echo "		</tr>\n";
	}
}
}

if(!function_exists('BuildPrintList')) {
function BuildPrintList($list)
{
	$ret='';
	if(!count($list)) return $ret;
	foreach($list as $k => $val) {
		//  echo "Key: ",$k,"  Value: ",$val,"<br/>\n";
		if(!empty($ret)) $ret.=', ';
		$val = ucwords(str_replace("_", " ", $val));
		$ret .= $val;	
	}
	return $ret;
}
}

if(!function_exists('PrintFormHeader')) {
function PrintFormHeader($psr, $title, $include_phone=false, $include_email=false, $include_web=false) {
	echo "<html>\n";
	echo htmlspecialchars($title,ENT_QUOTES,'',FALSE),"\n";
	echo "<head>\n";
	echo "<link rel='stylesheet' href='../../../library/wmt-v2/wmtprint.bkk.css' type='text/css'>\n";
	echo "</head>\n";

	echo "<body style='background: transparent'>\n";
	echo "<table width='98%'  border='0' cellspacing='0' cellpadding='0'>\n";
	echo "	<tr>\n";
	echo "		<td class='bkkPrnHuge'>",htmlspecialchars($psr->facility,ENT_QUOTES,'',FALSE),"</td>\n";
	echo "	</tr>\n";
	echo "  <tr>\n";
	echo "    <td class='bkkPrnChapter'>",htmlspecialchars($psr->addr,ENT_QUOTES,'',FALSE),"</td>\n";
	echo "  </tr>\n";
	echo "  <tr>\n";
	echo "    <td class='bkkPrnChapter'>",htmlspecialchars($psr->csz,ENT_QUOTES,'',FALSE),"</td>\n";
	echo "  </tr>\n";
	if($include_phone) {
		echo "  <tr>\n";
		echo "    <td class='bkkPrnChapter'>",htmlspecialchars($psr->phone,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "  </tr>\n";
	}
	if($include_email) {
		echo "  <tr>\n";
		echo "    <td class='bkkPrnChapter'>",htmlspecialchars($psr->email,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "  </tr>\n";
	}
	if($include_web) {
		echo "  <tr>\n";
		echo "    <td class='bkkPrnChapter'>",htmlspecialchars($psr->website,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "  </tr>\n";
	}
	echo "</table>\n";
	echo "<br/>\n";
	echo "</body>\n";
	echo "</html>\n";
}
}

if(!function_exists('PrintROS')) {
function PrintROS($yn='', $note='', $title='', $section='', $match='', $list='Yes_No')
{
	global $chp_printed, $hdr_printed, $chp_title;
	if(!isset($chp_title)) $chp_title = 'Review of Systems/Symptoms';
	if(strtolower(substr($yn,0,1)) != $match) return false;
	// echo "PrintROS - yn before look up => ($yn)<br>\n";
	$yn = ListLook($yn,$list);
	// echo "PrintROS - yn after look up => ($yn)<br>\n";
	$note=trim($note);
  if(!empty($yn) || !empty($note)) {
    $chp_printed = PrintChapter($chp_title, $chp_printed);
    $hdr_printed = PrintHeader($section, $hdr_printed);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnBody' style='width: 30%'>",htmlspecialchars($title,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "			<td class='wmtPrnBody' style='width: 5%'>",htmlspecialchars($yn,ENT_QUOTES,'',FALSE),"</td>\n";
  	echo "  		<td class='wmtPrnBody'>",htmlspecialchars($note,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "		</tr>\n";
  }
}
}

if(!function_exists('PrintCompoundROS')) {
function PrintCompoundROS($note='', $section='')
{
	global $chp_printed, $hdr_printed, $chp_title;
	if(!isset($chp_title)) $chp_title = 'Review of Systems/Symptoms';
	$note=trim($note);
  if(!empty($note)) {
    $chp_printed=PrintChapter($chp_title, $chp_printed);
    $hdr_printed=PrintHeader($section, $hdr_printed);
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnBody' colspan='3'>",htmlspecialchars($note,ENT_QUOTES,'',FALSE),"</td>\n";
		echo "		</tr>\n";
  }
}
}

function printSelCol($dt, $lbl, $id, $list='YesNo', $lbl_right = '', $type='', $style=array()) {
	if(!isset($dt[$id])) $dt[$id] = '';
	for($cnt=0; $cnt < 5; $cnt++) {
		if(!isset($style[$cnt])) $style[$cnt] = '';
	}
	$tmp = explode('_', $id);
	if(!isset($tmp[1])) $tmp[1] = '';
	if($tmp[1]) $tmp[1] .= '_';
	$range = explode('-', $list);
	if(!isset($range[1])) $range[1] = '';
	$min = $range[0];
	$max = $range[1];
	echo "<td";
	if($style[0]) echo " style='".$style[0]."'";
	echo '>' . htmlspecialchars($lbl, ENT_QUOTES) . "&nbsp;</td>\n";
	echo "<td";
	if($style[1]) echo " style='".$style[1]."'";
	echo '>';
	if(strtolower($type) == 'num') {
		echo htmlspecialchars($dt[$id_left], ENT_QUOTES); 
	} else {
		echo ListLook($dt[$id],$list); 
	}
	if($lbl_right) echo '&nbsp;&nbsp;' . htmlspecialchars($lbl_right, ENT_QUOTES);
	echo "</td>\n";
}

function printSelColsLR($dt, $lbl_left, $id_left, $id_right, $lbl_right='', $list='YesNo', $type='', $style=array()) {
	if(!isset($dt[$id_left])) $dt[$id_left] = '';
	if(!isset($dt[$id_right])) $dt[$id_right] = '';
	for($cnt=0; $cnt < 5; $cnt++) {
		if(!isset($style[$cnt])) $style[$cnt] = '';
	}
	$tmp = explode('_', $id_left);
	if(!isset($tmp[1])) $tmp[1] = '';
	if($tmp[1]) $tmp[1] .= '_';
	$range = explode('-', $list);
	if(!isset($range[1])) $range[1] = '';
	$min = $range[0];
	$max = $range[1];

	echo "<td";
	if($style[0]) echo " style='".$style[0]."'";
	echo '>' . htmlspecialchars($lbl_left, ENT_QUOTES) . "&nbsp;</td>\n";
	echo "<td";
	if($style[1]) echo " style='".$style[1]."'";
	echo '>';
	if(strtolower($type) == 'num') {
		echo htmlspecialchars($dt[$id_left], ENT_QUOTES); 
	} else {
		echo ListLook($dt[$id_left],$list); 
	}
	echo '</td><td';
	if($style[2]) echo " style='".$style[2]."'";
	echo '>';
	if(strtolower($type) == 'num') {
		echo htmlspecialchars($dt[$id_right], ENT_QUOTES); 
	} else {
		echo ListLook($dt[$id_right],$list); 
	}
	if($lbl_right) echo '&nbsp;&nbsp;' . htmlspecialchars($lbl_right, ENT_QUOTES);
	echo "</td>\n";
}

function printSelLRNoteRight($dt, $lbl_left, $section, $id, $lbl_right='', $list='YesNo', $nt_override='', $nt_rows=1, $style=array()) {
	for($cnt=0; $cnt < 5; $cnt++) {
		if(!isset($style[$cnt])) $style[$cnt] = '';
	}
	$left_id = $section . '_l_' . $id;
	$right_id = $section . '_r_' . $id;
	$nt = $id . '_nt';
	if($nt_override) $nt = $nt_override;
	echo "<tr>\n";
	echo "<td";
	if($style[0]) echo " style='".$style[0]."'";
	echo '>' . htmlspecialchars($lbl_left, ENT_QUOTES) . "&nbsp;</td>\n";
	echo "<td class='wmtPrnR'";
	if($style[1]) echo " style='".$style[1]."'";
	echo '>';
	echo ListLook($dt[$left_id],$list); 
	echo "</td>\n";
	echo "<td class='wmtPrnR'";
	if($style[1]) echo " style='".$style[1]."'";
	echo '>';
	echo ListLook($dt[$right_id],$list); 
	echo "</td>\n";
	if($lbl_right != -1) {
		echo "<td";
		if($style[2]) echo " style='".$style[2]."'";
		echo '>' . htmlspecialchars($lbl_right, ENT_QUOTES) . "&nbsp;</td>\n";
	}
	if($nt_rows) {
		echo "<td class='wmtPrnIndentBody'";
		if($style[3]) echo " style='".$style[3]."'";
		if($lbl_right == -1) echo " colspan='2'";
		if($nt_rows > 1) echo " rowspan='$nt_rows'";
		echo '>';
		echo htmlspecialchars($dt[$nt], ENT_QUOTES);
		echo "</td>\n";
	}
	echo "</tr>\n";
}


function printSelNoteRight($dt, $lbl_left, $section, $id, $lbl_right='', $list='YesNo', $nt_override='', $nt_rows=0, $style=array()) {
	for($cnt=0; $cnt < 5; $cnt++) {
		if(!isset($style[$cnt])) $style[$cnt] = '';
	}
	$id = $section . '_' . $id;
	$nt = $id . '_nt';
	if($nt_override) $nt = $nt_override;
	echo "<tr>\n";
	echo "<td";
	if($style[0]) echo " style='".$style[0]."'";
	echo '>' . text(xl($lbl_left)) . "&nbsp;</td>\n";
	echo "<td class='wmtPrnR' ";
	if($style[1]) echo "style='".$style[1]."'";
	echo ">";
	echo ListLook($dt[$id],$list); 
	echo "</td>\n";
	if($lbl_right != -1) {
		echo "<td";
		if($style[2]) echo " style='".$style[2]."' ";
		echo '>' . text(xl($lbl_right)) . "&nbsp;</td>\n";
	}
	if($nt_rows) {
		echo "<td class='wmtPrnIndentBody'";
		if($style[3]) echo " style='".$style[3]."'";
		if($lbl_right == -1) echo " colspan='2'";
		if($nt_rows > 1) echo " rowspan='$nt_rows'";
		echo '>';
		echo text($dt[$nt]);
		echo "</td>\n";
	} else {
		echo "<td>&nbsp;</td>\n";
	}
	echo "</tr>\n";
}

function printCheckLine($dt, $lbl_left='', $section, $id, $lbl_right='', $nt_override='', $nt_rows=0, $style=array()) {
	$checked = $GLOBALS['webroot'].'/library/wmt-v2/16x16_checkbox_yes.png';
	$unchecked = $GLOBALS['webroot'].'/library/wmt-v2/16x16_checkbox_no.png';
	for($cnt=0; $cnt < 5; $cnt++) {
		if(!isset($style[$cnt])) $style[$cnt] = '';
	}
	$id = $section . '_' . $id;
	$nt = $id . '_nt';
	if($nt_override) $nt = $nt_override;
	echo "<tr>\n";
	echo "<td";
	if($style[0]) echo " style='".$style[0]."'";
	echo '>' . htmlspecialchars($lbl_left, ENT_QUOTES) . "&nbsp;</td>\n";
	echo "<td";
	if($style[1]) echo " style='".$style[1]."'";
	echo '><img src="';
	echo  $dt{$id} ? $checked : $unchecked;
	echo  '" border="0" alt="';
	echo $dt{$id} ? '[x]' : '[ ]';
	echo '" /></td>';
	if($lbl_right != -1) {
		echo "<td";
		if($style[2]) echo " style='".$style[2]."'";
		echo '>' . htmlspecialchars($lbl_right, ENT_QUOTES) . "&nbsp;</td>\n";
	}
	if($nt_rows) {
		echo "<td class='wmtPrnT'";
		if($style[3]) echo " style='".$style[3]."'";
		if($lbl_right == -1) echo " colspan='2'";
		if($nt_rows > 1) echo " rowspan='$nt_rows'";
		echo '>';
		echo htmlspecialchars($dt[$nt], ENT_QUOTES);
		echo "</td>\n";
	} else {
		echo "<td>&nbsp;</td>\n";
	}
	echo "</tr>\n";
}

?>

