<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Next Appointment','r');
?>
		
<?php if($visit->next_appt_reason || $visit->next_appt_dt) { ?>
<fieldset style='border: solid 1px black;'><legend class='bkkPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellpadding='3' cellspacing='0'>
		<tr>
			<td class='bkkPrnLabel'>Appt Date:</td>
			<td class='bkkPrnBody'><?php echo htmlspecialchars($visit->next_appt_dt, ENT_QUOTES, '', FALSE); ?></td>
			<td class='bkkPrnLabel'>Appt Time:</td>
			<td class='bkkPrnBody'><?php echo htmlspecialchars($visit->next_appt_time, ENT_QUOTES, '', FALSE); ?></td>
		</tr>
		<tr>
			<td class='bkkPrnLabel'>Reason:</td>
			<td class='bkkPrnBody' colspan='3'><?php echo htmlspecialchars($visit->next_appt_reason, ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
		<?php if($visit->next_appt_comment) { ?>
		<tr>
			<td class='bkkPrnLabel'>Comments:</td>
			<td class='bkkPrnBody' colspan='3'><?php echo htmlspecialchars($visit->next_appt_comment, ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
		<?php } ?>
		<?php if($visit->next_appt_provider) { ?>
		<tr>
			<td class='bkkPrnLabel'>Provider:</td>
			<td class='bkkPrnBody' colspan='3'><?php echo htmlspecialchars(UserNameFromID($visit->next_appt_provider), ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
		<?php } ?>
	</table>
</fieldset>
<?php
	$pane_printed = true;
}
?>
