<?php 
if(!isset($field_prefix)) $field_prefix = '';
if(!isset($frmdir)) $frmdir = '';
?>
<?php if(checkSettingMode('wmt::hide_ins_title','',$frmdir)) { ?>
<table width="100%"	border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; padding: 0px; margin: 0px; border: solid 1px black;">
<?php } else { ?>
<table width="100%"	border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; padding: 0px; margin: 0px; border-top: solid 1px black; border-left: solid 1px black; border-right: solid 1px black;">
	<tr><td class="bkkPrnLabel wmtPrnC">Insurance Information</td></tr>
</table><table width="100%"	border="0" cellspacing="0" cellpadding="0" style="border-collapse: collapse; padding: 0px; margin: 0px; border: solid 1px black;">
<?php } ?>
	<tr>
		<td style="width: 50%; border-right: solid 1px black;">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td colspan="3"><span class="bkkPrnLabel2"><?php xl('Primary Insurance','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars(($patient->primary) ? $patient->primary : xl('No Insurance','e'), ENT_QUOTES, '', FALSE); ?></span></td>
				<td style="width: 20%;"><span class="bkkPrnLabel2"><?php xl('Policy #','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_id, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td style="width: 20%;"><span class="bkkPrnLabel2"><?php xl('Group #','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_group, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
			<tr>
				<td style="width: 20%;"><span class="bkkPrnLabel2"><?php xl('Insured First','e'); ?>&nbsp;</span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_fname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td style="width: 10%;"><span class="bkkPrnLabel2"><?php xl('Middle','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_mname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span>
				</td>
				<td><span class="bkkPrnLabel2"><?php xl('Last Name','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_lname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Birth Date','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_DOB, ENT_QUOTES, '', FALSE); ?>&nbsp;</span>
				</td>
				<td><span class="bkkPrnLabel2"><?php xl('Relationship','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars(ListLook($patient->primary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
			<tr>
				<td colspan="3"><span class="bkkPrnLabel2"><?php xl('Contact/Attn','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars(($patient->primary_attn), ENT_QUOTES, '', FALSE); ?></span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Phone','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->primary_phone, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Authorization','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($dt['ins1_auth'], ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
		</table></td>

		<td style="width: 50%">
		<table width="100%" border="0" cellspacing="0" cellpadding="2">
			<tr>
				<td colspan="3"><span class="bkkPrnLabel2"><?php xl('Secondary Insurance','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Policy #','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_id, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Group #','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_group, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
			<tr>
				<td><span class="bkkPrnLabel2"><?php xl('Subscriber First','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_fname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Middle','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_mname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Last Name','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_lname, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('SS#','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_ss, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Relationship','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars(ListLook($patient->secondary_relat, 'sub_relation'), ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
			<tr>
				<td colspan="3"><span class="bkkPrnLabel2"><?php xl('Contact/Attn','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars(($patient->secondary_attn), ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Phone','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($patient->secondary_phone, ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
				<td><span class="bkkPrnLabel2"><?php xl('Authorization','e'); ?></span><br>
					<span class="bkkPrnBody"><?php echo htmlspecialchars($dt['ins2_auth'], ENT_QUOTES, '', FALSE); ?>&nbsp;</span></td>
			</tr>
    </table></td>
	</tr>
</table>
<br>
