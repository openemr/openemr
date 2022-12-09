<?php 
if(!isset($field_prefix)) $field_prefix = '';

$local_fields = array( 'last_mp', 'last_bone', 'last_mamm', 'mam_law',
	'hpv', 'last_hpv', 'last_pap', 'HCG', 'age_men', 'pflow', 'pfreq', 
	'pflow_dur', 'pfreq_days', 'bc_chc', 'bc', 'db_pap_hist_nt');
foreach($local_fields as $tmp) {
	if(!isset($dt[$field_prefix.$tmp])) $dt[$field_prefix.$tmp]='';
}
?>

<?php if($pat_sex == 'f') { ?>
<fieldset style="border: solid 1px gray; margin: 6px;"><legend class="wmtLabel">&nbsp;Gynecological&nbsp;</legend>
	<table width="100%" border="0" cellspacing="0" cellpadding="0">
		<tr>
			<td>LMP:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_mp'},ENT_QUOTES); ?></td>
			<td>Last Bone Density:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_bone'},ENT_QUOTES); ?></td>
			<td>Last Mammogram:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_mamm'},ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td colspan="2">Dense Breast Mammogram Law Informed?
			<td><?php echo ListLook($dt[$field_prefix.'mam_law'], 'YesNo'); ?></td>
		</tr>

		<tr>
			<td>HPV Vaccinated:</td>
			<td colspan="2"><?php echo ListLook($dt[$field_prefix.'hpv'], 'Yes_No'); ?>
			</td>
			<td>Last HPV:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_hpv'},ENT_QUOTES); ?></td>
			<td>Last Pap Smear:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'last_pap'},ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>History of Abn Pap:</td>
			<td colspan="5"><?php echo htmlspecialchars($dt['db_pap_hist_nt'], ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Last HCG Result:</td>
			<td colspan="2"><?php echo htmlspecialchars($dt{$field_prefix.'HCG'},ENT_QUOTES); ?></td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
			<td>&nbsp;</td>
		</tr>

		<tr>
			<td>Periods:</td>
			<td colspan="2">Age Menarche:</td>
			<td colspan="2"><?php echo htmlspecialchars($dt{$field_prefix.'age_men'}, ENT_QUOTES); ?></td>
		</tr>

		<tr>
			<td>Flow:</td>
			<?php 
			$choices = array('l' => 'Light', 'h' => 'Heavy', 
			'n' => 'Normal', 'x' => 'None', 'm' => 'Menopause');
			if(!isset($choices[$field_prefix.'pflow'])) 
				$choices[$field_prefix.'pflow'] = '';
			?>
			<td><?php echo $choices[$field_prefix.'pflow']; ?></td>
			<td class="wmtR" colspan="2">Frequency:</td>
			<?php 
			$choices = array('r' => 'Regular', 'i' => 'Ireegular', 
			'x' => 'None');
			if(!isset($choices[$field_prefix.'pfreq'])) 
				$choices[$field_prefix.'pfreq'] = '';
			?>
			<td><?php echo $choices[$field_prefix.'pfreq']; ?></td>
		</tr>
		<tr>
			<td>Duration:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'pflow_dur'}, ENT_QUOTES); ?>&nbsp;&nbsp;days</td>
			<td>Interval:</td>
			<td><?php echo htmlspecialchars($dt{$field_prefix.'pfreq_days'}, ENT_QUOTES); ?>&nbsp;&nbsp;days</td>
		</tr>

		<tr>
			<td>Birth Control:</td>
			<td><?php echo ListLook($dt{$field_prefix.'bc_chc'}, 'Birth_Control_Methods'); ?></td>
			<td colspan="4"><?php echo htmlspecialchars($dt{$field_prefix.'bc'}, ENT_QUOTES); ?></td>
		</tr>

	</table>
</fieldset>
<?php } ?>

