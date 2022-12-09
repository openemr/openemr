<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'pat_blood_type', 'pat_rh_factor', 'last_chol', 
	'last_lipid', 'last_hepc', 'last_lipo', 'last_tri', 'last_urine_alb', 
	'last_hgba1c', 'last_hgba1c_val'
);
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<fieldset style="border: solid 1px gray; margin: 6px;"><legend>&nbsp;Blood&nbsp;&amp;&nbsp;Urine Tests&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>Blood Type:</td>
			<td colspan="2">
				<?php echo ListLook($dt{$field_prefix.'pat_blood_type'},'Blood_Types'); ?>
				&nbsp;&nbsp;
				<?php echo ListLook($dt{$field_prefix.'pat_rh_factor'},'RH_Factor'); ?>
			</td>
			<td>Last Cholesterol Check:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_chol'}),ENT_QUOTES); ?></td>
			<td>Last Hepatitis C Test:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_hepc'}),ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Last Lipid Panel:</td>
			<td class="wmtDateCell"><?php echo htmlspecialchars(($dt{$field_prefix.'last_lipid'}),ENT_QUOTES); ?></td>
			<td>Last Lipoprotein:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_lipo'}),ENT_QUOTES); ?></td>
			<td>Last Triglycerides:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_tri'}),ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Last Urine Micro Alb:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_urine_alb'}),ENT_QUOTES); ?></td>
			<td>Last HgbA1c:</td>
			<td><?php echo htmlspecialchars(($dt{$field_prefix.'last_hgba1c'}),ENT_QUOTES); ?></td>
			<td>Last HgbA1c Value:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_hgba1c_val'},ENT_QUOTES); ?></td>
			<td>&nbsp;</td>
		</tr>

	</table>
</fieldset>
