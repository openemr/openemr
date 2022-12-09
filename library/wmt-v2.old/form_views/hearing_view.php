<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'last_hear', 'left_ear', 'right_ear', 'hear_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Hearing&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Hearing Test:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_hear'},ENT_QUOTES); ?></td>
			<td>Left Ear:</td>
			<td><?php echo ListLook($dt{$field_prefix.'left_ear'},'PassFail'); ?></td>
			<td>Right Ear:</td>
			<td> <?php echo ListLook($dt{$field_prefix.'right_ear'},'PassFail'); ?></td>
		</tr>

		<tr>
			<td class="wmtPrnT">Hearing Notes:</td>
			<td colspan="6"><?php echo htmlspecialchars($dt{$field_prefix.'hear_nt'}, ENT_QUOTES); ?></td>
		</tr>

	</table>
</fieldset>
