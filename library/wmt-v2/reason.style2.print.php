<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title= xl('Reason for Encounter','r');
?>
		
<?php if($visit->full_reason) { ?>
<fieldset style='border: solid 1px black;'><legend class='bkkPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellpadding='3' cellspacing='0'>
		<!-- tr>
			<td class='bkkPrnLabel'></td>
		</tr -->
		<tr>
			<td class='bkkPrnBody'><?php echo htmlspecialchars($visit->full_reason, ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
	</table>
</fieldset>
<?php
	$pane_printed = true;
}
?>
