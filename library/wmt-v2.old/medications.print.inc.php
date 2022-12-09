<?php
if(!isset($med_add_allowed)) { $med_add_allowed = false; }
if(!isset($use_meds_not_rx)) { $use_meds_not_rx = false; }
echo "<div class='wmtPrnMainContainer'>\n";
echo "<div class='wmtPrnCollapseBar'>\n";
echo "	<span class='wmtPrnChapter'>",xl('Current Medications','e'),"</span>\n";
echo "</div>\n";
echo "<div class='wmtPrnCollapseBox'>\n";
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
if($use_meds_not_rx) {
	echo "	<tr>\n";
	echo "	<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
	echo "	<td class='wmtPrnLabelCenterBorderLB'>",xl('Medication','e'),"</td>\n";
	echo "	<td class='wmtPrnLabelCenterBorderLB'>",xl('End Date','e'),"</td>\n";
	echo "	<td class='wmtPrnLabelCenterBorderLB'>",xl('Status','e'),"</td>\n";
	echo "	<td class='wmtPrnLabelCenterBorderLB'>",xl('Comments','e'),"</td>\n";
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
			echo "<td class='wmtPrnBodyBorderB'>".$prev['begdate']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$prev['title']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$prev['enddate']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$med_status."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$prev['comments']."&nbsp;</td>\n";
			echo "</tr>\n";
			$cnt++;
		}
	} else {
		echo "<tr>\n";
		echo "<td class='wmtPrnLabelBorderB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>",xl('None on File','e'),"</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "</tr>\n";
	}
} else {
	// This is the section for e-Rx clients, no medication adding
	echo "	<tr>\n";
	echo "		<td class='wmtPrnLabelCenterBorderB' style='width: 95px'>",xl('Start Date','e'),"</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Medication','e'),"</td>\n";
	// echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Quantity','e'),"</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Dosage','e'),"</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Sig','e'),"</td>\n";
	echo "		<td class='wmtPrnLabelCenterBorderLB'>",xl('Comments','e'),"</td>\n";
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
			echo "<td class='wmtPrnBodyBorderB'>".$prev['date_added']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$prev['drug']."&nbsp;</td>\n";
			// echo "<td class='wmtPrnBodyBorderLB'>".$prev['quantity']."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$size."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$sig1."&nbsp;</td>\n";
			echo "<td class='wmtPrnBodyBorderLB'>".$prev['note']."&nbsp;</td>\n";
			echo "</tr>\n";
			$cnt++;
		}
	} else {
		echo "<tr>\n";
		echo "<td class='wmtPrnLabelBorderB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>",xl('None on File','e'),"</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		// echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='wmtPrnLabelBorderLB'>&nbsp;</td>\n";
		echo "</tr>\n";
	}
}
$tmp_col=5;
if($med_add_allowed) { $tmp_col=4; }
if(!empty($dt['fyi_med_nt'])) {
	echo "		<tr>\n";
	echo "			<td class='wmtPrnLabel' colspan='2'>",xl('Other Notes','e'),":</td>\n";
	echo "		</tr>\n";
	echo "		<tr>\n";
	echo "			<td class='wmtPrnBody' colspan='$tmp_col'>".$dt['fyi_med_nt']."</td>\n";
	echo "		</tr>\n";
}
echo "</table>\n";
echo "</div>\n";
echo "</div>\n";
?>
