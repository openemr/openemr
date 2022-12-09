<?php
if(!isset($img)) $img = array();
if(!isset($dt['fyi_img_nt'])) $dt['fyi_img_nt'] = '';
if(isset($fyi->fyi_img_nt)) $dt['fyi_img_nt'] = $fyi->fyi_img_nt;
if((count($img) > 0) || $dt['fyi_img_nt']) {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "  <div class='wmtPrnCollapseBar'>\n";
	echo "    <span class='wmtPrnChapter'>Images</span>\n";
	echo "  </div>\n";
	echo "  <div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($img) > 0) {
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabelCenterBorderB' style='width: 90px'>Date</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Type</td>\n";
	echo "			<td class='wmtPrnLabelCenterBorderLB'>Notes</td>\n";
	echo "		</tr>\n";
	}
	foreach($img as $prev) {
		echo "<tr>\n";
		echo "	<td class='wmtPrnBodyBorderB'>",htmlspecialchars($prev['img_dt'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "	<td class='wmtPrnBodyBorderLB'>",ListLook($prev['img_type'],'Image_Types'),"&nbsp;</td>\n";
		echo "	<td class='wmtPrnBodyBorderLB'>",htmlspecialchars($prev['img_nt'], ENT_QUOTES, '', FALSE),"&nbsp;</td>\n";
		echo "</tr>\n";
	}
	if($dt['fyi_img_nt'] != '') {
		if(isset($img) && count($img) > 0) {
			echo "		<tr>\n";
			echo "			<td colspan='3' class='wmtPrnLabel'>Other Notes:</td>\n";
			echo "		</tr>\n";
		}
		echo "		<tr>\n";
		echo "			<td colspan='3' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_img_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}

	echo "		</table>\n";
	echo "	</div>\n";
	echo "</div>\n";
}
?>
