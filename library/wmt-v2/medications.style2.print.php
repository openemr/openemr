<?php
if(!isset($use_meds_not_rx)) $use_meds_not_rx = false;
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Current Medications','r');
if(!isset($dt['fyi_med_nt'])) $dt['fyi_med_nt'] = '';
if(!isset($fyi->fyi_med_nt)) $fyi->fyi_med_nt = '';
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='wmtPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse;'>
<?php
if($use_meds_not_rx) {
	echo "	<tr>\n";
	echo "	<td class='wmtPrnLabel wmtPrnC' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
	echo "	<td class='wmtPrnLabel wmtPrnC'>",xl('Medication','e'),"</td>\n";
	echo "	<td class='wmtPrnLabel wmtPrnC'>",xl('End Date','e'),"</td>\n";
	echo "	<td class='wmtPrnLabel wmtPrnC'>",xl('Status','e'),"</td>\n";
	echo "	<td class='wmtPrnLabel wmtPrnC'>",xl('Comments','e'),"</td>\n";
	echo "</tr>\n";
	$cnt=1;
	if(isset($meds) && (count($meds) > 0)) {
		foreach($meds as $prev) {
			$med_status=xl('Active','r');
			if($prev['enddate'] != '' && $prev['enddate'] != '0000-00-00') {
				$med_status=xl('Inactive','r');
			}
			if($prev['extrainfo'] != '') { 
				$med_status= $prev['extrainfo'];
			}
			echo "<tr>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['begdate'], ENT_QUOTES, '', FALSE)."</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['title'], ENT_QUOTES, '', FALSE)."</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['enddate'], ENT_QUOTES, '', FALSE)."</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($med_status, ENT_QUOTES, '', FALSE)."</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>&nbsp;".htmlspecialchars($prev['comments'], ENT_QUOTES, '', FALSE)."</td>\n";
			echo "</tr>\n";
			$cnt++;
		}
	} else {
		echo "<tr>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;",xl('No Detail on File','e'),"</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "</tr>\n";
	}
} else {
	// This is the section for e-Rx clients, no medication adding
	echo "	<tr>\n";
	echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC'>",xl('Medication','e'),"</td>\n";
	// echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC'>",xl('Quantity','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC'>",xl('Dosage','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC'>",xl('Sig','e'),"</td>\n";
	echo "		<td class='wmtPrnLabel wmtPrnBorder1B wmtPrnC'>",xl('Comments','e'),"</td>\n";
	echo "	</tr>\n";
	$cnt=1;
	if(isset($meds) && (count($meds) > 0)) {
		foreach($meds as $prev) {
			// This flag isn't set and there's nothing to test for activity
			// $med_status='Inactive';
			// if($prev['active'] == '1') { $med_status='Active'; }
			$sig1=trim(ListLook($prev['route'],'drug_route'));
			if($sig1) { $sig1 = ' '.$sig1; }
			$form=trim(ListLook($prev['form'],'drug_form'));
			if($form) { $form = ' '.$form; }
			$sig2=trim(ListLook($prev['interval'],'drug_interval'));
			if($sig2) { $sig2 = ' '.$sig2; }
			$sig1=$prev['dosage'].$form.$sig1.$sig2;
			$size=trim($prev['size']);
			$unit=trim(ListLook($prev['unit'],'drug_units'));
			$size.=$unit;
			echo "<tr>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1T'>".htmlspecialchars($prev['date_added'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".htmlspecialchars($prev['drug'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			// echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".$prev['quantity']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".htmlspecialchars($size, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".htmlspecialchars($sig1, ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "<td class='wmtPrnBody wmtPrnBorder1L wmtPrnBorder1T'>".htmlspecialchars($prev['note'], ENT_QUOTES, '', FALSE)."&nbsp;</td>\n";
			echo "</tr>\n";
			$cnt++;
		}
	} else {
		echo "<tr>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>",xl('No Detail on File','e'),"</td>\n";
		// echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabel wmtPrnBorder1T'>&nbsp;</td>\n";
		echo "</tr>\n";
	}
}
$tmp_col=5;
if($med_add_allowed) { $tmp_col=5; }
if($fyi->fyi_med_nt) {
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel wmtPrnBorder1T' colspan='$tmp_col'>",xl('Other Notes','e'),":</td>\n";
	echo "		</tr>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnBody' colspan='$tmp_col'>",htmlspecialchars($fyi->fyi_med_nt, ENT_QUOTES, '', FALSE),"</td>\n";
	echo "		</tr>\n";
}
echo "</table>\n";
echo "</fieldset>\n";
$pane_printed = true;
?>
