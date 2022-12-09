<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Vitals','r');
if(!isset($empty_vitals)) $empty_vitals = false;
?>

<?php if($visit->vid) { ?>
<fieldset style='border: solid 1px black;'><legend class='bkkPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellpadding='3' cellspacing='0'>
		<tr>
			<td colspan='4'><span class='bkkPrnLabel'>Vitals Taken:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->timestamp, ENT_QUOTES, '', FALSE); ?></span></td>
		</tr>
		<tr>
			<td><span class='bkkPrnLabel'>Height:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->height, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnLabel'>Weight:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->weight, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnLabel'>BMI:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->BMI, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->BMI_status, ENT_QUOTES, '', FALSE); ?></span></td>
		</tr>
		<tr>
			<td><span class='bkkPrnLabel'>Blood Pressure:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->bps, ENT_QUOTES, '', FALSE); ?>&nbsp;/&nbsp;<?php echo htmlspecialchars($visit->bpd, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnLabel'>Temperature:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->temp, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnLabel'>Pulse:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->pulse, ENT_QUOTES, '', FALSE); ?></span></td>
			<td><span class='bkkPrnLabel'>Respiration:&nbsp;</span><span class='bkkPrnBody'><?php echo htmlspecialchars($visit->respiration, ENT_QUOTES, '', FALSE); ?></span></td>
		</tr>
	</table>
</fieldset>
<?php
	$pane_printed = true;
} else if($empty_vitals) {
?>
<fieldset style='border: solid 1px black;'><legend class='bkkPrnHeader'>&nbsp;Vitals&nbsp;</legend>
	<table width='100%' border='0' cellpadding='0' cellspacing='0'>
		<tr>
			<td colspan='4'><span class='bkkPrnLabel'><?php echo htmlspecialchars($empty_vitals, ENT_QUOTES, '', FALSE); ?></span></td>
		</tr>
	</table>
</fieldset>
<?php
	$pane_printed = true;
}
?>
