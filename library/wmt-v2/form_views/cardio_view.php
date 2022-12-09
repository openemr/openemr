<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'last_ekg', 'last_pft');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Cardio&nbsp;&amp;&nbsp;Pulmonary Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last EKG:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_ekg'},ENT_QUOTES); ?></td>
			<td>Last PFT:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_pft'},ENT_QUOTES); ?></td>
			<td style="width: 22%;">&nbsp;</td>
		</tr>

	</table>
</fieldset>
