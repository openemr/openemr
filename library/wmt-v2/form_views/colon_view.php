<?php 
if(!isset($field_prefix)) $field_prefix = '';
$local_fields = array( 'last_colon', 'last_fecal', 'last_barium', 
	'last_sigmoid', 'last_psa', 'last_rectal');

foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Colon&nbsp;<?php echo $pat_sex == 'f' ? '' : '&amp;&nbsp;Prostate&nbsp;'; ?></legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Last Colonoscopy/Cologuard:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_colon'},ENT_QUOTES); ?></td>
			<td>Last Fecal Occult Blood Test:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_fecal'},ENT_QUOTES); ?></td>
			<td>Last Barium Enema:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_barium'},ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Last Flexible Sigmoidoscopy:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_sigmoid'},ENT_QUOTES); ?></td>
			<?php if($pat_sex == 'f') { ?>
			<?php } else { ?>
			<td>Last PSA:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_psa'},ENT_QUOTES); ?></td>
			<?php } ?>
				<td>Last Rectal Exam:</td>
				<td><?php echo htmlspecialchars($dt{$field_prefix.'last_rectal'},ENT_QUOTES); ?></td>
		</tr>

	</table>
</fieldset>
