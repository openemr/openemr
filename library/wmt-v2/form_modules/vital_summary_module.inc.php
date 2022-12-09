<?php
$use_abnormal = FALSE;
if(file_exists($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc')) {
	require_once($GLOBALS['incdir'].'/forms/vitals/abnormal_vitals.inc');
	include_once($GLOBALS['srcdir'].'/patient.inc');
	$use_abnormal = TRUE;
	$age = explode(' ', $patient->age);
	$num = $age[0];
	$frame = 'year';
	if(isset($age[1])) $frame = $age[1];
}

$sql = "
			SELECT
				date,
				bps,
				bpd,
				weight,
				height,
				temperature,
				pulse,
				respiration,
				BMI
			FROM
				form_vitals
			WHERE pid = ?
			ORDER BY date DESC";

$res = sqlStatement( $sql, array($pid) );

if (sqlNumRows( $res ) > 0) {
?>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr style="border-bottom: solid 1px black;">
			<th class='date'><?php echo htmlspecialchars( xl('Date'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('Weight'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('Height'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('BP'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('Pulse'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('Resp'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('Temp'),ENT_NOQUOTES); ?></th>
			<th><?php echo htmlspecialchars( xl('BMI'),ENT_NOQUOTES); ?></th>
		</tr>
<?php
	$even = false;
	$class = '';
	
	while ( $row = sqlFetchArray( $res ) ) {
		$accent = $hr_accent = $rsp_accent = $temp_accent = $bp_accent = $bmi_accent = '';
		if($use_abnormal) {
			$hr_accent = isAbnormalPulse($num, $frame, $row['pulse']);
			$rsp_accent = isAbnormalRespiration($num, $frame, $row['respiration']);
			$temp_accent = isAbnormalTemperature($num, $frame, $row['temperature']);
			$bp_accent = isAbnormalBps($num, $frame, $row['bps']);
			if(!$bp_accent) $bp_accent = isAbnormalBpd($num, $frame, $row['bpd']);
			$bmi_accent = isAbnormalBMI($num, $frame, $row['BMI']);
			if($hr_accent || $rsp_accent || $temp_accent || $bp_accent || $bmi_accent) 
				$accent = 'style="color: red; "';
		}
		
		$date = date( 'Y-m-d', strtotime( $row['date'] ) );
		echo "<tr>";
		echo "<td align='center'";
		echo $accent ? " $accent" : '';
		echo '>' . htmlspecialchars( $date ) . "</td>";
		echo "<td align='center'>" . htmlspecialchars( $row['weight'], ENT_NOQUOTES ) . "</td>";
		echo "<td align='center'>" . htmlspecialchars( $row['height'], ENT_NOQUOTES ) . "</td>";
		echo "<td align='center'";
		echo $bp_accent ? " $bp_accent" : '';
		echo '>';
		if ($row['bps'] || $row['bpd']) {
			echo($row['bps']) ? htmlspecialchars( $row['bps'], ENT_NOQUOTES ) : "-";
			echo " / ";
			echo($row['bpd']) ? htmlspecialchars( $row['bpd'], ENT_NOQUOTES ) : "-";
		}
		echo "</td>";
		echo "<td align='center'";
		echo $hr_accent ? " $hr_accent" : '';
		echo '>'  . htmlspecialchars( $row['pulse'], ENT_NOQUOTES ) . "</td>";
		echo "<td align='center'";
		echo $rsp_accent ? " $rsp_accent" : '';
		echo '>' . htmlspecialchars( $row['respiration'], ENT_NOQUOTES ) . "</td>";
		echo "<td align='center'";
		echo $temp_accent ? " $temp_accent" : '';
		echo '>' . htmlspecialchars( $row['temperature'], ENT_NOQUOTES ) . "</td>";
		echo "<td align='center'";
		echo $bmi_accent ? " $bmi_accent" : '';
		echo '>' . htmlspecialchars( $row['BMI'], ENT_NOQUOTES ) . "</td>";
		echo "</tr>";
	}
	echo "</table>";
} else {
	echo "<div style='width:100%' class='summary_item'>" . xlt( "No Results" ) . "</div>";
}
?>
