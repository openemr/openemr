<?php
if(!isset($dated_list_selected)) $dated_list_selected = array();
if(!function_exists('PrintDatedListHeader')) {
function PrintDatedListHeader($hdr, $printed = false) {
	if(!$printed) {
  	echo "	<tr>\n";
  	echo "		<td class='wmtPrnLabel' style='width: 350px;'>&nbsp;",htmlspecialchars($hdr, ENT_QUOTES, '', FALSE),"&nbsp;</td>";
		echo "		<td class='wmtPrnLabel wmtPrnC' style='width: 40px;'>Done</td>\n";
		echo "		<td class='wmtPrnLabel wmtPrnDateCell wmtPrnC'>Date</td>\n";
		echo "		<td class='wmtPrnLabel'>Comments</td>\n";
  	echo "		</tr>\n";
	}
	return(true);
}
}

$chp_printed = false;
$hdr_printed = false;
$last_cat = 0;
$notes = array();
if($frmdir == 'ob_complete') {
	$cnt = 1;
	while($cnt < 4) {
		$notes[$cnt] = '';
		if($dt{'tri_'.$cnt.'_material_nt'}) 
								$notes[$cnt] = $dt{'tri_'.$cnt.'_material_nt'};
		$cnt++;
	}
}
if($frmdir == 'whc_post_partum') {
	$notes[1] = $dt{'w4_material_nt'};
}

foreach($dated_list_selected as $o) {
	$m = explode('^~', $o);
	if($m[0] == '') continue;
	$title = GetListTitleByKey($m[0],$dated_list_keys);
	$hdr = GetListSectionByKey($m[0],$dated_list_keys,$dated_list_sections);
	// $cat = GetListNoteByKey($m[0],$dated_list_keys);
	$flags = GetListNoteByKey($m[0],$dated_list_keys);
	$flags = explode('::', $flags);
	// POSITION ONE IS THE HEADER IT BELONGS TO
	// POSITION TWO IS THE SEX FILTER
	// POSITION THREE IS A MINIMUM AGE FILTER
	// POSITION FOUR IS A MAXIMUM AGE FILTER
	if(!isset($flags[0])) $flags[0] = '';
	if(!isset($flags[1])) $flags[1] = '';
	if(!isset($flags[2])) $flags[2] = '';
	if(!isset($flags[3])) $flags[3] = '';
	$cat = $flags[0];

	if($cat > 1 && $cat > $last_cat) {
		// Print any note fields that we may have skipped
		if(!$last_cat) $last_cat = 1;
		while($last_cat < $cat) {
			if($notes[$last_cat]) {
				$nt_title = GetListTitleByKey($last_cat, $dated_list_sections);
				$chp_printed = PrintChapter($chp_title, $chp_printed);
				echo "	<tr><td colspan='4' class='wmtPrnLabel'>";
				echo htmlspecialchars($nt_title, ENT_QUOTES, '', FALSE);
				echo "&nbsp;&nbsp;-&nbsp;Other Notes:</td></tr>\n";
				echo "	<tr><td colspan='4' class='wmtPrnBody'>";
				echo htmlspecialchars($notes[$last_cat], ENT_QUOTES, '', FALSE);
				echo "</td></tr>\n";
			}
			$last_cat++;
		}
		$hdr_printed = false;
	}
	$chp_printed = PrintChapter($chp_title, $chp_printed);
	$hdr_printed = PrintDatedListHeader($hdr, $hdr_printed);
	if(!isset($m[1])) $m[1] = '';
	if(!isset($m[2])) $m[2] = '';
	if(!isset($m[3])) $m[3] = '';
				
	echo "	<tr>\n";
	echo "		<td class='wmtPrnBody wmtPrnT'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
	echo htmlspecialchars($title,ENT_QUOTES, '', FALSE);
	echo "</td>\n";
	echo "		<td class='wmtPrnBody wmtPrnC wmtPrnT'>";
	echo ($m[1]) != '' ? 'X' : '&nbsp;';
	echo "		</td>\n";
	echo "		<td class='wmtPrnBody wmtPrnC wmtPrnT'>";
	echo htmlspecialchars($m[2], ENT_QUOTES, '', FALSE);
	echo "		</td>\n";
	echo "		<td class='wmtPrnBody'>";
	echo htmlspecialchars($m[3], ENT_QUOTES, '', FALSE);
	echo "</td>\n";
	echo "	</tr>\n";
	$last_cat = $cat;
}
if($last_cat < 4) {
	// Print any note fields that we may have skipped
	if(!$last_cat) $last_cat = 1;
	while($last_cat < 4) {
		if(!isset($notes[$last_cat])) $notes[$last_cat] = '';
		if($notes[$last_cat]) {
			$nt_title = GetListTitleByKey($last_cat, 'OB_Ed_Sections');
			$chp_printed = PrintChapter($chp_title, $chp_printed);
			echo "	<tr><td colspan='4' class='wmtPrnLabel'>";
			echo htmlspecialchars($nt_title, ENT_QUOTES, '', FALSE);
			echo "&nbsp;&nbsp;-&nbsp;Other Notes:</td></tr>\n";
			echo "	<tr><td colspan='4' class='wmtPrnBody'>";
			echo htmlspecialchars($notes[$last_cat], ENT_QUOTES, '', FALSE);
			echo "</td></tr>\n";
		}
		$last_cat++;
	}
}
?>
