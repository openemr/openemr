<?php 

function PY1_PrintCheckNote($check, $note, $title='')
{
  if($check == 1 || (!empty($note))) {
    $GLOBALS['chapter_printed']=EE1_PrintChapter('Past Medical History', $GLOBALS['chapter_printed']);
  	echo "  <tr><td class='wmtPrnLabel'>$title</td>\n";
  	echo "  <td class='wmtPrnBody'>$note</td></tr>\n";
  }
}

function PY1_PrintROS($yn='', $note='', $title='', $section='', $match='')
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

function PY1_PrintCompoundROS($note='', $section='')
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

function PY1_PrintROS_RefertoHPI($section='')
{
    $GLOBALS['chp_printed']=PrintChapter('Review of Systems', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section.":&nbsp;&nbsp;<span class='wmtPrnBody'>Refer to HPI for Details</span>", $GLOBALS['hdr_printed']);
}

function PY1_PrintGE_YN($type='',$yn='',$note='',$section='',$subsection='')
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

function PY1_PrintGE($type='',$chc='',$note='',$section='',$subsection='')
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

function PY1_PrintGE_3Col($type='',$section='',$subsection='')
{
  if(!empty($type)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='3'>$type</td>\n";
		echo "		</tr>\n";
  }
}

function PY1_PrintGE_2Col($type='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($note) || !empty($type)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel' colspan='2'>$type</td>\n";
  	echo "  		<td class='wmtPrnBody'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PY1_PrintGE_LongNote($type='',$note='',$section='',$subsection='')
{
	$note=trim($note);
  if(!empty($note) || !empty($type)) {
    $GLOBALS['chp_printed']=PrintChapter('General Physical Exam', $GLOBALS['chp_printed']);
    $GLOBALS['hdr_printed']=PrintHeader($section, $GLOBALS['hdr_printed']);
		if(!empty($subsection)) {
			$GLOBALS['sub_printed']=PrintSubSection($subsection, $GLOBALS['sub_printed']);
		}
  	echo "  	<tr>\n";
		echo "			<td class='wmtPrnIndentLabel'>$type</td>\n";
  	echo "  		<td class='wmtPrnBody' colspan='2'>$note</td>\n";
		echo "		</tr>\n";
  }
}

function PY1_PrintCompoundGE($note='', $section='', $subsection='')
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

function PY1_PrintNote($note='')
{
 	echo "  	<tr>\n";
	echo "			<td class='wmtPrnIndentText' colspan='3'>$note</td>\n";
	echo "		</tr>\n";
}

function PY1_AppendItem($existing='',$new='')
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
