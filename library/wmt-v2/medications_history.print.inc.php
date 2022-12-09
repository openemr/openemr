<?php
if(!isset($med_hist)) $med_hist = array();
if(!isset($dt['fyi_medhist_nt'])) $dt['fyi_medhist_nt'] = '';
if(isset($fyi->fyi_medhist_nt)) $dt['fyi_medhist_nt'] = $fyi->fyi_medhist_nt;
if((count($med_hist) > 0) || $dt['fyi_medhhist_nt'] != '') {
	echo "<div class='wmtPrnMainContainer'>\n";
	echo "<div class='wmtPrnCollapseBar'>\n";
	echo "	<span class='wmtPrnChapter'>",xl('Medication History','e'),"</span>\n";
	echo "</div>\n";
	echo "<div class='wmtPrnCollapseBox'>\n";
	echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	if(count($med_hist) > 0) {
		echo "	<tr>\n";
		echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Medication','e'),"</td>\n";
		// echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Quantity','e'),"</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Dosage','e'),"</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Sig','e'),"</td>\n";
		echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Comments','e'),"</td>\n";
		echo "	</tr>\n";
		$cnt=1;
		foreach($med_hist as $prev) {
			$sig1 = trim(ListLook($prev['route'],'drug_route'));
			if($sig1) $sig1 = ' '.$sig1;
			$form = trim(ListLook($prev['form'],'drug_form'));
			if($form) $form = ' '.$form;
			$sig2 = trim(ListLook($prev['interval'],'drug_interval'));
			if($sig2) $sig2 = ' '.$sig2;
			$sig1 = $prev['dosage'].$form.$sig1.$sig2;
			$size = trim($prev['size']);
			$unit = trim(ListLook($prev['unit'],'drug_units'));
			$size .= $unit;
			echo "<tr>\n";
			echo "<td class='wmtPrnBodyBorderB'>".htmlspecialchars($prev['date_added'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($prev['drug'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			// echo "<td class='wmtPrnBodyBorderLB'>".$prev['quantity']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$size."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$sig1."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "</tr>\n";
			$cnt++;
			if($cnt > 10) break;
		}
	}
	if($dt['fyi_medhist_nt'] != '') {
		echo "		<tr>\n";
		echo "			<td class='wmtPrnLabel' colspan='2'>",xl('Other Notes','e'),":</td>\n";
		echo "		</tr>\n";
		echo "		<tr>\n";
		echo "			<td colspan='5' class='wmtPrnBody'>",htmlspecialchars($dt['fyi_medhist_nt'], ENT_QUOTES, '', FALSE),"</td>\n";
		echo "		</tr>\n";
	}
	echo "</table>\n";
	echo "</div>\n";
	echo "</div>\n";
}
?>
