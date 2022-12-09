<?php
echo "	<table width='100%' border='0' cellspacing='0' cellpadding='0'>\n";
// This is the section for e-Rx clients, no medication adding
echo "	<tr>\n";
echo "		<td class='LabelCenterBorderB' style='width: 95px'>Start Date</td>\n";
echo "		<td class='LabelCenterBorderLB'>Medication</td>\n";
echo "		<td class='LabelCenterBorderLB'>Quantity</td>\n";
echo "		<td class='LabelCenterBorderLB'>Dosage</td>\n";
echo "		<td class='LabelCenterBorderLB'>Sig</td>\n";
echo "		<td class='LabelCenterBorderLB'>Comments</td>\n";
echo "	</tr>\n";
$cnt=1;
if(isset($meds) && (count($meds) > 0)) {
	foreach($meds as $prev) {
		// This flag isn't set and there's nothing to test for activity
		// $med_status='Inactive';
		// if($prev['active'] == '1') { $med_status='Active'; }
		$sig1=trim(ListLook($prev['route'],'drug_route'));
		if(!empty($sig1)) { $sig1=' by '.$sig1; }
		$sig2=trim(ListLook($prev['interval'],'drug_interval'));
		$sig1=$prev['dosage'].$sig1.' '.$sig2;
		$size=trim($prev['size']);
		$unit=trim(ListLook($prev['unit'],'drug_units'));
		$size.=$unit;
		echo "<tr>\n";
		echo "<td class='BodyBorderB'><input name='med_id_".$cnt."' id='med_id_".$cnt."' type='hidden' readonly='readonly' tabindex='-1' value='".$prev['id']."' />".$prev['date_added']."&nbsp;</td>\n";
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
