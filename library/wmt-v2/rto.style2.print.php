<?php
if(!isset($pane_printed)) $pane_printed = false;
if(!isset($pane_title)) $pane_title = xl('Orders','r');
?>
<fieldset style='border: solid 1px black; padding: 0px; border-collapse: collapse;'><legend class='bkkPrnHeader'>&nbsp;<?php echo $pane_title; ?>&nbsp;</legend>
	<table width='100%' border='0' cellspacing='0' cellpadding='3' style='border-collapse: collapse; margin-top: 4px;'>
		<tr>
			<td class='bkkPrnLabel bkkPrnC' style='width: 100px'>Target Dt</td>
			<td class='bkkPrnLabel bkkPrnC' >Event</td>
			<td class='bkkPrnLabel bkkPrnC' style='width: 50%'>Notes</td>
		</tr>
<?php
if(isset($rto_data) && count($rto_data) > 0) {
	$cnt=1;
	foreach($rto_data as $rto) {
		if($rto['rto_target_date'] == '') { $rto['rto_target_date'] = 'Unspecified'; }
?>
		<tr>
			<td class='bkkPrnBody bkkPrnBorder1T'><?php echo htmlspecialchars($rto['rto_target_date'], ENT_QUOTES, '', FALSE); ?></td>
			<td class='bkkPrnBody bkkPrnBorder1T bkkPrnBorder1L'><?php echo htmlspecialchars(ListLook($rto['rto_action'], 'RTO_Action', 'Not Specified'), ENT_QUOTES, '', FALSE); ?></td>
			<td class='bkkPrnBody bkkPrnBorder1T bkkPrnBorder1L'><?php echo htmlspecialchars($rto['rto_notes'], ENT_QUOTES, '', FALSE); ?>&nbsp;</td>
		</tr>
<?php
		$cnt++;
	}
} else {
?>
		<tr>
			<td class='bkkPrnLabel  bkkPrnBorder1T'>&nbsp;</td>
			<td class='bkkPrnBody bkkPrnBorder1T' colspan='2'>No Detail on File</td>
		</tr>
<?php
}
$pane_printed = true;
?>
	</table>
</fieldset>
