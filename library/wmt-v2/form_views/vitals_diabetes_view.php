<?php 
$close_v_table = FALSE;
include(FORM_VIEWS.'vitals_basic_view.php');
?>
		<tr>
			<td colspan="2">Diabetes Related</td>
		</tr>
		<tr>
			<td>TC:</td>
			<td><?php echo htmlspecialchars($vitals->TC, ENT_QUOTES); ?>&nbsp;</td>
			<td>LDL:</td>
			<td><?php echo htmlspecialchars($vitals->LDL, ENT_QUOTES); ?>&nbsp;</td>
			<td>HDL:</td>
			<td><?php echo htmlspecialchars($vitals->HDL, ENT_QUOTES); ?>&nbsp;</td>
			<td>Trig:</td>
			<td><?php echo htmlspecialchars($vitals->trig, ENT_QUOTES); ?>&nbsp;</td>
			<td>Micro:</td>
			<td><?php echo htmlspecialchars($vitals->microalbumin, ENT_QUOTES); ?>&nbsp;</td>
			<td>BUN:</td>
			<td><?php echo htmlspecialchars($vitals->BUN, ENT_QUOTES); ?>&nbsp;</td>
			<td>Creatine:</td>
			<td><?php echo htmlspecialchars($vitals->cr, ENT_QUOTES); ?>&nbsp;</td>
		</tr>
<?php if($vitals->note) { ?>
		<tr>
			<td>Vitals Note:&nbsp;</td>
			<td colspan="14"><?php echo htmlspecialchars($vitals->note, ENT_QUOTES); ?></td>
		</tr>
<?php } ?>
	</table>
</fieldset>
