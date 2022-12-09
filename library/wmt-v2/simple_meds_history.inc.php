<?php
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
	echo "	<tr>\n";
	echo "		<td class='LabelCenterBorderB' style='width: 95px'>Start Date</td>\n";
	echo "		<td class='LabelCenterBorderLB'>Medication</td>\n";
	echo "		<td class='LabelCenterBorderLB'>Quantity</td>\n";
	echo "		<td class='LabelCenterBorderLB'>Dosage</td>\n";
	echo "		<td class='LabelCenterBorderLB'>Sig</td>\n";
	echo "		<td class='LabelCenterBorderLB'>Comments</td>\n";
	echo "	</tr>\n";
if($med_hist && (count($med_hist) > 0)) {
	$cnt=1;
	foreach($med_hist as $prev) {
			$sig1=trim(ListLook($prev['route'],'drug_route'));
			if(!empty($sig1)) { $sig1=' by '.$sig1; }
			$sig2=trim(ListLook($prev['interval'],'drug_interval'));
			$sig1=$prev['dosage'].$sig1.' '.$sig2;
			$size=trim($prev['size']);
			$unit=trim(ListLook($prev['unit'],'drug_units'));
			$size.=$unit;
			echo "<tr>\n";
			echo "<td class='BodyBorderB'>".$prev['date_added']."&nbsp;</td>\n";
			echo "<td class='BodyBorderLB'>".$prev['drug']."&nbsp;</td>\n";
			echo "<td class='BodyBorderLB'>".$prev['quantity']."&nbsp;</td>\n";
			echo "<td class='BodyBorderLB'>".$size."&nbsp;</td>\n";
			echo "<td class='BodyBorderLB'>".$sig1."&nbsp;</td>\n";
			echo "<td class='BodyBorderLB'>".$prev['note']."&nbsp;</td>\n";
		echo "</tr>\n";
		$cnt++;
	}
} else {
	echo "<tr>\n";
		echo "<td class='LabelBorderB'>&nbsp;</td>\n";
		echo "<td class='LabelBorderLB'>None on File</td>\n";
		echo "<td class='LabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='LabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='LabelBorderLB'>&nbsp;</td>\n";
		echo "<td class='LabelBorderLB'>&nbsp;</td>\n";
	echo "</tr>\n";
}
echo "</table>\n";
?>
