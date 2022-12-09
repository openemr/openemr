<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'last_db_screen', 'last_db_eye', 'last_db_foot',
	'last_glaucoma', 'last_db_dbsmt' );
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Diabetes Related&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Diabetes Screening:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_db_screen'},ENT_QUOTES); ?></td>
			<td>Last Diabetic Eye Exam:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_db_eye'},ENT_QUOTES); ?></td>
			<td>Last Diabetic Foot Exam:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_db_foot'},ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Last Glaucoma Screening:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_glaucoma'},ENT_QUOTES); ?></td>
			<td>Last Self-Management Training:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_db_dbsmt'},ENT_QUOTES); ?></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

	</table>
</fieldset>
