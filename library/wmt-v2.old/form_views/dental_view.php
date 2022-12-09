<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'last_dental', 'last_dental_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Dental&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Dental Exam:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_dental'}, ENT_QUOTES); ?></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="wmtPrnT">Dental Exam Notes:</td>
			<td colspan="7"><?php echo htmlspecialchars($dt{$field_prefix.'last_dental_nt'}, ENT_QUOTES); ?></td>
		</tr>
	</table>
</fieldset>
