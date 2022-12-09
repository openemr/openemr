<?php
if(!isset($pmh_note)) $pmh_note = '';
if(!isset($dt['fyi_pmh_nt'])) $dt['fyi_pmh_nt'] = '';
if($dt['fyi_pmh_nt'] == '') $dt['fyi_pmh_nt'] = $pmh_note;
if(isset($fyi->fyi_pmh_nt)) $dt['fyi_pmh_nt'] = $fyi->fyi_pmh_nt;
if(!isset($pmh)) $pmh = array();
if((count($pmh) > 0) || $pmh_note != '') {
	echo "<span class='wmtPrnHeader''>",xl('Medical History','e'),"&nbsp;</span>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0' style='border-collapse: collapse; margin-top: 4px;'>\n";
	if(count($pmh) > 0) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel'>Issue</td>\n";
		echo "			<td class='wmtPrnLabel'>Notes</td>\n";
		echo "		</tr>\n";
	}
	foreach($pmh as $prev) {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnBody '>",ListLook($prev['pmh_type'],'Medical_History_Problems'),"&nbsp;</td>\n";
		echo "			<td class='wmtPrnBody '>",htmlspecialchars($prev['pmh_nt'],ENT_QUOTES,'',FALSE),"&nbsp;</td>\n";
		echo "		</tr>\n";
	}
	if($dt['fyi_pmh_nt'] != '') {
		if(count($pmh) > 0) {
			echo "		<tr>\n";
			echo "			<td colspan='2' class='wmtPrnLabel'>Other Notes:</td>\n";
			echo "		</tr>\n";
		}
		echo "		<tr>\n";
		echo "			<td colspan='2' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_pmh_nt'],ENT_QUOTES,'',FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "	</table>\n";
	echo "<br>\n";
}
?>
